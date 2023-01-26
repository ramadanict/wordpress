<?php

namespace Tablesome\Includes\Shortcode_Builder;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Shortcode_Builder\Builder')) {
    class Builder
    {

        public function init()
        {
            // if (!function_exists('\CSF') && !class_exists('\CSF')) {
            //     require_once TABLESOME_PATH . 'includes/lib/codestar-framework/codestar-framework.php';
            // }

            $fields = new \Tablesome\Includes\Shortcode_Builder\Fields();

            $prefix = 'tablesome-shortcode';

            // Init shortcode builder
            \CSF::createShortcoder($prefix, array(
                'button_title' => __('Add Tablesome Shortcode', "tablesome"),
                'select_title' => __('Select a shortcode', "tablesome"),
                'insert_title' => __('Insert Shortcode', "tablesome"),
                'show_in_editor' => true,
                'gutenberg' => [
                    'title' => __('Add Tablesome Shortcode', "tablesome"),
                    'icon' => 'screenoptions',
                    'category' => 'widgets',
                    'keywords' => array('table', 'data', 'tablesome', 'shortcode'),
                ],
            ));

            // create builder section
            \CSF::createSection($prefix, array(
                'title' => __('Tablesome Shortcode', 'tablesome'),
                'view' => 'normal',
                'shortcode' => 'tablesome',
                'class' => 'tablesome-csf__section',
                'fields' => [
                    $fields->get_table_id_field(),
                    $fields->get_show_serial_number_column_field(),
                    $fields->get_search_field(),
                    $fields->get_hide_table_header_field(),
                    $fields->get_sort_field(),
                    $fields->get_filter_field(),
                    $fields->get_page_limit_field(),
                    $fields->get_exclude_columns_field(),
                ],
            ));

        }
    }
}
