<?php

namespace Tablesome\Components\Import;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

if (!class_exists('\Tablesome\Components\Import\Model')) {

    class Model
    {
        public $read_first_row_as_column = true;

        public function __construct()
        {
            $this->table = new \Tablesome\Includes\Core\Table();
            $this->file_handler = new \Tablesome\Components\Import\File_Handler();
            $this->crud = new \Tablesome\Includes\Db\CRUD();
        }

        public function import_data($props)
        {
            // create a post
            $post_id = $this->create_post($props);
            $props['post_id'] = $post_id;

            // uploading the attachment and get the id
            $attachment_id = $this->file_handler->uploading_the_file($props);
            $props['attachment_id'] = $attachment_id;

            // Set read first row as column
            $read_first_row_as_column = isset($props['read_first_row_as_column']) ? $props['read_first_row_as_column'] : 0;
            $this->read_first_row_as_column = filter_var($read_first_row_as_column, FILTER_VALIDATE_BOOLEAN);

            // ini_set('memory_limit', '2048M');
            $data = $this->import(get_attached_file($attachment_id), $post_id);

            // Set props
            $props['columns'] = $data['columns'];
            // $props["rows"] = $data['rows'];
            unset($data);

            // store table meta data
            $this->table->set_table_meta_data($props['post_id'], array(
                'columns' => $props['columns'],
                'rows' => [],
            ));

            // store table records
            // $this->crud->bulk_inserting($props['post_id'], $props);

            return $props;
        }

        public function import($attachment_url, $post_id)
        {
            if (!file_exists($attachment_url)) {
                return new \WP_Error('file_exception', __("File not exist", "tablesome"));
            }

            if (!version_compare(PHP_VERSION, '7.2', '>=')) {
                return new \WP_Error('php_version_exception', __("Table import will not be working on sites using PHP versions below PHP 7.2", "tablesome"));
            }

            $reader = ReaderEntityFactory::createReaderFromFile($attachment_url);
            $reader->open($attachment_url);
            $sheets = $reader->getSheetIterator();

            $props = [
                'columns' => [],
                'rows_count' => 0,
            ];

            $current_batch_no = 1;
            $record_counter = 0;
            foreach ($sheets as $sheet) {
                $rows = $sheet->getRowIterator();
                $assumptions = $this->get_assumptions($rows);
                foreach ($rows as $index => $row) {
                    $cells = $row->getCells();
                    if ($index == 1) {
                        $props['columns'] = $index == 1 && $this->read_first_row_as_column == true ? $this->get_column_cells($cells, false, $assumptions) : $this->get_column_cells($cells, true, $assumptions);
                    }

                    if ($this->read_first_row_as_column && $index == 1) {
                        continue;
                    }

                    $props["rows"][] = $this->get_row_cells($cells, $props["columns"], $assumptions);
                    $end_row_index = ($current_batch_no * TABLESOME_BATCH_SIZE) - 1;

                    if ($index == $end_row_index) {
                        // error_log('current_batch_no : ' . print_r($current_batch_no, true));
                        $current_batch_no++;
                        $this->crud->bulk_inserting($post_id, $props);
                        unset($props['rows']);
                        // error_log('after current_batch_no : ' . print_r($current_batch_no, true));
                        // error_log(' end_row_index : ' . print_r($end_row_index, true));
                        // $this->print_memory_usage('after batch ' . $current_batch_no);
                    }

                    $record_counter++;

                    if ($record_counter == TABLESOME_MAX_RECORDS_TO_READ) {
                        break;
                    }
                }
            }
            $reader->close();

            if (isset($props["rows"]) && !empty($props["rows"]) && $record_counter < $end_row_index) {
                $this->crud->bulk_inserting($post_id, $props);
                // error_log('row Count : ' . print_r(count($props["rows"]), true));
                // error_log('Left out !!');
                unset($props['rows']);
            }

            $props["rows_count"] = $record_counter;

            return $props;
        }

        public function get_assumptions($rows_obj)
        {
            $assumptions = [
                "highest_cells_count" => 0,
                "column_format_types" => [],
            ];

            $spout_lib_types = ["number", "text", "textarea", "text", "number", "date", "text"];
            $rows = [];
            $row_count = 0;
            foreach ($rows_obj as $row_index => $row_obj) {
                $row_count++;
                $cells_obj = $row_obj->getCells();
                $assumptions['highest_cells_count'] = $assumptions['highest_cells_count'] < count($cells_obj) ? count($cells_obj) : $assumptions['highest_cells_count'];

                $row = [];
                foreach ($cells_obj as $cell_obj) {
                    array_push($row, $spout_lib_types[$cell_obj->getType()]);
                }
                array_push($rows, $row);

                if ($row_count == 10) {
                    break;
                }
            }

            $format_types = [];
            $format_types_array = [];
            for ($ii = 0; $ii < $assumptions["highest_cells_count"]; $ii++) {
                foreach ($rows as $row_index => $row) {
                    // error_log('row_index : ' . $row_index);
                    if (isset($row[$ii])) {
                        $format_types_array[$ii][$row_index] = $row[$ii];
                    }
                }
                $assumptions["column_format_types"][$ii] = $this->get_column_type($format_types_array[$ii]);
            }
            // error_log('assumptions : ' . print_r($assumptions, true));

            return $assumptions;
        }

        public function get_column_cells($columns_obj, $is_empty = false, $assumptions = [])
        {
            $columns = [];

            for ($column_index = 0; $column_index < $assumptions["highest_cells_count"]; $column_index++) {
                $column_id = $column_index + 1;
                $format_type = isset($assumptions["column_format_types"][$column_index]) ? $assumptions["column_format_types"][$column_index] : "text";
                // $format_type = $this->convert_format_type($format_type);

                if ($is_empty) {
                    array_push($columns, [
                        'id' => $column_id,
                        'format' => $format_type,
                        'name' => '',
                    ]);
                } else {
                    array_push($columns, [
                        'id' => $column_id,
                        'format' => $format_type,
                        'name' => isset($columns_obj[$column_index]) ? $columns_obj[$column_index]->getValue() : "",
                    ]);
                }
            }
            // error_log('columns : ' . print_r($columns, true));

            return $columns;
        }

        public function get_row_cells($row_obj, $columns, $assumptions)
        {
            $cells = [];

            for ($column_index = 0; $column_index < $assumptions["highest_cells_count"]; $column_index++) {
                $column_id = $column_index + 1;
                $cell_value = isset($row_obj[$column_index]) ? $row_obj[$column_index]->getValue() : "";
                $cell_html = isset($row_obj[$column_index]) ? $row_obj[$column_index]->getValue() : "";

                if ($columns[$column_index]["format"] == "date") {
                    // error_log('cell_value : ' . print_r($cell_value, true));

                    $cell_value = isset($cell_value) && !empty($cell_value) && !is_string($cell_value) ? $cell_value->getTimestamp() * 1000 : $cell_value;
                    $cell_html = $cell_value;
                }

                $cells[$column_id] = [
                    'value' => $cell_value,
                    'html' => $cell_html,
                ];
            }

            // error_log('cells : ' . print_r($cells, true));
            // error_log('cells encode : ' . print_r(json_encode($cells, JSON_PARTIAL_OUTPUT_ON_ERROR), true));

            return json_encode($cells, JSON_PARTIAL_OUTPUT_ON_ERROR);
        }

        public function get_column_type($single_column_cells)
        {
            $counts = array_count_values($single_column_cells);
            arsort($counts);
            $type = key($counts);

            return $type;
        }

        public function convert_format_type($format_type)
        {
            switch ($format_type) {
                case 'integer':
                    $type = "number";
                    break;
                case 'string':
                    $type = "text";
                    break;
                case 'date':
                    $type = "date";
                    break;
                case 'datetime':
                    $type = "date";
                    break;
                case 'time':
                    $type = "date";
                    break;
                case 'float':
                    $type = "number";
                    break;
                default:
                    $type = "text";
                    break;
            }

            return $type;
        }

        public function create_post($props)
        {
            $post_data = array(
                'post_title' => $props['table_title'],
                'post_type' => TABLESOME_CPT,
                'post_content' => '',
                'post_status' => 'publish',
            );
            $post_id = 0;
            $post_id = $this->table->insert_or_update_post($post_id, $post_data);
            if (empty($post_id)) {
                $response = array(
                    'status' => 'failed', 'message' => __("Unable to create a post.", "tablesome"),
                );
                wp_send_json($response);
                wp_die();
            }
            return $post_id;
        }

        public function print_memory_usage($label = "Memory Usage")
        {
            $after_get_the_table_record = memory_get_usage();
            $mem_usage_in_mb = round($after_get_the_table_record / 1048576, 2);
            error_log($label . ' - ' . $mem_usage_in_mb);
        }

        public function isTimestamp($string)
        {
            try {
                new \DateTime('@' . $string);
            } catch (Exception $e) {
                return false;
            }
            return true;
        }
    }
}