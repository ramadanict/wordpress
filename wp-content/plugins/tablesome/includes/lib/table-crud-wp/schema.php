<?php

namespace Tablesome\Includes\Lib\Table_Crud_WP;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Lib\Table_Crud_WP\Schema')) {
    class Schema
    {
        public function get_schema($table_columns)
        {
            $schema = array();
            $schema[] = 'id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT';
            $schema[] = 'post_id BIGINT UNSIGNED NULL';
            foreach ($table_columns as $column) {
                $schema[] = $column . ' TEXT DEFAULT NULL';
            }
            $schema[] = 'author_id BIGINT UNSIGNED NULL';
            $schema[] = 'updated_by BIGINT UNSIGNED NULL';
            $schema[] = 'created_at DATETIME DEFAULT "0000-00-00 00:00:00" NULL';
            $schema[] = 'updated_at DATETIME DEFAULT "0000-00-00 00:00:00" NULL';
            $schema[] = 'rank_order varchar(255) DEFAULT NULL';
            $schema[] = 'PRIMARY KEY (id)';

            $schema_struct = implode(",", $schema);

            return $schema_struct;
        }
    }
}
