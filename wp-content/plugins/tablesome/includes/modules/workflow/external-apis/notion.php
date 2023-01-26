<?php

namespace Tablesome\Includes\Modules\Workflow\External_Apis;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\External_Apis\Notion')) {
    class Notion {
        public $api_key = '';
        public $api_status = false;
        public $version = '2022-02-22';

        public $api_key_option_name = 'tablesome_notion_api_key';
        public $api_key_status_option_name = 'tablesome_notion_api_status';
        public $api_key_status_message_option_name = 'tablesome_notion_api_status_message';

        public function __construct() {
            $api_key = get_option($this->api_key_option_name);
            $api_status = get_option($this->api_key_status_option_name);

            $this->api_key = $api_key ? $api_key : $this->api_key;
            $this->api_status = $api_status ? $api_status : $this->api_status;
        }

        public function get_api_headers() {
            return array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Notion-Version' => $this->version,
            );
        }

        public function ping() {
            $args = array(
                'headers' => $this->get_api_headers(),
                'body' => json_encode(array(
                    'filter' => array(
                        'value' => 'database',
                        'property' => 'object',
                    ),
                )),
            );
            $url = 'https://api.notion.com/v1/search';
            $response = wp_remote_post($url, $args);
            $data = json_decode(wp_remote_retrieve_body($response), true);

            if ($response['response']['code'] != 200) {
                $message = isset($data['message']) ? $data['message'] : 'The API key is invalid.';

                update_option($this->api_key_status_option_name, false);
                update_option($this->api_key_status_message_option_name, $message);

                return array(
                    'status' => false,
                    'message' => $message,
                );
            }
            update_option($this->api_key_status_option_name, true);
            update_option($this->api_key_status_message_option_name, 'Connected');

            return array(
                'status' => true,
                'message' => 'Connected',
            );
        }

        public function get_all_databases($args = array()) {

            if (!$this->api_status || empty($this->api_key)) {
                return array();
            }

            $excluded_props = isset($args['excluded_props']) && !empty($args['excluded_props']) ? explode(',', $args['excluded_props']) : [];

            $payload = array(
                'headers' => $this->get_api_headers(),
                'body' => json_encode(array(
                    'filter' => array(
                        'value' => 'database',
                        'property' => 'object',
                    ),
                )),
            );
            $url = 'https://api.notion.com/v1/search';
            $response = wp_remote_post($url, $payload);

            if (is_wp_error($response) || $response['response']['code'] != 200) {
                return [];
            }
            $data = json_decode(wp_remote_retrieve_body($response), true);
            $databases = array();
            foreach ($data['results'] as $result) {

                $database = array(
                    'id' => $result['id'],
                    'label' => isset($result['title'][0]) ? $result['title'][0]['plain_text'] : 'Untitled',
                    'integration_type' => 'notion',

                    // TODO: Should remove this below prop after developing the workflow api data
                    'fields' => $this->get_formatted_fieds($result),
                    'url' => $result['url'],
                    'archived' => $result['archived'],
                );

                $databases[] = (array_filter($database, function ($key) use ($excluded_props) {
                    return !in_array($key, $excluded_props);
                }, ARRAY_FILTER_USE_KEY));

            }
            return $databases;
        }

        public function get_database_by_id($database_id) {
            if (empty($database_id) || !$this->api_status || empty($this->api_key)) {
                return false;
            }

            $payload = array(
                'headers' => $this->get_api_headers(),
            );
            $url = "https://api.notion.com/v1/databases/{$database_id}";
            $response = wp_remote_get($url, $payload);
            if (is_wp_error($response) || $response['response']['code'] != 200) {
                return [
                    'status' => 'failed',
                    'message' => $response->get_error_message(),
                ];
            }
            $data = json_decode(wp_remote_retrieve_body($response), true);

            return $data;
        }

        public function add_record_in_database($database_id, $properties) {
            $payload = [
                'headers' => $this->get_api_headers(),
                'body' => json_encode(
                    [
                        'parent' => [
                            'type' => 'database_id',
                            'database_id' => $database_id,
                        ],
                        'properties' => $properties,
                    ]
                ),
            ];

            $url = 'https://api.notion.com/v1/pages';
            $response = wp_remote_post($url, $payload);

            if (is_wp_error($response) || $response['response']['code'] != 200) {
                return [];
            }

            $response_data = json_decode(wp_remote_retrieve_body($response), true);
            return $response_data;
        }

        public function get_formatted_fieds($database) {
            if (empty($database)) {
                return [];
            }

            $unsupported_types = array(
                'formula', 'relation', 'rollup', 'files', 'created_time', 'created_by', 'last_edited_time', 'last_edited_by', 'people',
            );

            $properties = isset($database['properties']) ? $database['properties'] : array();

            $fields = [];

            if (!empty($properties)) {
                foreach ($properties as $property) {

                    if (in_array($property['type'], $unsupported_types)) {
                        continue;
                    }

                    $fields[] = array(
                        'id' => $property['id'],
                        'label' => $property['name'],
                        'field_type' => $property['type'],
                    );
                }
            }

            return $fields;
        }
    }
}