<?php

namespace Tablesome\Includes\Modules;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Review_Notification')) {
    class Review_Notification
    {

        public $review_args = array(
            'post_type' => TABLESOME_CPT,
            'domain' => TABLESOME_DOMAIN,
            /** Review url. */
            'plugin_review_url' => 'https://wordpress.org/support/plugin/tablesome/reviews/?filter=5#new-post',

            'post_interval_limit' => 3,

            /*** Show the notice when the limit is met  **/
            'post_limit' => 2,

            /** Review notification message */
            'message' => '',

            'plugin_registered_datetime' => '',

            /** Show the notice 2-days after plugin register */
            'days_to_show_notice_after_install' => 2, //

            /** Show the notice again after 30-days later */
            'days_to_show_notice_again' => 30,

            /*** use this content if the interval limit is med  */
            // 'interval_message' => '<p><b>' . __("Congrats!", "tablesome") . ' %d+' . __("Tablesome tables are Created: ", "tablesome") . '</b>' . __("Hey,  I noticed you have created more than", "tablesome") . ' %d ' . __("tables using Tablesome - that's awesome! ", 'tablesome') . '</p>',
        );

        public function get_content()
        {
            $newtopic_url = 'https://wordpress.org/support/plugin/tablesome/#new-topic-0';
            $review_url = 'https://wordpress.org/support/plugin/tablesome/reviews/#new-post';

            $reviews = [
                [
                    'text' => 'Great. Worth 5 Stars!  Leave a Review',
                    'image' => $this->get_image(TABLESOME_URL . '/assets/images/review/star.svg'),
                    'url' => $review_url,
                ],
                [
                    'text' => 'Needs Improvement. Share a Comment',
                    'image' => $this->get_image(TABLESOME_URL . '/assets/images/review/comment.svg'),
                    'url' => $newtopic_url,
                ],
                [
                    'text' => 'Quick! I need help!',
                    'image' => $this->get_image(TABLESOME_URL . '/assets/images/review/headset.svg'),
                    'url' => $newtopic_url,
                ],
            ];

            $links = [
                [
                    'text' => __("I ❤️ Love it", $this->review_args['domain']),
                    'url' => $review_url,
                    'success' => true,
                ],
                [
                    'text' => __("Ask me later", $this->review_args['domain']),
                    'url' => add_query_arg(array($this->review_args['maybe_later_option'] => 'true')),
                ],
                [
                    'text' => __("Already did", $this->review_args['domain']),
                    'url' => add_query_arg(array($this->review_args['already_reviewed_option'] => 'true')),
                ],
                [
                    'text' => __("Don’t Ask Me Again", $this->review_args['domain']),
                    'url' => add_query_arg(array($this->review_args['dismissed_option'] => 'true')),
                ],
            ];

            $html = "";
            // logo
            $html .= '<img class="tablesome__notice__logo" src="' . TABLESOME_URL . '/assets/images/icon-256x256.jpg" alt="Tablesome Logo" />';

            // content
            $html .= '<div class="tablesome__notice__content">';

            // header
            $html .= '<div class="tablesome__notice__header">How do you like Tablesome so far?</div>';

            // body
            $html .= '<div class="tablesome__notice__body">';
            $html .= '<ul class="tablesome__notice__list">';
            foreach ($reviews as $review) {
                $html .= '<li class="tablesome__notice__listitem">';
                $html .= $review['image'];
                $html .= '<a class="tablesome__notice__link" href="' . $review['url'] . '"  target="_blank">' . $review['text'] . '</a>';
                $html .= '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
            // end body

            // footer
            $html .= '<div class="tablesome__notice__footer">';
            foreach ($links as $link) {
                $is_success_button = isset($link['success']) ? 'tablesome__notice__link--success' : '';
                $open_new_window = isset($link['success']) ? 'target="_blank"' : '';
                $html .= '<a class="tablesome__notice__link ' . $is_success_button . '" href="' . $link['url'] . '"  ' . $open_new_window . '>' . $link['text'] . '</a>';
                $html .= '<span class="tablesome__notice__divider"></span>';
            }
            $html .= '</div>';
            // end footer

            $html .= '</div>';
            // end content

            return $html;
        }

        public function get_image($src, $alt = '')
        {
            return "<img class='tablesome__notice__listitem--icon' src='" . $src . "' alt='" . $alt . "'>";
        }

        public function init()
        {
            $this->set_defaults();

            if ($this->can_show_the_notice()) {
                add_action('admin_notices', array($this, 'print_review_notice'));
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

            $this->review_args['already_reviewed_option'] = $this->review_args['post_type'] . '_already_reviewed_option';
            $this->review_args['message'] = $this->get_content();
            $this->review_args['plugin_registered_datetime'] = get_option($this->review_args['post_type'] . '_registered_datetime');
        }

        public function can_show_the_notice()
        {
            /*** Check the notice if it's dismissed or not */
            $notice_dismissed = get_option($this->review_args['dismissed_option']);
            $already_reviewed = get_option($this->review_args['already_reviewed_option']);

            $notice_dismissed = (isset($notice_dismissed) && $notice_dismissed == true);
            $already_reviewed = (isset($already_reviewed) && $already_reviewed == true);

            if ($notice_dismissed || $already_reviewed) {
                return false;
            }

            $posts = get_posts(array('post_type' => $this->review_args['post_type'], 'post_status' => 'publish', 'posts_per_page' => -1));
            $published_posts = count($posts);

            $ask_me_later_value = get_option($this->review_args['maybe_later_option']);

            $can_show_the_notice_after_plugin_registered = $this->can_show_the_notice_after_plugin_registered();
            $can_show_the_notice_again_after_n_days = $this->can_show_the_notice_again_after_n_days($ask_me_later_value);
            $can_show_the_notice_if_limit_met = ($published_posts >= $this->review_args['post_limit']);
            $can_show_the_notice_within_configured_days = $this->can_show_the_notice_within_configured_days();

            $can_show_the_notice_if_limit_met = ($can_show_the_notice_if_limit_met && empty($ask_me_later_value));
            $can_show_the_notice_after_plugin_registered = ($can_show_the_notice_after_plugin_registered && empty($ask_me_later_value));
            $can_show_the_notice_within_configured_days = ($can_show_the_notice_within_configured_days && empty($ask_me_later_value));

            if ($can_show_the_notice_if_limit_met || $can_show_the_notice_after_plugin_registered || $can_show_the_notice_within_configured_days || $can_show_the_notice_again_after_n_days) {
                return true;
            }

            return false;
        }

        public function print_review_notice()
        {
            $this->click_handler();

            $html = '<div class="tablesome__notice tablesome__notice--review notice notice-success is-dismissible ' . $this->review_args['classes'] . '" >';
            $html .= $this->review_args['message'];
            $html .= '</div>';

            echo $html;
        }

        public function click_handler()
        {
            /* 1. When the "Don't ask again" link clicked */
            $dismissed_option = $this->review_args['dismissed_option'];
            if (isset($_GET[$dismissed_option])) {
                update_option($dismissed_option, true);
                $escape_uri = remove_query_arg($dismissed_option);
                $this->refresh_page($escape_uri);
            }

            /* 2. When the "Ask me later" link clicked */
            $maybe_later_option = $this->review_args['maybe_later_option'];
            if (isset($_GET[$maybe_later_option])) {
                update_option($maybe_later_option, date('Y-m-d H:i:s', time()));
                $escape_uri = remove_query_arg($maybe_later_option);
                $this->refresh_page($escape_uri);
            }

            /* 3. When the "Already reviewed" link clicked */
            $already_reviewed_option = $this->review_args['already_reviewed_option'];
            if (isset($_GET[$already_reviewed_option])) {
                update_option($already_reviewed_option, true);
                $escape_uri = remove_query_arg($already_reviewed_option);
                $this->refresh_page($escape_uri);
            }

        }

        public function refresh_page($url)
        {
            echo "<script type='text/javascript'>
               window.location=document.location.href='" . $url . "';
            </script>";
        }

        public function can_show_the_notice_after_plugin_registered()
        {
            $plugin_registered_datetime = isset($this->review_args['plugin_registered_datetime']) ? $this->review_args['plugin_registered_datetime'] : '';
            $interval_limit_for_after_install = isset($this->review_args['days_to_show_notice_after_install']) ? $this->review_args['days_to_show_notice_after_install'] : 0;

            if (empty($plugin_registered_datetime) || empty($interval_limit_for_after_install)) {
                return false;
            }

            $plugin_registered_datetime = new \DateTime($plugin_registered_datetime);
            $now = new \DateTime();
            $diff = $plugin_registered_datetime->diff($now);
            $days = $diff->days;
            return $days >= $interval_limit_for_after_install ? true : false;
        }

        public function can_show_the_notice_again_after_n_days($ask_me_later_value)
        {
            if (empty($ask_me_later_value) || !is_valid_tablesome_date($ask_me_later_value)) {
                return false;
            }

            $datetime = new \DateTime($ask_me_later_value);
            $now = new \DateTime();
            $diff = $datetime->diff($now);
            $days = $diff->days;
            return $days >= $this->review_args['days_to_show_notice_again'] ? true : false;
        }

        public function can_show_the_notice_within_configured_days()
        {
            global $wpdb;
            $plugin_registered_datetime = isset($this->review_args['plugin_registered_datetime']) ? $this->review_args['plugin_registered_datetime'] : '';
            if (empty($plugin_registered_datetime) || !is_valid_tablesome_date($plugin_registered_datetime)) {
                return 0;
            }

            $query = "SELECT COUNT(*) as total_posts_count FROM $wpdb->posts WHERE post_type = '" . $this->review_args['post_type'] . "' AND post_status = 'publish' AND post_date <= '" . $plugin_registered_datetime . "'";
            $total_posts_count = $wpdb->get_var($query);
            return $total_posts_count >= $this->review_args['post_limit'] ? true : false;
        }
    }
}