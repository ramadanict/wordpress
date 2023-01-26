<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use Tablesome\Includes\Modules\Workflow\Action;
use Tablesome\Includes\Modules\Workflow\Traits\Placeholder;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\WP_Send_Mail')) {
    class WP_Send_Mail extends Action
    {
        use Placeholder;

        protected $headers = [
            "from" => '',
            'cc' => '',
            'bcc' => '',
            'content_type' => '',
        ];

        // public function __construct() {
        //     add_action('wp_mail_failed', [$this, 'onMailError'], 10, 1);
        // }
        public function get_config()
        {
            return array(
                'id' => 7,
                'name' => 'send_mail',
                'label' => __('Send Mail', 'tablesome'),
                'integration' => 'wordpress',
                'is_premium' => false,
            );
        }

        // public function onMailError($err) {
        //     error_log('$err : ' . print_r($err, true));
        // }

        public function do_action($trigger_class, $trigger_instance)
        {
            error_log('*** WordPress Sent Email Action Called  ***');
            $this->bind_props($trigger_class, $trigger_instance);

            if (!$this->validate()) {
                return false;
            }

            $this->set_mail_headers();
            foreach ($this->to_address_data as $data) {
                $to_address_emails = $this->get_emails_by_prop_name($data, 'emails');
                if (empty($to_address_emails)) {
                    continue;
                }

                $cc_emails = $this->get_emails_by_prop_name($data, 'cc');
                if (!empty($cc_emails)) {
                    $cc_emails_in_string = implode(",", $cc_emails);
                    $this->headers['cc'] = "Cc: {$cc_emails_in_string}";
                }

                $bcc_emails = $this->get_emails_by_prop_name($data, 'bcc');
                if (!empty($bcc_emails)) {
                    $bcc_emails_in_string = implode(",", $bcc_emails);
                    $this->headers['bcc'] = "Bcc: {$bcc_emails_in_string}";
                }

                $headers_content = implode("\r\n", $this->headers);
                $sent = \wp_mail(
                    $to_address_emails,
                    $this->subject_content,
                    $this->body_content,
                    $headers_content
                );
                // $this->get_attachments()

                //TODO: Add mail failure log
                // if (!$sent) {
                // }
            }
        }

        private function bind_props($trigger_class, $trigger_instance)
        {
            $this->trigger_class = $trigger_class;
            $this->trigger_instance = $trigger_instance;

            $this->trigger_source_data = $this->trigger_class->trigger_source_data['data'];
            $this->action_meta = isset($this->trigger_instance['action_meta']) ? $this->trigger_instance['action_meta'] : [];

            $this->email_fields = isset($this->action_meta['email_fields']) ? $this->action_meta['email_fields'] : [];

            $this->placeholders = $this->trigger_instance['_placeholders'];

            $from_email_address = isset($this->email_fields['from_address']['email']) ? trim($this->email_fields['from_address']['email']) : '';

            $this->from_email_address = !empty($from_email_address) ? $this->applyPlaceholders($this->placeholders, $from_email_address) : '';

            $this->to_address_data = isset($this->email_fields['to_address']) ? $this->email_fields['to_address'] : [];

            $this->subject_content = $this->get_subject_content();

            $this->body_content = $this->get_mail_body_content();
        }

        private function validate()
        {
            if (empty($this->from_email_address) || !is_email($this->from_email_address)) {
                return;
            }

            if (empty($this->to_address_data)) {
                return;
            }

            return true;
        }

        private function set_mail_headers()
        {
            $this->headers['from'] = "From: <{$this->from_email_address}>";
            $this->headers['content_type'] = "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
        }

        private function get_emails_by_prop_name($email_data, $prop_name)
        {
            if (!isset($email_data[$prop_name]) || empty($email_data[$prop_name])) {
                return [];
            }
            $emails = explode(",", $email_data[$prop_name]);
            if (!is_array($emails) || count($emails) == 0) {
                return [];
            }

            $valid_emails = [];

            foreach ($emails as $email) {
                $email = trim($email);
                $email = $this->applyPlaceholders($this->placeholders, $email);
                if (is_email($email)) {
                    $valid_emails[] = $email;
                }
            }
            return count($valid_emails) ? array_unique(array_values($valid_emails)) : [];
        }

        private function get_content_type()
        {
            return 'text/html';
        }

        private function get_subject_content()
        {
            $content = isset($this->email_fields['subject']['content']) ? $this->email_fields['subject']['content'] : '';
            $content = $this->applyPlaceholders($this->placeholders, $content);
            return $content;
        }

        private function get_mail_body_content()
        {
            $content = isset($this->email_fields['body']['content']) ? $this->email_fields['body']['content'] : '';
            $content = $this->applyPlaceholders($this->placeholders, $content);
            return $content;
        }
    }
}
