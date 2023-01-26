<?php

namespace Tablesome\Includes\Modules;

if (!class_exists('\Tablesome\Includes\Modules\API_Credentials_Handler')) {
    class API_Credentials_Handler
    {
        public $option_name;
        public function __construct()
        {
            $this->option_name = "tablesome_api_credentials";
        }

        public function get_all_api_credentials()
        {
            $integrations = ["hubspot", "slack", "google"];
            $all_api_credentials = [];

            foreach ($integrations as $integration) {
                $all_api_credentials[$integration] = $this->get_api_credentials($integration);
            }

            return $all_api_credentials;
        }

        public function get_api_credentials($integration)
        {
            $all_api_credentials = get_option($this->option_name);
            $credentials = isset($all_api_credentials[$integration]) && !empty($all_api_credentials[$integration]) ? $this->get_credentials($all_api_credentials[$integration], $integration) : $this->get_dummy_credentials($integration);
            return $credentials;
        }

        public function set_api_credentials($integration, $given_data)
        {
            $api_data = get_option($this->option_name);

            $data = isset($api_data[$integration]) && !empty($api_data[$integration]) ? $api_data[$integration] : [];
            $data = array_merge($data, $given_data);
            if (isset($data["access_token"]) && !empty($data["access_token"])) {
                $data["token_updated_utc"] = current_time('mysql', 1);
            }

            $api_data[$integration] = $data;
            // error_log('Store ' . $integration . ' api data : ' . print_r($data, true));
            update_option($this->option_name, $api_data);

            return $data;
        }

        public function delete_api_credentials($integration)
        {
            $api_data = get_option($this->option_name);
            unset($api_data[$integration]);
            update_option($this->option_name, $api_data);
        }

        private function get_credentials($data, $integration)
        {
            $result = [
                "access_token" => isset($data["access_token"]) ? $data["access_token"] : "",
                "refresh_token" => isset($data["refresh_token"]) ? $data["refresh_token"] : "",
                "status" => isset($data["status"]) && $data["status"] == "success" ? "success" : "failed",
                "message" => isset($data["message"]) ? $data["message"] : "",

            ];

            $result["message"] = $result["status"] == "success" ? "Successfully, connected with $integration server." : $result["message"];
            $result["message_with_redirect_url"] = $result['message'] . '<br><a class="tablesome-notice__link" href=" ' . $this->get_redirect_url($integration) . '">Go to ' . ucfirst($integration) . ' Integration Settings</a>';

            $expires_in = isset($data['expires_in']) ? $data['expires_in'] : 0;
            // For security measure we will assume the token will be expired 2 minutes before actual expiration time
            $expires_in = $expires_in - 120; // 120 seconds

            $credentials = array();
            $credentials['token_updated_utc'] = isset($data['token_updated_utc']) ? $data['token_updated_utc'] : '';
            $credentials['token_expiration_datetime_utc'] = date('Y-m-d H:i:s', strtotime($credentials['token_updated_utc'] . ' + ' . $expires_in . ' seconds'));
            $credentials['current_datetime_utc'] = current_time('mysql', 1);
            $credentials['is_token_expired'] = $credentials['current_datetime_utc'] > $credentials['token_expiration_datetime_utc'];

            // error_log('$credentials : ' . print_r($credentials, true));

            if ($credentials['is_token_expired'] && !empty($result['access_token'])) {
                $result["access_token_is_expired"] = true;
                $result["message"] = "Access Token is expired!";
                // error_log(' Token Expired : ' . print_r($result, true));
            }

            return $result;
        }

        private function get_dummy_credentials($integration)
        {
            $message = [
                "status" => "failed",
                "message" => "Connection is not established, Please do connect with $integration server.",

            ];
            $message["message_with_redirect_url"] = $message['message'] . '<br><a class="tablesome-notice__link" href=" ' . $this->get_redirect_url($integration) . '">Go to ' . ucfirst($integration) . ' Integration Settings</a>';
            return $message;
        }

        public function get_redirect_url($integration)
        {
            return admin_url('edit.php?post_type=' . TABLESOME_CPT . '&page=tablesome-settings#tab=integrations/' . $integration);
        }
    }

}
