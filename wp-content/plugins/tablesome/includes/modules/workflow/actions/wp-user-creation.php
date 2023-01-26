<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use Tablesome\Includes\Modules\Workflow\Action;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\WP_User_Creation')) {
    class WP_User_Creation extends Action {

        private static $USER_DEFAULT_ROLE = 'subscriber';

        public function get_config() {
            return array(
                'id' => 5,
                'name' => 'add_new_wp_user',
                'label' => __('Add New WP User', 'tablesome'),
                'integration' => 'wordpress',
                'is_premium' => true,
            );
        }

        public function do_action($trigger_class, $trigger_instance) {
            $this->bind_props($trigger_class, $trigger_instance);
            if (empty($this->fields)) {
                return;
            }
            $user_data = $this->get_user_data_from_trigger();
            if (empty($user_data)) {
                return;
            }

            $result = wp_insert_user($user_data);
            if (is_wp_error($result)) {
                $message = $result->get_error_message();
                return;
            }
            $this->user_id = $result;

            if ($this->can_send_notification) {
                wp_new_user_notification($this->user_id, null, 'both');
            }

            $this->add_usermeta();
        }

        private function bind_props($trigger_class, $trigger_instance) {
            $this->trigger_class = $trigger_class;
            $this->trigger_instance = $trigger_instance;

            $this->trigger_source_data = $this->trigger_class->trigger_source_data['data'];
            $this->action_meta = isset($this->trigger_instance['action_meta']) ? $this->trigger_instance['action_meta'] : [];

            $this->fields = isset($this->action_meta['fields']) ? $this->action_meta['fields'] : [];

            // set user-id is 0
            $this->user_id = 0;

            $this->smart_field_values = get_tablesome_smart_field_values();

            $this->can_send_notification = isset($this->action_meta['send_user_notification']) && 1 == $this->action_meta['send_user_notification'] ? true : false;
        }

        private function get_user_data_from_trigger() {
            $user_data = [];

            $username = $this->get_user_prop_value_by_name('user_login');
            if (empty($username) || username_exists($username)) {
                return;
            }

            $user_email = $this->get_user_prop_value_by_name('user_email');
            if (empty($user_email) || !is_valid_tablesome_email($user_email) || email_exists($user_email)) {
                return;
            }

            $user_role = $this->get_user_prop_value_by_name('role');
            if (empty($user_role)) {
                return;
            }

            $user_data['user_login'] = $username;
            $user_data['user_email'] = $user_email;
            $user_data['role'] = $user_role;

            // add additional properties
            $extra_props = ['user_nicename', 'display_name', 'nickname', 'first_name', 'last_name', 'description'];

            foreach ($extra_props as $prop_name) {
                $user_data[$prop_name] = $this->get_user_prop_value_by_name($prop_name);
            }

            // Generate a random password
            $random_password = wp_generate_password(12, false);
            $user_data['user_pass'] = $random_password;

            return $user_data;
        }

        private function get_user_prop_value_by_name($name) {
            $index = array_search($name, array_column($this->fields, 'name'));
            if (!is_numeric($index)) {
                return '';
            }

            $source_type = isset($this->fields[$index]['source_type']) ? $this->fields[$index]['source_type'] : 'custom';
            $value = isset($this->fields[$index]['value']) ? $this->fields[$index]['value'] : '';

            $target_field = $value;

            if ('trigger_source' === $source_type) {
                $value = isset($this->trigger_source_data[$target_field]['value']) ? $this->trigger_source_data[$target_field]['value'] : '';
            } else if ('trigger_smart_fields' === $source_type) {
                $value = isset($this->smart_field_values[$target_field]) ? $this->smart_field_values[$target_field] : '';
            }

            if ('role' === $name) {
                return !empty($value) && get_role($value) ? $value : self::$USER_DEFAULT_ROLE;
            }

            return $value;
        }

        private function add_usermeta() {
            $usermeta_fields = array_filter($this->fields, function ($field) {
                return $field['field_type'] == 'usermeta';
            });

            if (empty($usermeta_fields)) {
                return;
            }

            foreach ($usermeta_fields as $field) {
                $meta_key = isset($field['key']) ? $field['key'] : '';
                $target_field = isset($field['value']) ? $field['value'] : '';
                $source_type = isset($field['source_type']) ? $field['source_type'] : 'custom';

                if (empty($meta_key)) {
                    continue;
                }

                $meta_value = $target_field;
                if ('trigger_source' === $source_type) {
                    $meta_value = isset($this->trigger_source_data[$target_field]['value']) ? $this->trigger_source_data[$target_field]['value'] : '';
                } else if ('trigger_smart_fields' === $source_type) {
                    $meta_value = isset($this->smart_field_values[$target_field]) ? $this->smart_field_values[$target_field] : '';
                }

                update_user_meta($this->user_id, $meta_key, $meta_value);
            }

            return true;
        }
    }
}