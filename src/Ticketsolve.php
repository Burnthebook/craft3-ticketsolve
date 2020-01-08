<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\ticketsolve;

use craft\console\Application as ConsoleApplication;
use craft\queue\Queue;
use craft\web\twig\variables\CraftVariable;
use devkokov\ticketsolve\jobs\SyncJob;
use devkokov\ticketsolve\services\TagsService;
use devkokov\ticketsolve\services\SyncService;
use devkokov\ticketsolve\models\Settings;
use devkokov\ticketsolve\elements\Venue as VenueElement;
use devkokov\ticketsolve\elements\Show as ShowElement;
use devkokov\ticketsolve\elements\Event as EventElement;
use devkokov\ticketsolve\fields\Shows as ShowsField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use devkokov\ticketsolve\services\TwigService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Event;
use yii\queue\ExecEvent;

/**
 * Class Ticketsolve
 *
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 *
 * @property  SyncService $syncService
 * @property  TagsService $tagsService
 * @property  TwigService $twigService
 */
class Ticketsolve extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Ticketsolve
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @return Settings
     */
    public function getSettings()
    {
        return parent::getSettings();
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'devkokov\ticketsolve\console\controllers';
        }

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'ticketsolve/default';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['ticketsolve/sync-now'] = 'ticketsolve/admin/sync-now';
            }
        );

        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = VenueElement::class;
                $event->types[] = ShowElement::class;
                $event->types[] = EventElement::class;
            }
        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ShowsField::class;
            }
        );

        // queue subsequent sync jobs
        Craft::$app->queue->on(Queue::EVENT_AFTER_EXEC, function ($event) {
            /** @var ExecEvent $event */
            $settings = Ticketsolve::getInstance()->getSettings();
            if ($event->job instanceof SyncJob && $settings->autoSync) {
                $this->syncService->queueSyncJob($settings->syncDelay);
            }
        });

        if ($this->getSettings()->autoSync) {
            // ensure there's a sync job in the queue e.g. if autoSync was enabled post-install
            if (empty($this->syncService->getQueuedSyncJobs(true))) {
                $this->syncService->queueSyncJob($this->getSettings()->syncDelay);
            }
        } else {
            $this->syncService->removeQueuedSyncJobs();
        }

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_UNINSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin !== $this) {
                    return;
                }
                $this->syncService->removeQueuedSyncJobs(true);
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $e) {
                /** @var CraftVariable $variable */
                $variable = $e->sender;

                $variable->set('ticketsolve', TwigService::class);
            }
        );

        Craft::info(
            Craft::t(
                'ticketsolve',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function getCpNavItem()
    {
        $item = parent::getCpNavItem();
        $item['subnav'] = [
            'dashboard' => ['label' => 'Dashboard', 'url' => 'ticketsolve'],
            'venues' => ['label' => 'Venues', 'url' => 'ticketsolve/venues'],
            'shows' => ['label' => 'Shows', 'url' => 'ticketsolve/shows'],
            'events' => ['label' => 'Events', 'url' => 'ticketsolve/events'],
        ];
        return $item;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'ticketsolve/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
