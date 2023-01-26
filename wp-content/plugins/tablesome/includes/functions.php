<?php

use Tablesome\Includes\Modules\Workflow\Workflow_Manager;

if (!function_exists('set_tablesome_data')) {
    function set_tablesome_data($table_id, $props)
    {
        $options = isset($props['options']) ? $props['options'] : [];
        $editor_state = isset($props['editorState']) ? $props['editorState'] : [];
        $columns = isset($props['columns']) ? $props['columns'] : [];
        $rows = isset($props['rows']) ? $props['rows'] : [];
        $last_column_id = isset($props['meta']['last_column_id']) ? $props['meta']['last_column_id'] : 0;

        // already inserted to db
        $data = get_tablesome_data($table_id);
        $update_data = [
            'editorState' => $editor_state,
            'options' => $options,
            'columns' => $columns,
            'meta' => [
                'last_column_id' => $last_column_id,
            ],
        ];

        $tablesome_data = apply_filters('tablesome_data', $data, $update_data);
        update_post_meta($table_id, 'tablesome_data', $tablesome_data);

        return $tablesome_data;
    }
}

if (!function_exists('get_tablesome_data')) {
    function get_tablesome_data($table_id)
    {
        $table_data = \get_post_meta($table_id, 'tablesome_data');
        $table_data = isset($table_data[0]) && !empty($table_data[0]) ? $table_data[0] : [];

        return $table_data;
    }
}

if (!function_exists('get_tablesome_cell_type')) {
    function get_tablesome_cell_type($column_id, $columns = [])
    {
        $cell_type = 'text';

        if (!empty($columns)) {
            foreach ($columns as $column) {
                if ($column['id'] == $column_id) {
                    $cell_type = $column['format'];
                    break;
                }
            }
        }

        return $cell_type;
    }
}

if (!function_exists('get_tablesome_string')) {
    function get_tablesome_string($stringName)
    {
        // only set one time
        if (!isset($strings) || empty($strings)) {
            $translations = new \Tablesome\Includes\Translations();
            $strings = $translations->get_strings();
        }

        // Searched string is not exist display error for Developer insights
        if (!isset($strings[$stringName]) && empty($strings[$stringName])) {
            wp_die('"' . $stringName . '" translation string is not exist, Please add the given string in the translations.php file.');
        }

        return $strings[$stringName];
    }
}

if (!function_exists('get_tablesome_table_edit_url')) {
    function get_tablesome_table_edit_url($table_id)
    {
        $url = admin_url('edit.php?post_type=' . TABLESOME_CPT . '&action=edit&post=' . $table_id . '&page=tablesome_admin_page');
        return $url;
    }
}

if (!function_exists('splice_associative_array')) {
    function splice_associative_array($original_data, $position, $replacement_array)
    {
        /**
         *  Appending the $replacement_array array in $original_data array, set the $position ad -1.
         *  Add the $replacement_array array in top of the $original_data array, then set the $position as 0.
         *
         */

        $data = array_slice($original_data, 0, $position, true) +
        $replacement_array +
        array_slice($original_data, 0, count($original_data), true);

        return $data;
    }
}
if (!function_exists('get_app_memory_usage')) {
    function get_app_memory_usage()
    {
        $mem_usage = memory_get_usage(true);
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($mem_usage / pow(1024, ($i = floor(log($mem_usage, 1024)))), 2) . ' ' . $unit[$i];
    }
}

if (!function_exists('pauple_is_feature_active')) {
    function pauple_is_feature_active($feature_name)
    {
        $json = file_get_contents(__DIR__ . '/data/features.json');

        $features_array = json_decode($json);

        // error_log("json: " . print_r($json, true));
        // error_log("features_array: " . print_r($features_array, true));

        return isset($features_array->$feature_name) ? $features_array->$feature_name : false;
    }
}

if (!function_exists('tablesome_enable_feature')) {
    function tablesome_enable_feature($feature_name)
    {
        // is_feature_released
        // is_feature_allowed
        $is_premium = tablesome_fs()->can_use_premium_code__premium_only();
        $is_feature_active = pauple_is_feature_active($feature_name);
        $is_feature_enabled = $is_premium && $is_feature_active;
        return $is_feature_enabled;
    }
}

if (!function_exists('set_tablesome_table_triggers')) {
    function set_tablesome_table_triggers($table_id, $triggers_data)
    {
        if (empty($table_id)) {return [];}

        \update_post_meta($table_id, 'tablesome_table_triggers', $triggers_data);

        return get_tablesome_table_triggers($table_id);
    }
}

