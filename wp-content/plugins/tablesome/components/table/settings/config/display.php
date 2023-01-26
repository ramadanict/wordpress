<?php

namespace Tablesome\Components\Table\Settings\Config;

use \Tablesome\Components\Table\Settings\Config\Common as Common;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Display')) {
    class Display
    {
        public function __construct()
        {

        }

        public static function get_layout()
        {
            return [
                [
                    "type" => "fieldset",
                    "label" => "Table Fit",
                    "fields" => ["displayMode"],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Mobile Layout",
                    "fields" => ["mobileLayoutMode"],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Pagination Options",
                    "fields" => [
                        "numOfRecordsPerPage",
                        "pagination_show_first_and_last_buttons",
                        "pagination_show_previous_and_next_buttons",
                    ],
                ],
                [
                    "type" => "direct",
                    "fields" => [
                        "hideTableHeader",
                        "stickyFirstColumn",
                        "serialNumberColumn",
                    ],
                ],
                // [
                //     label" => "Default Sorting",
                //     type" => "fieldset",
                //     axis" => "x",
                //     fields" => ["sortingBy", "sortingOrder"],
                // ],
                [
                    "label" => "Column Width Adjustment",
                    "type" => "fieldset",
                    "axis" => "y",
                    "fields" => [
                        [
                            "type" => "flex",
                            "axis" => "x",
                            "fields" => ["enableColumnMinWidth", "columnMinWidth"],
                        ],
                        [
                            "type" => "flex",
                            "axis" => "x",
                            "fields" => ["enableColumnMaxWidth", "columnMaxWidth"],
                        ],
                    ],
                ],
                [
                    "type" => "center",
                    "fields" => ["showHideComponentDevice"],
                ],
                [
                    "type" => "direct",
                    "fields" => ["desktop-search", "desktop-filter", "desktop-export", "desktop-sort"],
                    "dependency" => [
                        [
                            "id" => "showHideComponentDevice",
                            "operator" => "==",
                            "value" => "desktop",
                        ],
                    ],
                ],
                [
                    "type" => "direct",
                    "fields" => ["mobile-search", "mobile-filter", "mobile-export", "mobile-sort"],
                    "dependency" => [
                        [
                            "id" => "showHideComponentDevice",
                            "operator" => "==",
                            "value" => "mobile",
                        ],
                    ],
                ],
            ];
        }

        public static function get_fields()
        {
            return [
                [
                    "id" => "displayMode",
                    // "label" => "Table Fit",
                    "type" => "select-grid",
                    "options" => [
                        [
                            "id" => "fit-to-container",
                            "label" => "Fit to Container",
                            "icon" => "&#xf8f5;",
                        ],
                        [
                            "id" => "standard",
                            "label" => "Standard",
                            "icon" => "&#xf8f6;",
                        ],
                    ],
                    "default" => "fit-to-container",
                ],

                [
                    "id" => "mobileLayoutMode",
                    // "label" => "Mobile Layout",
                    "type" => "select-grid",
                    "options" => [
                        [
                            "id" => "scroll-mode",
                            "label" => "Scroll",
                            // "icon" => "&#xf8f5;",
                            "icon" => "&#xea18;",
                        ],
                        [
                            "id" => "stack-mode",
                            "label" => "Stack",
                            "icon" => "&#xe1db;",
                        ],
                    ],
                    "default" => "scroll-mode",
                ],
                [
                    "id" => "numOfRecordsPerPage",
                    "label" => "Number of Rows per page (1-100)",
                    "placeholder" => "Number of Rows per page (1-100)",
                    "type" => "number",
                    "min" => 1,
                    "max" => 100,
                    "default" => 10,
                ],

                [
                    "id" => "pagination_show_first_and_last_buttons",
                    "label" => "Show First and Last buttons",
                    "type" => "toggle",
                    "default" => true,
                ],
                [
                    "id" => "pagination_show_previous_and_next_buttons",
                    "label" => "Show Previous and Next buttons",
                    "type" => "toggle",
                    "default" => true,
                ],

                [
                    "id" => "hideTableHeader",
                    "label" => "Hide Table Header",
                    "type" => "toggle",
                    "default" => false,
                ],

                [
                    "id" => "stickyFirstColumn",
                    "label" => "Sticky First Column",
                    "type" => "toggle",
                    "default" => false,
                ],

                [
                    "id" => "serialNumberColumn",
                    "label" => "Show Serial Number ",
                    "type" => "toggle",
                    "default" => true,
                ],

                [
                    "id" => "enableColumnMinWidth",
                    "type" => "toggle",
                    // "label" => "Enable Column Min-Width",
                    "default" => true,
                ],
                [
                    "id" => "enableColumnMaxWidth",
                    "type" => "toggle",
                    // "label" => "Enable Column Max-Width",
                    "default" => true,
                ],
                [
                    "id" => "columnMinWidth",
                    "type" => "number",
                    "label" => "Min-width (px)",
                    "placeholder" => "min-width",
                    // "default" => "auto",
                ],
                [
                    "id" => "columnMaxWidth",
                    "type" => "number",
                    "label" => "Max-width (px)",
                    "placeholder" => "max-width",
                    // "default" => "auto",
                ],
                // [
                //     "id" => "sortingBy",
                //     "type" => "select",
                //     "label" => "Sorting By",
                //     "placeholder" => "Sorting By",
                //     "options" => [
                //        [
                //             "id" => "created_at",
                //             "label" => "Created Date",
                //         ],
                //         [
                //             "id" => "updated_at",
                //             "label" => "Updated Date",
                //         ],
                //         [
                //             "id" => "rank_order",
                //             "label" => "Custom",
                //         ],
                //     ],
                //     "default" => "rank_order",
                // ],

                // [
                //     "id" => "sortingOrder",
                //     "type" => "select",
                //     "label" => "Sorting Order",
                //     "placeholder" => "Sorting Order",
                //     "options" => [
                //        [
                //             "id" => "asc",
                //             "label" => "ASC",
                //         ],
                //        [
                //             "id" => "desc",
                //             "label" => "DESC",
                //         ],
                //     ],
                //     "default" => "desc",
                // ],

                [
                    "id" => "showHideComponentDevice",
                    "label" => "Show/Hide Components",
                    "type" => "device",
                    "options" => Common::get_breakpoint(),
                    "default" => "desktop",
                ],

                [
                    "id" => "desktop-search",
                    "label" => "Show Search",
                    "type" => "toggle",
                    "default" => true,
                    "extra" => [
                        "device" => "desktop",
                    ],
                ],

                [
                    "id" => "mobile-search",
                    "label" => "Show Search in Mobile",
                    "type" => "toggle",
                    "default" => true,
                    "extra" => [
                        "device" => "mobile",
                    ],
                ],

                [
                    "id" => "desktop-filter",
                    "label" => "Show Filter ",
                    "type" => "toggle",
                    "default" => true,
                    "extra" => [
                        "device" => "desktop",
                    ],
                ],
                [
                    "id" => "mobile-filter",
                    "label" => "Show Filter in Mobile",
                    "type" => "toggle",
                    "default" => true,
                    "extra" => [
                        "device" => "mobile",
                    ],
                ],

                [
                    "id" => "desktop-export",
                    "label" => "Show Export ",
                    "type" => "toggle",
                    "default" => true,
                    "extra" => [
                        "device" => "desktop",
                    ],
                ],
                [
                    "id" => "mobile-export",
                    "label" => "Show Export in Mobile",
                    "type" => "toggle",
                    "default" => true,
                    "extra" => [
                        "device" => "mobile",
                    ],
                ],

                [
                    "id" => "desktop-sort",
                    "label" => "Show Sort",
                    "type" => "toggle",
                    "default" => true,
                    "extra" => [
                        "device" => "desktop",
                    ],
                    "dependency" => [
                        [
                            "id" => "hideTableHeader",
                            "operator" => "==",
                            "value" => false,
                        ],
                    ],
                ],
                [
                    "id" => "mobile-sort",
                    "label" => "Show Sort in Mobile",
                    "type" => "toggle",
                    "default" => true,
                    "extra" => [
                        "device" => "mobile",
                    ],
                    "dependency" => [
                        [
                            "id" => "hideTableHeader",
                            "operator" => "==",
                            "value" => false,
                        ],
                    ],
                ],
            ];
        }
    }
}
