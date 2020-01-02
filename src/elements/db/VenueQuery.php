<?php

namespace devkokov\ticketsolve\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class VenueQuery extends ElementQuery
{
    public $name;

    public function name($value)
    {
        $this->name = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('ticketsolve_venues');

        $this->query->select([
            'ticketsolve_venues.name',
        ]);

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_venues.name', $this->name));
        }

        return parent::beforePrepare();
    }
}
