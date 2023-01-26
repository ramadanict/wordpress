<?php

namespace Tablesome\Components\Table\Settings\Config\Customize\Fields;

use \Tablesome\Components\Table\Settings\Config\Common;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Customize\Fields\Header')) {
    class Header
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
                "id" => "header-breakpoint-device",
                "type" => "device",
                "options" => Common::get_breakpoint(),
                "default" => "desktop",
            ]];

            return array_merge($breakpoint, self::get_desktop_field(), self::get_mobile_field());
        }

        public static function get_selectors()
        {
            $selectors = [
                "column" => ".tablesome__table .tablesome__row > .tablesome__column",
                "column_hover" => ".tablesome__table .tablesome__row > .tablesome__column:hover",
            ];

            return $selectors;
        }

        private static function get_desktop_field()
        {

            return [
                [
                    "id" => "desktop-header-font-family",
                    "type" => "select",
                    "target" => self::$target["column"],
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
                    "id" => "desktop-header-font-size",
                    "type" => "pixel",
                    // "label" => "Size",
                    "target" => self::$target["column"],
                    "default" => "16",
                    "extra" => [
                        "css_property_name" => "font-size",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-header-color",
                    "type" => "color",
                    "label" => "Normal",
                    "target" => self::$target["column"],
                    "default" => "#ffffff",
                    "extra" => [
                        "css_property_name" => "color",
                        "breakpoint" => "desktop",
                    ],
                ],
                [
                    "id" => "desktop-header-hover-color",
                    "type" => "color",
                    "label" => "Hover",
                    "target" => self::$target["column_hover"],
                    "default" => "#ffffff",
                    "extra" => [
                        "css_property_name" => "color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-header-bg-color",
                    "type" => "color",
                    "label" => "Normal",
                    "target" => self::$target["column"],
                    "default" => self::$default["primary"],
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-header-bg-hover-color",
                    "type" => "color",
                    "label" => "Hover",
                    "target" => self::$target["column_hover"],
                    "default" => self::$default["primary"],
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-header-padding-top",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["column"],
                    "default" => "17",
                    "extra" => [
                        "css_property_name" => "padding-top",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-header-padding-right",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["column"],
                    "default" => "13",
                    "extra" => [
                        "css_property_name" => "padding-right",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-header-padding-bottom",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["column"],
                    "default" => "17",
                    "extra" => [
                        "css_property_name" => "padding-bottom",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-header-padding-left",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["column"],
                    "default" => "13",
                    "extra" => [
                        "css_property_name" => "padding-left",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-header-border-top-width",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["column"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-top-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-header-border-right-width",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["column"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-right-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-header-border-bottom-width",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["column"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-bottom-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-header-border-left-width",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["column"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-left-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-header-border-style",
                    "type" => "select",
                    "placeholder" => "Border Style",
                    "options" => Common::get_border_style(),
                    "target" => self::$target["column"],
                    "default" => "solid",
                    "extra" => [
                        "css_property_name" => "border-style",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-header-border-color",
                    "type" => "color",
                    "label" => "Color",
                    "target" => self::$target["column"],
                    "default" => self::$default["border"],
                    "extra" => [
                        "css_property_name" => "border-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-header-text-align",
                    "type" => "select-grid",
                    "options" => Common::get_text_alignment(),
                    "default" => "left",
                    "target" => self::$target["column"],
                    "extra" => [
                        "iconSize" => "small",
                        "css_property_name" => "text-align",
                        "breakpoint" => "desktop",
                    ],
                ],
            ];

        }

        private static function get_mobile_field()
        {
            return [
                [
                    "id" => "mobile-header-font-family",
                    "type" => "select",
                    "target" => self::$target["column"],
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
                    "id" => "mobile-header-font-size",
                    "type" => "pixel",
                    //"label" => "Size",
                    "target" => self::$target["column"],
                    "default" => "16",
                    "extra" => [
                        "css_property_name" => "font-size",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                // [
                //     "id" => "mobile-header-color",
                //     "type" => "color",
                //     "label" => "Normal",
                //     "target" => self::$target["column"],
                //     "default" => "#dddddd",
                //     "extra" => [
                //         "css_property_name" => "color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                // [
                //     "id" => "mobile-header-hover-color",
                //     "type" => "color",
                //     "label" => "Hover",
                //     "target" => self::$target["column_hover"],
                //     "default" => "#dddddd",
                //     "extra" => [
                //         "css_property_name" => "color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                // [
                //     "id" => "mobile-header-bg-color",
                //     "type" => "color",
                //     "label" => "Normal",
                //     "target" => self::$target["column"],
                //     "default" => self::$default["primary"],
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                // [
                //     "id" => "mobile-header-bg-hover-color",
                //     "type" => "color",
                //     "label" => "Hover",
                //     "target" => self::$target["column_hover"],
                //     "default" => "#ffffff",
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                [
                    "id" => "mobile-header-padding-top",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["column"],
                    "default" => "17",
                    "extra" => [
                        "css_property_name" => "padding-top",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-header-padding-right",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["column"],
                    "default" => "13",
                    "extra" => [
                        "css_property_name" => "padding-right",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-header-padding-bottom",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["column"],
                    "default" => "17",
                    "extra" => [
                        "css_property_name" => "padding-bottom",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-header-padding-left",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["column"],
                    "default" => "13",
                    "extra" => [
                        "css_property_name" => "padding-left",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-header-border-top-width",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["column"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-top-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-header-border-right-width",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["column"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-right-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-header-border-bottom-width",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["column"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-bottom-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-header-border-left-width",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["column"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-left-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-header-border-style",
                    "type" => "select",
                    "placeholder" => "Border Style",
                    "options" => Common::get_border_style(),
                    "target" => self::$target["column"],
                    "default" => "solid",
                    "extra" => [
                        "css_property_name" => "border-style",
                        "breakpoint" => "mobile",
                    ],
                ],
                // [
                //     "id" => "mobile-header-border-color",
                //     "type" => "color",
                //     "label" => "Color",
                //     "target" => self::$target["column"],
                //     "default" => self::$default["border"],
                //     "extra" => [
                //         "css_property_name" => "border-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                [
                    "id" => "mobile-header-text-align",
                    "type" => "select-grid",
                    "options" => Common::get_text_alignment(),
                    "default" => "center",
                    "target" => self::$target["column"],
                    "extra" => [
                        "iconSize" => "small",
                        "css_property_name" => "text-align",
                        "breakpoint" => "mobile",
                    ],
                ],
            ];
        }
    }
}
