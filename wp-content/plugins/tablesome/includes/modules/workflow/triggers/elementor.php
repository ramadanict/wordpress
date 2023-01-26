<?php

namespace Tablesome\Includes\Modules\Workflow\Triggers;

use Tablesome\Includes\Modules\Workflow\Trigger;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Triggers\Elementor')) {
    class Elementor extends Trigger {
        public $unsupported_formats = array(
            'hidden',
            'password',
            'acceptance',
            'step',
            'recaptcha',
            'recaptcha_v3',
            'honeypot',

            // can be used in the future
            'time',
            'html',
        );

        public function get_config() {
            $is_active = defined('ELEMENTOR_PRO_PATH') ? true : false;

            return array(
                'integration' => 'elementor',
                'integration_label' => __('Elementor', 'tablesome'),
                'trigger' => 'tablesome_elementor_form_submit',
                'trigger_id' => 3,
                'trigger_label' => __('On Form Submit', 'tablesome'),
                'trigger_type' => 'forms',
                'is_active' => $is_active,
                'is_premium' => "no",
                'hooks' => array(
                    array(
                        'priority' => 10,
                        'accepted_args' => 4,
                        'name' => 'elementor_pro/forms/new_record',
                        'callback_name' => 'trigger_callback',
                    ),
                ),
                'supported_actions' => [],
                'unsupported_actions' => [8, 9]
            );
        }

        public function trigger_callback($record, $handler) {
            if (empty($record)) {
                return;
            }

            $form_id = $record->get_form_settings('id');
            if (empty($form_id)) {
                return;
            }

            $submission_data = $this->get_formatted_posted_data($record->get('fields'));
            $submission_data = apply_filters("tablesome_form_submission_data", $submission_data);

            $this->trigger_source_id = $form_id;
            $this->trigger_source_data = array(
                'integration' => $this->get_config()['integration'],
                'form_title' => $record->get('form_settings')['form_name'],
                'form_id' => $form_id,
                'data' => $submission_data,
            );

            $this->run_triggers($this, $this->trigger_source_data);
        }

        public function conditions($trigger_meta, $trigger_data) {
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

        public function get_collection() {
            $forms = $this->get_posts();
            if (empty($forms)) {
                return [];
            }

            foreach ($forms as $index => $form) {
                $forms[$index]['fields'] = $this->get_post_fields($form["id"]);
            }
            return $forms;
        }

        public function get_posts() {
            $posts = [];
            global $wpdb;
            $post_metas = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT pm.meta_value
                        FROM $wpdb->postmeta pm
                            LEFT JOIN $wpdb->posts p
                                ON p.ID = pm.post_id
                        WHERE p.post_type IS NOT NULL
                        AND p.post_status = %s
                        AND pm.meta_key = %s
                        AND pm.`meta_value` LIKE %s",
                    'publish',
                    '_elementor_data',
                    '%%form_fields%%'
                )
            );

            if (!empty($post_metas)) {
                foreach ($post_metas as $post_meta) {
                    $inner_forms = self::get_all_inner_forms(json_decode($post_meta->meta_value));
                    if (!empty($inner_forms)) {
                        foreach ($inner_forms as $form) {
                            $posts[] = array(
                                'id' => $form->id,
                                'label' => $form->settings->form_name . " (ID: " . $form->id . ")",
                                'integration_type' => 'elementor',
                            );
                        }
                    }
                }
            }

            return $posts;
        }

        /**
         * Return all the specific fields of a form ID
         */
        public function get_post_fields($form_id) {
            $fields = [];

            global $wpdb;
            $query = "SELECT ms.meta_value  FROM {$wpdb->postmeta} ms JOIN {$wpdb->posts} p on p.ID = ms.post_id WHERE ms.meta_key LIKE '_elementor_data' AND ms.meta_value LIKE '%form_fields%' AND p.post_status = 'publish' ";
            $post_metas = $wpdb->get_results($query);

            if (!empty($post_metas)) {
                foreach ($post_metas as $post_meta) {
                    $inner_forms = self::get_all_inner_forms(json_decode($post_meta->meta_value));
                    if (!empty($inner_forms)) {
                        foreach ($inner_forms as $form) {
                            if ($form->id == $form_id) {
                                if (!empty($form->settings->form_fields)) {
                                    foreach ($form->settings->form_fields as $field) {
                                        // error_log(' field : ' . print_r($field, true));

                                        $type = isset($field->field_type) && !empty($field->field_type) ? $field->field_type : 'text';

                                        error_log(' ele type : ' . $type);

                                        if (!in_array($type, $this->unsupported_formats)) {
                                            $options = self::get_options($field);
                                            $single_field = [
                                                'id' => $field->custom_id,
                                                'label' => !empty($field->field_label) ? $field->field_label : 'unknown',
                                                'field_type' => $type,
                                            ];
                                            if (!empty($options)) {
                                                $single_field['options'] = $options;
                                            }

                                            $fields[] = $single_field;
                                        }

                                    }
                                }
                            }
                        }
                    }
                }
            }

            // error_log(' fields : ' . print_r($fields, true));

            return $fields;
        }

        public static function get_all_inner_forms($elements) {
            $block_is_on_page = array();
            if (!empty($elements)) {
                foreach ($elements as $element) {
                    if ('widget' === $element->elType && 'form' === $element->widgetType) {
                        $block_is_on_page[] = $element;
                    }
                    if (!empty($element->elements)) {
                        $inner_block_is_on_page = self::get_all_inner_forms($element->elements);
                        if (!empty($inner_block_is_on_page)) {
                            $block_is_on_page = array_merge($block_is_on_page, $inner_block_is_on_page);
                        }
                    }
                }
            }

            return $block_is_on_page;
        }

        public static function get_options($field) {
            $options = [];
            if (isset($field->field_options) && !empty($field->field_options)) {
                $options_list = preg_split('/\r\n|\r|\n/', $field->field_options);

                foreach ($options_list as $option) {
                    array_push($options, [
                        'label' => $option,
                        'id' => $option,
                    ]);
                }
            }

            return $options;
        }

        public function get_formatted_posted_data($fields) {
            $data = array();
            if (empty($fields)) {
                return $data;
            }
            foreach ($fields as $id => $field) {
                $value = $field['value'];
                if ($field['type'] == 'date' && is_valid_tablesome_date($value, 'Y-m-d')) {
                    $unix_timestamp = convert_tablesome_date_to_unix_timestamp($value, 'Y-m-d');
                    $unix_timestamp = $unix_timestamp * 1000; // convert to milliseconds
                }
                $data[$id] = array(
                    'label' => $field['title'],
                    'value' => $value,
                    'type' => $field['type'],
                    'unix_timestamp' => isset($unix_timestamp) ? $unix_timestamp : '', // use this prop when the column format type is date
                );
            }
            return $data;
        }
    }

}