<?php

namespace Tablesome\Includes\Modules\TablesomeDB_Rest_Api;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

/**
 * Ref:
 * https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/#arguments
 */

if (!class_exists('\Tablesome\Includes\Modules\TablesomeDB_Rest_Api\Routes')) {
    class Routes
    {

        public function get_routes()
        {
            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB_Rest_Api\TablesomeDB_Rest_Api();
            $workflow_api = new \Tablesome\Includes\Modules\TablesomeDB_Rest_Api\Workflow_Rest_Api();
            $hubspot = new \Tablesome\Includes\Modules\Workflow\External_Apis\Hubspot();
            return array(

                /** Import Records */
                array(
                    'url' => '/tables/import',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array(new \Tablesome\Includes\Modules\TablesomeDB_Rest_Api\Import(), 'import_records'),
                        'permission_callback' => array($tablesome_db, 'api_access_permission'),
                    ),
                ),

                // get export table
                array(
                    'url' => '/tables/(?P<table_id>\d+)/export',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array(new \Tablesome\Components\Export(), 'get_export_table_props'),
                        'args' => array(
                            'table_id' => array(
                                'required' => true,
                                'type' => 'number',
                            ),
                        ),
                        'permission_callback' => '__return_true',
                    ),
                ),

                /** Get all Tables */
                array(
                    'url' => '/tables',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($tablesome_db, 'get_tables'),
                        'permission_callback' => '__return_true',
                    ),
                ),

                /*** Get table by table_id  */
                array(
                    'url' => '/tables/(?P<table_id>\d+)',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($tablesome_db, 'get_table_data'),
                        'args' => array(
                            'table_id' => array(
                                'required' => true,
                                'type' => 'number',
                            ),
                        ),
                        'permission_callback' => '__return_true',
                    ),
                ),

                /** create (or) update the table */
                array(
                    'url' => '/tables',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($tablesome_db, 'create_or_update_table'),
                        'permission_callback' => array($tablesome_db, 'api_access_permission'),
                    ),
                ),

                /*** Delete Table */
                array(
                    'url' => '/tables',
                    'args' => array(
                        'methods' => \WP_REST_Server::DELETABLE,
                        'callback' => array($tablesome_db, 'delete'),
                        'permission_callback' => array($tablesome_db, 'api_access_permission'),
                    ),
                ),

                /** Get Records From table */
                array(
                    'url' => '/tables/(?P<table_id>\d+)/records',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($tablesome_db, 'get_table_records'),
                        'permission_callback' => '__return_true',
                        'args' => array(
                            'table_id' => array(
                                'required' => true,
                                'type' => 'number',
                            ),
                        ),
                    ),
                ),

                /** Save & update records */
                array(
                    'url' => '/tables/(?P<table_id>\d+)/records',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($tablesome_db, 'modified_records'),
                        'permission_callback' => array($tablesome_db, 'api_access_permission'),
                        'args' => array(
                            'table_id' => array(
                                'required' => true,
                                'type' => 'number',
                            ),
                        ),
                    ),
                ),

                /*** Delete Table Records */
                array(
                    'url' => '/tables/(?P<table_id>\d+)/records',
                    'args' => array(
                        'methods' => \WP_REST_Server::DELETABLE,
                        'callback' => array($tablesome_db, 'delete_records'),
                        'permission_callback' => array($tablesome_db, 'api_access_permission'),
                        'args' => array(
                            'table_id' => array(
                                'required' => true,
                                'type' => 'number',
                            ),
                        ),
                    ),
                ),
                array(
                    'url' => '/tablesome-api-keys',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array(new \Tablesome\Includes\Modules\Workflow\External_Apis\Api_Connect(), 'add_or_update_api_keys'),
                        'permission_callback' => array($tablesome_db, 'api_access_permission'),
                    ),
                ),

                /** Get Workflow Data */
                array(
                    'url' => '/workflow/posts',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_posts'),
                        'permission_callback' => '__return_true',
                    ),
                ),

                array(
                    'url' => '/workflow/fields',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_fields'),
                        'permission_callback' => '__return_true',
                    ),
                ),
                array(
                    'url' => '/workflow/tags',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_tags'),
                        'permission_callback' => '__return_true',
                    ),
                ),

                array(
                    'url' => '/workflow/get-post-types',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_post_types'),
                        'permission_callback' => '__return_true',
                    ),
                ),

                array(
                    'url' => '/workflow/taxonomies',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_taxonomies_with_terms_by_post_type'),
                        'permission_callback' => '__return_true',
                    ),
                ),

                array(
                    'url' => '/workflow/terms',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_terms_by_taxonomy_name'),
                        'permission_callback' => '__return_true',
                    ),
                ),
                array(
                    'url' => '/workflow/get-user-roles',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_user_roles'),
                        'permission_callback' => '__return_true',
                    ),
                ),
                array(
                    'url' => '/workflow/get-users',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_users'),
                        'permission_callback' => '__return_true',
                    ),
                ),
                array(
                    'url' => '/workflow/get-postmeta-keys',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_postmeta_keys_by_post_type'),
                        'permission_callback' => '__return_true',
                    ),
                ),

                array(
                    'url' => '/workflow/get-oauth-data',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'getOAuthDataByIntegration'),
                        'permission_callback' => '__return_true',
                    ),
                ),

                array(
                    'url' => '/workflow/set-oauth-data',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'setOAuthDataByIntegration'),
                        'permission_callback' => '__return_true',
                    ),
                ),

                array(
                    'url' => '/workflow/delete-oauth-data',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'deleteOAuthDataByIntegration'),
                        'permission_callback' => '__return_true',
                    ),
                ),

                array(
                    'url' => '/workflow/get-spreadsheets',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_spreadsheets'),
                        'permission_callback' => '__return_true',
                    ),
                ),
                array(
                    'url' => '/workflow/get-spreadsheet-data',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_sheets_by_spreadsheet_id'),
                        'permission_callback' => '__return_true',
                    ),
                ),
                array(
                    'url' => '/workflow/get-spreadsheet-records',
                    'args' => array(
                        'methods' => \WP_REST_Server::READABLE,
                        'callback' => array($workflow_api, 'get_spreadsheet_records'),
                        'permission_callback' => '__return_true',
                    ),
                ),
                // Test Endpoint for add records to Spreadsheet
                array(
                    'url' => '/workflow/append-records-to-spreadsheet',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($workflow_api, 'add_records_to_spreadsheet'),
                        'permission_callback' => '__return_true',
                    ),
                ),
            );
        }
    }
}
