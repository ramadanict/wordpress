<?php

namespace Tablesome\Includes\Modules\Workflow\Actions;

use Tablesome\Includes\Modules\Workflow\Action;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Workflow\Actions\Tablesome_Filter_Table')) {
    class Tablesome_Filter_Table extends Action
    {
        public function get_config()
        {
            return array(
                'id' => 9,
                'name' => 'filter_table',
                'label' => __('Filter Table', 'tablesome'),
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
