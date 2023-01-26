<?php

namespace Tablesome\Includes\Modules\Workflow\External_Apis;

use Tablesome\Includes\Modules\API_Credentials_Handler;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\External_Apis\Hubspot')) {
    class Hubspot
    {
        public $integration;
        public $api_credentials_handler;
        public function __construct()
        {
            $this->integration = "hubspot";
            $this->api_credentials_handler = new API_Credentials_Handler();
        }

        public function is_active()
        {
            $data = $this->api_credentials_handler->get_api_credentials("hubspot");
            return $data["status"] == "success";
        }

        public function get_static_lists()
        {
            $access_token = maybe_refresh_access_token_by_integration($this->integration);
            $url = " https://api.hubapi.com/contacts/v1/lists/static?count=100&offset=0";

            $response = wp_remote_post($url, array(
                'method' => 'GET',
                'headers' => [
                    "authorization" => "Bearer " . $access_token,
                ],
            ));

            if (is_wp_error($response)) {
                $data = [
                    'status' => "failed",
                    'message' => "Couldn't get the $this->integration static lists!",
                ];
            } else {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $status = isset($data['status']) ? $data['status'] : '';
                $category = isset($data['category']) ? $data['category'] : '';
                $token_expired = $status == "error" && $category == "EXPIRED_AUTHENTICATION";

                if ($token_expired) {
                    maybe_refresh_access_token_by_integration($this->integration, true);
                    return $this->get_static_lists();
                }

                $data = $this->get_formated_lists($data["lists"]);
            }

            return $data;
        }

        public function get_properties()
        {
            $access_token = maybe_refresh_access_token_by_integration($this->integration);
            $response = wp_remote_post("https://api.hubapi.com/crm/v3/properties/contacts?archived=false", array(
                'method' => 'GET',
                'headers' => [
                    "authorization" => "Bearer " . $access_token,
                ],
            ));

            if (is_wp_error($response)) {
                $data = [
                    'status' => "failed",
                    'message' => "Couldn't get $this->integration contacts properties!",
                ];
            } else {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $status = isset($data['status']) ? $data['status'] : '';
                $category = isset($data['category']) ? $data['category'] : '';
                $token_expired = $status == "error" && $category == "EXPIRED_AUTHENTICATION";

                if ($token_expired) {
                    maybe_refresh_access_token_by_integration($this->integration, true);
                    return $this->get_properties();
                }

                $data = $this->get_formated_properties($data["results"]);
            }

            return $data;
        }

        public function add_contact($properties)
        {
            $access_token = maybe_refresh_access_token_by_integration($this->integration);
            $contact_properties = [
                "properties" => $properties,
            ];

            $url = "https://api.hubapi.com/crm/v3/objects/contacts";
            $args = array(
                'method' => 'POST',
                'headers' => [
                    "Authorization" => "Bearer " . $access_token,
                    "Content-Type" => "application/json",
                ],
                'body' => wp_json_encode($contact_properties),
            );

            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                $data = [
                    'status' => "failed",
                    'message' => "Failed, contact not added!",
                ];
            } else {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $status = isset($data['status']) ? $data['status'] : '';
                $category = isset($data['category']) ? $data['category'] : '';
                $token_expired = $status == "error" && $category == "EXPIRED_AUTHENTICATION";

                if ($token_expired) {
                    maybe_refresh_access_token_by_integration($this->integration, true);
                    return $this->add_contact($properties);
                }

                $data = array_merge([
                    'status' => "success",
                    'message' => "Successfully, added contact!",
                ], $data);
            }

            return $data;
        }

        public function add_contact_to_static_list($args)
        {
            $data = $this->add_contact($args["properties"]);
            $list_id = $args["list_id"];
            $contact_id = isset($data['id']) ? $data['id'] : '';
            if (empty($list_id) || empty($contact_id)) {
                return false;
            }

            $subscriber_data = ["vids" => [$contact_id]];
            $data = $this->api_credentials_handler->get_api_credentials("hubspot");
            $url = "https://api.hubapi.com/contacts/v1/lists/$list_id/add";
            $args = array(
                'method' => 'POST',
                'headers' => [
                    "Authorization" => "Bearer " . $data["access_token"],
                    "Content-Type" => "application/json",
                ],
                'body' => wp_json_encode($subscriber_data),
            );

            $response = wp_remote_post($url, $args);
            error_log(' response : ' . print_r($response, true));

            if (is_wp_error($response)) {
                $data = [
                    'status' => "failed",
                    'message' => "Failed, contact not added to static list!",
                ];
            } else {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $status = isset($data['status']) ? $data['status'] : '';
                $category = isset($data['category']) ? $data['category'] : '';
                $token_expired = $status == "error" && $category == "EXPIRED_AUTHENTICATION";

                if ($token_expired) {
                    maybe_refresh_access_token_by_integration($this->integration, true);
                    return $this->add_contact_to_static_list($args);
                }

                $data = array_merge([
                    'status' => "success",
                    'message' => "Successfully, contact added to static list!",
                ], $data);
            }

            return rest_ensure_response(
                $data
            );
        }

        private function get_formated_lists($given_lists)
        {
            $lists = [];
            foreach ($given_lists as $list) {
                if ($list["dynamic"] == true) {
                    continue;
                }

                array_push($lists, [
                    "id" => $list["listId"],
                    "label" => ucfirst($list["name"]),
                    "integration_type" => $this->integration,
                ]);
            }

            return $lists;
        }

        private function get_formated_properties($properties)
        {
            $fields = [];
            foreach ($properties as $property) {

                if ($property["modificationMetadata"]["readOnlyValue"]) {
                    continue;
                }

                $single_option = [
                    "id" => $property["name"],
                    "label" => $property["label"],
                    "type" => $property["fieldType"],
                ];

                if (isset($property["options"]) && !empty($property["options"])) {
                    $single_options = [];
                    foreach ($property["options"] as $option) {
                        array_push($single_options, [
                            "id" => $option["value"],
                            "label" => $option["label"],
                        ]);
                    }
                    $single_option["options"] = $single_options;
                }
                $fields[$property["label"]] = $single_option;
            }
            ksort($fields);
            return array_values($fields);
        }
    }
}
