<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\ticketsolve\validators;

use craft\elements\db\ElementQuery;
use craft\validators\ArrayValidator;

class ElementCountValidator extends ArrayValidator
{
    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!$value instanceof \Countable && !is_array($value)) {
            return [$this->message, []];
        }

        if ($value instanceof ElementQuery) {
            $value->createCommand();
            $count = $value->count();
        } else {
            $count = count($value);
        }

        if ($this->min !== null && $count < $this->min) {
            return [$this->tooFew, ['min' => $this->min]];
        }
        if ($this->max !== null && $count > $this->max) {
            return [$this->tooMany, ['max' => $this->max]];
        }
        if ($this->count !== null && $count !== $this->count) {
            return [$this->notEqual, ['count' => $this->count]];
        }

        return null;
    }
}
