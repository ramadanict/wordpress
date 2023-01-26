<?php

namespace Tablesome\Components\Table\Settings\Config;

use \Tablesome\Components\Table\Settings\Config\Common;

// Field
use \Tablesome\Components\Table\Settings\Config\Customize\Fields\Filter as FilterField;
use \Tablesome\Components\Table\Settings\Config\Customize\Fields\Header as HeaderField;
use \Tablesome\Components\Table\Settings\Config\Customize\Fields\Pagination as PaginationField;
use \Tablesome\Components\Table\Settings\Config\Customize\Fields\Row as RowField;
use \Tablesome\Components\Table\Settings\Config\Customize\Fields\Search as SearchField;

// Layout
use \Tablesome\Components\Table\Settings\Config\Customize\Layout\Filter as FilterLayout;
use \Tablesome\Components\Table\Settings\Config\Customize\Layout\Header as HeaderLayout;
use \Tablesome\Components\Table\Settings\Config\Customize\Layout\Pagination as PaginationLayout;
use \Tablesome\Components\Table\Settings\Config\Customize\Layout\Row as RowLayout;
use \Tablesome\Components\Table\Settings\Config\Customize\Layout\Search as SearchLayout;

// themes
use \Tablesome\Components\Table\Settings\Config\Themes\Brand as BrandTheme;
use \Tablesome\Components\Table\Settings\Config\Themes\Classic as ClassicTheme;
use \Tablesome\Components\Table\Settings\Config\Themes\Purple as PurpleTheme;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Style')) {
    class Style
    {
        private static $default;
        private static $is_premium;
        private static $font_families;
        private static $text_alignment;

        public function __construct()
        {
            self::$default = Common::get_default();
            self::$is_premium = tablesome_fs()->can_use_premium_code__premium_only();
            self::$font_families = Common::get_font_family();
            self::$text_alignment = Common::get_text_alignment();
        }

        public static function get_layout()
        {
            $standard = [
                [
                    "type" => "direct",
                    "fields" => ["style-mode"],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Themes",
                    "axis" => "y",
                    "fields" => ["standard-theme"],
                    "dependency" => [
                        [
                            "id" => "style-mode",
                            "operator" => "==",
                            "value" => "standard",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Font",
                    "axis" => "y",
                    "fields" => [
                        "standard-font-size",
                        "standard-font-family",
                        "standard-color",
                    ],
                    "dependency" => [
                        [
                            "id" => "style-mode",
                            "operator" => "==",
                            "value" => "standard",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Border",
                    "fields" => ["standard-border-width"],
                    "dependency" => [
                        [
                            "id" => "style-mode",
                            "operator" => "==",
                            "value" => "standard",
                        ],
                    ],
                ],
                [
                    "type" => "fieldset",
                    "label" => "Text Alignment",
                    "fields" => ["standard-text-align"],
                    "dependency" => [
                        [
                            "id" => "style-mode",
                            "operator" => "==",
                            "value" => "standard",
                        ],
                    ],
                ],
            ];

            $customize = [
                [
                    "type" => "direct",
                    "fields" => ["customize-list-panel"],
                    "dependency" => [
                        [
                            "id" => "style-mode",
                            "operator" => "==",
                            "value" => "customize",
                        ],
                    ],
                ],
                [
                    "type" => "paneloverlay",
                    "fields" => HeaderLayout::get_layout(),
                    "label" => "Header (Customize)",
                    "sublabel" => "Style Settings > Header",
                    "dependency" => [
                        [
                            "id" => "customize-list-panel",
                            "operator" => "==",
                            "value" => "header",
                        ],
                    ],
                ],

                [
                    "type" => "paneloverlay",
                    "fields" => RowLayout::get_layout(),
                    "label" => "Row (Customize)",
                    "sublabel" => "Style Settings > Row",
                    "dependency" => [
                        [
                            "id" => "customize-list-panel",
                            "operator" => "==",
                            "value" => "row",
                        ],
                    ],
                ],

                [
                    "type" => "paneloverlay",
                    "fields" => PaginationLayout::get_layout(),
                    "label" => "Pagination (Customize)",
                    "sublabel" => "Style Settings > Pagination",
                    "dependency" => [
                        [
                            "id" => "customize-list-panel",
                            "operator" => "==",
                            "value" => "pagination",
                        ],
                    ],
                ],

                [
                    "type" => "paneloverlay",
                    "fields" => SearchLayout::get_layout(),
                    "label" => "Search (Customize)",
                    "sublabel" => "Style Settings > Search",
                    "dependency" => [
                        [
                            "id" => "customize-list-panel",
                            "operator" => "==",
                            "value" => "search",
                        ],
                    ],
                ],

                [
                    "type" => "paneloverlay",
                    "fields" => FilterLayout::get_layout(),
                    "label" => "Filter (Customize)",
                    "sublabel" => "Style Settings > Filter",
                    "dependency" => [
                        [
                            "id" => "customize-list-panel",
                            "operator" => "==",
                            "value" => "filter",
                        ],
                    ],
                ],
            ];

            $global = [
                [
                    "type" => "center",
                    "fields" => ["global_link"],
                    "dependency" => [
                        [
                            "id" => "style-mode",
                            "operator" => "==",
                            "value" => "global",
                        ],
                    ],
                ],

            ];
            $style_layout = array_merge($standard, $customize, $global);

            return $style_layout;
        }

        public static function get_fields()
        {

            $standard = [
                [

                    "id" => "style-mode",
                    "type" => "select-grid",
                    "options" => [
                        [
                            "id" => "standard",
                            "icon" => "&#xe41d;",
                            "label" => "Standard",
                        ],
                        [
                            "id" => "customize",
                            "icon" => "&#xf10a;",
                            "label" => "Customize",
                            "is_premium" => self::$is_premium,
                        ],
                        [
                            "id" => "global",
                            "icon" => "&#xe894;",
                            "label" => "Global",
                        ],
                    ],
                    "default" => "standard",
                    "extra" => [
                        "border" => "bottom-border",
                    ],
                ],
                [
                    "id" => "standard-theme",
                    "type" => "select-grid",
                    "options" => [
                        [
                            "id" => "classic",
                            "label" => "classic",
                            // "component" => ThemeTable,
                        ],
                        [
                            "id" => "brand",
                            "label" => "brand",
                            // "component" => ThemeTable,
                        ],
                        // [
                        //     "id" => "light",
                        //     "label" => "light",
                        //     // "component" => ThemeTable,
                        // ],
                        // [
                        //     "id" => "dark",
                        //     "label" => "dark",
                        //     // "component" => ThemeTable,
                        // ],
                        // [
                        //     "id" => "blue",
                        //     "label" => "blue",
                        //     // "component" => ThemeTable,
                        // ],
                        [
                            "id" => "purple",
                            "label" => "purple",
                            // "component" => ThemeTable,
                            "is_premium" => self::$is_premium,
                        ],
                        // [
                        //     "id" => "amber",
                        //     "label" => "amber",
                        //     // "component" => ThemeTable,
                        // ],
                        // [
                        //     "id" => "tomato",
                        //     "label" => "tomato",
                        //     // "component" => ThemeTable,
                        // ],
                        // [
                        //     "id" => "teal",
                        //     "label" => "teal",
                        //     // "component" => ThemeTable,
                        // ],
                    ],
                    "extra" => [
                        "itemType" => "component",
                        "drawType" => "vertical",
                        "themes" => [
                            "classic" => ClassicTheme::get_theme(),
                            "brand" => BrandTheme::get_theme(),
                            "purple" => PurpleTheme::get_theme(),
                        ],
                    ],
                    "default" => "classic",
                ],
                [
                    "id" => "standard-border-width",
                    "type" => "select-grid",
                    "label" => "Border",
                    "options" => [
                        [
                            "id" => "none",
                            "icon" => "&#xe22a;",
                            "label" => "None",
                        ],
                        [
                            "id" => "thin",
                            "icon" => "&#xe228;",
                            "label" => "Thin",
                        ],
                        [
                            "id" => "medium",
                            "icon" => "&#xe228;",
                            "label" => "Medium",
                        ],
                        [
                            "id" => "thick",
                            "icon" => "&#xe228;",
                            "label" => "Thick",
                        ],
                    ],
                    "default" => "thin",
                    "extra" => [
                        "iconSize" => "small",
                        "values" => [
                            "none" => "0",
                            "thin" => "1",
                            "medium" => "2",
                            "thick" => "4",
                        ],
                        "fields" => [
                            "desktop-header-border-top-width",
                            "desktop-header-border-right-width",
                            "desktop-header-border-bottom-width",
                            "desktop-header-border-left-width",

                            "desktop-row-border-top-width",
                            "desktop-row-border-right-width",
                            "desktop-row-border-bottom-width",
                            "desktop-row-border-left-width",

                            "desktop-pagination-border-top-width",
                            "desktop-pagination-border-right-width",
                            "desktop-pagination-border-bottom-width",
                            "desktop-pagination-border-left-width",

                            "desktop-search-border-top-width",
                            "desktop-search-border-right-width",
                            "desktop-search-border-bottom-width",
                            "desktop-search-border-left-width",

                            "desktop-filter-border-top-width",
                            "desktop-filter-border-right-width",
                            "desktop-filter-border-bottom-width",
                            "desktop-filter-border-left-width",

                            "mobile-header-border-top-width",
                            "mobile-header-border-right-width",
                            "mobile-header-border-bottom-width",
                            "mobile-header-border-left-width",

                            "mobile-row-border-top-width",
                            "mobile-row-border-right-width",
                            "mobile-row-border-bottom-width",
                            "mobile-row-border-left-width",

                            "mobile-pagination-border-top-width",
                            "mobile-pagination-border-right-width",
                            "mobile-pagination-border-bottom-width",
                            "mobile-pagination-border-left-width",

                            "mobile-search-border-top-width",
                            "mobile-search-border-right-width",
                            "mobile-search-border-bottom-width",
                            "mobile-search-border-left-width",

                            "mobile-filter-border-top-width",
                            "mobile-filter-border-right-width",
                            "mobile-filter-border-bottom-width",
                            "mobile-filter-border-left-width",
                        ],
                    ],

                ],
                [
                    "id" => "standard-text-align",
                    "type" => "select-grid",
                    "label" => "Text Alignment",
                    "options" => self::$text_alignment,
                    "default" => "left",
                    "extra" => [
                        "iconSize" => "small",
                    ],
                    "extra" => [
                        "iconSize" => "small",
                        "fields" => [
                            "desktop-header-text-align",
                            "desktop-row-text-align",
                            "desktop-search-text-align",
                            "desktop-filter-text-align",

                            "mobile-header-text-align",
                            "mobile-row-text-align",
                            "mobile-search-text-align",
                            "mobile-filter-text-align",
                        ],
                    ],
                ],
                [
                    "id" => "standard-font-size",
                    "type" => "select-grid",
                    "options" => [
                        [
                            "id" => "large",
                            //"icon" => "&#xeb94;",
                            "icon" => "&#xe262;",
                            "label" => "Large",
                        ],
                        [
                            "id" => "medium",
                            "icon" => "&#xe262;",
                            "label" => "Medium",
                        ],
                        [
                            "id" => "small",
                            "icon" => "&#xe262;",
                            "label" => "Small",
                        ],
                    ],
                    "default" => "medium",
                    "extra" => [
                        "iconSize" => "small",
                        "values" => [
                            "large" => "18",
                            "medium" => "16",
                            "small" => "12",
                        ],
                        "fields" => [
                            "desktop-header-font-size",
                            "desktop-row-font-size",
                            "desktop-pagination-font-size",
                            "desktop-search-font-size",
                            "desktop-filter-font-size",

                            // "mobile-header-font-size",
                            // "mobile-row-font-size",
                            // "mobile-pagination-font-size",
                            // "mobile-search-font-size",
                            // "mobile-filter-font-size",
                        ],
                    ],
                ],
                [
                    "id" => "standard-font-family",
                    "type" => "select",
                    "options" => self::$font_families,
                    "default" => self::$default["font-family"],
                    "placeholder" => "Choose Font Family",
                    "extra" => [
                        "optionType" => "group",
                        "fields" => [ // singuar
                            "desktop-header-font-family",
                            "desktop-header-font-family",
                            "desktop-row-font-family",
                            "desktop-row-font-family",
                            "desktop-pagination-font-family",
                            "desktop-pagination-font-family",
                            "desktop-search-font-family",
                            "desktop-search-font-family",
                            "desktop-filter-font-family",
                            "desktop-filter-font-family",

                            "mobile-header-font-family",
                            "mobile-header-font-family",
                            "mobile-row-font-family",
                            "mobile-row-font-family",
                            "mobile-pagination-font-family",
                            "mobile-pagination-font-family",
                            "mobile-search-font-family",
                            "mobile-search-font-family",
                            "mobile-filter-font-family",
                            "mobile-filter-font-family",
                        ],
                    ],
                ],
                [
                    "id" => "standard-color",
                    "label" => "Font Color",
                    "type" => "color",
                    "default" => self::$default["primary"],
                    "extra" => [
                        "fields" => [
                            "desktop-header-color",
                            "desktop-header-hover-color",
                            "desktop-row-color",
                            "desktop-row-hover-color",
                            "desktop-pagination-color",
                            "desktop-pagination-hover-color",
                            "desktop-search-color",
                            "desktop-search-hover-color",
                            "desktop-filter-color",
                            "desktop-filter-hover-color",

                            // "mobile-header-color",
                            // "mobile-header-hover-color",
                            // "mobile-row-color",
                            // "mobile-row-hover-color",
                            // "mobile-pagination-color",
                            // "mobile-pagination-hover-color",
                            // "mobile-search-color",
                            // "mobile-search-hover-color",
                            // "mobile-filter-color",
                            // "mobile-filter-hover-color",
                        ],
                    ],
                ],
            ];

            $customize = [
                [
                    "id" => "customize-list-panel",
                    "type" => "list",
                    "options" => [
                        [
                            "id" => "header",
                            "label" => "Header (Customize)",
                            "subLabel" => "Style settings > Header",
                        ],
                        [
                            "id" => "row",
                            "label" => "Row (Customize)",
                            "subLabel" => "Style settings > Row",
                        ],
                        [
                            "id" => "pagination",
                            "label" => "Pagination (Customize)",
                            "subLabel" => "Style settings > Pagination",
                        ],
                        [
                            "id" => "search",
                            "label" => "Search (Customize)",
                            "subLabel" => "Style settings > Search",
                        ],
                        [
                            "id" => "filter",
                            "label" => "Filter (Customize)",
                            "subLabel" => "Style settings > Filter",
                        ],
                    ],
                    "default" => "",
                ],
            ];

            $row = new RowField();
            $header = new HeaderField();
            $pagination = new PaginationField();
            $search = new SearchField();
            $filter = new FilterField();

            $customize = array_merge($customize, $header::get_fields(), $row::get_fields(), $pagination::get_fields(), $search::get_fields(), $filter::get_fields());

            $global = [
                [

                    "id" => "global_link",
                    "default" => "",
                    "type" => "paragraph",
                    "html" => "Change your Global Style <a href='" . admin_url('edit.php?post_type=' . TABLESOME_CPT . '&page=tablesome-settings#tab=style') . "' class='tablesome-no-underline hover:tablesome-underline'>Settings Here >> </a>",
                ],
            ];

            $style_fields = array_merge($standard, $customize, $global);
            return $style_fields;
        }

        // Yet to be implemented in tabel level settings
        public static function get_future_fields()
        {
            $target_cell = ".tablesome__table .tablesome__row > .tablesome__cell";
            $target_column = ".tablesome__table .tablesome__row > .tablesome__column";

            $fields = [

                // Header Desktop
                [
                    "id" => "desktop-header-line-height",
                    "target" => $target_column,
                    "default" => "20",
                    "extra" => [
                        "css_property_name" => "line-height",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-header-font-style",
                    "target" => $target_column,
                    "default" => "inherit",
                    "extra" => [
                        "css_property_name" => "font-style",
                        "breakpoint" => "desktop",
                    ],
                ],
                [
                    "id" => "desktop-header-letter-spacing",
                    "target" => $target_column,
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "letter-spacing",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-header-text-transform",
                    "target" => $target_column,
                    "default" => "none",
                    "extra" => [
                        "css_property_name" => "text-transform",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-header-font-weight",
                    "target" => $target_column,
                    "default" => "500",
                    "extra" => [
                        "css_property_name" => "font-weight",
                        "breakpoint" => "desktop",
                    ],
                ],

                // Header Mobile
                [
                    "id" => "mobile-header-line-height",
                    "target" => $target_column,
                    "default" => "20",
                    "extra" => [
                        "css_property_name" => "line-height",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-header-font-style",
                    "target" => $target_column,
                    "default" => "inherit",
                    "extra" => [
                        "css_property_name" => "font-style",
                        "breakpoint" => "mobile",
                    ],
                ],
                [
                    "id" => "mobile-header-letter-spacing",
                    "target" => $target_column,
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "letter-spacing",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-header-text-transform",
                    "target" => $target_column,
                    "default" => "none",
                    "extra" => [
                        "css_property_name" => "text-transform",
                        "breakpoint" => "mobile",
                    ],
                ],

                [
                    "id" => "mobile-header-font-weight",
                    "target" => $target_column,
                    "default" => "500",
                    "extra" => [
                        "css_property_name" => "font-weight",
                        "breakpoint" => "mobile",
                    ],
                ],

                // ROW Desktop
                [
                    "id" => "desktop-row-line-height",
                    "target" => $target_cell,
                    "default" => "20",
                    "extra" => [
                        "css_property_name" => "line-height",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "desktop-row-font-style",
                    "target" => $target_cell,
                    "default" => "inherit",
                    "extra" => [
                        "css_property_name" => "font-style",
                        "breakpoint" => "desktop",
                    ],
                ],
                [
                    "id" => "desktop-row-letter-spacing",
                    "target" => $target_cell,
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "letter-spacing",
                        "breakpoint" => "desktop",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "desktop-row-text-transform",
                    "target" => $target_cell,
                    "default" => "none",
                    "extra" => [
                        "css_property_name" => "text-transform",
                        "breakpoint" => "desktop",
                    ],
                ],

                [
                    "id" => "desktop-row-font-weight",
                    "target" => $target_cell,
                    "default" => "normal",
                    "extra" => [
                        "css_property_name" => "font-weight",
                        "breakpoint" => "desktop",
                    ],
                ],

                // ROW Mobile
                [
                    "id" => "mobile-row-line-height",
                    "target" => $target_cell,
                    "default" => "20",
                    "extra" => [
                        "css_property_name" => "line-height",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],
                [
                    "id" => "mobile-row-font-style",
                    "target" => $target_cell,
                    "default" => "inherit",
                    "extra" => [
                        "css_property_name" => "font-style",
                        "breakpoint" => "mobile",
                    ],
                ],
                [
                    "id" => "mobile-row-letter-spacing",
                    "target" => $target_cell,
                    "default" => "0",
                    "extra" => [
                        "css_property_name" => "letter-spacing",
                        "breakpoint" => "mobile",
                        "unit" => "px",
                    ],
                ],

                [
                    "id" => "mobile-row-text-transform",
                    "target" => $target_cell,
                    "default" => "none",
                    "extra" => [
                        "css_property_name" => "text-transform",
                        "breakpoint" => "mobile",
                    ],
                ],

                [
                    "id" => "mobile-row-font-weight",
                    "target" => $target_cell,
                    "default" => "normal",
                    "extra" => [
                        "css_property_name" => "font-weight",
                        "breakpoint" => "mobile",
                    ],
                ],
            ];

            return $fields;
        }
    }
}
