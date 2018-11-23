<?php

use yii\db\Migration;

/**
 * Handles the creation of table `auth_item_node`.
 * Has foreign keys to the tables:
 *
 * - `auth_item`
 * - `node`
 */
class m181123_093040_create_junction_table_for_auth_item_and_node_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('auth_item_node', [
            'auth_item_name' => $this->string(64),
            'node_id' => $this->integer(),
            'created_at' => $this->dateTime(),
            'PRIMARY KEY(auth_item_name, node_id)',
        ]);

        // creates index for column `auth_item_name`
        $this->createIndex(
            'idx-auth_item_node-auth_item_name',
            'auth_item_node',
            'auth_item_name'
        );

        // add foreign key for table `auth_item`
        $this->addForeignKey(
            'fk-auth_item_node-auth_item_name',
            'auth_item_node',
            'auth_item_name',
            'auth_item',
            'name',
            'CASCADE'
        );

        // creates index for column `node_id`
        $this->createIndex(
            'idx-auth_item_node-node_id',
            'auth_item_node',
            'node_id'
        );

        // add foreign key for table `node`
        $this->addForeignKey(
            'fk-auth_item_node-node_id',
            'auth_item_node',
            'node_id',
            'node',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `auth_item`
        $this->dropForeignKey(
            'fk-auth_item_node-auth_item_name',
            'auth_item_node'
        );

        // drops index for column `auth_item_name`
        $this->dropIndex(
            'idx-auth_item_node-auth_item_name',
            'auth_item_node'
        );

        // drops foreign key for table `node`
        $this->dropForeignKey(
            'fk-auth_item_node-node_id',
            'auth_item_node'
        );

        // drops index for column `node_id`
        $this->dropIndex(
            'idx-auth_item_node-node_id',
            'auth_item_node'
        );

        $this->dropTable('auth_item_node');
    }
}
