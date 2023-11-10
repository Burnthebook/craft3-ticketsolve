<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\ticketsolve\sync;

use Craft;
use craft\services\Path;
use SimpleXMLElement;
use DateTime;
use DateTimeZone;
use Throwable;
use burnthebook\ticketsolve\services\TagsService;
use burnthebook\ticketsolve\elements\Event;
use burnthebook\ticketsolve\elements\Show;
use burnthebook\ticketsolve\elements\Venue;
use burnthebook\ticketsolve\Ticketsolve;
use yii\base\UnknownClassException;

class XMLFeedSync
{
    // Public Properties
    // =========================================================================

    /** @var string */
    public $feedUrl;

    /** @var callable */
    public $setProgressFunction;

    // Private Properties
    // =========================================================================

    /**
     * @var int
     */
    private $entityCount = 0;

    /**
     * @var int
     */
    private $entityCounter = 0;

    /**
     * @var array
     */
    private $venueRefs = [];

    /**
     * @var array
     */
    private $showRefs = [];

    /**
     * @var array
     */
    private $eventRefs = [];

    /**
     * @var array
     */
    private $tagNames = [];

    /**
     * @var string
     */
    private $cachePath = '';

    /**
     * @var int
     */
    private $showAPICallLimit = 250;

    /**
     * @var float|int
     */
    private $cacheFileExpiry = (60 * 60) * 24; // 24 hours

    /**
     * @var int
     */
    private $showAPICallCount = 0;

    /**
     * @var string
     */
    private $activeImportFileName = '_run_active';

    /**
     * @var bool
     */
    private $showAPICallLimitExceeded = false;

    /** @var TagsService */
    private $tagsService;

    // Public Methods
    // =========================================================================
    /**
     * @param callable|null $setProgressFunction A function that sets the progress of the sync.
     * Accepts a number between 0 and 1 as the first parameter, and an optional label as the second
     */
    public function __construct(callable $setProgressFunction = null)
    {
        $this->setProgressFunction = $setProgressFunction;
        $this->feedUrl = Ticketsolve::getInstance()->getSettings()->xmlUrl;
        $this->tagsService = Ticketsolve::getInstance()->tagsService;
    }

    /**
     * @throws Throwable
     */
    public function start()
    {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        set_time_limit(600);

        echo "Starting sync from XML feed ... \n";

        if (empty($this->feedUrl)) {
            echo "No XML URL specified. Exiting. \n";
            return;
        }

        $this->showAPICallCount = 0;

        $cachePath = Craft::$app->path->getCachePath() . '/ticketsolve/';

        if (!is_dir($cachePath)) {
            mkdir($cachePath);
        }

        $this->cachePath = $cachePath;

        if (!file_exists($this->cachePath . $this->activeImportFileName)) {
            file_put_contents($this->cachePath . $this->activeImportFileName, json_encode(['eventRefs' => [], 'showRefs' => [], 'venueRefs' => [], 'tagNames' => []]));
        } else {
            $json = json_decode(file_get_contents($this->cachePath . $this->activeImportFileName), true);
            $this->eventRefs = $json['eventRefs'];
            $this->showRefs = $json['showRefs'];
            $this->venueRefs = $json['venueRefs'];
            $this->tagNames = $json['tagNames'];
        }

        $this->deleteExpiredCacheFiles($this->cacheFileExpiry);

        $xml = simplexml_load_file($this->feedUrl);

        $this->entityCount = $this->getEntityCount($xml);

        echo "entityCount {$this->entityCount} \n";

        $this->syncVenues($xml);

        file_put_contents($this->cachePath . $this->activeImportFileName, json_encode([
            'eventRefs' => $this->eventRefs,
            'showRefs' => $this->showRefs,
            'venueRefs' => $this->venueRefs,
            'tagNames' => $this->tagNames,
        ]));

        echo "entityCounter {$this->entityCounter} \n";

        if (!$this->showAPICallLimitExceeded && $this->entityCounter > 0 && $this->showAPICallCount > 0) {
            $this->processDeletions();
            $this->deleteExpiredCacheFiles();
        }

        echo "Sync finished. \n";
    }

    // Private Methods
    // =========================================================================

    private function deleteExpiredCacheFiles($hours = 0)
    {
        if ($hours == 0) {
            echo "Removing cache\n";
            foreach (glob($this->cachePath . '*') as $cacheFile) {
                @unlink($cacheFile);
            }
            rmdir($this->cachePath);
        } else {
            foreach (glob($this->cachePath . 'file_*') as $cacheFile) {
                $mtime = filemtime($cacheFile);
                $age = time() - $mtime;
                if ($age > $hours) {
                    @unlink($cacheFile);
                }
            }

            $mtime = filemtime($this->cachePath . $this->activeImportFileName);
            $age = time() - $mtime;
            if ($age > $hours) {
                @unlink($this->cachePath . $this->activeImportFileName);
            }
        }
    }

    private function generateFileHash($url)
    {
        return 'file_' . sha1($url);
    }

