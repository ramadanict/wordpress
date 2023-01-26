<?php

namespace Tablesome\Includes\Modules\Workflow\External_Apis;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\External_Apis\Mailchimp')) {
    class Mailchimp
    {
        public $api_key = '';
        public $api_status = false;
        public $api_status_message = '';

        public $api_key_option_name = 'tablesome_mailchimp_api_key';
        public $api_key_status_option_name = 'tablesome_mailchimp_api_status';
        public $api_key_status_message_option_name = 'tablesome_mailchimp_api_status_message';

        public function __construct()
        {
            $api_key = get_option($this->api_key_option_name);
            $api_status = get_option($this->api_key_status_option_name);
            $api_status_message = get_option($this->api_key_status_message_option_name);

            $this->api_key = $api_key ? $api_key : $this->api_key;
            $this->api_status = $api_status ? $api_status : $this->api_status;
            $this->api_status_message = isset($api_status_message) ? $api_status_message : $this->api_status_messag;
        }

        public function get_api_base_path()
        {
            $dc = $this->get_dc_value_from_api_key();
            return 'https://' . $dc . '.api.mailchimp.com/3.0';
        }

        /* Get Mailchimp datacenter value from the API Key
         *
         * Ref :- https://mailchimp.com/developer/marketing/docs/fundamentals/#the-basics
         * @return string
         */
        public function get_dc_value_from_api_key()
        {
            $dc = substr($this->api_key, strpos($this->api_key, '-') + 1);
            return !empty($dc) ? $dc : '';
        }

        public function get_auth_header()
        {
            return array(
                'Authorization' => 'Basic ' . base64_encode('tablesome:' . $this->api_key),
            );
        }

        public function get_default_address_fields()
        {
            /**
             * @see https://mailchimp.com/developer/marketing/docs/merge-fields/#add-merge-data-to-contacts
             */
            return array(
                array(
                    'id' => 'addr1',
                    'label' => __('Street Address', 'tablesome'),
                ),
                array(
                    'id' => 'addr2',
                    'label' => __('Address Line 2', 'tablesome'),
                ),
                array(
                    'id' => 'city',
                    'label' => __('City', 'tablesome'),
                ),
                array(
                    'id' => 'state',
                    'label' => __('State/Prov/Region', 'tablesome'),
                ),
                array(
                    'id' => 'zip',
                    'label' => __('Postal/Zip', 'tablesome'),
                ),
                array(
                    'id' => 'country',
                    'label' => __('Country', 'tablesome'),
                ),
            );
        }

        /**
         * Use of the below method for checking API status is active or not.
         *
         */
        public function ping()
        {
            $args = array(
                'headers' => $this->get_auth_header(),
            );
            $url = $this->get_api_base_path() . '/ping';
            $response = wp_remote_get($url, $args);
            $data = json_decode(wp_remote_retrieve_body($response), true);

            $status = false;

            if (wp_remote_retrieve_response_code($response) != 200) {
                $message = isset($data['detail']) ? $data['detail'] : 'The API key is invalid.';

                update_option($this->api_key_status_option_name, $status);
                update_option($this->api_key_status_message_option_name, $message);

                return array(
                    'status' => $status,
                    'message' => $message,
                );
            }

            $health_status = isset($data['health_status']) ? $data['health_status'] : $status;
            $status = (!empty($health_status) && $health_status === "Everything's Chimpy!") ? true : false;
            $message = ($status == true) ? 'Connected' : 'Not Connected';

            update_option($this->api_key_status_option_name, $status);
            update_option($this->api_key_status_message_option_name, $message);

            return array(
                'status' => $status,
                'message' => $message,
            );
        }

        public function get_audiences()
        {
            $audiences = array();

            if (!$this->api_status) {
                return $audiences;
            }

            $args = array(
                'headers' => $this->get_auth_header(),
            );
            $url = $this->get_api_base_path() . '/lists?count=1000';
            $response = wp_remote_get($url, $args);

            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
                return $audiences;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            $lists = isset($data['lists']) ? $data['lists'] : [];
            if (empty($lists)) {
                return $audiences;
            }

            foreach ($lists as $list) {
                $audiences[] = array(
                    'id' => $list['id'],
                    'label' => $list['name'],
                    'tags' => $this->get_all_tags_from_audience($list["id"]),
                    'integration_type' => 'mailchimp',
                );
            }

            // error_log(' audiences : ' . print_r($audiences, true));

            return $audiences;
        }

        public function get_all_tags_from_audience($audience_id)
        {
            $tags = array();
            if (empty($this->api_key) || empty($this->api_status)) {
                return $tags;
            }

            // Hint: Here is official endpoint for getting the tags from the audience, but it didn't works.
            // $url = $this->get_api_base_path() . '/tags/lists';

            /***
             * // Hint:
             *  Use the tag-search endpoint for getting the tags in the specific audience by searching the tag.
             *
             *  How to get all the tags from the audience by using this endpoint?
             *  If the search tag value is empty then its return the all the tags. else return it the searched tags only.
             */

            $url = $this->get_api_base_path() . "/lists/{$audience_id}/tag-search";

            $payload = array(
                'headers' => $this->get_auth_header(),
            );
            $response = wp_remote_get($url, $payload);

            if (is_wp_error($response) || $response['response']['code'] != 200) {
                return $tags;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            $tags = isset($data['tags']) && !empty($data['tags']) ? $data['tags'] : [];

            $tags = (array_map(function ($tag) {
                $return_data = $tag;
                // add new prop(label) for frontend purpose
                $return_data['label'] = $return_data['name'];
                return $return_data;
            }, $tags));

            return $tags;
        }

        public function get_fields_from_audience($audience_id)
        {
            $url = $this->get_api_base_path() . "/lists/{$audience_id}/merge-fields?count=1000";
            $payload = array(
                'headers' => $this->get_auth_header(),
            );
            $response = wp_remote_get($url, $payload);

            if (is_wp_error($response)) {
                return [];
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            if ($response['response']['code'] != 200) {
                return [];
            }

            $merge_fields = isset($data['merge_fields']) ? $data['merge_fields'] : [];

            if (empty($merge_fields)) {
                return [];
            }

            /** Ordering the fields based on display order value  */
            $display_orders = array_column($merge_fields, 'display_order');
            // Ref:- https://www.php.net/manual/en/function.array-multisort.php
            array_multisort($display_orders, SORT_ASC, $merge_fields);

            return $merge_fields;
        }

        /**
         * Add (or) Update the member to the list
         * API Document URL https://mailchimp.com/developer/marketing/api/list-members/add-or-update-list-member/
         */
        public function add_contact($audience_id, $email, $subscriber_data)
        {
            if (empty($audience_id) || empty($email) || !is_valid_tablesome_email($email)) {
                return false;
            }

            $subscriber_hash = md5(strtolower($email));

            $url = $this->get_api_base_path() . '/lists/' . $audience_id . '/members/' . $subscriber_hash . '?skip_merge_validation=true';

            $args = array(
                'method' => 'PUT',
                'headers' => $this->get_auth_header(),
                'body' => json_encode($subscriber_data),
            );

            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                return false;
            }

            $data = json_decode($response['body'], true);

            if ($response['response']['code'] != 200) {
                return false;
            }

            return true;
        }
    }
}
