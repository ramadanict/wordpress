<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use Tablesome\Includes\Modules\Workflow\Action;
use Tablesome\Includes\Modules\Workflow\Traits\Placeholder;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\Slack_Send_Message_To_Channel')) {
    class Slack_Send_Message_To_Channel extends Action
    {
        use Placeholder;
        public $slack_api;
        public $action_meta;
        public $trigger_class;
        public $trigger_instance;
        public $placeholders;

        public function __construct()
        {
            $this->slack_api = new \Tablesome\Includes\Modules\Workflow\External_Apis\Slack();
        }

        public function get_config()
        {
            return array(
                'id' => 13,
                'name' => 'slack_send_message_to_channel',
                'label' => __('Send message to Channel', 'tablesome'),
                'integration' => 'slack',
                'is_premium' => true,
            );
        }

        public function do_action($trigger_class, $trigger_instance)
        {
            error_log('*** Slack Send a Message to Channel Action Called  ***');
            $this->bind_props($trigger_class, $trigger_instance);

            $channel_id = isset($this->action_meta['channel_id']) ? $this->action_meta['channel_id'] : '';
            $content = isset($this->action_meta['message']['content']) ? $this->action_meta['message']['content'] : '';
            // error_log('$content : ' . print_r($content, true));
            $message_content = $this->applyPlaceholders($this->placeholders, $content);
            // error_log('$channel_id : ' . print_r($channel_id, true));
            // error_log('$message_content : ' . print_r($message_content, true));
            if (empty($channel_id) || empty($message_content)) {
                return;
            }

            $result = $this->slack_api->send_message($channel_id, $message_content);
            return isset($result['ok']) && $result['ok'] == true;
        }

        private function bind_props($trigger_class, $trigger_instance)
        {
            $this->trigger_class = $trigger_class;
            $this->trigger_instance = $trigger_instance;
            $this->action_meta = isset($this->trigger_instance['action_meta']) ? $this->trigger_instance['action_meta'] : [];
            $this->placeholders = $this->trigger_instance['_placeholders'];
        }
    }
}
