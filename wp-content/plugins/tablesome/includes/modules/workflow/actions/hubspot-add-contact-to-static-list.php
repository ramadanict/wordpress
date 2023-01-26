<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use  Tablesome\Includes\Modules\Workflow\Action ;
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( '\\Tablesome\\Includes\\Modules\\Workflow\\Actions\\Hubspot_Add_Contact_To_Static_List' ) ) {
    class Hubspot_Add_Contact_To_Static_List extends Action
    {
        public  $hubspot_api ;
        public  $action_meta ;
        public  $trigger_class ;
        public  $trigger_instance ;
        public  $smart_field_values ;
        public  $trigger_source_data ;
        public function __construct()
        {
            $this->hubspot_api = new \Tablesome\Includes\Modules\Workflow\External_Apis\Hubspot();
        }
        
        public function get_config()
        {
            return array(
                'id'          => 11,
                'name'        => 'hubspot_add_contact_to_static_list',
                'label'       => __( 'Add Contact to Static List', 'tablesome' ),
                'integration' => 'hubspot',
                'is_premium'  => true,
            );
        }
        
        public function do_action( $trigger_class, $trigger_instance )
        {
            error_log( '*** Hubspot Add Contact to Static List Action Called  ***' );
            $this->bind_props( $trigger_class, $trigger_instance );
            if ( !$this->can_add_contact() ) {
                return;
            }
            $this->add_contact_to_list( $this->get_contact_properties() );
        }
        
        public function can_add_contact()
        {
            return false;
            $map_fields = ( isset( $this->action_meta['map_fields'] ) ? $this->action_meta['map_fields'] : [] );
            if ( empty($map_fields) ) {
                return false;
            }
            $email_address_configured = $this->is_email_address_configured( $map_fields );
            if ( !$email_address_configured ) {
                return false;
            }
            return true;
        }
        
        public function get_contact_properties()
        {
            $properties = [];
            foreach ( $this->action_meta["map_fields"] as $field ) {
                if ( !isset( $field['destination_field']['id'] ) || empty($field['source_field']['id']) ) {
                    continue;
                }
                $source = [
                    "id"          => ( isset( $field['source_field']['id'] ) ? $field['source_field']['id'] : '' ),
                    "object_type" => ( isset( $field['source_field']['object_type'] ) ? $field['source_field']['object_type'] : '' ),
                    "value"       => ( isset( $field['destination_field']['value'] ) ? $field['destination_field']['value'] : '' ),
                ];
                $property_value = $this->get_value( $source );
                if ( empty($property_value) ) {
                    continue;
                }
                $properties[$field['destination_field']['id']] = $property_value;
            }
            error_log( 'contact properties : ' . print_r( $properties, true ) );
            return $properties;
        }
        
        public function add_contact_to_list( $contact_properties )
        {
            $args = [
                "list_id"    => $this->action_meta["list_id"],
                "properties" => $contact_properties,
            ];
            $this->hubspot_api->add_contact_to_static_list( $args );
        }
        
        private function get_value( $source )
        {
            $value = "";
            
            if ( $source["object_type"] == "trigger_source" ) {
                $value = ( isset( $this->trigger_source_data[$source["id"]] ) ? $this->trigger_source_data[$source["id"]]["value"] : $value );
            } else {
                
                if ( $source["object_type"] == "trigger_smart_fields" ) {
                    $value = $this->smart_field_values[$source["id"]];
                } else {
                    $value = $source["value"];
                }
            
            }
            
            return $value;
        }
        
        private function is_email_address_configured( $map_fields )
        {
            $is_configured = false;
            foreach ( $map_fields as $map_field ) {
                $field_id = ( isset( $map_field['destination_field']["id"] ) ? $map_field['destination_field']["id"] : '' );
                
                if ( $field_id == 'email' ) {
                    $is_configured = true;
                    break;
                }
            
            }
            return $is_configured;
        }
        
        private function bind_props( $trigger_class, $trigger_instance )
        {
            $this->trigger_class = $trigger_class;
            $this->trigger_instance = $trigger_instance;
            $this->trigger_source_data = $this->trigger_class->trigger_source_data['data'];
            $this->action_meta = ( isset( $this->trigger_instance['action_meta'] ) ? $this->trigger_instance['action_meta'] : [] );
            $this->smart_field_values = get_tablesome_smart_field_values();
        }
    
    }
}