<?php

namespace \Pauple\Pluginator\Component;

if (!class_exists('\Pauple\Pluginator\Component\View_Interface')) {

    interface View_Interface
    {
        public function get_html($args);
    }
}