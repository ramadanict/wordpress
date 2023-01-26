<?php

namespace Tablesome\Includes;

use \Tablesome\Includes\Actions;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Shortcodes')) {
    class Shortcodes
    {
        public function __construct()
        {
            add_shortcode('tablesome', array($this, 'basic'));
            add_shortcode('tablesome_get_params', array($this, 'show_param_values'));
        }

        public function basic($atts, $content = null)
        {
            $defaults = $this->default_args();
            $args = array_merge($defaults, $atts);
            // $args = shortcode_atts($defaults, $atts);
            $is_valid_table = $this->validate($args);

            if (!$is_valid_table) {
                return;
            }

            $Actions = new Actions();
            $Actions->handle_frontend_assets($args['table_id'], 'tablesome_shortcode');
            $table = new \Tablesome\Components\Table\Controller();
            return $table->get_view($args);
        }

        private function default_args()
        {
            $args = [
                'table_id' => get_the_ID(),
                'pagination' => true,
                // 'page_limit' => Tablesome_Getter::get('num_of_records_per_page'),
                // 'exclude_column_ids' => '',
                // 'search' => Tablesome_Getter::get('search'),
                // 'hide_table_header' => Tablesome_Getter::get('hide_table_header'),
                // 'show_serial_number_column' => Tablesome_Getter::get('show_serial_number_column'),
                // 'sorting' => Tablesome_Getter::get('sorting'),
                // 'filters' => Tablesome_Getter::get('filters'),
            ];
            return $args;
        }

        private function validate($args)
        {
            $post = get_post($args['table_id']);
            if (empty($post)) {
                return false;
            }

            if (isset($post) && $post->post_type != TABLESOME_CPT) {
                return false;
            }

            if (isset($post) && $post->post_status != 'publish') {
                return false;
            }
            return true;
        }

        public function show_param_values($atts, $content = null)
        {

            // get shortcode params
            $params = isset($atts['params']) && !empty($atts['params']) ? explode(",", $atts['params']) : [];
            $data = array();

            // show all the params values if shortcode doesn't have 'params' attribute (or) the params has empty values
            $list_all_values = isset($params) && !empty($params) ? false : true;

            // sanitize the url params values
            $request = [];
            foreach ($_GET as $param_name => $param_value) {
                $value = sanitize_text_field(urldecode($param_value));
                if ($value) {
                    $request[$param_name] = $value;
                }
            }

            if (empty($request)) {
                return;
            }

            if (!$list_all_values && is_array($params)) {
                // collect the values from URL based on shortcode params attribute
                foreach ($params as $param_name) {
                    $param_name = $this->add_tablesome_prefix($param_name);
                    if (!isset($request[$param_name])) {
                        continue;
                    }
                    $value = isset($request[$param_name]) ? $request[$param_name] : '';
                    $data[$param_name] = $value;
                }
            } else {
                $data = $request;
            }

            if (empty($data)) {
                return;
            }

            $content = '';

            if ($list_all_values) {
                $content .= '<ul>';
                foreach ($data as $key => $value) {
                    $content .= '<li>' . $value . '</li>';
                }
                $content .= '</ul>';
            } else {
                $content = implode(',', $data);
            }

            return $content;
        }

        public function add_tablesome_prefix($param_name)
        {
            return str_starts_with($param_name, TABLESOME_ALIAS_PREFIX) ? $param_name : TABLESOME_ALIAS_PREFIX . $param_name;
        }
    }
}
