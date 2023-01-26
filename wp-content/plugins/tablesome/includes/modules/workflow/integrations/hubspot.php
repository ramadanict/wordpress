<?php

namespace Tablesome\Includes\Modules\Workflow\Integrations;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Integrations\Hubspot')) {
    class Hubspot
    {
        public $hubspot_api;
        public function __construct()
        {
            $this->hubspot_api = new \Tablesome\Includes\Modules\Workflow\External_Apis\Hubspot();
        }

        public function get_config()
        {
            return array(
                'integration' => 'hubspot',
                'integration_label' => __('Hubspot', 'tablesome'),
                'is_active' => $this->hubspot_api->is_active(),
                'is_premium' => true,
                'actions' => array(),
            );
        }

        public function get_static_lists()
        {
            return $this->hubspot_api->get_static_lists();
        }

        public function get_fields()
        {
            return $this->hubspot_api->get_properties();
        }

    }
}
