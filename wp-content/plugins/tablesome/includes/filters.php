<?php

namespace Tablesome\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Filters')) {
    class Filters
    {
        public function __construct()
        {
            add_filter("the_content", [$this, "content_filter"]);
            /** Re-arranging Tablesome Submenus */
            add_filter('custom_menu_order', array($this, 'rearranging_tablesome_submenus'));

            add_filter('tablesome_data', [$this, 'get_IDed_tablesome_data'], 10, 2);

            add_filter("tablesome_sanitizing_the_array_values", array($this, 'sanitizing_the_array_values'));

            $tablesome_cpt = TABLESOME_CPT;
            add_filter("manage_edit-{$tablesome_cpt}_columns", array($this, 'add_tablesome_shortcode_column'), 10, 2);
            add_filter("manage_{$tablesome_cpt}_posts_custom_column", array($this, 'add_tablesome_shortcode_column_data'), 10, 2);

            add_filter('cron_schedules', [new \Tablesome\Includes\Cron, 'set_intervals']);

            add_filter('wp_check_filetype_and_ext', array($this, 'check_filetype_and_ext'), 10, 5);

            add_filter("post_row_actions", [new \Tablesome\Components\Table\Duplicate_Table, 'modify_table_row_actions'], 10, 2);

            $this->add_compatibility_filters();
        }

        public function check_filetype_and_ext($types, $file, $filename, $mimes, $real_mime = false)
        {
            $extensions = [
                "csv" => "text/csv",
                "xla|xls|xlt|xlw" => "application/vnd.ms-excel",
                "xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "ods" => "application/vnd.oasis.opendocument.spreadsheet",
            ];

            foreach ($extensions as $extension => $mime) {
                if (false !== strpos($filename, '.' . $extension)) {
                    $types['ext'] = $extension;
                    $types['type'] = $mime;
                }
            }

            return $types;
        }

        // Belows are callback functions of adding filters order wise
        public function content_filter($content)
        {
            $post_id = get_the_ID();
            $post_type = get_post_type($post_id);

            // Remove "Tablesome Table" text from table content
            if (isset($post_type) && !empty($post_type) && $post_type == TABLESOME_CPT) {
                $content = isset($content) && is_string($content) ? str_replace("Tablesome Table", "", $content) : $content;
            }

            if (is_singular() && $post_type == TABLESOME_CPT) {

                $args = array(
                    'table_id' => $post_id,
                    'pagination' => true,
                    // 'page_limit' => Tablesome_Getter::get('num_of_records_per_page'),
                    'last_record_id' => 0,
                );

                $table = new \Tablesome\Components\Table\Controller();
                $content .= $table->get_view($args);
            }

            return $content;
        }

        public function rearranging_tablesome_submenus($menu_ord)
        {
            global $submenu;

            if (!isset($submenu['edit.php?post_type=' . TABLESOME_CPT]) || empty($submenu['edit.php?post_type=' . TABLESOME_CPT])) {
                return $menu_ord;
            }

            // 1. Get Tablesome WP Submenus from global $submenu
            $tablesome_submenus = $submenu['edit.php?post_type=' . TABLESOME_CPT];
            foreach ($tablesome_submenus as $index => $tablesome_submenu) {
                /**
                 * default add new post link not use in tablesome. so we should remove that link.
                 */
                if ($tablesome_submenu[0] == 'Add New') {
                    // 2. remove add new submenu link
                    unset($submenu['edit.php?post_type=' . TABLESOME_CPT][$index]);
                }
            }

            return $menu_ord;
        }

        public function get_IDed_tablesome_data(array $data, array $update_data)
        {
            $helpers = new \Tablesome\Includes\Helpers();
            $columns_data = $helpers->get_IDed_columns($data, $update_data);
            $last_column_id = $columns_data['last_column_id'];
            $columns = $columns_data['columns'];
            $options = isset($update_data['options']) ? $update_data['options'] : [];
            $editor_state = isset($update_data['editorState']) ? $update_data['editorState'] : [];
            // $rows = $helpers->get_IDed_rows($columns, $update_data['rows']);

            return [
                'editorState' => $editor_state,
                'options' => $options,
                'columns' => $columns,
                'rows' => [],
                'meta' => [
                    'last_column_id' => $last_column_id,
                ],
            ];
        }

        public function sanitizing_the_array_values(array $data)
        {
            if (!is_array($data)) {
                return $data;
            }
            if (empty($data)) {
                return [];
            }
            $sanitized_data = [];
            foreach ($data as $array_key => $value) {
                $sanitize_key = sanitize_text_field($array_key);
                if (is_array($value)) {
                    $sanitized_data[$sanitize_key] = $this->sanitizing_the_array_values($value);
                } else if ($array_key == "html") {
                    $sanitized_data[$sanitize_key] = wp_kses_post($value);
                } else {
                    $sanitized_data[$sanitize_key] = sanitize_textarea_field($value);
                }
            }
            return $sanitized_data;
        }

        public function add_tablesome_shortcode_column($columns)
        {

            $new_columns = array_slice($columns, 0, 2, true) +
            array("tablesome-shortcode__column" => __("Shortcode", "tablesome")) +
            array_slice($columns, 2, count($columns) - 1, true);

            return $new_columns;
        }

        public function add_tablesome_shortcode_column_data($column_name, $table_id)
        {
            $html = '';
            if (empty($table_id)) {
                return $html;
            }
            if ($column_name != 'tablesome-shortcode__column') {
                return $html;
            }
            $shortcode = "[tablesome table_id='" . $table_id . "'/]";

            $html = '<span class="tablesome-shortcode">';
            $html .= '<span class="tablesome-shortcode__content">' . $shortcode . '</span>';
            $html .= '<span class="tablesome-shortcode__clipboard--icon dashicons dashicons-admin-page" title="Copy to Shortcode Clipboard" id="clipboard-' . $table_id . '"></span>';
            $html .= '<span class="tablesome-shortcode__clipboard">' . __("Shortcode Copied!", "tablesome") . '</span>';
            $html .= '</span>';
            echo $html;
        }

        public function add_compatibility_filters()
        {
            /**
             * Fix #663: Email's are changed to unicodes while updating to V0.5 (when Email Encoder plugin is used)
             */
            add_filter("eeb/validate/is_post_excluded", [$this, "exclude_tablesome_from_encoder_email"]);
        }

        public function exclude_tablesome_from_encoder_email($return)
        {
            $args = array(
                'numberposts' => -1,
                'post_type' => TABLESOME_CPT,
                'fields' => 'ids',
            );
            $posts_ids = get_posts($args);

            return $posts_ids;
        }
    }
}
