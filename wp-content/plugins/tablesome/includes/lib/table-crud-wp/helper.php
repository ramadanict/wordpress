<?php

namespace Tablesome\Includes\Lib\Table_Crud_WP;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Lib\Table_Crud_WP\Helper')) {
    class Helper
    {
        /**
         * Use of this method, Getting the table columns from the table meta-data
         *
         * @param [array] $meta_data
         * @return array
         */
        public function get_table_columns($meta_data)
        {
            $columns = array();
            if (!isset($meta_data['columns'])) {
                return $columns;
            }

            foreach ($meta_data['columns'] as $column) {
                $id = $column['id'];
                $columns[$id] = 'column_' . $id;
            }
            return $columns;
        }

        public function get_column_ided_record($meta_data, $record)
        {
            $columns = isset($meta_data['columns']) ? $meta_data['columns'] : [];
            $content = isset($record['content']) && !empty($record['content']) ? $record['content'] : [];

            /** First: Set the empty values to the db cells by columns */
            $cell_values = $this->get_column_ided_empty_record($columns);

            if (empty($content)) {return $cell_values;}

            $cell_index = 0;
            foreach ($record['content'] as $cell_data) {

                $cell_value = "";
                if (!is_array($cell_data)) {
                    $cell_value = $cell_data;
                }

                $cell_value = isset($cell_data['value']) ? $cell_data['value'] : $cell_value;
                $cell_html = isset($cell_data['html']) ? $cell_data['html'] : $cell_value;

                $cell_value = !empty($cell_value) ? addslashes($cell_value) : '';
                $cell_html = !empty($cell_html) ? addslashes($cell_html) : '';

                /** Get columnId from table meta-column by using the cell index*/
                $column_id = isset($columns[$cell_index]['id']) ? $columns[$cell_index]['id'] : $cell_index;

                // Column Format
                $column_format = isset($columns[$cell_index]['format']) ? $columns[$cell_index]['format'] : 'text';

                // DB Column Name
                $db_column_name = 'column_' . $column_id;
                $db_meta_column_name = $db_column_name . '_meta';

                $meta_columns = ($column_format == 'url' || $column_format == 'button' || $column_format == 'file');

                if ($meta_columns) {
                    // $cell_value = $this->get_converted_link_content($cell_data);

                    $cell_mata_args = array();

                    if ($column_format == 'file') {
                        $cell_mata_args['link'] = isset($cell_data['link']) ? $cell_data['link'] : '';
                    } else {
                        $cell_mata_args = array(
                            'linkText' => isset($cell_data['linkText']) ? $cell_data['linkText'] : '',
                            'value' => isset($cell_data['value']) ? $cell_data['value'] : '',
                        );

                    }
                    $cell_values[$db_meta_column_name] = esc_sql(json_encode($cell_mata_args, JSON_UNESCAPED_UNICODE));
                }

                // Should store the cell prop html value instead of value prop if the cell-format is textarea
                if ($column_format == 'textarea') {
                    $cell_value = $cell_html;
                }

                $cell_values[$db_column_name] = $cell_value;

                $cell_index++;
            }

            return $cell_values;
        }

        public function get_converted_link_content($cell_data)
        {
            $content = '';

            foreach ($cell_data as $cell_key => $cell_value) {
                $content .= '[' . $cell_key . ']';

                $cell_value = str_replace('(', 'TS_{', $cell_value);
                $cell_value = str_replace(')', 'TS_}', $cell_value);

                $content .= '(' . $cell_value . ')';
            }
            // $content = implode("||", $cell_data);
            return $content;
        }

        public function get_column_ided_empty_record($columns)
        {
            $record = [];
            foreach ($columns as $column) {
                $column_id = $column['id'];
                $column_format = isset($column['format']) ? $column['format'] : 'text';

                $db_column_name = 'column_' . $column_id;
                $db_meta_column_name = $db_column_name . '_meta';

                $meta_columns = ($column_format == 'url' || $column_format == 'button');

                if ($meta_columns) {
                    $record[$db_meta_column_name] = '';
                }

                $record[$db_column_name] = '';
            }
            return $record;
        }
    }
}