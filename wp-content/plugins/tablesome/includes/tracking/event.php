<?php

namespace Tablesome\Includes\Tracking;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\Tablesome\Includes\Tracking\Event')) {
    class Event
    {
        public function get_properties($event, $value)
        {
            return array(
                'data' => $value,
                'label' => $this->get_event_title($event),
            );
        }

        public function get_event_title($type)
        {
            $events_titles = $this->get_events_titles();
            return isset($events_titles[$type]) ? $events_titles[$type] : 'Undefined-Event';
        }

        public function get_events_titles()
        {
            return [

                /*** Options fields from Settings */
                'num_of_records_per_page' => 'Total no. of records per page',
                // 'show_serial_number_column' => 'Show Serial Number Column (S.No)',
                'search' => 'Enable/Disable the Search',
                'hide_table_header' => 'Hide Table Header',
                'sorting' => 'Enable/Disable the Tablesome Sorting',
                'filters' => 'Enable/Disable the Tablesome Filters',
                'mobile_layout_mode' => 'Mobile Layout Mode',
                'style_disable' => 'Enable/Disable the Style',
                'min_column_width' => 'Min column width',

                /** Extras */
                'deactivate' => 'Tablesome plugin deactivated',
                'tables_count' => 'Total No of tables Count',
                'tables_column_format_collection' => 'Tables Columns format collection',
                'tables_records_count' => 'Total tables records count',

                'triggers_and_actions_used' => 'Triggers and Actions used Or not',
                'triggers_collection' => 'Total triggers collection',
                'actions_collection' => 'Total actions collection',
                'plugins_info' => 'Plugins info',
                'themes_info' => 'Themes info',
            ];
        }
    }
}