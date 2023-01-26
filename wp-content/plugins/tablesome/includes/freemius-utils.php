<?php

namespace Tablesome\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Freemius_Utils')) {
    class Freemius_Utils
    {
        public function get_collection_props()
        {
            global $tablesome_fs;

            $user_id = isset($tablesome_fs->get_user()->id) ? $tablesome_fs->get_user()->id : '';
            if (empty($user_id)) {
                $user_id = 'T' . mt_rand();
            }

            $site_id = isset($tablesome_fs->get_site()->site_id) ? $tablesome_fs->get_site()->site_id : '';
            if (empty($site_id)) {
                $site_id = 'T' . mt_rand();
            }

            $props = array();
            $props['plan'] = $tablesome_fs->get_plan_name();
            $props['wp_version'] = $tablesome_fs->get_site()->platform_version;
            $props['php_version'] = $tablesome_fs->get_site()->programming_language_version;
            $props['site_url'] = $tablesome_fs->get_site()->url;
            $props['email'] = $tablesome_fs->get_user()->email;
            $props['user_id'] = $user_id;
            $props['site_id'] = $site_id;
            $props['language'] = $tablesome_fs->get_site()->language;

            return $props;
        }

        public function is_registered_and_allowed_tracking()
        {
            global $tablesome_fs;
            if (empty($tablesome_fs)) {
                return false;
            }
            return ($tablesome_fs->is_registered() && $tablesome_fs->is_tracking_allowed());
        }

        public function can_track()
        {
            $allowed_tracking = $this->is_registered_and_allowed_tracking();

            /** Compatible with the tablesome below version 0.4.1 */
            $can_track_below_v041 = $this->can_track_below_v041();
            return ($allowed_tracking && $can_track_below_v041);
        }

        public function can_track_below_v041()
        {
            $can_track_events = get_option('tablesome_can_track_events');

            if ($can_track_events == 'disabled') {return false;}

            // if ($can_track_events == 'enabled' || empty($can_track_events)) {return true;}
            return true;
        }
    }
}
