<?php

namespace devkokov\ticketsolve\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class ShowQuery extends ElementQuery
{
    public $showId;
    public $venueId;
    public $name;
    public $eventCategory;
    public $productionCompanyName;

    public function showId($value)
    {
        $this->showId = $value;

        return $this;
    }

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

    public function eventCategory($value)
    {
        $this->eventCategory = $value;

        return $this;
    }

    public function productionCompanyName($value)
    {
        $this->productionCompanyName = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('ticketsolve_shows');

        $this->query->select([
            'ticketsolve_shows.showId',
            'ticketsolve_shows.venueId',
            'ticketsolve_shows.name',
            'ticketsolve_shows.eventCategory',
            'ticketsolve_shows.productionCompanyName',
        ]);

        if ($this->showId) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_shows.showId', $this->showId));
        }

        if ($this->venueId) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_shows.venueId', $this->venueId));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_shows.name', $this->name));
        }

        if ($this->eventCategory) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_shows.eventCategory', $this->eventCategory));
        }

        if ($this->productionCompanyName) {
            $this->subQuery->andWhere(
                Db::parseParam('ticketsolve_shows.productionCompanyName', $this->productionCompanyName)
            );
        }

        return parent::beforePrepare();
    }
}
