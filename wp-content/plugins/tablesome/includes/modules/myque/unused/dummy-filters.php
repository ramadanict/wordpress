<?php

namespace Tablesome\Includes\Modules\TablesomeDB;

if (!class_exists('\Tablesome\Includes\Modules\TablesomeDB\Dummy_Filters')) {
    class Dummy_Filters
    {
        public function get_dummy_filters(){
            return [
                // array(
                //     'operand1' => 'column_3',
                //     'operand2' => 'qwqwq@gmail.com',
                //     'operator' => '=',
                //     'format' => 'text',
                // ),
                //   array(
                //     'operand1' => 'id',
                //     'operand2' => 8,
                //     'operator' => '>',
                // ),
                // array(
                //     'operand1' => 'column_6',
                //     'operand2' => '1429048385',
                //     'operator' => '>',
                //     'format' => 'datetime',
                // ),
                //  array(
                //     'operand1' => 'column_7',
                //     'operand2' => '2010-12-01 21:24:09',
                //     'operator' => '>',
                //     'format' => 'datetime',
                // ),
                // array(
                //     'operand1' => 'column_7',
                //     'operand2' => '2020-12-14 21:24:09',
                //     'operator' => '<',
                //     'format' => 'datetime',
                // ),
                array(
                    'operand1' => 'column_8',
                    'operand2' => 1980,
                    'operator' => '<',
                    'format' => 'number',
                ),
                array(
                    'operand1' => 'column_14_meta',
                    'operand2' => 1980,
                    'operator' => '<',
                    'format' => 'number',
                ),
                array(
                    'operand1' => 'column_4',
                    'operand2' => '12',
                    'operator' => 'is_not_empty',
                    // 'operator' => 'contains',
                    'format' => 'text',
                ),
                // array(
                //     'operand1' => 'column_14_meta',
                //     'operand2' => '.com',
                //     'operator' => 'contains',
                //     // 'operator' => '=',
                //     'format' => 'json',
                // ),
                // array(
                //     'operand1' => 'created_at',
                //     'operand2' => '2022-11-23 05:54:09',
                //     'operator' => '>',
                //     'format' => 'datetime',
                // ),
                // array(
                //     'operand1' => 'created_at',
                //     'operand2' => '2022-11-23 06:23:18',
                //     'operator' => '<',
                //     'format' => 'datetime',
                // ),
                // array(
                //     'operand1' => 'product_name',
                //     'operand2' => 'Mortgage',
                //     'operator' => '=',
                // )
            ];
        }
    }
}