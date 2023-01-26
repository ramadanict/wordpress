<?php

namespace Tablesome\Includes\Modules\Workflow\Traits;

if (!trait_exists('\Tablesome\Includes\Modules\Workflow\Traits\Placeholder')) {
    trait Placeholder
    {

        public function getPlaceholdersFromKeyValues($data)
        {
            $placeholders = [];

            foreach ($data as $fieldName => $fieldValue) {
                $placeholderName = $this->getPlaceholderName($fieldName);
                $placeholders["{{{$placeholderName}}}"] = $fieldValue;
            }

            return $placeholders;
        }

        public function getPlaceholdersFromTriggerSourceData($triggerSourceData)
        {
            $data = [];

            $triggerData = isset($triggerSourceData['data']) ? $triggerSourceData['data'] : [];
            if (empty($triggerData)) {
                return [];
            }

            // First convert the trigger data into the formatted data which is used to create placeholders data
            foreach ($triggerData as $fieldName => $fieldData) {
                $data[$fieldName] = isset($fieldData['value']) ? $fieldData['value'] : '';
            }

            return $this->getPlaceholdersFromKeyValues($data);
        }

        public function getPlaceholderName($name)
        {
            $name = preg_replace("/[^a-zA-Z0-9]/", "_", $name);
            return strtolower($name);
        }

        public function applyPlaceholders($placeholders, $content)
        {
            foreach ($placeholders as $name => $value) {
                $content = str_replace($name, $value, $content);
            }
            return $content;
        }
    }
}