<?php

namespace devkokov\ticketsolve\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

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
        $this->joinElementTable('ticketsolve_events');

        $this->query->select([
            'ticketsolve_events.showId',
            'ticketsolve_events.eventRef',
            'ticketsolve_events.name',
            'ticketsolve_events.dateTimeString',
            'ticketsolve_events.openingTimeString',
            'ticketsolve_events.onSaleTimeString',
            'ticketsolve_events.duration',
            'ticketsolve_events.available',
            'ticketsolve_events.capacity',
            'ticketsolve_events.venueLayout',
            'ticketsolve_events.comment',
            'ticketsolve_events.url',
            'ticketsolve_events.status',
            'ticketsolve_events.fee',
            'ticketsolve_events.feeCurrency',
            'ticketsolve_events.maximumTickets',
            'ticketsolve_events.pricesJson',
        ]);

        if ($this->eventRef) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_events.eventRef', $this->eventRef));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_events.name', $this->name));
        }

        if ($this->eventStatus) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_events.eventStatus', $this->eventStatus));
        }

        if ($this->showId) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_events.showId', $this->showId));
        }

        if ($this->excludeEventRefs) {
            $this->subQuery->andWhere(['not in', 'ticketsolve_events.eventRef', $this->excludeEventRefs]);
        }

        return parent::beforePrepare();
    }
}
