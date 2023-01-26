<?php

namespace Tablesome\Includes\Modules\Workflow\Integrations;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Integrations\Mailchimp')) {
    class Mailchimp {
        public function __construct() {
            $this->mailchimp_api = new \Tablesome\Includes\Modules\Workflow\External_Apis\Mailchimp();
        }

        public function add_api($api_key) {
            $this->mailchimp_api->api_key = $api_key;
            update_option($this->mailchimp_api->api_key_option_name, $api_key);
        }

        public function remove_api_data() {
            delete_option($this->mailchimp_api->api_key_option_name);
            delete_option($this->mailchimp_api->api_key_status_option_name);
            delete_option($this->mailchimp_api->api_key_status_message_option_name);
        }

        public function get_config() {
            return array(
                'integration' => 'mailchimp',
                'integration_label' => __('Mailchimp', 'tablesome'),
                'is_active' => $this->mailchimp_api->api_status,
                'is_premium' => false,
                'actions' => array(),
            );
        }

        public function get_collection() {

            $audiences = $this->get_all_audiences(array('can_add_fields' => true, 'can_add_tags' => true));

            $status = $this->mailchimp_api->api_status;
            $message = $this->mailchimp_api->api_status_message;

            $api_not_configured = empty($status) && empty($message);

            if ($api_not_configured) {
                $message = 'Please configure Mailchimp API in Tablesome for this action to work.';
            }

            return array(
                'audiences' => $audiences,
                'api' => array(
                    'status' => $status,
                    'message' => $message,
                    'redirect_url' => admin_url('edit.php?post_type=' . TABLESOME_CPT . '&page=tablesome-settings#tab=integrations/mailchimp'),
                ),
            );
        }

        public function get_all_audiences($args = array()) {
            /** Get all audiences */
            $audiences = $this->mailchimp_api->get_audiences();

            // add additional probs
            $audiences = $this->add_audience_props($audiences, $args);

            return $audiences;
        }

        public function add_audience_props($audiences, $args = array()) {
            if (empty($audiences)) {
                return [];
            }

            $can_add_tags = isset($args['can_add_tags']) && true == $args['can_add_tags'];
            $can_add_fields = isset($args['can_add_fields']) && true == $args['can_add_fields'];

            foreach ($audiences as $index => $audience) {

                if ($can_add_tags) {
                    $audiences[$index]['tags'] = $this->mailchimp_api->get_all_tags_from_audience($audience['id']);
                }

                if ($can_add_fields) {
                    $audiences[$index]['fields'] = $this->get_all_fields_from_audience($audience['id']);
                }
            }

            return $audiences;
        }

        public function get_all_fields_from_audience($audience_id) {
            $fields = array();

            $merge_fields = $this->mailchimp_api->get_fields_from_audience($audience_id);
            /***
             * Important:- Manually, add the email-address field if doesn't exist in the audience fields.
             * As per doc, we can't add a contact without subscriber email-address.
             */
            $email_address_exists = in_array('email_address', array_column($merge_fields, 'tag'));
            if (!$email_address_exists) {
                $fields[] = array(
                    'id' => 'email_address',
                    'label' => __('Email Address', 'tablesome'),
                );
            }

            foreach ($merge_fields as $field) {
                $type = $field['type'];
                $tag = $field['tag'];

                /**
                 * Check the address field from the below url
                 * @see https://mailchimp.com/developer/marketing/docs/merge-fields/#add-merge-data-to-contacts
                 * The address is one type of field in Mailchimp. This field has a collection of properties. like street, city, country, Pincode..
                 * Those props couldn't get from API. It's added manually.
                 */
                if ($type == 'address') {
                    $address_fields = $this->mailchimp_api->get_default_address_fields();
                    $fields = array_merge($fields, $address_fields);
                } else {
                    $fields[] = array(
                        'id' => $tag,
                        'label' => $field['name'],
                    );
                }
            }
            return $fields;
        }
    }
}