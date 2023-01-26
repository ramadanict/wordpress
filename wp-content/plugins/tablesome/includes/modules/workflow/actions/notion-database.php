<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use Tablesome\Includes\Modules\Workflow\Action;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\Notion_Database')) {
    class Notion_Database extends Action {

        public static $instance = null;

        public static function get_instance() {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct() {
            $this->notion_api = new \Tablesome\Includes\Modules\Workflow\External_Apis\Notion();
        }

        public function get_config() {
            return array(
                'id' => 3,
                'name' => 'add_page',
                'label' => __('Add Record to Notion DB', 'tablesome'),
                'integration' => 'notion',
                'is_premium' => false,
            );
        }

        public function do_action($trigger_class, $trigger_instance) {
            if (!$this->notion_api->api_status || empty($this->notion_api->api_key)) {
                return false;
            }
            $this->trigger_class = $trigger_class;
            $this->trigger_instance = $trigger_instance;

            $action_meta = $this->trigger_instance['action_meta'];
            $data = isset($this->trigger_class->trigger_source_data['data']) ? $this->trigger_class->trigger_source_data['data'] : [];

            $database_id = isset($action_meta['database_id']) ? $action_meta['database_id'] : '';
            $match_fields = isset($action_meta['match_fields']) ? $action_meta['match_fields'] : [];

            if (empty($database_id) || empty($match_fields)) {
                return;
            }

            $database = $this->notion_api->get_database_by_id($database_id);

            if (empty($database) || isset($database['status']) && 'failed' == $database['status']) {
                return;
            }

            $data = $this->get_matched_property_values($match_fields, $data, $database);

            if (empty($data)) {
                return;
            }

            $response_data = $this->notion_api->add_record_in_database($database_id, $data);

            return $response_data;
        }

        public function get_matched_property_values($match_fields, $form_data, $database) {

            $data = array();
            foreach ($match_fields as $match_field) {
                $property_id = isset($match_field['property_id']) ? $match_field['property_id'] : '';
                $field_name = isset($match_field['field_name']) ? $match_field['field_name'] : '';

                if (empty($property_id)) {
                    continue;
                }

                $property = $this->get_property($property_id, $database);

                if (empty($property)) {
                    continue;
                }

                $value = isset($form_data[$field_name]['value']) ? $form_data[$field_name]['value'] : '';
                $property_values = $this->get_property_values($property, $value);

                if (empty($property_values)) {
                    continue;
                }

                $data[$property_id] = $property_values;
            }
            return $data;
        }

        public function get_property($property_id, $database) {
            $data = [];
            $properties = isset($database['properties']) ? $database['properties'] : array();
            if (empty($properties)) {
                return $data;
            }
            foreach ($properties as $property) {
                if ($property['id'] == $property_id) {
                    $data = $property;
                    break;
                }
            }
            return $data;
        }

        public function get_property_values($property, $value) {
            $method_name = "get_{$property['type']}_values";
            if (method_exists($this, $method_name)) {
                return $this->$method_name($property, $value);
            } else {
                return [];
            }
        }

        public function get_title_values($property, $value) {
            $data = [];
            $data['title'] = array(
                array(
                    'type' => 'text',
                    'text' => array(
                        'content' => $value,
                    ),
                ),
            );
            return $data;
        }

        public function get_rich_text_values($property, $value) {
            $data = [];
            $data['rich_text'] = array(
                array(
                    'type' => "text",
                    "text" => array(
                        'content' => $value,
                    ),
                ),
            );
            return $data;
        }

        public function get_number_values($property, $value) {
            $data = null;
            $float_val = (float) $value;
            $int_val = (int) $value;
            $double_val = (double) $value;

            if (is_float($float_val)) {
                $data = floatval($float_val);
            } else if (is_int($int_val)) {
                $data = intval($int_val);
            } else if (is_double($double_val)) {
                $data = doubleval($double_val);
            }

            return array(
                'number' => $data,
            );
        }

        public function get_select_values($property, $value) {
            return array(
                'select' => array(
                    'name' => $value,
                ),
            );
        }

        public function get_multi_select_values($property, $value) {
            $values = isset($value) && !empty($value) ? explode(',', $value) : [];
            if (empty($values)) {
                return [];
            }

            $data = [];
            $data['multi_select'] = array();
            foreach ($values as $value) {
                $data['multi_select'][] = array(
                    'name' => $value,
                );
            }
            return $data;
        }

        public function get_checkbox_values($property, $value) {
            $checkbox_value = false;
            if (is_string($value)) {
                $checkbox_value = !empty($value) ? true : false;
            } else if (is_numeric($value)) {
                $checkbox_value = $value > 0 ? true : false;
            } else if (is_bool($value)) {
                $checkbox_value = $value ? true : false;
            }

            return array(
                'checkbox' => $checkbox_value,
            );
        }

        public function get_url_values($property, $value) {
            return array(
                'url' => $value,
            );
        }

        public function get_email_values($property, $value) {
            return array(
                'email' => $value,
            );
        }

        public function get_phone_number_values($property, $value) {
            return array(
                'phone_number' => $value,
            );
        }

        // public function get_people_values($property, $value)
        // {
        // }

        public function get_date_values($property, $value) {
            if (empty($value) && !is_valid_tablesome_date($value, 'Y-m-d')) {
                return [];
            }

            $datetime = new \DateTime($value);
            $date = $datetime->format('Y-m-d');

            return array(
                'date' => array(
                    'start' => $date,
                ),
            );
        }

    }
}