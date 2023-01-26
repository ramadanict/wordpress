<?php

namespace Tablesome\Includes\Settings;

use \Tablesome\Includes\Settings\Tablesome_Getter;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Settings\Settings')) {
    class Settings
    {
        public function __construct()
        {
            $prefix = TABLESOME_OPTIONS;

            add_action("csf_loaded", [$this, "init_settings"]);

            // $this->init_settings();
            add_filter("csf_{$prefix}_output_css", array($this, 'outputCSS_hook'));
        }

        public function init_settings()
        {
            include_once TABLESOME_PATH . 'includes/settings/helper.php';

            if (class_exists('\CSF')) {
                // Set a unique slug-like ID
                $prefix = TABLESOME_OPTIONS; // tablesome_options

                $options = array(
                    'menu_title' => __('Tablesome Settings', "tablesome"),
                    'menu_parent' => 'edit.php?post_type=tablesome_cpt',
                    'menu_type' => 'submenu', // menu, submenu, options, theme, etc.
                    'menu_slug' => 'tablesome-settings',
                    'framework_title' => __('Table Settings', "tablesome"),
                    'theme' => 'light',
                    'show_search' => false,
                    'class' => 'tablesome-settings',
                );

                // Create options
                \CSF::createOptions($prefix, $options);

                $this->sections($prefix);
            }
        }

        public function outputCSS_hook($content)
        {
            if (Tablesome_Getter::get('style_disable')) {
                $content = '';
            }

            return $content;
        }

        public function sections($prefix)
        {

            \CSF::createSection(
                $prefix,
                array(
                    // 'parent' => 'user_access',
                    'id' => 'general',
                    'title' => __('General', 'tablesome'),
                    'icon' => 'fa fa-cogs',
                    'fields' => $this->general_settings($prefix),
                )
            );

            \CSF::createSection(
                $prefix,
                array(
                    // 'parent' => 'user_access',
                    'id' => 'style',
                    'title' => __('Style', 'tablesome'),
                    'icon' => 'fa fa-paint-brush',
                    'fields' => $this->styles($prefix),
                )
            );

            // Create a top-tab
            \CSF::createSection($prefix, array(
                'id' => 'integrations', // Set a unique slug-like ID
                'title' => __('Integrations', 'tablesome'),
                'icon' => 'fa fa-plus-circle',
            ));

            \CSF::createSection(
                $prefix,
                array(
                    'parent' => 'integrations',
                    'id' => 'mailchimp',
                    'title' => __("Mailchimp", "tablesome"),
                    'icon' => 'fab fa-mailchimp',
                    'fields' => $this->mailchimp_settings($prefix),
                )
            );

            \CSF::createSection(
                $prefix,
                array(
                    'parent' => 'integrations',
                    'id' => 'notion',
                    'title' => __("Notion", "tablesome"),
                    'icon' => 'fab fa-neos',
                    'fields' => $this->notion_settings($prefix),
                )
            );

            if (tablesome_enable_feature("gsheet_action")) {
                \CSF::createSection(
                    $prefix,
                    array(
                        'parent' => 'integrations',
                        'id' => 'google',
                        'title' => __("Google", "tablesome"),
                        'icon' => 'fab fa-google',
                        'fields' => $this->google_settings($prefix),
                    )
                );
            }

            if (tablesome_enable_feature("slack_action")) {
                \CSF::createSection(
                    $prefix,
                    array(
                        'parent' => 'integrations',
                        'id' => 'slack',
                        'title' => __("Slack", "tablesome"),
                        'icon' => 'fab fa-slack',
                        'fields' => $this->slack_settings($prefix),
                    )
                );
            }

            if (tablesome_enable_feature("hubspot_action")) {
                \CSF::createSection(
                    $prefix,
                    array(
                        'parent' => 'integrations',
                        'id' => 'hubspot',
                        'title' => __("Hubspot", "tablesome"),
                        'icon' => 'fab fa-hubspot',
                        'fields' => $this->hubspot_settings($prefix),
                    )
                );
            }

            \CSF::createSection(
                $prefix,
                array(
                    // 'parent' => 'user_access',
                    'id' => 'forms',
                    'title' => __('Forms', 'tablesome'),
                    'icon' => 'fa fa-paint-brush',
                    'fields' => $this->form_settings($prefix),
                )
            );

        }

        public function mailchimp_settings($prefix)
        {
            $fields = array(

                // A Callback Field Example
                array(
                    'type' => 'callback',
                    'function' => 'print_tablesome_external_api_connector_html',
                    'args' => 'mailchimp',
                ),
                array(
                    'type' => 'submessage',
                    'style' => 'info',
                    'content' => 'Learn how to find your Mailchimp API Key <a href="https://mailchimp.com/help/about-api-keys/" target="_blank">Here >></a>.',
                ),

            );

            return $fields;
        }

        public function notion_settings($prefix)
        {
            $fields = array(

                // A Callback Field Example
                array(
                    'type' => 'callback',
                    'function' => 'print_tablesome_external_api_connector_html',
                    'args' => 'notion',
                ),
                array(
                    'type' => 'submessage',
                    'style' => 'info',
                    'content' => '<p>Step 1: Generate Notion API Key <a href="https://www.notion.so/my-integrations" target="_blank">Here >></a>.</p><p>Step 2: Share your database with your Notion integration <a href="https://developers.notion.com/docs/getting-started#step-2-share-a-database-with-your-integration" target="_blank">Here >></a>.</p><p>Readme: <a href="https://developers.notion.com/docs/getting-started#getting-started" target="_blank">Here >></a>.</p>',
                ),
            );

            return $fields;
        }

        public function hubspot_settings($prefix)
        {
            $fields = array(
                array(
                    'type' => 'callback',
                    'function' => 'print_tablesome_external_api_connector_html',
                    'args' => 'hubspot',
                ),
            );

            return $fields;
        }

        public function slack_settings($prefix)
        {
            $fields = array(
                array(
                    'type' => 'callback',
                    'function' => 'print_tablesome_external_api_connector_html',
                    'args' => 'slack',
                ),
            );

            return $fields;
        }

        public function general_settings($prefix)
        {
            $pro_feature_label = (!tablesome_fs()->can_use_premium_code__premium_only()) ? "<span class='tablesome__premiumText'>* Pro Feature</span>" : "";
            $fields = array(
                array(
                    'id' => 'show_serial_number_column',
                    'type' => 'switcher',
                    'title' => __("Show Serial Number Column (S.No)", "tablesome"),
                    // 'subtitle' => __("This will affect the front end only", "tablesome"),
                    // 'class' => 'show_ser',
                    'default' => false,
                ),

                array(
                    'id' => 'search',
                    'type' => 'switcher',
                    'title' => __("Search", "tablesome"),
                    'subtitle' => __("This will affect the front end only", "tablesome"),
                    'class' => 'search',
                    'default' => true,
                ),

                array(
                    'id' => 'hide_table_header',
                    'type' => 'switcher',
                    'title' => __("Hide Table Header", "tablesome"),
                    'subtitle' => __("This will affect the front end only", "tablesome"),
                    'class' => 'hide_table_header',
                    'default' => false,
                ),

                array(
                    'id' => 'sorting',
                    'type' => 'switcher',
                    'title' => __("Sorting", "tablesome"),
                    'subtitle' => __("This will affect the front end only", "tablesome"),
                    'dependency' => array('hide_table_header', '==', 'false'),
                    'class' => 'sorting',
                    'default' => true,
                ),

                array(
                    'id' => 'export',
                    'type' => 'switcher',
                    'title' => __("Export", "tablesome"),
                    'subtitle' => __("This will affect the front end only", "tablesome"),
                    'class' => 'export',
                    'default' => false,
                ),

                array(
                    'id' => 'filters',
                    'type' => 'switcher',
                    'title' => __("Filters", "tablesome"),
                    'subtitle' => __("This will affect the front end only", "tablesome"),
                    'class' => 'filters',
                    'default' => false,
                ),

                array(
                    'type' => 'subheading',
                    'content' => __('Pagination Options', 'tablesome'),
                ),
                array(
                    'id' => 'num_of_records_per_page',
                    'type' => 'number',
                    'title' => __("Number Of Records per Page", "tablesome"),
                    'subtitle' => __("Value should between 1-100", "tablesome"),
                    'default' => 10,
                    'attributes' => array(
                        'min' => 1,
                        'max' => 100,
                    ),
                    'validate' => 'csf_validate_number_of_records_per_page',
                ),

                array(
                    'id' => 'pagination_show_first_and_last_buttons',
                    'type' => 'switcher',
                    'title' => __("Pagination: Show First and Last buttons", "tablesome"),
                    'subtitle' => __("This will affect the front end only", "tablesome"),
                    'class' => 'pagination_show_first_and_last_buttons',
                    'default' => true,
                ),

                array(
                    'id' => 'pagination_show_previous_and_next_buttons',
                    'type' => 'switcher',
                    'title' => __("Pagination: Show Previous and Next buttons", "tablesome"),
                    'subtitle' => __("This will affect the front end only", "tablesome"),
                    'class' => 'pagination_show_previous_and_next_buttons',
                    'default' => true,
                ),

                // array(
                //     'id' => 'table_layout_mode',
                //     'type' => 'image_select',
                //     'title' => __('Table Layout', 'tablesome'),
                //     'options' => array(
                //         'auto' => TABLESOME_URL . '/assets/images/table-layout-auto.jpg',
                //         'fixed' => TABLESOME_URL . '/assets/images/table-layout-fixed.jpg'
                //     ),
                //     'default' => 'auto',
                //     'desc' => __("Choose either 1)Table-Layout: Auto or 2)Table-Layout: Fixed", "tablesome"),
                // ),
                array(
                    'type' => 'subheading',
                    'content' => __('Layout Options', 'tablesome'),
                ),

                array(
                    'id' => 'sticky_first_column',
                    'type' => 'switcher',
                    'title' => __("Sticky first Column (Fixed Column / Freeze Column)", "tablesome"),
                    'subtitle' => __("This will affect the front end only", "tablesome"),
                    'default' => false,
                ),

                array(
                    'id' => 'enable_min_column_width',
                    'type' => 'switcher',
                    'title' => __("Enable Column Min-Width", "tablesome"),
                    'subtitle' => __("This will affect the front end only", "tablesome"),
                    'default' => true,
                ),

                array(
                    'id' => 'min_column_width',
                    'type' => 'number',
                    'title' => __("Min Column Width", "tablesome"),
                    // 'output' => [
                    //     '.tablesome__container.min-column-width .tablesome__table .tablesome__cell',
                    //     '.tablesome__container.min-column-width .tablesome__table .tablesome__column',
                    // ],
                    // 'output_mode' => 'min-width',
                    // 'output_important' => true,
                    'unit' => 'px',
                    'dependency' => array('enable_min_column_width', '==', 'true'),
                    'desc' => 'For "Fit to container" display mode, min-width will be applied after table goes to scroll-mode (large number of columns)',
                    'default' => "",
                    // 'desc' => "Works for table layout: auto. Also works for scroll mode when table layout: fixed"
                ),

                array(
                    'id' => 'enable_max_column_width',
                    'type' => 'switcher',
                    'title' => __("Enable Max Column Width", "tablesome"),
                    'subtitle' => __("This will affect the front end only", "tablesome"),
                    'default' => true,
                ),

                array(
                    'id' => 'max_column_width',
                    'type' => 'number',
                    'title' => __("Max Column Width", "tablesome"),
                    // 'output' => [
                    //     '.tablesome__container.max-column-width .tablesome__table .tablesome__cell',
                    //     '.tablesome__container.max-column-width .tablesome__table .tablesome__column',
                    // ],
                    // 'output_mode' => 'max-width',
                    // 'output_important' => true,
                    'unit' => 'px',
                    'dependency' => array('enable_max_column_width', '==', 'true'),
                    'desc' => 'For "Fit to container" display mode, max-width will be applied after table goes to scroll-mode (large number of columns)',
                    'default' => "",
                ),

                array(
                    'id' => 'table_display_mode',
                    'type' => 'image_select',
                    'title' => __('Table Display Mode', 'tablesome'),
                    'options' => array(
                        'fit-to-container' => TABLESOME_URL . '/assets/images/display-mode-fit-to-container.png',
                        'standard' => TABLESOME_URL . '/assets/images/display-mode-standard.png',
                    ),
                    'default' => 'fit-to-container',
                ),

                array(
                    'id' => 'mobile_layout_mode',
                    'type' => 'image_select',
                    'title' => __('Mobile Layout', 'tablesome'),
                    'options' => array(
                        'scroll-mode' => TABLESOME_URL . '/assets/images/scroll-icon.jpg',
                        'stack-mode' => TABLESOME_URL . '/assets/images/stack-icon.jpg',
                    ),
                    'default' => 'scroll-mode',
                ),

                // array(
                //     'id'    => 'max_column_width',
                //     'type'  => 'number',
                //     'title' => 'Max Column Width',
                //         'output' => [
                //         '.tablesome__table.table-layout-mode--block .tablesome__cell',
                //         '.tablesome__table.table-layout-mode--block .tablesome__column',
                //         '.tablesome__table.table-layout-mode--auto .tablesome__cell',
                //         '.tablesome__table.table-layout-mode--auto .tablesome__column',
                //     ],

                //     'output_mode' => 'max-width',
                //     'unit' => 'px',
                //     'desc' => "Works for table layout: auto. Does not work scroll mode, or for table layout: fixed"
                // ),

            );

            return $fields;
        }

        public function styles($prefix)
        {
            // .tablesome__customstyle -- global level
            // .tablesome__customstyle.tablesome__customstyle--table-{id} -- Table level
            $fields = array(
                array(
                    'id' => 'style_disable',
                    'type' => 'switcher',
                    'title' => __("Disable Style", "tablesome"),
                    'class' => 'style_disable',
                    'desc' => __("load default table styles from theme", "tablesome"),
                    'default' => false,
                ),
            );

            $fields = array_merge($fields, array(
                array(
                    'type' => 'subheading',
                    'content' => __("Table Header Styles", "tablesome"),
                    'dependency' => array('style_disable', '==', 'false'),
                ),

                array(
                    'id' => 'style_table_header_border_color',
                    'type' => 'border',
                    'title' => __("Header Border Width and Color", "tablesome"),
                    'all' => true,
                    // 'output' => array('.tablesome__customstyle .tablesome__table .tablesome__row .tablesome__column'),
                    'default' => array(
                        'all' => '1',
                        'style' => 'solid',
                        'color' => '#dddddd',
                    ),
                    'dependency' => array('style_disable', '==', 'false'),
                ),

                array(
                    'id' => 'style_header_background',
                    'type' => 'color',
                    'title' => __("Header background", "tablesome"),
                    // 'output' => array('.tablesome__customstyle .tablesome__table .tablesome__row .tablesome__column'),
                    'output_mode' => 'background-color',
                    'default' => '#1d1f20',
                    'dependency' => array('style_disable', '==', 'false'),
                ),

                array(
                    'id' => 'style_header_typography',
                    'type' => 'typography',
                    'title' => __("Header Typography", "tablesome"),
                    // 'output' => array('.tablesome__customstyle .tablesome__table .tablesome__row .tablesome__column'),
                    'default' => array(
                        'color' => '#ffffff',
                        'font-family' => 'Trebuchet MS',
                        'font-style' => 'normal',
                        'font-size' => '16',
                        'line-height' => '20',
                        'letter-spacing' => '0',
                        'text-align' => 'left',
                        'text-transform' => 'none',
                        'unit' => 'px',
                    ),
                    'dependency' => array('style_disable', '==', 'false'),
                ),

                array(
                    'type' => 'subheading',
                    'content' => __("Table Row and Cell Styles", "tablesome"),
                    'dependency' => array('style_disable', '==', 'false'),
                ),

                array(
                    'id' => 'style_row_background',
                    'type' => 'color',
                    'title' => __("Row background", "tablesome"),
                    // 'output' => '.tablesome__customstyle .tablesome__table .tablesome__row > .tablesome__cell',
                    'output_mode' => 'background-color',
                    'default' => '#ffffff',
                    'dependency' => array('style_disable', '==', 'false'),
                ),

                array(
                    'id' => 'style_row_background_even',
                    'type' => 'color',
                    'title' => __("Row background for alternate rows", "tablesome"),
                    // 'output' => '.tablesome__customstyle .tablesome__table .tablesome__row:nth-child(even) > .tablesome__cell',
                    'output_mode' => 'background-color',
                    'default' => '#f2f2f2',
                    'dependency' => array('style_disable', '==', 'false'),
                ),

                array(
                    'id' => 'style_cell_border',
                    'type' => 'border',
                    'title' => __("Cell Border Width and Color", "tablesome"),
                    'all' => true,
                    // 'output' => array('.tablesome__customstyle .tablesome__table .tablesome__row .tablesome__cell'),
                    'default' => array(
                        'all' => '1',
                        'style' => 'solid',
                        'color' => '#dddddd',
                    ),
                    'dependency' => array('style_disable', '==', 'false'),
                ),

                array(
                    'id' => 'style_cell_typography',
                    'type' => 'typography',
                    'title' => __("Cell Typography", "tablesome"),
                    // 'output' => array('.tablesome__customstyle .tablesome__table .tablesome__row .tablesome__cell'),
                    'default' => array(
                        'color' => '#000000',
                        'font-family' => 'Trebuchet MS',
                        'font-style' => 'normal',
                        'font-size' => '16',
                        'line-height' => '20',
                        'letter-spacing' => '0',
                        'text-align' => 'left',
                        'text-transform' => 'none',
                        'unit' => 'px',
                    ),
                    'dependency' => array('style_disable', '==', 'false'),
                ),

            ));

            return $fields;
        }

        public function form_settings($prefix)
        {
            $fields = array(
                array(
                    'id' => 'enabled_all_forms_entries',
                    'type' => 'switcher',
                    'title' => __("Store all Forms Entries", "tablesome"),
                    'class' => 'enabled_all_forms_entries',
                    'default' => true,
                ),
            );

            return $fields;
        }

        public function google_settings($prefix)
        {
            return array(
                array(
                    'type' => 'callback',
                    'function' => 'print_tablesome_external_api_connector_html',
                    'args' => 'google',
                ),
                array(
                    'type' => 'submessage',
                    'style' => 'info',
                    'content' => 'Please make sure all permission are checked. If not, disconnect and connect again!',
                ),
            );
        }

    }
}
