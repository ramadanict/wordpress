<?php

namespace Tablesome\Components\Import;

if (!class_exists('\Tablesome\Components\Import\View')) {
    class View
    {

        public function get_import_page_content()
        {
            $html = '<h3>' . __("Importing the Data", "tablesome") . '</h3>';

            // if (!version_compare(PHP_VERSION, '7.2', '>=')) {
            //     $html .= $this->get_import_notice();
            //     return $html;
            // }

            $html .= '<form id="tablesome-importing-data-form" enctype="multipart/form-data">';
            $html .= $this->get_hidden_input_fields();

            $html .= '<div class="tablesome__fields">';
            $html .= '<label>' . __("Title", "tablesome") . '</label>';
            $html .= '<input type="text" name="table_title" id="table_title" value="" placeholder="' . __("Table Name", "tablesome") . '">';
            $html .= '</div>';

            $html .= '<div class="tablesome__fields">';
            $html .= '<label>' . __("File", "tablesome") . '</label>';
            $html .= '<div class="tablesome__fields--file-group">';
            $html .= '<input type="file" id="file_attachment" name="file_attachment">';
            $html .= '</div>';
            $html .= '</div>';

            $html .= '<div class="tablesome__fields">';
            $html .= '<input type="checkbox" name="read_first_row_as_column" id="read_first_row_as_column" value="1" checked>';
            $html .= '<label for="read_first_row_as_column">' . __("Take the first row as the column header of the table", "tablesome") . '</label>';
            $html .= '</div>';

            // $html .= '<div class="tablesome__fields">';
            // $html .= '<input type="checkbox" name="read_empty_rows" id="read_empty_rows" value="1" checked>';
            // $html .= '<label for="read_empty_rows">' . __("Read the empty rows of the file", "tablesome") . '</label>';
            // $html .= '</div>';

            $html .= $this->get_notes_content();
            $html .= '<br>';
            $html .= '<div class="tablesome_cpt__footer">';
            $html .= '<div class="tablesome__button--wrapper">';
            $html .= '<input type="submit" class="tablesome__button--submit" value="' . __("Load Data", "tablesome") . '">';
            $html .= '</div>';
            $html .= '<div class="tablesome__spinner"><div class="tablesome__loader" /></div>';
            $html .= '</div>';

            return $html;
        }

        public function get_hidden_input_fields()
        {
            $html = '';
            $html .= '<input type="hidden" name="action" value="importing_data">';
            return $html;
        }

        public function get_notes_content()
        {
            $html = '';
            $html .= '<p class="tablesome__notes">' . __("Notes", "tablesome") . '</p>';
            $html .= '<ul class="tablesome__notes-list">';
            $html .= '<li>' . __("As of now, it would load only up to 10,000 records. We are working to load more records and will be added soon.", "tablesome") . '</li>';
            $html .= '<li>' . __("As of now, it supports only XLSX and CSV format.", "tablesome") . '</li>';
            $html .= '</ul>';
            return $html;
        }

        public function get_import_notice()
        {
            $html = '<div class="notice notice-error">';
            $html .= '<p>Table import will not be working on sites using PHP versions below PHP 7.2.</p>';
            $html .= '<p>If you want to make use of the import feature, please update your site to PHP 7.2 or above.</p>';
            $html .= '<p>PHP versions recommended by WordPress are PHP 7.4 or greater.</p>';
            $html .= '</div>';

            return $html;
        }
    }
}
