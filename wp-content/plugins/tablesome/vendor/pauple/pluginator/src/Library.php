<?php

namespace Pauple\Pluginator;

if (!class_exists('\Pauple\Pluginator\Library')) {
    class Library
    {
        public static function register_libraries($dependencies)
        {
            if (empty($dependencies) || !is_array($dependencies)) {
                return;
            }
            self::load_dependencies($dependencies);
        }

        public static function load_dependencies($dependencies)
        {
            $dependency_methods = self::get_dependency_methods();

            foreach ($dependencies as $dependency) {
                $callback = isset($dependency_methods[$dependency]) ? $dependency_methods[$dependency] : '';
                if (empty($callback) || !method_exists(__CLASS__, $callback)) {
                    continue;
                }
                self::$callback();
            }
        }

        public static function get_dependency_methods()
        {
            return [
                'codestar' => 'register_codestar',
                'freemius' => 'register_freemius',
            ];
        }

        public static function register_codestar()
        {
            // Include CodeStar Framework SDK.
            if (!function_exists('\CSF') && !class_exists('\CSF')) {
                require_once __DIR__ . '/Library/codestar-framework/codestar-framework.php';
            }
        }

        public static function register_freemius()
        {
            // Include Freemius SDK.
            if (!function_exists('fs_dynamic_init') && !class_exists('Freemius')) {
                require_once __DIR__ . "/Library/freemius/start.php";
            }
        }

    } // END CLASS
}