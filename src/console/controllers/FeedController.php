<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\ticketsolve\console\controllers;

use Throwable;
use yii\console\Controller;
use yii\console\ExitCode;
use devkokov\ticketsolve\Ticketsolve;

class FeedController extends Controller
{
    /**
     * Syncs the CMS with data from your Ticketsolve XML feed.
     * @return int
     * @throws Throwable
     */
    public function actionSync()
    {
        Ticketsolve::getInstance()->syncService->startXMLFeedSync();

        return ExitCode::OK;
    }
}
