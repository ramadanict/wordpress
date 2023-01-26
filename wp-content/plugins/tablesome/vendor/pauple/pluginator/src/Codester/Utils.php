<?php

namespace Pauple\Pluginator\Codester;

if (!class_exists('\Pauple\Pluginator\Codester\Utils')) {
    class Utils
    {
        public function add_premium_field_notice($collections)
        {
            $premium_notice = new \Pauple\Pluginator\Codester\PremiumFieldsNotice($collections);
            $fields = $premium_notice->get_fields_with_notice();
            return $fields;
        }
    }
}