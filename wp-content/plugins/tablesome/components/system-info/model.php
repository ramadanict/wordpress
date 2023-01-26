<?php

namespace Tablesome\Components\System_Info;

if (!class_exists('\Tablesome\Components\System_Info\Model')) {
    class Model
    {

        public function get_viewProps()
        {
            return [
                'collection' => [],
                'items' => $this->get_itemProps(),
            ];
        }

        private function get_itemProps()
        {

            $plugins_info = $this->get_plugins_info();

            $props = array(
                'server' => array(
                    'label' => __("Server Environment", "tablesome"),
                    'data' => $this->get_server_info(),
                ),
                'wordpress' => array(
                    'label' => __('WordPress Environment', 'tablesome'),
                    'data' => $this->get_wordpress_info(),
                ),
                'theme' => array(
                    'label' => __('Theme', 'tablesome'),
                    'data' => $this->get_theme_info(),
                ),
                'user' => array(
                    'label' => __('User', 'tablesome'),
                    'data' => $this->get_user_info(),
                ),
                'active_plugins' => array(
                    'label' => __('Active Plugins', 'tablesome'),
                    'data' => isset($plugins_info['active_plugins']) && !empty($plugins_info['active_plugins']) ? $plugins_info['active_plugins'] : array(),
                ),
                'inactive_plugins' => array(
                    'label' => __('Inactive Plugins', 'tablesome'),
                    'data' => isset($plugins_info['inactive_plugins']) && !empty($plugins_info['inactive_plugins']) ? $plugins_info['inactive_plugins'] : array(),
                ),
                'tablesome_api_credentials' => array(
                    'label' => __('Tablesome API Info', 'tablesome'),
                    'data' => $this->get_tablesome_api_serialize_data(),
                ),
            );
            // Use of this prop for copying or downloading (not now, will do later) the overall system info as a text file.
            $props['raw_code_content'] = $this->get_raw_code_content($props);
            return $props;
        }

        public function get_server_info()
        {
            return array(
                'os' => array(
                    'label' => __('Operating System', 'tablesome'),
                    'value' => PHP_OS,
                ),
                'software' => array(
                    'label' => __('Software', 'tablesome'),
                    'value' => $_SERVER['SERVER_SOFTWARE'],
                ),
                'mysql_version' => array(
                    'label' => __('MySQL version', 'tablesome'),
                    'value' => $this->get_mysql_version(),
                ),
                'php_version' => array(
                    'label' => __('PHP Version', 'tablesome'),
                    'value' => PHP_VERSION,
                ),
                'php_max_input_vars' => array(
                    'label' => __('PHP Max Input Vars', 'tablesome'),
                    'value' => ini_get('max_input_vars'),
                ),
                'php_max_post_size' => array(
                    'label' => __('PHP Max Post Size', 'tablesome'),
                    'value' => ini_get('post_max_size'),
                ),
            );
        }

        public function get_wordpress_info()
        {
            return array(
                'wp_version' => array(
                    'label' => __('WP Version', 'tablesome'),
                    'value' => get_bloginfo('version'),
                ),
                'home_url' => array(
                    'label' => __('Home Url', 'tablesome'),
                    'value' => home_url(),
                ),
                'site_url' => array(
                    'label' => __('Site Url', 'tablesome'),
                    'value' => site_url(),
                ),
                'is_multisite' => array(
                    'label' => __('WP Multisite', 'tablesome'),
                    'value' => is_multisite() ? "Yes" : "No",
                ),
                'max_upload_size' => array(
                    'label' => __('Max Upload Size', 'tablesome'),
                    'value' => size_format(wp_max_upload_size()),
                ),
                'php_memory_limit' => array(
                    'label' => __('PHP Memory Limit', 'tablesome'),
                    'value' => ini_get('memory_limit'),
                ),
                'wp_memory_limit' => array(
                    'label' => __('WP Memory Limit', 'tablesome'),
                    'value' => WP_MEMORY_LIMIT,
                ),
                'permalink_structure' => array(
                    'label' => __('Permalink Structure', 'tablesome'),
                    'value' => get_option('permalink_structure'),
                ),
                'timezone' => array(
                    'label' => __('Timezone', 'tablesome'),
                    'value' => wp_timezone_string(),
                ),
                'language' => array(
                    'label' => __('Language', 'tablesome'),
                    'value' => get_locale(),
                ),
                'debug_mode' => array(
                    'label' => __('Debug Mode', 'tablesome'),
                    'value' => (defined('WP_DEBUG') && WP_DEBUG) ? "Enabled" : "Disabled",
                ),
            );
        }

        public function get_theme_info()
        {
            $theme = wp_get_theme();

            return [
                'name' => array(
                    'label' => __('Name', 'tablesome'),
                    'value' => $theme->get('Name'),
                ),
                'version' => array(
                    'label' => __('Version', 'tablesome'),
                    'value' => $theme->get('Version'),
                ),
                'author' => array(
                    'label' => __('Author', 'tablesome'),
                    'value' => $theme->get('Author'),
                ),
            ];
        }

        public function get_user_info()
        {
            $user = wp_get_current_user();
            $roles = isset($user->roles) ? $user->roles : array();
            $role = !empty($roles) ? array_shift($roles) : '';

            return [
                'name' => array(
                    'label' => __('Name', 'tablesome'),
                    'value' => isset($user->data->display_name) ? $user->data->display_name : '',
                ),
                'role' => array(
                    'label' => __('Role', 'tablesome'),
                    'value' => $role,
                ),
                'user_agent' => array(
                    'label' => __('User Agent', 'tablesome'),
                    'value' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                ),
            ];
        }

        public function get_plugins_info()
        {
            $helper = new \Tablesome\Includes\Helpers();
            $plugins_data = $helper->get_plugins_data();

            if (empty($plugins_data)) {
                return [];
            }

            $active_plugins = array();
            $inactive_plugins = array();

            foreach ($plugins_data as $base_path => $data) {

                $is_active = is_plugin_active($base_path);

                $label = '<a href="' . $data['PluginURI'] . '" target="_blank">' . $data['Name'] . '</a>';
                $label .= ' - <small>' . $data['Version'] . '</small>';
                $value = 'By' . ' <a href="' . $data['AuthorURI'] . '" target="_blank">' . $data['Author'] . '</a>';

                $text_content = '';
                $text_content .= $data['Name'] . "\n";
                $text_content .= "\t\tVersion: " . $data['Version'] . "\n";
                $text_content .= "\t\tAuthor: " . $data['Author'] . "\n\n";

                $plugin_prop = array(
                    'label' => $label,
                    'value' => $value,
                    'text_content' => $text_content,
                );

                $is_active == true ? array_push($active_plugins, $plugin_prop) : array_push($inactive_plugins, $plugin_prop);
            }

            return array(
                'active_plugins' => $active_plugins,
                'inactive_plugins' => $inactive_plugins,
            );
        }

        public function get_mysql_version()
        {
            global $wpdb;
            $db_server_version = $wpdb->get_results("SHOW VARIABLES WHERE `Variable_name` IN ( 'version_comment', 'innodb_version' )", OBJECT_K);
            return $db_server_version['version_comment']->Value . ' v' . $db_server_version['innodb_version']->Value;
        }

        private function get_raw_code_content($props)
        {
            $content = '';

            foreach ($props as $prop_key => $prop_data) {
                $label = $prop_data['label'];
                $env_data = isset($prop_data['data']) ? $prop_data['data'] : [];

                $content .= '== ' . $label . ' ==' . PHP_EOL;

                if (empty($env_data)) {
                    continue;
                }

                if ($prop_key == 'tablesome_api_credentials') {
                    $content .= $prop_data['data'] . PHP_EOL;
                    continue;
                }

                foreach ($env_data as $env_key => $env_info) {
                    $content .= "\t";
                    $is_plugin_info = isset($env_info['text_content']);
                    if ($is_plugin_info) {
                        $content .= $env_info['text_content'];
                    } else {
                        $content .= $env_info['label'] . ': ' . $env_info['value'] . PHP_EOL;
                    }

                }

                $content .= PHP_EOL;

            }
            return $content;
        }

        private function get_tablesome_api_serialize_data()
        {
            $api_credentials = get_option('tablesome_api_credentials');
            $required_key_info = array('status', 'message', 'api_version', 'expires_in', 'token_updated_utc', 'response_code');
            $data = [];
            foreach ($api_credentials as $key => $value) {
                $data[$key] = [];
                foreach ($value as $k => $v) {
                    if (in_array($k, $required_key_info)) {
                        $data[$key][$k] = $v;
                    }
                }
            }
            $api_credentials = maybe_serialize($data);
            return $api_credentials;
        }
    }
}
