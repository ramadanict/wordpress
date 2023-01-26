<?php

namespace Tablesome\Includes\Shortcode_Builder;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Shortcode_Builder\Handler')) {
    class Handler
    {
        public function validate($table_id)
        {
            $post = get_post($table_id);
            return isset($post) && $post->post_type == TABLESOME_CPT ? true : false;
        }

        public function get_columns($table_id)
        {
            $table_meta = get_tablesome_data($table_id);
            return isset($table_meta['columns']) ? $table_meta['columns'] : [];
        }
    }
}