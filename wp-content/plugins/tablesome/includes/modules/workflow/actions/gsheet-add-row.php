<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use Tablesome\Includes\Modules\Workflow\Action;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\GSheet_Add_Row')) {
    class GSheet_Add_Row extends Action
    {
        public $gsheet_api;
        public $gsheet_integration_handler;
        public function __construct()
        {
            $this->gsheet_api = new \Tablesome\Includes\Modules\Workflow\External_Apis\GSheet();
            $this->gsheet_integration_handler = new \Tablesome\Includes\Modules\Workflow\Integrations\GSheet();
        }

        // TODO: get config from json instead of declaring in class file.
        public function get_config()
        {
            return array(
                'id' => 12,
                'name' => 'gsheet_add_row',
                'label' => __('Add Row', 'tablesome'),
                'integration' => 'gsheet',
                'is_premium' => true,
            );
        }

        public function do_action($trigger_class, $trigger_instance)
        {
            $action_meta = $trigger_instance['action_meta'];
            $spreadsheet_id = isset($action_meta['spreadsheet_id']) ? $action_meta['spreadsheet_id'] : '';
            $sheet_id = isset($action_meta['sheet_id']) ? $action_meta['sheet_id'] : ''; // sheet always start with 0.
            $trigger_source_data = isset($trigger_class->trigger_source_data['data']) ? $trigger_class->trigger_source_data['data'] : [];
            $map_fields = isset($action_meta['map_fields']) ? $action_meta['map_fields'] : [];
            // error_log('$spreadsheet_id : ' . $spreadsheet_id);
            // error_log('$map_fields : ' . print_r($map_fields, true));
            // error_log('$trigger_source_data : ' . print_r($trigger_source_data, true));
            if (empty($map_fields) || empty($spreadsheet_id) || empty($trigger_source_data)) {
                return;
            }

            $trigger_source_data = $this->get_attachment_url_added_trigger_data($trigger_source_data);

            $trigger_record = $this->get_record($map_fields, $trigger_source_data);
            if (empty($trigger_record)) {
                return;
            }

            // error_log('$sheet_id : ' . print_r($sheet_id, true));
            $sheet_name = $this->get_sheet_name($spreadsheet_id, $sheet_id);
            // error_log('$sheet_name : ' . print_r($sheet_name, true));
            if (empty($sheet_name)) {
                return;
            }
            $values = [$trigger_record];

            $result = $this->gsheet_integration_handler->add_records_to_sheet([
                'spreadsheet_id' => $spreadsheet_id,
                'sheet_name' => $sheet_name,
                'values' => $values,
                'range' => $this->get_range($action_meta),
            ]);

            $updated_records_count = isset($result['updates']['updatedRows']) ? $result['updates']['updatedRows'] : 0;

            return $updated_records_count == 1;
        }

        private function get_record($map_fields, $trigger_source_data)
        {
            $record = [];
            foreach ($map_fields as $map_field) {
                $destination_id = isset($map_field['destination_field']['id']) ? $map_field['destination_field']['id'] : '';
                $record[$destination_id] = $this->get_cell_value($map_field, $trigger_source_data);
            }
            // fill empty cells with null
            $record = $this->fill_empty_cells($record);
            return $record;
        }

        private function get_cell_value($map_field, $trigger_source_data)
        {
            $object_type = isset($map_field['source_field']['object_type']) ? $map_field['source_field']['object_type'] : 'trigger_source';
            $source_field_id = isset($map_field['source_field']['id']) ? $map_field['source_field']['id'] : '';
            $value = isset($trigger_source_data[$source_field_id]['value']) ? $trigger_source_data[$source_field_id]['value'] : '';

            if ($object_type == 'trigger_smart_fields') {
                $value = $this->get_smart_field_value($source_field_id);
            }
            return $value;
        }

        private function get_smart_field_value($smart_field_id)
        {
            $smart_fields = get_tablesome_smart_field_values();
            return isset($smart_fields[$smart_field_id]) ? $smart_fields[$smart_field_id] : '';
        }

        private function get_sheet_name($spreadsheet_id, $sheet_id)
        {
            $sheet_name = '';
            $sheets = $this->gsheet_integration_handler->get_sheets_by_spreadsheet_id($spreadsheet_id);

            if (empty($sheets)) {
                return '';
            }
            foreach ($sheets as $sheet) {
                if ($sheet['id'] == $sheet_id) {
                    $sheet_name = $sheet['label'];
                    break;
                }
            }
            return $sheet_name;
        }

        private function get_range($data)
        {
            $starting_column = isset($data['range']['starting_column']) ? $data['range']['starting_column'] : 'A';
            $starting_row = isset($data['range']['starting_row']) ? $data['range']['starting_row'] : '1';
            $ending_column = isset($data['range']['ending_column']) ? $data['range']['ending_column'] : 'A';
            $ending_row = isset($data['range']['ending_row']) ? $data['range']['ending_row'] : '1';

            if (empty($starting_column) || empty($starting_row) || empty($ending_column) || empty($ending_row)) {
                return '';
            }
            $range = $starting_column . $starting_row . ':' . $ending_column . $ending_row;
            return $range;
        }

        private function fill_empty_cells($record)
        {
            $min_value = 0;
            $max_value = max(array_keys($record));

            $empty_cell_values = array_fill_keys(range($min_value, $max_value), null);
            $record += $empty_cell_values;
            ksort($record);
            return $record;
        }

        // TODO: Dummy method for testing.
        public function work_with_dummy_data()
        {
            $dummydata = get_data_from_json_file('gsheet-add-row-dummy.json');
            error_log('dummydata : ' . print_r($dummydata, true));

            $range = $this->get_range($dummydata);

            $dummydata['range'] = $range;
            $record = $this->get_record_from_dummy_data($dummydata);
            error_log('Before record : ' . print_r($record, true));

            $record = $this->fill_empty_cells($record);

            error_log('record : ' . print_r($record, true));

            // $result = $this->gsheet_integration_handler->add_records_to_sheet([
            //     'spreadsheet_id' => $dummydata['spreadsheet_id'],
            //     'sheet_name' => $dummydata['dummy_data']['sheet_name'],
            //     'range' => $range,
            //     'values' => [$record],
            // ]);
            // error_log(' Response Result : ' . print_r($result, true));

        }

        public function get_record_from_dummy_data($data)
        {
            $record = [];
            foreach ($data['dummy_data']['map_fields'] as $map_field) {
                $source_value = $map_field['source_value'];
                $target_value = $map_field['target_value'];
                $record[$target_value] = $source_value;
            }
            return $record;
        }

        private function get_attachment_url_added_trigger_data($trigger_data)
        {
            $file_types = ["upload", "file-upload", "fileupload", "post_image", 'input_image', 'input_file', 'file'];
            foreach ($trigger_data as $field_key => $field) {
                if (in_array($field["type"], $file_types) && !empty($field["value"])) {
                    $trigger_data[$field_key]["value"] = $this->get_attachment_url_by_id($field["value"]);
                }
            }

            return $trigger_data;
        }

        private function get_attachment_url_by_id($attachment_id)
        {
            $post = get_post($attachment_id);
            if (!isset($post->post_mime_type) && empty($post->post_mime_type)) {
                return $attachment_id;
            }
            $image_url = wp_get_attachment_image_url($attachment_id, "full");
            $attachment_url = isset($image_url) && !empty($image_url) ? $image_url : $post->guid;

            return $attachment_url;
        }

    }

}
