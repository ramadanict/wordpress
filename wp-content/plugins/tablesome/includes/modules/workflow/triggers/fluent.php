<?php

namespace Tablesome\Includes\Modules\Workflow\Triggers;

use Tablesome\Includes\Modules\Workflow\Trigger;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Triggers\Fluent')) {
    class Fluent extends Trigger
    {
        public $unsupported_formats = array(
            'custom_html',
            'section_break',
            'shortcode',
            'action_hook',
            'input_password',
            'form_step',
            'tabular_grid',
            'custom_submit_button',
            'save_progress_button',
            'recaptcha',
            'hcaptcha',
            'turnstile',
            'repeater_field',
            'chained_select',

            // Payments fields
            'payment_summary_component',
            'subscription_payment_component',
        );

        public static $instance = null;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function get_config()
        {

            $is_active = function_exists('wpFluent') ? true : false;
            return array(
                'integration' => 'fluent',
                'integration_label' => __('Fluent Forms', 'tablesome'),
                'trigger' => 'tablesome_fluent_form_submit',
                'trigger_id' => 7,
                'trigger_label' => __('On Form Submit', 'tablesome'),
                'trigger_type' => 'forms',
                'is_active' => $is_active,
                'is_premium' => "no",
                'hooks' => array(
                    array(
                        'priority' => 10,
                        'accepted_args' => 3,
                        'name' => 'fluentform_submission_inserted',
                        'callback_name' => 'trigger_callback',
                    ),
                ),
                'supported_actions' => [],
                'unsupported_actions' => [8, 9]
            );
        }

        public function get_collection()
        {
            $forms = $this->get_posts();
            if (empty($forms)) {
                return [];
            }

            foreach ($forms as $index => $form) {
                $forms[$index]['fields'] = $this->get_post_fields($form['id']);
            }
            return $forms;
        }

        public function get_posts()
        {
            $exists = function_exists('wpFluent') && method_exists(wpFluent()->table('fluentform_forms'), 'get');
            if (!$exists) {
                return [];
            }

            $forms = wpFluent()->table('fluentform_forms')
                ->select(array('id', 'title'))
                ->orderBy('id', 'DESC')
                ->get();
            if (empty($forms)) {
                return [];
            }

            $posts = [];
            foreach ($forms as $form) {
                $posts[] = array(
                    'id' => (int) $form->id,
                    'label' => esc_html($form->title) . " (ID: " . $form->id . ")",
                    'integration_type' => 'fluent',
                );
            }
            return $posts;
        }

        public function get_post_fields($id, array $args = array())
        {
            if (!function_exists('wpFluent')) {
                return [];
            }

            $formApi = fluentFormApi('forms')->form($id);
            $form = $formApi->fields();

            if (!isset($form["fields"]) && empty($form["fields"])) {
                return [];
            }

            // error_log('form_fields  : ' . print_r($form["fields"], true));

            return $this->get_fields($form["fields"]);

        }

        public function get_fields($form_fields, $fields = [], $parent = [])
        {
            foreach ($form_fields as $form_field) {
                /**
                 * Making sure field id must consit of parent and child field id because address fields consist of its own set of fields like
                 * address_line_1, address_line_2, city, state, zip and country. This why we have to piping parent and child with dot(.)
                 * Followings are examples:-
                 * 1. address_1.country and address_1.zip
                 * 2. address_2.city and address_2.zip
                 * */

                $attribute_name = isset($form_field['attributes']["name"]) && !empty($form_field['attributes']["name"]) ? $form_field['attributes']["name"] : '';
                $id = isset($parent) && !empty($parent) ? $parent['id'] . "." . $attribute_name : $attribute_name;
                $type = isset($form_field['element']) ? $form_field['element'] : '';
                $admin_label = isset($form_field["settings"]['admin_field_label']) && !empty($form_field["settings"]['admin_field_label']) ? $form_field["settings"]['admin_field_label'] : '';
                $label = isset($form_field["settings"]['label']) && !empty($form_field["settings"]['label']) ? $form_field["settings"]['label'] : $admin_label;
                $label = isset($parent) && !empty($parent) ? $parent['label'] . " - " . $label : $label;

                if (isset($form_field["columns"]) && !empty($form_field["columns"])) {
                    foreach ($form_field["columns"] as $column) {
                        $column_fields = $this->get_fields($column["fields"]);
                        $fields = array_merge($fields, $column_fields);
                    }
                    continue;
                }

                if (!empty($type) && $type == "address") {

                    $parent_field = [
                        'id' => $id,
                        'label' => $label,
                    ];
                    $address_fields = $this->get_fields($form_field["fields"], [], $parent_field);
                    $fields = array_merge($fields, $address_fields);
                    continue;
                }

                if (!in_array($type, $this->unsupported_formats)) {

                    $field = [
                        "id" => $id,
                        "label" => $label,
                        //TODO: need to remove ternary condition specific for send_email action.
                        "field_type" => $type == "input_email" ? 'email' : $type,
                    ];

                    $have_options = (isset($form_field['settings']["advanced_options"]) && !empty($form_field['settings']["advanced_options"]));
                    if ($have_options) {
                        $field['options'] = $this->get_formatted_options($form_field['settings']["advanced_options"]);
                    }
                    $fields[] = $field;
                }
            }
            return $fields;
        }

        public function trigger_callback($entry_id, $data, $form)
        {
            $fields = $this->get_post_fields($form->id);

            $submission_data = $this->get_formatted_posted_data($fields, $data);
            $submission_data = apply_filters("tablesome_form_submission_data", $submission_data);

            // error_log(' submission_data $fields : ' . print_r($fields, true));
            error_log(' submission_data $data : ' . print_r($data, true));

            $this->trigger_source_id = $form->id;
            $this->trigger_source_data = array(
                'integration' => $this->get_config()['integration'],
                'form_title' => $form->title,
                'form_id' => $form->id,
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

        public function get_formatted_posted_data($fields, $form_data)
        {
            $data = array();
            if (empty($fields)) {
                return $data;
            }
            foreach ($fields as $field) {
                $field_ids = explode(".", $field['id']);
                $does_parent_exists = count($field_ids) > 1;
                $value = isset($form_data[$field['id']]) ? $form_data[$field['id']] : '';
                $value = $does_parent_exists && isset($form_data[$field_ids[0]][$field_ids[1]]) ? $form_data[$field_ids[0]][$field_ids[1]] : $value;
                $type = $field['field_type'];

                // error_log(' field : ' . print_r($field, true));

                if ($type == 'input_name') {
                    $value = implode(' ', $value);
                } else if ($type == 'input_date' && !empty($value)) {
                    $date_obj = date_parse($value);
                    if (isset($date_obj) && !empty($date_obj)) {
                        $date = $date_obj['year'] . '-' . $date_obj['month'] . '-' . $date_obj['day'];
                        $unix_timestamp = convert_tablesome_date_to_unix_timestamp($date, 'Y-m-d');
                        $unix_timestamp = $unix_timestamp * 1000; // convert to milliseconds
                    }

                } else if ($type == 'input_file' || $type == 'input_image') {
                    $value = is_array($value) ? $value[0] : $value;
                } else if (($type == 'select' || $type == 'input_checkbox') && is_array($value)) {
                    $value = implode(', ', $value);
                }

                $data[$field['id']] = array(
                    'label' => $field['label'],
                    'value' => $value,
                    'type' => $type,
                    'unix_timestamp' => isset($unix_timestamp) ? $unix_timestamp : '', // use this prop when the column format type is date
                );
            }
            return $data;
        }

        public function get_formatted_options($form_field_options)
        {
            $options = array();
            foreach ($form_field_options as $option) {
                $options[] = array(
                    'id' => $option["value"],
                    'label' => $option['label'],
                );
            }
            return $options;
        }
    }
}
