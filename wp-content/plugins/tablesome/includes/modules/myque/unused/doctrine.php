<?php

namespace Tablesome\Includes\Modules\Myque;

// Query Builder for MySQL
if (!class_exists('\Tablesome\Includes\Modules\Myque\Doctrine')) {
    class Doctrine
    {

        public function __construct(){}

        
        public function get_rows($args) {
            global $wpdb;
            $table_name = $wpdb->prefix . $args['table_name'];
            $queryBuilder = $this->getQueryBuilder();

            $query = $queryBuilder->select('*')
            ->from($table_name);

            $ii = 0;
            $parameters = [];
            foreach ($args['where'] as $field => $singleCondition) {
                $operand1 = $singleCondition['operand1'];
                $compare = $singleCondition['compare'];
                // $compare = '>';
                $parameterName = 'operand2_' . $ii;
               
                $query->andWhere($table_name . '.' .$operand1. ' ' . $compare . ' :' . $parameterName);
                $parameters[$parameterName] = $singleCondition['operand2'];
                $ii++;
            }

            if (count($parameters)) {
                $query->setParameters($parameters);
            }

            error_log('get doctrine sql: ' . $query->getSql());
        //    error_log('$query : ' . print_r($query, true));
            $results = $query->execute()->fetchAll();
            error_log('doctrine_get_rows() results_count: ' . count($results)); 

            // error_log('doctrine_get_rows() results: ' . print_r($results, true));    
            
            $results = array_map(function ($value) {
                return (object) $value;
            }, $results);

            return $results;
        }

        public function getQueryBuilder() {
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


    }
}
