<?php

if ( !function_exists( 'tablesome_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tablesome_fs()
    {
        global  $tablesome_fs ;
        if ( !isset( $tablesome_fs ) ) {
            // Include Freemius SDK.
            // $freemius_wordpress_sdk = TABLESOME_PATH . "vendor/freemius/wordpress-sdk/start.php";
            // if (!file_exists($freemius_wordpress_sdk)) {
            //     wp_die("composer package \"freemius/wordpress-sdk\" was not installed, Do run \"composer update.\"");
            // }
            // require_once $freemius_wordpress_sdk;
            $tablesome_fs = fs_dynamic_init( array(
                'id'             => '7163',
                'slug'           => 'tablesome',
                'type'           => 'plugin',
                'public_key'     => 'pk_12b7206bfde98e6b6646e8714b8f2',
                'is_premium'     => false,
                'premium_suffix' => '',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 7,
                'is_require_payment' => true,
            ),
                'menu'           => array(
                'slug'       => 'edit.php?post_type=' . TABLESOME_CPT,
                'first-path' => 'edit.php?post_type=' . TABLESOME_CPT . '&page=tablesome-onboarding',
                'contact'    => false,
                'support'    => true,
            ),
                'is_live'        => true,
            ) );
        }
        return $tablesome_fs;
    }
    
    // Init Freemius.
    tablesome_fs();
    tablesome_fs()->add_filter( 'permission_list', function ( $permissions ) {
        $permissions['tablesome-feature-tracking'] = array(
            'icon-class' => 'dashicons dashicons-admin-generic',
            'label'      => tablesome_fs()->get_text_inline( 'Tablesome Features', 'tablesome' ),
            'desc'       => tablesome_fs()->get_text_inline( 'Anonymously track which Tablesome features are being used to allow us to prioritize development.', 'tablesome' ),
            'priority'   => 50,
            'optional'   => true,
        );
        return $permissions;
    } );
    tablesome_fs()->add_filter( 'support_forum_url', 'tablesome_fs_support_forum_url' );
    function tablesome_fs_support_forum_url( $wp_support_url )
    {
        return 'https://wordpress.org/support/plugin/tablesome/';
    }
    
    tablesome_fs()->override_i18n( array(
        'support-forum' => __( 'Help & Feature Request', TABLESOME_DOMAIN ),
    ) );
    // Signal that SDK was initiated.
    do_action( 'tablesome_fs_loaded' );
}
