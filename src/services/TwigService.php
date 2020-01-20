<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\ticketsolve\services;

use Craft;
use craft\base\Component;
use burnthebook\ticketsolve\elements\db\EventQuery;
use burnthebook\ticketsolve\elements\db\ShowQuery;
use burnthebook\ticketsolve\elements\db\VenueQuery;
use burnthebook\ticketsolve\elements\Event;
use burnthebook\ticketsolve\elements\Show;
use burnthebook\ticketsolve\elements\Venue;

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
