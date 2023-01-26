<?php

namespace Tablesome\Includes;

use  Tablesome\Includes\Modules\API_Credentials_Handler ;
use  Tablesome\Components\Table\Settings\Settings as TableLevelSettings ;
use  Tablesome\Includes\Settings\Tablesome_Getter ;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( '\\Tablesome\\Includes\\Actions' ) ) {
    class Actions
    {
        public function __construct()
        {
            $this->utils = new \Tablesome\Includes\Utils();
            /** plugin activation Hook */
            register_activation_hook( TABLESOME__FILE__, array( $this, 'activation_hook_callback' ) );
            /** plugin deactivation Hook */
            register_deactivation_hook( TABLESOME__FILE__, array( new \Tablesome\Includes\Core\Deactivation(), 'init' ) );
            /*  Tablesome Init Hook */
            add_action( 'init', array( $this, 'init_hook' ) );
            /**  Rest Endpoints */
            add_action( 'rest_api_init', array( new \Tablesome\Includes\Modules\TablesomeDB_Rest_Api\TablesomeDB_Rest_Api(), 'init' ) );
            /* Admin Enqueing Script Action hook */
            add_action( 'admin_enqueue_scripts', array( $this, 'handle_admin_assets' ) );
            /*  Enqueing Script Action hook */
            add_action( 'wp_enqueue_scripts', array( $this, 'handle_frontend_assets' ) );
            // Admin Dashboard Area
            /*  Tablesome Admin Section Initialization Hook */
            add_action( 'admin_menu', [ $this, 'add_submenu' ] );
            add_action( 'admin_menu', [ new \Tablesome\Components\System_Info\Controller(), 'add_menu' ], 11 );
            add_action( 'admin_menu', [ $this, 'add_external_links_as_a_submenus' ], 11 );
            add_action( 'admin_menu', [ new \Tablesome\Includes\Modules\Workflow\Event_Log\Event_Log_List_Page(), 'add_menu' ] );
            // TODO: Remove the get_tables_count_collection method when tablesomeDB release after
            // add_action('admin_init', [$this, 'get_tables_count_collection']);
            add_action( 'admin_init', array( $this, 'admin_init_hook' ) );
            add_action( 'admin_init', [ new \Tablesome\Includes\Modules\Review_Notification(), 'init' ] );
            add_action( 'admin_init', [ new \Tablesome\Includes\Modules\Feature_Notice(), 'init' ] );
            add_action( 'init', array( $this, 'init_automation' ) );
            add_action( "load-post-new.php", [ $this, "redirect_to_add_new_table_custom_page" ] );
            add_action( 'admin_enqueue_scripts', 'wp_enqueue_media' );
            add_action( 'admin_bar_menu', [ $this, 'modify_admin_bar' ], 99 );
            //#1150: Exclude columns not working in elementor table shortcode builder
            add_action( "elementor/editor/before_enqueue_scripts", [ $this, "enqueue_shortcode_builder_script" ] );
            add_action( 'before_delete_post', function ( $postId ) {
                global  $post ;
                if ( isset( $post ) && $post->post_type != TABLESOME_CPT ) {
                    return;
                }
                // $table = new \Tablesome\Includes\Core\Table();
                // $table->delete_records_by_table_id($postId);
                $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
                $table = $tablesome_db->create_table_instance( $postId );
                $table->drop( $table );
            } );
            new \Tablesome\Includes\Settings\Settings();
            $this->cron = new \Tablesome\Includes\Cron();
            add_action( 'tablesome/send_data_to_amplitude', [ $this->cron, 'run' ] );
            $this->duplicate_table_controller = new \Tablesome\Components\Table\Duplicate_Table();
            add_action(
                'admin_action_duplicate_the_tablesome_table',
                [ $this, 'duplicate_table' ],
                10,
                1
            );
            add_action( 'admin_footer', array( $this, 'print_premium_modal_content' ) );
            add_action( 'admin_footer', array( $this, 'print_js_content' ) );
            add_action( 'wp_footer', array( $this, 'print_js_content' ) );
            add_action( 'wp_footer', array( $this, 'append_table_css' ) );
        }
        
        public function duplicate_table()
        {
            // Ref: https://rudrastyh.com/wordpress/duplicate-post.html
            $default_params = array(
                'post_type'   => TABLESOME_CPT,
                'link_action' => 'DUPLICATE',
            );
            
            if ( empty($_GET['table_id']) ) {
                $default_params['status'] = 'MISSING_TABLE_ID';
                wp_safe_redirect( add_query_arg( $default_params, admin_url( 'edit.php' ) ) );
                exit;
            }
            
            // Nonce verification
            
            if ( !isset( $_GET['tablesome_duplicate_nonce'] ) || !wp_verify_nonce( $_GET['tablesome_duplicate_nonce'], TABLESOME_PLUGIN_BASE ) ) {
                $default_params['status'] = 'SESSION_EXPIRED';
                wp_safe_redirect( add_query_arg( $default_params, admin_url( 'edit.php' ) ) );
                exit;
            }
            
            // Get the table id from the URL
            $table_id = absint( $_GET['table_id'] );
            // Get the table data
            $post = get_post( $table_id );
            
            if ( !isset( $post ) || empty($post) ) {
                $default_params['status'] = 'INVALID_POST_ID';
                wp_safe_redirect( add_query_arg( $default_params, admin_url( 'edit.php' ) ) );
                exit;
            }
            
            $new_table_id = $this->duplicate_table_controller->duplicate_table( $post );
            
            if ( empty($new_table_id) ) {
                $default_params['status'] = 'TABLE_NOT_DUPLICATE';
                wp_safe_redirect( add_query_arg( $default_params, admin_url( 'edit.php' ) ) );
                exit;
            }
            
            $default_params['status'] = 'TABLE_DUPLICATED';
            wp_safe_redirect( add_query_arg( $default_params, admin_url( 'edit.php' ) ) );
            exit;
        }
        
        public function modify_admin_bar( $wp_admin_bar )
        {
            // Update Edit Table URL
            
            if ( get_post_type() == "tablesome_cpt" && $wp_admin_bar->get_node( 'edit' ) ) {
                $edit_node = $wp_admin_bar->get_node( 'edit' );
                $edit_node->href = admin_url() . 'edit.php?post_type=' . TABLESOME_CPT . '&action=edit&post=' . get_the_ID() . '&page=tablesome_admin_page';
                $wp_admin_bar->add_node( $edit_node );
            }
            
            // Remove Tablesome Settings from admin bar menu
            $wp_admin_bar->remove_node( 'tablesome-settings' );
        }
        
        public function activation_hook_callback()
        {
            $table = new \Tablesome\Includes\Db\Tablesome_Table();
            $table->create();
            $onboarding = new \Tablesome\Includes\Modules\Onboarding();
            $onboarding->init();
            $tablesome_cpt = TABLESOME_CPT;
            $option_name = "{$tablesome_cpt}_registered_datetime";
            // Capture the datetime when plugin is activated first.
            $already_captured_plugin_registered_datetime = get_option( $option_name );
            if ( !$already_captured_plugin_registered_datetime ) {
                update_option( $option_name, date( 'Y-m-d H:i:s', time() ) );
            }
        }
        
        // Belows are callback functions of adding Actions order wise
        public function init_hook()
        {
            $this->setGlobalCurrentUserID();
            $this->cron->action( 'start' );
            /*  Tablesome Table-Actions Ajax Hooks */
            new \Tablesome\Includes\Ajax_Handler();
            
            if ( is_admin() ) {
                $tracking_notices = new \Tablesome\Includes\Tracking\Notices();
                $tracking_notices->can_show_notices();
            }
        
        }
        
        public function admin_init_hook()
        {
            // edit post url, to redirecting custom page
            add_filter(
                'get_edit_post_link',
                function ( $url, $post_id ) {
                $current_screen = get_current_screen();
                if ( isset( $current_screen ) && $current_screen->post_type == TABLESOME_CPT ) {
                    $url = admin_url( 'edit.php?post_type=' . TABLESOME_CPT . '&action=edit&post=' . $post_id . '&page=tablesome_admin_page' );
                }
                return $url;
            },
                10,
                2
            );
            /** Show the notices when perform the duplicating the table */
            $this->duplicate_table_controller->show_notices();
            $this->load_api_admin_notices_by_status( $this->get_api_data() );
        }
        
        public function handle_admin_assets()
        {
            // TODO: Bundle splitting needed for dashboard assets in order to below condition work
            // $current_screen = get_current_screen();
            // if (isset($current_screen) && $current_screen->post_type != TABLESOME_CPT) {
            //     return;
            // }
            $bundle_name = TABLESOME_DOMAIN . '-admin-bundle';
            // Enqueue admin scripts
            $this->register_admin_assets( $bundle_name );
            $this->enqueue_admin_assets( $bundle_name );
            $this->localize_admin_assets( $bundle_name );
            // Load Freemius Styles
            $should_load_freemius_styles = function_exists( 'fs_asset_url' ) && !wp_style_is( 'fs_common' );
            if ( $should_load_freemius_styles ) {
                wp_enqueue_style(
                    'fs_common',
                    fs_asset_url( WP_FS__DIR_CSS . '/' . trim( '/admin/common.css', '/' ) ),
                    [],
                    false,
                    'all'
                );
            }
            // Load Typography Assets
            $typography = new \Tablesome\Includes\Settings\Typography();
            $typography->enqueue( $bundle_name );
            // Load Common Assets
            $this->run_common_script( $bundle_name, 0, 'tablesome-edit-cpt' );
            $this->enqueue_shortcode_builder_script();
        }
        
        public function should_load_frontend_assets( $location )
        {
            $handle = 'quilljs';
            $list = 'enqueued';
            $is_script_enqueued_already = wp_script_is( $handle, $list );
            //     error_log(' is_script_enqueued_already: ' . $is_script_enqueued_already);
            // if ($is_script_enqueued_already) {
            //     return false;
            // }
            if ( is_singular( array( TABLESOME_CPT ) ) || $location == 'tablesome_shortcode' ) {
                return true;
            }
            return false;
        }
        
        public function handle_frontend_assets( $table_id = 0, $location = '' )
        {
            $should_load_frontend_assets = $this->should_load_frontend_assets( $location );
            $bundle_name = TABLESOME_DOMAIN . '-bundle';
            $workflow_bundle_name = TABLESOME_DOMAIN . '-workflow-bundle';
            $this->register_and_enqueue_workflow_scripts();
            $this->tablesome_ajax_object_localize_script( $workflow_bundle_name );
            if ( !$should_load_frontend_assets ) {
                return false;
            }
            if ( $table_id == 0 ) {
                $table_id = get_the_ID();
            }
            $this->register_frontend_assets( $bundle_name );
            $this->enqueue_frontend_assets( $bundle_name );
            $this->run_common_script( $bundle_name, $table_id, 'frontend' );
        }
        
        // Depend on admin or frontend enqueue_scripts
        protected function run_common_script( $bundle_name, $table_id = 0, $location = '' )
        {
            $this->localize_common_script( $bundle_name );
            $this->localize_tablesome_settings( $bundle_name, $table_id );
            $this->register_common_assets();
            $this->enqueue_common_assets( $table_id, $location );
            $this->enqueue_sheetjs( $table_id, $location );
        }
        
        public function register_frontend_assets( $bundle_name )
        {
            wp_register_style(
                $bundle_name,
                TABLESOME_URL . 'assets/bundles/public.bundle.css',
                [],
                TABLESOME_VERSION,
                'all'
            );
            wp_register_script(
                $bundle_name,
                TABLESOME_URL . 'assets/bundles/public.bundle.js',
                [ 'jquery' ],
                TABLESOME_VERSION,
                false
            );
        }
        
        private function register_and_enqueue_workflow_scripts()
        {
            wp_register_script(
                TABLESOME_DOMAIN . '-workflow-bundle',
                TABLESOME_URL . 'assets/bundles/workflow.bundle.js',
                [ 'jquery' ],
                TABLESOME_VERSION,
                false
            );
            wp_enqueue_script( TABLESOME_DOMAIN . '-workflow-bundle' );
        }
        
        public function enqueue_frontend_assets( $bundle_name )
        {
            wp_enqueue_style( $bundle_name );
            wp_enqueue_script( $bundle_name );
        }
        
        public function localize_admin_assets( $bundle_name )
        {
            $tablesome_localize_data = [
                "config" => TableLevelSettings::get_config(),
            ];
            wp_localize_script( $bundle_name, 'tablesome_api_data', $this->get_api_data() );
            wp_localize_script( $bundle_name, 'tablesome', $tablesome_localize_data );
        }
        
        public function register_admin_assets( $bundle_name )
        {
            wp_register_style(
                $bundle_name,
                TABLESOME_URL . 'assets/bundles/admin.bundle.css',
                [],
                TABLESOME_VERSION,
                'all'
            );
            wp_register_script(
                $bundle_name,
                TABLESOME_URL . 'assets/bundles/admin.bundle.js',
                [ 'jquery' ],
                TABLESOME_VERSION,
                false
            );
            wp_register_style( 'material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons' );
        }
        
        public function enqueue_admin_assets( $bundle_name )
        {
            wp_enqueue_style( $bundle_name );
            wp_enqueue_script( $bundle_name );
            wp_enqueue_style( 'material-icons' );
        }
        
        public function register_common_assets()
        {
            wp_register_style( 'quill-css', TABLESOME_URL . 'includes/lib/js/quilljs/quill.snow.css' );
            // wp_enqueue_style('quill-css');
            // Quill JS
            wp_register_script(
                'quilljs',
                TABLESOME_URL . 'includes/lib/js/quilljs/quill.min.js',
                [],
                TABLESOME_VERSION,
                true
            );
            // wp_enqueue_script('quilljs');
            // Sheet.JS
            wp_register_script(
                'sheetjs',
                TABLESOME_URL . 'includes/lib/js/sheetjs/xlsx.full.min.js',
                [],
                TABLESOME_VERSION,
                true
            );
            // wp_enqueue_script('sheetjs');
            wp_register_script(
                'svelte-dnd-action',
                TABLESOME_URL . 'includes/lib/js/svelte-dnd-action/svelte-dnd-action.min.js',
                [],
                TABLESOME_VERSION,
                true
            );
            // wp_enqueue_script('svelte-dnd-action');
            // wp_register_script('quilljs', TABLESOME_URL . 'includes/lib/js/quilljs/quill.min.js', [], TABLESOME_VERSION, true);
            // wp_register_script('sheetjs', TABLESOME_URL . 'includes/lib/js/sheetjs/xlsx.full.min.js', [], TABLESOME_VERSION, true);
            // wp_register_script('svelte-dnd-action', TABLESOME_URL . 'includes/lib/js/svelte-dnd-action/svelte-dnd-action.min.js', [], TABLESOME_VERSION, true);
        }
        
        public function enqueue_common_assets( $table_id, $location )
        {
            wp_enqueue_script( 'svelte-dnd-action' );
            wp_enqueue_style( 'quill-css' );
            wp_enqueue_style( 'dashicons' );
            wp_enqueue_script( 'quilljs' );
            // $should_load_sheetjs = $this->should_load_sheetjs($table_id, $location);
            // wp_enqueue_script('sheetjs');
        }
        
        public function enqueue_sheetjs( $table_id, $location = '' )
        {
            $tablesome_settings = $this->get_tablesome_settings_to_localize( $table_id );
            $desktop_export = $this->utils->get_bool( $tablesome_settings['display']['desktop-export'] );
            $mobile_export = $this->utils->get_bool( $tablesome_settings['display']['mobile-export'] );
            $is_export_enabled = $desktop_export || $mobile_export;
            $should_load_sheetjs = $location == 'tablesome-edit-cpt' || $is_export_enabled;
            // error_log('$table_id : ' . $table_id);
            // error_log('$should_load_sheetjs : ' . $should_load_sheetjs);
            if ( $should_load_sheetjs ) {
                // error_log('$tablesome_settings : ' . print_r($tablesome_settings, true));
                wp_enqueue_script( 'sheetjs' );
            }
        }
        
        public function get_api_data()
        {
            $api_credentials_handler = new API_Credentials_Handler();
            return array(
                'mailchimp_api_key'            => get_option( 'tablesome_mailchimp_api_key' ),
                'mailchimp_api_status'         => get_option( 'tablesome_mailchimp_api_status' ),
                'mailchimp_api_status_message' => get_option( 'tablesome_mailchimp_api_status_message' ),
                'notion_api_key'               => get_option( 'tablesome_notion_api_key' ),
                'notion_api_status'            => get_option( 'tablesome_notion_api_status' ),
                'notion_api_status_message'    => get_option( 'tablesome_notion_api_status_message' ),
                'api_credentials'              => $api_credentials_handler->get_all_api_credentials(),
            );
        }
        
        public function enqueue_shortcode_builder_script()
        {
            $bundle_name = TABLESOME_DOMAIN . '-shortcode-builder-bundle';
            wp_enqueue_script(
                $bundle_name,
                TABLESOME_URL . 'assets/bundles/shortcodebuilder.bundle.js',
                [ 'jquery' ],
                TABLESOME_VERSION,
                false
            );
            $this->tablesome_ajax_object_localize_script( $bundle_name );
        }
        
        public function add_submenu()
        {
            $params = $this->get_params_from_url();
            $edit_table_title = __( "Edit Table", "tablesome" );
            $create_new_table_title = __( "Create New Table", "tablesome" );
            $page_title = ( $params['post_action'] == 'edit' ? $edit_table_title : $create_new_table_title );
            $submenu_pages = [
                [
                'name'     => 'tablesome_admin_page',
                'title'    => $page_title,
                'menu'     => $create_new_table_title,
                'callback' => [
                'controller' => $this,
                'method'     => 'get_add_template_view',
            ],
            ],
                [
                'name'     => 'tablesome-import',
                'title'    => __( "Import a Table", "tablesome" ),
                'menu'     => __( "Import a Table", "tablesome" ),
                'callback' => [
                'controller' => new \Tablesome\Components\Import\Controller(),
                'method'     => 'render',
            ],
            ],
                [
                'name'     => 'tablesome-export',
                'title'    => __( "Export a Table", "tablesome" ),
                'menu'     => __( "Export a Table", "tablesome" ),
                'callback' => [
                'controller' => new \Tablesome\Components\Export(),
                'method'     => 'render',
            ],
            ],
                [
                'name'     => 'tablesome-onboarding',
                'title'    => __( "Getting Started", "tablesome" ),
                'menu'     => __( "Getting Started", "tablesome" ),
                'callback' => [
                'controller' => new \Tablesome\Includes\Pages\Onboarding(),
                'method'     => 'render',
            ],
            ]
            ];
            foreach ( $submenu_pages as $submenu_page ) {
                $this->add_submenu_page( $submenu_page );
            }
        }
        
        public function add_submenu_page( $submenu_page )
        {
            add_submenu_page(
                'edit.php?post_type=' . TABLESOME_CPT,
                /* main menu slug */
                $submenu_page["title"],
                /* page title */
                $submenu_page["menu"],
                /* page submenu title */
                'manage_categories',
                /* page roles and capability needed*/
                $submenu_page["name"],
                /* page name */
                array( $submenu_page["callback"]["controller"], $submenu_page["callback"]["method"] )
            );
        }
        
        public function add_external_links_as_a_submenus()
        {
            $docs = __( "Documentation", "tablesome" );
            $liked = __( "Liked Tablesome?", "tablesome" );
            $beta_link = __( "Try Latest (Beta)", "tablesome" );
            $menus = array( array(
                'page_title' => $beta_link,
                'menu_title' => $beta_link,
                'capability' => 'manage_options',
                'menu_slug'  => 'tablesome-test-beta-page',
                'callback'   => array( $this, 'handle_external_links' ),
            ), array(
                'page_title' => $docs,
                'menu_title' => $docs,
                'capability' => 'manage_categories',
                'menu_slug'  => 'tablesome-docs-page',
                'callback'   => array( $this, 'handle_external_links' ),
            ), array(
                'page_title' => $liked,
                'menu_title' => '<span class="dashicons dashicons-heart" style="color: #ff0077;"></span> ' . $liked,
                'capability' => 'manage_options',
                'menu_slug'  => 'tablesome-liked-page',
                'callback'   => array( $this, 'handle_external_links' ),
            ) );
            $parent_slug = 'edit.php?post_type=' . TABLESOME_CPT;
            foreach ( $menus as $menu ) {
                add_submenu_page(
                    $parent_slug,
                    $menu['page_title'],
                    $menu['menu_title'],
                    $menu['capability'],
                    $menu['menu_slug'],
                    $menu['callback']
                );
            }
        }
        
        public function handle_external_links()
        {
            $page = ( isset( $_GET['page'] ) ? $_GET['page'] : '' );
            if ( empty($page) ) {
                return;
            }
            return;
        }
        
        public function redirect_to_add_new_table_custom_page()
        {
            if ( isset( $_GET["post_type"] ) && $_GET["post_type"] == TABLESOME_CPT ) {
                wp_redirect( 'edit.php?post_type=' . TABLESOME_CPT . '&page=tablesome_admin_page' );
            }
        }
        
        public function get_add_template_view()
        {
            $defaults = array(
                'table_mode'     => 'editor',
                'pagination'     => true,
                'last_record_id' => 0,
            );
            $params = array_merge( $defaults, $this->get_params_from_url() );
            $dashboard_cpt_page = new \Tablesome\Includes\Dashboard\CPT_Page();
            $html = '<div class="tablesome-wrap wrap">';
            $html .= $dashboard_cpt_page->get_view( $params );
            $html .= '</div>';
            echo  $html ;
        }
        
        public function get_params_from_url()
        {
            $post_id = ( isset( $_GET['post'] ) ? $_GET['post'] : 0 );
            $post_action = ( empty($post_id) ? 'add' : 'edit' );
            return [
                'post_id'     => $post_id,
                'post_action' => $post_action,
            ];
        }
        
        public function print_premium_modal_content( $args = array() )
        {
            
            if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == TABLESOME_CPT ) {
                $html = '<div id="tablesome__modal--premium-notice" class="tablesome__modal">';
                $html .= '<div class="tablesome__modal__content">';
                $html .= '<span class="tablesome__modal--close">&times;</span>';
                $html .= '<h1>Start Free Trial</h1>';
                $html .= '<p>Start free trial to access the premium features.</p>';
                $html .= '<a class="tablesome button-primary" href="' . tablesome_fs()->get_trial_url() . '">Start Free Trial</a>';
                $html .= '</div>';
                $html .= '</div>';
                echo  $html ;
            }
        
        }
        
        public function append_table_css()
        {
            global  $tablesome_tables_collection ;
            if ( empty($tablesome_tables_collection) ) {
                return;
            }
            $tables_css = "";
            foreach ( $tablesome_tables_collection as $table_props ) {
                $table_id = $table_props["collection"]["table_id"];
                $table_style_meta = $table_props["collection"]["style"];
                $tables_css .= " " . TableLevelSettings::get_table_css( $table_id, $table_style_meta );
            }
            if ( !empty($tables_css) ) {
                echo  '<style type="text/css">' . wp_strip_all_tags( $tables_css ) . '</style>' ;
            }
        }
        
        public function print_js_content()
        {
            $this->print_tables_collection();
            
            if ( is_admin() ) {
                $this->print_triggers_actions_collection();
                $this->print_all_tables_collection();
            }
        
        }
        
        public function print_triggers_actions_collection()
        {
            $post_type = ( isset( $_GET['post_type'] ) ? $_GET['post_type'] : '' );
            $page = ( isset( $_GET['page'] ) ? $_GET['page'] : '' );
            if ( $post_type != TABLESOME_CPT || $page != 'tablesome_admin_page' ) {
                return;
            }
            $table_id = ( isset( $_GET['post'] ) ? $_GET['post'] : 0 );
            $api_data = $this->get_api_data();
            $mailchimp_message = $api_data['mailchimp_api_status_message'];
            $notion_message = $api_data['notion_api_status_message'];
            $notion_api_not_configured = empty($api_data['notion_api_status']) && empty($notion_message);
            $mailchimp_api_not_configured = empty($api_data['mailchimp_api_status']) && empty($mailchimp_message);
            if ( $notion_api_not_configured ) {
                $notion_message = 'Please configure Notion API in Tablesome for this action to work.';
            }
            if ( $mailchimp_api_not_configured ) {
                $mailchimp_message = 'Please configure Mailchimp API in Tablesome for this action to work';
            }
            $enqueue_data = array(
                'triggers'            => get_tablesome_table_triggers( $table_id ),
                'availableTriggers'   => $this->workflow_instance->get_triggers_config(),
                'availableActions'    => $this->workflow_instance->get_actions_config(),
                'mailchimpCollection' => array(
                'status'       => $api_data['mailchimp_api_status'],
                'message'      => $mailchimp_message,
                'redirect_url' => admin_url( 'edit.php?post_type=' . TABLESOME_CPT . '&page=tablesome-settings#tab=integrations/mailchimp' ),
            ),
                'notionCollection'    => array(
                'status'       => $api_data['notion_api_status'],
                'message'      => $notion_message,
                'redirect_url' => admin_url( 'edit.php?post_type=' . TABLESOME_CPT . '&page=tablesome-settings#tab=integrations/notion' ),
            ),
            );
            $script = "<script>";
            $script .= "window.tablesomeTriggers = " . json_encode( $enqueue_data ) . ";";
            $script .= "</script>";
            echo  $script ;
        }
        
        public function print_tables_collection()
        {
            global  $tablesome_tables_collection ;
            if ( empty($tablesome_tables_collection) ) {
                return;
            }
            $script = "<script type='text/javascript'>";
            $script .= "window.tablesomeTables = " . tablesome_json_encode( $tablesome_tables_collection ) . ";";
            $script .= "</script>";
            echo  $script ;
        }
        
        public function print_all_tables_collection()
        {
            $post_type = ( isset( $_GET['post_type'] ) ? $_GET['post_type'] : '' );
            $page = ( isset( $_GET['page'] ) ? $_GET['page'] : '' );
            if ( $post_type != TABLESOME_CPT && $page != 'tablesome-export' ) {
                return;
            }
            $get_all_tables = get_posts( array(
                'post_type'      => TABLESOME_CPT,
                'orderby'        => 'ID',
                'post_status'    => 'publish',
                'order'          => 'DESC',
                'posts_per_page' => -1,
            ) );
            $tables = array();
            foreach ( $get_all_tables as $table ) {
                array_push( $tables, array(
                    'id'    => $table->ID,
                    'title' => esc_html( $table->post_title ),
                ) );
            }
            $script = "<script>";
            $script .= "window.tablesomeAllTables = " . json_encode( $tables ) . ";";
            $script .= "</script>";
            echo  $script ;
        }
        
        public function localize_tablesome_settings( $bundle_name, $table_id = 0 )
        {
            $tablesome_settings = $this->get_tablesome_settings_to_localize( $table_id );
            wp_localize_script( $bundle_name, 'tablesome_settings', $tablesome_settings );
        }
        
        public function localize_common_script( $bundle_name )
        {
            $this->tablesome_ajax_object_localize_script( $bundle_name );
            // $tablesome_settings = $this->get_tablesome_settings_to_localize();
            $translations = new \Tablesome\Includes\Translations();
            $translation_strings = $translations->get_strings();
            $tablesome_fs = array(
                "plan"      => ( tablesome_fs()->can_use_premium_code__premium_only() ? 'premium' : 'free' ),
                "trial_url" => tablesome_fs()->get_trial_url(),
            );
            // wp_localize_script($bundle_name, 'tablesome_settings', $tablesome_settings);
            wp_localize_script( $bundle_name, 'translation_strings', $translation_strings );
            wp_localize_script( $bundle_name, 'tablesome_fs', $tablesome_fs );
        }
        
        public function get_tablesome_settings_to_localize( $table_id = 0 )
        {
            $helpers = new \Tablesome\Includes\Helpers();
            $date_format = $helpers->get_date_fns_js_compatible_with_wp( get_option( "date_format" ) );
            $table = new \Tablesome\Components\Table\Controller();
            $table_level_settings = $table->get_table_level_settings( $table_id );
            $tablesome_settings = [
                'rowLimit'             => TABLESOME_MAX_RECORDS_TO_READ,
                'columnLimit'          => TABLESOME_MAX_COLUMNS_TO_READ,
                'customStyle'          => Tablesome_Getter::get( 'style_disable' ),
                'date_format'          => $date_format,
                'add_new_link'         => admin_url( 'edit.php?post_type=' . TABLESOME_CPT . '&page=tablesome_admin_page' ),
                'settingsImgDirectory' => TABLESOME_URL . 'assets/images/settings/',
                'editorState'          => $table_level_settings["editorState"],
                'display'              => $table_level_settings["display"],
                'style'                => $table_level_settings["style"],
            ];
            return $tablesome_settings;
        }
        
        // public function register_common_assets()
        // {
        //     wp_register_style('quill-css', TABLESOME_URL . 'includes/lib/js/quilljs/quill.snow.css');
        //     wp_register_script('quilljs', TABLESOME_URL . 'includes/lib/js/quilljs/quill.min.js', [], TABLESOME_VERSION, true);
        //     wp_register_script('sheetjs', TABLESOME_URL . 'includes/lib/js/sheetjs/xlsx.full.min.js', [], TABLESOME_VERSION, true);
        //     wp_register_script('svelte-dnd-action', TABLESOME_URL . 'includes/lib/js/svelte-dnd-action/svelte-dnd-action.min.js', [], TABLESOME_VERSION, true);
        // }
        // public function enqueue_common_assets()
        // {
        //     wp_enqueue_style('quill-css');
        //     wp_enqueue_script('quilljs');
        //     wp_enqueue_script('sheetjs');
        //     wp_enqueue_script('svelte-dnd-action');
        //     wp_enqueue_style('dashicons');
        // }
        public function tablesome_ajax_object_localize_script( $bundle_name )
        {
            $tablesome_ajax_object = $this->get_tablesome_ajax_object();
            wp_localize_script( $bundle_name, 'tablesome_ajax_object', $tablesome_ajax_object );
        }
        
        public function get_tablesome_ajax_object()
        {
            $url = get_rest_url( null, 'tablesome/v1/workflow/posts' );
            $tablesome_ajax_object = array(
                'nonce'          => wp_create_nonce( 'tablesome_nonce' ),
                'ajax_url'       => admin_url( 'admin-ajax.php' ),
                'rest_nonce'     => wp_create_nonce( 'wp_rest' ),
                'edit_table_url' => admin_url( 'edit.php?post_type=' . TABLESOME_CPT . '&action=edit&post=0&page=tablesome_admin_page' ),
                'api_endpoints'  => array(
                'prefix'                 => get_rest_url( null, 'tablesome/v1/tables/' ),
                'create_or_update_table' => get_rest_url( null, 'tablesome/v1/tables' ),
                'import_records'         => get_rest_url( null, 'tablesome/v1/tables/import' ),
                'store_api_key'          => get_rest_url( null, 'tablesome/v1/tablesome-api-keys/' ),
                'workflow_posts_data'    => $url,
                'workflow_posts'         => get_rest_url( null, 'tablesome/v1/workflow/posts?' ),
                'workflow_fields'        => get_rest_url( null, 'tablesome/v1/workflow/fields?' ),
                'workflow_terms'         => get_rest_url( null, 'tablesome/v1/workflow/terms?' ),
                'workflow_taxonomies'    => get_rest_url( null, 'tablesome/v1/workflow/taxonomies?' ),
                'workflow_user_roles'    => get_rest_url( null, 'tablesome/v1/workflow/get-user-roles?' ),
                'workflow_post_types'    => get_rest_url( null, 'tablesome/v1/workflow/get-post-types?' ),
                'workflow_users'         => get_rest_url( null, 'tablesome/v1/workflow/get-users?' ),
                'get_oauth_data'         => get_rest_url( null, 'tablesome/v1/workflow/get-oauth-data?' ),
                'delete_oauth_data'      => get_rest_url( null, 'tablesome/v1/workflow/delete-oauth-data?' ),
            ),
                "site_domain"    => $_SERVER['SERVER_NAME'],
            );
            return $tablesome_ajax_object;
        }
        
        public function get_tables_count_collection()
        {
            global  $pagenow ;
            global  $tablesome_tables_count_collection ;
            $tablesome_tables_count_collection = array();
            return;
            $post_type = ( isset( $_GET['post_type'] ) ? $_GET['post_type'] : '' );
            $page = ( isset( $_GET['page'] ) ? $_GET['page'] : '' );
            /** The Current page is must be a tablesome tables summary page. otherwise, it's a return. */
            $is_tablesome_tables_list_page = isset( $pagenow ) && $pagenow == 'edit.php' && $post_type == 'tablesome_cpt' && empty($page);
            if ( !$is_tablesome_tables_list_page ) {
                return;
            }
            $crud = new \Tablesome\Includes\Db\CRUD();
            /**
             * Tablesome tables count collection
             */
            $collections = $crud->get_tables_count_collection_by_query();
            $data = array();
            if ( empty($collections) ) {
                return;
            }
            foreach ( $collections as $collection ) {
                $table_id = $collection['post_id'];
                $data[$table_id] = $collection['records_count'];
            }
            $tablesome_tables_count_collection = $data;
        }
        
        public function init_automation()
        {
            $this->workflow_instance = tablesome_workflow_manager();
        }
        
        public function load_api_admin_notices_by_status( $api_data )
        {
            return;
            $status = ( $api_data['mailchimp_api_status'] == true ? true : false );
            $message = $api_data['mailchimp_api_status_message'];
            $api_not_configured = !$status && empty($message);
            $is_settings_page = isset( $_GET['page'] ) && $_GET['page'] == 'tablesome-settings';
            if ( $status == true || $api_not_configured || $is_settings_page ) {
                return;
            }
            $url = admin_url( 'edit.php?post_type=' . TABLESOME_CPT . '&page=tablesome-settings#tab=integrations/mailchimp' );
            $content = '';
            $content .= '<h3>' . __( 'Mailchimp API key validation has failed.', 'tablesome' ) . '</h3>';
            $content .= '<p>' . __( ' Response from Mailchimp', 'tablesome' ) . ' "' . $message . '"</p>';
            $content .= '<p><a href="' . $url . '">' . __( 'Click here', 'tablesome' ) . '</a> ' . __( 'to configure the Mailchimp API settings in Tablesome.', 'tablesome' ) . '</p>';
            $html = '<div class="helpie-notice notice notice-error is-dismissible">';
            $html .= $content;
            $html .= '</div>';
            add_action( 'admin_notices', function () use( $html ) {
                echo  $html ;
            } );
        }
        
        private function setGlobalCurrentUserID()
        {
            global  $globalCurrentUserID ;
            $currentUserID = get_current_user_id();
            if ( is_null( $globalCurrentUserID ) && !is_null( $currentUserID ) && !empty($currentUserID) ) {
                $globalCurrentUserID = $currentUserID;
            }
        }
    
    }
}