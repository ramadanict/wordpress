<?php

namespace Tablesome\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Translations')) {
    class Translations
    {
        public function get_strings()
        {
            return array_merge($this->get_site_strings(), $this->get_dashboard_strings());
        }

        public function get_site_strings()
        {
            $strings = array(
                'first' => __("First", "tablesome"),
                'previous' => __("Prev", "tablesome"),
                'next' => __('Next', 'tablesome'),
                'last' => __('Last', 'tablesome'),
                'sort_ascending' => __('Sort Ascending', 'tablesome'),
                'sort_descending' => __('Sort Descending', 'tablesome'),
                'insert_left' => __('Insert left', 'tablesome'),
                'insert_right' => __('Insert Right', 'tablesome'),
                'move_left' => __('Move left', 'tablesome'),
                'move_right' => __('Move Right', 'tablesome'),
                'duplicate' => __('Duplicate', 'tablesome'),
                'delete' => __('Delete', 'tablesome'),
                'serial_number' => __('S.No', 'tablesome'),

                'search_placeholder' => __('Type to Search ...', 'tablesome'),
                'filter' => __('FILTER', 'tablesome'),
                'add_a_filter' => __('Add a Filter', 'tablesome'),
                'filter_placeholder' => __('Type to filter ...', 'tablesome'),
                'column_placeholder' => __('Column name...', 'tablesome'),
                'export_table' => __('Export Table', 'tablesome'),
                'export_table_header' => __('Export Table as', 'tablesome'),
                'export_table_csv' => __('CSV (.csv)', 'tablesome'),
                'export_table_excel' => __('Excel (.xlsx)', 'tablesome'),
                'export_table_pdf' => __('PDF (.pdf)', 'tablesome'),

                'format_type' => __("Format Type", 'tablesome'),
                'basic' => __('Basic', 'tablesome'),
                'import_table' => __('Import Table', 'tablesome'),
                'add_new_table' => __('Add New Table', 'tablesome'),
                'delete' => __('Delete', 'tablesome'),
                'enter_table_id_alert' => __('Please enter the tablesome table id', 'tablesome'),

                'loading' => __('Loading...', 'tablesome'),

            );
            return $strings;
        }

        public function get_dashboard_strings()
        {
            return [];
        }
    }
}
