<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\ticketsolve\records;

use craft\db\ActiveRecord;

/**
 * @property int    $id
 * @property string $name
 */
class TagRecord extends ActiveRecord
{
    const TABLE     = '{{%ticketsolve_tags}}';
    const TABLE_STD = 'ticketsolve_tags';

    /**
     * @return TagRecord
     */
    public static function create(): TagRecord
    {
        return new static();
    }

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
            [['handle'], 'unique'],
            [['name', 'handle'], 'required'],
        ];
    }
}
