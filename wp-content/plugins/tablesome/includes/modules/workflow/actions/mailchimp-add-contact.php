<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use  Tablesome\Includes\Modules\Workflow\Action ;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( '\\Tablesome\\Includes\\Modules\\Workflow\\Actions\\Mailchimp_Add_Contact' ) ) {
    class Mailchimp_Add_Contact extends Action
    {
        public function __construct()
        {
            $this->mailchimp_api = new \Tablesome\Includes\Modules\Workflow\External_Apis\Mailchimp();
        }
        
        public function get_config()
        {
            return array(
                'id'          => 2,
                'name'        => 'add_contact',
                'label'       => __( 'Add Contact', 'tablesome' ),
                'integration' => 'mailchimp',
                'is_premium'  => false,
            );
        }
        
        public function do_action( $trigger_class, $trigger_instance )
        {
            error_log( '*** Mailchimp Add Contact Action Called  ***' );
            $this->trigger_class = $trigger_class;
            $this->trigger_instance = $trigger_instance;
            $action_meta = $this->trigger_instance["action_meta"];
            $data = ( isset( $this->trigger_class->trigger_source_data['data'] ) ? $this->trigger_class->trigger_source_data['data'] : [] );
            $can_add_contact = $this->can_add_contact( $action_meta, $data );
            if ( !$can_add_contact ) {
                return;
            }
            $subscriber_data = $this->get_subscriber( $action_meta, $data );
            $this->add_contact_to_the_lists( $action_meta, $subscriber_data );
        }
        
        public function can_add_contact( $action_meta, $data )
        {
            $list_id = ( isset( $action_meta['list_id'] ) ? $action_meta['list_id'] : '' );
            if ( empty($list_id) ) {
                return false;
            }
            $match_fields = ( isset( $action_meta['match_fields'] ) ? $action_meta['match_fields'] : [] );
            if ( empty($match_fields) ) {
                return false;
            }
            $email_address_configured = $this->is_email_address_configured( $match_fields );
            if ( !$email_address_configured ) {
                return false;
            }
            return true;
        }
        
        public function is_email_address_configured( $match_fields )
        {
            $is_configured = false;
            foreach ( $match_fields as $match_field ) {
                $type_name = ( isset( $match_field['type_name'] ) ? $match_field['type_name'] : '' );
                $field_name = ( isset( $match_field['field_name'] ) ? $match_field['field_name'] : '' );
                $is_configured = $type_name == 'email_address' && $field_name;
                if ( $is_configured ) {
                    break;
                }
            }
            return $is_configured;
        }
        
        public function is_conditions_are_valid( $conditions, $data )
        {
            $is_valid = true;
            if ( empty($conditions) ) {
                return true;
            }
            $conditional_statement_is_true = true;
            foreach ( $conditions as $condition ) {
                $field = ( isset( $condition['field'] ) ? $condition['field'] : '' );
                $operator = ( isset( $condition['operator'] ) ? $condition['operator'] : '' );
                $value = ( isset( $condition['value'] ) ? $condition['value'] : '' );
                if ( empty($field) || empty($operator) ) {
                    continue;
                }
                $trigger_value = ( isset( $data[$field]['value'] ) ? $data[$field]['value'] : '' );
                $to_be_continued = !isset( $data[$field]['value'] ) || !$conditional_statement_is_true;
                
                if ( $to_be_continued ) {
                    $is_valid = false;
                    continue;
                }
                
                if ( $this->trigger_class->get_config()['integration'] == 'wpforms' && !empty($value) && intval( $value ) ) {
                    $trigger_value = $this->trigger_class->get_field_option_id_by_value( array(
                        'field'         => $field,
                        'value'         => $value,
                        'trigger_value' => $trigger_value,
                    ) );
                }
                
                if ( $operator == 'equal_to' ) {
                    $is_valid = $value == $trigger_value;
                } else {
                    if ( $operator == 'not_equal_to' ) {
                        $is_valid = $value != $trigger_value;
                    }
                }
                
                $conditional_statement_is_true = $is_valid;
            }
            return $is_valid;
        }
        
        public function get_subscriber( $action_meta, $data )
        {
            $subscriber_data = array();
            $match_fields = ( isset( $action_meta['match_fields'] ) ? $action_meta['match_fields'] : [] );
            foreach ( $match_fields as $match_field ) {
                $type_name = ( isset( $match_field['type_name'] ) ? $match_field['type_name'] : '' );
                $field_name = ( isset( $match_field['field_name'] ) ? $match_field['field_name'] : '' );
                if ( empty($type_name) || empty($field_name) ) {
                    continue;
                }
                $value = ( isset( $data[$field_name]['value'] ) ? $data[$field_name]['value'] : '' );
                $subscriber_data[$type_name] = $value;
            }
            return $subscriber_data;
        }
        
        public function add_contact_to_the_lists( $action_meta, $subscriber_data )
        {
            if ( isset( $this->api_key ) && empty($this->api_key) ) {
                return;
            }
            $email_address = ( isset( $subscriber_data['email_address'] ) ? $subscriber_data['email_address'] : '' );
            // Return if empty
            if ( empty($email_address) || !is_valid_tablesome_email( $email_address ) ) {
                return;
            }
            $formatted_subscriber_data = $this->get_formatted_subscriber_data( $subscriber_data );
            $contact_added = $this->mailchimp_api->add_contact( $action_meta['list_id'], $email_address, $formatted_subscriber_data );
            return $contact_added;
        }
        
        public function get_formatted_subscriber_data( $data )
        {
            /**
             * Ref Url's
             * https://mailchimp.com/developer/marketing/docs/merge-fields/#structure
             * https://mailchimp.com/developer/marketing/docs/merge-fields/#add-merge-data-to-contacts
             */
            $available_address_tag_fields = array_column( $this->mailchimp_api->get_default_address_fields(), 'id' );
            $subscriber_data = array(
                'status'        => 'subscribed',
                'email_address' => $data['email_address'],
            );
            foreach ( $data as $tag => $value ) {
                $is_address_field_props = in_array( $tag, $available_address_tag_fields );
                if ( $is_address_field_props ) {
                    $subscriber_data['ADDRESS1'][$tag] = $value;
                }
                if ( !$is_address_field_props && $tag != 'email_address' ) {
                    $subscriber_data['merge_fields'][$tag] = $value;
                }
            }
            return $subscriber_data;
        }
        
        public function get_tag_names_by_ids( $mailchimp_meta_data )
        {
            $selected_tags = ( isset( $mailchimp_meta_data['tags'] ) ? $mailchimp_meta_data['tags'] : [] );
            $names = array();
            if ( empty($selected_tags) ) {
                return $names;
            }
            $all_tags = $this->mailchimp_api->get_all_tags_from_audience( $mailchimp_meta_data['list_id'] );
            if ( empty($all_tags) ) {
                return $names;
            }
            foreach ( $all_tags as $tag ) {
                $id = $tag['id'];
                if ( in_array( $id, $selected_tags ) ) {
                    $names[] = $tag['name'];
                }
            }
            return $names;
        }
    
    }
}