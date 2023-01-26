<?php

namespace Tablesome\Includes\Modules\Workflow\Integrations;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Integrations\Notion')) {
    class Notion {
        public function __construct() {
            $this->notion_api = new \Tablesome\Includes\Modules\Workflow\External_Apis\Notion();
        }

        public function get_config() {
            return array(
                'integration' => 'notion',
                'integration_label' => __('Notion', 'tablesome'),
                'is_active' => $this->notion_api->api_status,
                'is_premium' => false,
                'actions' => array(),
            );
        }

        public function add_api($api_key) {
            $this->notion_api->api_key = $api_key;
            update_option($this->notion_api->api_key_option_name, $api_key);
        }

        public function remove_api_data() {
            delete_option($this->notion_api->api_key_option_name);
            delete_option($this->notion_api->api_key_status_option_name);
            delete_option($this->notion_api->api_key_status_message_option_name);
        }

        // TODO: Should remove this below prop after developing the workflow api data
        public function get_collection() {
            $status = get_option($this->notion_api->api_key_status_option_name);
            $message = get_option($this->notion_api->api_key_status_message_option_name);

            $api_not_configured = empty($status) && empty($message);

            if ($api_not_configured) {
                $message = 'Please configure Notion API in Tablesome for this action to work.';
            }

            return array(
                'databases' => $this->notion_api->get_all_databases(),
                'api' => array(
                    'status' => !$status ? false : true,
                    'message' => $message,
                    'redirect_url' => admin_url('edit.php?post_type=' . TABLESOME_CPT . '&page=tablesome-settings#tab=integrations/notion'),
                    'share_database_url' => 'https://developers.notion.com/docs/getting-started#step-2-share-a-database-with-your-integration',
                ),
            );
        }

    }
}