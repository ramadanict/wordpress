<?php

namespace Tablesome\Components\CellTypes\File;

if (!class_exists('Tablesome\Components\CellTypes\File\View')) {
    class View
    {
        public function get_extra_view()
        {
            $html = '<div class="tablesome__inputMedia tablesome__inputMedia--inactive">';
            $html .= $this->get_image();
            $html .= $this->get_video();
            $html .= $this->get_audio();
            $html .= $this->get_media_link();
            $html .= '<div class="tablesome__inputMedia--button"><span class="dashicons dashicons-plus-alt2"></span> Add Media</div> ';
            $html .= '</div>';

            return $html;
        }

        public function get_media_view($data)
        {
            $html = '';

            if (!isset($data['type']) && empty($data['type'])) {
                return $html = '';
            }

            switch ($data['type']) {
                case 'image':
                    $html .= $this->get_image($data, true);
                    break;

                case 'audio':
                    $html .= $this->get_audio($data['url'], $data['mime_type']);
                    break;

                case 'video':
                    $html .= $this->get_video($data['url'], $data['mime_type']);
                    break;

                default:
                    $html .= $this->get_media_link($data['url'], $data['name']);
                    break;
            }

            // error_log('html : ' . $html);

            return $html;
        }

        public function get_image($data, $is_preview = false)
        {
            $url = isset($data["url"]) && !empty($data["url"]) ? $data["url"] : "";
            $link = isset($data["link"]) && !empty($data["link"]) ? $data["link"] : "";

            $html = '<img  class="tablesome__inputMediaPreview tablesome__inputMediaPreview--image" src="' . $url . '" />';

            if ($is_preview) {
                $html = '<a href="' . $link . '" class="tablesome__inputMediaPreview--link" target="_blank">' . $html . '</a>';
            }

            return $html;
        }

        public function get_video($url = '', $mime_type = '')
        {
            $html = '<video controls class="tablesome__inputMediaPreview tablesome__inputMediaPreview--video">';
            $html .= '<source src="' . $url . '" type="' . $mime_type . '">';
            $html .= __('Your browser does not support HTML video', 'tablesome');
            $html .= '</video>';

            return $html;
        }

        public function get_audio($url = '', $mime_type = '')
        {
            $html = '<audio controls class="tablesome__inputMediaPreview tablesome__inputMediaPreview--audio">';
            $html .= '<source src="' . $url . '" type="' . $mime_type . '">';
            $html .= __('Your browser does not support HTML audio', 'tablesome');
            $html .= '</audio>';

            return $html;
        }

        public function get_media_link($url = '', $name = '')
        {
            return '<a class="tablesome__inputMediaPreview tablesome__inputMediaPreview--link" href="' . $url . '" target="_blank">' . $name . '</a>';
        }
    }
}
