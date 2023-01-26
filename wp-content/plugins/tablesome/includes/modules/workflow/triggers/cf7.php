<?php

namespace Tablesome\Includes\Modules\Workflow\Triggers;

use Tablesome\Includes\Modules\Workflow\Trigger;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Triggers\Cf7')) {
    class Cf7 extends Trigger
    {
        /**
         * Define the un-supported fields in CF7
         *
         */
        public $unsupported_formats = array(
            'submit',
        );

        public function get_config()
        {
            $is_active = class_exists('WPCF7') ? true : false;

            return array(
                'integration' => 'cf7',
                'integration_label' => __('Contact Form 7', 'tablesome'),
                'trigger' => 'tablesome_cf7_form_submit',
                'trigger_id' => 1,
                'trigger_label' => __('On Form Submit', 'tablesome'),
                'trigger_type' => 'forms',
                'is_active' => $is_active,
                'is_premium' => "no",
                'hooks' => array(
                    array(
                        'priority' => 10,
                        'accepted_args' => 1,
                        'name' => 'wpcf7_before_send_mail',
                        'callback_name' => 'trigger_callback',
                    ),
                ),
                'supported_actions' => [],
                'unsupported_actions' => [8, 9]
            );
        }

        public function get_collection()
        {
            $forms = $this->get_posts();
            if (empty($forms)) {
                return [];
            }

            foreach ($forms as $index => $form) {
                // Get form fields
                $forms[$index]['fields'] = $this->get_post_fields($form['id']);
            }

            return $forms;
        }

        public function get_posts()
        {
            $forms = get_posts(array(
                'post_type' => 'wpcf7_contact_form',
                'numberposts' => -1,
            ));

            if (empty($forms)) {
                return [];
            }
            $posts = array();
            foreach ($forms as $post) {
                $posts[] = array(
                    'id' => $post->ID,
                    'label' => $post->post_title . " (ID: " . $post->ID . ")",
                    'integration_type' => 'cf7',
                );
            }

            return $posts;
        }

        public function get_post_fields($form_id, array $args = array())
        {
            if (!is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
                return [];
            }

            $form = \WPCF7_ContactForm::get_instance($form_id);
            if (is_null($form) || empty($form)) {
                return [];
            }

            $fields_object = $form->scan_form_tags();
            $fields = $this->get_formatted_fields($fields_object);
            return $fields;
        }

        public function get_formatted_fields($fields_object)
        {
            $fields = array();
            if (empty($fields_object)) {
                return $fields;
            }
            foreach ($fields_object as $field_object) {
                $basetype = isset($field_object['basetype']) ? $field_object['basetype'] : '';
                $name = isset($field_object['name']) ? $field_object['name'] : '';
                if (!empty($name) && !in_array($basetype, $this->unsupported_formats)) {

                    $field = [
                        "id" => $name,
                        "label" => $name,
                        "field_type" => $basetype,
                    ];

                    $have_options = (isset($field_object['values']) && !empty($field_object['values']));
                    if (in_array($basetype, ['select', 'checkbox', 'radio']) && $have_options) {
                        $field['options'] = $this->get_formatted_options($field_object);
                    }

                    $fields[] = $field;

                }
            }
            return $fields;
        }

        public function trigger_callback($wpcf7)
        {
            $submission = \WPCF7_Submission::get_instance();
            if (!$submission) {
                return $wpcf7;
            }

            // error_log(' submission : ' . print_r($submission, true));

            $form_tags = $wpcf7->scan_form_tags();
            // Get all the fields types
            $fields_types = array_column($form_tags, 'basetype', 'name');

            $upload_files = $submission->uploaded_files();

            $posted_data = $submission->get_posted_data();
            $posted_data = $this->get_modified_posted_data($posted_data, $upload_files);

            $submission_data = $this->get_formatted_posted_data($posted_data, $fields_types);

            // error_log(' submission_data : ' . print_r($submission_data, true));

            $this->trigger_source_id = $wpcf7->id();
            $this->trigger_source_data = array(
                'integration' => $this->get_config()['integration'],
                'form_title' => $wpcf7->title(),
                'form_id' => $wpcf7->id(),
                'data' => $submission_data,
            );

            $this->run_triggers($this, $this->trigger_source_data);
        }

        /**
         * Current: Check the current trigger have a single instance.
         * Later: will add more trigger specific conditions.
         */
        public function conditions($trigger_meta, $trigger_data)
        {

            $integration = isset($trigger_meta['integration']) ? $trigger_meta['integration'] : '';
            $trigger_id = isset($trigger_meta['trigger_id']) ? $trigger_meta['trigger_id'] : '';

            if ($integration != $this->get_config()['integration'] || $trigger_id != $this->get_config()['trigger_id']) {
                return false;
            }

            $trigger_source_id = isset($trigger_meta['form_id']) ? $trigger_meta['form_id'] : 0;
            if (isset($trigger_data['form_id']) && $trigger_data['form_id'] == $trigger_source_id) {
                return true;
            }
            return false;
        }

        public function get_formatted_posted_data($posted_data, $fields_types = array())
        {
            // error_log('cf7 posted_data : ' . print_r($posted_data, true));

            $data = array();
            foreach ($posted_data as $key => $value) {
                $field_type = isset($fields_types[$key]) ? $fields_types[$key] : '';

                if (is_array($value) && !empty($value)) {
                    $value = implode(',', $value);
                } else if (is_valid_tablesome_date($value, 'Y-m-d')) {
                    $unix_timestamp = convert_tablesome_date_to_unix_timestamp($value, 'Y-m-d');
                    $unix_timestamp = $unix_timestamp * 1000; // convert to milliseconds
                }

                $data[$key] = array(
                    'label' => $key,
                    'value' => $value,
                    'type' => $field_type,
                    'unix_timestamp' => isset($unix_timestamp) ? $unix_timestamp : '', // use this prop when the column format type is date
                );
            }

            // error_log('cf7 final_data : ' . print_r($data, true));

            return $data;
        }

        public function get_formatted_options($field_object)
        {
            $options = array();
            foreach ($field_object['values'] as $value) {
                $options[] = array(
                    'id' => $value,
                    'label' => $value,
                );
            }
            return $options;
        }

        public function get_modified_posted_data($posted_data, $file_uploads)
        {

            if (isset($file_uploads) && !empty($file_uploads)) {
                foreach ($file_uploads as $field_key => $field) {
                    if (array_key_exists($field_key, $posted_data) && !empty($field)) {
                        $posted_data[$field_key] = $this->upload_file_from_path($field[0]);
                    }
                }
            }

            return $posted_data;
        }

        public function upload_file_from_path($file, $title = null)
        {
            $media_dir = wp_upload_dir();
            $time_now = time();
            $upload_data = array();

            copy($file, $media_dir['path'] . '/' . $time_now . '-' . basename($file));
            $upload_data['name'] = basename($file);
            $filename = $upload_data['name'];
            $file = $media_dir['path'] . '/' . $time_now . '-' . basename($file);

            $wp_filetype = wp_check_filetype($filename, null);

            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit',
            );

            $attachment_id = wp_insert_attachment($attachment, $file);
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $file);
            wp_update_attachment_metadata($attachment_id, $attachment_metadata);

            return (int) $attachment_id;
        }
    }
}
