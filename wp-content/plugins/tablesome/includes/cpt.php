<?php

namespace Tablesome\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Cpt')) {
    class Cpt
    {
        /* Register post type in init Hook */
        public function register()
        {
            add_action('init', array($this, 'register_post_type_with_taxonomy'));
        }

        /* Register post type on activation hook cause can't call other filter and actions */
        public function register_tablesome_cpt()
        {
            $this->register_post_type_with_taxonomy();
        }

        public function register_post_type_with_taxonomy()
        {
            $labels = array(
                'name' => _x('Tables', 'post type general name', 'tablesome'),
                'singular_name' => _x('Table', 'post type singular name', 'tablesome'),
                'menu_name' => _x('Tablesome', 'admin menu', 'tablesome'),
                'name_admin_bar' => _x('Table', 'add new on admin bar', 'tablesome'),
                'add_new' => _x('Add New', 'Table', 'tablesome'),
                'add_new_item' => __('Add New Table', 'tablesome'),
                'new_item' => __('New Table', 'tablesome'),
                'edit_item' => __('Edit Table', 'tablesome'),
                'update_item' => __('Update Table', 'tablesome'),
                'view_item' => __('View Table', 'tablesome'),
                'all_items' => __('All Tables', 'tablesome'),
                'search_items' => __('Search Tables', 'tablesome'),
                'not_found' => __('No Tables found', 'tablesome'),
                'parent_item_colon' => __('Parent Tables:', 'tablesome'),
                'not_found' => __('No Tables found.', 'tablesome'),
                'not_found_in_trash' => __('No Tables found in Trash.', 'tablesome'),
                'items_list' => __('Table Items list', 'tablesome'),
                'items_list_navigation' => __('Table Items list Navigation', 'tablesome'),
                'filter_items_list' => __('Filter Table Items list', 'tablesome'),
            );

            $cpt_slug = 'tablesome';

            $publicly_queryable = current_user_can('edit_posts') ? true : false;

            $args = array(
                'labels' => $labels,
                'public' => true,
                'publicly_queryable' => $publicly_queryable,
                'show_ui' => true,
                'menu_position' => 26,
                'menu_icon' => 'dashicons-editor-table',
                'show_in_nav_menus' => false,
                'show_in_rest' => true,
                'map_meta_cap' => true,
                'can_export' => true,
                'has_archive' => true,
                'exclude_from_search' => false,
                'supports' => array('title', 'editor', 'excerpt', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats', 'thumbnail', 'author'),
                'rewrite' => array('slug' => $cpt_slug, 'with_front' => false),
            );

            register_post_type(TABLESOME_CPT, $args);
            // $this->register_category();
            // $this->register_tag();

            flush_rewrite_rules();
        }

        public function register_category()
        {
            $labels = array(
                'name' => _x('Table Categories', 'taxonomy general name', 'tablesome'),
                'singular_name' => _x('Table Category', 'taxonomy singular name', 'tablesome'),
                'search_items' => __('Search Table Categories', 'tablesome'),
                'all_items' => __('All Table Categories', 'tablesome'),
                'parent_item' => __('Parent Table Category', 'tablesome'),
                'parent_item_colon' => __('Parent Table Category:', 'tablesome'),
                'edit_item' => __('Edit Table Category', 'tablesome'),
                'update_item' => __('Update Table Category', 'tablesome'),
                'add_new_item' => __('Add New Table Category', 'tablesome'),
                'new_item_name' => __('New Table Category Name', 'tablesome'),
                'menu_name' => __('Table Category', 'tablesome'),
            );

            $args = array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_in_rest' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'tablesome_category', 'with_front' => false),
            );

            register_taxonomy('tablesome_category', array(TABLESOME_CPT), $args);
        }

        public function register_tag()
        {
            $labels = array(
                'name' => _x('Table Tags', 'taxonomy general name', 'tablesome'),
                'singular_name' => _x('Table Tag', 'taxonomy singular name', 'tablesome'),
                'search_items' => __('Search Table Tags', 'tablesome'),
                'all_items' => __('All Table Tags', 'tablesome'),
                'parent_item' => __('Parent Table Tag', 'tablesome'),
                'parent_item_colon' => __('Parent Table Tag:', 'tablesome'),
                'edit_item' => __('Edit Table Tag', 'tablesome'),
                'update_item' => __('Update Table Tag', 'tablesome'),
                'add_new_item' => __('Add New Table Tag', 'tablesome'),
                'new_item_name' => __('New Table Tag Name', 'tablesome'),
                'menu_name' => __('Table Tag', 'tablesome'),
            );

            $args = array(
                'hierarchical' => true,
                'labels' => $labels,
                'show_ui' => true,
                'show_in_rest' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'tablesome_tag', 'with_front' => false),
            );

            register_taxonomy('tablesome_tag', array(TABLESOME_CPT), $args);
        }

        public function show_other_cpt_and_tax()
        {
            if (taxonomy_exists('tablesome_category')) {
                register_taxonomy_for_object_type('tablesome_category', TABLESOME_CPT);
            }
        }
    } // END CLASS
}