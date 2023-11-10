<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\ticketsolve\services;

use craft\base\Component;
use burnthebook\ticketsolve\elements\Show;
use burnthebook\ticketsolve\models\TagModel;
use burnthebook\ticketsolve\records\TagIndexRecord;
use burnthebook\ticketsolve\records\TagRecord;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class TagsService extends Component
{
    /** @var TagModel[] */
    private static $tagsCacheById = [];
    /** @var TagModel[] */
    private static $tagsCacheByName = [];

    // Public Methods
    // =========================================================================

    /**
     * @param string $tagName
     * @return TagModel|bool
     */
    public function createTag(string $tagName): bool|TagModel
    {
        $record = new TagRecord();
        $record->name = $tagName;

        $record->validate();

        if ($record->hasErrors()) {
            return false;
        }

        if (!$record->save(false)) {
            return false;
        }

        $model = $record->toModel();

        self::$tagsCacheById[$model->id] = $model;
        self::$tagsCacheByName[$model->name] = $model;

        return $model;
    }

    /**
     * @param $id
     * @return TagModel|null
     */
    public function getTagById($id): ?TagModel
    {
        if (isset(self::$tagsCacheById[$id])) {
            return self::$tagsCacheById[$id];
        }

        $record = TagRecord::findOne(['id' => $id]);
        if (!$record) {
            return null;
        }

        $model = $record->toModel();

        self::$tagsCacheById[$id] = $model;
        self::$tagsCacheByName[$model->name] = $model;

        return $model;
    }

    /**
     * @param $name
     * @return TagModel|null
     */
    public function getTagByName($name): ?TagModel
    {
        if (isset(self::$tagsCacheByName[$name])) {
            return self::$tagsCacheByName[$name];
        }

        $record = TagRecord::findOne(['name' => $name]);
        if (!$record) {
            return null;
        }

        $model = $record->toModel();

        self::$tagsCacheById[$model->id] = $model;
        self::$tagsCacheByName[$name] = $model;

        return $model;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteTagById($id): bool
    {
        return (bool) TagRecord::deleteAll(['id' => $id]);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function deleteTagByName($name): bool
    {
        return (bool) TagRecord::deleteAll(['name' => $name]);
    }

    /**
     * @param Show $show
     * @param bool $strings Whether to return an array of strings or an array of TagModel objects
     * @return array
     */
    public function getTagsFromShow(Show $show, $strings = true): array
    {
        $tags = [];

        $records = TagIndexRecord::find()->where(['showId' => $show->id])->all();
        foreach ($records as $record) {
            $tag = $this->getTagById($record->tagId);
            $tags[] = $strings ? $tag->name : $tag;
        }

        return $tags;
    }

    /**
     * @param Show $show
     * @param array $tagNames
     * @return int Number of added tags
     */
    public function tagShow(Show $show, array $tagNames): int
    {
        $tagsAdded = 0;

        foreach ($tagNames as $tagName) {
            $tag = $this->getTagByName($tagName);

            if (!$tag) {
                $tag = $this->createTag($tagName);
            }

            if (!$tag) {
                continue;
            }

            $record = new TagIndexRecord();
            $record->tagId = $tag->id;
            $record->showId = $show->id;

            $record->validate();

            if ($record->hasErrors()) {
                continue;
            }

            if ($record->save(false)) {
                $tagsAdded++;
            }
        }

        return $tagsAdded;
    }

    /**
     * @param Show $show
     * @param array $tagNames
     * @return int Number of removed tags
     */
    public function untagShow(Show $show, array $tagNames): int
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tag = $this->getTagByName($tagName);

            if (!$tag) {
                continue;
            }

            $tagIds[] = $tag->id;
        }

        if (empty($tagIds)) {
            return 0;
        }

        return TagIndexRecord::deleteAll(['and', ['showId' => $show->id], ['tagId' => $tagIds]]);
    }

    /**
     * @param array $tagNames
     * @return int Number of deleted tags
     */
    public function deleteAllTagsExcept(array $tagNames): int
    {
        return TagRecord::deleteAll(['not in', 'name', $tagNames]);
    }
}
