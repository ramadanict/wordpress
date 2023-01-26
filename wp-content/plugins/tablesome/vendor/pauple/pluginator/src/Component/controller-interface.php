<?php

namespace \Pauple\Pluginator\Component;

if (!class_exists('\Pauple\Pluginator\Component\Controller_Interface')) {

    interface Controller_Interface
    { 
        public function get_view($args);
    }
}