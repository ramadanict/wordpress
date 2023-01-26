<?php

namespace Pauple\Pluginator\Interfaces;

if (!class_exists('\Pauple\Pluginator\Interfaces\UpgraderInterface')) {

    interface UpgraderInterface
    {
        public static function add_actions(); // Call in plugin's constructor method
    }
}
