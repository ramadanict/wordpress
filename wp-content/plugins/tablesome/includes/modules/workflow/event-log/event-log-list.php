<?php

namespace Tablesome\Includes\Modules\Workflow\Event_Log;

use \Tablesome\Includes\Modules\Workflow\Event_Log\Event_Log;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Event_Log\Event_Log_List')) {
    class Event_Log_List extends \WP_List_Table {
        public $per_page = 20;
        public $current_page = 1;
        public $order = 'desc';
        public $orderby = 'id';

        public function prepare_items() {
            $this->orderby = (isset($_GET['orderby']) && !empty($_GET['orderby'])) ? $_GET['orderby'] : $this->orderby;
            $this->order = (isset($_GET['order']) && !empty($_GET['order'])) ? $_GET['order'] : $this->order;
            $this->current_page = (isset($_GET['paged'])) && !empty($_GET['paged']) ? $_GET['paged'] : 0;

            $columns = $this->get_columns();
            $hidden = $this->get_hidden_columns();
            $sortable = $this->get_sortable_columns();

            $data = $this->table_data();
            if (!empty($this->orderby)) {
                usort($data, array(&$this, 'sort_data'));
            }

            $this->set_pagination_args(array(
                'total_items' => $this->get_total_events_count(),
                'per_page' => $this->per_page,
            ));

            $this->_column_headers = array($columns, $hidden, $sortable);
            $this->items = $data;
        }

        public function get_columns() {
            $columns = array(
                // 'id' => 'ID',
                'integration_type' => __('Integration Type', 'tablesome'),
                'table' => __('Table', 'tablesome'),
                'trigger' => __('Trigger', 'tablesome'),
                'action' => __('Action', 'tablesome'),
                'content' => __('Message', 'tablesome'),
                'status' => __('Status', 'tablesome'),
                'user' => __('User', 'tablesome'),
                'created_at' => __('Completion Date', 'tablesome'),
            );

            return $columns;
        }

        public function get_hidden_columns() {
            return array();
        }

        public function get_sortable_columns() {
            return array(
                'integration_type' => array('integration_type', false),
                'table' => array('table', false),
                'trigger' => array('trigger', false),
                'action' => array('action', false),
                'status' => array('status', false),
                'user' => array('user', false),
                'created_at' => array('created_at', false),
            );
        }

        /**
         * Get the table data
         *
         * @return Array
         */
        private function table_data() {
            $data = array();
            $offset = 0;

            if (!empty($this->current_page)) {
                $offset = ($this->current_page - 1) * $this->per_page;
            }

            $query = new \Tablesome_Event_Log_Table_Query(array(
                'number' => $this->per_page,
                'orderby' => array($this->orderby),
                'order' => $this->order,
                'offset' => $offset,
            ));

            $items = isset($query->items) ? $query->items : [];
            $data = [];

            foreach ($items as $item) {

                $labels = $this->get_workflow_labels($item);

                $action_label = __('Unknown Action', 'tablesome');
                if (!empty($labels['action_label'])) {
                    $action_label = $labels['action_label'] . ' (<b>' . $labels['action_integration_label'] . '</b>)';
                }

                $data[] = array(
                    'id' => $item->ID,
                    'integration_type' => !empty($labels['trigger_integration_label']) ? $labels['trigger_integration_label'] : __('Unknown Integration', 'tablesome'),
                    'table' => $this->get_table_name_by_id($item->table_id),
                    'trigger' => !empty($labels['trigger_label']) ? $labels['trigger_label'] : __('Unknown Trigger', 'tablesome'),
                    'action' => $action_label,
                    'content' => $item->content,
                    'status' => $this->get_status($item->status),
                    'user' => $this->get_user_name_by_id($item->user_id),
                    'created_at' => $item->created_at,
                );
            }

            return $data;
        }

        public function column_default($item, $column_name) {
            switch ($column_name) {
            case 'id':
            case 'integration_type':
            case 'table':
            case 'trigger':
            case 'action':
            case 'content':
            case 'status':
            case 'user':
            case 'created_at':
                return $item[$column_name];

            default:
                return print_r($item, true);
            }
        }

        private function sort_data($a, $b) {
            $result = strcmp($a[$this->orderby], $b[$this->orderby]);
            if ($this->order === 'asc') {
                return $result;
            }
            return -$result;
        }

        private function get_user_name_by_id($user_id) {
            if (empty($user_id)) {
                return __('Anonymous', 'tablesome');
            }
            $user = get_user_by('id', $user_id);
            $display_name = isset($user->display_name) ? $user->display_name : '';
            if (empty($user)) {
                return __('Anonymous', 'tablesome');
            }
            return $display_name;
        }

        private function get_status($status) {
            return $status == 1 ? __('Success', 'tablesome') : __('Failed', 'tablesome');
        }

        private function get_table_name_by_id($table_id) {
            $name = 'Unknown Table';
            $url = '#';
            if (empty($table_id)) {
                return $name;
            }

            $table = get_post($table_id);

            if (empty($table)) {
                return $name;
            }

            $name = $table->post_title;
            $url = get_tablesome_table_edit_url($table_id);

            $html = '';
            $html .= '<a href="' . $url . '" target="_blank" title="' . $name . '">';
            $html .= $name;
            $html .= '</a>';

            return $html;
        }
        private function get_total_events_count() {
            $count = Event_Log::get_instance()->get_total_events_count();
            return $count;
        }

        public function get_workflow_labels($item) {
            $workflow_instance = tablesome_workflow_manager();
            return [
                'trigger_label' => $workflow_instance->get_trigger_prop_value_by_id($item->trigger_id, 'trigger_label'),
                'trigger_integration_label' => $workflow_instance->get_trigger_prop_value_by_id($item->trigger_id, 'integration_label'),
                'action_label' => $workflow_instance->get_action_prop_value_by_id($item->action_id, 'label'),
                'action_integration_label' => $workflow_instance->get_action_integration_label_by_id($item->action_id),
            ];
        }
    }
}