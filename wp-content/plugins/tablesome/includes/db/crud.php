<?php

namespace Tablesome\Includes\Db;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

use Tablesome\Includes\Db\CRUD_Interface;

if (!class_exists('\Tablesome\Includes\Db\CRUD')) {
    class CRUD implements CRUD_Interface
    {

        public $wpdb;
        public $table_name;

        public function __construct()
        {
            /** Check the tablesome records table in wp db, if else migrate that table structure  */
            // $table = new \Tablesome\Includes\Db\Tablesome_Table();
            // $table->create();

            global $wpdb;
            $this->wpdb = $wpdb;
            $this->wp_prefix = $this->wpdb->prefix;
            $this->table_name = $this->wpdb->prefix . TABLESOME_RECORDS_TABLE_NAME;
        }

        public function get_all_rows($table_id, array $params = array())
        {
            if (empty($table_id)) {
                return;
            }
            $limit = isset($params['limit']) && (int) $params['limit'] ? (int) $params['limit'] : 0;

            $query = "select * from $this->table_name where post_id=$table_id ORDER BY `rank_order`, `record_id`";
            if (!empty($limit)) {
                $query .= " limit $limit";
            }
            return $this->wpdb->get_results($query);
        }

        public function get_rows($table_id, array $record_ids)
        {
            if (empty($table_id) || empty($record_ids)) {
                return;
            }

            $query = "select * from $this->table_name  where post_id=$table_id and record_id in $record_ids ORDER BY `rank_order`, `record_id`";
            return $this->wpdb->get_results($query);
        }

        public function get_row($table_id, $record_id)
        {
            if (empty($table_id) || empty($record_id)) {
                return;
            }

            $row = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table_name WHERE post_id = %d and record_id = %d", $table_id, $record_id));
            return $row;
        }

        public function insert($table_id, array $args)
        {
            if (empty($table_id) || empty($args)) {
                return;
            }

            $author_id = get_current_user_id();

            $timestamp = current_time('timestamp');
            $datetime = date('Y-m-d H:i:s', $timestamp);
            $rank_order = isset($args["rank_order"]) && !empty($args["rank_order"]) ? $args["rank_order"] : "";
            $data = array(
                'post_id' => $table_id,
                'content' => json_encode($args['content']),
                'author_id' => $author_id,
                'created_at' => $datetime,
                'updated_at' => $datetime,
                'rank_order' => $rank_order,
            );
            return $this->wpdb->insert($this->table_name, $data);
        }

        public function bulk_inserting($table_id, array $args)
        {
            if (empty($table_id) || empty($args)) {
                return;
            }
            $query = "INSERT INTO " . $this->table_name . " (post_id, content, author_id, created_at, updated_at, rank_order) VALUES ";
            $rows = $args['rows'];
            $timestamp = current_time('timestamp');
            $datetime = date('Y-m-d H:i:s', $timestamp);
            $author_id = get_current_user_id();

            foreach ($rows as $row) {

                $rank_order = isset($row['rank_order']) ? $row['rank_order'] : '';
                $content = isset($row['content']) && is_array($row['content']) ? json_encode($row['content']) : $row;

                $query .= $this->wpdb->prepare(
                    "(%d, %s, %d, %s, %s, %s),",
                    $table_id,
                    $content,
                    $author_id,
                    $datetime,
                    $datetime,
                    $rank_order
                );
            }
            $query = rtrim($query, ',') . ';';
            if ($this->wpdb->query($query)) {
                return true;
            }
            return false;
        }

        public function update($table_id, $record_id, array $content, $rank_order = "")
        {
            if (empty($table_id) || empty($record_id)) {
                return;
            }

            if (!is_array($content)) {
                $reponse = array(
                    'message' => 'Content Should be an array',
                    'type' => 'Invalid Format',
                );
                wp_send_json($reponse);
                wp_die();
            }

            $author_id = get_current_user_id();
            $timestamp = current_time('timestamp');
            $datetime = date('Y-m-d H:i:s', $timestamp);

            $wpdb_props = array(
                "table" => $this->table_name,
                "data" => array(
                    'content' => json_encode($content),
                    'author_id' => $author_id,
                    'updated_at' => $datetime,
                    'rank_order' => $rank_order,
                ),
                "where" => array(
                    'post_id' => $table_id,
                    'record_id' => $record_id,
                ),
                "format" => array('%s', '%d', '%s', '%s'),
                "where_format" => array('%d', '%d')
            );


            $wpdb_props = $this->update_props_based_on_conditions($wpdb_props);

            $update = $this->wpdb->update($wpdb_props['table'], $wpdb_props['data'], $wpdb_props['where'], $wpdb_props['format'], $wpdb_props['where_format']);

            return $update;
        }

        public function update_props_based_on_conditions($wpdb_props, $update_props = array())
        {
            // Todo: 
            return $wpdb_props;
        }

        public function remove($table_id, $record_id)
        {
            if (empty($table_id) || empty($record_id)) {
                return;
            }
            $remove = $this->wpdb->delete($this->table_name, array('post_id' => $table_id, 'record_id' => $record_id), array('%d', '%d'));
            if ($remove) {
                return true;
            }
            return false;
        }

        public function delete_records($table_id, array $record_ids)
        {
            if (empty($table_id) || empty($record_ids)) {
                return;
            }

            $recordIDs = implode(',', array_map('absint', $record_ids));
            $query = "delete from  $this->table_name where post_id=$table_id and record_id in($recordIDs)";
            return $this->wpdb->get_results($query);
        }

        public function delete_records_by_table_id($table_id)
        {
            if (empty($table_id)) {
                return;
            }
            $delete_records = $this->wpdb->delete($this->table_name, array('post_id' => $table_id), array('%d'));
            if ($delete_records) {
                return true;
            }
            return false;
        }

        public function get_paginated_records($table_id, array $args = array())
        {
            if (empty($table_id)) {
                return;
            }

            $last_record_id = isset($args['last_record_id']) && !empty($args['last_record_id']) ? $args['last_record_id'] : 0;
            $limit = isset($args['page_limit']) && !empty($args['page_limit']) ? $args['page_limit'] : TABLESOME_NO_OF_RECORDS_PER_PAGE;

            $query = "select * from $this->table_name where post_id=$table_id and record_id > $last_record_id ORDER BY `rank_order`, `record_id` limit $limit";
            return $this->wpdb->get_results($query);
        }

        public function get_records_count($table_ids)
        {
            if (empty($table_ids)) {
                return 0;
            }
            $query = "select count('post_id') as total_records_count from $this->table_name where";
            if (is_array($table_ids)) {
                $table_ids = implode(',', $table_ids);
                $query .= " post_id in ($table_ids)";
            } else {
                $query .= " post_id=$table_ids";
            }
            return $this->wpdb->get_var($query);
        }

        public function get_tables_collection(array $args = array())
        {
            $tables = $this->get_tables($args);
            $tables_count = $this->get_tables_count($tables);
            $tables_column_format_collection = $this->get_tables_columns_formats_count($tables);
            $tables_records_count = $this->get_tables_records_count($tables);

            return [
                'tables_count' => $tables_count,
                'tables_column_format_collection' => $tables_column_format_collection,
                'tables_records_count' => $tables_records_count,
            ];
        }

        public function get_tables_count($tables)
        {
            return isset($tables) && !empty($tables) ? count($tables) : 0;
        }

        public function get_tables(array $args = array())
        {
            $post_args = array(
                'post_type' => TABLESOME_CPT,
                'numberposts' => -1,
            );

            if (isset($args['post__not_in']) && !empty($args['post__not_in'])) {
                $post_args['post__not_in'] = $args['post__not_in'];
            }

            return get_posts($post_args);
        }

        public function get_tables_columns_formats_count($tables)
        {
            $tables_column_format_collection = [];
            foreach ($tables as $table) {
                $table_meta = get_tablesome_data($table->ID);
                $columns = isset($table_meta['columns']) ? $table_meta['columns'] : [];
                $table_column_format_collection = $this->get_columns_counter($columns);
                $tables_column_format_collection = $this->get_recalculated_site_column_format_collection($tables_column_format_collection, $table_column_format_collection);
            }
            return $tables_column_format_collection;
        }

        public function get_columns_counter($columns)
        {
            $counter = [];
            foreach ($columns as $column) {
                $format = isset($column['format']) ? $column['format'] : '';
                if (empty($format)) {
                    continue;
                }
                $format_exits = (isset($counter[$format]));
                if (!$format_exits) {
                    $counter[$format] = 1;
                    continue;
                }
                $value = $counter[$format];
                $counter[$format] = $value + 1;
            }
            return $counter;
        }

        public function get_recalculated_site_column_format_collection($tables_column_format_collection, $table_column_format_collection)
        {
            foreach ($table_column_format_collection as $column_format => $value) {
                $exists = (isset($tables_column_format_collection[$column_format]));
                if (!$exists) {
                    $tables_column_format_collection[$column_format] = $value;
                    continue;
                }
                $sum_counter = $tables_column_format_collection[$column_format] + $value;
                $tables_column_format_collection[$column_format] = $sum_counter;
            }
            return $tables_column_format_collection;
        }

        public function get_tables_records_count($tables)
        {
            if (empty($tables)) {
                return 0;
            }
            // $table_ids = array_column($tables, 'ID');
            // return $this->get_records_count($table_ids);

            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            return $tablesome_db->get_tables_records_count($tables);
        }

        public function copy_table_records($table_id, $new_table_id)
        {
            if (empty($table_id) || empty($new_table_id)) {
                return;
            }

            $author_id = get_current_user_id();

            $copy_records_limit = TABLESOME_COPY_RECORDS_LIMIT;

            $temp_table_name = $this->wp_prefix . 'tablesome_temp';

            /*** Drop the temp table if already exists */
            $this->wpdb->query("DROP TABLE IF EXISTS $temp_table_name");

            /** Creating the temp table  */
            $query = '';
            $query .= " CREATE TABLE $temp_table_name";
            $query .= " AS select content from $this->table_name where post_id=$table_id ORDER BY `rank_order`, `record_id` limit $copy_records_limit; ";
            $table_created = $this->wpdb->query($query);
            $this->wpdb->flush();
            /** Return false, if creating the temp table is fails */
            if (empty($table_created)) {
                return false;
            }

            /*** inserting the records to the temp table */
            $query = '';
            $query = " INSERT INTO $this->table_name (post_id,content,author_id,created_at,updated_at) SELECT $new_table_id as post_id,content as content,$author_id as author_id,now() as created_at,now() as updated_at FROM $temp_table_name;";
            $this->wpdb->query($query);

            /*** DROP the temp table */
            $query = '';
            $query = " DROP TABLE $temp_table_name;";
            $temp_table_dropped = $this->wpdb->query($query);
            $this->wpdb->flush();

            if (empty($temp_table_dropped)) {
                return false;
            }
            return true;
        }

        public function get_tables_count_collection_by_query()
        {
            $post_type = TABLESOME_CPT;

            $posts_table = $this->wp_prefix . 'posts';

            $query_clause = array();
            $query_clause[] = "select posts.ID as post_id, count(records.post_id) as records_count from $posts_table as posts";
            $query_clause[] = "left join $this->table_name as records on records.post_id = posts.ID";
            $query_clause[] = "where posts.post_type='$post_type'";
            $query_clause[] = "and posts.post_status='publish'";
            $query_clause[] = "group by posts.ID";

            $query = implode(" ", $query_clause);

            return $this->wpdb->get_results($query, 'ARRAY_A');
        }

        public function truncate_table()
        {
            $query = '';
            $query = "TRUNCATE TABLE $this->table_name;";
            $result = $this->wpdb->query($query);
            return $result;
        }

        public function drop_table()
        {
            $query = '';
            $query = "DROP TABLE IF EXISTS $this->table_name";
            $result = $this->wpdb->query($query);
            return $result;
        }
    }
}
