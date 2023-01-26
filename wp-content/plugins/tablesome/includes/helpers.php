<?php

namespace Tablesome\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Helpers')) {
    class Helpers
    {
        public function get_IDed_columns($data, $given_meta)
        {
            $given_columns = $given_meta["columns"];
            $columns = $given_columns;

            $given_last_column_id = $given_meta["meta"]["last_column_id"];
            $last_column_id = isset($data['meta']['last_column_id']) && !empty($data['meta']['last_column_id']) ? $data['meta']['last_column_id'] : 0;
            $last_column_id = $given_last_column_id > $last_column_id ? $given_last_column_id : $last_column_id;

            $iterator = 0;
            foreach ($given_columns as $column) {
                if (!isset($column['id']) || empty($column['id'])) {
                    $last_column_id++;
                    $column_id = $last_column_id;
                }

                $column_id = isset($column_id) ? $column_id : $column['id'];
                $column_format = isset($column['format']) && !empty($column['format']) ? $column['format'] : 'text';

                $columns[$iterator]["id"] = $column_id;
                $columns[$iterator]["format"] = $column_format;

                unset($column_id);
                $iterator++;
            }

            return [
                'columns' => $columns,
                'last_column_id' => $last_column_id,
            ];
        }

        public function get_IDed_rows(array $given_columns, array $given_rows)
        {
            $rows = [];

            if (empty($given_columns) || empty($given_rows)) {
                return $rows;
            }
            foreach ($given_rows as $row_key => $row) {
                foreach ($given_columns as $column_key => $column) {
                    if (isset($row[$column_key])) {
                        $rows[$row_key][$column['id']] = $row[$column_key];
                    }
                }
            }
            return $rows;
        }

        public function get_IDed_row(array $Ided_columns, array $row)
        {
            /** If row is empty then, return the empty Ided row values */
            if (empty($row)) {
                $Ided_row = $this->get_empty_Ided_row($Ided_columns);
                return $Ided_row;
            }

            $Ided_row = $this->get_IDed_rows($Ided_columns, [$row]);
            $Ided_row = isset($Ided_row[0]) && !empty($Ided_row[0]) ? $Ided_row[0] : [];

            return $Ided_row;
        }

        public function get_empty_Ided_row(array $given_columns)
        {
            $row = [];
            foreach ($given_columns as $column_key => $column) {
                $row[$column['id']] = '';
            }
            return $row;
        }

        public function get_decoded_rows($rows)
        {
            $decoded_rows = [];
            if (empty($rows)) {
                return $decoded_rows;
            }
            foreach ($rows as $row) {
                $rank_order = isset($row->rank_order) && !empty($row->rank_order) ? $row->rank_order : "";
                $decoded_rows[] = array(
                    'record_id' => $row->record_id,
                    'content' => json_decode($row->content, true),
                    'rank_order' => $rank_order,
                );
                // $rows[] = json_decode($data->content, true);
            }
            return $decoded_rows;
        }

        public function get_columns_to_be_inserted($requested_columns)
        {
            $columns = [];
            foreach ($requested_columns as $column_order => $requested_column) {
                $column_id = isset($requested_column['id']) ? $requested_column['id'] : 0;
                if (empty($column_id) && $column_id == 0) {
                    $columns[$column_order] = array(
                        'index' => $column_order,
                        'name' => isset($requested_column['name']) ? $requested_column['name'] : '',
                        'format' => isset($requested_column['format']) ? $requested_column['format'] : 'text',
                    );
                }
            }
            return $columns;
        }

        public function filter_duplicates_columns($columns_inserted, $columns_duplicated)
        {
            if (empty($columns_duplicated)) {
                return $columns_inserted;
            }
            /** remove duplicates columns from the original columns data */
            foreach ($columns_duplicated as $duplicate_column) {
                $column_index = $duplicate_column['index'];
                if (isset($columns_inserted[$column_index])) {
                    unset($columns_inserted[$column_index]);
                }
            }
            return $columns_inserted;
        }

        public function get_date_fns_js_compatible_with_wp($pattern)
        {
            $pattern_charecters = str_split($pattern);
            $new_pattern_charecters = [];
            foreach ($pattern_charecters as $charecter) {

                switch ($charecter) {
                    case 'j':
                        $new_charecter = "d";
                        break;
                    case 'd':
                        $new_charecter = "dd";
                        break;
                    case 'l':
                        $new_charecter = "EEEE";
                        break;
                    case 'D':
                        $new_charecter = "EEE";
                    case 'm':
                        $new_charecter = "MM";
                        break;
                    case 'm':
                        $new_charecter = "M";
                        break;
                    case 'F':
                        $new_charecter = "MMMM";
                        break;
                    case 'M':
                        $new_charecter = "MMM";
                        break;
                    case 'y':
                        $new_charecter = "yy";
                        break;
                    case 'Y':
                        $new_charecter = "yyyy";
                        break;

                    default:
                        $new_charecter = $charecter;
                        break;
                }

                array_push($new_pattern_charecters, $new_charecter);
            }

            $new_pattern_charecters = join("", $new_pattern_charecters);
            // error_log('new_pattern_charecters : ' . print_r($new_pattern_charecters, true));

            return $new_pattern_charecters;
        }

        public function get_plugins_data()
        {
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            // Get all plugin info
            $plugins = get_plugins();

            // Get active plugin info
            $active_plugins = get_option('active_plugins');

            foreach ($plugins as $plugin_path => $plugin_info) {
                // Check if the plugin is active or not
                $is_active = in_array($plugin_path, $active_plugins) ? 1 : 0;
                $plugins[$plugin_path]['is_active'] = $is_active;
            }
            return $plugins;
        }
    }
}
