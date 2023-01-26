<?php

namespace Tablesome\Includes\Modules\Workflow\Triggers;

use Tablesome\Includes\Modules\Workflow\Trigger;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Triggers\Gravity')) {
    class Gravity extends Trigger
    {
        /**
         * un-supported fields
         *
         */
        public $unsupported_formats = array(
            'section',
            'page',
            'html',
            'consent',
            'captcha',
        );

        public function get_config()
        {
            $is_active = class_exists('GFForms') ? true : false;

            return array(
                'integration' => 'gravity',
                'integration_label' => __('Gravity Forms', 'tablesome'),
                'trigger' => 'tablesome_gravity_form_submit',
                'trigger_id' => 6,
                'trigger_label' => __('On Form Submit', 'tablesome'),
                'trigger_type' => 'forms',
                'is_active' => $is_active,
                'is_premium' => "no",
                'hooks' => array(
                    array(
                        'priority' => 10,
                        'accepted_args' => 2,
                        'name' => 'gform_after_submission',
                        'callback_name' => 'trigger_callback',
                    ),
                ),
                'supported_actions' => [],
                'unsupported_actions' => [8, 6, 9]
            );
        }

        public function get_collection()
        {
            $forms = $this->get_posts();
            if (empty($forms)) {
                return [];
            }

            foreach ($forms as $index => $form) {
                // Get form fields
                $forms[$index]['fields'] = $this->get_post_fields($form['id']);
            }
            return $forms;
        }

        public function get_posts()
        {
            if (!class_exists('GFAPI')) {
                return [];
            }

            $forms = \GFAPI::get_forms();

            if (empty($forms)) {
                return [];
            }
            $posts = array();
            foreach ($forms as $form) {
                $posts[] = array(
                    'id' => $form['id'],
                    'label' => $form['title'] . " (ID: " . $form['id'] . ")",
                    'integration_type' => 'gravity',
                );
            }
            return $posts;
        }

        public function get_post_fields($form_id, array $args = array())
        {
            $fields = [];
            if (!class_exists('GFAPI')) {
                return [];
            }

            $form = \GFAPI::get_form($form_id);

            if (is_null($form) || empty($form)) {
                return [];
            }
            $fields = $this->get_formatted_fields($form['fields']);
            return $fields;
        }

        public function get_formatted_fields($unformatted_fields)
        {
            $fields = array();
            if (empty($unformatted_fields)) {
                return $fields;
            }
            foreach ($unformatted_fields as $unformatted_field) {
                $basetype = isset($unformatted_field['type']) ? $unformatted_field['type'] : '';
                $single_field_inputs = isset($unformatted_field['inputs']) ? $unformatted_field['inputs'] : [];

                if (in_array($basetype, $this->unsupported_formats)) {
                    continue;
                }

                if (in_array($basetype, ['address'])) {

                    foreach ($single_field_inputs as $input) {
                        $fields[] = array(
                            "id" => strval($input['id']),
                            "label" => $input['label'],
                            "field_type" => $basetype,
                        );
                    }
                } else {

                    $field = [
                        "id" => strval($unformatted_field['id']),
                        "label" => $unformatted_field['label'],
                        "field_type" => $basetype,
                    ];

                    $have_options = (isset($unformatted_field['choices']) && !empty($unformatted_field['choices']));
                    if (in_array($basetype, ['select', 'checkbox', 'radio', 'option']) && $have_options) {
                        $field['options'] = $this->get_formatted_options($unformatted_field);
                    }
                    $fields[] = $field;
                }
            }

            return $fields;
        }

        public function trigger_callback($entry, $form)
        {
            if (empty($entry)) {
                return;
            }
            $submission_data = $this->get_formatted_posted_data($entry, $form);
            $submission_data = apply_filters("tablesome_form_submission_data", $submission_data);
            $this->trigger_source_id = $form['id'];
            $this->trigger_source_data = array(
                'integration' => $this->get_config()['integration'],
                'form_title' => $form['title'],
                'form_id' => $form['id'],
                'data' => $submission_data,
            );

            $this->run_triggers($this, $this->trigger_source_data);
        }

        public function conditions($trigger_meta, $trigger_data)
        {

            $integration = isset($trigger_meta['integration']) ? $trigger_meta['integration'] : '';
            $trigger_id = isset($trigger_meta['trigger_id']) ? $trigger_meta['trigger_id'] : '';

            if ($integration != $this->get_config()['integration'] || $trigger_id != $this->get_config()['trigger_id']) {
                return false;
            }

            $trigger_source_id = isset($trigger_meta['form_id']) ? $trigger_meta['form_id'] : 0;
            if (isset($trigger_data['form_id']) && $trigger_data['form_id'] == $trigger_source_id) {
                return true;
            }
            return false;
        }

        public function get_formatted_posted_data($entry, $form)
        {
            $data = array();

            foreach ($form['fields'] as $field) {
                $inputs = isset($field['inputs']) ? $field['inputs'] : [];
                $type = $field['type'];
                if (in_array($type, $this->unsupported_formats)) {
                    continue;
                }
                if ('address' === $type) {
                    foreach ($inputs as $input) {

                        $value = isset($entry[$input['id']]) ? $entry[$input['id']] : '';

                        $data[$input['id']] = array(
                            'label' => $input['label'],
                            'value' => isset($entry[$input['id']]) ? $entry[$input['id']] : '',
                            'type' => 'text',
                            'unix_timestamp' => '',
                        );
                    }
                } else {

                    $value = isset($entry[$field['id']]) ? $entry[$field['id']] : '';
                    $formatted_value = $this->get_formatted_value_by_field_type($value, $type);

                    if ($type == 'date' && is_valid_tablesome_date($value, 'Y-m-d')) {
                        $unix_timestamp = convert_tablesome_date_to_unix_timestamp($value, 'Y-m-d');
                        $unix_timestamp = convert_into_js_timestamp($unix_timestamp);
                    } else if ("name" == $type) {
                        $formatted_value = $this->get_formatted_name_values_from_inputs($entry, $inputs);
                    }

                    $data[$field['id']] = array(
                        'label' => $field['label'],
                        'value' => $formatted_value,
                        'type' => $type,
                        'unix_timestamp' => isset($unix_timestamp) && !empty($unix_timestamp) ? $unix_timestamp : '',
                    );
                }
            }

            return $data;
        }

        public function get_formatted_options($unformatted_field)
        {
            $options = array();
            foreach ($unformatted_field['choices'] as $choice) {
                $options[] = array(
                    'id' => $choice['value'],
                    'label' => $choice['text'],
                );
            }
            return $options;
        }

        private function get_formatted_value_by_field_type($value, $type)
        {
            if ($type == 'multiselect') {
                $value = !empty($value) ? implode(',', json_decode($value, true)) : '';
            } else if ($type == 'list') {
                $items = !empty($value) ? maybe_unserialize($value) : [];
                $data = [];
                foreach ($items as $item) {
                    if (is_array($item)) {
                        $data[] = implode(",", $item);
                    } else {
                        $data[] = $item;
                    }
                }
                $value = implode(',', $data);
            } else if ($type == 'post_category' && !empty($value)) {
                $extract_data = explode(':', $value);
                $value = isset($extract_data[0]) ? $extract_data[0] : '';
            } else if ($type == 'option' && !empty($value)) {
                $extract_option = explode('|', $value);
                $value = isset($extract_option[0]) ? $extract_option[0] : '';
            } else if ($type == 'fileupload') {
                $extract_data = !empty($value) ? json_decode($value, true) : [];
                if (!json_last_error()) {
                    $value = !empty($extract_data) ? implode(",", $extract_data) : '';
                }
            } else if ($type == 'post_image') {
                $extract_data = !empty($value) ? explode("|", $value) : [];
                $value = isset($extract_data[0]) && !empty($extract_data[0]) ? $extract_data[0] : '';
            }
            return $value;
        }
        private function get_formatted_name_values_from_inputs($entry, $inputs)
        {
            $name_values = [];
            foreach ($inputs as $input) {
                $name_values[] = isset($entry[$input['id']]) ? $entry[$input['id']] : '';
            }
            return !empty($name_values) ? implode(" ", $name_values) : "";
        }
    }

}
