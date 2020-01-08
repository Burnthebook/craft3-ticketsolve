<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\ticketsolve\jobs;

use Craft;
use Throwable;
use craft\queue\BaseJob;
use devkokov\ticketsolve\Ticketsolve;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class SyncJob extends BaseJob
{
    // Public Methods
    // =========================================================================

    public $manual = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function execute($queue)
    {
        $job = $this;

        $setProgressFunction = function ($progress, $label = null) use ($job, $queue) {
            $job->setProgress($queue, $progress, $label);
        };

        Ticketsolve::getInstance()->syncService->startXMLFeedSync($setProgressFunction);
    }

    public static function getDefaultDescription(): string
    {
        return Craft::t('ticketsolve', 'Ticketsolve Sync');
    }

    public static function getDefaultManualDescription(): string
    {
        return Craft::t('ticketsolve', 'Ticketsolve Sync (Manual)');
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return $this->manual ? self::getDefaultManualDescription() : self::getDefaultDescription();
    }
}
