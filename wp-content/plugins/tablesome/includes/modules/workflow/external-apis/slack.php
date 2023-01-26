<?php

namespace Tablesome\Includes\Modules\Workflow\External_Apis;

use Tablesome\Includes\Modules\API_Credentials_Handler;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\External_Apis\Slack')) {
    class Slack
    {
        public $integration;
        public $api_credentials_handler;
        public $base_url = 'https://slack.com/api/';
        public function __construct()
        {
            $this->integration = "slack";
            $this->api_credentials_handler = new API_Credentials_Handler();
        }

        public function is_active()
        {
            $data = $this->api_credentials_handler->get_api_credentials($this->integration);
            return $data["status"] == "success";
        }
        public function get_channels()
        {
            $response = $this->make_request('GET', 'conversations.list', ['exclude_archived' => true]);
            $is_ok = (isset($response['ok']) && $response['ok'] == true);
            if (!$is_ok) {
                return [];
            }

            $channels = $response['channels'];
            $channels = array_filter($channels, function ($channel) {
                return $channel['is_channel'] == true;
            });

            if (!isset($channels) || empty($channels)) {
                return [];
            }

            return array_map(function ($channel) {
                return [
                    'id' => $channel['id'],
                    'label' => $channel['name'],
                    'topic' => $channel['topic']['value'],
                ];
            }, $channels);

            // return [
            //     [
            //         "id" => "channel1",
            //         "label" => "Channel One",
            //     ],
            //     [
            //         "id" => "channel2",
            //         "label" => "Channel Two",
            //     ],
            //     [
            //         "id" => "channel3",
            //         "label" => "Channel Three",
            //     ],
            // ];
        }

        public function get_users()
        {
            $response = $this->make_request('GET', 'users.list', []);
            $is_ok = (isset($response['ok']) && $response['ok'] == true);
            if (!$is_ok) {
                return [];
            }

            $members = $response['members'];
            $users = array_filter($members, function ($member) {
                return $member['is_bot'] == false && $member['deleted'] == false && $member['id'] != "USLACKBOT";
            });

            if (empty($users)) {
                return [];
            }

            $users = array_map(function ($user) {
                return [
                    'id' => $user['id'],
                    'label' => $user['real_name'] . " (" . $user['name'] . ")",
                ];
            }, $users);
            return array_values($users);

            // return [
            //     [
            //         "id" => "user1",
            //         "label" => "User One",
            //     ],
            //     [
            //         "id" => "user2",
            //         "label" => "User Two",
            //     ],
            //     [
            //         "id" => "user3",
            //         "label" => "User Three",
            //     ],
            // ];
        }

        public function send_message($channel_id, $message)
        {
            $params = [
                'text' => $message,
                'channel' => $channel_id, // TODO: Test channel. For sending message to a channel.
                // 'thread_ts' => '1631000000.000100' // TODO: Test thread_ts. For replying to a message.
            ];
            $response = $this->make_request('POST', 'chat.postMessage', $params);

            return $response;
        }

        private function get_header()
        {
            $access_token = maybe_refresh_access_token_by_integration($this->integration);
            return array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json;charset=utf-8',
            );
        }

        private function make_request($request_method = 'GET', $method, $params)
        {
            $header = $this->get_header();
            $url = $this->base_url . $method;

            $payload = [
                'method' => $request_method,
                'headers' => $header,
            ];
            if ($request_method == 'GET') {
                $url .= '?' . http_build_query($params);
            } else if ($request_method == 'POST') {
                $payload['body'] = json_encode($params);
            }

            $response = wp_remote_post($url, $payload);

            if (is_wp_error($response) || $response['response']['code'] != 200) {
                return [];
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            return $data;
        }

    }
}
