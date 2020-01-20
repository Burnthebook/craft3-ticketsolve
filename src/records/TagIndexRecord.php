<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace devkokov\ticketsolve\records;

use craft\db\ActiveRecord;

/**
 * @property int $id
 * @property int $tagId
 * @property int $showId
 */
class TagIndexRecord extends ActiveRecord
{
    const TABLE     = '{{%ticketsolve_tags_index}}';
    const TABLE_STD = 'ticketsolve_tags_index';

    /**
     * @return string
     */
    public static function tableName(): string
    {
        return self::TABLE;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['tagId', 'showId'], 'required'],
            [['tagId', 'showId'], 'integer'],
        ];
    }
}
