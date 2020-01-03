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

use devkokov\ticketsolve\elements\Venue;
use devkokov\ticketsolve\Ticketsolve;

use Craft;
use craft\base\Component;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class TicketsolveService extends Component
{
    // Public Methods
    // =========================================================================

    public function syncFromXML()
    {
        echo "Starting sync from XML feed ... \n";

        $url = Ticketsolve::getInstance()->getSettings()->xmlUrl;

        if (empty($url)) {
            echo "No XML URL specified. Exiting. \n";
            return;
        }

        // store all venue Refs that we've processed from the XML
        $venueRefs = [];

        $venues = simplexml_load_file($url);

        foreach ($venues->venue as $i => $xmlVenue) {
            $venue = new Venue();
            $venue->venueRef = isset($xmlVenue['id']) ? (integer) $xmlVenue['id'] : null;
            $venue->name = trim($xmlVenue->name);

            if (!$venue->venueRef) {
                echo "Missing venueRef at iteration $i \n";
                continue;
            }

            $oldVenue = Venue::find()->venueRef($venue->venueRef)->one();

            if ($oldVenue) {
                if ($venue->isDifferent($oldVenue)) {
                    // update existing venue
                    $venue->syncToElement($oldVenue);
                    echo "Updating Venue Ref $venue->venueRef ... \n";
                    Craft::$app->elements->saveElement($oldVenue);
                } else {
                    echo "Skipping Venue Ref $venue->venueRef - no change detected ... \n";
                }
            } else {
                // create new venue
                echo "Creating Venue Ref $venue->venueRef ... \n";
                Craft::$app->elements->saveElement($venue, false);
            }

            // store venue ref so we can sort out deletions later on
            $venueRefs[] = $venue->venueRef;
        }

        $deleteVenues = Venue::find()->excludeVenueRefs($venueRefs)->all();
        /** @var Venue $deleteVenue */
        foreach ($deleteVenues as $deleteVenue) {
            echo "Deleting Venue Ref $deleteVenue->venueRef ... \n";
            Craft::$app->elements->deleteElement($deleteVenue, true);
        }

        echo "Sync finished. \n";
    }
}
