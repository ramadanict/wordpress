<?php

namespace Tablesome\Includes\Modules\Workflow\External_Apis;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\External_Apis\GDrive')) {
    class GDrive
    {
        public $integration = 'google';

        public function get_spreadsheets()
        {
            $access_token = maybe_refresh_access_token_by_integration($this->integration);

            $url = "https://www.googleapis.com/drive/v3/files";
            $parameters = [
                'q' => "mimeType='application/vnd.google-apps.spreadsheet'",
                'alt' => 'json',
                'pageSize' => 1000,
            ];
            $url = add_query_arg($parameters, $url);

            $response = wp_remote_post($url, array(
                'method' => 'GET',
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token,
                ),
            ));
            $response_failed = (is_wp_error($response) || $response['response']['code'] != 200);
            if ($response_failed) {
                return [];
            }
            $data = json_decode(wp_remote_retrieve_body($response), true);
            return $data;
        }
    }
}
