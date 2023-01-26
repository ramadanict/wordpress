<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\Tablesome')) {
    class Tablesome
    {
        public function __construct()
        {
            $this->setup_autoload();

            // $this->test_myque();

            $this->load_library();
            $this->load_tablesome_functions();
            $this->register_cpt_and_taxonomy();
            $this->load_update_handler();
            $this->load_actions();
            $this->load_filters();
            $this->load_shorcodes();

            // global $myque_db_created;
            // $myque_db_created = false;
        }

        protected function setup_autoload()
        {
            require_once TABLESOME_PATH . '/vendor/autoload.php';
        }

        protected function load_library()
        {
            if (!class_exists("\Pauple\Pluginator\Library")) {
                wp_die("\"freemius/wordpress-sdk\" and \"Codestar Framework\" library was not installed, \"Tablesome\" is depend on it. Do run \"composer update\".");
            }

            $library = new \Pauple\Pluginator\Library();
            $library::register_libraries(['codestar', 'freemius']);
        }

        public function load_tablesome_functions()
        {
            require_once TABLESOME_PATH . 'includes/functions.php';
            require_once TABLESOME_PATH . 'includes/workflow-functions.php';
            require_once TABLESOME_PATH . 'includes/settings/getter.php';
        }

        /*  Register Tablesome Post types and its Taxonomies */
        public function register_cpt_and_taxonomy()
        {
            $cpt = new \Tablesome\Includes\Cpt();
            $cpt->register();
        }

        public function test_myque()
        {
            // $myque = new \Tablesome\Includes\Modules\Myque\Myque_Exp();

            // $myque->create_table();

            // $myque->add_new_column();
            // $myque->save_column_value();

            // $myque->get_rows();
            // $myque->load_test(10);
            // $myque->doctrine_wrapper();
        }
        public function load_actions()
        {
            new \Tablesome\Includes\Actions();
        }

        public function load_filters()
        {
            new \Tablesome\Includes\Filters();
        }

        public function load_update_handler()
        {
            $upgrade = new \Tablesome\Includes\Update\Upgrade();
            $upgrade::init();
        }

        /*  Tablesome Shortcode */
        public function load_shorcodes()
        {
            new \Tablesome\Includes\Shortcodes();

            /** Init shortcode builder  */
            $builder = new \Tablesome\Includes\Shortcode_Builder\Builder();
            $builder->init();
        }
    }
    new Tablesome();
}
