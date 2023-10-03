<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\ticketsolve\models;

use craft\base\Model;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $xmlUrl = '';
    public $autoSync = 0;
    public $syncDelay = 900; // 15 minute delay between sync jobs, in seconds

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules() : array
    {
        return [
            ['xmlUrl', 'string'],
            ['autoSync', 'integer'],
            ['syncDelay', 'integer']
        ];
    }
}
