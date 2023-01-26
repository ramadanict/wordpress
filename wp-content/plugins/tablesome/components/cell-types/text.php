<?php

namespace Tablesome\Components\CellTypes;

if (!class_exists('\Tablesome\Components\CellTypes\Text')) {
    class Text
    {
        public function __construct()
        {
            add_filter("tablesome_get_cell_data", [$this, 'get_text_data']);
        }

        public function get_text_data($cell)
        {
            if ($cell['type'] != 'text') {
                return $cell;
            }

            $escaped_value = (isset($cell["value"]) && !empty($cell["value"]) && gettype($cell["value"]) == "string") ? html_entity_decode($cell["value"]) : "";
            $cell["value"] = $escaped_value;
            $cell["html"] = $escaped_value;

            return $cell;
        }

    } // END CLASS
}
