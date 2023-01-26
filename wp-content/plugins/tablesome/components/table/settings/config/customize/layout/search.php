<?php

namespace Tablesome\Components\Table\Settings\Config\Customize\Layout;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Customize\Layout\Search')) {
    class Search
    {
        public static function get_layout()
        {
            $breakpoint = [[
                "type" => "center",
                "fields" => ["search-breakpoint-device"],
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
                        "desktop-search-font-size",
                        "desktop-search-font-family",
                    ],
                    "dependency" => [
                        [
                            "id" => "search-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Text Color",
                    "axis" => "x",
                    "fields" => ["desktop-search-color", "desktop-search-hover-color"],
                    "dependency" => [
                        [
                            "id" => "search-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Background Color",
                    "axis" => "x",
                    "fields" => ["desktop-search-bg-color", "desktop-search-bg-hover-color"],
                    "dependency" => [
                        [
                            "id" => "search-breakpoint-device",
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
                        "desktop-search-padding-top",
                        "desktop-search-padding-right",
                        "desktop-search-padding-bottom",
                        "desktop-search-padding-left",
                    ],
                    "dependency" => [
                        [
                            "id" => "search-breakpoint-device",
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
                                "desktop-search-border-top-width",
                                "desktop-search-border-right-width",
                                "desktop-search-border-bottom-width",
                                "desktop-search-border-left-width",
                            ],
                        ],
                        "desktop-search-border-style",
                        "desktop-search-border-color",
                    ],
                    "dependency" => [
                        [
                            "id" => "search-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],

                [
                    "type" => "fieldset",
                    "label" => "Text Alignment",
                    "fields" => ["desktop-search-text-align"],
                    "dependency" => [
                        [
                            "id" => "search-breakpoint-device",
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
                        "mobile-search-font-size",
                        "mobile-search-font-family",
                    ],
                    "dependency" => [
                        [
                            "id" => "search-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
                // [
                //     "type" => "fieldset",
                //     "label" => "Text Color",
                //     "axis" => "x",
                //     "fields" => ["mobile-search-color", "mobile-search-hover-color"],
                //     "dependency" => [
                //         [
                //             "id" => "search-breakpoint-device",
                //             "operator" => "==",
                //             "value" => "mobile",
                //         ],
                //     ],
                // ],
                // [
                //     "type" => "fieldset",
                //     "label" => "Background Color",
                //     "axis" => "x",
                //     "fields" => ["mobile-search-bg-color", "mobile-search-bg-hover-color"],
                //     "dependency" => [
                //         [
                //             "id" => "search-breakpoint-device",
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
                        "mobile-search-padding-top",
                        "mobile-search-padding-right",
                        "mobile-search-padding-bottom",
                        "mobile-search-padding-left",
                    ],
                    "dependency" => [
                        [
                            "id" => "search-breakpoint-device",
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
                                "mobile-search-border-top-width",
                                "mobile-search-border-right-width",
                                "mobile-search-border-bottom-width",
                                "mobile-search-border-left-width",
                            ],
                        ],
                        "mobile-search-border-style",
                        // "mobile-search-border-color",
                    ],
                    "dependency" => [
                        [
                            "id" => "search-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Text Alignment",
                    "fields" => ["mobile-search-text-align"],
                    "dependency" => [
                        [
                            "id" => "search-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
            ];
        }
    }
}
