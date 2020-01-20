<?php

namespace burnthebook\ticketsolve\migrations;

use craft\db\Migration;
use burnthebook\ticketsolve\elements\Event;
use burnthebook\ticketsolve\elements\Show;
use burnthebook\ticketsolve\elements\Venue;

/**
 * m200120_170525_namespaces migration.
 */
class m200120_170525_namespaces extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $elementClasses = [
            Event::class,
            Show::class,
            Venue::class
        ];

        foreach ($elementClasses as $elementClass) {
            $this->update(
                '{{%elements}}',
                [
                    'type' => $elementClass
                ],
                [
                    'type' => str_replace('burnthebook\ticketsolve', 'burnthebook\ticketsolve', $elementClass)
                ],
                [],
                false
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m200120_170525_namespaces cannot be reverted.\n";
        return false;
    }
}
