<?php

namespace Tablesome\Includes;

if (!class_exists('\Tablesome\Includes\Ajax_Handler')) {
    class Ajax_Handler {

        public function __construct() {
            add_action('wp_ajax_store_tablesome_data', array($this, 'save_table'));
            add_action('wp_ajax_nopriv_store_tablesome_data', array($this, 'save_table'));

            // data import - ajax handler
            add_action('wp_ajax_importing_data', array($this, 'import_table'));
            add_action('wp_ajax_nopriv_importing_data', array($this, 'import_table'));

            /*** Get the table Props  */
            add_action('wp_ajax_get_tables_data', array($this, 'load_tables'));
            add_action('wp_ajax_nopriv_get_tables_data', array($this, 'load_tables'));

            /*** Get the table Props  */
            add_action('wp_ajax_get_table_columns', array($this, 'get_table_columns_by_table_id'));
            add_action('wp_ajax_nopriv_get_table_columns', array($this, 'get_table_columns_by_table_id'));

            add_action('wp_ajax_update_feature_notice_dismissal_data_via_ajax', array(new \Tablesome\Includes\Modules\Feature_Notice(), 'update_feature_notice_dismissal_data_via_ajax'));

            add_action("wp_ajax_get_redirection_data", array($this, 'get_redirection_data'));
            add_action("wp_ajax_nopriv_get_redirection_data", array($this, 'get_redirection_data'));

        }

        public function save_table() {
            /** Get table table-data from ajax request. And decode the table-data.*/
            $data = isset($_REQUEST['table_data']) ? json_decode(stripslashes($_REQUEST['table_data']), true) : [];

            $table = new \Tablesome\Includes\Core\Table();
            $getter = new \Tablesome\Includes\Ajax\Getter();
            $props = $getter->get_tablesome_storing_data_props_from_ajax($data);
            $post_title = isset($props['post_title']) && !empty($props['post_title']) ? $props['post_title'] : '';
            $post_data = array(
                'post_title' => $post_title,
                'post_type' => TABLESOME_CPT,
                'post_content' => '',
                'post_status' => 'publish',
            );

            $post_id = $table->insert_or_update_post($props['post_id'], $post_data);

            $table->set_table_meta_data($post_id, $props);

            $edit_page_url = $table->get_edit_table_url($post_id);

            $response_message = isset($props["post_action"]) && !empty($props["post_action"]) && $props['post_action'] == 'add' ? 'Table Created' : 'Table Updated';

            $reponse = array(
                'message' => $response_message,
                'type' => 'UPDATED',
                'edit_page_url' => $edit_page_url,
            );

            wp_send_json($reponse);
            wp_die();
        }

        public function import_table() {
            $import_controller = new \Tablesome\Components\Import\Controller();
            $process_data = $import_controller->processing_the_importing_data();
        }

        public function load_tables() {
            /** Note: This Class Only for handling the ajax requests */
            $table_getter = new \Tablesome\Includes\Ajax\Getter();
            $tables_props = $table_getter->get_table_data_from_ajax();
            $table_data = $table_getter->get_table_collections_data($tables_props);

            $response = array(
                'status' => 'success',
                'message' => 'Successfully read the tablesome data',
                'data' => $table_data,
            );

            wp_send_json($response);
            wp_die();
        }

        public function get_table_columns_by_table_id() {
            $table_id = isset($_REQUEST['table_id']) && !empty($_REQUEST['table_id']) && intval($_REQUEST['table_id']) ? $_REQUEST['table_id'] : 0;
            $shortcode_builder_handler = new \Tablesome\Includes\Shortcode_Builder\Handler();
            $validate_the_post_id = $shortcode_builder_handler->validate($table_id);
            $columns = [];

            $status = 'failed';
            $message = 'validation failed';
            if ($validate_the_post_id) {
                $status = 'success';
                $message = 'Successfully get the table columns';
                $columns = $shortcode_builder_handler->get_columns($table_id);
            }

            $response = array(
                'status' => $status,
                'message' => $message,
                'data' => $columns,
            );

            wp_send_json($response);
            wp_die();
        }

        public function get_redirection_data() {
            $redirection_data = get_option('workflow_redirection_data');

            error_log('*** get_redirection_data ***');
            $redirection_data = isset($redirection_data) && !empty($redirection_data) ? $redirection_data : [];

            $response = array(
                'status' => 'success',
                'message' => 'Successfully get the redirection data',
                'data' => $redirection_data,
            );

            wp_send_json($response);
            wp_die();
        }

    }
}