<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\ticketsolve\elements;

use Craft;
use craft\base\Element;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ArrayHelper;
use devkokov\ticketsolve\elements\db\VenueQuery;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class Venue extends Element
{
    // Public Properties
    // =========================================================================

    public $venueId;
    public $name = '';

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('ticketsolve', 'Venue');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('ticketsolve', 'Venues');
    }

    /**
     * @inheritdoc
     * @return VenueQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new VenueQuery(static::class);
    }

    protected static function defineSortOptions(): array
    {
        return [
            'venueId' => \Craft::t('ticketsolve', 'Venue ID'),
            'name' => \Craft::t('ticketsolve', 'Name'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'venueId' => \Craft::t('ticketsolve', 'Venue ID'),
            'name' => \Craft::t('ticketsolve', 'Name'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['venueId','name'];
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['name', 'venueId'], 'required'],
        ];
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            \Craft::$app->db->createCommand()
                ->insert('{{%ticketsolve_venues}}', [
                    'id' => $this->id,
                    'venueId' => $this->venueId,
                    'name' => $this->name,
                ])
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update('{{%ticketsolve_venues}}', [
                    'venueId' => $this->venueId,
                    'name' => $this->name,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
    }

    /**
     * @inheritdoc
     */
    public function beforeMoveInStructure(int $structureId): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterMoveInStructure(int $structureId)
    {
    }
}
