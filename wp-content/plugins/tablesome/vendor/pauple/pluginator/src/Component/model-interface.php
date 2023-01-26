<?php

namespace \Pauple\Pluginator\Component;

if (!class_exists('\Pauple\Pluginator\Component\Model_Interface')) {

    interface Model_Interface
    {
        
        public function get_viewProps($args);
        public function get_viewProps_fallback();
    }
}