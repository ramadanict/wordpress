<?php

namespace Tablesome\Includes\Modules\TablesomeDB;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\TablesomeDB\Schema_Generator')) {
    class Schema_Generator
    {
        public $defaults = array(
            'id' => [
                'name' => 'id',
                'type' => 'bigint',
                'length' => '20',
                'unsigned' => true,
                'extra' => 'auto_increment',
                'primary' => true,
                'sortable' => true,
            ],
            'post_id' => [
                'name' => 'post_id',
                'type' => 'bigint',
                'length' => '20',
                'unsigned' => true,
            ],
            'author_id' => [
                'name' => 'author_id',
                'type' => 'bigint',
                'length' => '20',
                'unsigned' => true,
            ],
            'created_at' => [
                'name' => 'created_at',
                'type' => 'datetime',
                'date_query' => true,
                'unsigned' => true,
                'searchable' => true,
                'sortable' => true,
            ],
            'updated_at' => [
                'name' => 'updated_at',
                'type' => 'datetime',
                'date_query' => true,
                'unsigned' => true,
                'searchable' => true,
                'sortable' => true,
            ],
            'rank_order' => [
                'name' => 'rank_order',
                'type' => 'tinytext',
                'unsigned' => true,
                'searchable' => true,
                'sortable' => true,
            ],
        );

        public function __construct($columns)
        {
            if (!is_array($columns) || empty($columns)) {return;}
            $this->set_columns($columns);
        }

        public function set_columns($columns)
        {
            foreach ($columns as $column) {
                if (isset($this->defaults[$column])) {
                    continue;
                }
                $this->defaults[$column] = $this->add_column($column);
            }
        }

        public function add_column($column)
        {
            return array(
                'name' => $column,
                'type' => 'text',
                'unsigned' => true,
                'searchable' => true,
                'sortable' => true,
            );
        }

        public function get_columns()
        {
            return $this->defaults;
        }

    }
}
