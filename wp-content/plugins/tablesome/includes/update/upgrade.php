<?php

namespace Tablesome\Includes\Update;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
/**
 * Tablesome upgrades.
 *
 * Tablesome upgrades handler class is responsible for updating different
 * Tablesome versions.
 *
 * @since 0.0.2
 */
if (!class_exists('\Tablesome\Includes\Update\Upgrade')) {
    class Upgrade
    {

        public static function init()
        {

            $args = [
                'db_version' => get_option('tablesome_version'),
                'file_version' => TABLESOME_VERSION,
                'version_option' => 'tablesome_version',
                'slug' => 'tablesome',
            ];

            $upgrade_list = new \Tablesome\Includes\Update\Upgrade_List();
            $upgrader = new \Pauple\Pluginator\Upgrader($args, $upgrade_list);
            $upgrader::add_actions();

        }
    } // END CLASS
}