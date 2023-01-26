<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tablesome_Event_Log_Table_Schema extends \BerlinDB\Database\Schema
{
    public $columns = array(

        'id' => [
            'name' => 'id',
            'type' => 'bigint',
            'length' => '20',
            'unsigned' => true,
            'extra' => 'auto_increment',
            'primary' => true,
            'sortable' => true,
        ],

        'user_id' => [
            'name' => 'user_id',
            'type' => 'bigint',
            'length' => '20',
            'unsigned' => true,
        ],

        'type' => [
            'name' => 'type',
            'type' => 'varchar',
            'length' => '25',
            'unsigned' => true,
            'searchable' => true,
            'sortable' => true,
        ],

        'table_id' => [
            'name' => 'table_id',
            'type' => 'bigint',
            'length' => '20',
            'unsigned' => true,
            'searchable' => true,
            'sortable' => true,
        ],

        'trigger_id' => [
            'name' => 'trigger_id',
            'type' => 'int',
            'length' => '11',
            'unsigned' => true,
        ],

        'action_id' => [
            'name' => 'action_id',
            'type' => 'int',
            'length' => '11',
            'unsigned' => true,
        ],

        'status' => [
            'name' => 'status',
            'type' => 'tinyint',
            'unsigned' => true,
        ],

        'content' => [
            'name' => 'content',
            'type' => 'text',
            'searchable' => true,
            'sortable' => true,
        ],

        'user_ip' => [
            'name' => 'user_ip',
            'type' => 'varbinary',
            'length' => '16',
            'searchable' => true,
            'sortable' => true,
        ],

        'user_agent' => [
            'name' => 'user_agent',
            'type' => 'text',
        ],

        'created_at' => [
            'name' => 'created_at',
            'type' => 'datetime',
            'date_query' => true,
            'unsigned' => true,
            'searchable' => true,
            'sortable' => true,
        ],

    );

}