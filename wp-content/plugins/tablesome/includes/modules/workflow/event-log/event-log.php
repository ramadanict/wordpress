<?php

namespace Tablesome\Includes\Modules\Workflow\Event_Log;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Event_Log\Event_Log')) {
    class Event_Log
    {

        public static $instance = null;

        private static $current_user_id = 0;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
                self::$instance->load_hooks();
                self::$instance->get_db_table_instance();
            }
            return self::$instance;
        }

        public function load_hooks()
        {
            self::$current_user_id = get_current_user_id();
            add_action('tablesome_automation/add_log', array($this, 'add_log'), 10, 2);
        }

        public function get_db_table_instance()
        {
            $table = new \Tablesome_Event_Log();
            if (!$table->exists()) {
                $table->install();
            }
            return $table;
        }

        public function get_total_events_count()
        {
            $table = $this->get_db_table_instance();
            return $table->count();
        }

        public function add_log($collection)
        {
            $log_data = $this->get_log_data($collection);
            $query = new \Tablesome_Event_Log_Table_Query();
            $result = $query->add_item($log_data);
            return $result;
        }

        private function get_log_data($collection)
        {
            $action_id = isset($collection['args']['action_meta']['action_id']) ? $collection['args']['action_meta']['action_id'] : 0;
            $table_id = isset($collection['args']['table']->ID) ? $collection['args']['table']->ID : 0;
            $integration = isset($collection['args']['trigger_meta']['integration']) ? $collection['args']['trigger_meta']['integration'] : '';
            $trigger_id = isset($collection['args']['trigger_meta']['trigger_id']) ? $collection['args']['trigger_meta']['trigger_id'] : 0;

            $data = array(
                'type' => $integration,
                'table_id' => $table_id,
                'trigger_id' => $trigger_id,
                'action_id' => $action_id,
                'content' => 'Data received successfully',
                'status' => 1,
            );

            $defaults = $this->get_default_log_data();

            $data = array_merge($data, $defaults);

            return $data;
        }

        private function get_default_log_data()
        {
            $timestamp = current_time('timestamp');
            $datetime = date('Y-m-d H:i:s', $timestamp);
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

            return array(
                'user_id' => self::$current_user_id,
                'user_ip' => get_tablesome_ip_address(),
                'user_agent' => $user_agent,
                'created_at' => $datetime,
            );
        }
    }
}
