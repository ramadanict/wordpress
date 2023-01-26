<?php

namespace Pauple\Pluginator;

if (!class_exists('\Pauple\Pluginator\ReviewNotification')) {

    class ReviewNotification
    {

        public $review_args;

        public $errors = array();

        public function __construct()
        {

        }

        public function init($review_args)
        {
            $this->review_args = $review_args;

            $this->validate($review_args);

            if (!empty($this->errors)) {
                foreach ($this->errors as $error) {
                    trigger_error($error);
                }
                return false;
            }

            /** set the default props valus if missing props */
            $this->set_defaults();

            $this->call_review_notice();
        }

        public function validate($review_args)
        {
            /** required field validation */
            if (!array_key_exists('post_type', $review_args) || empty($review_args['post_type'])) {
                $this->errors['post_type'] = 'Missing the plugin post_type';
            }

            if (!array_key_exists('domain', $review_args) || empty($review_args['domain'])) {
                $this->errors['domain'] = 'Missing the plugin domain value';
            }

            if (!array_key_exists('plugin_review_url', $review_args) || empty($review_args['plugin_review_url'])) {
                $this->errors['plugin_review_url'] = 'Missing the plugin review URL';
            }

            if (!array_key_exists('post_interval_limit', $review_args) || empty($review_args['post_interval_limit'])) {
                $this->errors['post_interval_limit'] = 'Missing the interval limit value.';
            }

            if (!array_key_exists('post_limit', $review_args) || empty($review_args['post_limit'])) {
                $this->errors['post_limit'] = 'Missing the posts limit value.';
            }

            if (!array_key_exists('message', $review_args) || empty($review_args['message'])) {
                $this->errors['message'] = 'Missing the review notification content';
            }

            if (!array_key_exists('interval_message', $review_args) || empty($review_args['interval_message'])) {
                $this->errors['interval_message'] = 'Missing the interval review notification content';
            }

            /** validating the user review URL */
            if (!empty($review_args['plugin_review_url']) && !filter_var($review_args['plugin_review_url'], FILTER_VALIDATE_URL)) {
                $this->errors['plugin_review_url'] = 'Please update the valid user review URL';
            }

            /***
             * count props should be an interger
             */
            if (!empty($review_args['post_interval_limit']) && !is_numeric($review_args['post_interval_limit'])) {
                $this->errors['post_interval_limit'] = 'Post interval limit should be an integer';
            }

            if (!empty($review_args['post_limit']) && !is_numeric($review_args['post_limit'])) {
                $this->errors['post_limit'] = 'Post limit should be an integer';
            }

        }

        public function set_defaults()
        {
            /*** Set the default values for below options */
            if (!array_key_exists('maybe_later_option', $this->review_args) || empty($this->review_args['maybe_later_option'])) {
                $this->review_args['maybe_later_option'] = $this->review_args['post_type'] . '_maybe_later_option';
            }

            if (!array_key_exists('dismissed_option', $this->review_args) || empty($this->review_args['dismissed_option'])) {
                $this->review_args['dismissed_option'] = $this->review_args['post_type'] . '_review_dismissed_option';
            }

            if (!array_key_exists('classes', $this->review_args) || empty($this->review_args['classes'])) {
                $this->review_args['classes'] = '';
            }

            /** Set the overall posts count as 0 */
            $this->review_args['overall_posts_count'] = 0;
        }

        public function call_review_notice()
        {
            if ($this->can_see_the_notice()) {
                add_action('admin_notices', array($this, 'print_review_notice'));
            }
        }

        public function can_see_the_notice()
        {
            /*** Check the notice if it's dismissed or not */
            $notice_dismissed = get_option($this->review_args['dismissed_option']);
            if (isset($notice_dismissed) && $notice_dismissed == true) {
                return false;
            }

            $maybe_later_option = get_option($this->review_args['maybe_later_option']);
            $posts_count = $this->get_posts_count();

            $limit = $this->get_limit();

            if ($posts_count == $this->review_args['post_limit'] && empty($maybe_later_option)) {
                return true;
            }

            return ($posts_count < $limit) ? false : true;
        }

        public function click_handler()
        {
            /* 1. If button clicked is 'Dismiss' button */
            $dismissed_option = $this->review_args['dismissed_option'];
            if (isset($_GET[$dismissed_option])) {
                update_option($dismissed_option, true);
                $escape_uri = remove_query_arg($dismissed_option);
                $this->refresh_page($escape_uri);
            }

            /* 2. If button clicked is 'Maybe Later' button */
            $maybe_later_option = $this->review_args['maybe_later_option'];
            if (isset($_GET[$maybe_later_option])) {
                update_option($maybe_later_option, $this->review_args['overall_posts_count']);
                $escape_uri = remove_query_arg($maybe_later_option);
                $this->refresh_page($escape_uri);
            }
        }

        public function refresh_page($url)
        {
            echo "<script type='text/javascript'>
               window.location=document.location.href='" . $url . "';
            </script>";
        }

        public function print_review_notice()
        {
            $this->click_handler();

            $limit = $this->get_limit();

            $content = $this->get_review_content($limit);

            $html = '<div class="helpie-notice notice notice-success is-dismissible ' . $this->review_args['classes'] . '" >';
            $html .= $content;
            $html .= '<p>' . __("Could you do me a favor and rate us with 5-stars. It would be such a motivation for us to keep improving the plugin.", $this->review_args['domain']) . '</p>';
            $html .= '<p><a class="success-button" target="_blank" href="' . $this->review_args['plugin_review_url'] . '">' . __("OK. You deserve it", $this->review_args['domain']) . '</a>&nbsp;&nbsp;&nbsp;';
            $html .= '<a href="' . add_query_arg(array($this->review_args['maybe_later_option'] => 'true')) . '">' . __("Nope. Maybe later", $this->review_args['domain']) . '</a>&nbsp;&nbsp;&nbsp;';
            $html .= '<a href="' . add_query_arg(array($this->review_args['dismissed_option'] => 'true')) . '">' . __("I already did", $this->review_args['domain']) . '</a></p>';
            $html .= '</div>';

            echo $html;
        }

        /**
         * Use this method to get the total posts count
         *
         */
        public function get_posts_count()
        {
            $results = get_posts(array('post_type' => $this->review_args['post_type'], 'post_status' => 'publish', 'posts_per_page' => -1));
            $overall_posts_count = count($results);
            $this->review_args['overall_posts_count'] = $overall_posts_count;
            return $overall_posts_count;
        }

        /**
         * Use this method to get the limit to show the review notification on the admin page.
         */
        public function get_limit()
        {
            $interval_limit = get_option($this->review_args['maybe_later_option']);
            $limit = $this->review_args['post_limit'];
            if (isset($interval_limit) && 0 < $interval_limit) {
                $limit = $interval_limit + (int) $this->review_args['post_interval_limit'];
            }
            return $limit;
        }

        public function get_review_content($limit)
        {
            $maybe_later_option_exists = get_option($this->review_args['maybe_later_option']);
            if (empty($maybe_later_option_exists)) {
                $limit = $this->review_args['overall_posts_count'];
            }

            /**  return the message if the $limit and configured limit is met */
            if ($limit == $this->review_args['post_limit']) {
                return sprintf($this->review_args['message']);
            }

            if ($limit > 5 && fmod($limit, 5) > 0) {
                $limit = floor($limit / 5);
                $limit = $limit * 5;
            } else if ($limit < 5) {
                $limit = ($limit >= 2) ? ($limit - 1) : $limit;
            }

            return sprintf($this->review_args['interval_message'], $limit, $limit);
        }

    }
}
