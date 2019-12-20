<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\ticketsolve\assetbundles\Ticketsolve;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class TicketsolveAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@devkokov/ticketsolve/assetbundles/ticketsolve/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Ticketsolve.js',
        ];

        $this->css = [
            'css/Ticketsolve.css',
        ];

        parent::init();
    }
}
