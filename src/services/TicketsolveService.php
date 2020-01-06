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

use devkokov\ticketsolve\elements\Show;
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

        $venueRefs = [];
        $showRefs = [];

        $venues = simplexml_load_file($url);

        foreach ($venues->venue as $i => $xmlVenue) {
            // SYNC VENUES

            $venue = $this->getVenueFromXML($xmlVenue);

            if (!$venue->venueRef) {
                echo "Missing venueRef at iteration $i \n";
                continue;
            }

            $oldVenue = Venue::find()->venueRef($venue->venueRef)->one();

            if ($oldVenue) {
                if ($venue->isDifferent($oldVenue)) {
                    // update existing venue
                    $venue->syncToElement($oldVenue);
                    $venue = $oldVenue;
                    echo "Updating Venue Ref $venue->venueRef ... \n";
                    Craft::$app->elements->saveElement($venue, false);
                } else {
                    $venue = $oldVenue;
                    echo "Skipping Venue Ref $venue->venueRef - no change detected ... \n";
                }
            } else {
                // create new venue
                echo "Creating Venue Ref $venue->venueRef ... \n";
                Craft::$app->elements->saveElement($venue, false);
            }

            // store venue ref so we can sort out deletions later on
            $venueRefs[] = $venue->venueRef;

            // SYNC SHOWS

            foreach ($xmlVenue->shows->show as $j => $xmlShow) {
                $show = $this->getShowFromXML($xmlShow, $venue);

                if (!$show->showRef) {
                    echo "Missing showRef at iteration $j \n";
                    continue;
                }

                $oldShow = Show::find()->showRef($show->showRef)->one();

                if ($oldShow) {
                    if ($show->isDifferent($oldShow)) {
                        // update existing show
                        $show->syncToElement($oldShow);
                        echo "Updating Show Ref $show->showRef ... \n";
                        Craft::$app->elements->saveElement($oldShow, false);
                    } else {
                        echo "Skipping Show Ref $show->showRef - no change detected ... \n";
                    }
                } else {
                    // create new show
                    echo "Creating Show Ref $show->showRef ... \n";
                    Craft::$app->elements->saveElement($show, false);
                }

                // store show ref so we can sort out deletions later on
                $showRefs[] = $show->showRef;
            }
        }

        $deleteVenues = Venue::find()->excludeVenueRefs($venueRefs)->all();
        /** @var Venue $deleteVenue */
        foreach ($deleteVenues as $deleteVenue) {
            echo "Deleting Venue Ref $deleteVenue->venueRef ... \n";
            Craft::$app->elements->deleteElement($deleteVenue, true);
        }

        $deleteShows = Show::find()->excludeShowRefs($showRefs)->all();
        /** @var Show $deleteShow */
        foreach ($deleteShows as $deleteShow) {
            echo "Deleting Show Ref $deleteShow->showRef ... \n";
            Craft::$app->elements->deleteElement($deleteShow, true);
        }

        echo "Sync finished. \n";
    }

    private function getVenueFromXML(\SimpleXMLElement $xml): Venue
    {
        $venue = new Venue();
        $venue->venueRef = isset($xml['id']) ? (integer) $xml['id'] : null;
        $venue->name = trim($xml->name);

        return $venue;
    }

    private function getShowFromXML(\SimpleXMLElement $xml, Venue $venue): Show
    {
        $show = new Show();
        $show->venueId = $venue->id;
        $show->showRef = isset($xml['id']) ? (integer) $xml['id'] : null;
        $show->name = trim($xml->name);
        $show->description = trim($xml->description);
        $show->eventCategory = trim($xml->event_category);
        $show->productionCompanyName = trim($xml->production_company_name);
        $show->priority = (integer) trim($xml->priority);
        $show->url = trim($xml->url);
        $show->version = (integer) trim($xml->version);
        $show->images = [];

        foreach ($xml->images->image as $imageXml) {
            $image = [];
            foreach ($imageXml->url as $url) {
                $size = trim($url->attributes()['size']);
                $image[$size] = trim($url);
            }
            $show->images[] = $image;
        }

        return $show;
    }
}
