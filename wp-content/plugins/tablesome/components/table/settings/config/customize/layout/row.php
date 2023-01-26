<?php

namespace Tablesome\Components\Table\Settings\Config\Customize\Layout;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Customize\Layout\Row')) {
    class Row
    {
        public static function get_layout()
        {
            $breakpoint = [[
                "type" => "center",
                "fields" => ["row-breakpoint-device"],
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
                        "desktop-row-font-size",
                        "desktop-row-font-family",
                    ],
                    "dependency" => [
                        [
                            "id" => "row-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Text Color",
                    "axis" => "x",
                    "fields" => ["desktop-row-color", "desktop-row-hover-color"],
                    "dependency" => [
                        [
                            "id" => "row-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Background Color",
                    "axis" => "x",
                    "fields" => ["desktop-row-bg-color", "desktop-row-bg-hover-color"],
                    "dependency" => [
                        [
                            "id" => "row-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Alternate Row Background",
                    "axis" => "y",
                    "fields" => [
                        [
                            "type" => "direct",
                            "fields" => ["desktop-row-alternate"],
                        ],
                        [
                            "type" => "fieldset",
                            "label" => "Alternate Background",
                            "axis" => "x",
                            "margin" => "zero",
                            "fields" => [
                                "desktop-row-alternate-bg-color",
                                "desktop-row-alternate-bg-hover-color",
                            ],
                            "dependency" => [
                                [
                                    "id" => "desktop-row-alternate",
                                    "operator" => "==",
                                    "value" => true,
                                ],
                            ],
                        ],
                    ],
                    "dependency" => [
                        [
                            "id" => "row-breakpoint-device",
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
                        "desktop-row-padding-top",
                        "desktop-row-padding-right",
                        "desktop-row-padding-bottom",
                        "desktop-row-padding-left",
                    ],
                    "dependency" => [
                        [
                            "id" => "row-breakpoint-device",
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
                                "desktop-row-border-top-width",
                                "desktop-row-border-right-width",
                                "desktop-row-border-bottom-width",
                                "desktop-row-border-left-width",
                            ],
                        ],
                        "desktop-row-border-style",
                        "desktop-row-border-color",
                    ],
                    "dependency" => [
                        [
                            "id" => "row-breakpoint-device",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],

                [
                    "type" => "fieldset",
                    "label" => "Text Alignment",
                    "fields" => ["desktop-row-text-align"],
                    "dependency" => [
                        [
                            "id" => "row-breakpoint-device",
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
                        "mobile-row-font-size",
                        "mobile-row-font-family",
                    ],
                    "dependency" => [
                        [
                            "id" => "row-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
                // [
                //     "type" => "fieldset",
                //     "label" => "Text Color",
                //     "axis" => "x",
                //     "fields" => ["mobile-row-color", "mobile-row-hover-color"],
                //     "dependency" => [
                //         [
                //             "id" => "row-breakpoint-device",
                //             "operator" => "==",
                //             "value" => "mobile",
                //         ],
                //     ],
                // ],
                // [
                //     "type" => "fieldset",
                //     "label" => "Background Color",
                //     "axis" => "x",
                //     "fields" => ["mobile-row-bg-color", "mobile-row-bg-hover-color"],
                //     "dependency" => [
                //         [
                //             "id" => "row-breakpoint-device",
                //             "operator" => "==",
                //             "value" => "mobile",
                //         ],
                //     ],
                // ],
                // [
                //     "type" => "fieldset",
                //     "label" => "Alternate Row Background",
                //     "axis" => "y",
                //     "fields" => [
                //         [
                //             "type" => "direct",
                //             "fields" => ["mobile-row-alternate"],
                //         ],
                //         [
                //             "type" => "fieldset",
                //             "label" => "Alternate Background",
                //             "axis" => "x",
                //             "margin" => "zero",
                //             "fields" => [
                //                 "mobile-row-alternate-bg-color",
                //                 "mobile-row-alternate-bg-hover-color",
                //             ],
                //             "dependency" => [
                //                 [
                //                     "id" => "mobile-row-alternate",
                //                     "operator" => "==",
                //                     "value" => true,
                //                 ],
                //             ],
                //         ],
                //     ],
                //     "dependency" => [
                //         [
                //             "id" => "row-breakpoint-device",
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
                        "mobile-row-padding-top",
                        "mobile-row-padding-right",
                        "mobile-row-padding-bottom",
                        "mobile-row-padding-left",
                    ],
                    "dependency" => [
                        [
                            "id" => "row-breakpoint-device",
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
                                "mobile-row-border-top-width",
                                "mobile-row-border-right-width",
                                "mobile-row-border-bottom-width",
                                "mobile-row-border-left-width",
                            ],
                        ],
                        "mobile-row-border-style",
                        // "mobile-row-border-color",
                    ],
                    "dependency" => [
                        [
                            "id" => "row-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],

                [
                    "type" => "fieldset",
                    "label" => "Text Alignment",
                    "fields" => ["mobile-row-text-align"],
                    "dependency" => [
                        [
                            "id" => "row-breakpoint-device",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
            ];
        }
    }
}
