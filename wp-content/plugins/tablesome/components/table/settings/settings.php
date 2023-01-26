<?php

namespace Tablesome\Components\Table\Settings;

use  Tablesome\Components\Table\Settings\Config\Display ;
use  Tablesome\Components\Table\Settings\Config\Style ;
if ( !class_exists( '\\Tablesome\\Components\\Table\\Settings\\Settings' ) ) {
    class Settings
    {
        public function __construct()
        {
        }
        
        public static function get_config()
        {
            $style = new Style();
            $display = new Display();
            return [
                "display" => [
                "layout" => $display::get_layout(),
                "field"  => $display::get_fields(),
            ],
                "style"   => [
                "layout"       => $style::get_layout(),
                "field"        => $style::get_fields(),
                "future_field" => $style::get_future_fields(),
            ],
            ];
        }
        
        public static function get_fields_defaults( $section )
        {
            $config = self::get_config();
            $defaults = [];
            
            if ( !isset( $config[$section] ) ) {
                error_log( $section . ' not found in existing config ' );
                return $defaults;
            }
            
            foreach ( $config[$section]["field"] as $field_key => $field ) {
                
                if ( !isset( $field["default"] ) ) {
                    error_log( $field["id"] . ' doesn\'t have a default value ' );
                    continue;
                }
                
                $defaults[$field["id"]] = $field["default"];
            }
            return $defaults;
        }
        
        public static function get_table_css( $table_id, $style_meta )
        {
            if ( !isset( $style_meta ) || empty($style_meta) ) {
                return "";
            }
            $config = self::get_config()["style"];
            $config_fields = array_merge( $config["field"], $config["future_field"] );
            $css_properties = [
                "desktop" => [],
                "mobile"  => [],
            ];
            $fields = [];
            foreach ( $config_fields as $field ) {
                foreach ( $style_meta as $meta_field_id => $meta_field_value ) {
                    
                    if ( isset( $field["id"] ) && $field["id"] == $meta_field_id ) {
                        $field["value"] = $meta_field_value;
                        $fields[] = $field;
                    }
                
                }
            }
            if ( empty($fields) ) {
                return "";
            }
            foreach ( $fields as $field ) {
                if ( !isset( $field["target"] ) || empty($field["target"]) ) {
                    continue;
                }
                
                if ( !isset( $field["extra"]["breakpoint"] ) ) {
                    error_log( $field["id"] . ' doesn\'t have a breakpoint' );
                    continue;
                }
                
                
                if ( !isset( $field["extra"]["css_property_name"] ) ) {
                    error_log( $field["id"] . ' doesn\'t have a css_property_name' );
                    continue;
                }
                
                $cannot_display = !self::get_dependency( $style_meta, $field );
                if ( $cannot_display ) {
                    // error_log($field["id"] . ' is a dependent field');
                    continue;
                }
                // Adding Typography
                if ( isset( $field["extra"]["css_property_name"] ) && $field["extra"]["css_property_name"] == "font-family" ) {
                    self::add_typography( $style_meta[$field["id"]] );
                }
                $device = $field["extra"]["breakpoint"];
                $targets = explode( ",", $field["target"] );
                $property_name = $field["extra"]["css_property_name"];
                $property_value = ( isset( $field["extra"]["unit"] ) && !empty($field["extra"]["unit"]) ? $style_meta[$field["id"]] . $field["extra"]["unit"] : $style_meta[$field["id"]] );
                // skip when empty property value
                if ( !isset( $property_value ) || empty($property_value) ) {
                    continue;
                }
                foreach ( $targets as $target ) {
                    $css_properties[$device][$target][$property_name] = $property_value;
                }
            }
            // error_log(' css_properties : ' . print_r($css_properties, true));
            return self::get_generated_css( $table_id, $css_properties );
        }
        
        // below get_table_css_v1 method is Not used
        public static function get_table_css_v1( $table_id, $style_meta )
        {
            $config = self::get_config()["style"];
            $fields = array_merge( $config["field"], $config["future_field"] );
            $css_properties = [
                "desktop" => [],
                "mobile"  => [],
            ];
            foreach ( $fields as $field ) {
                if ( !isset( $field["target"] ) || empty($field["target"]) ) {
                    continue;
                }
                
                if ( !isset( $field["extra"]["breakpoint"] ) ) {
                    error_log( $field["id"] . ' doesn\'t have a breakpoint' );
                    continue;
                }
                
                
                if ( !isset( $field["extra"]["css_property_name"] ) ) {
                    error_log( $field["id"] . ' doesn\'t have a css_property_name' );
                    continue;
                }
                
                $cannot_display = !self::get_dependency( $style_meta, $field );
                if ( $cannot_display ) {
                    // error_log($field["id"] . ' is a dependent field');
                    continue;
                }
                // Adding Typography
                if ( isset( $field["extra"]["css_property_name"] ) && $field["extra"]["css_property_name"] == "font-family" ) {
                    self::add_typography( $style_meta[$field["id"]] );
                }
                $device = $field["extra"]["breakpoint"];
                $targets = explode( ",", $field["target"] );
                $property_name = $field["extra"]["css_property_name"];
                $property_value = ( isset( $field["extra"]["unit"] ) && !empty($field["extra"]["unit"]) ? $style_meta[$field["id"]] . $field["extra"]["unit"] : $style_meta[$field["id"]] );
                foreach ( $targets as $target ) {
                    $css_properties[$device][$target][$property_name] = $property_value;
                }
            }
            // error_log(' css_properties : ' . print_r($css_properties, true));
            return self::get_generated_css( $table_id, $css_properties );
        }
        
        protected static function get_generated_css( $table_id, $css_properties )
        {
            $css = "";
            $prefix = ".tablesome__container.tablesome__container--" . $table_id . " ";
            unset( $css_properties["mobile"] );
            foreach ( $css_properties as $device => $elements ) {
                if ( $device == "mobile" ) {
                    $css .= "@media only screen and (max-width: 640px) {";
                }
                foreach ( $elements as $target => $properties ) {
                    $css_block = "";
                    foreach ( $properties as $property_name => $property_value ) {
                        $css_block .= $property_name . ': ' . $property_value . ';';
                    }
                    $css .= " " . $prefix . $target . "{" . $css_block . "}";
                }
                if ( $device == "mobile" ) {
                    $css .= "}";
                }
            }
            return $css;
        }
        
        protected static function add_typography( $typography )
        {
            if ( empty($typography) ) {
                return;
            }
            $safe_fonts = [
                'Arial',
                'Arial Black',
                'Helvetica',
                'Times New Roman',
                'Courier New',
                'Tahoma',
                'Verdana',
                'Impact',
                'Trebuchet MS',
                'Comic Sans MS',
                'Lucida Console',
                'Lucida Sans Unicode',
                'Georgia, serif',
                'Palatino Linotype'
            ];
            if ( in_array( $typography, $safe_fonts ) ) {
                return;
            }
            $query = [];
            $fonts = [];
            $fonts[] = $typography;
            if ( !empty($fonts) ) {
                $query['family'] = implode( '%7C', $fonts );
            }
            $query['display'] = 'swap';
            wp_enqueue_style(
                'csf-google-web-fonts',
                esc_url( add_query_arg( $query, '//fonts.googleapis.com/css' ) ),
                array(),
                null
            );
        }
        
        private static function get_dependency( $meta, $field )
        {
            $can_display = true;
            if ( isset( $field["dependency"] ) && !empty($field["dependency"]) ) {
                foreach ( $field["dependency"] as $dependency ) {
                    if ( $meta[$dependency["id"]] != $dependency["value"] ) {
                        $can_display = false;
                    }
                }
            }
            return $can_display;
        }
    
    }
}