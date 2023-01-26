<?php

namespace Tablesome\Includes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\Tablesome\Includes\Cron')) {
    class Cron
    {

        /** Tablesome cron schedular hook name */
        public $cron_action_hook = 'tablesome/send_data_to_amplitude';

        // public $cron_processed_option_name = "tablesome_cron_processed";

        public function __construct()
        {
            // $this->did_cron_processed = get_option($this->cron_processed_option_name);
            $this->fs_utils = new \Tablesome\Includes\Freemius_Utils();

        }

        public function event_setup()
        {
            if (!$this->fs_utils->can_track()) {
                return;
            }
            $this->start();
        }

        public function set_intervals($schedules)
        {
            $schedules['tablesome_data_interval'] = array(
                'interval' => 7 * 24 * 60 * 60,
                'display' => __('Every Week', 'tablesome'),
            );
            $schedules['tablesome_data_test_interval'] = array(
                'interval' => 300,
                'display' => __('Every 5 min', 'tablesome'),
            );
            $schedules['every_min'] = array(
                'interval' => 18000,
                'display' => __('Every 60 Seconds', 'tablsome'),
            );
            $schedules['biweekly'] = array(
                'interval' => 7 * 24 * 60 * 60 * 2,
                'display' => __('Bi-Weekly', 'tablsome'),
            );
            return $schedules;
        }

        public function run(array $args = array())
        {
            error_log('$this->fs_utils->can_track() : ' . $this->fs_utils->can_track());
            if (!$this->fs_utils->can_track()) {
                return;
            }
            error_log(' READY TO SEND DATA TO AMPLITUDE ');
            $controller = new \Tablesome\Includes\Tracking\Controller();
            $controller->send_data();

            // $this->action('clear');
            // $this->cron_processed();
        }

        public function cron_processed()
        {
            update_option($this->cron_processed_option_name, 1);
        }

        public function clear()
        {
            wp_clear_scheduled_hook($this->cron_action_hook);
        }

        public function start(array $args = array())
        {
            // Ref: https://www.smashingmagazine.com/2013/10/schedule-events-using-wordpress-cron/
            $timestamp = wp_next_scheduled($this->cron_action_hook);
            if ($timestamp == false) {
                /** Single Event Schedule */

                // if (TABLESOME_ENV_MODE == 'production') {
                //     $interval = time() + (7 * 24 * 60 * 60);
                // } else {
                //     $interval = time() + 300;
                // }

                // wp_schedule_single_event($interval, $this->cron_action_hook, []);

                /*** Recurrence Scheduling  */
                wp_schedule_event(time(), 'tablesome_data_interval', $this->cron_action_hook);
            }
        }

        public function action($type, array $args = array())
        {
            if ($type == 'clear') {
                $this->clear();
            } else if ($type == 'start') {
                if (!$this->fs_utils->can_track()) {
                    $this->action('clear');
                    return;
                }
                // error_log(' STARTING CRON ');
                $this->event_setup();
            }
        }
    }
}
