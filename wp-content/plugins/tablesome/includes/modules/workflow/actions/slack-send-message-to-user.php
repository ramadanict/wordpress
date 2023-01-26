<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use Tablesome\Includes\Modules\Workflow\Action;
use Tablesome\Includes\Modules\Workflow\Traits\Placeholder;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\Slack_Send_Message_To_User')) {

    class Slack_Send_Message_To_User extends Action
    {
        use Placeholder;
        public $slack_api;
        public $action_meta;
        public $trigger_class;
        public $trigger_instance;
        public $placeholders;
        public function __construct()
        {
            $this->slack_api = new \Tablesome\Includes\Modules\Workflow\External_Apis\slack();
        }

        public function get_config()
        {
            return array(
                'id' => 14,
                'name' => 'slack_send_message_to_user',
                'label' => __('Send Direct Message to User', 'tablesome'),
                'integration' => 'slack',
                'is_premium' => true,
            );
        }

        public function do_action($trigger_class, $trigger_instance)
        {
            error_log('*** Slack Send a Message to User Action Called  ***');
            $this->bind_props($trigger_class, $trigger_instance);

            $user_id = isset($this->action_meta['user_id']) ? $this->action_meta['user_id'] : '';
            $content = isset($this->action_meta['message']['content']) ? $this->action_meta['message']['content'] : '';
            $message_content = $this->applyPlaceholders($this->placeholders, $content);

            if (empty($user_id) || empty($message_content)) {
                return;
            }

            $result = $this->slack_api->send_message($user_id, $message_content);
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
