<?php

namespace devkokov\ticketsolve\migrations;

use craft\db\Migration;

class Install extends Migration
{
    public function safeUp()
    {
        if (!$this->db->tableExists('{{%ticketsolve_venues}}')) {
            $this->createTable('{{%ticketsolve_venues}}', [
                'id' => $this->integer()->notNull(),
                'venueRef' => $this->bigInteger()->notNull(),
                'name' => $this->char(255)->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'PRIMARY KEY(id)',
            ]);

            // give it a FK to the elements table
            $this->addForeignKey(
                $this->db->getForeignKeyName('{{%ticketsolve_venues}}', 'id'),
                '{{%ticketsolve_venues}}',
                'id',
                '{{%elements}}',
                'id',
                'CASCADE',
                null
            );
        }

        if (!$this->db->tableExists('{{%ticketsolve_shows}}')) {
            $this->createTable('{{%ticketsolve_shows}}', [
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
                'PRIMARY KEY(id)',
            ]);

            // give it a FK to the elements table
            $this->addForeignKey(
                $this->db->getForeignKeyName('{{%ticketsolve_shows}}', 'id'),
                '{{%ticketsolve_shows}}',
                'id',
                '{{%elements}}',
                'id',
                'CASCADE',
                null
            );

            // give it a FK to the venues table
            $this->addForeignKey(
                $this->db->getForeignKeyName('{{%ticketsolve_shows}}', 'venueId'),
                '{{%ticketsolve_shows}}',
                'venueId',
                '{{%ticketsolve_venues}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }

        if (!$this->db->tableExists('{{%ticketsolve_tags}}')) {
            $this->createTable('{{%ticketsolve_tags}}', [
                'id' => $this->integer()->notNull()->append('AUTO_INCREMENT'),
                'name' => $this->char(255)->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'PRIMARY KEY(id)',
            ]);
        }

        if (!$this->db->tableExists('{{%ticketsolve_tags_index}}')) {
            $this->createTable('{{%ticketsolve_tags_index}}', [
                'id' => $this->integer()->notNull()->append('AUTO_INCREMENT'),
                'tagId' => $this->integer()->notNull(),
                'showId' => $this->integer()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'PRIMARY KEY(id)',
            ]);

            // give it a FK to the tags table
            $this->addForeignKey(
                $this->db->getForeignKeyName('{{%ticketsolve_tags_index}}', 'tagId'),
                '{{%ticketsolve_tags_index}}',
                'tagId',
                '{{%ticketsolve_tags}}',
                'id',
                'CASCADE',
                'CASCADE'
            );

            // give it a FK to the shows table
            $this->addForeignKey(
                $this->db->getForeignKeyName('{{%ticketsolve_tags_index}}', 'showId'),
                '{{%ticketsolve_tags_index}}',
                'showId',
                '{{%ticketsolve_shows}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }
    }

    public function safeDown()
    {
        $this->dropTableIfExists('{{%ticketsolve_tags_index}}');
        $this->dropTableIfExists('{{%ticketsolve_tags}}');
        $this->dropTableIfExists('{{%ticketsolve_shows}}');
        $this->dropTableIfExists('{{%ticketsolve_venues}}');
    }
}
