<?php
namespace devkokov\ticketsolve;

use Craft;
use devkokov\ticketsolve\elements\db\VenueQuery;
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
}
