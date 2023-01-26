<?php

namespace Pauple\Pluginator;

// if (!defined('ABSPATH')) {
//     exit; // Exit if accessed directly.
// }

if (!class_exists('\Pauple\Pluginator\ElementorMigration')) {
    class ElementorMigration
    {

        public $update_elementor_data = false;

        public $update_elementor_controls_usage = false;

        /** keep the elementor migrated fields */
        public $migrated_fields = array();

        public function __construct($data)
        {
            global $wpdb;
            $this->wpdb = $wpdb;
            $this->table_name = $wpdb->prefix . 'postmeta';
            $this->field_name = 'post_id';

            /** name of the widget for migration the widget settings.. */
            $this->widget_type = isset($data['widget_type']) ? $data['widget_type'] : '';

            /** get the elementor migration callback method name */
            $this->callback = isset($data['callback']) ? $data['callback'] : '';

            /** In _elementor_controls_usage meta, stores widget changed fields name. So we should update the migrating fields also. */
            $this->migrated_fields = isset($data['migrated_fields']) ? $data['migrated_fields'] : [];
        }

        public function run()
        {
            /*** run before, check the migration method are found in class */
            if (!method_exists($this->callback[0], $this->callback[1]) || empty($this->widget_type)) {
                return;
            }

            $this->migrate_elementor_data_settings();
            $this->migrate_elementor_controls_usage_settings();
        }

        public function migrate_elementor_data_settings()
        {
            $meta_key = "_elementor_data";
            $statement = $this->wpdb->prepare('SELECT ' . $this->field_name . ' FROM ' . $this->table_name . ' WHERE meta_key = %s and meta_value like %s', $meta_key, "%$this->widget_type%");
            /** Get all post ids from the post meta table */
            $post_ids = $this->wpdb->get_col($statement);
            foreach ($post_ids as $post_id) {
                $meta_data = get_post_meta($post_id, $meta_key, true);
                $meta_data = isset($meta_data) ? json_decode($meta_data, true) : [];

                if (empty($meta_data)) {
                    continue;
                }
                $this->update_elementor_data = false;
                $updated_data = $this->update_the_elementor_data($meta_data);

                if (true == $this->update_elementor_data) {
                    $encoded_meta_data = json_encode($updated_data);
                    update_metadata('post', $post_id, $meta_key, $encoded_meta_data);
                }
            }

        }

        public function migrate_elementor_controls_usage_settings()
        {
            $meta_key = '_elementor_controls_usage';
            $statement = $this->wpdb->prepare('SELECT ' . $this->field_name . ' FROM ' . $this->table_name . ' WHERE meta_key = %s and meta_value like %s', $meta_key, "%$this->widget_type%");
            $post_ids = $this->wpdb->get_col($statement);

            foreach ($post_ids as $post_id) {
                $controls_meta_data = get_post_meta($post_id, $meta_key, true);
                if (empty($controls_meta_data) || !is_array($controls_meta_data)) {
                    continue;
                }
                $this->update_elementor_controls_usage = false;
                $update_controls_data = $this->update_the_elementor_controls_data($controls_meta_data);
                if (true == $this->update_elementor_controls_usage) {
                    update_metadata('post', $post_id, $meta_key, $update_controls_data);
                }
            }
        }

        public function update_the_elementor_controls_data($controls_meta_data)
        {

            foreach ($controls_meta_data as $key => $data) {
                if ($key == $this->widget_type) {
                    $section_content = isset($data['controls']['content']['section_content']) ? $data['controls']['content']['section_content'] : [];
                    if (!empty($this->migrated_fields)) {
                        $section_content = array_merge($section_content, array_fill_keys($this->migrated_fields, 1));
                    }
                    $data['controls']['content']['section_content'] = $section_content;
                    $controls_meta_data[$key] = $data;
                    $this->update_elementor_controls_usage = true;
                }
            }
            return $controls_meta_data;
        }

        public function update_the_elementor_data($meta_data)
        {
            $elementor_meta_data = array();

            foreach ($meta_data as $index => $data) {

                $elements = isset($data['elements']) ? $data['elements'] : [];
                $element_type = isset($data['elType']) ? $data['elType'] : '';
                $widget_type = isset($data['widgetType']) ? $data['widgetType'] : '';

                if ($element_type == 'widget' && $widget_type == $this->widget_type) {
                    $settings = $this->elementor_settings_combatability($data['settings']);
                    $data['settings'] = $settings;
                    $this->update_elementor_data = true;
                }

                /** looping the inner child elements */
                if (count($elements) > 0) {
                    $elementor_meta_data[$index] = $data;
                    $elementor_meta_data[$index]['elements'] = $this->update_the_elementor_data($elements);
                } else {
                    $elementor_meta_data[$index] = $data;
                }
            }

            return $elementor_meta_data;
        }

        public function elementor_settings_combatability($settings)
        {
            $settings = call_user_func_array($this->callback, array($settings));
            return $settings;
        }

    }

}