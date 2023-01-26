<?php

namespace Tablesome\Components\CellTypes;

if (!class_exists('\Tablesome\Components\CellTypes\Email')) {
    class Email
    {
        public function __construct()
        {
            add_filter("tablesome_get_cell_data", [$this, 'get_email_data']);
        }

        public function get_email_data($cell)
        {
            if ($cell['type'] != 'email' || empty(trim($cell['value']))) {
                return $cell;
            }

            $cell["html"] = $cell['value'];
            if (filter_var(trim($cell['value']), FILTER_VALIDATE_EMAIL)) {
                $cell["html"] = '<a href="mailto:' . $cell['value'] . '?subject = Feedback&body = Message">' . $cell['value'] . '</a>';
            }

            return $cell;
        }
    } // END CLASS
}
