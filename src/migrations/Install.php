<?php

namespace devkokov\ticketsolve\migrations;

use craft\db\Migration;
use devkokov\ticketsolve\elements\Event;
use devkokov\ticketsolve\elements\Show;
use devkokov\ticketsolve\elements\Venue;
use devkokov\ticketsolve\records\TagRecord;
use devkokov\ticketsolve\records\TagIndexRecord;

class Install extends Migration
{
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
        $this->dropTableIfExists(Event::TABLE);
        $this->dropTableIfExists(TagIndexRecord::TABLE);
        $this->dropTableIfExists(TagRecord::TABLE);
        $this->dropTableIfExists(Show::TABLE);
        $this->dropTableIfExists(Venue::TABLE);
    }

    // Private Methods
    // =========================================================================

    private function createVenuesTable()
    {
        if ($this->db->tableExists(Venue::TABLE)) {
            return;
        }

        $this->createTable(Venue::TABLE, [
            'id' => $this->integer()->notNull(),
            'venueRef' => $this->bigInteger()->notNull(),
            'name' => $this->char(255)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->createIndex(
            $this->db->getIndexName(Venue::TABLE, 'venueRef', true),
            Venue::TABLE,
            'venueRef',
            true
        );

        // give it a FK to the elements table
        $this->addForeignKey(
            $this->db->getForeignKeyName(Venue::TABLE, 'id'),
            Venue::TABLE,
            'id',
            self::TABLE_ELEMENTS,
            'id',
            'CASCADE',
            null
        );
    }

    private function createShowsTable()
    {
        if ($this->db->tableExists(Show::TABLE)) {
            return;
        }

        $this->createTable(Show::TABLE, [
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
            $this->db->getIndexName(Show::TABLE, 'showRef', true),
            Show::TABLE,
            'showRef',
            true
        );

        // give it a FK to the elements table
        $this->addForeignKey(
            $this->db->getForeignKeyName(Show::TABLE, 'id'),
            Show::TABLE,
            'id',
            self::TABLE_ELEMENTS,
            'id',
            'CASCADE',
            null
        );

        // give it a FK to the venues table
        $this->addForeignKey(
            $this->db->getForeignKeyName(Show::TABLE, 'venueId'),
            Show::TABLE,
            'venueId',
            Venue::TABLE,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    private function createEventsTable()
    {
        if ($this->db->tableExists(Event::TABLE)) {
            return;
        }

        $this->createTable(Event::TABLE, [
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
            $this->db->getIndexName(Event::TABLE, 'eventRef', true),
            Event::TABLE,
            'eventRef',
            true
        );

        // give it a FK to the elements table
        $this->addForeignKey(
            $this->db->getForeignKeyName(Event::TABLE, 'id'),
            Event::TABLE,
            'id',
            self::TABLE_ELEMENTS,
            'id',
            'CASCADE',
            null
        );

        // give it a FK to the shows table
        $this->addForeignKey(
            $this->db->getForeignKeyName(Event::TABLE, 'showId'),
            Event::TABLE,
            'showId',
            Show::TABLE,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    private function createTagsTable()
    {
        if ($this->db->tableExists(TagRecord::TABLE)) {
            return;
        }

        $this->createTable(TagRecord::TABLE, [
            'id' => $this->integer()->notNull()->append('AUTO_INCREMENT'),
            'name' => $this->char(255)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->createIndex(
            $this->db->getIndexName(TagRecord::TABLE, 'name', true),
            TagRecord::TABLE,
            'name',
            true
        );
    }

    private function createTagsIndexTable()
    {
        if ($this->db->tableExists(TagIndexRecord::TABLE)) {
            return;
        }

        $this->createTable(TagIndexRecord::TABLE, [
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
            $this->db->getForeignKeyName(TagIndexRecord::TABLE, 'tagId'),
            TagIndexRecord::TABLE,
            'tagId',
            TagRecord::TABLE,
            'id',
            'CASCADE',
            'CASCADE'
        );

        // give it a FK to the shows table
        $this->addForeignKey(
            $this->db->getForeignKeyName(TagIndexRecord::TABLE, 'showId'),
            TagIndexRecord::TABLE,
            'showId',
            Show::TABLE,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
