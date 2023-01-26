<?php

namespace Tablesome\Components\Table\Settings\Config\Themes;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Themes\Brand')) {
    class Brand
    {
        public static function get_theme()
        {
            $primary = "#4A5E76";
            $secondary = "#ffffff";
            $alternate = "#f2f2f2";
            $border = "#4A5E76";
            $font_color = "#6a727f";

            $colors = [
                // Standard
                "standard-color" => $font_color,
                "desktop-header-color" => $secondary,
                "desktop-header-hover-color" => $secondary,
                "desktop-header-bg-color" => $primary,
                "desktop-header-bg-hover-color" => $primary,
                "desktop-header-border-color" => $border,
                "desktop-row-color" => $font_color,
                "desktop-row-hover-color" => $font_color,
                "desktop-row-bg-color" => $secondary,
                "desktop-row-bg-hover-color" => $secondary,
                "desktop-row-alternate" => true,
                "desktop-row-alternate-bg-color" => $alternate,
                "desktop-row-alternate-bg-hover-color" => $alternate,
                "desktop-row-border-color" => $border,
                "desktop-pagination-color" => $font_color,
                "desktop-pagination-hover-color" => $secondary,
                "desktop-pagination-bg-color" => $secondary,
                "desktop-pagination-bg-hover-color" => $primary,
                "desktop-pagination-border-color" => $border,
                "desktop-search-color" => $font_color,
                "desktop-search-hover-color" => $font_color,
                "desktop-search-bg-color" => $secondary,
                "desktop-search-bg-hover-color" => $secondary,
                "desktop-search-border-color" => $border,
                "desktop-filter-color" => $font_color,
                "desktop-filter-hover-color" => $font_color,
                "desktop-filter-bg-color" => $secondary,
                "desktop-filter-bg-hover-color" => $secondary,
                "desktop-filter-border-color" => $border,

                // Mobile mostly same as desktop
                // "mobile-header-color" => $secondary,
                // "mobile-header-hover-color" => $secondary,
                // "mobile-header-bg-color" => $primary,
                // "mobile-header-bg-hover-color" => $primary,
                // "mobile-header-border-color" => $border,
                // "mobile-row-color" => $primary,
                // "mobile-row-hover-color" => $primary,
                // "mobile-row-bg-color" => $secondary,
                // "mobile-row-bg-hover-color" => $secondary,
                // "mobile-row-alternate-bg-color" => $alternate,
                // "mobile-row-alternate-bg-hover-color" => $alternate,
                // "mobile-row-border-color" => $border,
                // "mobile-pagination-color" => $primary,
                // "mobile-pagination-hover-color" => $secondary,
                // "mobile-pagination-bg-color" => $secondary,
                // "mobile-pagination-bg-hover-color" => $primary,
                // "mobile-pagination-border-color" => $border,
                // "mobile-search-color" => $primary,
                // "mobile-search-hover-color" => $primary,
                // "mobile-search-bg-color" => $secondary,
                // "mobile-search-bg-hover-color" => $secondary,
                // "mobile-search-border-color" => $border,
                // "mobile-filter-color" => $primary,
                // "mobile-filter-hover-color" => $secondary,
                // "mobile-filter-bg-color" => $secondary,
                // "mobile-filter-bg-hover-color" => $primary,
                // "mobile-filter-border-color" => $border,
            ];

            return array_merge($colors, self::get_border_width(), self::get_text_align(), self::get_font_size(), self::get_font_family());
        }

        private static function get_border_width($table = "0", $other = "1", $standard_value = "none")
        {
            $border = [
                "desktop-header-border-top-width" => $table,
                "desktop-header-border-right-width" => $table,
                "desktop-header-border-bottom-width" => $table,
                "desktop-header-border-left-width" => $table,

                "desktop-row-border-top-width" => $table,
                "desktop-row-border-right-width" => $table,
                "desktop-row-border-bottom-width" => $table,
                "desktop-row-border-left-width" => $table,

                "desktop-pagination-border-top-width" => $other,
                "desktop-pagination-border-right-width" => $other,
                "desktop-pagination-border-bottom-width" => $other,
                "desktop-pagination-border-left-width" => $other,

                "desktop-search-border-top-width" => $other,
                "desktop-search-border-right-width" => $other,
                "desktop-search-border-bottom-width" => $other,
                "desktop-search-border-left-width" => $other,

                "desktop-filter-border-top-width" => $other,
                "desktop-filter-border-right-width" => $other,
                "desktop-filter-border-bottom-width" => $other,
                "desktop-filter-border-left-width" => $other,

                // Mobile
                "mobile-header-border-top-width" => $table,
                "mobile-header-border-right-width" => $table,
                "mobile-header-border-bottom-width" => $table,
                "mobile-header-border-left-width" => $table,

                "mobile-row-border-top-width" => $table,
                "mobile-row-border-right-width" => $table,
                "mobile-row-border-bottom-width" => $table,
                "mobile-row-border-left-width" => $table,

                "mobile-pagination-border-top-width" => $other,
                "mobile-pagination-border-right-width" => $other,
                "mobile-pagination-border-bottom-width" => $other,
                "mobile-pagination-border-left-width" => $other,

                "mobile-search-border-top-width" => $other,
                "mobile-search-border-right-width" => $other,
                "mobile-search-border-bottom-width" => $other,
                "mobile-search-border-left-width" => $other,

                "mobile-filter-border-top-width" => $other,
                "mobile-filter-border-right-width" => $other,
                "mobile-filter-border-bottom-width" => $other,
                "mobile-filter-border-left-width" => $other,
            ];

            $border["standard-border-width"] = $standard_value;

            return $border;
        }

        private static function get_text_align($value = "left")
        {
            return [];
        }

        private static function get_font_size($value = "16")
        {

            return [];
        }

        private static function get_font_family($value = "Trebuchet MS")
        {
            return [];
        }
    }
}
