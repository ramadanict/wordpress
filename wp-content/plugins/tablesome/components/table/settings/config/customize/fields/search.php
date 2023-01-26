<?php

namespace Tablesome\Components\Table\Settings\Config\Customize\Fields;

use \Tablesome\Components\Table\Settings\Config\Common;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Customize\Fields\Search')) {
    class Search
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
                "id" => "search-breakpoint-device",
                "type" => "device",
                "options" => Common::get_breakpoint(),
                "default" => "desktop",
            ]];

            return array_merge($breakpoint, self::get_desktop_field(), self::get_mobile_field());
        }

        public static function get_selectors()
        {

            $selectors = [
                "search" => ".tablesome-header__wrapper .tablesome-search input[type=search].tablesome-search__input",
                "search_hover" => ".tablesome-header__wrapper .tablesome-search input[type=search].tablesome-search__input:hover",
            ];

            $selectors["search_with_focus"] = $selectors["search"] . ", .tablesome-header__wrapper .tablesome-search input[type=search].tablesome-search__input:focus";

            $selectors["search_placeholder"] = $selectors["search"] . ", .tablesome-header__wrapper .tablesome-search input[type=search].tablesome-search__input::placeholder";

            $selectors["search_icon_and_placeholder"] = $selectors["search"] . ", .tablesome-header__wrapper .tablesome-search .tablesome-search__icon, .tablesome-header__wrapper .tablesome-search input[type=search].tablesome-search__input::placeholder";

            return $selectors;

        }

        private static function get_desktop_field()
        {

            return [
                [
                    "id" => "desktop-search-font-family",
                    "type" => "select",
                    "target" => self::$target["search_placeholder"],
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
                    "id" => "desktop-search-font-size",
                    "type" => "pixel",
                    "// label" => "Size",
                    "target" => self::$target["search_placeholder"],
                    "default" => "16",
                    "extra" => [
                        "css_property_name" => "font-size",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-search-color",
                    "type" => "color",
                    "label" => "Normal",
                    "target" => self::$target["search_icon_and_placeholder"],
                    "default" => self::$default["primary"],
                    "extra" => [
                        "css_property_name" => "color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-search-hover-color",
                    "type" => "color",
                    "label" => "Hover",
                    "target" => self::$target["search_hover"],
                    "default" => self::$default["primary"],
                    "extra" => [
                        "css_property_name" => "color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-search-bg-color",
                    "type" => "color",
                    "label" => "Normal",
                    "target" => self::$target["search"],
                    "default" => "#ffffff",
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-search-bg-hover-color",
                    "type" => "color",
                    "label" => "Hover",
                    "target" => self::$target["search_hover"],
                    "default" => "#ffffff",
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-search-padding-top",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["search"],
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "padding-top",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-search-padding-right",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["search"],
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "padding-right",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-search-padding-bottom",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["search"],
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "padding-bottom",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-search-padding-left",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["search"],
                    "default" => "28",
                    "extra" => [
                        "css_property_name" => "padding-left",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-search-border-top-width",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["search_with_focus"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-top-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-search-border-right-width",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["search_with_focus"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-right-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-search-border-bottom-width",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["search_with_focus"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-bottom-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-search-border-left-width",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["search_with_focus"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-left-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-search-border-style",
                    "type" => "select",
                    "options" => Common::get_border_style(),
                    "target" => self::$target["search_with_focus"],
                    "default" => "solid",
                    "extra" => [
                        "css_property_name" => "border-style",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-search-border-color",
                    "type" => "color",
                    "label" => "Color",
                    "target" => self::$target["search_with_focus"],
                    "default" => self::$default["border"],
                    "extra" => [
                        "css_property_name" => "border-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-search-text-align",
                    "type" => "select-grid",
                    "options" => Common::get_text_alignment(),
                    "default" => "left",
                    "target" => self::$target["search"],
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
                    "id" => "mobile-search-font-family",
                    "type" => "select",
                    "target" => self::$target["search_placeholder"],
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
                    "id" => "mobile-search-font-size",
                    "type" => "pixel",
                    "// label" => "Size",
                    "target" => self::$target["search_placeholder"],
                    "default" => "16",
                    "extra" => [
                        "css_property_name" => "font-size",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                // [
                //     "id" => "mobile-search-color",
                //     "type" => "color",
                //     "label" => "Normal",
                //     "target" => self::$target["search_icon_and_placeholder"],
                //     "default" => self::$default["primary"],
                //     "extra" => [
                //         "css_property_name" => "color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                // [
                //     "id" => "mobile-search-hover-color",
                //     "type" => "color",
                //     "label" => "Hover",
                //     "target" => self::$target["search_hover"],
                //     "default" => self::$default["primary"],
                //     "extra" => [
                //         "css_property_name" => "color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                // [
                //     "id" => "mobile-search-bg-color",
                //     "type" => "color",
                //     "label" => "Normal",
                //     "target" => self::$target["search"],
                //     "default" => "#ffffff",
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                // [
                //     "id" => "mobile-search-bg-hover-color",
                //     "type" => "color",
                //     "label" => "Hover",
                //     "target" => self::$target["search_hover"],
                //     "default" => "#ffffff",
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                [
                    "id" => "mobile-search-padding-top",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["search"],
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "padding-top",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-search-padding-right",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["search"],
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "padding-right",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-search-padding-bottom",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["search"],
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "padding-bottom",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-search-padding-left",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["search"],
                    "default" => "40",
                    "extra" => [
                        "css_property_name" => "padding-left",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-search-border-top-width",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["search_with_focus"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-top-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-search-border-right-width",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["search_with_focus"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-right-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-search-border-bottom-width",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["search_with_focus"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-bottom-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-search-border-left-width",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["search_with_focus"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-left-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-search-border-style",
                    "type" => "select",
                    "options" => Common::get_border_style(),
                    "target" => self::$target["search_with_focus"],
                    "default" => "solid",
                    "extra" => [
                        "css_property_name" => "border-style",
                        "breakpoint" => "mobile",
                    ],
                ],
                // [
                //     "id" => "mobile-search-border-color",
                //     "type" => "color",
                //     "label" => "Color",
                //     "target" => self::$target["search_with_focus"],
                //     "default" => self::$default["border"],
                //     "extra" => [
                //         "css_property_name" => "border-color",
                //         "breakpoint" => "mobile",
                //         "unit" => "px",
                //     ],
                // ],
                [
                    "id" => "mobile-search-text-align",
                    "type" => "select-grid",
                    "options" => Common::get_text_alignment(),
                    "default" => "left",
                    "target" => self::$target["search"],
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
