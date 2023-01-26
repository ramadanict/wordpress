<?php

namespace Tablesome\Components\System_Info;

if (!class_exists('\Tablesome\Components\System_Info\View')) {
    class View
    {
        public function get_view($collection_props)
        {
            $raw_code_content = isset($collection_props['raw_code_content']) ? $collection_props['raw_code_content'] : '';
            $html = '';
            $html .= '<div class="tablesome__systemInfo">';
            $html .= $this->get_header();
            $html .= $this->get_system_info_html_content($collection_props);
            $html .= $this->get_raw_code_html_content($raw_code_content);
            $html .= '</div>';

            return $html;
        }

        public function get_header()
        {
            return '<h3 class="wp-heading-inline">' . __("System Info", "tablesome") . '</h3>';
        }

        private function get_system_info_html_content($collection_props)
        {
            $html = '';
            $html .= $this->get_env_summary_content($collection_props['server']);
            $html .= $this->get_env_summary_content($collection_props['wordpress']);
            $html .= $this->get_env_summary_content($collection_props['theme']);
            $html .= $this->get_env_summary_content($collection_props['user']);
            $html .= $this->get_env_summary_content($collection_props['active_plugins']);
            $html .= $this->get_env_summary_content($collection_props['inactive_plugins']);
            return $html;
        }

        private function get_env_summary_content($collection)
        {
            $heading = $collection['label'];

            $html = '';
            $html .= '<table class="wp-list-table widefat fixed striped table-view-list">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>' . $heading . '</th>';
            $html .= '<th></th>';
            $html .= '</tr>';
            $html .= '</thead>';
            foreach ($collection['data'] as $key => $item) {
                $html .= '<tr>';
                $html .= '<td>' . $item['label'] . '</td>';
                $html .= '<td>' . $item['value'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '<tbody>';
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '<br>';
            return $html;
        }

        public function get_raw_code_html_content($raw_code_content)
        {
            $html = '';
            $html .= '<div class="tablesome__systemInfo--wrapper">';
            $html .= '<h3>' . __('Copy & Paste site Info') . '</h3>';
            $html .= '<label>' . __('You can copy the below info as simple text with Ctrl+C / Ctrl+V:', 'tablesome') . '</label>';
            $html .= '<textarea class="tablesome__systemInfo--textContent" readonly>' . $raw_code_content . '</textarea>';
            $html .= $this->get_download_button_html_content();
            $html .= '</div>';
            return $html;
        }

        public function get_download_button_html_content()
        {
            $html = '';
            $html = '<div style="margin-top: 5px;">';
            $html .= '<input type="button" class="button button-primary tablesome__systemInfo--downloadButton" value="' . __('Download System Info', 'tablesome') . '">';
            $html .= '</div>';
            return $html;
        }

    }
}