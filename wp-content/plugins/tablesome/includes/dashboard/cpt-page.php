<?php

namespace Tablesome\Includes\Dashboard;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( '\\Tablesome\\Includes\\Dashboard\\CPT_Page' ) ) {
    class CPT_Page
    {
        public function get_view( $args )
        {
            $post = null;
            if ( !empty($args['post_id']) ) {
                $post = get_post( $args['post_id'] );
            }
            $html = '';
            $html .= '<div class="tablesome_cpt">';
            $html .= '<form class="tablesome_cpt__form" onSubmit="return false;">';
            $html .= $this->get_form_hidden_fields( $args );
            $html .= $this->get_the_page_title( $args );
            $html .= $this->get_the_title_field( $post );
            $html .= $this->get_shortcode_field( $post );
            $html .= $this->get_table_view( $args );
            $html .= $this->get_form_button_controls( $post, $args );
            $html .= '</form> <!--- Close Form Tag -->';
            $html .= '</div> <!-- Close Tablesome Page -->';
            $html .= $this->get_shortcut_notice();
            return $html;
        }
        
        private function get_form_hidden_fields( $args )
        {
            $html = '';
            // set post ID
            $html .= '<input type="hidden" id="post_id" name="post_id" value="' . $args['post_id'] . '">';
            // set Post type
            $html .= '<input type="hidden" id="post_type" name="post_type" value="' . TABLESOME_CPT . '">';
            // post action
            $html .= '<input type="hidden" id="post_action" name="post_action" value="' . $args['post_action'] . '">';
            return $html;
        }
        
        public function get_the_page_title( $args )
        {
            $html = '';
            $title = __( 'Create New Table', 'tablesome' );
            $add_new_link = '';
            
            if ( $args['post_action'] == 'edit' ) {
                $title = __( 'Edit Table', "tablesome" );
                $add_new_link = admin_url( 'edit.php?post_type=' . TABLESOME_CPT . '&page=tablesome_admin_page' );
                $post_id = $_GET['post'];
                $post_link = get_permalink( $post_id );
            }
            
            $html .= '<div class="wrap">';
            $html .= '<h1 class="wp-heading-inline">' . $title . '</h1>';
            
            if ( !empty($add_new_link) ) {
                $html .= '<a href="' . $post_link . '" class="tablesome__button--action" target="_blank">' . __( "Preview Table", "tablesome" ) . '<span class="dashicons dashicons-external"></span></a>';
                // $html .= '<a href="' . $add_new_link . '" class="tablesome__button--action">' . __("Add New", "tablesome") . '<span class="dashicons dashicons-plus-alt"></span></a>';
            }
            
            $html .= '</div>';
            return $html;
        }
        
        public function get_the_title_field( $post )
        {
            $post_title = '';
            if ( isset( $post ) ) {
                $post_title = $post->post_title;
            }
            $html = '';
            $html = '<div class="tablesome__fields">';
            // $html .= '<label for="title">Title</label>';
            $html .= '<input type="text" id="title" autofocus name="post_title" class="tablesome__inputText--title" title="' . __( "Table Name", "tablesome" ) . '" placeholder="' . __( "Table Name", "tablesome" ) . '" value="' . $post_title . '">';
            $html .= '</div>';
            return $html;
        }
        
        public function get_form_button_controls( $post, $args )
        {
            // $disabled = (!isset($post->post_title) || empty($post->post_title)) ? 'disabled="disabled"' : '';
            $button_label = __( "Save Table", "tablesome" );
            if ( $args['post_action'] == 'edit' ) {
                $button_label = __( "Update Table", "tablesome" );
            }
            $html = '';
            $html .= '<div class="tablesome_cpt__footer">';
            $html .= '<div class="tablesome__button--wrapper">';
            $html .= '<input type="button" class="tablesome__button--submit" value="' . $button_label . '">';
            $html .= '</div>';
            $html .= '<div class="tablesome__spinner"><div class="tablesome__loader" /></div>';
            $html .= '</div>';
            return $html;
        }
        
        private function get_shortcode_field( $post )
        {
            $html = '';
            if ( !isset( $post ) ) {
                return $html;
            }
            $shortcode_in_text = "[tablesome table_id='" . $post->ID . "'/]";
            $html = '';
            $html = '<div class="tablesome__fields">';
            // $html .= '<label for="tablesome-shortcode">'.__("", "tablesome").'Tablesome Shortcode</label>';
            $html .= '<div class="tablesome__field--shortcodeWrapper" title="Table Shortcode">';
            $html .= '<input type="text" id="tablesome__field--shortcode" value="' . $shortcode_in_text . '" readonly class="tablesome__field--shortcode" >';
            $html .= '<span class="tablesome__field--clipboardText" title="' . __( "Copy Shortcode Clipboard", "tablesome" ) . '">' . __( "Copy Shortcode", "tablesome" ) . '</span>';
            $html .= '</div>';
            $html .= '<p class="description">' . __( "Copy and paste this shortcode in any page or post to display this Table", "tablesome" ) . '</p>';
            $html .= '</div>';
            return $html;
        }
        
        public function get_shortcut_notice()
        {
            $upgrade_link = "";
            $url = tablesome_fs()->get_trial_url();
            $upgrade_link = '<a href="' . $url . '" title="Pro Feature" rel="permalink"> Upgrade >> </a>';
            $html = '<div class="tablesome__notice--shortcuts">';
            $html .= '<div class="tablesome__notice--shortcuts__title"> Navigating with a keyboard </div>';
            $html .= '<p><ol>';
            $html .= '<li>ENTER ↵  Key Move down</li>';
            // $html .= '<li>&#8593; SHIFT  + ENTER ↵  Key Move up</li>';
            $html .= '<li>TAB &#11134;  Key Move right.</li>';
            $html .= '<li>&#8593; SHIFT  + TAB &#11134;  Key Move left.</li>';
            $html .= '</ol></p>';
            $html .= '<div class="tablesome__notice--shortcuts__title"> Row Controls </div>';
            $html .= '<p><ol>';
            $html .= '<li><strong>Drag rows</strong>: by dragging the <span class="dashicons dashicons-menu-alt2"></span> icon, rearrange the row to the location you want. Drag & Drop is for PRO users. ' . $upgrade_link . '</li>';
            $html .= '<li><strong>Row options</strong>: click the <span class="dashicons dashicons-menu-alt2"></span> icon and in the dropdown, can find options such as Add Row, Delete Row, and Duplicate Row (PRO). ' . $upgrade_link . '</li>';
            $html .= '</ol></p>';
            $html .= '</div>';
            return $html;
        }
        
        public function get_table_view( $args )
        {
            $table = new \Tablesome\Components\Table\Controller();
            $table_view = $table->get_view( $args );
            return $table_view;
        }
    
    }
}