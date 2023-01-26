<?php

namespace Tablesome\Includes\Core;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Core\Deactivation')) {
    class Deactivation
    {

        public function init()
        {
            $this->clear_cron_event();

            $this->track_data();
        }

        public function clear_cron_event()
        {
            /** Clear the CRON sh event */
            $scheduler = new \Tablesome\Includes\Cron();
            $scheduler->action('clear');
        }

        public function track_data()
        {
            /** Track tablesome event */
            $controller = new \Tablesome\Includes\Tracking\Controller();
            $controller->send_data('deactivate');
        }
    }
}