<?php

namespace Tablesome\Components\CellTypes;

if (!class_exists('\Tablesome\Components\CellTypes\Number')) {
    class Number
    {
        public function __construct()
        {
            // add_filter("tablesome_get_cell_data", [$this, 'get_number_data']);
        }

        public function get_number_data($cell)
        {
            if ($cell['type'] != 'number') {
                return $cell;
            }

            // error_log('!!! Number Cell !!!');

            return $cell;
        }
    } // END CLASS
}
