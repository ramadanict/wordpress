<?php

namespace Pauple\Pluginator;

// if (!defined('ABSPATH')) {
//     exit;
// } // Exit if accessed directly

if (!class_exists('\Pauple\Pluginator\Widgetry')) {
    class Widgetry implements \Pauple\Pluginator\Interfaces\WidgetryInterface
    {
        public $widgets = [];
        // Initialise class in plugin constructor's - plugin.php -> _construct()
        public function __construct()
        {
        }

        public function init()
        {
            add_action('elementor/widgets/widgets_registered', [$this, 'register_widget_to_elementor']);
            add_action('widgets_init', [$this, 'register_wp_widget']);
        }

        public function register_widget($widget_args)
        {
            array_push($this->widgets, $widget_args);
        }

        public function register_wp_widget()
        {
            for ($ii = 0; $ii < sizeof($this->widgets); $ii++) {
                $widget_args = $this->widgets[$ii];
                $widget_object = new \Pauple\Pluginator\Widgetry\WidgetFactory($widget_args);
                register_widget($widget_object);
            }
        }

        public function register_widget_to_elementor()
        {
            for ($ii = 0; $ii < sizeof($this->widgets); $ii++) {
                $widget_args = $this->widgets[$ii];
                \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Pauple\Pluginator\Widgetry\ElementorWidgetFactory([], $widget_args));
            }
        }
    } // END CLASS
}
