<?php

namespace Tablesome\Includes\Modules\TablesomeDB_Rest_Api;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\TablesomeDB_Rest_Api\Import')) {
    class Import
    {
        public function import_records($request)
        {
            $crud = new \Tablesome\Includes\Db\CRUD();
            error_log('[START] : ' . get_app_memory_usage());
            $params = $request->get_params();

            $table_id = isset($params['table_id']) ? $params['table_id'] : 0;

            if (empty($table_id)) {
                $error_code = "REQUIRED_POST_ID";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }

            $post = get_post($table_id);

            if (empty($post) || $post->post_type != TABLESOME_CPT) {
                $error_code = "INVALID_POST";
                return new \WP_Error($error_code, $this->get_error_message($error_code));
            }
            $records = isset($params['records_inserted']) ? $params['records_inserted'] : [];
            $table_meta = get_tablesome_data($table_id);

            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $table = $tablesome_db->create_table_instance($table_id);
            $insert_info = $tablesome_db->insert_many($table_id, $table_meta, $records);
            $records_inserted_count = isset($insert_info['records_inserted_count']) ? $insert_info['records_inserted_count'] : 0;

            $message = 'No records inserts';
            if ($records_inserted_count > 0) {
                $message = $records_inserted_count . ' records inserted successfully';
            }

            $response_data = array(
                'records_inserted_count' => $records_inserted_count,
                'message' => $message,
            );
            error_log('[END] : ' . get_app_memory_usage());
            return rest_ensure_response($response_data);
        }

        public function get_row_cells($record, $columns)
        {
            $cells = array();

            foreach ($record['content'] as $index => $cell_value) {
                $column_id = $columns[$index]['id'];
                $cells[$column_id] = $cell_value;
            }
            return $cells;
        }
    }
}