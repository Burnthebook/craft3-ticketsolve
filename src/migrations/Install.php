<?php

namespace devkokov\ticketsolve\migrations;

use craft\db\Migration;
use devkokov\ticketsolve\elements\Event;
use devkokov\ticketsolve\elements\Show;
use devkokov\ticketsolve\elements\Venue;

class Install extends Migration
{
    const TABLE_VENUES = '{{%ticketsolve_venues}}';
    const TABLE_SHOWS = '{{%ticketsolve_shows}}';
    const TABLE_TAGS = '{{%ticketsolve_tags}}';
    const TABLE_TAGS_INDEX = '{{%ticketsolve_tags_index}}';
    const TABLE_EVENTS = '{{%ticketsolve_events}}';
    const TABLE_ELEMENTS = '{{%elements}}';

    public function safeUp()
    {
        $this->createVenuesTable();
        $this->createShowsTable();
        $this->createTagsTable();
        $this->createTagsIndexTable();
        $this->createEventsTable();
    }

    public function safeDown()
    {
        // clean-up elements table
        $this->delete(self::TABLE_ELEMENTS, ['in', 'type', [
            Event::class,
            Show::class,
            Venue::class
        ]]);

        // drop plugin's tables
        $this->dropTableIfExists(self::TABLE_EVENTS);
        $this->dropTableIfExists(self::TABLE_TAGS_INDEX);
        $this->dropTableIfExists(self::TABLE_TAGS);
        $this->dropTableIfExists(self::TABLE_SHOWS);
        $this->dropTableIfExists(self::TABLE_VENUES);
    }

    // Private Methods
    // =========================================================================

    private function createVenuesTable()
    {
        if ($this->db->tableExists(self::TABLE_VENUES)) {
            return;
        }

        $this->createTable(self::TABLE_VENUES, [
            'id' => $this->integer()->notNull(),
            'venueRef' => $this->bigInteger()->notNull(),
            'name' => $this->char(255)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->createIndex(
            $this->db->getIndexName(self::TABLE_VENUES, 'venueRef', true),
            self::TABLE_VENUES,
            'venueRef',
            true
        );

        // give it a FK to the elements table
        $this->addForeignKey(
            $this->db->getForeignKeyName(self::TABLE_VENUES, 'id'),
            self::TABLE_VENUES,
            'id',
            self::TABLE_ELEMENTS,
            'id',
            'CASCADE',
            null
        );
    }

    private function createShowsTable()
    {
        if ($this->db->tableExists(self::TABLE_SHOWS)) {
            return;
        }

        $this->createTable(self::TABLE_SHOWS, [
            'id' => $this->integer()->notNull(),
            'venueId' => $this->integer()->notNull(),
            'showRef' => $this->bigInteger()->notNull(),
            'name' => $this->char(255)->notNull(),
            'description' => $this->text()->notNull(),
            'eventCategory' => $this->char(255)->notNull(),
            'productionCompanyName' => $this->char(255)->notNull(),
            'priority' => $this->integer()->notNull(),
            'url' => $this->char(255)->notNull(),
            'version' => $this->integer()->notNull(),
            'imagesJson' => $this->text()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->createIndex(
            $this->db->getIndexName(self::TABLE_SHOWS, 'showRef', true),
            self::TABLE_SHOWS,
            'showRef',
            true
        );

        // give it a FK to the elements table
        $this->addForeignKey(
            $this->db->getForeignKeyName(self::TABLE_SHOWS, 'id'),
            self::TABLE_SHOWS,
            'id',
            self::TABLE_ELEMENTS,
            'id',
            'CASCADE',
            null
        );

        // give it a FK to the venues table
        $this->addForeignKey(
            $this->db->getForeignKeyName(self::TABLE_SHOWS, 'venueId'),
            self::TABLE_SHOWS,
            'venueId',
            self::TABLE_VENUES,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    private function createEventsTable()
    {
        if ($this->db->tableExists(self::TABLE_EVENTS)) {
            return;
        }

        $this->createTable(self::TABLE_EVENTS, [
            'id' => $this->integer()->notNull(),
            'showId' => $this->integer()->notNull(),
            'eventRef' => $this->bigInteger()->notNull(),
            'name' => $this->char(255)->notNull(),
            'dateTimeString' => $this->dateTime(),
            'openingTimeString' => $this->dateTime(),
            'onSaleTimeString' => $this->dateTime(),
            'duration' => $this->integer(),
            'available' => $this->integer(),
            'capacity' => $this->integer(),
            'venueLayout' => $this->char(255),
            'comment' => $this->text(),
            'url' => $this->char(255),
            'status' => $this->char(255),
            'fee' => $this->decimal(10, 2),
            'feeCurrency' => $this->char(3),
            'maximumTickets' => $this->integer(),
            'pricesJson' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->createIndex(
            $this->db->getIndexName(self::TABLE_EVENTS, 'eventRef', true),
            self::TABLE_EVENTS,
            'eventRef',
            true
        );

        // give it a FK to the elements table
        $this->addForeignKey(
            $this->db->getForeignKeyName(self::TABLE_EVENTS, 'id'),
            self::TABLE_EVENTS,
            'id',
            self::TABLE_ELEMENTS,
            'id',
            'CASCADE',
            null
        );

        // give it a FK to the shows table
        $this->addForeignKey(
            $this->db->getForeignKeyName(self::TABLE_EVENTS, 'showId'),
            self::TABLE_EVENTS,
            'showId',
            self::TABLE_SHOWS,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    private function createTagsTable()
    {
        if ($this->db->tableExists(self::TABLE_TAGS)) {
            return;
        }

        $this->createTable(self::TABLE_TAGS, [
            'id' => $this->integer()->notNull()->append('AUTO_INCREMENT'),
            'name' => $this->char(255)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);
    }

    private function createTagsIndexTable()
    {
        if ($this->db->tableExists(self::TABLE_TAGS_INDEX)) {
            return;
        }

        $this->createTable(self::TABLE_TAGS_INDEX, [
            'id' => $this->integer()->notNull()->append('AUTO_INCREMENT'),
            'tagId' => $this->integer()->notNull(),
            'showId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        // give it a FK to the tags table
        $this->addForeignKey(
            $this->db->getForeignKeyName(self::TABLE_TAGS_INDEX, 'tagId'),
            self::TABLE_TAGS_INDEX,
            'tagId',
            self::TABLE_TAGS,
            'id',
            'CASCADE',
            'CASCADE'
        );

        // give it a FK to the shows table
        $this->addForeignKey(
            $this->db->getForeignKeyName(self::TABLE_TAGS_INDEX, 'showId'),
            self::TABLE_TAGS_INDEX,
            'showId',
            self::TABLE_SHOWS,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
