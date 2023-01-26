<?php

namespace Tablesome\Includes\Tracking;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\Tablesome\Includes\Tracking\Notices')) {
    class Notices
    {

        public function can_show_notices()
        {
            if ($this->can_see_the_notice()) {
                add_action('admin_notices', array($this, 'print_review_notice'));
            }
        }

        public function can_see_the_notice()
        {
            $fs_utils = new \Tablesome\Includes\Freemius_Utils();
            $show_opt_in_notices = get_option('tablesome_opt_in_notices');
            return ($fs_utils->is_registered_and_allowed_tracking() && $show_opt_in_notices);
        }

        public function refresh_page($url)
        {
            echo "<script type='text/javascript'>
               window.location=document.location.href='" . $url . "';
            </script>";
        }

        public function click_handler()
        {
            $can_track_tablesome_events = isset($_GET['can_track_tablesome_events']) ? $_GET['can_track_tablesome_events'] : '';

            if (!empty($can_track_tablesome_events)) {
                update_option('tablesome_opt_in_notices', 0);

                /** use of this below option for tracking users events if they are using the tablesome below version 0.4.1 */
                if ($can_track_tablesome_events == 'enable') {
                    update_option('tablesome_can_track_events', 'enabled');
                }
                $url = remove_query_arg('can_track_tablesome_events');
                $this->refresh_page($url);
            }
        }

        public function print_review_notice()
        {
            $this->click_handler();

            $heading = __('The following option control the permission you are granting for Tablesome.', 'tablesome');
            $desc = __('When this is enabled, we will collect completely anonymous information about which features are enabled, which storage/vision provider is being used and which batch tools are being used. We do not collect any personally identifiable information, everything is completely anonymous. Nor do we collect any information such as file names, access keys, etc. We use this information to focus development efforts on features that matter most to our users.', 'tablesome');

            $html = '<div class="helpie-notice notice notice-success is-dismissible" >';
            $html .= '<p>' . $heading . '</p>';
            $html .= '<p>' . $desc . '</p>';
            $html .= '<p><a href="' . add_query_arg(array('can_track_tablesome_events' => 'enable')) . '">' . __("Enable", "tablesome") . '</a>&nbsp;&nbsp;';
            $html .= '<a href="' . add_query_arg(array('can_track_tablesome_events' => 'disable')) . '">' . __("Disable", "tablesome") . '</a></p>';
            $html .= '</div>';
            echo $html;

        }

    }
}