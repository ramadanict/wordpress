<?php

namespace Tablesome\Includes\Update;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\Tablesome\Includes\Update\Upgrade_List')) {
    class Upgrade_List
    {

        public function __construct()
        {
            $this->crud = new \Tablesome\Includes\Db\CRUD();
            $this->helpers = new \Tablesome\Includes\Helpers();
        }

        public function get_upgrades()
        {
            $upgrades = [
                '0.9.9' => 'upgrade_v099',
                '0.8.3' => 'upgrade_v083',
                '0.7.3' => 'upgrade_v073',
                '0.7' => 'upgrade_v07',
                '0.6.5' => 'upgrade_v065',
                '0.5.9.2' => 'upgrade_v0592',
                // '0.5.9.1' => 'upgrade_v0591', // Doesn't need to run the v0591 migration
                '0.5.8' => 'upgrade_v058',
                '0.4.1' => 'upgrade_v041',
                '0.4' => 'upgrade_v040',
                '0.2.6' => 'upgrade_v026',
                '0.0.2' => 'upgrade_v002',
                '0.2' => 'upgrade_v02',
            ];

            return $upgrades;
        }

        public function upgrade_v099()
        {
            $upgrade_v099_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.9.9") {
                return $upgrade_v099_done;
            }

            $tables = get_posts([
                'numberposts' => -1,
                'post_type' => TABLESOME_CPT,
                'post_status' => array('publish', 'pending', 'draft', 'trash'),
            ]);

            if (!isset($tables) || empty($tables)) {
                return $upgrade_v099_done;
            }
            $newColumnName = 'updated_by';
            global $wpdb;

            foreach ($tables as $table) {

                // table name
                $tableName = "{$wpdb->prefix}tablesome_table_{$table->ID}";

                // query for check table is exist or not
                $tableExist = $wpdb->get_var("SHOW TABLES LIKE '$tableName'");
                if (!$tableExist) {
                    continue;
                }

                // query for check column exist
                $query = "SHOW COLUMNS FROM {$tableName} where Field = '{$newColumnName}'";
                $results = $wpdb->get_results($query, ARRAY_A);

                if (count($results) > 0) {
                    continue;
                }
                // query for add new column `updated_by`
                $query = "ALTER TABLE {$tableName} ADD {$newColumnName} INT(11) NOT NULL DEFAULT 0 AFTER updated_at";
                $addCloumnResult = $wpdb->query($query);
                if (!$addCloumnResult) {
                    continue;
                }

                // update new column (`updated_by`) value with author_id
                $query = "UPDATE {$tableName} SET {$newColumnName} = author_id";
                $updateColumnResult = $wpdb->query($query);
                if ($updateColumnResult === false) {
                    $upgrade_v099_done = false;
                }
            }
            return $upgrade_v099_done;
        }

        public function upgrade_v083()
        {
            error_log('!!! upgrade_v083 processed !!!');

            $upgrade_v083_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.8.3") {
                return $upgrade_v083_done;
            }

            $tables = get_posts([
                'numberposts' => -1,
                'post_type' => TABLESOME_CPT,
            ]);

            if (!isset($tables) || empty($tables)) {
                return $upgrade_v083_done;
            }

            foreach ($tables as $table) {
                $tablesome_data = get_tablesome_data($table->ID);

                if (!isset($tablesome_data) || !is_array($tablesome_data)) {
                    $tablesome_data = [
                        "options" => [],
                        "columns" => [],
                        "meta" => [],
                    ];
                }

                $tablesome_data["options"]["style"]["style-mode"] = "global";
                set_tablesome_data($table->ID, $tablesome_data);
            }

            return $upgrade_v083_done;
        }

        public function upgrade_v073()
        {
            $upgrade_v073_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.7.3") {
                return $upgrade_v073_done;
            }

            $options = get_option(TABLESOME_OPTIONS);
            $options['enabled_all_forms_entries'] = false;
            update_option(TABLESOME_OPTIONS, $options);

            return $upgrade_v073_done;
        }

        public function upgrade_v07()
        {
            $upgrade_v07_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.7") {
                return $upgrade_v07_done;
            }

            $tables = get_posts([
                'numberposts' => -1,
                'post_type' => TABLESOME_CPT,
            ]);

            if (!isset($tables) || empty($tables)) {
                return $upgrade_v07_done;
            }

            foreach ($tables as $table) {
                $triggers_meta_data = get_tablesome_table_triggers($table->ID);

                if (!isset($triggers_meta_data) || empty($triggers_meta_data)) {
                    continue;
                }
                $can_update_the_trigger = false;

                // loop through the triggers
                foreach ($triggers_meta_data as $trigger_index => $trigger_meta_data) {

                    $actions = isset($trigger_meta_data['actions']) ? $trigger_meta_data['actions'] : [];
                    // Skip the iteration if the trigger action is empty
                    if (empty($actions)) {
                        continue;
                    }

                    foreach ($actions as $action_index => $action) {
                        $is_add_row_action = (isset($action['action_id']) && $action['action_id'] == 1);
                        // Skip it if the action is not add row action
                        if (!$is_add_row_action) {
                            continue;
                        }
                        $match_columns = isset($action['match_columns']) ? $action['match_columns'] : [];
                        for ($ii = 0; $ii < count($match_columns); $ii++) {
                            // set the "manual" to the detection-mode property
                            $match_columns[$ii]['detection_mode'] = 'manual';
                        }
                        // set the default props for the action
                        $actions[$action_index]['autodetect_enabled'] = false;
                        $actions[$action_index]['matchcolumns_enabled'] = false;
                        $actions[$action_index]['match_columns'] = $match_columns;
                        $can_update_the_trigger = true;
                    }
                    $triggers_meta_data[$trigger_index]['actions'] = $actions;
                }

                if ($can_update_the_trigger) {
                    set_tablesome_table_triggers($table->ID, $triggers_meta_data);
                }
            }
            return $upgrade_v07_done;
        }

        public function upgrade_v065()
        {
            $upgrade_v065_done = true;
            $tablesome_version = get_option("tablesome_version");
            error_log('[$tablesome_version] : ' . $tablesome_version);
            if ($tablesome_version === "0.6.5") {
                return $upgrade_v065_done;
            }

            $tables = get_posts([
                'numberposts' => -1,
                'post_type' => TABLESOME_CPT,
            ]);

            if (!isset($tables) || empty($tables)) {
                return $upgrade_v065_done;
            }

            $trigger_ids_by_integration = [
                'cf7' => 1,
                'wpforms' => 2,
                'elementor' => 3,
            ];

            foreach ($tables as $table) {
                $triggers_meta_data = get_tablesome_table_triggers($table->ID);

                if (!isset($triggers_meta_data) || empty($triggers_meta_data)) {
                    continue;
                }

                foreach ($triggers_meta_data as $index => $trigger_meta_data) {
                    $integration = $trigger_meta_data['integration'];
                    $trigger_id = $trigger_ids_by_integration[$integration];
                    $triggers_meta_data[$index]['trigger_id'] = $trigger_id;
                }
                error_log('$triggers_meta_data : ' . print_r($triggers_meta_data, true));
                set_tablesome_table_triggers($table->ID, $triggers_meta_data);
            }
            return $upgrade_v065_done;
        }

        public function upgrade_v0592()
        {

            $upgrade_v0592_done = true;

            $tablesome_version = get_option("tablesome_version");
            error_log('[$tablesome_version] : ' . $tablesome_version);
            if ($tablesome_version === "0.5.9.2") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v0592_done;
            }
            error_log('[upgrade_v0592]');

            $migrate_old_data = new \Tablesome\Includes\Modules\Migrate_Old_Crud_Data_To_TablesomeDB();
            $upgrade_v0592_done = $migrate_old_data->run();

            return $upgrade_v0592_done;
        }

        public function upgrade_v0591()
        {
            $upgrade_v0591_done = true;
            $tablesome_version = get_option("tablesome_version");
            error_log('[$tablesome_version] : ' . $tablesome_version);
            if ($tablesome_version === "0.5.9.1") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v0591_done;
            }
            error_log('[upgrade_v0591]');

            $migrate_old_data = new \Tablesome\Includes\Modules\Migrate_Old_Crud_Data_To_TablesomeDB();
            $migrate_old_data->run();

            return $upgrade_v0591_done;
        }

        public function upgrade_v058()
        {
            $upgrade_v058_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.5.8") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v058_done;
            }
            error_log('[upgrade_v058]');

            global $wpdb;
            $table_name = $wpdb->prefix . TABLESOME_RECORDS_TABLE_NAME;
            $wpdb->query("UPDATE $table_name SET `rank_order` = '0|0zzzzs:' WHERE `rank_order` = '0|000000:'");

            return $upgrade_v058_done;
        }

        public function upgrade_v041()
        {
            $upgrade_v41_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.4.1") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v41_done;
            }
            error_log('[upgrade_v041]');

            /** Use this option to show the Tablesome opt-in notice to the admin pages.( < v0.4.1)  */
            update_option("tablesome_opt_in_notices", 1);
            update_option("tablesome_can_track_events", 'disabled');

            return $upgrade_v41_done;
        }

        public function upgrade_v040()
        {
            $upgrade_v40_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.4") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v40_done;
            }
            error_log('[upgrade_v040]');

            global $wpdb;
            $table_name = $wpdb->prefix . TABLESOME_RECORDS_TABLE_NAME;
            $column_name = "rank_order";
            $column_query = "ALTER TABLE $table_name ADD $column_name varchar(255) NOT NULL DEFAULT ''";
            maybe_add_column($table_name, $column_name, $column_query);

            return $upgrade_v40_done;
        }

        public function upgrade_v026()
        {
            $upgrade_v026_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.2.6") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v026_done;
            }
            error_log('[upgrade_v026]');

            $tables = get_posts([
                'numberposts' => -1,
                'post_type' => TABLESOME_CPT,
            ]);

            if (!isset($tables) || empty($tables)) {
                return $upgrade_v026_done;
            }

            foreach ($tables as $table) {
                $this->run_migration_v026($table->ID);
            }

            return $upgrade_v026_done;
        }

        public function upgrade_v002()
        {
            $upgrade_v002_done = true;
            $tablesome_version = get_option("tablesome_version");
            $tablesome_upgrades = get_option("tablesome_upgrades");
            if ($tablesome_version === "0.0.2" || !empty($tablesome_upgrades)) {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v002_done;
            }
            error_log('[upgrade_v002]');

            // error_log('!!! upgrade_v002 in processing !!!');
            $tables = get_posts([
                'numberposts' => -1,
                'post_type' => TABLESOME_CPT,
            ]);

            if (!isset($tables) || empty($tables)) {
                // error_log('!!! upgrade_v002 done !!!');
                return $upgrade_v002_done;
            }

            foreach ($tables as $table) {
                $old_table_data = get_post_meta($table->ID, 'tablesome_data', true);
                $new_table_data = $this->run_migration_v002($old_table_data);
                update_post_meta($table->ID, 'tablesome_data', $new_table_data);
            }
            // error_log('!!! upgrade_v002 done !!!');
            return $upgrade_v002_done;
        }

        public function upgrade_v02()
        {
            $upgrade_v02_done = true;
            $tablesome_version = get_option("tablesome_version");
            $tablesome_upgrades = get_option("tablesome_upgrades");
            if ($tablesome_version === "0.2") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v02_done;
            }
            error_log('[upgrade_v02]');

            $tables = get_posts([
                'numberposts' => -1,
                'post_type' => TABLESOME_CPT,
            ]);

            $this->run_tablesome_records_table();
            foreach ($tables as $table) {
                $old_table_data = get_post_meta($table->ID, 'tablesome_data', true);
                $new_table_data = $this->run_migration_v02($table->ID, $old_table_data);
                update_post_meta($table->ID, 'tablesome_data', $new_table_data);
            }
            return $upgrade_v02_done;
        }

        public function run_migration_v026($table_id)
        {
            $records = $this->crud->get_all_rows($table_id);
            $records = $this->helpers->get_decoded_rows($records);

            foreach ($records as $record) {
                if (isset($record["content"]) && !empty($record["content"])) {
                    foreach ($record["content"] as $column_id => $cell) {
                        if (!is_array($cell)) {
                            $record["content"][$column_id] = ["value" => $cell];
                        }
                    }
                    $this->crud->update($table_id, $record["record_id"], $record["content"]);
                }
            }

            return true;
        }

        public function run_migration_v002($data)
        {
            $new_data = [
                'options' => [],
                'columns' => [],
                'rows' => [],
                'meta' => [
                    'last_column_id' => 0,
                ],
            ];

            if (!isset($data['columns']) || empty($data['columns']) || !isset($data['rows']) || empty($data['rows'])) {
                return $new_data;
            }

            $column_id = 0;
            foreach ($data['columns'] as $column_name) {
                $column_id++;
                $new_data['columns'][] = [
                    'id' => $column_id,
                    'name' => $column_name,
                    'format' => 'text',
                ];
            }

            foreach ($data['rows'] as $row) {
                $column_id = 0;
                $new_row = [];
                foreach ($row as $cell_key => $cell_value) {
                    $column_id++;
                    $new_row[$column_id] = $cell_value;
                }
                array_push($new_data['rows'], $new_row);
            }

            $new_data['meta']['last_column_id'] = $column_id;

            return $new_data;
        }

        public function run_tablesome_records_table()
        {
            $table = new \Tablesome\Includes\Db\Tablesome_Table();
            $table->create();
        }

        public function run_migration_v02($table_id, $old_table_data)
        {
            $old_rows = isset($old_table_data['rows']) ? $old_table_data['rows'] : [];
            if (empty($old_rows)) {
                return $old_table_data;
            }
            $table = new \Tablesome\Includes\Core\Table();
            foreach ($old_rows as $row) {
                $insert_row = $table->insert_row(
                    array(
                        'content' => $row,
                        'post_id' => $table_id,
                    )
                );
            }
            $old_table_data['rows'] = [];
            return $old_table_data;
        }

    } // END CLASS
}
