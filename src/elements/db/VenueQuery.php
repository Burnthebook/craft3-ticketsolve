<?php

namespace devkokov\ticketsolve\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class VenueQuery extends ElementQuery
{
    public $venueRef;
    public $name;

    public function venueRef($value)
    {
        $this->venueRef = $value;

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
            'ticketsolve_venues.venueRef',
            'ticketsolve_venues.name',
        ]);

        if ($this->venueRef) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_venues.venueRef', $this->venueRef));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_venues.name', $this->name));
        }

        return parent::beforePrepare();
    }
}
