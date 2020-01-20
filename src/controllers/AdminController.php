<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace devkokov\ticketsolve\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use devkokov\ticketsolve\Ticketsolve;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class AdminController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect(UrlHelper::cpUrl('ticketsolve/venues'));
    }

    /**
     * @return mixed
     */
    public function actionSyncNow()
    {
        $syncService = Ticketsolve::getInstance()->syncService;

        $syncService->removeQueuedSyncJobs(true);
        $syncService->queueSyncJob(0, true);

        Craft::$app->session->setNotice(Craft::t('ticketsolve', 'Sync has been started'));

        return $this->redirect(UrlHelper::cpUrl('ticketsolve'));
    }
}
