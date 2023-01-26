<?php

namespace Tablesome\Includes\Core;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Core\Table')) {
    class Table
    {
        public function __construct()
        {
            $this->crud = new \Tablesome\Includes\Db\CRUD();
            $this->helpers = new \Tablesome\Includes\Helpers();

            // $this->table_crud_wp = new \Tablesome\Includes\Lib\Table_Crud_WP\Table_Crud_WP();
        }

        public function insert_or_update_post($post_id, $post_data)
        {
            $post_data['post_content'] = "Tablesome Table";

            if ($post_id != 0) {
                $post_data['ID'] = $post_id;
                return wp_update_post($post_data);
            }

            return wp_insert_post($post_data);
        }

        public function set_table_meta_data($table_id, $props)
        {
            $rows = isset($props['rows']) ? $props['rows'] : [];
            $records_updated = isset($props['records_updated']) ? $props['records_updated'] : [];
            $records_inserted = isset($props['records_inserted']) ? $props['records_inserted'] : [];
            $records_deleted = isset($props['records_deleted']) ? $props['records_deleted'] : [];
            $columns_deleted = isset($props['columns_deleted']) ? $props['columns_deleted'] : [];
            $columns_duplicated = isset($props['columns_duplicated']) ? $props['columns_duplicated'] : [];
            $requested_columns = isset($props['columns']) ? $props['columns'] : [];
            $columns_inserted = isset($props['columns_inserted']) ? $props['columns_inserted'] : [];

            $columns_duplicated = [];
            $columns_deleted = [];
            $columns_inserted = [];

            $meta_data = set_tablesome_data($table_id, $props);
            $columns = isset($meta_data['columns']) ? $meta_data['columns'] : [];

            if (empty($table_id)) {return;}

            // create a new custom table if not exists from DB
            // $this->table_crud_wp->create_table($table_id, $meta_data);

            // $this->table_crud_wp->modify_table_structure($table_id, $meta_data);

            /*** Removing the Records */
            $this->delete_records($table_id, $records_deleted);

            /*** Columns Inserts */
            // $this->columns_inserted($table_id, $columns, $columns_inserted);

            /*** Column Duplicates */
            // $this->columns_duplicated($table_id, $columns, $columns_duplicated);

            /*** Updating the Existing Records */
            $this->records_updated($table_id, $columns, $records_updated);

            /*** Inserting the New Records  */
            $this->records_inserted($table_id, $columns, $records_inserted);

            /*** Columns Deleted */
            // $this->columns_deleted($table_id, $columns, $columns_deleted);

        }

        public function get_edit_table_url($post_id)
        {
            return get_tablesome_table_edit_url($post_id);
        }

        public function get_all_rows($table_id, array $params = array())
        {
            if (empty($table_id)) {
                return;
            }
            $data = $this->crud->get_all_rows($table_id, $params);
            $rows = $this->helpers->get_decoded_rows($data);
            return $rows;
        }

        public function get_rows($table_id, $record_ids)
        {
            if (empty($table_id)) {
                return;
            }
            $data = $this->crud->get_rows($table_id, $record_ids);
            $rows = $this->helpers->get_decoded_rows($data);
            return $rows;
        }

        public function get_row($table_id, $record_id)
        {
            $row = $this->crud->get_row($table_id, $record_id);
            if (empty($row)) {
                return [];
            }
            /** Converting Object to array */
            $row = (array) $row;
            /** decoding the row content */
            $row['content'] = isset($row['content']) && !empty($row['content']) ? json_decode($row['content'], true) : [];
            return $row;
        }

        public function update_row($table_id, $record_id, $content)
        {
            $meta_data = get_tablesome_data($table_id);
            $Ided_columns = isset($meta_data['columns']) ? $meta_data['columns'] : [];
            $Ided_row = $this->helpers->get_IDed_row($Ided_columns, $content);
            $updated = $this->crud->update($table_id, $record_id, $Ided_row);
            return $updated;
        }

        public function delete_row($table_id, $record_id)
        {
            $delete_row = $this->crud->remove($table_id, $record_id);
            return $delete_row;
        }

        public function insert_row($args)
        {
            if (!isset($args['post_id']) && empty($args['post_id'])) {
                return false;
            }
            $insert_row = $this->crud->insert($args['post_id'], $args);
            return $insert_row;
        }

        public function get_table_data($table_id, array $collectionProps = array())
        {
            $meta_data = get_tablesome_data($table_id);
            $records = $this->get_records($table_id, $collectionProps);
            $meta_data['rows'] = $records;
            return $meta_data;
        }

        public function remove_row_cells($cells, $columns_deleted)
        {
            foreach ($columns_deleted as $column_id) {
                if (isset($cells[$column_id])) {
                    unset($cells[$column_id]);
                }
            }
            return $cells;
        }

        public function check_and_set_empty_column_cells($columns, $cells)
        {
            $new_cells = [];
            foreach ($columns as $index => $column) {
                $column_id = $column['id'];
                if (isset($cells[$column_id])) {
                    $new_cells[$column_id] = $cells[$column_id];
                } else {
                    $new_cells[$column_id] = '';
                }
            }
            return $new_cells;
        }

        private function duplicate_cells($record, $columns_duplicated)
        {
            for ($ii = 0; $ii < count($columns_duplicated); $ii++) {
                $content = [];
                $duplicate_from = $columns_duplicated[$ii]['source_column_id'];
                $column_id = $columns_duplicated[$ii]['column_id'];
                $index = $columns_duplicated[$ii]['index'];
                $content[$column_id] = isset($record[$duplicate_from]) && !empty($record[$duplicate_from]) ? $record[$duplicate_from] : '';
                $record = splice_associative_array($record, $index, $content);
            }
            return $record;
        }

        public function delete_records_by_table_id($table_id)
        {
            if (empty($table_id)) {
                return;
            }
            $delete_records = $this->crud->delete_records_by_table_id($table_id);
            return $delete_records;
        }

        private function set_column_id_for_duplicated_columns($columns, $columns_duplicated)
        {
            for ($ii = 0; $ii < count($columns_duplicated); $ii++) {
                $index = $columns_duplicated[$ii]['index'];
                $current_duplicated_column = isset($columns[$index]) ? $columns[$index] : [];
                if (isset($current_duplicated_column) && !empty($current_duplicated_column)) {
                    $current_duplicated_column_id = $current_duplicated_column['id'];
                    $columns_duplicated[$ii]['column_id'] = $current_duplicated_column_id;
                }
            }
            return $columns_duplicated;
        }

        public function delete_records($table_id, $records_deleted)
        {
            if (empty($records_deleted)) {
                return;
            }
            $this->crud->delete_records($table_id, $records_deleted);
        }

        public function columns_duplicated($table_id, $columns, $columns_duplicated)
        {
            if (empty($columns_duplicated)) {
                return;
            }
            $records = $this->get_all_rows($table_id);
            $columns_duplicated = $this->set_column_id_for_duplicated_columns($columns, $columns_duplicated);
            foreach ($records as $record) {
                /** Remove Row cells value from the records content */
                $cells = $this->duplicate_cells($record['content'], $columns_duplicated);
                $this->crud->update($table_id, $record['record_id'], $cells);
            }
            return true;
        }

        public function records_updated($table_id, $columns, $records_updated)
        {
            if (empty($records_updated)) {
                return;
            }
            foreach ($records_updated as $record) {
                $record_id = isset($record['record_id']) ? $record['record_id'] : 0;
                $row_content = isset($record['content']) ? $record['content'] : [];
                $rank_order = isset($record['rank_order']) && !empty($record['rank_order']) ? $record['rank_order'] : "";
                $Ided_row = $this->helpers->get_IDed_row($columns, $row_content);
                if (is_numeric($record_id) && !empty($record_id)) {
                    $this->crud->update($table_id, $record_id, $Ided_row, $rank_order);
                }
            }
            return true;
        }

        public function records_inserted($table_id, $columns, $records_inserted)
        {
            if (empty($records_inserted)) {
                return;
            }

            foreach ($records_inserted as $record) {
                $row_content = isset($record['content']) ? $record['content'] : [];
                $rank_order = isset($record['rank_order']) && !empty($record['rank_order']) ? $record['rank_order'] : "";
                $Ided_row = $this->helpers->get_IDed_row($columns, $row_content);
                $this->crud->insert($table_id, array(
                    'content' => $Ided_row,
                    'rank_order' => $rank_order,
                ));
            }
            return true;
        }

        public function columns_deleted($table_id, $columns, $columns_deleted)
        {
            if (!isset($columns_deleted) || empty($columns_deleted)) {
                return;
            }
            $records = $this->get_all_rows($table_id);
            if (empty($records)) {
                return;
            }
            foreach ($records as $record) {
                /** Remove Row cells value from the records content */
                $cells = $this->remove_row_cells($record['content'], $columns_deleted);
                /** assign empty values when the row cells content doesn't have a column_id */
                $cells = $this->check_and_set_empty_column_cells($columns, $cells);
                $rank_order = isset($record['rank_order']) && !empty($record['rank_order']) ? $record['rank_order'] : "";
                $this->crud->update($table_id, $record['record_id'], $cells, $rank_order);
            }
            return true;
        }

        public function columns_inserted($table_id, $columns, $columns_inserted)
        {
            if (empty($columns_inserted)) {
                return;
            }
            $records = $this->get_all_rows($table_id);
            foreach ($records as $record) {
                /**
                 * add new column cells value in each record of the table
                 * */
                $cells = $this->add_new_cells($record['content'], $columns, $columns_inserted);
                $this->crud->update($table_id, $record['record_id'], $cells);
            }
            return true;
        }

        public function add_new_cells($record, $columns, $columns_inserted)
        {
            foreach ($columns_inserted as $index => $column_props) {
                $content = [];
                /**
                 * Get the column Id from Current Stored Columns data
                 */
                $column_id = isset($columns[$index]['id']) ? $columns[$index]['id'] : '';
                if (empty($column_id)) {
                    continue;
                }
                $content[$column_id] = '';
                $record = splice_associative_array($record, $index, $content);
            }
            return $record;
        }

        public function get_paginated_records($table_id, array $collectionProps = array())
        {
            $data = $this->crud->get_paginated_records($table_id, $collectionProps);
            $records = $this->helpers->get_decoded_rows($data);
            return $records;
        }

        public function get_records($table_id, $collectionProps)
        {
            $pagination_enabled = isset($collectionProps['pagination']) && $collectionProps['pagination'] == true ? true : false;
            if ($pagination_enabled) {
                return $this->get_paginated_records($table_id, $collectionProps);
            }
            return $this->get_all_rows($table_id);
        }

        public function copy_table_records($table_id, $new_table_id)
        {
            if (empty($table_id) || empty($new_table_id)) {
                return;
            }

            return $this->crud->copy_table_records($table_id, $new_table_id);
        }

    }
}
