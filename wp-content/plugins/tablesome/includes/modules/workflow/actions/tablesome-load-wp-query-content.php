<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use Tablesome\Includes\Modules\Workflow\Action;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\Tablesome_Load_WP_Query_Content')) {
    class Tablesome_Load_WP_Query_Content extends Action
    {
        public function get_config()
        {
            return array(
                'id' => 8,
                'name' => 'load_wp_query_content',
                'label' => __('Replace Table Content with WP Query', 'tablesome'),
                'integration' => 'tablesome',
                'is_premium' => true,
            );
        }

        public function do_action($trigger_class, $trigger_instance)
        {
            return true;
        }

    }
}
