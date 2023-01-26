<?php

namespace Tablesome\Includes\Modules\Myque;

// Query Builder for MySQL
if (!class_exists('\Tablesome\Includes\Modules\Myque\Myque_Exp')) {
    class Myque_Exp
    {

        public function __construct(){}
        public function sample()
        {
            $cols_old = array(
                [
                    'name'    => 'id',
                    'type'    => 'mediumint',
                    'length'  => 100,
                ],
                [
                    'name'    => 'name',
                    'type'    => 'varchar',
                    'length'  => 100,
                ],
                [
                    'name'    => 'email',
                    'type'    => 'varchar',
                    'length'  => 100,
                ],
                [
                    'name'    => 'phone',
                    'type'    => 'varchar',
                    'length'  => 100,
                ],
                [
                    'name'    => 'address',
                    'type'    => 'varchar',
                    'length'  => 100,
                ],
                [
                    'name'    => 'created_at',
                    'type'    => 'timestamp',
                    'length'  => 100,
                ],
                [
                    'name'    => 'updated_at',
                    'type'    => 'timestamp',
                    'length'  => 100,
                ],

            );

            $new = array(
                [
                    'name' => 'date_received',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'product_name',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'sub_product',
                    'type' => 'varchar',
                    'length' => 100,

                ],
                [
                    'name' => 'issue',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'sub_issue',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'complaint_narratives',
                    'type' => 'text',
                ],
                [
                    'name'  => 'company_public_response',
                    'type'  => 'text',
                ],

                [
                    'name' => 'company',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'state_name',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'zip_code',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'tags',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'consumer_consent_provided',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'submitted_via',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'date_sent_to_company',
                    'type' => 'varchar',
                    'length' => 100,

                ],
                [
                    'name' => 'company_response_to_consumer',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'timely_response',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'consumer_disputed',
                    'type' => 'varchar',
                    'length' => 100,
                ],
                [
                    'name' => 'complaint_id',
                    'type' => 'mediumint',
                ],
            );

            return $new;
        }

        public function doctrine() {

            $this->doctrine_get_rows();

            // global $wpdb;
            // error_log('$wpdb : ' . print_r($wpdb, true));
            // $queryBuilder = $wpdb->createQueryBuilder();
            $table_name = "wp_customer_complaints";

            $connectionParams = [
                'dbname' => DB_NAME,
                'user' => DB_USER,
                'password' => DB_PASSWORD,
                'host' => DB_HOST,
                // 'port' => '10083',
                'driver' => 'mysqli', // 'mysqli' Works without port, 'pdo_mysql' does not
            ];
            $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

            $queryBuilder = $conn->createQueryBuilder();
            error_log('DB_HOST; : ' . DB_HOST);
            // error_log('$GLOBALS : ' . print_r($GLOBALS, true));

            // echo "<pre>";
            // print_r($GLOBALS);
            // echo "</pre>";

            // $query = $queryBuilder->select('complaint_id', 'consumer_disputed')
            //     ->from($table_name)->where('submitted_via = ?')
            //     ->setParameter(0, 'Web')->setMaxResults(100);

            $query = $queryBuilder->select('complaint_id', 'consumer_disputed')
                ->from($table_name)->where('submitted_via = ?')
                ->setParameter(0, 'Web')->where('product_name = ?')
                ->setParameter(0, 'Mortgage');

             $results = $query->execute()->fetchAll();

             error_log('doctrine() results_count: ' . count($results)); 
            // error_log('doctrine() results: ' . print_r($results, true));        
        }

        public function doctrine_get_rows() {
            $table_name = "wp_customer_complaints";
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
                'where' => [
                    array(
                        'operand1' => 'submitted_via',
                        'operand2' => 'Web',
                        'compare' => '=',
                    ),
                    array(
                        'operand1' => 'product_name',
                        'operand2' => 'Mortgage',
                        'compare' => '=',
                    )
                ]
            );

            $queryBuilder = $this->getQueryBuilder();

            $query = $queryBuilder->select('complaint_id', 'consumer_disputed')
            ->from($table_name);

            $ii = 0;
            $parameters = [];
            foreach ($args['where'] as $field => $singleCondition) {
                $operand1 = $singleCondition['operand1'];
                $parameterName = 'operand2_' . $ii;
               
                $query->andWhere($table_name . '.' .$operand1.' = :' . $parameterName);
                $parameters[$parameterName] = $singleCondition['operand2'];
                $ii++;
            }

            if (count($parameters)) {
                $query->setParameters($parameters);
            }

        //    error_log('$query : ' . print_r($query, true));
            $results = $query->execute()->fetchAll();
            error_log('doctrine_get_rows() results_count: ' . count($results)); 

            // error_log('doctrine_get_rows() results: ' . print_r($results, true));       
        }

        public function getQueryBuilder(){
            $table_name = "wp_customer_complaints";

            $connectionParams = [
                'dbname' => DB_NAME,
                'user' => DB_USER,
                'password' => DB_PASSWORD,
                'host' => DB_HOST,
                // 'port' => '10083',
                'driver' => 'mysqli', // 'mysqli' Works without port, 'pdo_mysql' does not
            ];
            $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

            $queryBuilder = $conn->createQueryBuilder('complaints');

            return $queryBuilder;
        }



        public function doctrine_wrapper()
        {
            // global $wpdb;
            // global $result_of_query;
            // $table_name = $wpdb->prefix . "customer_complaints";
            $starttime = microtime(true);
            $start_memory = memory_get_peak_usage();
            
            $this->doctrine();

            $endtime = microtime(true);
            $end_memory = memory_get_peak_usage();
            $duration = $this->get_duration($endtime, $starttime); //calculates total time taken
            $memory_used  = $this->convert($end_memory - $start_memory); // in KB
            error_log('$memory_used; : ' . $memory_used);
            error_log('$end_memory; : ' .  $this->convert($end_memory));
            error_log( 'Doctrine - duration : ' . $duration);
            // error_log('count($result); : ' . count($result));

            // print_r($result);
            // error_log('$result : ' . print_r($result, true));
        }

        public function create_table_old()
        {
            global $wpdb;

            $charset_collate = $wpdb->get_charset_collate();
            $table_name = $wpdb->prefix . "liveshoutbox";
            $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            name tinytext NOT NULL,
            text text NOT NULL,
            url varchar(55) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";

            error_log($sql);
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
            
        }



        public function save_column_value()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . "customer_complaints";
            // $json_as_php_array = array(
            //     "key2" =>  "I am ID2",
            //     "key3" => "I am ID3"
            // );

            // $json_value = json_encode($json_as_php_array);

            // error_log('$json_value : ' . $json_value);

            // $sql = 'UPDATE  $table_name set zlistjson = ' . $json_value . ';';

            for ($ii = 454526; $ii < 555526; $ii++) {
                $json_as_php_array = array(
                    "key2" =>  "key2_" . rand(10, 100),
                    "key3" => "key3_" . rand(10, 100)
                );
                $json_value = json_encode($json_as_php_array);
                $sql = "UPDATE  $table_name set zlistjson = '$json_value' WHERE complaint_id = $ii;";
                // $sql = "UPDATE  $table_name set my_custom_posts_column = 199;";
                $wpdb->query($sql);
            }
        }

        public function add_new_column()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . "customer_complaints";
            $sql = "ALTER TABLE $table_name ADD zsometext varchar(55) DEFAULT NULL;";
            $wpdb->query("ALTER TABLE  $table_name ADD zlistjson json");

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            // dbDelta($sql);
        }


        public function move_column_to_meta_table()
        {
            global $wpdb;

            $charset_collate = $wpdb->get_charset_collate();
            $table_name = $wpdb->prefix . "customer_complaints_meta";
            $table_name = $wpdb->prefix . "customer_complaints";
            $meta_table_name = $wpdb->prefix . "customer_complaints_meta";
            $sql = " Select A.complaint_id ,B.meta_key ,B.meta_value From  $table_name A Cross Apply OpenJSON( (Select A.* For JSON Path,Without_Array_Wrapper ) ) B
            Where [key] not in ($table_name)";

            error_log($sql);
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }
        public function create_meta_table()
        {
            global $wpdb;

            $charset_collate = $wpdb->get_charset_collate();
            $table_name = $wpdb->prefix . "customer_complaints_meta";
            $sql = "CREATE TABLE $table_name (
            meta_id mediumint(10) NOT NULL AUTO_INCREMENT,
            complaint_id mediumint(10) NOT NULL DEFAULT '0',
            meta_key tinytext NOT NULL,
            meta_value varchar(100) DEFAULT '' NOT NULL,
            PRIMARY KEY  (meta_id)
            ) $charset_collate;";

            error_log($sql);
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        public function load_test($size = 10)
        {
            for ($ii = 0; $ii < $size; $ii++) {
                error_log('call_count $ii : ' . $ii);
                $this->get_rows();
            }
        }
        public function get_rows()
        {
            global $wpdb;
            global $result_of_query;

            // if (isset($result_of_query)) {
            //     return;
            // }
            $result_of_query = true;
            $table_name = $wpdb->prefix . "customer_complaints";

            $simple_query = "SELECT * FROM $table_name LIMIT 100";
            $this->get_rows_wrapper($simple_query, 'simple_query');
            $single_filter_query = "SELECT * FROM $table_name WHERE product_name = 'Mortgage' LIMIT 100";
            $this->get_rows_wrapper($single_filter_query, 'single_filter_query');
            $three_filter_query = "SELECT * FROM $table_name WHERE product_name = 'Mortgage' and submitted_via = 'fax' LIMIT 100";
            $this->get_rows_wrapper($three_filter_query, 'three_filter_query');
            $three_filter_query = "SELECT * FROM $table_name WHERE product_name = 'Mortgage' and submitted_via = 'fax' and company_response_to_consumer = '	Closed with explanation' LIMIT 100";
            $this->get_rows_wrapper($three_filter_query, 'three_filter_query');
            $json_filter_query = "SELECT * FROM $table_name WHERE JSON_EXTRACT(zlistjson, '$.key2') = 'key2_78' LIMIT 100";
            $this->get_rows_wrapper($json_filter_query, 'json_filter_query');
            $two_json_filter_query = "SELECT * FROM $table_name WHERE JSON_EXTRACT(zlistjson, '$.key2') = 'key2_78' and JSON_EXTRACT(zlistjson, '$.key3') = 'key3_25' LIMIT 100";
            $this->get_rows_wrapper($two_json_filter_query, 'two_json_filter_query');
        }


        public function convert($size)
        {

            if ($size == 0) {
                return 0;
            }

            $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
            return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
        }

        public function get_rows_wrapper($query, $label = 'simple_query')
        {
            global $wpdb;
            global $result_of_query;
            $table_name = $wpdb->prefix . "customer_complaints";
            $starttime = microtime(true);
            $start_memory = memory_get_peak_usage();
            $result = $wpdb->get_results($query);
            $endtime = microtime(true);
            $end_memory = memory_get_peak_usage();
            $duration = $this->get_duration($endtime, $starttime); //calculates total time taken
            $memory_used  = $this->convert($end_memory - $start_memory); // in KB
            error_log('$memory_used; : ' . $memory_used);
            error_log('$end_memory; : ' .  $this->convert($end_memory));
            error_log($label . ' - duration : ' . $duration);
            error_log('count($result); : ' . count($result));

            // print_r($result);
            // error_log('$result : ' . print_r($result, true));
        }

        public function get_duration($endtime, $starttime)
        {
            $duration = $endtime - $starttime;
            $hours = (int)($duration / 60 / 60);
            $minutes = (int)($duration / 60) - $hours * 60;
            $seconds = (float)$duration - $hours * 60 * 60 - $minutes * 60;

            return $seconds;
        }


        public function create_table()
        {
            global $wpdb;
            $table_name = $wpdb->prefix . "customer_complaints";
            $cols_obj = $this->sample();
            $sql_string = $this->get_sql_from_cols_obj($cols_obj);
            error_log($sql_string);
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name ($sql_string) $charset_collate;";



            error_log($sql);
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        public function get_sql_from_cols_obj($cols_obj)
        {
            global $wpdb;


            $sql_string = '';
            // $sql_string .= "id mediumint(9) NOT NULL AUTO_INCREMENT,";
            foreach ($cols_obj as $key => $col) {
                $sql_string .= " " . $col['name'] . " " . $col['type'];

                if (isset($col['length'])) {
                    $sql_string .= "(" . $col['length'] . ")";
                }

                if ($col['type'] == 'datetime') {
                    $sql_string .= " DEFAULT '0000-00-00' ";
                } else if ($col['type'] == 'varchar') {
                    $sql_string .= " DEFAULT '' ";
                } else if ($col['type'] == 'mediumint') {
                    $sql_string .= " DEFAULT 0 ";
                }

                $sql_string .=  " NOT NULL,";
            }

            $sql_string .= "PRIMARY KEY  (complaint_id)";
            // $sql_string .= ") $charset_collate;";

            return $sql_string;
        }
    }
}