if (!function_exists('get_tablesome_table_triggers')) {
    function get_tablesome_table_triggers($table_id)
    {
        if (empty($table_id)) {return [];}
        $table_triggers_data = \get_post_meta($table_id, 'tablesome_table_triggers', true);
        // $table_triggers_data = isset($table_triggers_data[0]) && !empty($table_triggers_data[0]) ? $table_triggers_data[0] : [];
        // error_log(' table_triggers_data : ' . print_r($table_triggers_data, true));
        return $table_triggers_data;
    }
}

// A Callback function for csf field
if (!function_exists('print_tablesome_external_api_connector_html')) {
    function print_tablesome_external_api_connector_html($integration = "dummy")
    {
        echo "<div id='tablesome-$integration-settings'></div>";
    }
}

if (!function_exists('get_tablesome_insights_data')) {
    function get_tablesome_insights_data()
    {
        $insights_data = get_option(TABLESOME_INSIGHTS_DATA_OPTION);
        $insights_data = isset($insights_data) && !empty($insights_data) && is_array($insights_data) ? $insights_data : [];
        return $insights_data;
    }
}

if (!function_exists('set_tablesome_insights_data')) {
    function set_tablesome_insights_data($data)
    {
        \update_option(TABLESOME_INSIGHTS_DATA_OPTION, $data);

        return get_tablesome_insights_data();
    }
}

if (!function_exists('tablesome_multi_array_diff_assoc')) {
    function tablesome_multi_array_diff_assoc($array1, $array2)
    {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!array_key_exists($key, $array2)) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $multidimensionalDiff = tablesome_multi_array_diff_assoc($value, $array2[$key]);
                    if (count($multidimensionalDiff) > 0) {
                        $difference[$key] = $multidimensionalDiff;
                    }
                }
            } else {
                if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                    $difference[$key] = $value;
                }
            }
        }
        return $difference;
    }
}

