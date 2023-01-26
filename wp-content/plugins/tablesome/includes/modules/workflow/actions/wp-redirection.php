<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use Tablesome\Includes\Modules\Workflow\Action;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\WP_Redirection')) {
    class WP_Redirection extends Action {

        public function get_config() {
            return array(
                'id' => 6,
                'name' => 'redirection',
                'label' => __('Redirection', 'tablesome'),
                'integration' => 'wordpress',
                'is_premium' => false,
            );
        }

        public function do_action($trigger_class, $trigger_instance) {
            global $workflow_redirection_data;

            $this->trigger_class = $trigger_class;
            $this->trigger_instance = $trigger_instance;

            $this->set_defaults();

            // validate the URL
            if (empty($this->redirect_url)) {
                return false;
            }

            $workflow_redirection_data[] = array(
                'open_in_new_tab' => $this->open_in_new_tab,
                'url' => $this->redirect_url,
            );

            return true;
        }

        private function set_defaults() {

            $this->trigger_source_data = $this->trigger_class->trigger_source_data['data'];

            $this->action_meta = isset($this->trigger_instance['action_meta']) ? $this->trigger_instance['action_meta'] : [];

            $this->open_in_new_tab = isset($this->action_meta['open_in_new_tab']) ? $this->action_meta['open_in_new_tab'] : false;

            $custom_url_enabled = isset($this->action_meta['custom_url_enabled']) ? $this->action_meta['custom_url_enabled'] : false;

            if ($custom_url_enabled) {
                $this->redirect_url = isset($this->action_meta['custom_url']) ? $this->action_meta['custom_url'] : '';
            } else {
                $post_id = isset($this->action_meta['post_id']) ? $this->action_meta['post_id'] : 0;
                $this->redirect_url = !empty($post_id) ? get_permalink($post_id) : '';
            }

            if (empty($this->redirect_url)) {
                return;
            }

            // Bind params to the URL ⚓
            $this->bind_params();
        }

        private function bind_params() {

            $params = [];

            $enable_all_url_params = isset($this->action_meta['enable_all_url_params']) ? $this->action_meta['enable_all_url_params'] : false;

            $url_params = isset($this->action_meta['url_params']) ? $this->action_meta['url_params'] : [];

            if ($enable_all_url_params) {

                foreach ($this->trigger_source_data as $source_field_name => $source_field_data) {

                    $param_name = $this->get_active_param_name_from_config($url_params, $source_field_name);
                    $params[$param_name] = isset($source_field_data['value']) ? $source_field_data['value'] : '';
                }

            } else {
                foreach ($url_params as $param) {

                    $field_status = isset($param['field_status']) ? $param['field_status'] : false;
                    $field_name = isset($param['field_id']) ? $param['field_id'] : '';

                    // Skip if the field status is false or the field name is empty
                    if (!$field_status || empty($field_name)) {
                        continue;
                    }

                    $alias_name = isset($param['field_alias']) ? $param['field_alias'] : '';
                    $param_name = !empty($alias_name) ? $alias_name : $field_name;

                    $param_value = isset($this->trigger_source_data[$field_name]) ? $this->trigger_source_data[$field_name]['value'] : '';

                    $params[$param_name] = $param_value;
                }
            }

            $params = $this->add_tablesome_prefix_in_params($params);
            // Bind the params
            $this->redirect_url = sanitize_url(add_query_arg($params, $this->redirect_url));
        }

        private function get_active_param_name_from_config($url_params, $source_field_name) {
            $param_name = $source_field_name;

            if (empty($url_params)) {
                return $param_name;
            }

            foreach ($url_params as $param) {

                $field_status = isset($param['field_status']) ? $param['field_status'] : false;
                $field_name = isset($param['field_id']) ? $param['field_id'] : '';

                $param_is_configured = ($source_field_name == $field_name);

                if ($param_is_configured && $field_status) {
                    $alias_name = isset($param['field_alias']) ? $param['field_alias'] : '';
                    $param_name = !empty($alias_name) ? $alias_name : $field_name;
                    break;
                }
            }
            return $param_name;
        }

        private function add_tablesome_prefix_in_params($params) {
            $new_params = [];
            foreach ($params as $param_key => $param_value) {
                $new_param_key = str_starts_with($param_key, TABLESOME_ALIAS_PREFIX) ? $param_key : TABLESOME_ALIAS_PREFIX . $param_key;
                $new_params[$new_param_key] = urlencode($param_value);
            }

            return $new_params;
        }
    }
}