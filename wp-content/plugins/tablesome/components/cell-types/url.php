<?php

namespace Tablesome\Components\CellTypes;

if (!class_exists('\Tablesome\Components\CellTypes\URL')) {
    class URL
    {
        public function __construct()
        {

            add_filter("tablesome_get_cell_data", [$this, 'get_url_data']);
        }

        public function get_url_data($cell)
        {
            if ($cell['type'] != 'url' || empty(trim($cell['value']))) {
                return $cell;
            }

            $link = wp_http_validate_url($cell['value']) ? $cell['value'] : '//' . $cell['value'];

            $link_text = isset($cell['linkText']) && !empty($cell['linkText']) ? $cell['linkText'] : $cell['value'];
            $link_text = url_shorten($link_text, $length = 40);

            $cell["html"] = '<a class="tablesome__url" href="' . $link . '" target="_blank">' . $link_text . '</a>';

            return $cell;
        }
    } // END CLASS
}
