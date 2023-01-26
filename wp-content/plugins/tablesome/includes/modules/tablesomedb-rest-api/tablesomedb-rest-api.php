<?php

namespace Tablesome\Includes\Modules\TablesomeDB_Rest_Api;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\Tablesome\Includes\Modules\TablesomeDB_Rest_Api\TablesomeDB_Rest_Api')) {
    class TablesomeDB_Rest_Api
    {
        public $tablesome_db;

        public function init()
        {
            $namespece = 'tablesome/v1';

            $this->tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();

            /** All REST-API Routes */
            $routes_controller = new \Tablesome\Includes\Modules\TablesomeDB_Rest_Api\Routes();
            $routes = $routes_controller->get_routes();

            foreach ($routes as $route) {
                /** Register the REST route */
                register_rest_route($namespece, $route['url'], $route['args']);
            }
        }

        public function api_access_permission()
        {
            if (current_user_can('edit_posts')) {
                return true;
            }
            $error_code = "UNAUTHORIZED";
            return new \WP_Error($error_code, $this->get_error_message($error_code));
        }

        public function get_error_message($error_code)
        {
            $messages = array(
                'UNAUTHORIZED' => "You don't have an permission to access this resource",
                'REQUIRED_POST_ID' => "Required, Tablesome table ID ",
                'INVALID_POST' => "Invalid, Tablesome post",
                'REQUIRED_RECORD_IDS' => "Required, Tablesome table record IDs",
                'UNABLE_TO_CREATE' => "Unable to create a post.",
            );

            $message = isset($messages[$error_code]) ? $messages[$error_code] : 'Something Went Wrong, try later';
            return $message;
        }

        public function create_or_update_table($request)
        {
            $params = $request->get_params();
            $table = new \Tablesome\Includes\Core\Table();
            $table_id = isset($params['table_id']) ? $params['table_id'] : 0;
            $columns = isset($params['columns']) ? $params['columns'] : [];
            $last_column_id = isset($params['last_column_id']) ? $params['last_column_id'] : 0;
            $triggers = isset($params['triggers']) ? $params['triggers'] : [];
            $editor_state = isset($params['editorState']) ? $params['editorState'] : [];
            $display = isset($params['display']) ? $params['display'] : [];
            $style = isset($params['style']) ? $params['style'] : [];

            // error_log(' triggers : ' . print_r($triggers, true));
            // error_log(' display : ' . print_r($display, true));

            $post_data = array(
                'post_title' => isset($params['table_title']) ? $params['table_title'] : 'Untitled Table',
                'post_type' => TABLESOME_CPT,
                'post_content' => isset($params['content']) ? $params['content'] : '',
                'post_status' => isset($params['table_status']) ? $params['table_status'] : 'publish',
            );
            $table_id = $table->insert_or_update_post($table_id, $post_data);

            if (empty($table_id)) {
                $response = array(
                    'status' => 'failed',
                    'message' => $this->get_error_message('UNABLE_TO_CREATE'),
                );
                return rest_ensure_response($response);
            }

            set_tablesome_table_triggers($table_id, $triggers);

            set_tablesome_data($table_id,
                array(
                    'editorState' => $editor_state,
                    'options' => array(
                        'display' => $display,
                        'style' => $style,
                    ),
                    'columns' => $columns,
                    'meta' => array(
                        'last_column_id' => $last_column_id,
                    ),
                )
            );

            $meta_data = get_tablesome_data($table_id);

            $response = array(
                'table_id' => $table_id,
                'table_meta' => $meta_data,
                'status' => 'success',
            );
            return rest_ensure_response($response);
        }

        public function get_tables($request)
        {
            $data = array();
            /** Get all tablesome posts */
            $posts = get_posts(
                array(
                    'post_type' => TABLESOME_CPT,
                    'numberposts' => -1,
                )
            );
            $response_data = array(
                'data' => $data,
                'message' => 'Get all tablesome tables data',
            );

            if (empty($posts)) {
                return rest_ensure_response($response_data);
            }
            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();

            foreach ($posts as $post) {
                $meta_data = get_tablesome_data($post->ID);

                error_log('$meta_data : ' . print_r($meta_data, true));

                $table = $tablesome_db->create_table_instance($post->ID);
                /** Get records count */
                $records_count = $table->count();

                $data[] = array(
                    'ID' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_content' => $post->post_title,
                    'post_status' => $post->post_status,
                    'meta_data' => $meta_data,
                    'records_count' => $records_count,
                );
            }

            $response_data['data'] = $data;
            return rest_ensure_response($data);
        }

        public function get_table_data($request)
        {
            $data = array();
            $table_id = $request->get_param('table_id');
            $post = get_post($table_id);

            if (empty($post) || $post->post_type != TABLESOME_CPT) {
                $error_code = "INVALID_POST";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }
            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $table_meta = get_tablesome_data($post->ID);

            $table = $tablesome_db->create_table_instance($post->ID);
            $records_count = $table->count();

            // $query = $tablesome_db->query(array(
            //     'table_id' => $post->ID,
            //     'table_name' => $table->name,
            //     'orderby' => array('rank_order', 'id'),
            //     'order' => 'asc',
            // ));

            // $records = isset($query->items) ? $query->items : [];
            // $records = $tablesome_db->get_formatted_rows($records, $table_meta, []);

            $args = array(
                'table_id' => $post->ID,
                'table_name' => $table->name,
            );

            $args['table_meta'] = $table_meta;
            $args['collection'] = [];

            $records = $tablesome_db->get_rows($args);

            $data = array(
                'ID' => $post->ID,
                'post_title' => $post->post_title,
                'post_content' => $post->post_content,
                'post_status' => $post->post_status,
                'meta_data' => $table_meta,
                'records' => $records,
                'records_count' => $records_count,
                'status' => 'success',
                'message' => 'Successfully get table with records',
            );

            return rest_ensure_response($data);
        }

        public function delete($request)
        {
            $table_id = $request->get_param('table_id');

            if (empty($table_id)) {
                $error_code = "REQUIRED_POST_ID";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }

            $post = get_post($table_id);

            if (empty($post) || $post->post_type != TABLESOME_CPT) {
                $error_code = "INVALID_POST";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }
            $table = $this->tablesome_db->create_table_instance($post->ID);
            $table_drop = $table->drop();

            $message = 'Table Deleted';
            if (!$table_drop) {
                $message = 'Can\'t delete the table';
            }

            $response_data = array(
                'message' => $message,
            );
            return rest_ensure_response($response_data);
        }

        public function get_table_records($request)
        {
            $params = $request->get_params();

            $table_id = isset($params['table_id']) ? $params['table_id'] : 0;

            if (empty($table_id)) {
                $error_code = "REQUIRED_POST_ID";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }

            $query_args = isset($params['query_args']) && is_array($params['query_args']) ? $params['query_args'] : [];

            $post = get_post($table_id);

            if (empty($post) || $post->post_type != TABLESOME_CPT) {
                $error_code = "INVALID_POST";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }
            $table_meta = get_tablesome_data($post->ID);
            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $table = $tablesome_db->create_table_instance($post->ID);

            $args = array_merge(
                array(
                    'table_id' => $post->ID,
                    'table_name' => $table->name,
                ), $query_args
            );

            $records = $tablesome_db->get_rows($args);

            // $query = $tablesome_db->query($query_args);

            // // TODO: Return the formatted data if need. don't send the actual db data
            // $records = isset($query->items) ? $query->items : [];

            $response_data = array(
                'records' => $tablesome_db->get_formatted_rows($records, $table_meta, []),
                'message' => 'Get records successfully',
                'status' => 'success',
            );

            return rest_ensure_response($response_data);
        }

        public function modified_records($request)
        {
            $params = $request->get_params();
            $args = array();

            // error_log('$params : ' . print_r($params, true));

            $args['table_id'] = isset($params['table_id']) ? $params['table_id'] : 0;
            $args['meta_data'] = get_tablesome_data($args['table_id']);

            if (empty($args['table_id'])) {
                $error_code = "REQUIRED_POST_ID";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }

            $post = get_post($args['table_id']);

            if (empty($post) || $post->post_type != TABLESOME_CPT) {
                $error_code = "INVALID_POST";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }

            $records_inserted = isset($params['records_inserted']) ? $params['records_inserted'] : [];
            $args['records_updated'] = isset($params['records_updated']) ? $params['records_updated'] : [];
            $records_deleted = isset($params['records_deleted']) ? $params['records_deleted'] : [];

            $requests = array(
                'columns_deleted' => isset($params['columns_deleted']) ? $params['columns_deleted'] : [],
            );

            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $table = $tablesome_db->create_table_instance($args['table_id'], [], $requests);
            $args['table_name'] = $table->name;

            $args['query'] = $tablesome_db->query(array(
                'table_id' => $args['table_id'],
                'table_name' => $args['table_name'],
            ));

            /** Table MetaData */

            $inserted_records_count = 0;
            $updated_records_count = 0;

            if (is_array($records_deleted) && !empty($records_deleted)) {
                $tablesome_db->delete_records($args['query'], $records_deleted);
            }

            /** Insert all records  */
            if (!empty($records_inserted) && is_array($records_inserted)) {
                $insert_info = $tablesome_db->insert_many($args['table_id'], $args['meta_data'], $records_inserted);
                $inserted_records_count = isset($insert_info) && $insert_info['records_inserted_count'] ? $insert_info['records_inserted_count'] : 0;
            }

            // TODO: Need implement updating bulk record
            /**  */
            $response_data = $tablesome_db->update_records($args);

            $response_data = array_merge($response_data, array(
                'inserted_records_count' => $inserted_records_count,
                // 'updated_records_count' => $updated_records_count,
                'message' => 'Records modified successfully',
                'status' => 'success',
            ));

            return rest_ensure_response($response_data);
        }

        public function delete_records($request)
        {
            $params = $request->get_params();
            $table_id = $request->get_param('table_id');
            if (empty($table_id)) {
                $error_code = "REQUIRED_POST_ID";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }

            $record_ids = $request->get_param("record_ids");

            $post = get_post($table_id);

            if (empty($post) || $post->post_type != TABLESOME_CPT) {
                $error_code = "INVALID_POST";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }

            if (empty($record_ids)) {
                $error_code = "REQUIRED_RECORD_IDS";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }

            $message = 'Records removed successfully';

            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $table = $tablesome_db->create_table_instance($post->ID);

            $query = $tablesome_db->query(array(
                'table_id' => $post->ID,
                'table_name' => $table->name,
            ));

            $delete_records = $tablesome_db->delete_records($query, $record_ids);

            $response_data = array(
                'message' => $message,
                'status' => ($delete_records) ? 'success' : 'failed',
            );
            return rest_ensure_response($response_data);
        }

    }
}
