<?php

namespace Tablesome\Components\Table;

if ( !class_exists( '\\Tablesome\\Components\\Table\\Duplicate_Table' ) ) {
    class Duplicate_Table
    {
        public function __construct()
        {
            $this->core_table = new \Tablesome\Includes\Core\Table();
        }
        
        public function duplicate_table( $post )
        {
            /** Get current user ID */
            $new_table_id = $this->copy_table_post( $post );
            if ( empty($new_table_id) ) {
                return false;
            }
            $post_meta_copied = $this->copy_table_meta_data( $post->ID, $new_table_id );
            if ( !$post_meta_copied ) {
                return false;
            }
            /** Copy the records from source table and insert the records into duplicated table  */
            // $records_copied = $this->core_table->copy_table_records($post->ID, $new_table_id);
            // if (false == $records_copied) {
            //     return false;
            // }
            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $old_table_instance = $tablesome_db->create_table_instance( $post->ID );
            $table_copied = $tablesome_db->duplicate_table( $old_table_instance, $new_table_id );
            if ( false == $table_copied ) {
                return false;
            }
            return $new_table_id;
        }
        
        public function copy_table_post( $post )
        {
            $current_user = wp_get_current_user();
            $author_id = $current_user->ID;
            $table_data = array(
                'post_author'  => $author_id,
                'post_content' => $post->post_content,
                'post_name'    => 'copy-of-' . $post->post_name,
                'post_status'  => $post->post_status,
                'post_title'   => 'Copy of ' . $post->post_title,
                'post_type'    => TABLESOME_CPT,
            );
            $new_table_id = $this->core_table->insert_or_update_post( 0, $table_data );
            return $new_table_id;
        }
        
        public function copy_table_meta_data( $source_table_id, $new_table_id )
        {
            /** Get the source table meta data */
            $source_table_post_meta = get_tablesome_data( $source_table_id );
            /** Get the source table trigger data */
            $source_table_trigger_meta = get_tablesome_table_triggers( $source_table_id );
            /** add those data to the copied table */
            update_post_meta( $new_table_id, 'tablesome_data', $source_table_post_meta );
            update_post_meta( $new_table_id, 'tablesome_table_triggers', $source_table_trigger_meta );
            $meta_data_copied = metadata_exists( 'post', $new_table_id, 'tablesome_data' );
            $trigger_data_copied = metadata_exists( 'post', $new_table_id, 'tablesome_table_triggers' );
            return ( $meta_data_copied && $trigger_data_copied ? true : false );
        }
        
        /**
         *  use of this method to adding the duplicate action link to the tablesome table summary
         */
        public function modify_table_row_actions( $actions, $post )
        {
            // global $tablesome_tables_count_collection;
            if ( !current_user_can( 'edit_posts' ) ) {
                return $actions;
            }
            if ( isset( $post ) && $post->post_type != TABLESOME_CPT ) {
                return $actions;
            }
            $actions['export'] = '<a href="' . admin_url( 'admin.php?page=tablesome-export&action=export&table_id=' . $post->ID ) . '">' . __( 'Export', 'tablesome' ) . '</a>';
            $actions['duplicate'] = $this->get_action_url( $post );
            return $actions;
        }
        
        public function get_action_url( $post )
        {
            /** duplicate action url */
            $url = wp_nonce_url( add_query_arg( array(
                'action'   => 'duplicate_the_tablesome_table',
                'table_id' => $post->ID,
            ), 'admin.php' ), TABLESOME_PLUGIN_BASE, 'tablesome_duplicate_nonce' );
            $title = __( 'Duplicate the table', 'tablesome' );
            $link_text = __( 'Duplicate', 'tablesome' );
            $classes = 'tablesome__table-action--duplicate';
            $link_text .= '<span class="tablesome__premiumText">PRO</span>';
            $classes .= ' free';
            $url = tablesome_fs()->get_trial_url();
            return '<a class="' . $classes . '" href="javascript:void(0);" data-url="' . $url . '" title="' . $title . '" rel="permalink">' . $link_text . '</a>';
        }
        
        public function show_notices()
        {
            $post_type = ( isset( $_GET['post_type'] ) ? $_GET['post_type'] : '' );
            $action = ( isset( $_GET['link_action'] ) ? $_GET['link_action'] : '' );
            $status = ( isset( $_GET['status'] ) ? $_GET['status'] : '' );
            if ( $post_type != TABLESOME_CPT || $action != 'DUPLICATE' ) {
                return;
            }
            $status_content = array(
                'MISSING_POST_ID'     => array(
                'class'   => 'notice-warning',
                'message' => __( 'Missing Tablesome table ID ', 'tablesome' ),
            ),
                'SESSION_EXPIRED'     => array(
                'class'   => 'notice-warning',
                'message' => __( 'Session Expired, Please try again.', 'tablesome' ),
            ),
                'INVALID_POST_ID'     => array(
                'class'   => 'notice-warning',
                'message' => __( 'Invalid Table ID', 'tablesome' ),
            ),
                'TABLE_NOT_DUPLICATE' => array(
                'class'   => 'notice-warning',
                'message' => __( 'Table Can\'t duplicated, Please try again', 'tablesome' ),
            ),
                'TABLE_DUPLICATED'    => array(
                'class'   => 'notice-success',
                'message' => __( 'Table duplicated successfully.', 'tablesome' ),
            ),
            );
            $notice_class = ( isset( $status_content[$status]['class'] ) ? $status_content[$status]['class'] : 'notice-warning' );
            $desc = ( isset( $status_content[$status] ) ? $status_content[$status]['message'] : 'Something went wrong to duplicating the table, try again later' );
            $html = '<div class="helpie-notice notice ' . $notice_class . ' is-dismissible" >';
            $html .= '<p>' . $desc . '</p>';
            $html .= '</div>';
            add_action( 'admin_notices', function () use( $html ) {
                echo  $html ;
            } );
        }
    
    }
}