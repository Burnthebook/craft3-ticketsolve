<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\ticketsolve\elements\db;

use burnthebook\ticketsolve\elements\Event;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use burnthebook\ticketsolve\elements\Show;
use burnthebook\ticketsolve\records\TagIndexRecord;
use burnthebook\ticketsolve\records\TagRecord;

class ShowQuery extends ElementQuery
{
    public $showRef;
    public $name;
    public $eventCategory;
    public $productionCompanyName;
    public $venueId;
    public $excludeShowRefs = [];
    public $tags = [];

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

    public function venueId($value)
    {
        $this->venueId = $value;

        return $this;
    }

    public function excludeShowRefs(array $showRefs)
    {
        $this->excludeShowRefs = $showRefs;

        return $this;
    }

    public function tags(array $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable(Show::TABLE_STD);

        $this->query->select([
            Show::TABLE_STD . '.venueId',
            Show::TABLE_STD . '.showRef',
            Show::TABLE_STD . '.name',
            Show::TABLE_STD . '.description',
            Show::TABLE_STD . '.eventCategory',
            Show::TABLE_STD . '.productionCompanyName',
            Show::TABLE_STD . '.priority',
            Show::TABLE_STD . '.url',
            Show::TABLE_STD . '.version',
            Show::TABLE_STD . '.imagesJson',
            'min(' . Event::TABLE_STD . '.dateTimeString) as nextEventDate',
            Event::TABLE_STD . '.id as nextEventId'
        ]);

        $this->query->leftJoin(
            Event::TABLE . ' ' . Event::TABLE_STD,
            Event::TABLE_STD . '.showId = ' . Show::TABLE_STD . '.id'
        );

        $this->groupBy = [Show::TABLE_STD . '.id'];

        if ($this->showRef) {
            $this->subQuery->andWhere(Db::parseParam(Show::TABLE_STD . '.showRef', $this->showRef));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam(Show::TABLE_STD . '.name', $this->name));
        }

        if ($this->eventCategory) {
            $this->subQuery->andWhere(Db::parseParam(Show::TABLE_STD . '.eventCategory', $this->eventCategory));
        }

        if ($this->productionCompanyName) {
            $this->subQuery->andWhere(
                Db::parseParam(Show::TABLE_STD . '.productionCompanyName', $this->productionCompanyName)
            );
        }

        if ($this->venueId) {
            $this->subQuery->andWhere(Db::parseParam(Show::TABLE_STD . '.venueId', $this->venueId));
        }

        if ($this->excludeShowRefs) {
            $this->subQuery->andWhere(['not in', Show::TABLE_STD . '.showRef', $this->excludeShowRefs]);
        }

        if ($this->tags) {
            $tags = $this->tags;

            // convert to an array of tag names, in case we have an array of tag models
            array_walk($tags, function (&$value) {
                $value = (string) $value;
            });

            $this->subQuery->andWhere([
                'in',
                Show::TABLE_STD . '.id',
                (new Query())
                    ->select(TagIndexRecord::TABLE . '.showId')
                    ->distinct()
                    ->from(TagIndexRecord::TABLE)
                    ->innerJoin(
                        TagRecord::TABLE,
                        TagIndexRecord::TABLE . '.tagId = ' . TagRecord::TABLE . '.id'
                    )
                    ->where(['in', TagRecord::TABLE . '.name', $tags])
            ]);
        }

        return parent::beforePrepare();
    }

    protected function afterPrepare(): bool
    {
        $removeSubqueryOrderBy = ['nextEventDate', 'nextEventId'];

        if (is_array($this->subQuery->orderBy)) {
            foreach ($removeSubqueryOrderBy as $column) {
                unset($this->subQuery->orderBy[$column]);
            }
        }

        return parent::afterPrepare();
    }
}
