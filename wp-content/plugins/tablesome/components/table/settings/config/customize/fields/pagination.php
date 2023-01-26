<?php

namespace Tablesome\Components\Table\Settings\Config\Customize\Fields;

use \Tablesome\Components\Table\Settings\Config\Common;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Customize\Fields\Pagination')) {
    class Pagination
    {
        static $target;
        static $default;

        public function __construct()
        {
            self::$target = self::get_selectors();
            self::$default = Common::get_default();
        }

        public static function get_fields()
        {
            $breakpoint = [[
                "id" => "pagination-breakpoint-device",
                "type" => "device",
                "options" => Common::get_breakpoint(),
                "default" => "desktop",
            ]];

            return array_merge($breakpoint, self::get_desktop_field(), self::get_mobile_field());
        }

        public static function get_selectors()
        {
            $selectors = [
                "pagination" => ".tablesome-pagination ul li a",
                "pagination_hover" => ".tablesome-pagination ul li a.active, .tablesome-pagination ul li a:hover",
                "pagination_alignment" => ".tablesome-pagination ul",
            ];

            return $selectors;
        }

        private static function get_desktop_field()
        {

            return [
                [
                    "id" => "desktop-pagination-font-family",
                    "type" => "select",
                    "target" => self::$target["pagination"],
                    "placeholder" => "Choose Font Family",
                    "options" => Common::get_font_family(),
                    "default" => self::$default["font-family"],
                    "extra" => [
                        "optionType" => "group",
                        "css_property_name" => "font-family",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-pagination-font-size",
                    "type" => "pixel",
                    // "label" => "Size",
                    "target" => self::$target["pagination"],
                    "default" => "16",
                    "extra" => [
                        "css_property_name" => "font-size",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-pagination-color",
                    "type" => "color",
                    "label" => "Normal",
                    "target" => self::$target["pagination"],
                    "default" => self::$default["primary"],
                    "extra" => [
                        "css_property_name" => "color",
                        "breakpoint" => "desktop",
                    ],
                ],
                [
                    "id" => "desktop-pagination-hover-color",
                    "type" => "color",
                    "label" => "Active/Hover",
                    "target" => self::$target["pagination_hover"],
                    "default" => "#ffffff",
                    "extra" => [
                        "css_property_name" => "color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-pagination-bg-color",
                    "type" => "color",
                    "label" => "Normal",
                    "target" => self::$target["pagination"],
                    "default" => self::$default["primary"],
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-pagination-bg-hover-color",
                    "type" => "color",
                    "label" => "Active/Hover",
                    "target" => self::$target["pagination_hover"],
                    "default" => self::$default["primary"],
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-pagination-padding-top",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["pagination"],
                    "default" => "6",
                    "extra" => [
                        "css_property_name" => "padding-top",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-pagination-padding-right",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["pagination"],
                    "default" => "12",
                    "extra" => [
                        "css_property_name" => "padding-right",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-pagination-padding-bottom",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["pagination"],
                    "default" => "6",
                    "extra" => [
                        "css_property_name" => "padding-bottom",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-pagination-padding-left",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["pagination"],
                    "default" => "12",
                    "extra" => [
                        "css_property_name" => "padding-left",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-pagination-border-top-width",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["pagination"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-top-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-pagination-border-right-width",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["pagination"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-right-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-pagination-border-bottom-width",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["pagination"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-bottom-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-pagination-border-left-width",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["pagination"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-left-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-pagination-border-style",
                    "type" => "select",
                    "options" => Common::get_border_style(),
                    "target" => self::$target["pagination"],
                    "default" => "solid",
                    "extra" => [
                        "css_property_name" => "border-style",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-pagination-border-color",
                    "type" => "color",
                    "label" => "Color",
                    "target" => self::$target["pagination"],
                    "default" => self::$default["border"],
                    "extra" => [
                        "css_property_name" => "border-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-pagination-alignment",
                    "type" => "select-grid",
                    "options" => Common::get_content_alignment(),
                    "default" => "center",
                    "target" => self::$target["pagination_alignment"],
                    "extra" => [
                        "iconSize" => "small",
                        "css_property_name" => "justify-content",
                        "breakpoint" => "desktop",
                    ],
                ],
            ];

        }

        private static function get_mobile_field()
        {
            return [
                [
                    "id" => "mobile-pagination-font-family",
                    "type" => "select",
                    "target" => self::$target["pagination"],
                    "placeholder" => "Choose Font Family",
                    "options" => Common::get_font_family(),
                    "default" => self::$default["font-family"],
                    "extra" => [
                        "optionType" => "group",
                        "css_property_name" => "font-family",
                        "breakpoint" => "mobile",
                    ],
                ],

                [
                    "id" => "mobile-pagination-font-size",
                    "type" => "pixel",
                    // "label" => "Size",
                    "target" => self::$target["pagination"],
                    "default" => "16",
                    "extra" => [
                        "css_property_name" => "font-size",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                // [
                //     "id" => "mobile-pagination-color",
                //     "type" => "color",
                //     "label" => "Normal",
                //     "target" => self::$target["pagination"],
                //     "default" => self::$default["primary"],
                //     "extra" => [
                //         "css_property_name" => "color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                // [
                //     "id" => "mobile-pagination-hover-color",
                //     "type" => "color",
                //     "label" => "Active/Hover",
                //     "target" => self::$target["pagination_hover"],
                //     "default" => "#ffffff",
                //     "extra" => [
                //         "css_property_name" => "color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                // [
                //     "id" => "mobile-pagination-bg-color",
                //     "type" => "color",
                //     "label" => "Normal",
                //     "target" => self::$target["pagination"],
                //     "default" => self::$default["primary"],
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                // [
                //     "id" => "mobile-pagination-bg-hover-color",
                //     "type" => "color",
                //     "label" => "Active/Hover",
                //     "target" => self::$target["pagination_hover"],
                //     "default" => self::$default["primary"],
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                [
                    "id" => "mobile-pagination-padding-top",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["pagination"],
                    "default" => "3",
                    "extra" => [
                        "css_property_name" => "padding-top",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-pagination-padding-right",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["pagination"],
                    "default" => "6",
                    "extra" => [
                        "css_property_name" => "padding-right",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-pagination-padding-bottom",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["pagination"],
                    "default" => "3",
                    "extra" => [
                        "css_property_name" => "padding-bottom",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-pagination-padding-left",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["pagination"],
                    "default" => "6",
                    "extra" => [
                        "css_property_name" => "padding-left",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-pagination-border-top-width",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["pagination"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-top-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-pagination-border-right-width",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["pagination"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-right-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-pagination-border-bottom-width",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["pagination"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-bottom-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-pagination-border-left-width",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["pagination"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-left-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-pagination-border-style",
                    "type" => "select",
                    "options" => Common::get_border_style(),
                    "target" => self::$target["pagination"],
                    "default" => "solid",
                    "extra" => [
                        "css_property_name" => "border-style",
                        "breakpoint" => "mobile",
                    ],
                ],

                // [
                //     "id" => "mobile-pagination-border-color",
                //     "type" => "color",
                //     "label" => "Color",
                //     "target" => self::$target["pagination"],
                //     "default" => self::$default["border"],
                //     "extra" => [
                //         "css_property_name" => "border-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                [
                    "id" => "mobile-pagination-alignment",
                    "type" => "select-grid",
                    "options" => Common::get_content_alignment(),
                    "default" => "center",
                    "target" => self::$target["pagination_alignment"],
                    "extra" => [
                        "iconSize" => "small",
                        "css_property_name" => "justify-content",
                        "breakpoint" => "mobile",
                    ],
                ],
            ];
        }
    }
}
