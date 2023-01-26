<?php

namespace Tablesome\Components\Table\Settings\Config\Customize\Fields;

use \Tablesome\Components\Table\Settings\Config\Common;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Customize\Fields\Filter')) {
    class Filter
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
                "id" => "filter-breakpoint-device",
                "type" => "device",
                "options" => Common::get_breakpoint(),
                "default" => "desktop",
            ]];

            return array_merge($breakpoint, self::get_desktop_field(), self::get_mobile_field());
        }

        public static function get_selectors()
        {
            $selectors = [
                "filter" => ".tablesome-header__wrapper .tablesome-filter .tablesome-filter__button, .tablesome-header__wrapper .tablesome-export .tablesome-export__button",
                "filter_hover" => ".tablesome-header__wrapper .tablesome-filter .tablesome-filter__button:hover, .tablesome-header__wrapper .tablesome-export .tablesome-export__button:hover",
            ];

            return $selectors;
        }

        private static function get_desktop_field()
        {

            return [
                [
                    "id" => "desktop-filter-font-family",
                    "type" => "select",
                    "target" => self::$target["filter"],
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
                    "id" => "desktop-filter-font-size",
                    "type" => "pixel",
                    "// label" => "Size",
                    "target" => self::$target["filter"],
                    "default" => "16",
                    "extra" => [
                        "css_property_name" => "font-size",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-filter-color",
                    "type" => "color",
                    "label" => "Normal",
                    "target" => self::$target["filter"],
                    "default" => self::$default["primary"],
                    "extra" => [
                        "css_property_name" => "color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-filter-hover-color",
                    "type" => "color",
                    "label" => "Hover",
                    "target" => self::$target["filter_hover"],
                    "default" => "#ffffff",
                    "extra" => [
                        "css_property_name" => "color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-filter-bg-color",
                    "type" => "color",
                    "label" => "Normal",
                    "target" => self::$target["filter"],
                    "default" => "#ffffff",
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-filter-bg-hover-color",
                    "type" => "color",
                    "label" => "Hover",
                    "target" => self::$target["filter_hover"],
                    "default" => self::$default["primary"],
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-filter-padding-top",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["filter"],
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "padding-top",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-filter-padding-right",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["filter"],
                    "default" => "15",
                    "extra" => [
                        "css_property_name" => "padding-right",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-filter-padding-bottom",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["filter"],
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "padding-bottom",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-filter-padding-left",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["filter"],
                    "default" => "15",
                    "extra" => [
                        "css_property_name" => "padding-left",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-filter-border-top-width",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["filter"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-top-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-filter-border-right-width",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["filter"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-right-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-filter-border-bottom-width",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["filter"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-bottom-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-filter-border-left-width",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["filter"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-left-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-filter-border-style",
                    "type" => "select",
                    "options" => Common::get_border_style(),
                    "target" => self::$target["filter"],
                    "default" => "solid",
                    "extra" => [
                        "css_property_name" => "border-style",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-filter-border-color",
                    "type" => "color",
                    "label" => "Color",
                    "target" => self::$target["filter"],
                    "default" => self::$default["border"],
                    "extra" => [
                        "css_property_name" => "border-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-filter-text-align",
                    "type" => "select-grid",
                    "options" => Common::get_text_alignment(),
                    "default" => "left",
                    "target" => self::$target["filter"],
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
                    "id" => "mobile-filter-font-family",
                    "type" => "select",
                    "target" => self::$target["filter"],
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
                    "id" => "mobile-filter-font-size",
                    "type" => "pixel",
                    "// label" => "Size",
                    "target" => self::$target["filter"],
                    "default" => "16",
                    "extra" => [
                        "css_property_name" => "font-size",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                // [
                //     "id" => "mobile-filter-color",
                //     "type" => "color",
                //     "label" => "Normal",
                //     "target" => self::$target["filter"],
                //     "default" => self::$default["primary"],
                //     "extra" => [
                //         "css_property_name" => "color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                // [
                //     "id" => "mobile-filter-hover-color",
                //     "type" => "color",
                //     "label" => "Hover",
                //     "target" => self::$target["filter_hover"],
                //     "default" => "#ffffff",
                //     "extra" => [
                //         "css_property_name" => "color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                // [
                //     "id" => "mobile-filter-bg-color",
                //     "type" => "color",
                //     "label" => "Normal",
                //     "target" => self::$target["filter"],
                //     "default" => "#ffffff",
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                // [
                //     "id" => "mobile-filter-bg-hover-color",
                //     "type" => "color",
                //     "label" => "Hover",
                //     "target" => self::$target["filter_hover"],
                //     "default" => self::$default["primary"],
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                [
                    "id" => "mobile-filter-padding-top",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["filter"],
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "padding-top",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-filter-padding-right",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["filter"],
                    "default" => "15",
                    "extra" => [
                        "css_property_name" => "padding-right",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-filter-padding-bottom",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["filter"],
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "padding-bottom",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-filter-padding-left",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["filter"],
                    "default" => "15",
                    "extra" => [
                        "css_property_name" => "padding-left",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-filter-border-top-width",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["filter"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-top-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-filter-border-right-width",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["filter"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-right-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-filter-border-bottom-width",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["filter"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-bottom-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-filter-border-left-width",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["filter"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-left-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-filter-border-style",
                    "type" => "select",
                    "options" => Common::get_border_style(),
                    "target" => self::$target["filter"],
                    "default" => "solid",
                    "extra" => [
                        "css_property_name" => "border-style",
                        "breakpoint" => "mobile",
                    ],
                ],
                // [
                //     "id" => "mobile-filter-border-color",
                //     "type" => "color",
                //     "label" => "Color",
                //     "target" => self::$target["filter"],
                //     "default" => self::$default["border"],
                //     "extra" => [
                //         "css_property_name" => "border-color",
                //         "breakpoint" => "mobile",
                //         "unit" => "px",
                //     ],
                // ],
                [
                    "id" => "mobile-filter-text-align",
                    "type" => "select-grid",
                    "options" => Common::get_text_alignment(),
                    "default" => "left",
                    "target" => self::$target["filter"],
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