if (!function_exists('is_valid_tablesome_date')) {
    function is_valid_tablesome_date($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}

if (!function_exists('convert_tablesome_date_to_unix_timestamp')) {
    function convert_tablesome_date_to_unix_timestamp($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d->getTimestamp();
    }
}

if (!function_exists('get_default_tablesome_smart_fields')) {
    function get_default_tablesome_smart_fields()
    {
        return [
            [
                'column_id' => 0,
                'column_label' => 'Submission Date',
                'column_format' => 'date',
                'column_status' => 'pending',
                'field_name' => 'created_at',
                'field_type' => 'tablesome_smart_fields',
                'detection_mode' => 'enabled',
            ],
            // [
            //     'column_id' => 0,
            //     'column_label' => 'Created By',
            //     'column_format' => 'number',
            // 'column_status' => 'pending',
            //     'field_name' => 'created_by',
            //     'field_type' => 'tablesome_smart_fields',
            //     'is_enabled' => false,
            // ],
            [
                'column_id' => 0,
                'column_label' => 'IP Address',
                'column_format' => 'text',
                'column_status' => 'pending',
                'field_name' => 'ip_address',
                'field_type' => 'tablesome_smart_fields',
                'detection_mode' => 'disabled',
            ],
            [
                'column_id' => 0,
                'column_label' => 'Page Source URL',
                'column_format' => 'url',
                'column_status' => 'pending',
                'field_name' => 'page_source_url',
                'field_type' => 'tablesome_smart_fields',
                'detection_mode' => 'disabled',
            ],
        ];
    }
}

if (!function_exists('get_tablesome_smart_field_info_by_field_name')) {
    function get_tablesome_smart_field_info_by_field_name($field_name)
    {
        // Set default values to avoid undefined index error
        $data = [
            'column_label' => 'Undefined Column',
            'column_format' => 'text',
        ];

        foreach (get_default_tablesome_smart_fields() as $smart_field) {
            if ($field_name == $smart_field['field_name']) {
                $data = $smart_field;
                break;
            }
        }
        return $data;
    }
}

if (!function_exists('get_tablesome_request_url')) {
    function get_tablesome_request_url()
    {
        $home_url = untrailingslashit(home_url());
        $referer = isset($_SERVER['HTTP_REFERER'])
        ? trim($_SERVER['HTTP_REFERER']) : '';

        if ($referer
            && 0 === strpos($referer, $home_url)) {
            return esc_url_raw($referer);
        }

        return esc_url_raw(home_url(add_query_arg(array())));
    }
}

if (!function_exists('get_tablesome_ip_address')) {
    function get_tablesome_ip_address()
    {
        $ip_addr = '';
        if (isset($_SERVER['REMOTE_ADDR']) && \WP_Http::is_ip_address($_SERVER['REMOTE_ADDR'])) {
            $ip_addr = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip_addr = 'UNKNOWN';
        }
        return $ip_addr;
        // $ipaddress = '';
        // if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        //     $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        // } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //     $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        // } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
        //     $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        // } else if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        //     $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        // } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        //     $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        // } else if (isset($_SERVER['HTTP_FORWARDED'])) {
        //     $ipaddress = $_SERVER['HTTP_FORWARDED'];
        // } else if (isset($_SERVER['REMOTE_ADDR'])) {
        //     $ipaddress = $_SERVER['REMOTE_ADDR'];
        // } else {
        //     $ipaddress = 'UNKNOWN';
        // }
        // return $ipaddress;
    }
}

if (!function_exists('is_valid_tablesome_email')) {
    function is_valid_tablesome_email($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }
}

if (!function_exists('tablesome_workflow_manager')) {
    function tablesome_workflow_manager()
    {
        $instance = Workflow_Manager::get_instance();
        return $instance;
    }
}

if (!function_exists('tablesome_json_encode')) {
    function tablesome_json_encode($data)
    {
        $encoded_data = json_encode($data);

        if (json_last_error()) {
            $encoded_data = json_encode($data, JSON_PARTIAL_OUTPUT_ON_ERROR);
        }
        if ($encoded_data !== false) {
            return $encoded_data;
        } else {
            wp_die("json_encode fail: " . json_last_error_msg());
        }
    }
}

if (!function_exists('get_tablesome_smart_field_values')) {
    function get_tablesome_smart_field_values()
    {
        global $globalCurrentUserID;

        $current_datetime = date('Y-m-d H:i:s');
        $unix_timestamp = strtotime($current_datetime);

        $values = array(
            'ip_address' => get_tablesome_ip_address(),
            'page_source_url' => get_tablesome_request_url(),
            'created_at_datetime' => $current_datetime,
            'created_at' => $unix_timestamp * 1000,
            'created_by' => $globalCurrentUserID,
            'current_user_id' => get_current_user_id(),
            'global_current_user_id' => $globalCurrentUserID,
            'admin_email' => get_bloginfo('admin_email'), // for send email
        );

        return $values;
    }
}

if (!function_exists('tablesome_env_defined')) {
    function tablesome_env_defined()
    {
        return defined('TABLESOME_ENV') ? constant('TABLESOME_ENV') : null;
    }
}

if (!function_exists('can_use_tablesome_premium')) {
    function can_use_tablesome_premium()
    {

        $is_premium = tablesome_fs()->can_use_premium_code__premium_only();
        if ($is_premium) {
            return true;
        }
        // Note:- It'll be mainly used in running testcases in docker env.
        if (tablesome_env_defined() && in_array(TABLESOME_ENV, ['development', 'testing']) && !$is_premium) {
            return true;
        }

        return false;
    }
}

if (!function_exists('convert_into_js_timestamp')) {
    function convert_into_js_timestamp($timestamp)
    {
        return $timestamp * 1000;
    }
}

if (!function_exists('get_json_data_from_file')) {
    function get_data_from_json_file($file_name)
    {

        $file_path = __DIR__ . "/data/{$file_name}";
        if (!file_exists($file_path)) {
            trigger_error("File Not Found  ", E_USER_NOTICE);
            return [];
        }
        $json = file_get_contents($file_path);

        $data = json_decode($json, true);

        return $data;
    }
}

if (!function_exists('maybe_refresh_access_token_by_integration')) {
    function maybe_refresh_access_token_by_integration($integration, $retry_again = false)
    {
        $api_credentials_handler = new Tablesome\Includes\Modules\API_Credentials_Handler();
        $api_credentials = $api_credentials_handler->get_api_credentials($integration);

        $is_access_token_expired = isset($api_credentials["access_token_is_expired"]) && $api_credentials["access_token_is_expired"] == true;
        $does_refresh_token_exist = isset($api_credentials["refresh_token"]) && !empty($api_credentials["refresh_token"]);

        $can_request_refresh_token = ($is_access_token_expired && $does_refresh_token_exist) || $retry_again;
        // use access token if it's not expired
        if (!$can_request_refresh_token) {
            return $api_credentials['access_token'];
        }

        if ($can_request_refresh_token) {
            $response = wp_remote_post(TABLESOME_CONNECTOR_DOMAIN . "/wp-json/tablesome-connector/v1/oauth/exchange-token?integration=$integration", array(
                'method' => 'GET',
                'body' => $api_credentials,
            ));

            if (is_wp_error($response)) {
                $api_credentials = [
                    'status' => "failed",
                    'message' => "Couldn't reach $integration server for exchange token!",
                ];
            } else {
                $api_credentials = json_decode(wp_remote_retrieve_body($response), true);
            }

            $result = $api_credentials_handler->set_api_credentials($integration, $api_credentials);
            return $result['access_token'];
        }
    }
}
