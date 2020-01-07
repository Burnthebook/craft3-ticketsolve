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

use devkokov\ticketsolve\Ticketsolve;

use Craft;
use craft\queue\BaseJob;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class SyncJob extends BaseJob
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $job = $this;

        Ticketsolve::getInstance()->ticketsolveService->syncFromXML(
            function ($progress, $label = null) use ($job, $queue) {
                $job->setProgress($queue, $progress, $label);
            }
        );
    }

    public static function getDefaultDescription(): string
    {
        return Craft::t('ticketsolve', 'Ticketsolve Sync');
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return self::getDefaultDescription();
    }
}
