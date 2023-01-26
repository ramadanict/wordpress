<?php

namespace Tablesome\Includes\Modules\TablesomeDB;

if (!class_exists('\Tablesome\Includes\Modules\TablesomeDB\Transform_Filters_For_Myque')) {
    class Transform_Filters_For_Myque
    {
        public function get_transformed_filters($filters_source, $action_data_version = 2 ) {
            $transformed_filters = [];
            foreach ($filters_source as $group) {
                // error_log('$filters_source : ' . print_r($filters_source, true));
                error_log('$group : ' . print_r($group, true));
                foreach ($group['conditions'] as $condition) {
                    $t_condition = [];
                    $t_condition['operand_1'] = $condition['operand_1']['id'];
                    $t_condition['data_type'] = $this->data_type_conversion($condition);
                    $t_condition['operator'] = $condition['operator'];
                    $t_condition['operand_2'] = $condition['operand_2']['value'];
                    array_push($transformed_filters, $t_condition);
                }
            }
            return $transformed_filters;
        }

        private function data_type_conversion($condition){
            if($condition['operand_1']['data_type'] == 'date') {
                return 'datetime';
            } 
            
            return $condition['operand_1']['data_type'];
           
        }

       

    } // END CLASS
}