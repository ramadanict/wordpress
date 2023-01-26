<?php

namespace Tablesome\Includes\Modules\Workflow\Integrations;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Integrations\WP_Core')) {
    class WP_Core {

        public function get_config() {
            return array(
                'integration' => 'wordpress',
                'integration_label' => __('WordPress', 'tablesome'),
                'is_active' => true,
                'is_premium' => "no",
                'actions' => array(),
            );
        }

        public function get_available_post_types() {
            $args = array(
                'public' => true,
                '_builtin' => false, // skip getting the default post types.
            );

            $post_types = get_post_types($args, 'objects');
            $formatted_post_types = [];

            if (isset($post_types) && !empty($post_types)) {
                foreach ($post_types as $post_type_object) {
                    $formatted_post_types[] = array(
                        'id' => $post_type_object->name,
                        'label' => $post_type_object->label,
                    );
                }
            }

            $defaults = [
                [
                    'id' => 'post',
                    'label' => 'Posts',
                ],
                [
                    'id' => 'page',
                    'label' => 'Pages',
                ],
            ];

            $overall_post_types = array_merge($defaults, $formatted_post_types);

            return $overall_post_types;
        }

        public function get_taxonomies_with_terms_by_post_type($post_type) {

            // Get post type taxonomies.
            $taxonomies = get_object_taxonomies($post_type, 'objects');
            if (is_wp_error($taxonomies) || empty($taxonomies)) {
                return [];
            }

            $formatted_taxonomies = [];

            if (isset($taxonomies) && !empty($taxonomies)) {
                foreach ($taxonomies as $taxonomy_name => $taxonomy) {
                    $taxonomy_data = [
                        'id' => $taxonomy_name,
                        'label' => $taxonomy->label,
                        'post_type' => $post_type,
                    ];

                    $taxonomy_data['terms'] = $this->get_terms_by_taxonomy_name($taxonomy_name);

                    $formatted_taxonomies[] = $taxonomy_data;
                }
            }
            return $formatted_taxonomies;
        }

        public function get_terms_by_taxonomy_name($taxonomy_name) {

            $terms = get_terms(array(
                'taxonomy' => $taxonomy_name,
                'hide_empty' => false,
            ));

            if (is_wp_error($terms) || empty($terms)) {
                return [];
            }

            $options = [];
            foreach ($terms as $term) {
                $options[] = [
                    'id' => $term->term_id,
                    'label' => $term->name,
                    'taxonomy_name' => $taxonomy_name,
                ];
            }

            return $options;
        }

        public function get_postmeta_keys_by_post_type($post_type) {

            global $wpdb;

            $data = $wpdb->get_results($wpdb->prepare("SELECT pm.meta_key FROM $wpdb->postmeta pm JOIN $wpdb->posts p ON p.ID = pm.post_id WHERE p.post_type = %s AND p.post_status = %s group by pm.meta_key", $post_type,'publish'),'ARRAY_A');

            if (is_wp_error($data) || empty($data)) {
                return [];
            }

            $options = [];
            foreach ($data as $item) {
                $options[] = [
                    'id' => $item['meta_key'],
                    'label' => $item['meta_key'],
                    'post_type' => $post_type,
                ];
            }

            return $options;
        }
    }
}