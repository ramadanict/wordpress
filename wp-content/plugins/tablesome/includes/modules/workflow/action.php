<?php

namespace Tablesome\Includes\Modules\Workflow;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Action')) {
    abstract class Action {
        public function conditions($trigger_data, $action_data) {
            return true;
        }

    }
}