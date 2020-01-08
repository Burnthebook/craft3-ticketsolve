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
use craft\elements\db\ElementQueryInterface;
use devkokov\ticketsolve\elements\db\EventQuery;
use devkokov\ticketsolve\elements\db\ShowQuery;
use devkokov\ticketsolve\models\TagModel;
use devkokov\ticketsolve\Ticketsolve;

/**
 * @author    Dimitar Kokov
 * @package   Ticketsolve
 * @since     1.0.0
 */
class Show extends AbstractComparableElement
{
    const TABLE     = '{{%ticketsolve_shows}}';
    const TABLE_STD = 'ticketsolve_shows';

    // Public Properties
    // =========================================================================

    public $venueId;
    public $showRef;
    public $name;
    public $description;
    public $eventCategory;
    public $productionCompanyName;
    public $priority;
    public $url;
    public $version;
    public $images = [];

    // Private Properties
    // =========================================================================

    /** @var Venue */
    private $venue;
    /** @var TagModel[] */
    private $tags;

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
            'name' => \Craft::t('ticketsolve', 'Name'),
            'showRef' => \Craft::t('ticketsolve', 'Show ID'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'name' => \Craft::t('ticketsolve', 'Name'),
            'showRef' => \Craft::t('ticketsolve', 'Show ID'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['showRef', 'name', 'description', 'eventCategory', 'productionCompanyName'];
    }

    protected static function defineComparableAttributes(): array
    {
        return [
            'venueId',
            'showRef',
            'name',
            'description',
            'eventCategory',
            'productionCompanyName',
            'priority',
            'url',
            'version',
            'images'
        ];
    }

    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('ticketsolve', 'All Shows'),
                'criteria' => []
            ],
        ];
    }

    // Public Methods
    // =========================================================================

    /**
     * @param string|null $value JSON encoded array of images
     */
    public function setImagesJson($value)
    {
        $this->images = (array)\GuzzleHttp\json_decode($value, true);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['name', 'showRef'], 'required'],
        ];
    }

    public function __toString()
    {
        if ($this->name) {
            return $this->name;
        }

        return parent::__toString();
    }

    /**
     * @return Venue|array|null
     */
    public function getVenue()
    {
        if (!is_null($this->venue)) {
            return $this->venue;
        }

        if (!$this->venueId) {
            return null;
        }

        return $this->venue = Venue::find()->id($this->venueId)->one();
    }

    /**
     * @return EventQuery
     */
    public function getEvents()
    {
        return Event::find()->showId($this->id);
    }

    /**
     * @return TagModel[]
     */
    public function getTags()
    {
        if (!is_null($this->tags)) {
            return $this->tags;
        }

        return $this->tags = Ticketsolve::getInstance()->tagsService->getTagsFromShow($this, false);
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
        $data = [
            'venueId' => $this->venueId,
            'showRef' => $this->showRef,
            'name' => $this->name,
            'description' => $this->description,
            'eventCategory' => $this->eventCategory,
            'productionCompanyName' => $this->productionCompanyName,
            'priority' => $this->priority,
            'url' => $this->url,
            'version' => $this->version,
            'imagesJson' => \GuzzleHttp\json_encode($this->images),
        ];

        if ($isNew) {
            $data['id'] = $this->id;
            \Craft::$app->db->createCommand()
                ->insert(self::TABLE, $data)
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update(self::TABLE, $data, ['id' => $this->id])
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
