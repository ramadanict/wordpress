<?php

namespace Tablesome\Includes\Modules\Workflow;

use Tablesome\Includes\Modules\Workflow\Traits\Placeholder;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
// Underscore (_example) property name consider as derived values.
if (!class_exists('\Tablesome\Includes\Modules\Workflow\Trigger')) {
    abstract class Trigger
    {
        use Placeholder;

        public $is_premium = false;
        public $actions = [];

        public function __construct()
        {
            $this->is_premium = can_use_tablesome_premium();
            $this->store_all_entries = new \Tablesome\Includes\Modules\Workflow\Actions\Store_All_Forms_Entries();
        }

        public function init($actions)
        {
            $this->actions = $actions;
        }

        public function run_triggers($trigger_class, $trigger_source_data)
        {
            global $workflow_redirection_data;
            $trigger_instances = $this->get_trigger_instances($trigger_class, $trigger_source_data);

            if (empty($trigger_instances)) {
                return;
            }

            // delete redirection data in DB before loop the trigger instances
            delete_option('workflow_redirection_data');

            $placeholders = $this->getPlaceholders($trigger_source_data);

            foreach ($trigger_instances as $trigger_instance) {

                $actions = $trigger_instance['trigger_meta']['actions'];

                $configured_free_action_positions = $this->get_configured_free_action_positions($actions);

                foreach ($actions as $action_position => $action) {
                    /** Appends the action position and the action meta in $trigger_data array */
                    $trigger_instance['action_position'] = $action_position;
                    $trigger_instance['action_meta'] = $action;
                    $trigger_instance['_placeholders'] = $placeholders;

                    $action_id = isset($action['action_id']) ? intval($action['action_id']) : 0;

                    $action_class = $this->get_action_class_by_id($action_id);

                    // Free plan users only have an access to access the free actions.
                    $can_access_the_action = ($this->is_premium || !$this->is_premium && in_array($action_position, $configured_free_action_positions)) ? true : false;

                    if (empty($action_class) || is_null($action_class) || !$can_access_the_action) {
                        continue;
                    }

                    if ($action_class->conditions($trigger_instance, $action)) {
                        $action_class->do_action($trigger_class, $trigger_instance);
                    }
                }
            }

            // store the redirection data in DB if any redirection action has configured
            if (isset($workflow_redirection_data) && count($workflow_redirection_data) > 0) {
                update_option('workflow_redirection_data', $workflow_redirection_data);
            }

        }

        private function get_trigger_instances($trigger_class, $trigger_source_data)
        {
            $this->store_all_entries->init($trigger_class, $trigger_source_data);

            $trigger_instances = [];
            $tables = $this->get_tables();
            if (!isset($tables) || empty($tables)) {
                return $trigger_instances;
            }

            foreach ($tables as $table) {

                $triggers_meta = get_tablesome_table_triggers($table->ID);

                if (!isset($triggers_meta) || empty($triggers_meta)) {
                    continue;
                }

                // Free trigger for free user (Can access 1 trigger per table)
                $free_trigger_applied = false;

                foreach ($triggers_meta as $trigger_position => $trigger_meta) {

                    if (true == ($free_trigger_applied && !$this->is_premium)) {
                        continue;
                    }

                    /**
                     * Free plan user can access the 1 trigger and 3 actions per table
                     */
                    if (!$free_trigger_applied && !$this->is_premium && "no" == $trigger_class->get_config()['is_premium']) {
                        $free_trigger_applied = true;
                    }

                    $trigger_id = isset($trigger_meta['trigger_id']) ? $trigger_meta['trigger_id'] : 0;

                    $trigger_does_not_have_instance = !$trigger_class->conditions($trigger_meta, $trigger_source_data);
                    $not_instance_of_current_trigger = $trigger_class->get_config()['trigger_id'] != $trigger_id;
                    $trigger_does_not_have_actions = !isset($trigger_meta['actions']) || empty($trigger_meta['actions']);
                    $trigger_is_not_active = !isset($trigger_meta['status']) || $trigger_meta['status'] != 1;
                    $trigger_does_not_have_permission = !$this->can_access_trigger($trigger_class);

                    $trigger_is_not_valid = $trigger_does_not_have_instance
                        || $not_instance_of_current_trigger
                        || $trigger_does_not_have_actions
                        || $trigger_is_not_active
                        || $trigger_does_not_have_permission;

                    if ($trigger_is_not_valid) {
                        continue;
                    }

                    $trigger_instances[] = array(
                        'trigger_meta' => $trigger_meta,
                        'table_id' => $table->ID,
                        'trigger_position' => $trigger_position,
                        'trigger_data' => $trigger_source_data,
                    );
                }
            }

            return $trigger_instances;
        }

        public function get_tables()
        {
            $tables = get_posts(
                array(
                    'post_type' => TABLESOME_CPT,
                    'numberposts' => -1,
                )
            );

            return $tables;
        }

        public function get_action_class_by_id($action_id)
        {
            $class = null;
            if (empty($action_id) || empty($this->actions)) {
                return $class;
            }

            foreach ($this->actions as $action_class) {
                $config = $action_class->get_config();
                if (isset($config['id']) && $config['id'] == $action_id) {
                    $class = $action_class;
                    break;
                }
            }
            return $class;
        }

        public function can_access_trigger($trigger_class)
        {
            if ($this->is_premium) {
                return true;
            }
            return ("no" == $trigger_class->get_config()['is_premium']);
        }

        public function get_free_action_ids()
        {
            $ids = array();

            if (empty($this->actions)) {
                return $ids;
            }

            foreach ($this->actions as $action_instance) {
                $config = $action_instance->get_config();
                if (false == $config['is_premium']) {
                    $ids[] = $config['id'];
                }
            }

            return $ids;
        }

        public function get_configured_free_action_positions($configured_actions)
        {
            $free_actions_ids = $this->get_free_action_ids();
            $positions = [];
            foreach ($configured_actions as $position => $action_meta) {
                $action_id = isset($action_meta['action_id']) ? $action_meta['action_id'] : 0;
                if (in_array($action_id, $free_actions_ids) && count($positions) < 3) {
                    $positions[] = $position;
                }
            }
            return $positions;
        }

        public function getPlaceholders($triggerSourceData)
        {
            $smartFieldValues = get_tablesome_smart_field_values();
            $smartFieldsPlaceholders = $this->getPlaceholdersFromKeyValues($smartFieldValues);
            $triggerDataPlaceholders = $this->getPlaceholdersFromTriggerSourceData($triggerSourceData);
            return array_merge($smartFieldsPlaceholders, $triggerDataPlaceholders);
        }
    }
}
