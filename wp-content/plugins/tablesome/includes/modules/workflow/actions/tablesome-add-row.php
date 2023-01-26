<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use Tablesome\Includes\Modules\Workflow\Action;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\Tablesome_Add_Row')) {
    class Tablesome_Add_Row extends Action
    {

        public $un_supported_fields = array(
            'vx_width',
            'vx_height',
            'vx_url',
        );

        public $column_formats = array(
            'textarea' => array('textarea', 'address', 'post_excerpt', 'rich_text_input'),
            'date' => array('date', 'date-time', 'input_date'),
            'email' => array('email', 'input_email'),
            'file' => array("upload", "file-upload", "file", "fileupload", "post_image", "input_image", "input_file"),
            'url' => array('url', 'input_url'),
            'number' => array('number-slider', 'rating', 'number', 'postdata', 'currency', 'calculation', 'quantity', 'input_number'),
        );

        public function get_config()
        {
            return array(
                'id' => 1,
                'name' => 'add_row',
                'label' => __('Add Row', 'tablesome'),
                'integration' => 'tablesome',
                'is_premium' => false,
            );
        }

        public function do_action($trigger_class, $trigger_instance)
        {
            error_log('*** Tablesome Add Row New Action Called  ***');

            $this->trigger_class = $trigger_class;
            $this->trigger_instance = $trigger_instance;

            $this->table_id = $trigger_instance['table_id'];
            $this->trigger_position = intval($trigger_instance['trigger_position']);
            $this->action_position = intval($trigger_instance['action_position']);

            $this->integration = $this->trigger_class->trigger_source_data['integration'];

            if (empty($this->table_id)) {
                return false;
            }

            $this->set_match_fields();

            $table_meta = get_tablesome_data($this->table_id);
            $triggers_meta = get_tablesome_table_triggers($this->table_id);

            $action_meta = isset($triggers_meta[$this->trigger_position]['actions'][$this->action_position]) ? $triggers_meta[$this->trigger_position]['actions'][$this->action_position] : array();
            $data = isset($this->trigger_class->trigger_source_data['data']) ? $this->trigger_class->trigger_source_data['data'] : [];

            if (empty($action_meta) || empty($table_meta) || empty($data)) {
                return;
            }

            $row_values = $this->get_row_values($action_meta, $table_meta, $data);
            if (empty($row_values)) {
                return;
            }

            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();

            $db_table = $tablesome_db->create_table_instance($this->table_id, $table_meta);
            $query = $tablesome_db->query(array(
                'table_id' => $this->table_id,
                'table_name' => $db_table->name,
            ));

            $default_record_values = $this->get_record_default($this->table_id);
            $insert_record_data = array_merge(array(), $default_record_values, $row_values);
            $result = $tablesome_db->insert($query, $insert_record_data);
            return $result;
        }

        public function get_record_default($table_id)
        {
            global $globalCurrentUserID;
            return array(
                'post_id' => $table_id,
                'author_id' => $globalCurrentUserID,
                'updated_by' => $globalCurrentUserID,
                'created_at' => current_time('mysql', true),
                'updated_at' => current_time('mysql', true),
                'rank_order' => '',
            );
        }

        public function get_extra_information()
        {
            $current_datetime = date('Y-m-d H:i:s');
            $unix_timestamp = strtotime($current_datetime);

            $values = array(
                'ip_address' => get_tablesome_ip_address(),
                'page_source_url' => get_tablesome_request_url(),
                'created_at_datetime' => $current_datetime,
                'created_at' => $unix_timestamp * 1000,
                'created_by' => get_current_user_id(),
            );

            return $values;
        }

        public function set_match_fields()
        {
            $this->set_match_columns_meta();
            $this->set_smart_fields_columns();
        }

        public function get_row_values($action_meta, $table_meta, $data)
        {
            $row_values = array();
            $match_columns = isset($action_meta['match_columns']) ? $action_meta['match_columns'] : [];

            if (empty($match_columns)) {
                return $row_values;
            }

            $table_columns = isset($table_meta['columns']) ? $table_meta['columns'] : [];

            foreach ($match_columns as $match_column_info) {

                $field_type = isset($match_column_info['field_type']) ? $match_column_info['field_type'] : '';
                if ($field_type == 'tablesome_smart_fields') {
                    continue;
                }

                /** Skip the iteration if the field-name or column-id prop doesn't exist in the match column array */
                if (!isset($match_column_info['field_name']) || !isset($match_column_info['column_id'])) {
                    continue;
                }

                $field_name = $match_column_info['field_name'];
                $column_id = $match_column_info['column_id'];

                $column_id_exists = $this->column_id_exists_in_table_meta($column_id, $table_meta);
                if (!$column_id_exists) {
                    continue;
                }
                $column_format = get_tablesome_cell_type($column_id, $table_columns);

                $field_value = isset($data[$field_name]['value']) ? $data[$field_name]['value'] : '';
                if ($column_format == 'date') {
                    $field_value = isset($data[$field_name]['unix_timestamp']) ? $data[$field_name]['unix_timestamp'] : '';
                }

                $db_column = "column_{$column_id}";
                $row_values[$db_column] = $field_value;
            }

            $smart_fields_values = $this->get_extra_information();

            foreach ($match_columns as $match_column_info) {
                $field_type = isset($match_column_info['field_type']) ? $match_column_info['field_type'] : '';
                $column_id = isset($match_column_info['column_id']) ? intval($match_column_info['column_id']) : '';
                $field_name = isset($match_column_info['field_name']) ? $match_column_info['field_name'] : '';
                $detection_mode = isset($match_column_info['detection_mode']) ? $match_column_info['detection_mode'] : '';

                if ($field_type != 'tablesome_smart_fields' || !$column_id || !$field_name || $detection_mode != 'enabled') {
                    continue;
                }

                $db_column = "column_{$column_id}";
                $field_value = isset($smart_fields_values[$field_name]) ? $smart_fields_values[$field_name] : '';
                $row_values[$db_column] = $field_value;
            }
            return $row_values;
        }

        public function column_id_exists_in_table_meta($column_id, $table_meta)
        {
            $exists = false;
            if (!isset($table_meta['columns']) || empty($table_meta['columns'])) {
                return $exists;
            }

            foreach ($table_meta['columns'] as $column) {
                if ($column_id == $column['id']) {
                    $exists = true;
                    break;
                }
            }
            return $exists;
        }

        public function set_match_columns_meta()
        {

            $triggers_meta = get_tablesome_table_triggers($this->table_id);

            if (!isset($this->trigger_instance['action_meta']['autodetect_enabled']) || !isset($this->trigger_instance['action_meta']['match_columns'])) {
                return;
            }

            $action_meta = $this->trigger_instance['action_meta'];

            $is_autodetect_enabled = isset($action_meta['autodetect_enabled']) && !empty($action_meta['autodetect_enabled']) ? $action_meta['autodetect_enabled'] : false;

            if (!$is_autodetect_enabled) {
                return;
            }
            $table_meta = get_tablesome_data($this->table_id);
            $last_column_id = $table_meta['meta']['last_column_id'];

            $trigger_meta = isset($triggers_meta[$this->trigger_position]) ? $triggers_meta[$this->trigger_position] : array();

            $matched_colums = isset($action_meta['match_columns']) ? $action_meta['match_columns'] : [];

            /**
             * @version v0.8.6
             * Match Column Compatible v0.8.5
             * For adding the missing properties
             */
            $modified_match_columns = $this->get_modified_match_columns($matched_colums);

            // when auto-detection_enabled and new form field found in trigger data we add that field into match_column_meta
            $modified_fields = $this->get_updated_match_fields($modified_match_columns);

            // return if the modified fields are empty
            if (empty($modified_fields)) {
                return;
            }

            foreach ($modified_fields as $modified_field) {
                // Check the field is matched in other triggers
                $is_field_exist = $this->is_current_field_exist_in_other_trigger_fields($modified_field['name']);

                $field_type = $modified_field['type'];

                if (!$is_field_exist) {
                    $last_column_id = $last_column_id + 1;

                    // Add new column to the table meta
                    $table_meta['columns'][] = array(
                        'id' => $last_column_id,
                        'name' => $modified_field['label'],
                        'format' => $this->get_column_format_by_field_type($field_type),
                    );

                }

                $column_id = $is_field_exist ? $is_field_exist : $last_column_id;

                /**
                 *  1. if return value is "NO_NEED" it means don't need to update the value to the existing match columns meta. Instead, Should add a new meta column set
                 *  2. if the return value is a valid integer it means the value will be considered as a match column index. Should update match column meta properties by this value.
                 */
                $should_add_new_match_field_set = $this->should_add_new_match_field_set($modified_field['name'], $matched_colums);

                if ('NO_NEED' !== $should_add_new_match_field_set) {
                    $match_column_index = $should_add_new_match_field_set;

                    $trigger_meta['actions'][$this->action_position]['match_columns'][$match_column_index]['column_id'] = $column_id;
                    $trigger_meta['actions'][$this->action_position]['match_columns'][$match_column_index]['column_status'] = 'published';
                    $trigger_meta['actions'][$this->action_position]['match_columns'][$match_column_index]['detection_mode'] = 'manual';

                } else {
                    // Add new matching columns to the trigger meta
                    $trigger_meta['actions'][$this->action_position]['match_columns'][] = array(
                        'column_id' => $column_id,
                        'column_status' => 'published',
                        'detection_mode' => 'manual',
                        'field_name' => strval($modified_field['name']), // Convert the field value to string type if it's in integer type.
                        'field_type' => 'trigger_source',
                    );

                }

            }

            // Update the last-column-id value to the table meta
            $table_meta['meta']['last_column_id'] = $last_column_id;

            if (!isset($triggers_meta[$this->trigger_position])) {
                return;
            }

            // Update the modified table-postmeta data.
            set_tablesome_data($this->table_id, $table_meta);
            // Store new fields in DB.
            $helper = new \Tablesome\Includes\Lib\Table_Crud_WP\Helper();
            $table_columns = $helper->get_table_columns($table_meta);

            $db_table = new \Tablesome_Table(array(
                'table_name' => "tablesome_table_{$this->table_id}",
            ));
            $db_table->modify_the_table($table_meta, $table_columns, []);

            // Update the trigger meta.
            $triggers_meta[$this->trigger_position] = []; // reset the trigger meta by trigger position
            $triggers_meta[$this->trigger_position] = $trigger_meta; // update the trigger meta by trigger position
            set_tablesome_table_triggers($this->table_id, $triggers_meta); // update the triggers meta in DB
        }

        public function get_updated_match_fields($match_columns)
        {
            $modified_fields = array();

            $integration = $this->trigger_class->trigger_source_data['integration'];
            $data = $this->trigger_class->trigger_source_data['data'];

            foreach ($data as $field_name => $field) {

                $field_name_exists = $this->field_name_exists_in_match_columns($field_name, $match_columns, $integration);

                if (!$field_name_exists) {

                    $modified_fields[] = array(
                        'name' => $field_name,
                        'label' => $field['label'],
                        'type' => $field['type'],
                    );
                }
            }

            if (empty($modified_fields)) {
                return [];
            }

            foreach ($modified_fields as $index => $modified_field) {
                // Remove the field from the modified-fields array if these fields are added by 3-rd party plugin
                if (in_array($modified_field['name'], $this->un_supported_fields, true)) {
                    unset($modified_fields[$index]);
                }
            }

            $modified_fields = count($modified_fields) > 0 ? array_values($modified_fields) : [];
            return $modified_fields;
        }

        public function field_name_exists_in_match_columns($field_name, $match_columns, $integration)
        {
            $exists = false;
            if (empty($match_columns)) {
                return $exists;
            }

            foreach ($match_columns as $match_column_info) {
                $field_type = isset($match_column_info['field_type']) ? $match_column_info['field_type'] : '';
                $column_id = isset($match_column_info['column_id']) ? intval($match_column_info['column_id']) : 0;
                $column_status = isset($match_column_info['column_status']) ? $match_column_info['column_status'] : '';

                if ($field_type == 'tablesome_smart_fields' || !isset($match_column_info['field_name'])) {
                    continue;
                }

                $configured_field_name = $match_column_info['field_name'];

                if ($integration == 'wpforms') {
                    $field_name = (int) $field_name;
                    $configured_field_name = (int) $match_column_info['field_name'];
                }

                if (($field_name == $configured_field_name) && $column_id != 0 && $column_status == 'published') {
                    $exists = true;
                    break;
                }
            }
            return $exists;
        }

        public function is_current_field_exist_in_other_trigger_fields($field_name)
        {
            $triggers_meta = get_tablesome_table_triggers($this->table_id);
            $exists = false;

            foreach ($triggers_meta as $trigger_position => $trigger) {

                // Skip, If the trigger iteration index and the current trigger position are same.
                if ($this->trigger_position == $trigger_position) {
                    continue;
                }

                $trigger_id = isset($trigger['trigger_id']) ? $trigger['trigger_id'] : 0;
                $actions = isset($trigger['actions']) ? $trigger['actions'] : [];

                if (empty($trigger_id) || empty($actions)) {
                    continue;
                }

                foreach ($actions as $action) {
                    $action_id = $action['action_id'];
                    $match_columns = isset($action['match_columns']) ? $action['match_columns'] : [];

                    if ($action_id != 1 || empty($match_columns)) {
                        continue;
                    }

                    foreach ($match_columns as $match_column_info) {
                        $column_id = isset($match_column_info['column_id']) ? $match_column_info['column_id'] : '';
                        if (isset($match_column_info['field_name']) && $field_name == $match_column_info['field_name'] && !empty($column_id)) {
                            $exists = $column_id;
                            break;
                        }
                    }

                }
            }
            return $exists;
        }

        public function get_column_format_by_field_type($field_type)
        {
            $format = 'text';
            foreach ($this->column_formats as $column_format => $field_types) {
                if (in_array($field_type, $field_types)) {
                    $format = $column_format;
                    break;
                }
            }
            return $format;
        }

        public function set_smart_fields_columns()
        {

            // return if the table id is 0
            if (empty($this->table_id)) {
                return;
            }

            $table_meta = get_tablesome_data($this->table_id);
            $triggers_meta = get_tablesome_table_triggers($this->table_id);

            $trigger_meta = isset($triggers_meta[$this->trigger_position]) ? $triggers_meta[$this->trigger_position] : [];
            $action_meta = isset($trigger_meta['actions'][$this->action_position]) ? $trigger_meta['actions'][$this->action_position] : array();
            $match_columns = isset($action_meta['match_columns']) ? $action_meta['match_columns'] : array();

            if (empty($action_meta) || empty($match_columns)) {
                return;
            }

            $last_column_id = $table_meta['meta']['last_column_id'];
            $new_column_ids = [];
            foreach ($match_columns as $match_column_index => $match_column) {
                $field_type = isset($match_column['field_type']) ? $match_column['field_type'] : '';
                $detection_mode = isset($match_column['detection_mode']) ? $match_column['detection_mode'] : 'auto';
                $column_status = isset($match_column['column_status']) ? $match_column['column_status'] : '';
                $column_id = isset($match_column['column_id']) ? $match_column['column_id'] : '';

                if ($field_type != 'tablesome_smart_fields') {
                    continue;
                }

                if ($column_status == 'pending' && $column_id == 0 && $detection_mode == 'enabled') {
                    $last_column_id = $last_column_id + 1;
                    $smart_field = get_tablesome_smart_field_info_by_field_name($match_column['field_name']);

                    $table_meta['columns'][] = array(
                        'id' => $last_column_id,
                        'name' => $smart_field['column_label'],
                        'format' => $smart_field['column_format'],
                    );

                    // Collect the newely created column-ids and their corresponding property index
                    $new_column_ids[] = array(
                        'column_id' => $last_column_id,
                        'match_column_index' => $match_column_index,
                    );
                }
            }

            if (empty($new_column_ids)) {
                return;
            }

            $table_meta['meta']['last_column_id'] = $last_column_id;

            $table_meta = set_tablesome_data($this->table_id, $table_meta);

            // Store new fields in DB.
            $helper = new \Tablesome\Includes\Lib\Table_Crud_WP\Helper();
            $table_columns = $helper->get_table_columns($table_meta);

            $db_table = new \Tablesome_Table(array(
                'table_name' => "tablesome_table_{$this->table_id}",
            ));
            $db_table->modify_the_table($table_meta, $table_columns, []);

            foreach ($new_column_ids as $new_column_data) {
                $column_name = "column_{$new_column_data['column_id']}";
                $column_id_exists = $db_table->column_exists($column_name);
                if ($column_id_exists) {
                    $trigger_meta['actions'][$this->action_position]['match_columns'][$new_column_data['match_column_index']]['column_id'] = $new_column_data['column_id'];
                    $trigger_meta['actions'][$this->action_position]['match_columns'][$new_column_data['match_column_index']]['column_status'] = 'published';
                }
            }

            $triggers_meta[$this->trigger_position] = []; // reset the trigger meta by trigger position
            $triggers_meta[$this->trigger_position] = $trigger_meta; // update the trigger meta by trigger position
            $triggers_meta = set_tablesome_table_triggers($this->table_id, $triggers_meta);

        }

        /**
         * Get match column index
         *
         * @param [integer,string] $field_name (boolean, string)
         * @param [array] $match_columns
         * @return integer,string
         */
        private function should_add_new_match_field_set($field_name, $match_columns)
        {

            foreach ($match_columns as $match_column_index => $match_column_info) {

                $field_type = isset($match_column_info['field_type']) ? $match_column_info['field_type'] : '';
                $column_id = isset($match_column_info['column_id']) ? intval($match_column_info['column_id']) : 0;
                $column_status = isset($match_column_info['column_status']) ? $match_column_info['column_status'] : '';

                if ($field_type == 'tablesome_smart_fields' || !isset($match_column_info['field_name'])) {
                    continue;
                }

                $is_new_field = ($column_id == 0 && $column_status == 'pending');

                $configured_field_name = $match_column_info['field_name'];

                if ($this->integration == 'wpforms') {
                    $field_name = (int) $field_name;
                    $configured_field_name = (int) $match_column_info['field_name'];
                }

                if (($field_name == $configured_field_name) && $is_new_field) {
                    $index = $match_column_index;
                    break;
                }
            }

            return isset($index) ? intval($index) : 'NO_NEED';
        }

        private function get_modified_match_columns($match_colums)
        {
            $new_match_columns = [];
            foreach ($match_colums as $match_column) {

                if (!isset($match_column["field_type"])) {
                    $match_column['field_type'] = 'trigger_source';
                }

                if (isset($match_column["detection_mode"]) &&
                    $match_column["detection_mode"] == "auto"
                ) {
                    $match_column["detection_mode"] = "manual";
                }

                if (isset($match_column['field_type']) && $match_column['field_type'] == 'trigger_source') {
                    $match_column['column_status'] = 'published';
                }

                $new_match_columns[] = $match_column;
            }
            return $new_match_columns;
        }
    }

}
