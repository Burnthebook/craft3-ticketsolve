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

use craft\web\twig\variables\CraftVariable;
use devkokov\ticketsolve\services\TicketsolveService as TicketsolveServiceService;
use devkokov\ticketsolve\models\Settings;
use devkokov\ticketsolve\elements\Venue as VenueElement;
use devkokov\ticketsolve\elements\Show as ShowElement;
use devkokov\ticketsolve\elements\Event as EventElement;
use devkokov\ticketsolve\fields\Show as ShowField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class Ticketsolve
 *
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 *
 * @property  TicketsolveServiceService $ticketsolveService
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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

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
                $event->rules['cpActionTrigger1'] = 'ticketsolve/default/do-something';
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
                $event->types[] = ShowField::class;
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin !== $this) {
                    return;
                }

                // add the initial sync job to the queue and chain subsequent sync jobs
            }
        );

        Event:on(
            Plugins::class,
            Plugins::EVENT_AFTER_UNINSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin !== $this) {
                    return;
                }

                // remove any schedule sync jobs
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $e) {
                /** @var CraftVariable $variable */
                $variable = $e->sender;

                // Attach a behavior:
                $variable->attachBehaviors([
                    CraftVariableBehavior::class,
                ]);
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
     * @inheritdoc
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
