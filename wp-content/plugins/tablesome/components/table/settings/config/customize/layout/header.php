<?php

namespace Tablesome\Components\Table\Settings\Config\Customize\Layout;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Customize\Layout\Header')) {
    class Header
    {
        public static function get_layout()
        {
            $breakpoint = [[
                "type" => "center",
                "fields" => ["header-breakpoint-device"],
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
                        "desktop-header-font-size",
                        "desktop-header-font-family",
                    ],
                    "dependency" => [
                        [
                            "id" => "header-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Text Color",
                    "axis" => "x",
                    "fields" => ["desktop-header-color", "desktop-header-hover-color"],
                    "dependency" => [
                        [
                            "id" => "header-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Background Color",
                    "axis" => "x",
                    "fields" => ["desktop-header-bg-color", "desktop-header-bg-hover-color"],
                    "dependency" => [
                        [
                            "id" => "header-breakpoint-device",
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
                        "desktop-header-padding-top",
                        "desktop-header-padding-right",
                        "desktop-header-padding-bottom",
                        "desktop-header-padding-left",
                    ],
                    "dependency" => [
                        [
                            "id" => "header-breakpoint-device",
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
                                "desktop-header-border-top-width",
                                "desktop-header-border-right-width",
                                "desktop-header-border-bottom-width",
                                "desktop-header-border-left-width",
                            ],
                        ],
                        "desktop-header-border-style",
                        "desktop-header-border-color",
                    ],
                    "dependency" => [
                        [
                            "id" => "header-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],

                [
                    "type" => "fieldset",
                    "label" => "Text Alignment",
                    "fields" => ["desktop-header-text-align"],
                    "dependency" => [
                        [
                            "id" => "header-breakpoint-device",
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
                        "mobile-header-font-size",
                        "mobile-header-font-family",
                    ],
                    "dependency" => [
                        [
                            "id" => "header-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
                // [
                //     "type" => "fieldset",
                //     "label" => "Text Color",
                //     "axis" => "x",
                //     "fields" => ["mobile-header-color", "mobile-header-hover-color"],
                //     "dependency" => [
                //         [
                //             "id" => "header-breakpoint-device",
                //             "operator" => "==",
                //             "value" => "mobile",
                //         ],
                //     ],
                // ],
                // [
                //     "type" => "fieldset",
                //     "label" => "Background Color",
                //     "axis" => "x",
                //     "fields" => ["mobile-header-bg-color", "mobile-header-bg-hover-color"],
                //     "dependency" => [
                //         [
                //             "id" => "header-breakpoint-device",
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
                        "mobile-header-padding-top",
                        "mobile-header-padding-right",
                        "mobile-header-padding-bottom",
                        "mobile-header-padding-left",
                    ],
                    "dependency" => [
                        [
                            "id" => "header-breakpoint-device",
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
                                "mobile-header-border-top-width",
                                "mobile-header-border-right-width",
                                "mobile-header-border-bottom-width",
                                "mobile-header-border-left-width",
                            ],
                        ],
                        "mobile-header-border-style",
                        // "mobile-header-border-color",
                    ],
                    "dependency" => [
                        [
                            "id" => "header-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],

                [
                    "type" => "fieldset",
                    "label" => "Text Alignment",
                    "fields" => ["mobile-header-text-align"],
                    "dependency" => [
                        [
                            "id" => "header-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
            ];
        }
    }
}
