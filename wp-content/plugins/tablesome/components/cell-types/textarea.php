<?php

namespace Tablesome\Components\CellTypes;

if (!class_exists('\Tablesome\Components\CellTypes\Textarea')) {
    class Textarea
    {
        public function __construct()
        {
            add_filter("tablesome_get_cell_data", [$this, 'get_textarea_data']);
        }

        public function get_textarea_data($cell)
        {

            if ($cell['type'] != 'textarea') {
                return $cell;
            }

            // error_log('cell : ' . print_r($cell, true));

            $escaped_value = html_entity_decode($cell["value"]);
            $cell["value"] = $escaped_value;
            // $cell["html"] = $cell["html"];

            return $cell;
        }

    } // END CLASS
}
