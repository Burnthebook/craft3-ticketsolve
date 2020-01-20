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

class TagModel extends Model
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function safeAttributes(): array
    {
        return [
            'name',
        ];
    }
}
