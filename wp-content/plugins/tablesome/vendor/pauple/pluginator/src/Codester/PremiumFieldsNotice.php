<?php

namespace Pauple\Pluginator\Codester;

if (!class_exists('\Pauple\Pluginator\Codester\PremiumFieldsNotice')) {
    class PremiumFieldsNotice
    {

        public $collections = array();

        public $notice = array(
            'fields' => array(),
            'is_premium' => false,
            'collections' => array(),
            'plugin_slug' => 'helpie',
            'disabled_class_name' => '',
            'premium_fields_keys' => array(),
            'subtitle' => '<span style="color: #5cb85c; font-weight: 600;">* Pro Feature</span>',
        );

        public function __construct($collections)
        {
            $this->collections = $collections;
            // $this->validate($collections);
            $this->set();
        }

        public function set()
        {

            $this->notice['fields'] = isset($this->collections['fields']) ? $this->collections['fields'] : $this->fields;

            $this->notice['premium_fields_keys'] = isset($this->collections['premium_fields_keys']) ? $this->collections['premium_fields_keys'] : [];

            $this->notice['is_premium'] = isset($this->collections['is_premium']) && $this->collections['is_premium'] == true ? true : false;

            $this->notice['plugin_slug'] = isset($this->collections['plugin_slug']) && !empty($this->collections['plugin_slug']) ? $this->collections['plugin_slug'] : 'helpie';

            $this->notice['disabled_class_name'] = isset($this->collections['disabled_class_name']) ? $this->collections['disabled_class_name'] : $this->notice['plugin_slug'] . '-disabled';

            $this->notice['subtitle'] = $this->pro_feature_sub_title();
        }

        public function pro_feature_sub_title()
        {
            return '<span style="color: #5cb85c; font-weight: 600;">* Pro Feature</span>';
        }

        public function get_fields_with_notice()
        {
            $fields = $this->notice['fields'];
            if ($this->notice['is_premium'] == true) {
                return $fields;
            }

            if (empty($fields)) {
                return [];
            }

            foreach ($fields as $index => $field) {

                $field_id = isset($field['id']) && !empty($field['id']) ? $field['id'] : '';

                if (empty($field_id) || !in_array($field_id, $this->notice['premium_fields_keys'])) {
                    continue;
                }

                $classes = $this->get_field_classes($field);

                $classes = $classes . " " . $this->notice['disabled_class_name'];

                $fields[$index]['class'] = $classes;
                $fields[$index]['subtitle'] = $this->notice['subtitle'];

                $fields[$index]['attributes'] = array(
                    'disabled' => true,
                    'readonly' => 'readonly',
                );

                /** STOP!, applying the premium fields styles in free plan users.  */
                $has_output_attribute = isset($field['output']) && !empty($field['output']);
                if ($has_output_attribute) {
                    $fields[$index]['output'] = [];
                }

            }
            $this->notice['fields'] = $fields;
            return $fields;
        }

        private function get_field_classes($field)
        {
            $classes = isset($field['class']) ? $field['class'] : '';
            return !empty($classes) ? $classes : '';
        }

    }
}