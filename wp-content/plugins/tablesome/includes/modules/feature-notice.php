<?php

namespace Tablesome\Includes\Modules;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Feature_Notice')) {
    class Feature_Notice
    {
        public $dismissed_option_name = 'tablesome_feature_notice_dismissed';

        public $version = '0.9.9';

        public $feature_page_url = 'https://tablesomewp.com/releases/';

        public $content = 'Table Export is now free.';

        public function init()
        {
            $this->click_handler();

            if ($this->can_show_the_notice()) {
                add_action('admin_notices', array($this, 'print_feature_notice'));
            }
        }

        public function can_show_the_notice()
        {
            $dismissed_version = get_option($this->dismissed_option_name);

            if (!$dismissed_version || (version_compare(TABLESOME_VERSION, $this->version, '>=') && version_compare($this->version, $dismissed_version, '>'))) {
                return true;
            }

            return false;
        }

        public function print_feature_notice()
        {
            $html = '<div class="tablesome__notice tablesome__notice--feature notice is-dismissible tablesome-rounded">';

            $html .= '<img class="tablesome__notice__logo tablesome__notice__logo--small" src="' . TABLESOME_URL . '/assets/images/icon-256x256.jpg" alt="Tablesome Logo">';
            // content
            $html .= '<div class="tablesome__notice__content">';
            $html .= '<span class="tablesome-font-bold tablesome-mr-1">What\'s new in Tablesome:</span> ' . $this->get_content();
            $html .= '</div>';

            $html .= '<a role="button" class="notice-dismiss tablesome-no-underline" href="' . add_query_arg(array('tablesome_feature_notice_dismissed' => 'true')) . '"></a>';
            $html .= '</div>';
            echo $html;
        }

        public function get_content()
        {
            $content = $this->content;
            $content .= '<a class="tablesome__notice__button tablesome__featureLinkButton" href="' . $this->feature_page_url . '" target="_blank">See Product Updates</a>';
            return $content;
        }

        public function click_handler()
        {
            $notice_dismissed = isset($_GET['tablesome_feature_notice_dismissed']) ? $_GET['tablesome_feature_notice_dismissed'] : false;

            if ($notice_dismissed) {
                update_option($this->dismissed_option_name, TABLESOME_VERSION);
                $escape_uri = remove_query_arg(array('tablesome_feature_notice_dismissed'));
                $this->redirect_to($escape_uri);
            }
        }

        public function redirect_to($url)
        {
            echo "<script type='text/javascript'>
                let url = '" . $url . "';
                window.location.href = url;
            </script>";
        }

        public function update_feature_notice_dismissal_data_via_ajax()
        {
            update_option($this->dismissed_option_name, TABLESOME_VERSION);

            $response = array(
                'status' => 'success',
                'data' => array(
                    'url' => $this->feature_page_url,
                ),
            );

            wp_send_json($response);
            wp_die();
        }
    }
}
