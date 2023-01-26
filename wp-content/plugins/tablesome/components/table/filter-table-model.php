<?php

namespace Tablesome\Components\Table;

if (!class_exists('\Tablesome\Components\Table\Filter_Table_Model')) {
    class Filter_Table_Model
    {
        public function __construct()
        {
            $this->table_model = new \Tablesome\Components\Table\Model();
            $this->tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
        }

        public function get_viewProps($collectionProps)
        {
            $this->collection = $collectionProps;
            return [
                'collection' => $this->collection,
                'items' => $this->get_itemProps(),
            ];
        }

        public function get_itemProps()
        {

            $itemsProps = array(
                'columns' => [],
                'rows' => [],
            );

            $table_id = $this->collection["table_id"];
            $this->collection["table_meta"] = get_tablesome_data($table_id);
            $table_meta = $this->collection["table_meta"];
            $filter_table_action_meta = $this->collection["filter_table_action_meta"];
            $table_meta_columns = isset($table_meta['columns']) && !empty($table_meta['columns']) ? $table_meta['columns'] : [];

            $filters = isset($filter_table_action_meta['filters']) ? $filter_table_action_meta['filters'] : [];

            $table_columns = $this->table_model->get_columns($table_meta_columns, $this->collection);

            if (empty($table_columns)) {
                return $itemsProps;
            }

            if (!empty($filters) && isset($filters[0]) && !empty($filters[0]) && isset($filters[0]["conditions"]) && !empty($filters[0]["conditions"])) {
                $this->collection["where"] = $this->get_transformed_filters($filters);
            }

            return [
                'columns' => $table_columns,
                'rows' => $this->get_records(),
            ];
        }

        public function get_records()
        {
            $table_instance = $this->tablesome_db->create_table_instance($this->collection['table_id']);
            $number = isset($this->collection['pagination']) && $this->collection['pagination'] == 1 ? $this->collection["display"]['numOfRecordsPerPage'] : 0;

            $query = array(
                'table_id' => $this->collection['table_id'],
                'table_name' => $table_instance->name,
                'number' => $number,
                'orderby' => array('rank_order', 'id'),
                'order' => 'asc',
            );

            // To filter tablesome records using Actions
            $query = apply_filters("tablesome_records_query", $query);

            $args = $query;
            $args['collection'] = $this->collection;
            $args['table_meta'] = $this->collection['table_meta'];
            $args['where'] = $this->collection['where'];
            $rows = $this->tablesome_db->get_rows($args);

            // error_log(' rows : ' . print_r($rows, true));

            return $rows;
        }

        public function get_transformed_filters($given_filters)
        {
            $filters = [];
            foreach ($given_filters as $given_group) {
                // error_log('$filters_source : ' . print_r($filters_source, true));
                // error_log('$group : ' . print_r($given_group, true));
                foreach ($given_group['conditions'] as $given_condition) {
                    $condition = [];
                    $condition['operand_1'] = $this->get_column_id($given_condition['operand_1']['id']);
                    $condition['operand_1_date_format'] = $this->get_date_format($condition['operand_1']);
                    $condition['data_type'] = $this->data_type_conversion($given_condition);
                    $condition['operator'] = $given_condition['operator'];
                    $condition['operand_2'] = $this->get_value($given_condition);
                    $condition['operand_2_meta'] = $this->get_operand_2_meta($given_condition);
                    array_push($filters, $condition);
                }
            }

            error_log('$filters : ' . print_r($filters, true));
            return $filters;
        }

        private function get_column_id($column)
        {
            $column_id = $column;
            $column_id_prefix = "column_";
            $table_hidden_columns = ['author_id', 'updated_by', 'created_at', 'updated_at'];

            if (!in_array($column, $table_hidden_columns)) {
                $column_id = $column_id_prefix . $column;
            }

            return $column_id;
        }

        private function get_date_format($column)
        {
            return $column == "created_at" || $column == "updated_at" ? "mysql_date" : "js_timestamp";
        }

        private function data_type_conversion($condition)
        {
            if ($condition['operand_1']['data_type'] == 'date') {
                return 'datetime';
            } elseif ($condition['operand_1']['data_type'] == 'string') {
                return 'text';
            }

            return $condition['operand_1']['data_type'];
        }

        private function get_value($condition)
        {
            $operator = $condition["operator"];
            $operand_2 = $condition["operand_2"];
            $value = $condition["operand_2"]["value"];

            if ($value == "current_user") {
                $value = get_current_user_id();
            }
            error_log(' value : ' . print_r($value, true));

            return $value;

        }

        private function get_operand_2_meta($condition)
        {

            $operand_2 = $condition["operand_2"]["value"];
            $meta = $condition["operand_2"]["meta"];
            $meta_count = count($meta);
            $meta_value = "";
            if ($meta_count) {
                foreach ($meta as $metaKey => $metaItem) {
                    if ($operand_2 == $metaItem["id"]) {
                        $meta_value = $metaItem["value"];
                    }
                }
            }

            return $meta_value;
        }

    }
}
