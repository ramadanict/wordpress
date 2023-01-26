<?php

namespace Tablesome\Includes\Modules\Myque;

// Query Builder for MySQL
if (!class_exists('\Tablesome\Includes\Modules\Myque\Mysql')) {
    class Mysql
    {

        public function __construct()
        {}

        public function duplicate_column($args, $response = array())
        {
            global $wpdb;
            $table_name = $wpdb->prefix . $args['table_name'];
            $args['table_name'] = $table_name;
            $source_column = $args['source_column'];
            $target_column = $args['target_column'];

            // Create New Column
            $query = "ALTER TABLE $table_name ADD $target_column TEXT NOT NULL";
            $response['new_column_created'] = $wpdb->query($query);

            // Copy Data from Source Column to Target Column
            $query = "UPDATE $table_name SET $target_column = $source_column";
            error_log('$query : ' . $query);
            $response['copied_column_records'] = $wpdb->query($query);

            return $response;
        }

        public function get_rows($args)
        {
            // error_log(' Mysql $args : ' . print_r($args, true));
            global $wpdb;

            $table_name = $wpdb->prefix . $args['table_name'];
            $args['table_name'] = $table_name;

            $query = "SELECT * FROM $table_name";
            if (isset($args['where'])) {
                $query .= $this->convert_conditions_to_sql_string($args['where'], $table_name);
            }

            $query .= $this->orderby($args);
            $query .= " LIMIT " . $args['limit'];
            $result = $wpdb->get_results($query);

            // error_log('Mysql->get_rows $query: ' . $query);
            // error_log('Mysql->get_rows $result count: ' . count($result));
            // error_log('Mysql->get_rows $result : ' . print_r($result, true));

            return $result;
        }

        public function orderby($args)
        {
            $sql_string = " ORDER BY ";
            $orderByArgs = [];
            foreach ($args['orderby'] as $key => $value) {
                $orderByArgs[] = $args['table_name'] . "." . $value;
            }
            // Looks like wptablesome_table_287.column_2, wptablesome_table_287.column_3 ....
            $sql_string = $sql_string . implode(',', $orderByArgs);
            $sql_string .= " " . $args['order'];

            return $sql_string;
        }

        public function get_table_columns($table_name)
        {
            global $wpdb;

            $query = "SHOW COLUMNS FROM $table_name";
            $result = $wpdb->get_results($query, 'ARRAY_A');

            error_log(' result: ' . print_r($result, true));
            return $result;
        }

        public function does_column_exists($columns, $column_name)
        {
            foreach ($columns as $key => $column) {
                if ($column['Field'] == $column_name) {
                    return true;
                }
            }
            return false;
        }

        public function convert_conditions_to_sql_string($conditions, $table_name)
        {
            error_log('convert_conditions_to_sql_string: ');
            // error_log('$conditions : ' . print_r($conditions, true));

            $columns = $this->get_table_columns($table_name);
            foreach ($conditions as $key => $condition) {
                $column_name = $condition['operand_1'];

                // Remove columns which are not in Table
                if (!$this->does_column_exists($columns, $column_name)) {
                    unset($conditions[$key]);
                    continue;
                }

                if ($condition['operator'] == 'empty' || $condition['operator'] == 'is_empty') {
                    // Convert empty and not_empty to a condition_group
                    $conditions[$key] = $this->convert_condition_to_condition_group($condition, 'OR');
                    $new_condition = $condition;
                    $new_condition['operator'] = 'is_null';
                    array_push($conditions[$key]['conditions'], $new_condition);
                }

                if ($condition['operator'] == 'not_empty' || $condition['operator'] == 'is_not_empty') {
                    // Convert empty and not_empty to a condition_group
                    $conditions[$key] = $this->convert_condition_to_condition_group($condition, 'AND');
                    $new_condition = $condition;
                    $new_condition['operator'] = 'is_not_null';
                    array_push($conditions[$key]['conditions'], $new_condition);
                }
            }

            error_log('$conditions : ' . print_r($conditions, true));

            $count = count($conditions);
            $ii = 0;

            // Return if filter conditions are empty
            if ($count <= 0) {
                return "";
            }

            $sql_string = " WHERE ";

            foreach ($conditions as $key => $condition) {

                if (isset($condition['conditions'])) {
                    $sql_string .= $this->get_condition_group_sql($condition, $table_name);
                } else {
                    $sql_string .= $this->get_single_condition_sql($condition, $table_name);
                }

                if ($ii < $count - 1) {
                    $sql_string .= " AND ";
                }
                $ii++;
            }

            $sql_string = rtrim($sql_string, ' AND ');

            error_log('$sql_string : ' . $sql_string);
            return $sql_string;
        }

        public function get_condition_group_sql($condition_group, $table_name)
        {
            error_log('$condition_group : ' . print_r($condition_group, true));

            $sql_string = '';
            $jj = 0;
            $count = count($condition_group['conditions']);

            // Return if filter conditions are empty
            if ($count <= 0) {
                return "";
            }

            $sql_string .= " ( ";

            foreach ($condition_group['conditions'] as $key1 => $condition) {
                $sql_string .= $this->get_single_condition_sql($condition, $table_name);
                if ($jj < $count - 1) {
                    $sql_string .= isset($condition_group['relation']) ? " " . $condition_group['relation'] . " " : " AND ";
                }

                $jj++;
            }

            $sql_string .= " ) ";

            return $sql_string;
        }

        public function convert_condition_to_condition_group($condition, $relation)
        {
            $condition_group = [
                'conditions' => [
                    $condition,
                ],
                'relation' => $relation,
            ];

            return $condition_group;
        }

        public function get_single_condition_sql($condition, $table_name)
        {
            error_log('$condition : ' . print_r($condition, true));
            $sql_string = '';
            $condition = $this->condition_modifier($condition);
            $operand1 = $condition['operand_1'];
            $mysql_operator = $condition['mysql_operator'];
            $operand2 = $condition['operand_2'];

            error_log('$condition after : ' . print_r($condition, true));

            if ($condition['data_type'] == 'datetime') {
                // Todo: Detect operand2 format and convert to unix timestamp
                $sql_string .= $this->date_statements($condition, $table_name);
                // $sql_string .= "FROM_UNIXTIME(CAST($table_name.$operand1 / 1000 as UNSIGNED)) $mysql_operator '$operand2'";
            } else if ($condition['data_type'] == 'number') {
                $sql_string .= "CAST($table_name.$operand1 as UNSIGNED) $mysql_operator $operand2";
            } else if ($condition['data_type'] == 'json') {
                $sql_string .= "JSON_EXTRACT($table_name.$operand1, '$.value') $mysql_operator $operand2";
            } else {
                $sql_string .= "TRIM(" . $table_name . "." . $operand1 . ") " . $mysql_operator . " " . $operand2;
            }

            return $sql_string;
        }

        public function date_statements($condition, $table_name)
        {
            $sql_string = '';
            $operand1 = $condition['operand_1'];
            $mysql_operator = $condition['mysql_operator'];
            $operand2 = $condition['operand_2'];
            $operand2_meta = isset($condition['operand_2_meta']) ? $condition['operand_2_meta'] : '';
            $operand1_date_format = isset($condition['operand_1_date_format']) ? $condition['operand_1_date_format'] : 'js_timestamp';
            $operator = isset($condition['operator']) ? $condition['operator'] : '';

            if ($condition['data_type'] != 'datetime') {
                return $sql_string;
            }

            if ($operand2 == 'last_seven_days' || $operand2 == 'last_thirty_days') {
                if ($mysql_operator == 'is' || $mysql_operator == '=') {
                    $mysql_operator = 'BETWEEN';
                } else if ($mysql_operator == 'is_not' || $mysql_operator == '!=') {
                    $mysql_operator = 'NOT BETWEEN';
                }
            }

            if ($operand1_date_format == "js_timestamp") {
                $operand1_query_string = "FROM_UNIXTIME(CAST($table_name.$operand1 / 1000 as UNSIGNED))";
            } else {
                $operand1_query_string = $table_name . "." . $operand1;
            }

            // error_log('$mysql_operator : ' . $mysql_operator);
            // error_log('date_statements $condition : ' . print_r($condition, true));

            /* Special case for null and not null */
            if ($operator == 'null' || $operator == 'not_null' || $operator == 'is_null' || $operator == 'is_not_null') {
                $sql_string .= " $operand1_query_string $mysql_operator ";
                return $sql_string;
            }

            if ($operand2 == 'today') {
                $sql_string .= "DATE($operand1_query_string) $mysql_operator CURDATE()";
            } else if ($operand2 == 'tomorrow') {
                $sql_string .= "DATEDIFF($operand1_query_string, CURDATE()) $mysql_operator 1";
            } else if ($operand2 == 'yesterday') {
                $sql_string .= "DATEDIFF($operand1_query_string, CURDATE()) $mysql_operator -1";
            } else if ($operand2 == 'last_seven_days') {
                $sql_string .= "$operand1_query_string $mysql_operator  CURDATE() - INTERVAL 7 DAY AND CURDATE()";
            } else if ($operand2 == 'last_thirty_days') {
                $sql_string .= "$operand1_query_string $mysql_operator  CURDATE() - INTERVAL 30 DAY AND CURDATE()";
            } else if ($operand2 == 'current_month') {
                $sql_string .= "MONTH($operand1_query_string) $mysql_operator MONTH(CURRENT_DATE())";
            } else if ($operand2 == 'current_year') {
                $sql_string .= "YEAR($operand1_query_string) $mysql_operator YEAR(CURRENT_DATE())";
            } else if ($operand2 == 'month') {
                $sql_string .= "MONTH($operand1_query_string) $mysql_operator CAST($operand2_meta as UNSIGNED)";
            } else if ($operand2 == 'year') {
                $sql_string .= "YEAR($operand1_query_string) $mysql_operator CAST($operand2_meta as UNSIGNED)";
            } else if ($operand2 == 'exact_date') {
                $sql_string .= "DATE($operand1_query_string) $mysql_operator DATE(FROM_UNIXTIME(CAST($operand2_meta / 1000 as UNSIGNED)))";
            } else {
                $sql_string .= "DATE($operand1_query_string) $mysql_operator DATE(FROM_UNIXTIME(CAST($operand2 / 1000 as UNSIGNED)))";
            }

            return $sql_string;

        }

        public function condition_modifier($condition)
        {
            if ($this->is_general_condition($condition)) {
                $condition = $this->general_condition_modifier($condition);
                return $condition;
            }

            // Number and Datetime
            $condition = $this->number_condition_modifier($condition);

            // Text, RichText
            $condition = $this->string_condition_modifier($condition);

            error_log('$condition condition_modifier : ' . print_r($condition, true));
            return $condition;
        }

        public function is_general_condition($condition)
        {
            $general_conditions = array('empty', 'is_empty', 'not_empty', 'is_not_empty', 'is_null', 'is_not_null');
            return in_array($condition['operator'], $general_conditions);
        }

        public function general_condition_modifier($condition)
        {
            if ($condition['operator'] == 'empty' || $condition['operator'] == 'is_empty') {
                $condition['operand_2'] = "''";
                $condition['mysql_operator'] = "=";
            } else if ($condition['operator'] == 'not_empty' || $condition['operator'] == 'is_not_empty') {
                $condition['operand_2'] = "''";
                $condition['mysql_operator'] = "<>";
            } else if ($condition['operator'] == 'is_null') {
                $condition['operand_2'] = "";
                $condition['mysql_operator'] = "IS NULL";
            } else if ($condition['operator'] == 'is_not_null') {
                $condition['operand_2'] = "";
                $condition['mysql_operator'] = "IS NOT NULL";
            }

            return $condition;
        }

        public function number_condition_modifier($condition)
        {
            // Allow only number and datetime
            if ($condition['data_type'] != 'number' && $condition['data_type'] != 'datetime') {
                return $condition;
            }

            $condition['mysql_operator'] = $condition['operator'];

            return $condition;

        }

        public function string_condition_modifier($condition)
        {

            // Allow only text and json
            if ($condition['data_type'] != 'text' && $condition['data_type'] != 'json') {
                // $condition['mysql_operator'] = $condition['operator'];
                return $condition;
            }

            error_log('$condition[operator] : ' . $condition['operator']);

            if ($condition['operator'] == 'contains') {
                $condition['operand_2'] = "%" . $condition['operand_2'] . "%";
                $condition['mysql_operator'] = "LIKE";
            } else if ($condition['operator'] == 'does_not_contain') {
                $condition['operand_2'] = "%" . $condition['operand_2'] . "%";
                $condition['mysql_operator'] = "NOT LIKE";
            } else if ($condition['operator'] == 'starts_with') {
                $condition['operand_2'] = $condition['operand_2'] . "%";
                $condition['mysql_operator'] = "LIKE";
            } else if ($condition['operator'] == 'ends_with') {
                $condition['operand_2'] = "%" . $condition['operand_2'];
                $condition['mysql_operator'] = "LIKE";
            } else if ($condition['operator'] == 'is') {
                $condition['operand_2'] = $condition['operand_2'];
                $condition['mysql_operator'] = "=";
            } else if ($condition['operator'] == 'is_not') {
                $condition['operand_2'] = $condition['operand_2'];
                $condition['mysql_operator'] = "<>";
            } else {
                $condition['mysql_operator'] = $condition['operator'];
            }

            //
            // SHOULD ADD ' '
            $condition['operand_2'] = "'" . $condition['operand_2'] . "'";

            return $condition;
        }

    } // END CLASS
}
