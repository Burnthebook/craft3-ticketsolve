<?php

namespace devkokov\ticketsolve\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class ShowQuery extends ElementQuery
{
    public $showRef;
    public $name;
    public $eventCategory;
    public $productionCompanyName;
    public $excludeShowRefs = [];

    public function showRef($value)
    {
        $this->showRef = $value;

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

    public function excludeShowRefs(array $showRefs)
    {
        $this->excludeShowRefs = $showRefs;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('ticketsolve_shows');

        $this->query->select([
            'ticketsolve_shows.venueId',
            'ticketsolve_shows.showRef',
            'ticketsolve_shows.name',
            'ticketsolve_shows.description',
            'ticketsolve_shows.eventCategory',
            'ticketsolve_shows.productionCompanyName',
            'ticketsolve_shows.priority',
            'ticketsolve_shows.url',
            'ticketsolve_shows.version',
            'ticketsolve_shows.imagesJson',
        ]);

        if ($this->showRef) {
            $this->subQuery->andWhere(Db::parseParam('ticketsolve_shows.showRef', $this->showRef));
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

        if ($this->excludeShowRefs) {
            $this->subQuery->andWhere(['not in', 'ticketsolve_shows.showRef', $this->excludeShowRefs]);
        }

        return parent::beforePrepare();
    }
}
