<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\ticketsolve\records;

use craft\db\ActiveRecord;
use burnthebook\ticketsolve\models\TagModel;

/**
 * @property int    $id
 * @property string $name
 */
class TagRecord extends ActiveRecord
{
    public const TABLE     = '{{%ticketsolve_tags}}';
    public const TABLE_STD = 'ticketsolve_tags';

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
            [['name'], 'unique'],
            [['name'], 'required'],
        ];
    }

    public function toModel(): TagModel
    {
        $model = new TagModel();
        $model->id = $this->id;
        $model->name = $this->name;

        return $model;
    }
}
