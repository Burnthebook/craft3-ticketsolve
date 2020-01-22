<?php

namespace burnthebook\ticketsolve\migrations;

use burnthebook\ticketsolve\fields\Shows;
use craft\db\Migration;
use burnthebook\ticketsolve\elements\Event;
use burnthebook\ticketsolve\elements\Show;
use burnthebook\ticketsolve\elements\Venue;

/**
 * m200122_122625_field_namespaces migration.
 */
class m200122_122625_field_namespaces extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update(
            '{{%fields}}',
            [
                'type' => Shows::class
            ],
            [
                'type' => str_replace('burnthebook\ticketsolve', 'devkokov\ticketsolve', Shows::class)
            ],
            [],
            false
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200122_122625_field_namespaces cannot be reverted.\n";
        return false;
    }
}
