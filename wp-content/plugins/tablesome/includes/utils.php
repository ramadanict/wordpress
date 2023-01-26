<?php

namespace Tablesome\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Utils')) {
    class Utils
    {
        public function get_bool($value = false)
        {
            $boolean = false;

            if ($value == true || $value == 1 || $value == "true" || $value == "1") {
                $boolean = true;
            }

            return $boolean;
        }

        public function get_workflow_action_meta($table_id, $target_action_id = 8, $target_trigger_id = 5)
        {
            $action_meta = [];

            $triggers_meta = get_tablesome_table_triggers($table_id);

            if (empty($triggers_meta)) {
                return $action_meta;
            }

            foreach ($triggers_meta as $trigger) {
                $trigger_id = isset($trigger['trigger_id']) ? $trigger['trigger_id'] : 0;
                $trigger_status = isset($trigger['status']) ? $trigger['status'] : false;
                $actions = isset($trigger['actions']) ? $trigger['actions'] : [];

                if ($trigger_id != $target_trigger_id || !$trigger_status) {
                    continue;
                }

                foreach ($actions as $action) {
                    $action_id = isset($action['action_id']) ? $action['action_id'] : 0;
                    if ($action_id == $target_action_id) {
                        $action_meta = $action;
                        break;
                    }
                }
            }
            return $action_meta;
        }

    }
}
