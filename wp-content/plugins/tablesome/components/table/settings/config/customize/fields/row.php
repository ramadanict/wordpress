<?php

namespace Tablesome\Components\Table\Settings\Config\Customize\Fields;

use \Tablesome\Components\Table\Settings\Config\Common;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Customize\Fields\Row')) {
    class Row
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
                "id" => "row-breakpoint-device",
                "type" => "device",
                "options" => Common::get_breakpoint(),
                "default" => "desktop",
            ]];

            return array_merge($breakpoint, self::get_desktop_field(), self::get_mobile_field());
        }

        public static function get_selectors()
        {
            $selectors = [
                "row" => ".tablesome__table .tablesome__row > .tablesome__cell",
                "row_hover" => ".tablesome__table .tablesome__row:hover > .tablesome__cell",
                "row_alternate" => ".tablesome__table .tablesome__row:nth-child(even) > .tablesome__cell",
                "row_alternate_hover" => ".tablesome__table .tablesome__row:nth-child(even):hover > .tablesome__cell",
            ];
            $selectors["row_with_calculate_cell"] = $selectors["row"] . ", " . ".tablesome__table tr > .calculate__cell";

            return $selectors;
        }

        private static function get_desktop_field()
        {

            return [
                [
                    "id" => "desktop-row-font-family",
                    "type" => "select",
                    "target" => self::$target["row_with_calculate_cell"],
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
                    "id" => "desktop-row-font-size",
                    "type" => "pixel",
                    // "label" => "Size",
                    "target" => self::$target["row"],
                    "default" => "16",
                    "extra" => [
                        "css_property_name" => "font-size",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-row-color",
                    "type" => "color",
                    "label" => "Normal",
                    "target" => self::$target["row_with_calculate_cell"],
                    "default" => "#dddddd",
                    "extra" => [
                        "css_property_name" => "color",
                        "breakpoint" => "desktop",
                    ],
                ],
                [
                    "id" => "desktop-row-hover-color",
                    "type" => "color",
                    "label" => "Hover",
                    "target" => self::$target["row_hover"],
                    "default" => "#dddddd",
                    "extra" => [
                        "css_property_name" => "color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-row-bg-color",
                    "type" => "color",
                    "label" => "Normal",
                    "target" => self::$target["row"],
                    "default" => self::$default["primary"],
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-row-bg-hover-color",
                    "type" => "color",
                    "label" => "Hover",
                    "target" => self::$target["row_hover"],
                    "default" => "#ffffff",
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-row-alternate",
                    "type" => "toggle",
                    "label" => "Enable Alternate Background",
                    "default" => false,
                ],

                [
                    "id" => "desktop-row-alternate-bg-color",
                    "type" => "color",
                    "label" => "Normal",
                    "target" => self::$target["row_alternate"],
                    "default" => self::$default["primary"],
                    "dependency" => [
                        [
                            "id" => "desktop-row-alternate",
                            "operator" => "==",
                            "value" => true,
                        ],
                    ],
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-row-alternate-bg-hover-color",
                    "type" => "color",
                    "label" => "Hover",
                    "target" => self::$target["row_alternate_hover"],
                    "default" => self::$default["secondary"],
                    "dependency" => [
                        [
                            "id" => "desktop-row-alternate",
                            "operator" => "==",
                            "value" => true,
                        ],
                    ],
                    "extra" => [
                        "css_property_name" => "background-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-row-padding-top",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["row"],
                    "default" => "17",
                    "extra" => [
                        "css_property_name" => "padding-top",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-row-padding-right",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["row"],
                    "default" => "13",
                    "extra" => [
                        "css_property_name" => "padding-right",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-row-padding-bottom",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["row"],
                    "default" => "17",
                    "extra" => [
                        "css_property_name" => "padding-bottom",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-row-padding-left",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["row"],
                    "default" => "13",
                    "extra" => [
                        "css_property_name" => "padding-left",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-row-border-top-width",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["row"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-top-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-row-border-right-width",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["row"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-right-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-row-border-bottom-width",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["row"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-bottom-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-row-border-left-width",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["row"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-left-width",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-row-border-style",
                    "type" => "select",
                    "options" => Common::get_border_style(),
                    "target" => self::$target["row"],
                    "default" => "solid",
                    "extra" => [
                        "css_property_name" => "border-style",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-row-border-color",
                    "type" => "color",
                    "label" => "Color",
                    "target" => self::$target["row"],
                    "default" => self::$default["border"],
                    "extra" => [
                        "css_property_name" => "border-color",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-row-text-align",
                    "type" => "select-grid",
                    "options" => Common::get_text_alignment(),
                    "default" => "left",
                    "target" => self::$target["row"],
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
                    "id" => "mobile-row-font-family",
                    "type" => "select",
                    "target" => self::$target["row_with_calculate_cell"],
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
                    "id" => "mobile-row-font-size",
                    "type" => "pixel",
                    //"label" => "Size",
                    "target" => self::$target["row"],
                    "default" => "16",
                    "extra" => [
                        "css_property_name" => "font-size",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                // [
                //     "id" => "mobile-row-color",
                //     "type" => "color",
                //     "label" => "Color",
                //     "target" => self::$target["row_with_calculate_cell"],
                //     "default" => "#dddddd",
                //     "extra" => [
                //         "css_property_name" => "color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                // [
                //     "id" => "mobile-row-hover-color",
                //     "type" => "color",
                //     "label" => "Color",
                //     "target" => self::$target["row_hover"],
                //     "default" => "#dddddd",
                //     "extra" => [
                //         "css_property_name" => "color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                // [
                //     "id" => "mobile-row-bg-color",
                //     "type" => "color",
                //     "label" => "Normal",
                //     "target" => self::$target["row"],
                //     "default" => self::$default["primary"],
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                // [
                //     "id" => "mobile-row-bg-hover-color",
                //     "type" => "color",
                //     "label" => "Hover",
                //     "target" => self::$target["row_hover"],
                //     "default" => "#ffffff",
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                // [
                //     "id" => "mobile-row-alternate",
                //     "type" => "toggle",
                //     "label" => "Enable Alternate Background",
                //     "default" => false,
                // ],

                // [
                //     "id" => "mobile-row-alternate-bg-color",
                //     "type" => "color",
                //     "label" => "Normal",
                //     "target" => self::$target["row_alternate"],
                //     "default" => self::$default["primary"],
                //     "dependency" => [
                //         [
                //             "id" => "mobile-row-alternate",
                //             "operator" => "==",
                //             "value" => true,
                //         ],
                //     ],
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],

                // [
                //     "id" => "mobile-row-alternate-bg-hover-color",
                //     "type" => "color",
                //     "label" => "Hover",
                //     "target" => self::$target["row_alternate_hover"],
                //     "default" => self::$default["secondary"],
                //     "dependency" => [
                //         [
                //             "id" => "mobile-row-alternate",
                //             "operator" => "==",
                //             "value" => true,
                //         ],
                //     ],
                //     "extra" => [
                //         "css_property_name" => "background-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                [
                    "id" => "mobile-row-padding-top",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["row"],
                    "default" => "17",
                    "extra" => [
                        "css_property_name" => "padding-top",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-row-padding-right",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["row"],
                    "default" => "13",
                    "extra" => [
                        "css_property_name" => "padding-right",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-row-padding-bottom",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["row"],
                    "default" => "17",
                    "extra" => [
                        "css_property_name" => "padding-bottom",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-row-padding-left",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["row"],
                    "default" => "13",
                    "extra" => [
                        "css_property_name" => "padding-left",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-row-border-top-width",
                    "type" => "pixel",
                    "label" => "top",
                    "target" => self::$target["row"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-top-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-row-border-right-width",
                    "type" => "pixel",
                    "label" => "right",
                    "target" => self::$target["row"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-right-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-row-border-bottom-width",
                    "type" => "pixel",
                    "label" => "bottom",
                    "target" => self::$target["row"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-bottom-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-row-border-left-width",
                    "type" => "pixel",
                    "label" => "left",
                    "target" => self::$target["row"],
                    "default" => "1",
                    "extra" => [
                        "css_property_name" => "border-left-width",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-row-border-style",
                    "type" => "select",
                    "options" => Common::get_border_style(),
                    "target" => self::$target["row"],
                    "default" => "solid",
                    "extra" => [
                        "css_property_name" => "border-style",
                        "breakpoint" => "mobile",
                    ],
                ],
                // [
                //     "id" => "mobile-row-border-color",
                //     "type" => "color",
                //     "label" => "Color",
                //     "target" => self::$target["row"],
                //     "default" => self::$default["border"],
                //     "extra" => [
                //         "css_property_name" => "border-color",
                //         "breakpoint" => "mobile",
                //     ],
                // ],
                [
                    "id" => "mobile-row-text-align",
                    "type" => "select-grid",
                    "options" => Common::get_text_alignment(),
                    "default" => "center",
                    "target" => self::$target["row"],
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
