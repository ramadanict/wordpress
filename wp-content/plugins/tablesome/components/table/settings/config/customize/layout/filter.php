<?php

namespace Tablesome\Components\Table\Settings\Config\Customize\Layout;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Customize\Layout\Filter')) {
    class Filter
    {
        public static function get_layout()
        {
            $breakpoint = [[
                "type" => "center",
                "fields" => ["filter-breakpoint-device"],
            ]];
            return array_merge($breakpoint, self::get_desktop_layout(), self::get_mobile_layout());
        }

        private static function get_desktop_layout()
        {
            return [
                [
                    "type" => "fieldset",
                    "label" => "Font",
                    "axis" => "x",
                    "fields" => [
                        "desktop-filter-font-size",
                        "desktop-filter-font-family",
                    ],
                    "dependency" => [
                        [
                            "id" => "filter-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Text Color",
                    "axis" => "x",
                    "fields" => ["desktop-filter-color", "desktop-filter-hover-color"],
                    "dependency" => [
                        [
                            "id" => "filter-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Background Color",
                    "axis" => "x",
                    "fields" => ["desktop-filter-bg-color", "desktop-filter-bg-hover-color"],
                    "dependency" => [
                        [
                            "id" => "filter-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Padding",
                    "axis" => "x",
                    "fields" => [
                        "desktop-filter-padding-top",
                        "desktop-filter-padding-right",
                        "desktop-filter-padding-bottom",
                        "desktop-filter-padding-left",
                    ],
                    "dependency" => [
                        [
                            "id" => "filter-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],

                [
                    "type" => "fieldset",
                    "label" => "Border",
                    "axis" => "y",
                    "fields" => [
                        [
                            "type" => "fieldset",
                            "label" => "Width",
                            "margin" => "zero",
                            "axis" => "x",
                            "fields" => [
                                "desktop-filter-border-top-width",
                                "desktop-filter-border-right-width",
                                "desktop-filter-border-bottom-width",
                                "desktop-filter-border-left-width",
                            ],
                        ],
                        "desktop-filter-border-style",
                        "desktop-filter-border-color",
                    ],
                    "dependency" => [
                        [
                            "id" => "filter-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],

                [
                    "type" => "fieldset",
                    "label" => "Text Alignment",
                    "fields" => ["desktop-filter-text-align"],
                    "dependency" => [
                        [
                            "id" => "filter-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
            ];
        }

        private static function get_mobile_layout()
        {
            return [
                [
                    "type" => "fieldset",
                    "label" => "Font",
                    "axis" => "x",
                    "fields" => [
                        "mobile-filter-font-size",
                        "mobile-filter-font-family",
                    ],
                    "dependency" => [
                        [
                            "id" => "filter-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
                // [
                //     "type" => "fieldset",
                //     "label" => "Text Color",
                //     "axis" => "x",
                //     "fields" => ["mobile-filter-color", "mobile-filter-hover-color"],
                //     "dependency" => [
                //         [
                //             "id" => "filter-breakpoint-device",
                //             "operator" => "==",
                //             "value" => "mobile",
                //         ],
                //     ],
                // ],
                // [
                //     "type" => "fieldset",
                //     "label" => "Background Color",
                //     "axis" => "x",
                //     "fields" => ["mobile-filter-bg-color", "mobile-filter-bg-hover-color"],
                //     "dependency" => [
                //         [
                //             "id" => "filter-breakpoint-device",
                //             "operator" => "==",
                //             "value" => "mobile",
                //         ],
                //     ],
                // ],
                [
                    "type" => "fieldset",
                    "label" => "Padding",
                    "axis" => "x",
                    "fields" => [
                        "mobile-filter-padding-top",
                        "mobile-filter-padding-right",
                        "mobile-filter-padding-bottom",
                        "mobile-filter-padding-left",
                    ],
                    "dependency" => [
                        [
                            "id" => "filter-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Border",
                    "axis" => "y",
                    "fields" => [
                        [
                            "type" => "fieldset",
                            "label" => "Width",
                            "margin" => "zero",
                            "axis" => "x",
                            "fields" => [
                                "desktop-filter-border-top-width",
                                "desktop-filter-border-right-width",
                                "desktop-filter-border-bottom-width",
                                "desktop-filter-border-left-width",
                            ],
                        ],
                        "mobile-filter-border-style",
                        // "mobile-filter-border-color",
                    ],
                    "dependency" => [
                        [
                            "id" => "filter-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Text Alignment",
                    "fields" => ["mobile-filter-text-align"],
                    "dependency" => [
                        [
                            "id" => "filter-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
            ];
        }
    }
}
