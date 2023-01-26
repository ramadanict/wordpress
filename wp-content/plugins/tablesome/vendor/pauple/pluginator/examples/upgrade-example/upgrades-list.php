<?php

namespace Pluginator\Examples\Upgrade_Example;

// if (!defined('ABSPATH')) {
//     exit; // Exit if accessed directly.
// }

if (!class_exists('\Pluginator\Examples\Upgrade_Example\Upgrade_List')) {
    class Upgrade_List
    {
        public function get_upgrades()
        {
            $upgrades = [
                '0.5.9.2' => 'upgrade_v0592',
                '0.5.8' => 'upgrade_v058',
                '0.4.1' => 'upgrade_v041',
                '0.5.9.1' => 'upgrade_v0591',
                '0.2.6' => 'upgrade_v026',
                '0.4' => 'upgrade_v040',
                '0.2' => 'upgrade_v02',
                '0.0.2' => 'upgrade_v002',
            ];

            return $upgrades;
        }

        public function upgrade_v0592()
        {
            $upgrade_v0592_done = true;
            $tablesome_version = get_option("tablesome_version");
            error_log('[$tablesome_version] : ' . $tablesome_version);
            if ($tablesome_version === "0.5.9.2") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v0592_done;
            }
            error_log('[upgrade_v0592]');

            return $upgrade_v0592_done;
        }

        public function upgrade_v0591()
        {
            $upgrade_v0591_done = true;
            $tablesome_version = get_option("tablesome_version");
            error_log('[$tablesome_version] : ' . $tablesome_version);
            if ($tablesome_version === "0.5.9.1") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v0591_done;
            }
            error_log('[upgrade_v0591]');

            return $upgrade_v0591_done;
        }

        public function upgrade_v058()
        {
            $upgrade_v058_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.5.8") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v058_done;
            }
            error_log('[upgrade_v058]');

            return $upgrade_v058_done;
        }

        public function upgrade_v041()
        {
            $upgrade_v41_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.4.1") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v41_done;
            }
            error_log('[upgrade_v041]');

            return $upgrade_v41_done;
        }

        public function upgrade_v040()
        {
            $upgrade_v40_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.4") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v40_done;
            }

            error_log('[upgrade_v040]');

            return $upgrade_v40_done;
        }

        public function upgrade_v026()
        {
            $upgrade_v026_done = true;
            $tablesome_version = get_option("tablesome_version");
            if ($tablesome_version === "0.2.6") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v026_done;
            }
            error_log('[upgrade_v026]');

            return $upgrade_v026_done;
        }

        public function upgrade_v002()
        {
            $upgrade_v002_done = true;
            $tablesome_version = get_option("tablesome_version");
            $tablesome_upgrades = get_option("tablesome_upgrades");
            if ($tablesome_version === "0.0.2" || !empty($tablesome_upgrades)) {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v002_done;
            }
            error_log('[upgrade_v002]');

            return $upgrade_v002_done;
        }

        public function upgrade_v02()
        {
            $upgrade_v02_done = true;
            $tablesome_version = get_option("tablesome_version");
            $tablesome_upgrades = get_option("tablesome_upgrades");
            if ($tablesome_version === "0.2") {
                error_log('[Migration already processed for ' . $tablesome_version . ']');
                return $upgrade_v02_done;
            }
            error_log('[upgrade_v02]');

            return $upgrade_v02_done;
        }
    }
}
