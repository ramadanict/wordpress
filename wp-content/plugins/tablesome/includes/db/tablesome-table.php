<?php

namespace Tablesome\Includes\Db;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Db\Tablesome_Table')) {
    class Tablesome_Table
    {
        public function create()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . TABLESOME_RECORDS_TABLE_NAME;

            $charset_collate = $wpdb->get_charset_collate();
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            $query = "CREATE TABLE $table_name (
                record_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                post_id BIGINT UNSIGNED  NOT NULL,
                content LONGTEXT NULL,
                author_id BIGINT UNSIGNED  NOT NULL,
                created_at DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
                updated_at DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
                rank_order varchar(255) DEFAULT '' NOT NULL,
                PRIMARY KEY (record_id)
            ) $charset_collate;";
            
            /** Creates a wp table in the database, if it doesn’t already exist. */
            maybe_create_table($table_name, $query);            
        }
    }
}
