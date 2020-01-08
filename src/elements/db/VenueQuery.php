<?php

namespace devkokov\ticketsolve\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use devkokov\ticketsolve\elements\Venue;

class VenueQuery extends ElementQuery
{
    public $venueRef;
    public $name;
    public $excludeVenueRefs = [];

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

    public function excludeVenueRefs(array $venueRefs)
    {
        $this->excludeVenueRefs = $venueRefs;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable(Venue::TABLE_STD);

        $this->query->select([
            Venue::TABLE_STD . '.venueRef',
            Venue::TABLE_STD . '.name',
        ]);

        if ($this->venueRef) {
            $this->subQuery->andWhere(Db::parseParam(Venue::TABLE_STD . '.venueRef', $this->venueRef));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam(Venue::TABLE_STD . '.name', $this->name));
        }

        if ($this->excludeVenueRefs) {
            $this->subQuery->andWhere(['not in', Venue::TABLE_STD . '.venueRef', $this->excludeVenueRefs]);
        }

        return parent::beforePrepare();
    }
}