    /**
     * Gets the total number of Venues + Shows + Events that are present in the feed
     * @param SimpleXMLElement $xml
     * @return int
     */
    private function getEntityCount(SimpleXMLElement $xml)
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

    /**
     * @param SimpleXMLElement $xml
     * @throws Throwable
     */
    private function syncVenues(SimpleXMLElement $xml)
    {
        foreach ($xml->venue as $i => $xmlVenue) {
            if ($this->showAPICallLimitExceeded) {
                echo "showAPICallLimitExceeded in " . __METHOD__ . "\n";
                break;
            }

            $venue = $this->getVenueFromXML($xmlVenue);

            if (!$venue->venueRef) {
                echo "Missing venueRef at iteration $i \n";
                continue;
            }

            /** @var Venue $oldVenue */
            $oldVenue = Venue::find()->venueRef($venue->venueRef)->one();

            if ($oldVenue) {
                if ($venue->isDifferent($oldVenue)) {
                    echo "Updating Venue Ref $venue->venueRef ... \n";
                    $venue->syncToElement($oldVenue);
                    $venue = $oldVenue;
                    Craft::$app->elements->saveElement($venue, false);
                } else {
                    echo "Skipping Venue Ref $venue->venueRef - no change detected ... \n";
                    $venue = $oldVenue;
                }
            } else {
                echo "Creating Venue Ref $venue->venueRef ... \n";
                Craft::$app->elements->saveElement($venue, false);
            }

            // store venue ref so we can sort out deletions later on
            $this->venueRefs[$venue->venueRef] = $venue->venueRef;

            $this->updateSyncProgress();

            $this->syncShows($xmlVenue, $venue);
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param Venue $venue
     * @throws Throwable
     */
    private function syncShows(SimpleXMLElement $xml, Venue $venue)
    {
        foreach ($xml->shows->show as $i => $xmlShow) {
            if ($this->showAPICallLimitExceeded) {
                echo "showAPICallLimitExceeded in " . __METHOD__ . "\n";
                break;
            }

            $show = $this->getShowFromXML($xmlShow, $venue);

            if (!$show->showRef) {
                echo "Missing showRef at iteration $i \n";
                continue;
            }

            $tags = $this->getTagsFromXML($xmlShow);

            /** @var Show $oldShow */
            $oldShow = Show::find()->showRef($show->showRef)->one();

            if ($oldShow) {
                if ($show->isDifferent($oldShow)) {
                    echo "Updating Show Ref $show->showRef ... \n";
                    $show->syncToElement($oldShow);
                    $show = $oldShow;
                    Craft::$app->elements->saveElement($show, false);
                } else {
                    echo "Skipping Show Ref $show->showRef - no change detected ... \n";
                    $show = $oldShow;
                }

                $oldTags = $this->tagsService->getTagsFromShow($show);
                $tagsToAdd = array_diff($tags, $oldTags);
                $tagsToRemove = array_diff($oldTags, $tags);

            //echo "Added " . $this->tagsService->tagShow($show, $tagsToAdd) . " tags. \n";
            //echo "Removed " . $this->tagsService->untagShow($show, $tagsToRemove) . " tags. \n";
            } else {
                echo "Creating Show Ref $show->showRef ... \n";
                Craft::$app->elements->saveElement($show, false);
                //echo "Added " . $this->tagsService->tagShow($show, $tags) . " tags. \n";
            }

            // store show ref so we can sort out deletions later on
            $this->showRefs[$show->showRef] = $show->showRef;

            // store tag names so we can sort out deletions later on
            $this->tagNames = array_unique(array_merge($this->tagNames, $tags));

            $this->updateSyncProgress();

            $this->syncEvents($xmlShow, $show);
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param Show $show
     * @throws Throwable
     */
    private function syncEvents(SimpleXMLElement $xml, Show $show)
    {
        foreach ($xml->events->event as $i => $xmlEvent) {
            if ($this->showAPICallCount >= $this->showAPICallLimit) {
                echo "{$this->showAPICallLimit} reached\n";
                $this->showAPICallLimitExceeded = true;
                break;
            }

            $event = $this->getEventFromXML($xmlEvent, $show);

            if (!$event) {
                continue;
            }

            if (!$event->eventRef) {
                echo "Missing eventRef at iteration $i \n";
                continue;
            }

            /** @var Event $oldEvent */
            $oldEvent = Event::find()->eventRef($event->eventRef)->one();

            if ($oldEvent) {
                if ($event->isDifferent($oldEvent)) {
                    echo "Updating Event Ref $event->eventRef ... \n";
                    $event->syncToElement($oldEvent);
                    $event = $oldEvent;
                    Craft::$app->elements->saveElement($oldEvent, false);
                } else {
                    echo "Skipping Event Ref $event->eventRef - no change detected ... \n";
                    $event = $oldEvent;
                }
            } else {
                echo "Creating Event Ref $event->eventRef ... \n";
                Craft::$app->elements->saveElement($event, false);
            }

            // store event ref so we can sort out deletions later on
            $this->eventRefs[$event->eventRef] = $event->eventRef;

            $this->updateSyncProgress();
        }
    }

    /**
     * Deletes elements that were not present in the XML feed
     * @throws Throwable
     */
    private function processDeletions()
    {
        echo "Process Deletions. \n";
        $deleteVenues = Venue::find()->excludeVenueRefs($this->venueRefs)->all();
        /** @var Venue $deleteVenue */
        foreach ($deleteVenues as $deleteVenue) {
            echo "Deleting Venue Ref $deleteVenue->venueRef ... \n";
            Craft::$app->elements->deleteElement($deleteVenue, true);
        }

        $deleteShows = Show::find()->excludeShowRefs($this->showRefs)->all();
        /** @var Show $deleteShow */
        foreach ($deleteShows as $deleteShow) {
            echo "Deleting Show Ref $deleteShow->showRef ... \n";
            Craft::$app->elements->deleteElement($deleteShow, true);
        }

        $deleteEvents = Event::find()->excludeEventRefs($this->eventRefs)->all();
        /** @var Event $deleteEvent */
        foreach ($deleteEvents as $deleteEvent) {
            echo "Deleting Event Ref $deleteEvent->eventRef ... \n";
            $age = time() - $deleteEvent->dateUpdated->getTimestamp();
            if ($age > $this->cacheFileExpiry) {
                Craft::$app->elements->deleteElement($deleteEvent, true);
            }
            Craft::$app->elements->deleteElement($deleteEvent, true);
        }

        echo "Deleted " . $this->tagsService->deleteAllTagsExcept($this->tagNames) . " tags. \n";

        echo "Process Deletions finished. \n";
    }

    /**
     * @param SimpleXMLElement $xml
     * @return Venue
     */
    private function getVenueFromXML(SimpleXMLElement $xml): Venue
    {
        $venue = new Venue();
        $venue->venueRef = isset($xml['id']) ? (int)$xml['id'] : null;
        $venue->name = trim($xml->name);

        return $venue;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param Venue $venue
     * @return Show
     */
    private function getShowFromXML(SimpleXMLElement $xml, Venue $venue): Show
    {
        $show = new Show();
        $show->venueId = $venue->id;
        $show->showRef = isset($xml['id']) ? (int)$xml['id'] : null;
        $show->name = trim($xml->name);
        $show->description = trim($xml->description);
        $show->eventCategory = trim($xml->event_category);
        $show->productionCompanyName = trim($xml->production_company_name);
        $show->priority = (int)trim($xml->priority);
        $show->url = trim($xml->url);
        $show->version = (int)trim($xml->version);
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

    /**
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function getTagsFromXML(SimpleXMLElement $xml): array
    {
        $tags = [];

        foreach ($xml->tags->tag as $tag) {
            $tags[] = trim($tag);
        }

        return $tags;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param Show $show
     * @return Event
     * @throws Throwable
     */
    private function getEventFromXML(SimpleXMLElement $xml, Show $show): Event
    {
        $fileHash = $this->generateFileHash($xml->feed->url);

        $skip = false;

        if (file_exists($this->cachePath . $fileHash)) {
            $skip = true;
        }

        if ($skip) {
            echo "Skipped {$fileHash} - recently imported\n";
            return new Event();
        }

        touch($this->cachePath . $fileHash);

        $this->showAPICallCount++;
        echo "XML API Calls {$this->showAPICallCount}\n";

        // additional event information is stored in a separate XML feed. fetch it!
        $xml2 = simplexml_load_file($xml->feed->url);

        $event = new Event();
        $event->showId = $show->id;
        $event->eventRef = isset($xml['id']) ? (int)$xml['id'] : null;
        $event->name = trim($xml->name);
        $event->dateTime = $this->getDateTimeFromXML($xml2, 'date_time');
        $event->openingTime = $this->getDateTimeFromXML($xml2, 'opening_time');
        $event->onSaleTime = $this->getDateTimeFromXML($xml2, 'onsale_time');
        $event->duration = (int)trim($xml2->duration);
        $event->available = (int)trim($xml2->available);
        $event->capacity = (int)trim($xml2->capacity);
        $event->venueLayout = trim($xml->venue_layout);
        $event->comment = trim($xml2->comment);
        $event->url = trim($xml2->url);
        $event->status = trim($xml2->status);
        $event->fee = (float)trim($xml2->transaction->fee);
        $event->feeCurrency = trim($xml2->transaction->fee->attributes()['currency']);
        $event->maximumTickets = (int)trim($xml2->transaction->maximum_tickets);
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
     * @param SimpleXMLElement $xml
     * @param string $nodeName
     * @return DateTime|null
     * @throws Throwable
     */
    private function getDateTimeFromXML(SimpleXMLElement $xml, string $nodeName)
    {
        if (empty($xml->$nodeName)) {
            return null;
        }

        return new DateTime(trim($xml->$nodeName), new DateTimeZone($xml->$nodeName->attributes()['zone']));
    }

    private function updateSyncProgress()
    {
        $this->entityCounter++;

        if (is_callable($this->setProgressFunction)) {
            ($this->setProgressFunction)(
                $this->entityCounter / $this->entityCount,
                "Processed $this->entityCounter out of $this->entityCount."
            );
        }
    }
}
