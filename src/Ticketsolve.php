<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\ticketsolve;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\console\Application as ConsoleApplication;
use craft\queue\Queue;
use craft\web\twig\variables\CraftVariable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Event;
use yii\queue\ExecEvent;
use burnthebook\ticketsolve\jobs\SyncJob;
use burnthebook\ticketsolve\services\TagsService;
use burnthebook\ticketsolve\services\SyncService;
use burnthebook\ticketsolve\services\TwigService;
use burnthebook\ticketsolve\models\Settings;
use burnthebook\ticketsolve\elements\Venue as VenueElement;
use burnthebook\ticketsolve\elements\Show as ShowElement;
use burnthebook\ticketsolve\elements\Event as EventElement;
use burnthebook\ticketsolve\fields\Shows as ShowsField;

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
    public string $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @return Settings
     */
    public function getSettings(): ?craft\base\Model
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
            $this->controllerNamespace = 'burnthebook\ticketsolve\console\controllers';
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
                $event->rules['ticketsolve'] = 'ticketsolve/admin/index';
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

    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();
        $item['subnav'] = [
            'venues' => ['label' => Craft::t('ticketsolve', 'Venues'), 'url' => 'ticketsolve/venues'],
            'shows' => ['label' => Craft::t('ticketsolve', 'Shows'), 'url' => 'ticketsolve/shows'],
            'events' => ['label' => Craft::t('ticketsolve', 'Events'), 'url' => 'ticketsolve/events'],
        ];
        return $item;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?craft\base\Model
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
