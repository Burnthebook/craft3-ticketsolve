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

    public function parseXML()
    {
        $url = Ticketsolve::$plugin->getSettings()->xmlUrl;

        $xml = simplexml_load_file($url);

        print_r($xml);
    }
}
