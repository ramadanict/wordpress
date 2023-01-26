<?php

namespace Tablesome\Components\CellTypes;

if (!class_exists('\Tablesome\Components\CellTypes\Button')) {
    class Button
    {
        public function __construct()
        {
            add_filter("tablesome_get_cell_data", [$this, 'get_button_data']);
        }

        public function get_button_data($cell)
        {
            if ($cell['type'] != 'button' || empty(trim($cell['value']))) {
                return $cell;
            }

            $link = wp_http_validate_url($cell['value']) ? $cell['value'] : '//' . $cell['value'];

            $link_text = isset($cell['linkText']) && !empty($cell['linkText']) ? $cell['linkText'] : $cell['value'];
            $link_text = url_shorten($link_text, $length = 40);

            $cell["html"] = '<a class="tablesome__button" href="' . $link . '" target="_blank">' . $this->get_button($link_text) . '</a>';

            return $cell;
        }

        public function get_button($link_text)
        {
            return '<button type="submit" class="button">' . $link_text . '</button>';
        }
    } // END CLASS
}
