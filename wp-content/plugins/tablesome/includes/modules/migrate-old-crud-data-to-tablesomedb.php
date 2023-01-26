<?php

namespace Tablesome\Includes\Modules;

if (!class_exists('\Tablesome\Includes\Modules\Migrate_Old_Crud_Data_To_TablesomeDB')) {
    class Migrate_Old_Crud_Data_To_TablesomeDB
    {
        public function run()
        {

            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $crud = new \Tablesome\Includes\Db\CRUD();

            /** Get all tablesome posts including trash posts */
            $tables = get_posts(array(
                'post_type' => TABLESOME_CPT,
                'post_status' => array('publish', 'pending', 'draft', 'trash'),
                'numberposts' => -1,
            ));

            if (empty($tables)) {
                return true;
            }

            $table_ids = array_column($tables, 'ID');

            $failed_table_info = array();

            $migrated_table_info_option_name = 'tablesome_v0592_migrated_table_info';
            $failed_table_info_option_name = 'tablesome_v0592_failed_table_info';

            $migrated_table_info = \get_option($migrated_table_info_option_name);
            $migrated_table_info = !empty($migrated_table_info) ? json_decode($migrated_table_info, true) : [];
            error_log('[Get Migrated Table Info] : ' . print_r($migrated_table_info, true));

            foreach ($tables as $table) {

                error_log('[Table ID] : ' . $table->ID);
                error_log('[Table Status] : ' . $table->post_status);

                $already_migrated = (in_array($table->ID, $migrated_table_info));
                error_log('[Table ' . $table->ID . ' Is Already Migrated ? ' . ($already_migrated ? "yes" : "no"));

                /** Get Table Metadata  */
                $meta_data = get_post_meta($table->ID, 'tablesome_data', true);
                $columns = isset($meta_data['columns']) ? $meta_data['columns'] : [];

                /** Can't create a tablesomeDB table without meta_data */
                if (empty($columns)) {
                    error_log('[Empty Table Columns] ');
                    $migrated_table_info[] = $table->ID;
                    continue;
                }

                $db_table = $tablesome_db->create_table_instance($table->ID, $meta_data);

                /*** Get all records from DB({wp_prefix}_tablesome_records) */
                $rows = $crud->get_all_rows($table->ID, array()); /*** Continue the loop if the rows is empty */
                $old_table_rows_count = count($rows);
                error_log('[Existing Table Records count] : ' . $old_table_rows_count);

                $new_table_has_equal_or_more_records = ($db_table->count() >= $old_table_rows_count);

                $migrated = $new_table_has_equal_or_more_records;

                error_log('[Empty Records] : ' . ($old_table_rows_count == 0 ? "yes" : "no"));
                error_log('[new_table_has_equal_or_more_records ] : ' . ($new_table_has_equal_or_more_records ? "yes" : "no"));

                if ($old_table_rows_count == 0 || $migrated || $already_migrated) {
                    $migrated_table_info[] = $table->ID;
                    $this->migrate_image_link_option($table, $columns, $rows);
                    continue;
                }

                // Delete all the records if incorrectly records
                $should_truncate_the_new_table = (!$migrated && $db_table->count() > 0);

                if ($should_truncate_the_new_table) {
                    error_log('***  Table Truncated ***');
                    $db_table->truncate();
                }
                $records_inserted_count = 0;

                $records = array();
                foreach ($rows as $row) {
                    $content = isset($row->content) && !empty($row->content) ? json_decode($row->content, true) : [];
                    $rank_order = isset($row->rank_order) ? $row->rank_order : '';

                    $records[] = array(
                        'post_id' => $row->post_id,
                        'author_id' => $row->author_id,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'content' => $content,
                        'rank_order' => $rank_order,
                    );
                }

                $insert_info = $tablesome_db->insert_many($table->ID, $meta_data, $records);

                $records_inserted_count = isset($insert_info['records_inserted_count']) ? $insert_info['records_inserted_count'] : 0;

                $migration_done = ($old_table_rows_count == $db_table->count());

                if ($migration_done) {
                    $migrated_table_info[] = $table->ID;
                    error_log('**** Table ' . $table->ID . ' - migration done..  *****');
                } else {
                    error_log('**** Table ' . $table->ID . ' - migration failed.. *****');
                }
                error_log('[Migrated Records Count] : ' . $records_inserted_count);
            }

            $successfully_migrated = count(array_diff($table_ids, $migrated_table_info)) == 0 ? true : false;

            $failed_table_info = count($failed_table_info) > 0 ? json_encode($failed_table_info) : "";
            $migrated_table_info = count($migrated_table_info) > 0 ? json_encode(array_unique($migrated_table_info)) : "";

            update_option($failed_table_info_option_name, $failed_table_info);
            update_option($migrated_table_info_option_name, $migrated_table_info);

            return $successfully_migrated;
        }

        public function migrate_image_link_option($table, $columns, $rows)
        {
            if (empty($rows)) {return;}

            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $db_table = $tablesome_db->create_table_instance($table->ID, []);

            global $wpdb;

            $failed_image_link_info = array();
            foreach ($rows as $row) {
                $cells = isset($row->content) ? json_decode($row->content, true) : [];
                $rank_order = isset($row->rank_order) ? $row->rank_order : '';

                // Skip the current iteration if the cells & rank-order is empty
                if (empty($cells)) {continue;}

                $cell_index = 0;
                foreach ($cells as $cell_data) {
                    $cell_data_value = isset($cell_data['value']) ? $cell_data['value'] : '';
                    $cell_link_value = isset($cell_data['link']) ? $cell_data['link'] : '';

                    $column_id = isset($columns[$cell_index]['id']) ? $columns[$cell_index]['id'] : $cell_index;
                    $column_format = isset($columns[$cell_index]['format']) ? $columns[$cell_index]['format'] : 'text';

                    $cell_index++;

                    if ($column_format != 'file' || empty($cell_link_value)) {
                        continue;
                    }

                    $timestamp = current_time('timestamp');
                    $datetime = date('Y-m-d H:i:s', $timestamp);

                    $db_column_name = 'column_' . $column_id;
                    $db_meta_column_name = $db_column_name . '_meta';

                    $data = array();
                    $data[$db_meta_column_name] = esc_sql(json_encode(array('link' => $cell_link_value)));
                    $data['updated_at'] = $datetime;

                    $error_message = '';
                    try {

                        if (!empty($record_id)) {

                            $update = $wpdb->update(
                                $db_table->table_name,
                                $data,
                                array(
                                    'rank_order' => $rank_order,
                                ),
                                array('%s', '%s'),
                                array('%s')
                            );

                        } else if (empty($rank_order) && !empty($cell_data_value)) {
                            $where_clause = array();
                            $where_clause[$db_column_name] = $cell_data_value;

                            $update = $wpdb->update(
                                $db_table->table_name,
                                $data,
                                $where_clause,
                                array('%s', '%s'),
                                array('%d')
                            );
                        }
                    } catch (\Exception $e) {
                        $error_message = $e->getMessage();
                    }

                    if (!$update) {
                        $failed_image_link_info[$table->ID][] = array(
                            'record_id' => $row->record_id,
                            'error' => $error_message,
                        );
                    }
                }
            }

            $failed_image_link_info = !empty($failed_image_link_info) ? json_encode($failed_image_link_info) : "";
            update_option('tablesome_image_link_migration_v0592', $failed_image_link_info);
        }
    }
}