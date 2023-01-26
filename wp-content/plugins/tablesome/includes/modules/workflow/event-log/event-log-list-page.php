<?php

namespace Tablesome\Includes\Modules\Workflow\Event_Log;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Event_Log\Event_Log_List_Page')) {
    class Event_Log_List_Page
    {
        public function __construct()
        {

        }

        public function add_menu()
        {
            $label = __("Action Log", "tablesome");

            add_submenu_page(
                'edit.php?post_type=' . TABLESOME_CPT,
                $label,
                $label,
                'manage_options',
                'tablesome-action-log',
                array($this, 'action_log_summary_page')
            );
        }

        public function action_log_summary_page()
        {
            $heading = __('Action Log Summary', 'tablesome');
            $table_content = '';

            $lists = new \Tablesome\Includes\Modules\Workflow\Event_Log\Event_Log_List();
            $lists->prepare_items();

            ob_start();

            // $lists->search_box('Search', 'search');
            $lists->display();
            $table_content = ob_get_contents();
            ob_end_clean();
            $content = '<div class="wrap">';
            $content .= '<h1 class="wp-heading-inline">' . $heading . '</h1>';
            $content .= $table_content;
            $content .= '</div>';

            echo $content;
        }
    }
}
