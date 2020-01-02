<?php

namespace devkokov\ticketsolve\console\controllers;

use devkokov\ticketsolve\Ticketsolve;
use yii\console\Controller;
use yii\console\ExitCode;

class FeedController extends Controller
{
    /**
     * Syncs the CMS with data from your Ticketsolve XML feed.
     */
    public function actionSync()
    {
        Ticketsolve::getInstance()->ticketsolveService->syncFromXML();

        return ExitCode::OK;
    }
}
