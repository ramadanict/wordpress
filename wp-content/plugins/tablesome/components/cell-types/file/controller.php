<?php

namespace Tablesome\Components\CellTypes\File;

if (!class_exists('\Tablesome\Components\CellTypes\File\Controller')) {
    class Controller
    {
        public function __construct()
        {
            $this->model = new \Tablesome\Components\CellTypes\File\Model();
            $this->view = new \Tablesome\Components\CellTypes\File\View();

            add_filter("tablesome_get_cell_data", [$this, 'get_file_data']);
        }

        public function get_file_extra_html($extra_html, $table_mode)
        {
            if ($table_mode !== 'read-only') {
                $html = $this->view->get_extra_view();
                $extra_html .= $html;
            }

            return $extra_html;
        }

        public function get_file_data($cell)
        {
            if (empty($cell['value']) || $cell['type'] != 'file') {
                return $cell;
            }

            $data = $this->model->get_media_data($cell);
            // error_log('data : ' . print_r($data, true));

            $cell['html'] = $this->view->get_media_view($data);
            if (isset($data['attachment']) && !empty($data['attachment'])) {
                $cell['attachment'] = $data['attachment'];
            }

            return $cell;
        }

    } // END CLASS
}
