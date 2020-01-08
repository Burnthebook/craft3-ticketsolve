<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\ticketsolve\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use devkokov\ticketsolve\elements\Event;

class EventQuery extends ElementQuery
{
    public $eventRef;
    public $name;
    public $eventStatus;
    public $showId;
    public $excludeEventRefs = [];

    public function eventRef($value)
    {
        $this->eventRef = $value;

        return $this;
    }

    public function name($value)
    {
        $this->name = $value;

        return $this;
    }

    public function eventStatus($value)
    {
        $this->eventStatus = $value;

        return $this;
    }

    public function showId($value)
    {
        $this->showId = $value;

        return $this;
    }

    public function excludeEventRefs(array $eventRefs)
    {
        $this->excludeEventRefs = $eventRefs;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable(Event::TABLE_STD);

        $this->query->select([
            Event::TABLE_STD . '.showId',
            Event::TABLE_STD . '.eventRef',
            Event::TABLE_STD . '.name',
            Event::TABLE_STD . '.dateTimeString',
            Event::TABLE_STD . '.openingTimeString',
            Event::TABLE_STD . '.onSaleTimeString',
            Event::TABLE_STD . '.duration',
            Event::TABLE_STD . '.available',
            Event::TABLE_STD . '.capacity',
            Event::TABLE_STD . '.venueLayout',
            Event::TABLE_STD . '.comment',
            Event::TABLE_STD . '.url',
            Event::TABLE_STD . '.status',
            Event::TABLE_STD . '.fee',
            Event::TABLE_STD . '.feeCurrency',
            Event::TABLE_STD . '.maximumTickets',
            Event::TABLE_STD . '.pricesJson',
        ]);

        if ($this->eventRef) {
            $this->subQuery->andWhere(Db::parseParam(Event::TABLE_STD . '.eventRef', $this->eventRef));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam(Event::TABLE_STD . '.name', $this->name));
        }

        if ($this->eventStatus) {
            $this->subQuery->andWhere(Db::parseParam(Event::TABLE_STD . '.eventStatus', $this->eventStatus));
        }

        if ($this->showId) {
            $this->subQuery->andWhere(Db::parseParam(Event::TABLE_STD . '.showId', $this->showId));
        }

        if ($this->excludeEventRefs) {
            $this->subQuery->andWhere(['not in', Event::TABLE_STD . '.eventRef', $this->excludeEventRefs]);
        }

        return parent::beforePrepare();
    }
}
