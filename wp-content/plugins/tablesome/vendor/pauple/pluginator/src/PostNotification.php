<?php

namespace Pauple\Pluginator;


if (!class_exists('\Pauple\Pluginator\PostNotification')) {

    class PostNotification
    {

        /*
            $notification_settings = [
                'subject' => '',
                'message' => '',
                'headers' => 
                'conditions' => [
                    'post_type' => ['helpie_faq', 'starcat-reviews'],
                    'changed_values' => [
                        // Yet to be Decided
                    ]
                ],
                'when' => [
                    'old_status' => ['draft', '!publish'],
                    'new_status' => ['publish']
                ],
                'to' => [
                    'user_ids' => [24, 67, 78],
                    'user_roles' => ['editor', 'admin'],
                    'email_ids' => ['john@gmail.com']
                ]
                ];

        */


        // Add to init hook
        // add_action('init', array($post_notification, 'init_hook'));
        public function init($notification_settings)
        {
            $this->validate_settings($notification_settings);
            $this->settings = $notification_settings;
            if (current_user_can('administrator')) {
                add_action("transition_post_status", array($this, "post_transition_handler"), 10, 3);
            }
        }


        public function post_transition_handler($new, $old, $post)
        {
            $conditions = $this->check_conditions(); // true or false
            $when = $this->check_when($new, $old, $post); // true or false

            // if either conditions or $when is false, return 
            if (!$conditions || !$when) {
                return;
            }

            $emails = $this->get_to_emails(); // emails of $to

            $mail = [
                'to' => $emails,
                'subject' => $this->settings->subject,
                'message' => $this->settings->message,
                'headers' => $this->settings->headers,
            ];

            $this->send_email($mail);
        }

        public function send_email($mail)
        {
            wp_mail($mail->to, $mail->subject, $mail->message, $mail->headers);
        }

        private function validate_settings($notification_settings)
        {
            // 
        }

        private function get_to_emails()
        {
            // 
        }

        private function check_conditions()
        {
            // 
        }

        private function check_when($new, $old, $post)
        {
            // 
        }
    }
}
