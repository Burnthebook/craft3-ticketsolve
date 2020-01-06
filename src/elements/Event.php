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
use craft\elements\db\ElementQueryInterface;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use devkokov\ticketsolve\elements\db\EventQuery;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class Event extends AbstractComparableElement
{
    // Public Properties
    // =========================================================================

    public $showId;
    public $eventRef;
    public $name;
    public $dateTime;
    public $openingTime;
    public $onSaleTime;
    public $duration;
    public $available;
    public $capacity;
    public $venueLayout;
    public $comment;
    public $url;
    public $status;
    public $fee;
    public $feeCurrency;
    public $maximumTickets;
    public $prices = [];

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('ticketsolve', 'Event');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('ticketsolve', 'Events');
    }

    /**
     * @inheritdoc
     * @return EventQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new EventQuery(static::class);
    }

    protected static function defineSortOptions(): array
    {
        return [
            'eventRef' => \Craft::t('ticketsolve', 'Event ID'),
            'name' => \Craft::t('ticketsolve', 'Name'),
            'dateTime' => \Craft::t('ticketsolve', 'Date/Time'),
            'status' => \Craft::t('ticketsolve', 'Status'),
            'available' => \Craft::t('ticketsolve', 'Available'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'eventRef' => \Craft::t('ticketsolve', 'Event ID'),
            'name' => \Craft::t('ticketsolve', 'Name'),
            'dateTime' => \Craft::t('ticketsolve', 'Date/Time'),
            'status' => \Craft::t('ticketsolve', 'Status'),
            'available' => \Craft::t('ticketsolve', 'Available'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['eventRef', 'name'];
    }

    protected static function defineComparableAttributes(): array
    {
        return [
            'showId',
            'eventRef',
            'name',
            'dateTime',
            'openingTime',
            'onSaleTime',
            'duration',
            'available',
            'capacity',
            'venueLayout',
            'comment',
            'url',
            'status',
            'fee',
            'feeCurrency',
            'maximumTickets',
            'prices',
        ];
    }

    // Public Methods
    // =========================================================================

    /**
     * @param string|null $value JSON encoded array of prices
     */
    public function setPricesJson($value)
    {
        $this->prices = (array)\GuzzleHttp\json_decode($value, true);
    }

    /**
     * @param string|null $value
     * @throws \Exception
     */
    public function setDateTimeString($value)
    {
        $this->dateTime = DateTimeHelper::toDateTime($value);
    }

    /**
     * @param string|null $value
     * @throws \Exception
     */
    public function setOpeningTimeString($value)
    {
        $this->openingTime = DateTimeHelper::toDateTime($value);
    }

    /**
     * @param string|null $value
     * @throws \Exception
     */
    public function setOnSaleTimeString($value)
    {
        $this->onSaleTime = DateTimeHelper::toDateTime($value);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['name', 'eventRef'], 'required'],
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
        $data = [
            'showId' => $this->showId,
            'eventRef' => $this->eventRef,
            'name' => $this->name,
            'dateTimeString' => Db::prepareValueForDb($this->dateTime),
            'openingTimeString' => Db::prepareValueForDb($this->openingTime),
            'onSaleTimeString' => Db::prepareValueForDb($this->onSaleTime),
            'duration' => $this->duration,
            'available' => $this->available,
            'capacity' => $this->capacity,
            'venueLayout' => $this->venueLayout,
            'comment' => $this->comment,
            'url' => $this->url,
            'status' => $this->status,
            'fee' => $this->fee,
            'feeCurrency' => $this->feeCurrency,
            'maximumTickets' => $this->maximumTickets,
            'pricesJson' => \GuzzleHttp\json_encode($this->prices),
        ];

        if ($isNew) {
            $data['id'] = $this->id;
            \Craft::$app->db->createCommand()
                ->insert('{{%ticketsolve_events}}', $data)
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update('{{%ticketsolve_events}}', $data, ['id' => $this->id])
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
