<?php
namespace devkokov\ticketsolve\twig;

use Craft;
use devkokov\ticketsolve\elements\db\ShowQuery;
use devkokov\ticketsolve\elements\db\VenueQuery;
use devkokov\ticketsolve\elements\Show;
use devkokov\ticketsolve\elements\Venue;
use yii\base\Behavior;

class CraftVariableBehavior extends Behavior
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
}
