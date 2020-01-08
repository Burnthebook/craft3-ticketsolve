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

use craft\db\Table;
use devkokov\ticketsolve\elements\Event;
use devkokov\ticketsolve\elements\Show;
use devkokov\ticketsolve\elements\Venue;
use devkokov\ticketsolve\jobs\SyncJob;
use devkokov\ticketsolve\Ticketsolve;

use Craft;
use craft\base\Component;
use yii\db\Query;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class SyncService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * @param callable $setProgress A function that sets the progress of the sync.
     * Accepts a number between 0 and 1 as the first parameter, and an optional label as the second
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function syncFromXML(callable $setProgress = null)
    {
        echo "Starting sync from XML feed ... \n";

        $url = Ticketsolve::getInstance()->getSettings()->xmlUrl;

        if (empty($url)) {
            echo "No XML URL specified. Exiting. \n";
            return;
        }

        $venueRefs = [];
        $showRefs = [];
        $eventRefs = [];
        $tagNames = [];

        $venues = simplexml_load_file($url);

        $entityCount = $this->getEntityCountFromXML($venues);
        $entityCounter = 0;

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

            // update sync progress
            $entityCounter++;
            if (is_callable($setProgress)) {
                $setProgress($entityCounter / $entityCount, "Processed $entityCounter out of $entityCount.");
            }

            // SYNC SHOWS

            foreach ($xmlVenue->shows->show as $j => $xmlShow) {
                $show = $this->getShowFromXML($xmlShow, $venue);

                if (!$show->showRef) {
                    echo "Missing showRef at iteration $j \n";
                    continue;
                }

                $tags = $this->getTagsFromXML($xmlShow);

                /** @var Show $oldShow */
                $oldShow = Show::find()->showRef($show->showRef)->one();

                if ($oldShow) {
                    if ($show->isDifferent($oldShow)) {
                        // update existing show
                        $show->syncToElement($oldShow);
                        $show = $oldShow;
                        echo "Updating Show Ref $show->showRef ... \n";
                        Craft::$app->elements->saveElement($show, false);
                    } else {
                        $show = $oldShow;
                        echo "Skipping Show Ref $show->showRef - no change detected ... \n";
                    }

                    $oldTags = Ticketsolve::$plugin->tagsService->getTagsFromShow($show);

                    echo "Added " . $this->tagsService()->tagShow($show, array_diff($tags, $oldTags)) . " tags. \n";
                    echo "Removed " . $this->tagsService()->untagShow($show, array_diff($oldTags, $tags)) . " tags. \n";
                } else {
                    // create new show
                    echo "Creating Show Ref $show->showRef ... \n";
                    Craft::$app->elements->saveElement($show, false);

                    echo "Added " . Ticketsolve::$plugin->tagsService->tagShow($show, $tags) . " tags. \n";
                }

                // store show ref so we can sort out deletions later on
                $showRefs[] = $show->showRef;

                // store tag names so we can sort out deletions later on
                $tagNames = array_unique(array_merge($tagNames, $tags));

                // update sync progress
                $entityCounter++;
                if (is_callable($setProgress)) {
                    $setProgress($entityCounter / $entityCount, "Processed $entityCounter out of $entityCount.");
                }

                // SYNC EVENTS

                foreach ($xmlShow->events->event as $k => $xmlEvent) {
                    $event = $this->getEventFromXML($xmlEvent, $show);

                    if (!$event->eventRef) {
                        echo "Missing eventRef at iteration $k \n";
                        continue;
                    }

                    $oldEvent = Event::find()->eventRef($event->eventRef)->one();

                    if ($oldEvent) {
                        if ($event->isDifferent($oldEvent)) {
                            // update existing event
                            $event->syncToElement($oldEvent);
                            $event = $oldEvent;
                            echo "Updating Event Ref $event->eventRef ... \n";
                            Craft::$app->elements->saveElement($oldEvent, false);
                        } else {
                            $event = $oldEvent;
                            echo "Skipping Event Ref $event->eventRef - no change detected ... \n";
                        }
                    } else {
                        // create new event
                        echo "Creating Event Ref $event->eventRef ... \n";
                        Craft::$app->elements->saveElement($event, false);
                    }

                    // store event ref so we can sort out deletions later on
                    $eventRefs[] = $event->eventRef;

                    // update sync progress
                    $entityCounter++;
                    if (is_callable($setProgress)) {
                        $setProgress($entityCounter / $entityCount, "Processed $entityCounter out of $entityCount.");
                    }
                }
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

        $deleteEvents = Event::find()->excludeEventRefs($eventRefs)->all();
        /** @var Event $deleteEvent */
        foreach ($deleteEvents as $deleteEvent) {
            echo "Deleting Event Ref $deleteEvent->eventRef ... \n";
            Craft::$app->elements->deleteElement($deleteEvent, true);
        }

        echo "Deleted " . $this->tagsService()->deleteAllTagsExcept($tagNames) . " tags. \n";

        echo "Sync finished. \n";
    }

    /**
     * @param bool $includeManualSyncJobs
     * @return array
     */
    public function getQueuedSyncJobs($includeManualSyncJobs = false)
    {
        $query = (new Query())
            ->from(Table::QUEUE)
            ->where(['description' => SyncJob::getDefaultDescription()]);

        if ($includeManualSyncJobs) {
            $query->orWhere(['description' => SyncJob::getDefaultManualDescription()]);
        }

        return $query->all();
    }

    /**
     * @param bool $includingManualSyncJobs
     */
    public function removeQueuedSyncJobs($includingManualSyncJobs = false)
    {
        foreach ($this->getQueuedSyncJobs($includingManualSyncJobs) as $job) {
            Craft::$app->queue->release($job['id']);
        }
    }

    /**
     * @param int $delay in seconds
     * @param bool $manual If the sync is triggered manually by a user
     */
    public function queueSyncJob($delay = 0, $manual = false)
    {
        $job = new SyncJob();
        $job->manual = $manual;

        Craft::$app->queue->delay($delay)->push($job);
    }

    // Private Methods
    // =========================================================================

    private function getVenueFromXML(\SimpleXMLElement $xml): Venue
    {
        $venue = new Venue();
        $venue->venueRef = isset($xml['id']) ? (integer)$xml['id'] : null;
        $venue->name = trim($xml->name);

        return $venue;
    }

    private function getShowFromXML(\SimpleXMLElement $xml, Venue $venue): Show
    {
        $show = new Show();
        $show->venueId = $venue->id;
        $show->showRef = isset($xml['id']) ? (integer)$xml['id'] : null;
        $show->name = trim($xml->name);
        $show->description = trim($xml->description);
        $show->eventCategory = trim($xml->event_category);
        $show->productionCompanyName = trim($xml->production_company_name);
        $show->priority = (integer)trim($xml->priority);
        $show->url = trim($xml->url);
        $show->version = (integer)trim($xml->version);
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

    private function getTagsFromXML(\SimpleXMLElement $xml): array
    {
        $tags = [];

        foreach ($xml->tags->tag as $tag) {
            $tags[] = trim($tag);
        }

        return $tags;
    }

    private function getEventFromXML(\SimpleXMLElement $xml, Show $show): Event
    {
        // additional event information is stored in a separate XML feed. fetch it!
        $xml2 = simplexml_load_file($xml->feed->url);

        $event = new Event();
        $event->showId = $show->id;
        $event->eventRef = isset($xml['id']) ? (integer)$xml['id'] : null;
        $event->name = trim($xml->name);
        $event->dateTime = $this->getDateTimeFromXML($xml2, 'date_time');
        $event->openingTime = $this->getDateTimeFromXML($xml2, 'opening_time');
        $event->onSaleTime = $this->getDateTimeFromXML($xml2, 'onsale_time');
        $event->duration = (integer)trim($xml2->duration);
        $event->available = (integer)trim($xml2->available);
        $event->capacity = (integer)trim($xml2->capacity);
        $event->venueLayout = trim($xml->venue_layout);
        $event->comment = trim($xml2->comment);
        $event->url = trim($xml2->url);
        $event->status = trim($xml2->status);
        $event->fee = (float)trim($xml2->transaction->fee);
        $event->feeCurrency = trim($xml2->transaction->fee->attributes()['currency']);
        $event->maximumTickets = (integer)trim($xml2->transaction->maximum_tickets);
        $event->prices = [];

        foreach ($xml2->prices->price as $price) {
            $event->prices[] = [
                'type' => trim($price->type),
                'facePrice' => [
                    'value' => (float)trim($price->face_price),
                    'currency' => trim($price->face_price->attributes()['currency'])
                ],
                'sellingPrice' => [
                    'value' => (float)trim($price->selling_price),
                    'currency' => trim($price->selling_price->attributes()['currency'])
                ]
            ];
        }

        return $event;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param string $nodeName
     * @return \DateTime|null
     * @throws \Exception
     */
    private function getDateTimeFromXML(\SimpleXMLElement $xml, string $nodeName)
    {
        if (empty($xml->$nodeName)) {
            return null;
        }

        return new \DateTime(trim($xml->$nodeName), new \DateTimeZone($xml->$nodeName->attributes()['zone']));
    }

    private function getEntityCountFromXML(\SimpleXMLElement $xml)
    {
        $count = $xml->count();
        foreach ($xml->venue as $venue) {
            $count += count($venue->shows->show);
            foreach ($venue->shows->show as $show) {
                $count += count($show->events->event);
            }
        }
        return $count;
    }

    private function tagsService(): TagsService
    {
        return Ticketsolve::getInstance()->tagsService;
    }
}
