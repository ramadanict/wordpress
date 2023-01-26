<?php

namespace Tablesome\Includes\Tracking;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\Tablesome\Includes\Tracking\Controller')) {
    class Controller
    {
        public function __construct()
        {
            $this->option_prefix = TABLESOME_OPTIONS;
            $this->model = new \Tablesome\Includes\Tracking\Model();
        }

        public function process_data(array $data = array())
        {
            if (count($data) == 0) {
                return;
            }

            $fs_utils = new \Tablesome\Includes\Freemius_Utils();
            $fs_collection_props = $fs_utils->get_collection_props();
            $user_props = $this->model->get_user_props();

            $collections = array();
            $collections['events'] = [];

            $user_agent = $_SERVER['HTTP_USER_AGENT'];

            $event = new \Tablesome\Includes\Tracking\Event();

            $browser_name = $this->model->get_browser_name($user_agent);
            foreach ($data as $event_type => $value) {
                $event_properties = $event->get_properties($event_type, $value);

                $args = array(
                    "user_id" => $fs_collection_props['site_id'],
                    "event_type" => $event_type,
                    "event_properties" => $event_properties,
                    "user_properties" => $user_props,
                    "ip" => $_SERVER['REMOTE_ADDR'],
                    "platform" => $browser_name,
                );
                $collections['events'][] = $args;
            }
            $collections['language'] = $fs_collection_props['language'];
            $collections['app_version'] = TABLESOME_VERSION;

            $make_request = new \Tablesome\Includes\Tracking\Dispatcher($collections);
            $response = $make_request->send();
            return (false == $response) ? false : true;
        }

        public function send_data($request_type = '')
        {
            $fs_utils = new \Tablesome\Includes\Freemius_Utils();
            if (!$fs_utils->can_track()) {
                return;
            }
            $data = $this->model->get_data();
            if ($request_type == 'deactivate') {
                $data['deactivate'] = 'deactivate';
            }

            $this->process_data($data);
        }

    }
}