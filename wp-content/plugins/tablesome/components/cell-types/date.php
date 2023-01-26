<?php

namespace Tablesome\Components\CellTypes;

if (!class_exists('\Tablesome\Components\CellTypes\Date')) {
    class Date
    {
        public function __construct()
        {
            add_filter("tablesome_get_cell_data", [$this, 'get_date_data']);
        }

        public function get_date_data($cell)
        {
            if ($cell['type'] != 'date' || empty(trim($cell['value'])) || strlen($cell["value"]) < 9) {
                return $cell;
            }

            // Given cellValue came from JS as a string, We are converting that to numeric value.
            settype($cell["value"], "double");
            $cell["html"] = isset($cell["html"]) && !empty($cell["html"]) ? $cell["html"] : "";
            
            return $cell;
        }
    } // END CLASS
}
