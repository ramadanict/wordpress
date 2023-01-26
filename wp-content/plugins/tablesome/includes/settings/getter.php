<?php

namespace Tablesome\Includes\Settings;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Settings\Tablesome_Getter')) {

    class Tablesome_Getter
    {
        private static $options;
        private static $defaults;

        public static function get($option_name)
        {
            self::$defaults = self::default_settings();

            // Only set one time
            if (!isset(self::$options) || empty(self::$options)) {
                self::$options = get_option(TABLESOME_OPTIONS); // unique id of the framework
            }

            // error_log('self::$options : ' . print_r(self::$options, true));

            if (isset(self::$options[$option_name])) {
                return self::$options[$option_name];
            } else {
                return self::$defaults[$option_name];
            }
        }

        public static function set($option_name, $option_value)
        {
            // Only set one time
            if (!isset(self::$options) || empty(self::$options)) {
                self::$options = get_option(TABLESOME_OPTIONS); // unique id of the framework
            }

            if (isset($option_value)) {
                self::$options[$option_name] = $option_value;
                update_option(TABLESOME_OPTIONS, self::$options);
            }
        }

        public static function get_settings()
        {
            return self::$options;
        }

        public static function default_settings()
        {
            $defaults = array(
                // General Settings Start
                "num_of_records_per_page" => 10,
                "pagination_show_first_and_last_buttons" => true,
                "pagination_show_previous_and_next_buttons" => true,
                "show_serial_number_column" => false,
                "search" => true,
                "hide_table_header" => false,
                "sorting" => true,
                "filters" => false,
                "export" => false,
                "enable_min_column_width" => true,
                "enable_max_column_width" => true,
                "min_column_width" => "",
                "max_column_width" => "",
                "table_display_mode" => "fit-to-container",
                "mobile_layout_mode" => "scroll-mode",

                // Style settings
                "style_disable" => false,
                "sticky_first_column" => false,

                // Form Settings
                'enabled_all_forms_entries' => true,
            );

            return $defaults;
        }
    } // END CLASS
}
