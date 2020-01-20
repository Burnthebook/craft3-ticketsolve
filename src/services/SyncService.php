<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace devkokov\ticketsolve\services;

use Craft;
use Throwable;
use craft\base\Component;
use craft\db\Table;
use yii\db\Query;
use devkokov\ticketsolve\jobs\SyncJob;
use devkokov\ticketsolve\sync\XMLFeedSync;

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
     * @param callable|null $setProgressFunction A function that sets the progress of the sync.
     * Accepts a number between 0 and 1 as the first parameter, and an optional label as the second
     * @throws Throwable
     */
    public function startXMLFeedSync(callable $setProgressFunction = null)
    {
        (new XMLFeedSync($setProgressFunction))->start();
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
}
