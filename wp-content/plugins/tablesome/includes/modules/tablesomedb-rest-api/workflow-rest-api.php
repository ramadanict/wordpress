<?php

namespace Tablesome\Includes\Modules\TablesomeDB_Rest_Api;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\TablesomeDB_Rest_Api\Workflow_Rest_Api')) {
    class Workflow_Rest_Api
    {
        public $workflow_manager_instance;
        public $supported_integrations;
        public function __construct()
        {
            $this->workflow_manager_instance = tablesome_workflow_manager();

            $triggers = array_keys($this->workflow_manager_instance->triggers);
            $integrations = array_keys($this->workflow_manager_instance->integrations);
            $this->supported_integrations = array_merge($triggers, $integrations);
        }

        public function get_posts($request)
        {
            $params = $request->get_params();
            $integration_type = isset($params['integration_type']) ? $params['integration_type'] : '';

            if (empty($integration_type) || !in_array($integration_type, $this->supported_integrations)) {
                return rest_ensure_response(
                    array(
                        'status' => 'failed',
                        'message' => "Given Integration_type named {$integration_type} does not exist",
                        'params' => $params,
                    )
                );
            }

            // external services
            $external_services = ['notion', 'mailchimp', 'hubspot', 'gsheet'];

            if (!in_array($integration_type, $external_services)) {

                // Get all the form by intergration
                return rest_ensure_response(
                    array(
                        'status' => 'success',
                        'message' => "Successfully, Get all {$integration_type} posts",
                        'data' => $this->workflow_manager_instance->get_posts_by_integration($integration_type),
                    )
                );

            } else if (in_array($integration_type, $external_services)) {

                return rest_ensure_response(
                    array(
                        'status' => 'success',
                        'message' => 'Successfully, get the external data by integration',
                        'data' => $this->workflow_manager_instance->get_external_data_by_integration($integration_type),
                    )
                );

            }

        }

        public function get_fields($request)
        {
            $params = $request->get_params();

            $document_id = isset($params['id']) ? $params['id'] : '';
            $integration_type = isset($params['integration_type']) ? $params['integration_type'] : '';

            // wordpress post types
            if (!empty($integration_type) && !empty($document_id) && $integration_type == "wordpress") {
                // Get all the form by intergration
                return rest_ensure_response(
                    array(
                        'status' => 'success',
                        'message' => "Successfully, Get all {$integration_type} of posts",
                        'data' => $this->get_cpt_posts($document_id),
                    )
                );
            }

            if (empty($integration_type) || !in_array($integration_type, $this->supported_integrations)) {
                return rest_ensure_response(
                    array(
                        'status' => 'failed',
                        'message' => "Given Integration type named {$integration_type} does not exist",
                        'params' => $params,
                    )
                );
            }

            if (empty($document_id)) {
                return rest_ensure_response(
                    array(
                        'status' => 'failed',
                        'message' => 'Given ID does not exist',
                        'params' => $params,
                    )
                );
            }

            $external_services = ['notion', 'mailchimp', 'hubspot', 'slack', 'gsheet'];

            if (in_array($integration_type, $external_services)) {
                return rest_ensure_response(
                    array(
                        'status' => 'success',
                        'message' => 'Successfully, get the external data by ID',
                        'data' => $this->workflow_manager_instance->get_external_data_fields_by_id($integration_type, $document_id),
                    )
                );
            } else {
                return rest_ensure_response(
                    array(
                        'status' => 'success',
                        'message' => "Successfully, Get all the fields",
                        'data' => $this->workflow_manager_instance->get_post_fields_by_id($integration_type, $document_id),
                    )
                );
            }
        }

        public function get_tags($request)
        {
            $params = $request->get_params();
            $document_id = isset($params['id']) ? $params['id'] : '';
            $integration_type = isset($params['integration_type']) ? $params['integration_type'] : '';

            if (empty($integration_type) || empty($document_id) || 'mailchimp' != $integration_type) {
                return rest_ensure_response(
                    array(
                        'status' => 'failed',
                        'message' => "Given Integration_type named {$integration_type} does not exist or audience ID missing/empty",
                        'params' => $params,
                    )
                );
            }

            $tags = $this->workflow_manager_instance->integrations['mailchimp']->mailchimp_api->get_all_tags_from_audience($document_id);

            return rest_ensure_response(
                array(
                    'status' => 'success',
                    'message' => 'Getting mailchimp tags',
                    'data' => $tags,
                )
            );

        }

        public function get_post_types()
        {
            $options = [];
            $post_types = get_post_types(array('show_in_nav_menus' => true), 'objects');
            // Failed response
            if (is_wp_error($post_types) || empty($post_types)) {
                return rest_ensure_response(
                    array(
                        'status' => 'failed',
                        'message' => "Post types are not found",
                        'data' => $options,
                    )
                );
            }

            foreach ($post_types as $post_type) {
                $options[] = array(
                    "id" => $post_type->name,
                    "label" => ucfirst($post_type->labels->name),
                );
            }

            // Success response
            return rest_ensure_response(
                array(
                    'status' => 'success',
                    'message' => "Successfully, Get all post types",
                    'data' => $options,
                )
            );
        }

        protected function get_cpt_posts($post_type)
        {
            $posts = get_posts(array(
                'post_type' => $post_type,
                'numberposts' => -1,
            ));

            if (empty($posts)) {
                return [];
            }

            $options = array();
            foreach ($posts as $post) {
                $options[] = array(
                    'id' => $post->ID,
                    'label' => $post->post_title . " (ID: " . $post->ID . ")",
                    'url' => get_permalink($post->ID),
                );
            }

            return $options;
        }

        public function get_taxonomies_with_terms_by_post_type($request)
        {
            $params = $request->get_params();
            $post_type = isset($params['post_type']) ? $params['post_type'] : '';

            if (empty($post_type) || !post_type_exists($post_type)) {
                return rest_ensure_response(
                    array(
                        'status' => 'failed',
                        'message' => "Post type is missing or not registered",
                        'params' => $params,
                    )
                );
            }

            $taxonomies = $this->workflow_manager_instance->integrations['wordpress']->get_taxonomies_with_terms_by_post_type($post_type);

            return rest_ensure_response(
                array(
                    'status' => "success",
                    'message' => "Successfully, get the taxonomies data by {$post_type}",
                    'data' => $taxonomies,
                )
            );
        }

        public function get_terms_by_taxonomy_name($request)
        {
            $params = $request->get_params();
            $taxonomy_name = isset($params['taxonomy_name']) ? $params['taxonomy_name'] : '';

            if (empty($taxonomy_name) || !taxonomy_exists($taxonomy_name)) {
                return rest_ensure_response(
                    array(
                        'status' => 'failed',
                        'message' => "Taxonomy is missing or not registered",
                        'params' => $params,
                    )
                );
            }

            $terms = $this->workflow_manager_instance->integrations['wordpress']->get_terms_by_taxonomy_name($taxonomy_name);

            return rest_ensure_response(
                array(
                    'status' => "success",
                    'message' => "Successfully, get the terms data by {$taxonomy_name}",
                    'data' => $terms,
                )
            );
        }

        public function get_user_roles()
        {

            $wp_roles = isset(wp_roles()->role_names) ? wp_roles()->role_names : [];
            $user_roles = [];

            if (!empty($wp_roles)) {
                // Exclude the roles
                $exclude_roles = ['administrator'];
                foreach ($exclude_roles as $role) {
                    if (isset($wp_roles[$role])) {
                        unset($wp_roles[$role]);
                    }
                }

                foreach ($wp_roles as $name => $label) {
                    $user_roles[] = array(
                        'id' => $name,
                        'label' => $label,
                    );
                }

            }

            return rest_ensure_response(
                array(
                    'status' => "success",
                    'message' => "Successfully, get all user roles",
                    'data' => $user_roles,
                )
            );
        }

        public function get_users()
        {

            $users = get_users();

            if (is_wp_error($users) || empty($users)) {
                $response = array(
                    'status' => "failed",
                    'message' => "No users found! or error occurred when getting users data.",
                    'data' => [],
                );

                return rest_ensure_response($response);
            }

            $options = [];
            foreach ($users as $user) {
                $options[] = [
                    'id' => $user->ID,
                    'label' => isset($user->data->display_name) ? $user->data->display_name : '',
                ];
            }

            return rest_ensure_response(
                array(
                    'status' => "success",
                    'message' => "Successfully get all the users",
                    'data' => $options,
                )
            );
        }

        public function get_postmeta_keys_by_post_type($request)
        {
            $params = $request->get_params();
            $post_type = isset($params['post_type']) ? $params['post_type'] : '';

            if (empty($post_type) || !post_type_exists($post_type)) {
                return rest_ensure_response(
                    array(
                        'status' => 'failed',
                        'message' => "Post type is missing or not registered",
                        'params' => $params,
                    )
                );
            }

            $meta_keys = $this->workflow_manager_instance->integrations['wordpress']->get_postmeta_keys_by_post_type($post_type);

            return rest_ensure_response(
                array(
                    'status' => "success",
                    'message' => "Successfully, get the meta keys by {$post_type}",
                    'data' => $meta_keys,
                )
            );

        }

        public function getOAuthDataByIntegration($request)
        {
            $params = $request->get_query_params();
            $integration = isset($params["integration"]) ? $params["integration"] : "";

            if (empty($integration)) {
                return rest_ensure_response(
                    array(
                        'status' => 'failed',
                        'message' => "Failed, Missing integration!",
                    )
                );
            }

            $redirect_url = $this->get_oauth_redirect_url($integration);

            return rest_ensure_response(
                array(
                    'status' => 'success',
                    'message' => "Successfully, authorized hubspot",
                    'data' => $redirect_url,
                )
            );
        }

        public function setOAuthDataByIntegration($request)
        {
            $params = $request->get_query_params();
            $integration = isset($params["integration"]) ? $params["integration"] : "";
            error_log(' get_query_params : ' . print_r($params, true));

            if (!empty($integration)) {
                $api_credentials_handler = new \Tablesome\Includes\Modules\API_Credentials_Handler();
                $api_credentials_handler->set_api_credentials($integration, $params);
            } else {
                $integration = "mailchimp";
            }

            wp_redirect($api_credentials_handler->get_redirect_url($integration));
            exit;
        }

        public function deleteOAuthDataByIntegration($request)
        {
            $params = $request->get_query_params();
            $integration = isset($params["integration"]) ? $params["integration"] : "";

            if (empty($integration)) {
                return rest_ensure_response(
                    array(
                        'status' => 'failed',
                        'message' => "Failed, Missing integration!",
                    )
                );
            }

            $api_credentials_handler = new \Tablesome\Includes\Modules\API_Credentials_Handler();
            $api_credentials_handler->delete_api_credentials($integration);

            return rest_ensure_response(
                array(
                    'status' => 'success',
                    'message' => "Successfully, deleted $integration integration!",
                )
            );
        }

        private function get_oauth_redirect_url($integration)
        {
            $connector_url = TABLESOME_CONNECTOR_DOMAIN . "/wp-json/tablesome-connector/v1/oauth/install?integration=" . $integration;
            $client_redirect_url = get_rest_url(null, 'tablesome/v1/workflow/set-oauth-data?integration=' . $integration);
            $wp_nonce = wp_create_nonce('tablesome_workflow_nonce');

            $connector_url = add_query_arg('client_redirect_url', $client_redirect_url, $connector_url);
            $connector_url = add_query_arg('wp_nonce', $wp_nonce, $connector_url);

            return $connector_url;
        }

        public function get_spreadsheets()
        {
            $sheets = $this->workflow_manager_instance->integrations['gsheet']->get_spreadsheets();

            if (empty($sheets)) {
                $response = array(
                    'status' => "failed",
                    'message' => "No data found! or error occurred when getting spreadsheets data.",
                    'data' => [],
                );
                return rest_ensure_response($response);
            }

            $response = array(
                'status' => "success",
                'message' => "Successfully get the spreadsheets data.",
                'data' => $sheets,
            );
            return rest_ensure_response($response);

        }

        public function get_sheets_by_spreadsheet_id($request)
        {
            $params = $request->get_query_params();
            $spreadsheet_id = isset($params["spreadsheet_id"]) ? $params["spreadsheet_id"] : "";
            $data = $this->workflow_manager_instance->integrations['gsheet']->get_sheets_by_spreadsheet_id($spreadsheet_id);
            if (empty($data)) {
                $response = array(
                    'status' => "failed",
                    'message' => "No data found! or error occurred when getting sheet data by ID .",
                    'data' => [],
                );
                return rest_ensure_response($response);
            }

            $response = array(
                'status' => "success",
                'message' => "Successfully get the spreadsheet data by ID.",
                'data' => $data,
            );
            return rest_ensure_response($response);
        }

        public function get_spreadsheet_records($request)
        {
            $params = $request->get_query_params();
            $spreadsheet_id = isset($params["spreadsheet_id"]) ? $params["spreadsheet_id"] : "";
            $sheet_name = isset($params['sheet_name']) ? $params['sheet_name'] : "";
            $coordinates = isset($params['coordinates']) ? $params['coordinates'] : ""; // A1:Z100
            $read_first_row_as_header = isset($params['read_first_row_as_header']) ? $params['read_first_row_as_header'] : "NO";
            if (empty($spreadsheet_id) || empty($sheet_name)) {
                $response = array(
                    'status' => "failed",
                    'message' => "Required data missing!",
                    'data' => [],
                );
                return rest_ensure_response($response);
            }
            $data = $this->workflow_manager_instance->integrations['gsheet']->get_spreadsheet_records($spreadsheet_id, [
                'sheet_name' => $sheet_name,
                'coordinates' => $coordinates,
                'read_first_row_as_header' => $read_first_row_as_header,
            ]);

            if (empty($data)) {
                $response = array(
                    'status' => "failed",
                    'message' => "No data found! or error occurred when getting sheet records .",
                    'data' => [],
                );
                return rest_ensure_response($response);
            }

            $response = array(
                'status' => "success",
                'message' => "Successfully get the spreadsheet records.",
                'data' => $data,
            );
            return rest_ensure_response($response);
        }

        // TODO: Test method for add records to spreadsheet
        public function add_records_to_spreadsheet($request)
        {
            $params = $request->get_params();
            $result = $this->workflow_manager_instance->integrations['gsheet']->add_records_to_sheet($params);
            return rest_ensure_response($result);
        }
    }
}
