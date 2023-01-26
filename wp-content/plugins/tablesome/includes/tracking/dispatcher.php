<?php

namespace Tablesome\Includes\Tracking;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\Tablesome\Includes\Tracking\Dispatcher')) {
    class Dispatcher
    {
        public $errors = array();

        public $api_url = 'https://api2.amplitude.com/2/httpapi';

        // protected $api_key = '1606677b0a274d70003d5b8640c83d58'; /** Test API Key */

        protected $api_key = '1beb3c4f76b14e9b69c131083ea92c58'; /** Prod API Key */

        public function __construct($data)
        {
            $this->data = $data;

            $this->set_defaults();
        }

        private function set_defaults()
        {
            $this->data['api_key'] = $this->api_key;
        }

        protected function get_headers()
        {
            return array(
                'Content-Type: application/json',
                'Accept: */*',
            );
        }

        public function send()
        {
            if (empty($this->data) || count($this->data) == 0) {
                return;
            }

            $headers = $this->get_headers();
            $response = wp_remote_post($this->api_url, array(
                'method' => 'POST',
                'headers' => $headers,
                'body' => json_encode($this->data),
            ));

            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                return false;
            }

            $response = json_decode(wp_remote_retrieve_body($response), true);
            if (!is_array($response)) {
                return false;
            }
            return $response;
        }

    }
}
