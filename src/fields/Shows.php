<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\ticketsolve\fields;

use Craft;
use craft\base\Element;
use craft\fields\BaseRelationField;
use burnthebook\ticketsolve\elements\Show;
use burnthebook\ticketsolve\validators\ElementCountValidator;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class Shows extends BaseRelationField
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('ticketsolve', 'Ticketsolve Shows');
    }

    public static function elementType(): string
    {
        return Show::class;
    }

    public static function defaultSelectionLabel(): string
    {
        return \Craft::t('ticketsolve', 'Add a show');
    }

    public function getElementValidationRules(): array
    {
        $rules = [
            [
                ElementCountValidator::class,
                'max' => $this->allowLimit && $this->limit ? $this->limit : null,
                'tooMany' => Craft::t(
                    'app',
                    '{attribute} should contain at most {max, number} {max, plural, one{selection} other{selections}}.'
                ),
            ],
        ];

        if ($this->validateRelatedElements) {
            $rules[] = ['validateRelatedElements', 'on' => [Element::SCENARIO_LIVE]];
        }

        return $rules;
    }
}
