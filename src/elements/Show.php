<?php
/**
 * Ticketsolve plugin for Craft CMS 3.x
 *
 * Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.
 *
 * @link      https://github.com/devkokov
 * @copyright Copyright (c) 2019 Dimitar Kokov
 */

namespace devkokov\ticketsolve\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use devkokov\ticketsolve\elements\db\ShowQuery;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class Show extends Element
{
    // Public Properties
    // =========================================================================

    public $showId;
    public $venueId;
    public $name;
    public $description;
    public $eventCategory;
    public $productionCompanyName;
    public $priority;
    public $url;
    public $version;
    public $images = [];

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('ticketsolve', 'Show');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('ticketsolve', 'Shows');
    }

    /**
     * @inheritdoc
     * @return ShowQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new ShowQuery(static::class);
    }

    protected static function defineSortOptions(): array
    {
        return [
            'showId' => \Craft::t('ticketsolve', 'Show ID'),
            'venueId' => \Craft::t('ticketsolve', 'Venue ID'),
            'name' => \Craft::t('ticketsolve', 'Name'),
            'eventCategory' => \Craft::t('ticketsolve', 'Event Category'),
            'productionCompanyName' => \Craft::t('ticketsolve', 'Production Company Name'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'showId' => \Craft::t('ticketsolve', 'Show ID'),
            'venueId' => \Craft::t('ticketsolve', 'Venue ID'),
            'name' => \Craft::t('ticketsolve', 'Name'),
            'eventCategory' => \Craft::t('ticketsolve', 'Event Category'),
            'productionCompanyName' => \Craft::t('ticketsolve', 'Production Company Name'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['showId','venueId','name','description','eventCategory','productionCompanyName'];
    }

    // Public Methods
    // =========================================================================

    /**
     * @param string $value JSON encoded array of images
     */
    public function setImagesJson(string $value)
    {
        $this->images = (array) \GuzzleHttp\json_decode($value);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['name', 'showId', 'venueId'], 'required'],
        ];
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            \Craft::$app->db->createCommand()
                ->insert('{{%ticketsolve_shows}}', [
                    'id' => $this->id,
                    'showId' => $this->showId,
                    'venueId' => $this->venueId,
                    'name' => $this->name,
                    'description' => $this->description,
                    'eventCategory' => $this->eventCategory,
                    'productionCompanyName' => $this->productionCompanyName,
                    'priority' => $this->priority,
                    'url' => $this->url,
                    'version' => $this->version,
                    'imagesJson' => \GuzzleHttp\json_encode($this->images),
                ])
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update('{{%ticketsolve_shows}}', [
                    'name' => $this->name,
                ], ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
    }

    /**
     * @inheritdoc
     */
    public function beforeMoveInStructure(int $structureId): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterMoveInStructure(int $structureId)
    {
    }
}
