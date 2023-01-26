<?php

namespace Tablesome\Includes\Modules\Workflow\External_Apis;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\External_Apis\Api_Connect')) {
    class Api_Connect
    {
        public function add_or_update_api_keys($request)
        {
            $params = $request->get_params();
            $api_key = isset($params['api_key']) ? $params['api_key'] : '';
            $type = isset($params['type']) ? $params['type'] : ''; // mailchimp, google
            $action = isset($params['action']) ? $params['action'] : '';

            if (!in_array($type, array('mailchimp', 'notion'))) {
                $response_data = array(
                    'message' => 'Invalid integration type.',
                    'status' => false,
                );
                return rest_ensure_response($response_data);
            }

            $disconnect_success_message = "%s API key has been removed successfully.";

            if ($type == 'mailchimp') {

                $mailchimp = new \Tablesome\Includes\Modules\Workflow\Integrations\Mailchimp();

                if ($action == 'disconnect') {

                    $mailchimp->remove_api_data();
                    
                    return rest_ensure_response(array(
                        'action' => 'disconnect',
                        'status' => false,
                        'message' => sprintf($disconnect_success_message, 'Mailchimp'),
                    ));
                }

                $mailchimp->add_api($api_key);
                $response_data = $mailchimp->mailchimp_api->ping();
                error_log('$response_data : ' . print_r($response_data, true));
                return rest_ensure_response($response_data);

            } else if ($type == 'notion') {

                $notion = new \Tablesome\Includes\Modules\Workflow\Integrations\Notion();

                if ($action == 'disconnect') {

                    $notion->remove_api_data();

                    return rest_ensure_response(array(
                        'action' => 'disconnect',
                        'status' => false,
                        'message' => sprintf($disconnect_success_message, 'Notion'),
                    ));
                }

                $notion->add_api($api_key);
                $response_data = $notion->notion_api->ping();
              
                return rest_ensure_response($response_data);
            }

            return rest_ensure_response(array(
                'status' => false,
                'message' => 'We didn\'t yet implement your requested type of integration',
            ));
        }
    }
}