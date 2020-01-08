<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\ticketsolve\fields;

use craft\fields\BaseRelationField;

use Craft;
use devkokov\ticketsolve\elements\Show;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class Shows extends BaseRelationField
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('ticketsolve', 'Ticketsolve Shows');
    }

    protected static function elementType(): string
    {
        return Show::class;
    }

    public static function defaultSelectionLabel(): string
    {
        return \Craft::t('ticketsolve', 'Add a show');
    }
}
