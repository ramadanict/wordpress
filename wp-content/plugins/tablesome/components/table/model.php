<?php

namespace Tablesome\Components\Table;

use \Tablesome\Components\Table\Settings\Config\Themes\Classic as DefaultTheme;
use \Tablesome\Components\Table\Settings\Settings as TableLevelSettings;
use \Tablesome\Includes\Settings\Tablesome_Getter;

if (!class_exists('\Tablesome\Components\Table\Model')) {
    class Model
    {
        public $utils;
        public $columns;
        public $table_id;
        public $table_meta;
        public $collection;
        public $tablesome_db;
        public function __construct()
        {
            $this->tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $this->utils = new \Tablesome\Includes\Utils();
        }

        public function get_viewProps($args = [])
        {
            $viewProps = $this->get_tablesome_viewProps($args);

            // Filter for all actions related to Tablesome Update Content....
            $viewProps = apply_filters("tablesome_get_table_viewProps", $viewProps);

            return $viewProps;
        }

        public function get_tablesome_viewProps($args = [])
        {
            $this->collection = $this->get_collectionProps($args);

            $viewProps = [
                'collection' => $this->collection,
                'items' => $this->get_itemProps($this->collection),
            ];

            return $viewProps;
        }

        public function get_collectionProps($args = [])
        {
            $this->table_id = isset($args['post_id']) ? $args['post_id'] : $args['table_id'];
            $this->table_meta = get_tablesome_data($this->table_id);

            // error_log('$this->table_meta : ' . print_r($this->table_meta, true));

            $this->columns = isset($this->table_meta['columns']) && !empty($this->table_meta['columns']) ? $this->table_meta['columns'] : [];

            $last_column_id = isset($this->table_meta['meta']['last_column_id']) && !empty($this->table_meta['meta']['last_column_id']) ? $this->table_meta['meta']['last_column_id'] : 1;
            $table_mode = isset($args['table_mode']) ? $args['table_mode'] : 'read-only';
            $custom_style = Tablesome_Getter::get('style_disable') ? true : false;

            $collection = [
                'table_id' => $this->table_id,
                'table_title' => $this->get_table_title($this->table_id),
                'rowLimit' => TABLESOME_MAX_RECORDS_TO_READ,
                'columnLimit' => TABLESOME_MAX_COLUMNS_TO_READ,
                'defaultColumnName' => __("Column name", 'tablesome'),
                'rowDeleteMessage' => __("Are you sure want's to Delete the selected Row?", 'tablesome'),
                'columnDeleteMessage' => __("Are you sure want's to Delete the selected Column?", 'tablesome'),
                'rowLimitMessage' => __("Couldn't remove the row. The table must have one row at least!", 'tablesome'),
                'columnLimitMessage' => __("Couldn't remove the Column. The table must have one Column at least!", 'tablesome'),
                'last_column_id' => $last_column_id,
                'show_table_footer' => isset($args['show_table_footer']) ? $args['show_table_footer'] : false,
                'last_rank_order' => $this->tablesome_db->get_max_rank_order_value($this->table_id),
                'mode' => $table_mode,
                'customstyle' => $custom_style,
                'editorState' => $this->get_editor_state($this->table_id),
                'display' => $this->get_display_settings($this->table_id, $args),
                'style' => $this->get_style_settings($this->table_id, $args),
            ];

            return array_merge($collection, $args);
        }

        public function get_editor_state($table_id)
        {
            $editor_state = [
                // By default sidebar-display will be maximized
                "sidebar-display" => "maximized", // minimized
            ];

            $table_meta = $table_id != 0 ? get_tablesome_data($table_id) : [];
            $stored_editor_state = isset($table_meta['editorState']) && !empty($table_meta['editorState']) ? $table_meta['editorState'] : [];

            if (!empty($editor_state)) {
                $editor_state = array_merge($editor_state, $stored_editor_state);
            }

            return $editor_state;
        }

        public function get_display_settings($table_id = 0, $args = [])
        {

            // Three sources of settings
            $global_display_settings = $this->get_global_display_settings();
            $table_level_display_settings = $this->get_table_level_only_display_settings($table_id);
            $widgetkind_arguments = $this->get_widgetkind_display_args($args);

            // error_log(' args : ' . print_r($args, true));
            // error_log(' general : ' . print_r($general, true));
            // error_log(' arguments : ' . print_r($arguments, true));
            // error_log(' tableLevelDisplay : ' . print_r($tableLevelDisplay, true));

            $display_settings = array_replace_recursive($global_display_settings, $table_level_display_settings, $widgetkind_arguments);

            // error_log(' display_settings : ' . print_r($display_settings, true));

            return $display_settings;
        }

        public function get_table_level_only_display_settings($table_id)
        {
            $table_meta = $table_id != 0 ? get_tablesome_data($table_id) : [];
            $options = isset($table_meta) && isset($table_meta['options']) && !empty($table_meta['options']) ? $table_meta['options'] : [];
            $tableLevelDisplay = isset($options['display']) && !empty($options['display']) ? $options['display'] : [];

            return $tableLevelDisplay;
        }

        public function get_global_display_settings()
        {

            $global = [
                'displayMode' => Tablesome_Getter::get('table_display_mode'),
                'mobileLayoutMode' => Tablesome_Getter::get('mobile_layout_mode'),
                'numOfRecordsPerPage' => strval(Tablesome_Getter::get('num_of_records_per_page')),
                'pagination_show_first_and_last_buttons' => $this->utils->get_bool(Tablesome_Getter::get('pagination_show_first_and_last_buttons')),
                'pagination_show_previous_and_next_buttons' => $this->utils->get_bool(Tablesome_Getter::get('pagination_show_previous_and_next_buttons')),
                'hideTableHeader' => $this->utils->get_bool(Tablesome_Getter::get('hide_table_header')),
                'stickyFirstColumn' => $this->utils->get_bool(Tablesome_Getter::get('sticky_first_column')),
                'serialNumberColumn' => $this->utils->get_bool(Tablesome_Getter::get('show_serial_number_column')),

                'enableColumnMinWidth' => $this->utils->get_bool(Tablesome_Getter::get('enable_min_column_width')),
                'enableColumnMaxWidth' => $this->utils->get_bool(Tablesome_Getter::get('enable_max_column_width')),

                'columnMinWidth' => strval(Tablesome_Getter::get('min_column_width')),
                'columnMaxWidth' => strval(Tablesome_Getter::get('max_column_width')),
                // 'sortingOrder' => 'asc',
                // 'sortingBy' => 'rank_order',

            ];

            $globalResponsive = [
                "showHideComponentDevice" => "desktop",
                // desktop
                'desktop-search' => $this->utils->get_bool(Tablesome_Getter::get('search')),
                'desktop-sort' => $this->utils->get_bool(Tablesome_Getter::get('sorting')),
                'desktop-filter' => $this->utils->get_bool(Tablesome_Getter::get('filters')),
                'desktop-export' => $this->utils->get_bool(Tablesome_Getter::get('export')),

                // mobile
                'mobile-search' => $this->utils->get_bool(Tablesome_Getter::get('search')),
                'mobile-sort' => $this->utils->get_bool(Tablesome_Getter::get('sorting')),
                'mobile-filter' => $this->utils->get_bool(Tablesome_Getter::get('filters')),
                'mobile-export' => $this->utils->get_bool(Tablesome_Getter::get('export')),

            ];

            $global = array_merge($global, $globalResponsive);

            return $global;
        }

        // Args from Widgets, Shortcodes, Blocks, etc
        public function get_widgetkind_display_args($args)
        {
            $arguments = [];
            // error_log(' args : ' . print_r($args, true));

            // shortcode arguments or widget arguments
            if (isset($args['table_display_mode']) && !empty($args['table_display_mode'])) {
                $arguments['displayMode'] = $args['table_display_mode'];
            }
            if (isset($args['mobile_layout_mode']) && !empty($args['mobile_layout_mode'])) {
                $arguments['mobileLayoutMode'] = $args['mobile_layout_mode'];
            }
            if (isset($args['page_limit']) && !empty($args['page_limit'])) {
                $arguments['numOfRecordsPerPage'] = strval($args['page_limit']);
            }
            if (isset($args['hide_table_header'])) {
                $arguments['hideTableHeader'] = $this->utils->get_bool($args['hide_table_header']);
            }
            if (isset($args['show_serial_number_column'])) {
                $arguments['serialNumberColumn'] = $this->utils->get_bool($args['show_serial_number_column']);
            }
            if (isset($args['search'])) {
                $arguments['desktop-search'] = $this->utils->get_bool($args['search']);
            }
            if (isset($args['sorting'])) {
                $arguments['desktop-sort'] = $this->utils->get_bool($args['sorting']);
            }
            if (isset($args['filters'])) {
                $arguments['desktop-filter'] = $this->utils->get_bool($args['filters']);
            }

            return $arguments;
        }

        public function get_style_settings($table_id = 0, $args = [])
        {
            $table_meta = $table_id != 0 ? get_tablesome_data($table_id) : [];

            $is_edit_table = isset($args["table_mode"]) && $args["table_mode"] == "editor";

            $options = isset($table_meta) && isset($table_meta['options']) && !empty($table_meta['options']) ? $table_meta['options'] : [];
            $table_level_style_db = isset($options['style']) && !empty($options['style']) ? $options['style'] : [];

            $header_border = Tablesome_Getter::get('style_table_header_border_color');
            $header_typography = Tablesome_Getter::get('style_header_typography');
            $header_background = Tablesome_Getter::get('style_header_background');
            $header_font_wieght = isset($header_typography["font-weight"]) && !empty($header_typography["font-weight"]) ? $header_typography["font-weight"] : "400";

            // error_log('header_typography  : ' . print_r($header_typography, true));

            $global_header_style = [
                "desktop-header-color" => $header_typography["color"],
                "desktop-header-font-family" => $header_typography["font-family"],
                "desktop-header-font-size" => $header_typography["font-size"],
                "desktop-header-text-align" => $header_typography["text-align"],

                // "mobile-header-color" => $header_typography["color"],
                "mobile-header-font-family" => $header_typography["font-family"],
                "mobile-header-font-size" => $header_typography["font-size"],
                "mobile-header-text-align" => $header_typography["text-align"],

                "desktop-header-border-top-width" => $header_border["all"],
                "desktop-header-border-right-width" => $header_border["all"],
                "desktop-header-border-bottom-width" => $header_border["all"],
                "desktop-header-border-left-width" => $header_border["all"],
                "desktop-header-border-color" => $header_border["color"],
                "desktop-header-border-style" => $header_border["style"],

                "mobile-header-border-top-width" => $header_border["all"],
                "mobile-header-border-right-width" => $header_border["all"],
                "mobile-header-border-bottom-width" => $header_border["all"],
                "mobile-header-border-left-width" => $header_border["all"],
                // "mobile-header-border-color" => $header_border["color"],
                "mobile-header-border-style" => $header_border["style"],

                "desktop-header-bg-color" => $header_background,
                // "mobile-header-bg-color" => $header_background,

                // yet to be implemented in tabel level settings
                "desktop-header-font-weight" => $header_font_wieght,
                "desktop-header-text-transform" => $header_typography["text-transform"],
                "desktop-header-line-height" => $header_typography["line-height"],
                "desktop-header-font-style" => $header_typography["font-style"],
                // "desktop-header-subset" => $header_typography["subset"],
                "desktop-header-letter-spacing" => $header_typography["letter-spacing"],

                "mobile-header-line-height" => $header_typography["line-height"],
                "mobile-header-font-style" => $header_typography["font-style"],
                // "mobile-header-subset" => $header_typography["subset"],
                "mobile-header-letter-spacing" => $header_typography["letter-spacing"],
                "mobile-header-font-weight" => $header_font_wieght,
                "mobile-header-text-transform" => $header_typography["text-transform"],
            ];

            $row_border = Tablesome_Getter::get('style_cell_border');
            $row_typography = Tablesome_Getter::get('style_cell_typography');
            $row_background = Tablesome_Getter::get('style_row_background');
            $row_alternate_background = Tablesome_Getter::get('style_row_background_even');
            $row_font_wieght = isset($row_typography["font-weight"]) && !empty($row_typography["font-weight"]) ? $row_typography["font-weight"] : "400";

            // error_log(' row_typography : ' . print_r($row_typography, true));

            $global_row_style = [
                "desktop-row-color" => $row_typography["color"],
                "desktop-row-font-family" => $row_typography["font-family"],
                "desktop-row-font-size" => $row_typography["font-size"],
                "desktop-row-text-align" => $row_typography["text-align"],

                // "mobile-row-color" => $row_typography["color"],
                "mobile-row-font-family" => $row_typography["font-family"],
                "mobile-row-font-size" => $row_typography["font-size"],
                "mobile-row-text-align" => $row_typography["text-align"],

                "desktop-row-border-top-width" => $row_border["all"],
                "desktop-row-border-right-width" => $row_border["all"],
                "desktop-row-border-bottom-width" => $row_border["all"],
                "desktop-row-border-left-width" => $row_border["all"],
                "desktop-row-border-color" => $row_border["color"],
                "desktop-row-border-style" => $row_border["style"],

                "mobile-row-border-top-width" => $row_border["all"],
                "mobile-row-border-right-width" => $row_border["all"],
                "mobile-row-border-bottom-width" => $row_border["all"],
                "mobile-row-border-left-width" => $row_border["all"],
                // "mobile-row-border-color" => $row_border["color"],
                "mobile-row-border-style" => $row_border["style"],

                "desktop-row-bg-color" => $row_background,
                // "mobile-row-bg-color" => $row_background,
                "desktop-row-alternate" => true,

                "desktop-row-alternate-bg-color" => $row_alternate_background,
                // "mobile-row-alternate-bg-color" => $row_alternate_background,

                // yet to be implemented in tabel level settings
                "desktop-row-line-height" => $row_typography["line-height"],
                "desktop-row-font-style" => $row_typography["font-style"],
                "desktop-row-letter-spacing" => $row_typography["letter-spacing"],
                "desktop-row-font-weight" => $row_font_wieght,
                "desktop-row-text-transform" => $row_typography["text-transform"],

                "mobile-row-line-height" => $row_typography["line-height"],
                "mobile-row-font-style" => $row_typography["font-style"],
                "mobile-row-letter-spacing" => $row_typography["letter-spacing"],
                "mobile-row-font-weight" => $row_font_wieght,
                "mobile-row-text-transform" => $row_typography["text-transform"],
            ];

            $style_defaults = TableLevelSettings::get_fields_defaults("style");
            $default_theme = DefaultTheme::get_theme();
            $global_style = array_merge($global_header_style, $global_row_style);
            $style_settings = array_merge($style_defaults, $default_theme, $table_level_style_db);

            // Editor mode
            if ($is_edit_table) {
                if ($this->utils->get_bool(Tablesome_Getter::get("style_disable"))) {
                    $global_style = [];
                }

                return array_merge($style_defaults, $default_theme, $global_style, $table_level_style_db);
            }

            // read-only mode
            if ($style_settings["style-mode"] == "global") {
                if ($this->utils->get_bool(Tablesome_Getter::get("style_disable"))) {
                    return [];
                }

                return $global_style;
            }

            return $style_settings;
        }

        public function get_bool($value = false)
        {
            $boolean = false;

            if ($value == true || $value == 1 || $value == "true" || $value == "1") {
                $boolean = true;
            }

            return $boolean;
        }

        public function get_itemProps($collection = [])
        {
            $table_data['columns'] = $this->get_columns($this->columns, $collection);
            $table_data['rows'] = $this->get_rows_new($collection);

            return $table_data;
        }

        public function get_rows_new($collection)
        {
            /** Getting Records */
            $table_instance = $this->tablesome_db->create_table_instance($this->table_id);
            $number = isset($collection['pagination']) && $collection['pagination'] == 1 ? $collection["display"]['numOfRecordsPerPage'] : 0;

            $query = array(
                'table_id' => $this->table_id,
                'table_name' => $table_instance->name,
                'number' => $number,
                'orderby' => array('rank_order', 'id'),
                'order' => 'asc',
            );

            // To filter tablesome records using Actions
            $query = apply_filters("tablesome_records_query", $query);

            $args = $query;
            $args['collection'] = $collection;
            $args['table_meta'] = $this->table_meta;

            // New Method
            $rows = $this->tablesome_db->get_rows($args);

            // Old Method
            // $result = $this->tablesome_db->query($query);
            // $records = isset($result->items) ? $result->items : [];
            // $rows = $this->tablesome_db->get_formatted_rows($records, $this->table_meta, $collection);

            // $rows = [];

            return $rows;
        }

        // Refactor: is this method used anywhere?
        protected function get_rows($table_data)
        {
            $processed_rows = [];
            $rows = isset($table_data['records']) && !empty($table_data['records']) ? $table_data['records'] : [];
            // Create New table
            if (empty($rows)) {
                array_push($processed_rows, [
                    "record_id" => 0,
                    "content" => [""],
                    "rank_order" => "0|100000:",
                ]);
            }

            $columns = isset($table_data['columns']) && !empty($table_data['columns']) ? $table_data['columns'] : [];

            foreach ($rows as $index => $row) {
                array_push($processed_rows, $this->get_row($row, $columns));
            }

            // unset the $rows after get the row informations
            unset($rows);

            return $processed_rows;
        }

        // Refactor: is this method used anywhere?
        protected function get_row($row, $columns)
        {
            $cells = isset($row['content']) ? $row['content'] : [];

            if (empty($cells)) {
                return $row;
            }
            /** get exclude column ids */
            $exclude_column_ids = isset($this->collection['exclude_column_ids']) && !empty($this->collection['exclude_column_ids']) ? explode(",", $this->collection['exclude_column_ids']) : [];

            foreach ($cells as $cell_key => $cell_data) {

                /** remove the cell if that cell key (or) ID has found in exclude columns array */
                if (in_array($cell_key, $exclude_column_ids)) {
                    unset($row['content'][$cell_key]);
                    continue;
                }

                // $value = isset($cell_data["value"]) && !empty($cell_data["value"]) ? $cell_data["value"] : $cell_data;
                $cell = [
                    'type' => get_tablesome_cell_type($cell_key, $columns),
                    'html' => isset($cell_data) && isset($cell_data["html"]) && !empty($cell_data["html"]) ? $cell_data["html"] : $cell_data["value"],
                    'value' => isset($cell_data) && isset($cell_data["value"]) ? $cell_data["value"] : "",
                ];

                if (isset($cell_data["link"]) && !empty($cell_data["link"])) {
                    $cell = array_merge($cell, ["link" => $cell_data["link"]]);
                }

                if (isset($cell_data["linkText"]) && !empty($cell_data["linkText"])) {
                    $cell = array_merge($cell, ["linkText" => $cell_data["linkText"]]);
                }

                $cell = apply_filters("tablesome_get_cell_data", $cell);

                $row['content'][$cell_key] = $cell;
            }

            return $row;
        }

        public function get_columns(array $columns, array $collectionProps)
        {
            // $exclude_column_ids values received as string
            $exclude_column_ids = isset($collectionProps['exclude_column_ids']) && !empty($collectionProps['exclude_column_ids']) ? explode(",", $collectionProps['exclude_column_ids']) : [];

            foreach ($columns as $index => $column) {

                $columns[$index]["name"] = html_entity_decode($column["name"]);

                if (!empty($exclude_column_ids) && isset($column['id']) && in_array($column['id'], $exclude_column_ids)) {
                    /** remove the exclude columns */
                    unset($columns[$index]);
                }
            }
            // re-arranging the orders.
            $columns = array_values($columns);

            return $columns;
        }

        public function get_table_title($post_id)
        {
            $post_title = '';

            if (!empty($post_id)) {
                $post = get_post($post_id);
            }

            if (isset($post)) {
                $post_title = $post->post_title;
            }

            return $post_title;
        }
    }
}
