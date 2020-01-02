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
                'name' => $this->text()->notNull(),
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
    }

    public function safeDown()
    {
        $this->dropTableIfExists('{{%ticketsolve_venues}}');
    }
}
