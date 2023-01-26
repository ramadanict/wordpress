<?php

namespace Tablesome\Components;

if (!class_exists('\Tablesome\Components\Export')) {
    class Export
    {
        public function render()
        {
            echo '<div id="tablesome-export-page"></div>';
        }

        public function get_export_table_props($params)
        {
            $table_id = $params["table_id"];
            // error_log(' table_id : ' . print_r($table_id, true));

            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $table_instance = $tablesome_db->create_table_instance($table_id);
            $table_meta = get_tablesome_data($table_id);
            // init cell types
            new \Tablesome\Components\Table\Controller();

            $columns = isset($table_meta['columns']) && !empty($table_meta['columns']) ? $table_meta['columns'] : [];
            $records = [];

            $args = array(
                'table_id' => $table_id,
                'table_name' => $table_instance->name,
                'number' => 0,
                'orderby' => array('rank_order', 'id'),
                'order' => 'asc',
            );
            $args['table_meta'] = $table_meta;
            $args['collection'] = [];

            $records = $tablesome_db->get_rows($args);

            // $query = $tablesome_db->query($args);
            // $records = isset($query->items) ? $query->items : [];
            // $records = $tablesome_db->get_formatted_rows($records, $table_meta, []);

            return [
                "id" => $table_id,
                "title" => get_the_title($table_id),
                "columns" => $columns,
                "records" => $records,
            ];
        }
    }
}
