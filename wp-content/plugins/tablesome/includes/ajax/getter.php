<?php

namespace Tablesome\Includes\Ajax;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Ajax')) {
    class Getter
    {
        public function __construct()
        {
            $this->filters = new \Tablesome\Includes\Filters();
            $this->helpers = new \Tablesome\Includes\Helpers();
        }

        public function get_table_data_from_ajax()
        {
            $args = array();
            $tables_props = isset($_REQUEST['datas']) && !empty($_REQUEST['datas']) ? $_REQUEST['datas'] : [];
            if (empty($tables_props)) {
                return $args;
            }

            $tables_props = $this->filters->sanitizing_the_array_values($tables_props);
            foreach ($tables_props as $props) {
                $table_id = $props['tableID'];

                $args[$table_id] = array(
                    'table_id' => $table_id,
                    'page_limit' => $props['numOfRecordsPerPage'],
                    'exclude_columns_ids' => $props['excludeColumnIDs'],
                    'search' => $props['search'],
                    'hide_table_header' => $props['hideTableHeader'],
                    'show_serial_number_column' => $props['showSNoColumn'],
                    'sorting' => $props['sort'],
                    'filters' => $props['filter'],
                );
            }
            return $args;
        }

        public function get_table_collections_data($tables_props)
        {
            $table_data = [];
            if (empty($tables_props)) {
                return $table_data;
            }

            foreach ($tables_props as $table_id => $table_props) {
                $post_type = get_post_type($table_id);
                if ($post_type == TABLESOME_CPT) {
                    $exclude_column_ids = isset($table_props['exclude_columns_ids']) ? $table_props['exclude_columns_ids'] : [];
                    $hide_table_header = isset($table_props['hide_table_header']) ? $table_props['hide_table_header'] : 0;
                    $show_serial_number_column = isset($table_props['show_serial_number_column']) ? $table_props['show_serial_number_column'] : 0;
                    $page_limit = isset($table_props['page_limit']) ? $table_props['page_limit'] : 0;
                    $args = [
                        'table_id' => $table_id,
                        'exclude_column_ids' => $exclude_column_ids,
                        'page_limit' => $page_limit,
                        'search' => $table_props['search'],
                        'hide_table_header' => $hide_table_header,
                        'show_serial_number_column' => $show_serial_number_column,
                        'sorting' => $table_props['sorting'],
                        'filters' => $table_props['filters'],
                    ];
                    $table_controller = new \Tablesome\Components\Table\Controller();
                    $table_props = $table_controller->get_table_viewProps($args);
                    $table_data[$table_id] = $table_props;
                }
            }
            return $table_data;
        }

        public function get_tablesome_storing_data_props_from_ajax($data)
        {
            $props = [
                'post_id' => 0,
                'columns' => [],
                'records_deleted' => [],
                'records_inserted' => [],
                'records_updated' => [],
                'columns_deleted' => [],
                'columns_duplicated' => [],
                'columns_inserted' => [],
            ];

            if (isset($data['post_title']) && !empty($data['post_title'])) {
                $props['post_title'] = sanitize_text_field(wp_unslash($data['post_title']));
            }

            if (isset($data['post_id']) && !empty($data['post_id'])) {
                $props['post_id'] = sanitize_text_field(wp_unslash($data['post_id']));
            }

            if (isset($data['post_action']) && !empty($data['post_action'])) {
                $props['post_action'] = sanitize_text_field(wp_unslash($data['post_action']));
            }

            if (isset($data['columns']) && !empty($data['columns'])) {
                $props['columns'] = $this->filters->sanitizing_the_array_values(wp_unslash($data['columns']));
            }

            // if (isset($data['rows']) && !empty($data['rows'])) {
            //     $rows = $this->convert_rows_string_to_array($data['rows']);
            //     $props['rows'] = $this->sanitize_array(wp_unslash($rows));
            //     $props['rows'] = $this->filters->sanitizing_the_array_values(wp_unslash($data['rows']));
            // }

            if (isset($data['records_updated']) && !empty($data['records_updated'])) {
                $records = $this->convert_rows_string_to_array($data['records_updated']);
                $props['records_updated'] = $this->filters->sanitizing_the_array_values(wp_unslash($records));
            }

            if (isset($data['records_inserted']) && !empty($data['records_inserted'])) {
                $records = $this->convert_rows_string_to_array($data['records_inserted']);
                $props['records_inserted'] = $this->filters->sanitizing_the_array_values(wp_unslash($records));
            }

            if (isset($data['records_deleted']) && !empty($data['records_deleted'])) {
                $records = json_decode($data['records_deleted'], true);
                $props['records_deleted'] = $this->filters->sanitizing_the_array_values(wp_unslash($records));
            }

            if (isset($data['columns_deleted']) && !empty($data['columns_deleted'])) {
                $records = json_decode($data['columns_deleted'], true);
                $props['columns_deleted'] = $this->filters->sanitizing_the_array_values(wp_unslash($records));
            }

            if (!empty($props['post_id']) && (int) $props['post_id']) {
                $columns_inserted = $this->helpers->get_columns_to_be_inserted($props['columns']);
                $columns_inserted = $this->helpers->filter_duplicates_columns($columns_inserted, $props['columns_duplicated']);
                $props['columns_inserted'] = $columns_inserted;
            }

            return $props;
        }

        private function convert_rows_string_to_array($rows)
        {
            $converted_rows = [];
            if (isset($rows) && empty($rows)) {
                return $converted_rows;
            }
            foreach ($rows as $row) {
                $record_id = isset($row['record_id']) && !empty($row['record_id']) ? $row['record_id'] : 0;
                $rank_order = isset($row['rank_order']) && !empty($row['rank_order']) ? $row['rank_order'] : "";
                $row = json_decode($row['content'], true);

                $converted_rows[] = array(
                    'record_id' => $record_id,
                    'content' => $row,
                    'rank_order' => $rank_order,
                );
            }

            return $converted_rows;
        }
    }
}
