<?php

namespace Tablesome\Includes\Modules\Workflow;

use Tablesome\Includes\Modules\Workflow\Actions\GSheet_Add_Row;
use Tablesome\Includes\Modules\Workflow\Actions\Hubspot_Add_Contact;
use Tablesome\Includes\Modules\Workflow\Actions\Hubspot_Add_Contact_To_Static_List;
use Tablesome\Includes\Modules\Workflow\Actions\Mailchimp_Add_Contact;
use Tablesome\Includes\Modules\Workflow\Actions\Notion_Database;
use Tablesome\Includes\Modules\Workflow\Actions\Slack_Send_Message_To_Channel;
use Tablesome\Includes\Modules\Workflow\Actions\Slack_Send_Message_To_User;
use Tablesome\Includes\Modules\Workflow\Actions\Tablesome_Add_Row;
use Tablesome\Includes\Modules\Workflow\Actions\Tablesome_Filter_Table;
use Tablesome\Includes\Modules\Workflow\Actions\Tablesome_Load_WP_Query_Content;
use Tablesome\Includes\Modules\Workflow\Actions\WP_Post_Creation;
use Tablesome\Includes\Modules\Workflow\Actions\WP_Redirection;
use Tablesome\Includes\Modules\Workflow\Actions\WP_Send_Mail;
use Tablesome\Includes\Modules\Workflow\Actions\WP_User_Creation;
use Tablesome\Includes\Modules\Workflow\Event_Log\Event_Log;
use Tablesome\Includes\Modules\Workflow\Integrations\Mailchimp;
use Tablesome\Includes\Modules\Workflow\Integrations\Notion;
use Tablesome\Includes\Modules\Workflow\Integrations\Slack;
use Tablesome\Includes\Modules\Workflow\Integrations\Tablesome;
use Tablesome\Includes\Modules\Workflow\Integrations\WP_Core;
use Tablesome\Includes\Modules\Workflow\Triggers\Cf7;
use Tablesome\Includes\Modules\Workflow\Triggers\Elementor;
use Tablesome\Includes\Modules\Workflow\Triggers\Fluent;
use Tablesome\Includes\Modules\Workflow\Triggers\Forminator;
use Tablesome\Includes\Modules\Workflow\Triggers\Gravity;
use Tablesome\Includes\Modules\Workflow\Triggers\Tablesome as TablesomeTrigger;
use Tablesome\Includes\Modules\Workflow\Triggers\WP_Forms;
use \Tablesome\Includes\Modules\Workflow\Integrations\GSheet;
use \Tablesome\Includes\Modules\Workflow\Integrations\Hubspot;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Workflow_Manager')) {
    class Workflow_Manager
    {

        public static $instance = null;
        public $actions;
        public $triggers;
        public $integrations;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
                self::$instance->init();
            }
            return self::$instance;
        }

        public function init()
        {
            $this->triggers = array(
                'tablesome' => new TablesomeTrigger(),
                'cf7' => new Cf7(),
                'wpforms' => new WP_Forms(),
                'elementor' => new Elementor(),
                'forminator' => new Forminator(),
                'gravity' => new Gravity(),
                'fluent' => new Fluent(),
            );

            $this->integrations = array(
                'tablesome' => new Tablesome(),
                'wordpress' => new WP_Core(),
                'mailchimp' => new Mailchimp(),
                'notion' => new Notion(),
            );

            $this->actions = array(
                'add_row' => new Tablesome_Add_Row(),
                'add_contact' => new Mailchimp_Add_Contact(),
                'add_page' => new Notion_Database(),
                'redirection' => new WP_Redirection(),
                'add_new_wp_post' => new WP_Post_Creation(),
                'add_new_wp_user' => new WP_User_Creation(),
                'send_mail' => new WP_Send_Mail(),
                'load_wp_query_content' => new Tablesome_Load_WP_Query_Content(),
            );

            if (tablesome_enable_feature("filter_table_action")) {
                $this->actions['filter_table'] = new Tablesome_Filter_Table();
            }
            if (tablesome_enable_feature("gsheet_action")) {
                $this->integrations['gsheet'] = new GSheet();
                $this->actions['gsheet_add_row'] = new GSheet_Add_Row();
            }
            if (tablesome_enable_feature("slack_action")) {
                $this->integrations['slack'] = new Slack();
                $this->actions['slack_send_message_to_channel'] = new Slack_Send_Message_To_Channel();
                $this->actions['slack_send_message_to_user'] = new Slack_Send_Message_To_User();
            }

            if (tablesome_enable_feature("hubspot_action")) {
                $this->integrations['hubspot'] = new Hubspot();
                $this->actions['hubspot_add_contact'] = new Hubspot_Add_Contact();
                $this->actions['hubspot_add_contact_to_static_list'] = new Hubspot_Add_Contact_To_Static_List();
            }

            $this->register_trigger_hooks();
            // add_action("load_editor");

            add_filter("tablesome_form_submission_data", [self::$instance, "add_attachment_to_submission_data"]);

            Event_Log::get_instance();
        }

        public function register_trigger_hooks()
        {

            foreach ($this->triggers as $key => $trigger) {
                $trigger->init($this->actions);
                $config = $trigger->get_config();

                if (!isset($config['hooks'])) {
                    continue;
                }

                foreach ($config['hooks'] as $hook) {
                    add_action($hook['name'], array($trigger, $hook['callback_name']), $hook['priority'], $hook['accepted_args']);
                }

            }
        }

        public function get_triggers_config()
        {
            $configs = [];
            $is_premium = tablesome_fs()->can_use_premium_code__premium_only();
            $pro_text = " - PRO";
            foreach ($this->triggers as $trigger) {
                $config = $trigger->get_config();
                $config["trigger_label"] = $config["is_premium"] == "yes" && !$is_premium ? $config["trigger_label"] . $pro_text : $config["trigger_label"];

                $configs[] = $config;
            }
            return $configs;
        }

        public function get_actions_config()
        {
            $configs = [];
            $is_premium = tablesome_fs()->can_use_premium_code__premium_only();
            $pro_text = " - PRO";
            foreach ($this->integrations as $name => $integration_instance) {
                $config = $integration_instance->get_config();

                foreach ($this->actions as $action_name => $action_instance) {
                    $action_config = $action_instance->get_config();
                    if ($config['integration'] == $action_config['integration']) {
                        $action_config["label"] = $action_config["is_premium"] && !$is_premium ? $action_config["label"] . $pro_text : $action_config["label"];

                        $config['actions'][] = $action_config;
                    }
                }
                $configs[] = $config;
            }
            return $configs;
        }

        public function get_trigger_prop_value_by_id($trigger_id, $prop_name)
        {
            $value = '';
            foreach ($this->triggers as $trigger) {
                $config = $trigger->get_config();
                if (isset($config['trigger_id']) && $config['trigger_id'] == $trigger_id) {
                    $value = isset($config[$prop_name]) ? $config[$prop_name] : '';
                    break;
                }
            }
            return $value;
        }

        public function get_action_prop_value_by_id($action_id, $prop_name)
        {
            $value = '';
            foreach ($this->actions as $action) {
                $config = $action->get_config();
                if (isset($config['id']) && $config['id'] == $action_id) {
                    $value = isset($config[$prop_name]) ? $config[$prop_name] : '';
                    break;
                }
            }
            return $value;
        }

        public function get_action_integration_label_by_id($action_id)
        {
            $label = '';
            foreach ($this->actions as $action) {
                $config = $action->get_config();
                if (isset($config['id']) && $config['id'] == $action_id) {
                    $integration = $config['integration'];
                    $label = $this->integrations[$integration]->get_config()['integration_label'];
                    break;
                }
            }
            return $label;
        }

        public function get_external_data_by_integration($integration)
        {
            if (!isset($this->integrations[$integration])) {
                return [];
            }

            $class = $this->integrations[$integration];

            if ($integration == 'notion') {
                return $class->notion_api->get_all_databases(array('excluded_props' => 'fields,archived'));
            } else if ($integration == 'mailchimp') {
                return $class->get_all_audiences(array('can_add_fields' => false, 'can_add_tags' => false));
            } else if ($integration == 'hubspot') {
                return $class->get_static_lists();
            } else if ($integration == 'gsheet') {
                return $class->get_spreadsheets();
            }

        }

        public function get_external_data_fields_by_id($integration, $document_id)
        {
            if (!isset($this->integrations[$integration]) || empty($document_id)) {
                return [];
            }

            $class = $this->integrations[$integration];

            if ('notion' == $integration) {
                $database = $class->notion_api->get_database_by_id($document_id);
                return $class->notion_api->get_formatted_fieds($database);
            } else if ($integration == 'mailchimp') {
                return $class->get_all_fields_from_audience($document_id);
            } else if ($integration == 'hubspot') {
                return $class->get_fields();
            } else if ($integration == 'gsheet') {
                return $class->get_sheets_by_spreadsheet_id($document_id);
            } else if ($integration == 'slack' && $document_id == "channels") {
                return $class->slack_api->get_channels();
            } else if ($integration == 'slack' && $document_id == "users") {
                return $class->slack_api->get_users();
            }
        }

        public function get_posts_by_integration($integration)
        {
            $trigger_classs = isset($this->triggers[$integration]) ? $this->triggers[$integration] : null;
            if (is_null($trigger_classs)) {
                return [];
            }
            $posts = $trigger_classs->get_posts();
            return $posts;
        }

        public function get_post_fields_by_id($integration, $document_id)
        {
            $trigger_classs = isset($this->triggers[$integration]) ? $this->triggers[$integration] : null;
            if (is_null($trigger_classs)) {
                return [];
            }

            $fields = $trigger_classs->get_post_fields($document_id);
            return $fields;
        }

        public function add_attachment_to_submission_data($submission_data)
        {
            $file_types = ["upload", "file-upload", "fileupload", "post_image", 'input_image', 'input_file'];
            if (isset($submission_data) && !empty($submission_data)) {
                error_log(' before submission_data : ' . print_r($submission_data, true));

                foreach ($submission_data as $field_key => $field) {
                    if (in_array($field["type"], $file_types) && !empty($field["value"])) {
                        $file_url = self::$instance->get_single_url_from_value($field["value"]);
                        error_log(' file_url : ' . print_r($file_url, true));

                        $field["value"] = self::$instance->upload_file_from_url($file_url);
                    }

                    $submission_data[$field_key] = $field;
                }

                error_log(' after submission_data : ' . print_r($submission_data, true));
                return $submission_data;
            }

            return $submission_data;
        }

        public function get_single_url_from_value($value)
        {
            $url = "";
            $is_comma_separated = false;
            $is_linebreak_separated = false;

            if (!empty($value)) {
                $comma_separated_values = explode(",", $value);
                $linebreak_separated_values = explode("\n", $value);

                $is_comma_separated = is_array($comma_separated_values) && count($comma_separated_values) > 1;
                $is_linebreak_separated = is_array($linebreak_separated_values) && count($linebreak_separated_values) > 1;

                if ($is_comma_separated) {
                    $value = $comma_separated_values[0];
                } else if ($is_linebreak_separated) {
                    $value = $linebreak_separated_values[0];
                }

                $url = trim($value);
            }

            return $url;
        }

        public function upload_file_from_url($url, $title = null)
        {
            require_once ABSPATH . "/wp-load.php";
            require_once ABSPATH . "/wp-admin/includes/image.php";
            require_once ABSPATH . "/wp-admin/includes/file.php";
            require_once ABSPATH . "/wp-admin/includes/media.php";

            // Download url to a temp file
            $tmp = download_url($url);
            if (is_wp_error($tmp)) {
                return false;
            }

            // Get the filename and extension ("photo.png" => "photo", "png")
            $filename = pathinfo($url, PATHINFO_FILENAME);
            $extension = pathinfo($url, PATHINFO_EXTENSION);

            // An extension is required or else WordPress will reject the upload
            if (!$extension) {
                // Look up mime type, example: "/photo.png" -> "image/png"
                $mime = mime_content_type($tmp);
                $mime = is_string($mime) ? sanitize_mime_type($mime) : false;

                // Only allow certain mime types because mime types do not always end in a valid extension (see the .doc example below)
                $mime_extensions = array(
                    // mime_type         => extension (no period)
                    'text/plain' => 'txt',
                    'text/csv' => 'csv',
                    'application/msword' => 'doc',
                    'image/jpg' => 'jpg',
                    'image/jpeg' => 'jpeg',
                    'image/gif' => 'gif',
                    'image/png' => 'png',
                    'video/mp4' => 'mp4',
                );

                if (isset($mime_extensions[$mime])) {
                    // Use the mapped extension
                    $extension = $mime_extensions[$mime];
                } else {
                    // Could not identify extension
                    @unlink($tmp);
                    return false;
                }
            }

            // Upload by "sideloading": "the same way as an uploaded file is handled by media_handle_upload"
            $args = array(
                'name' => "$filename.$extension",
                'tmp_name' => $tmp,
            );

            // Do the upload
            $attachment_id = media_handle_sideload($args, 0, $title);

            // Cleanup temp file
            @unlink($tmp);

            // Error uploading
            if (is_wp_error($attachment_id)) {
                return false;
            }

            // Success, return attachment ID (int)
            return (int) $attachment_id;
        }
    }

}
