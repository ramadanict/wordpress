<?php

namespace Tablesome\Components\Table\Settings\Config\Customize\Layout;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Customize\Layout\Pagination')) {
    class Pagination
    {
        public static function get_layout()
        {
            $breakpoint = [[
                "type" => "center",
                "fields" => ["pagination-breakpoint-device"],
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
                        "desktop-pagination-font-size",
                        "desktop-pagination-font-family",
                    ],
                    "dependency" => [
                        [
                            "id" => "pagination-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Text Color",
                    "axis" => "x",
                    "fields" => ["desktop-pagination-color", "desktop-pagination-hover-color"],
                    "dependency" => [
                        [
                            "id" => "pagination-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Background Color",
                    "axis" => "x",
                    "fields" => ["desktop-pagination-bg-color", "desktop-pagination-bg-hover-color"],
                    "dependency" => [
                        [
                            "id" => "pagination-breakpoint-device",
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
                        "desktop-pagination-padding-top",
                        "desktop-pagination-padding-right",
                        "desktop-pagination-padding-bottom",
                        "desktop-pagination-padding-left",
                    ],
                    "dependency" => [
                        [
                            "id" => "pagination-breakpoint-device",
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
                                "desktop-pagination-border-top-width",
                                "desktop-pagination-border-right-width",
                                "desktop-pagination-border-bottom-width",
                                "desktop-pagination-border-left-width",
                            ],
                        ],
                        "desktop-pagination-border-style",
                        "desktop-pagination-border-color",
                    ],
                    "dependency" => [
                        [
                            "id" => "pagination-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],

                [
                    "type" => "fieldset",
                    "label" => "Pagination Alignment",
                    "fields" => ["desktop-pagination-alignment"],
                    "dependency" => [
                        [
                            "id" => "pagination-breakpoint-device",
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
                        "mobile-pagination-font-size",
                        "mobile-pagination-font-family",
                    ],
                    "dependency" => [
                        [
                            "id" => "pagination-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
                // [
                //     "type" => "fieldset",
                //     "label" => "Text Color",
                //     "axis" => "x",
                //     "fields" => ["mobile-pagination-color", "mobile-pagination-hover-color"],
                //     "dependency" => [
                //         [
                //             "id" => "pagination-breakpoint-device",
                //             "operator" => "==",
                //             "value" => "mobile",
                //         ],
                //     ],
                // ],
                // [
                //     "type" => "fieldset",
                //     "label" => "Background Color",
                //     "axis" => "x",
                //     "fields" => ["mobile-pagination-bg-color", "mobile-pagination-bg-hover-color"],
                //     "dependency" => [
                //         [
                //             "id" => "pagination-breakpoint-device",
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
                        "mobile-pagination-padding-top",
                        "mobile-pagination-padding-right",
                        "mobile-pagination-padding-bottom",
                        "mobile-pagination-padding-left",
                    ],
                    "dependency" => [
                        [
                            "id" => "pagination-breakpoint-device",
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
                                "mobile-pagination-border-top-width",
                                "mobile-pagination-border-right-width",
                                "mobile-pagination-border-bottom-width",
                                "mobile-pagination-border-left-width",
                            ],
                        ],
                        "mobile-pagination-border-style",
                        // "mobile-pagination-border-color",
                    ],
                    "dependency" => [
                        [
                            "id" => "pagination-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Pagination Alignment",
                    "fields" => ["mobile-pagination-alignment"],
                    "dependency" => [
                        [
                            "id" => "pagination-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
            ];
        }
    }
}
