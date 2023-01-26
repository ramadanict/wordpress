<?php

namespace Tablesome\Includes\Modules;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Delete_Old_Crud_Data')) {
    class Delete_Old_Crud_Data
    {
        public function __construct()
        {
            $this->crud = new \Tablesome\Includes\Db\CRUD();
        }

        public function delete_all_records()
        {
            $result = $this->crud->truncate_table();
            return $result;
        }

        public function delete_table()
        {
            $result = $this->crud->drop_table();
            return $result;
        }
    }
}