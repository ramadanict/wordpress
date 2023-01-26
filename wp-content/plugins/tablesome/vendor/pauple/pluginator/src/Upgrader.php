<?php

namespace Pauple\Pluginator;

// if (!defined('ABSPATH')) {
//     exit; // Exit if accessed directly.
// }

if (!class_exists('\Pauple\Pluginator\Upgrader')) {
    class Upgrader implements Interfaces\UpgraderInterface

    {
        private static $db_version;
        private static $file_version;
        private static $version_option;
        private static $plugin_slug;
        private static $upgrades_list;
        private static $upgrades_option;

        public function __construct($args, $upgrades_list)
        {
            // error_log('!!! upgrader !!!');
            // error_log('$args : ' . print_r($args, true));
            self::$db_version = $args['db_version'];
            self::$file_version = $args['file_version'];
            self::$version_option = $args['version_option'];
            self::$plugin_slug = $args['slug'];
            self::$upgrades_list = $upgrades_list;
            self::$upgrades_option = $args['slug'] . '_upgrades';
        }

        /**
         * Add actions.
         *
         * Hook into WordPress actions and launch plugin upgrades.
         *
         * @static
         * @since 1.9
         * @access public
         */
        public static function add_actions()
        {
            // error_log('upgrader->add_action() ');
            add_action('admin_init', [__CLASS__, 'init']);
        }

        /**
         * Init.
         *
         * Initialize plugin upgrades.
         *
         * Fired by `init` action.
         *
         * @static
         * @since 1.9
         * @access public
         */
        public static function init()
        {
            // error_log('upgrader CLASS init');
            $db_version = self::$db_version;
            $file_version = self::$file_version;

            // Normal init.
            if ($db_version === $file_version) {
                return;
            }

            self::check_upgrades($db_version);
        }
        /**
         * Check upgrades.
         *
         * Checks whether a given plugin version needs to be upgraded.
         *
         * If an upgrade required for a specific plugin version, it will update
         * the plugin_version option in the database.
         *
         * @static
         * @since 1.0.10
         * @access private
         *
         * @param string $db_version
         */

        public static function check_upgrades($db_version)
        {
            $upgrades = self::$upgrades_list->get_upgrades();

            // It's a new install.
            if (!$db_version || empty($upgrades)) {
                self::fresh_install_action($upgrades);
                error_log('VERYYY NEW INSTALL');
                return true;
            }

            asort($upgrades);
            // error_log('$upgrades  : ' . print_r($upgrades, true));

            error_log('NOT NEW INSTALL');

            $db_upgrades = \get_option(self::$upgrades_option, []);

            // Runs methods of each upgrade
            foreach ($upgrades as $migrate_version => $function) {
                if (version_compare($db_version, $migrate_version, '<') && !isset($db_upgrades[$migrate_version])) {
                    $is_done_sequencial_migration = self::$upgrades_list->$function(); // fire sequencial upgrade from given array value

                    error_log('$migrate_version : ' . $migrate_version . " , is_done_sequencial_migration: " . $is_done_sequencial_migration);
                    // if it is sequencial migration, then we need to update the option
                    if (!$is_done_sequencial_migration) {
                        return;
                    }

                    $db_upgrades[$migrate_version] = true;
                    // upgrades
                    \update_option(self::$upgrades_option, $db_upgrades);
                    // migrate version
                    \update_option(self::$version_option, $migrate_version);
                }
            }
        }

        private static function fresh_install_action($upgrades)
        {
            error_log('fresh_install_action');
            foreach ($upgrades as $version => $method_name) {
                $upgrades[$version] = true;
            }
            error_log('fresh_install_action $upgrades : ' . print_r($upgrades, true));
            \update_option(self::$upgrades_option, $upgrades);
            \update_option(self::$version_option, self::$file_version);
        }
    } // END CLASS
}
