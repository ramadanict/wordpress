<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

use \Tablesome\Includes\Settings\Tablesome_Getter;

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\Store_All_Forms_Entries')) {

    class Store_All_Forms_Entries
    {
        public $form_id = 0;

        public $integration = '';

        public $form_title = '';

        public $trigger_class;

        public function init($trigger_class, $trigger_data = array())
        {
            $this->trigger_class = $trigger_class;

            $this->form_id = isset($this->trigger_class->trigger_source_id) ? $this->trigger_class->trigger_source_id : '';
            $this->form_title = isset($this->trigger_class->trigger_source_data['form_title']) ? $this->trigger_class->trigger_source_data['form_title'] : '';
            $this->integration = $this->trigger_class->get_config()['integration'];

            $enabled_all_forms_entries = Tablesome_Getter::get('enabled_all_forms_entries');

            if (!$enabled_all_forms_entries || empty($this->form_id) || $this->trigger_class->get_config()['trigger_type'] != 'forms') {
                return;
            }

            $table_triggers_post_metas = $this->get_table_trigger_metas();

            // If the records are empty, should create a new table with the default form trigger data
            if (empty($table_triggers_post_metas)) {
                $this->create_table_with_default_form_triggers();
            }

            if (!empty($table_triggers_post_metas)) {

                $form_already_configured = false;
                foreach ($table_triggers_post_metas as $post_meta) {

                    $form_already_configured = $this->is_form_already_configured($post_meta);

                    if ($form_already_configured == true) {
                        break;
                    }
                }

                if (!$form_already_configured) {
                    $this->create_table_with_default_form_triggers();
                }
            }
        }

        public function is_form_already_configured($post_meta)
        {
            $form_already_configured = false;

            $triggers_meta = isset($post_meta->meta_value) && !empty($post_meta->meta_value) ? maybe_unserialize($post_meta->meta_value) : [];

            $form_ids = isset($triggers_meta) && !empty($triggers_meta) ? array_column($triggers_meta, 'integration', 'form_id') : [];

            // Check both form_id and integration
            if (isset($form_ids[$this->form_id]) && $form_ids[$this->form_id] == $this->integration) {
                $form_already_configured = true;
            }

            return $form_already_configured;
        }

        public function get_table_trigger_metas()
        {
            global $wpdb;
            $table_triggers_post_metas = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT p.ID,pm.meta_value
                        FROM $wpdb->postmeta pm
                            LEFT JOIN $wpdb->posts p
                                ON p.ID = pm.post_id
                        WHERE p.post_type IS NOT NULL
                        AND p.post_status = %s
                        AND pm.meta_key = %s
                        AND pm.`meta_value` LIKE %s",
                    'publish',
                    'tablesome_table_triggers',
                    '%%tablesome%%'
                )
            );

            return $table_triggers_post_metas;
        }

        public function create_table()
        {
            $table = new \Tablesome\Includes\Core\Table();

            $post_data = array(
                'post_title' => $this->form_title,
                'post_type' => TABLESOME_CPT,
                'post_content' => '',
                'post_status' => 'publish',
            );

            return $table->insert_or_update_post(0, $post_data);
        }

        public function get_default_form_triggers($table_id)
        {
            $default_smart_fields_data = get_default_tablesome_smart_fields();
            $form_id = $this->integration != 'elementor' ? (int) $this->form_id : $this->form_id;
            $trigger_id = isset($this->trigger_class->get_config()['trigger_id']) ? $this->trigger_class->get_config()['trigger_id'] : 0;

            $triggers = array(
                array(
                    'integration' => $this->integration,
                    'status' => 1,
                    'trigger_id' => $trigger_id,
                    'form_id' => $form_id,
                    'actions' => array(
                        array(
                            'integration' => 'tablesome',
                            'match_columns' => $default_smart_fields_data,
                            'autodetect_enabled' => 1,
                            'action_id' => 1,
                        ),
                    ),
                ),
            );
            return $triggers;
        }

        public function create_table_with_default_form_triggers()
        {
            $table_id = $this->create_table();
            $table_meta = set_tablesome_data(
                $table_id,
                array(
                    'columns' => array(),
                    'meta' => array(
                        'last_column_id' => 0,
                    ),
                )
            );
            $form_triggers = $this->get_default_form_triggers($table_id);
            set_tablesome_table_triggers($table_id, $form_triggers);

            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $tablesome_db->create_table_instance($table_id, $table_meta);
        }
    }
}
