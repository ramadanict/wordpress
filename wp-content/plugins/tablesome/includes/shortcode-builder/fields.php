<?php

namespace Tablesome\Includes\Shortcode_Builder;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Shortcode_Builder\Fields')) {
    class Fields
    {
        public function get_table_id_field()
        {
            return [
                'id' => 'table_id',
                'type' => 'select',
                'title' => __('Choose the tablesome table', 'tablesome'),
                'class' => 'tablesome__fields--table_id',
                'options' => 'posts',
                'placeholder' => __('Choose the table', 'tablesome'),
                'query_args' => array(
                    'post_type' => 'tablesome_cpt',
                    'posts_per_page' => -1,
                ),
                // 'ajax' => true,
                'chosen' => true,
            ];
        }

        public function get_hide_table_header_field()
        {
            return [
                'id' => 'hide_table_header',
                'type' => 'switcher',
                'title' => __("Hide Table Header", "tablesome"),
                'subtitle' => __("This will affect the front end only", "tablesome"),
                'class' => 'hide_table_header',
                'default' => 0,
            ];
        }

        public function get_sort_field()
        {
            return [
                'id' => 'sorting',
                'type' => 'switcher',
                'title' => __("Sorting", "tablesome"),
                'subtitle' => __("This will affect the front end only", "tablesome"),
                'dependency' => array('hide_table_header', '==', 'false'),
                'default' => 1,
            ];
        }

        public function get_show_serial_number_column_field()
        {
            return [
                'id' => 'show_serial_number_column',
                'type' => 'switcher',
                'title' => __("Show Serial Number Column (S.No)", "tablesome"),
                // 'subtitle' => __("This will affect the front end only", "tablesome"),
                // 'class' => 'search',
                'default' => 0,
            ];
        }

        public function get_search_field()
        {
            return [
                'id' => 'search',
                'type' => 'switcher',
                'title' => __("Search", "tablesome"),
                'subtitle' => __("This will affect the front end only", "tablesome"),
                'class' => 'search',
                'default' => 1,
            ];
        }

        public function get_filter_field()
        {
            return [
                'id' => 'filters',
                'type' => 'switcher',
                'title' => __("Filters", "tablesome"),
                'subtitle' => __("This will affect the front end only", "tablesome"),
                'default' => 0,
            ];
        }

        public function get_page_limit_field()
        {
            return array(
                'id' => 'page_limit',
                'type' => 'number',
                'title' => __('Number Of Records per Page', "tablesome"),
                'subtitle' => __("Value should between 1-100", "tablesome"),
                'default' => 10,
                'attributes' => array(
                    'min' => 1,
                    'max' => 100,
                ),
            );
        }

        public function get_exclude_columns_field()
        {
            return array(
                'id' => 'exclude_column_ids',
                'type' => 'select',
                'title' => __('Exclude Columns', "tablesome"),
                'chosen' => true,
                'multiple' => true,
                'placeholder' => __('Select an table exclude columns', 'tablesome'),
                'options' => array(
                    '' => '',
                ),
                'class' => 'tablesome__fields--exclude_columns',
            );

        }
    }
}
