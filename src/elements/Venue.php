<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace devkokov\ticketsolve\elements;

use Craft;
use craft\elements\db\ElementQueryInterface;
use devkokov\ticketsolve\elements\db\ShowQuery;
use devkokov\ticketsolve\elements\db\VenueQuery;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class Venue extends AbstractComparableElement
{
    const TABLE     = '{{%ticketsolve_venues}}';
    const TABLE_STD = 'ticketsolve_venues';

    // Public Properties
    // =========================================================================

    public $venueRef;
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
            'name' => \Craft::t('ticketsolve', 'Name'),
            'venueRef' => \Craft::t('ticketsolve', 'Venue ID'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'name' => \Craft::t('ticketsolve', 'Name'),
            'venueRef' => \Craft::t('ticketsolve', 'Venue ID'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['venueRef','name'];
    }

    protected static function defineComparableAttributes(): array
    {
        return ['venueRef','name'];
    }

    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('ticketsolve', 'All Venues'),
                'criteria' => []
            ],
        ];
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
            [['name', 'venueRef'], 'required'],
        ];
    }

    public function __toString()
    {
        if ($this->name) {
            return $this->name;
        }

        return parent::__toString();
    }

    /**
     * @return ShowQuery
     */
    public function getShows()
    {
        return Show::find()->venueId($this->id);
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
        $data = [
            'venueRef' => $this->venueRef,
            'name' => $this->name,
        ];

        if ($isNew) {
            $data['id'] = $this->id;
            \Craft::$app->db->createCommand()
                ->insert(self::TABLE, $data)
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update(self::TABLE, $data, ['id' => $this->id])
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
