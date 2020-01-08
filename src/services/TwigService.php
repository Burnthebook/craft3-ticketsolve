<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\ticketsolve\services;

use Craft;
use craft\base\Component;
use devkokov\ticketsolve\elements\db\EventQuery;
use devkokov\ticketsolve\elements\db\ShowQuery;
use devkokov\ticketsolve\elements\db\VenueQuery;
use devkokov\ticketsolve\elements\Event;
use devkokov\ticketsolve\elements\Show;
use devkokov\ticketsolve\elements\Venue;

class TwigService extends Component
{
    public function venues($criteria = null): VenueQuery
    {
        $query = Venue::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }

    public function shows($criteria = null): ShowQuery
    {
        $query = Show::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }

    public function events($criteria = null): EventQuery
    {
        $query = Event::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }
}
