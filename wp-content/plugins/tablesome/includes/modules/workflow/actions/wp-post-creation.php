<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use Tablesome\Includes\Modules\Workflow\Action;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\WP_Post_Creation')) {
    class WP_Post_Creation extends Action
    {

        public $available_status = array(
            'publish' => 'Publish',
            'future' => 'Future',
            'draft' => 'Draft',
            'pending' => 'Pending',
            'private' => 'Private',
            'trash' => 'Trash',
        );

        private static $DEFAULT_POST_STATUS = 'draft';

        private static $DEFAULT_POST_TITLE = 'Untitled Post';

        public function get_config()
        {
            return array(
                'id' => 4,
                'name' => 'add_new_wp_post',
                'label' => __('Add New WP Post', 'tablesome'),
                'integration' => 'wordpress',
                'is_premium' => true,
            );
        }

        public function do_action($trigger_class, $trigger_instance)
        {

            $this->bind_props($trigger_class, $trigger_instance);

            if (empty($this->fields)) {
                return;
            }

            $post_data = $this->get_post_data_from_trigger();
            $result = wp_insert_post($post_data);

            if (is_wp_error($result)) {
                // $message = $result->get_error_message();
                return false;
            }

            $this->post_id = $result;

            $this->add_postmeta();

            $this->set_post_terms();

            $this->set_featured_image($post_data);
        }

        private function bind_props($trigger_class, $trigger_instance)
        {

            $this->trigger_class = $trigger_class;
            $this->trigger_instance = $trigger_instance;

            $this->trigger_source_data = $this->trigger_class->trigger_source_data['data'];
            $this->action_meta = isset($this->trigger_instance['action_meta']) ? $this->trigger_instance['action_meta'] : [];

            $this->fields = isset($this->action_meta['fields']) ? $this->action_meta['fields'] : [];

            // set post_id is 0
            $this->post_id = 0;

            $this->smart_field_values = get_tablesome_smart_field_values();
        }

        private function get_post_data_from_trigger()
        {
            $post_data = array();

            $post_type_index = array_search('post_type', array_column($this->fields, 'name'));
            $post_type = isset($this->fields[$post_type_index]['value']) ? $this->fields[$post_type_index]['value'] : '';
            if (empty($post_type)) {
                return;
            }
            $post_type_exists = post_type_exists($post_type);

            if (!$post_type_exists) {
                return;
            }

            $post_data['post_title'] = $this->get_post_prop_value_by_name('post_title');
            $post_data['post_content'] = $this->get_post_prop_value_by_name('post_content');
            $post_data['post_type'] = $post_type;
            $post_data['post_status'] = $this->get_post_prop_value_by_name('post_status');
            $post_data['post_excerpt'] = $this->get_post_prop_value_by_name('post_excerpt');
            $post_data['post_featured_image'] = $this->get_post_prop_value_by_name('post_featured_image');
            // $post_data['post_author'] = $this->get_post_prop_value_by_name('post_author');

            return $post_data;
        }

        private function get_post_prop_value_by_name($name)
        {
            $index = array_search($name, array_column($this->fields, 'name'));
            if (!is_numeric($index)) {
                return '';
            }

            $source_type = isset($this->fields[$index]['source_type']) ? $this->fields[$index]['source_type'] : 'custom';
            $value = isset($this->fields[$index]['value']) ? $this->fields[$index]['value'] : '';

            $target_field = $value;

            if ('trigger_source' === $source_type) {
                $value = isset($this->trigger_source_data[$target_field]['value']) ? $this->trigger_source_data[$target_field]['value'] : '';
            } else if ('trigger_smart_fields' === $source_type) {
                $value = isset($this->smart_field_values[$target_field]) ? $this->smart_field_values[$target_field] : '';
            }

            if ('post_status' === $name) {
                return !empty($value) && in_array($value, array_keys($this->available_status)) ? $value : self::$DEFAULT_POST_STATUS;
            } else if ('post_title' === $name) {
                return !empty($value) ? wp_strip_all_tags($value) : self::$DEFAULT_POST_TITLE;
            } else if ('post_author' === $name) {

                if (is_numeric($value)) {
                    return get_user_by('id', $value) ? intval($value) : 0;
                } else if (is_string($value)) {
                    /**
                     * If username exists its return the user-id else its return false.
                     * Ref: https://developer.wordpress.org/reference/functions/username_exists/
                     */
                    $user_id = username_exists($value);
                    return isset($user_id) ? $user_id : 0;
                }
            }

            return $value;
        }

        private function add_postmeta()
        {

            $postmeta_fields = array_filter($this->fields, function ($field) {
                return $field['field_type'] == 'postmeta';
            });

            if (empty($postmeta_fields)) {
                return;
            }

            foreach ($postmeta_fields as $field) {
                $meta_key = isset($field['key']) ? $field['key'] : '';
                $target_field = isset($field['value']) ? $field['value'] : '';
                $source_type = isset($field['source_type']) ? $field['source_type'] : 'custom';

                if (empty($meta_key)) {
                    continue;
                }
                $meta_value = $target_field;
                if ('trigger_source' === $source_type) {
                    $meta_value = isset($this->trigger_source_data[$target_field]['value']) ? $this->trigger_source_data[$target_field]['value'] : '';
                } else if ('trigger_smart_fields' === $source_type) {
                    $meta_value = isset($this->smart_field_values[$target_field]) ? $this->smart_field_values[$target_field] : '';
                }
                update_post_meta($this->post_id, $meta_key, $meta_value);
            }
        }

        private function set_post_terms()
        {

            $taxonomy_fields = array_filter($this->fields, function ($field) {
                return $field['field_type'] == 'taxonomies';
            });

            if (empty($taxonomy_fields)) {
                return;
            }

            foreach ($taxonomy_fields as $taxonomy_field) {

                $taxonomy = isset($taxonomy_field['taxonomy']) ? $taxonomy_field['taxonomy'] : '';

                if (empty($taxonomy) || !taxonomy_exists($taxonomy)) {
                    continue;
                }

                $terms = isset($taxonomy_field['terms']) ? $taxonomy_field['terms'] : [];
                $source_type = isset($taxonomy_field['source_type']) ? $taxonomy_field['source_type'] : 'custom';

                if ('custom' === $source_type) {
                    foreach ($terms as $term_value) {
                        $term = get_term_by('id', (int) $term_value, $taxonomy);

                        if (!isset($term) || is_wp_error($term)) {
                            continue;
                        }

                        wp_set_object_terms($this->post_id, intval($term->term_id), $taxonomy, true);
                    }
                } else if ('trigger_source' === $source_type || 'trigger_smart_fields' === $source_type) {
                    // TODO How users includes the terms in form?

                }
            }

        }

        private function set_featured_image($post_data)
        {
            $attachment_id = $post_data["post_featured_image"];
            $does_attachment_id_exist = isset($attachment_id) && !empty($attachment_id);
            $does_this_attachment_post_type = 'attachment' === get_post_type($attachment_id);

            if (!$does_attachment_id_exist || !$does_this_attachment_post_type) {
                return false;
            }

            wp_update_post(array(
                'ID' => $attachment_id,
                'post_parent' => $this->post_id,
            ), true);

            require_once ABSPATH . "/wp-admin/includes/image.php";
            $attachment_data = wp_generate_attachment_metadata($attachment_id, get_attached_file($attachment_id));
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            set_post_thumbnail($this->post_id, $attachment_id);
        }
    }
}
