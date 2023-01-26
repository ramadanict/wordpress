<?php

namespace Pauple\Pluginator\Interfaces;

if (!class_exists('\Pauple\Pluginator\Interfaces\WidgetryInterface')) {

    interface WidgetryInterface
    {
        // public function init(); // Call in plugin's constructor method
        public function register_widget($widget_args); // Call in plugin's constructor method
    }
}
