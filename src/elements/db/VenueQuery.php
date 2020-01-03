<?php

namespace devkokov\ticketsolve\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class VenueQuery extends ElementQuery
{
    public $venueId;
    public $name;

    public function venueId($value)
    {
        $this->venueId = $value;

        return $this;
    }

    public function name($value)
    {
        $this->name = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('ticketsolve_venues');

        $this->query->select([
            'ticketsolve_venues.venueId',
            'ticketsolve_venues.name',
        ]);

        if ($this->venueId) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_venues.venueId', $this->venueId));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_venues.name', $this->name));
        }

        return parent::beforePrepare();
    }
}
