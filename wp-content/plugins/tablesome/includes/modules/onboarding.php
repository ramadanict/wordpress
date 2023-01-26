<?php

namespace Tablesome\Includes\Modules;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Tablesome\Includes\Modules\Onboarding')) {
    class Onboarding
    {
        public function init()
        {
            $option_name = TABLESOME_SAMPLE_TABLE_OPTION;

            $sample_table_exists = \get_option($option_name);

            /** Don't create sample table if the sample-table id is exists in db */
            if (!empty($sample_table_exists)) {
                return;
            }

            $table = new \Tablesome\Includes\Core\Table();
            $post_id = $table->insert_or_update_post(0, $this->get_post_data());

            if (empty($post_id)) {return;}

            /** Store sample table id in db */
            update_option($option_name, $post_id);
            $table_data = $this->get_table_data($post_id);
            // $table->set_table_meta_data($post_id, array());
            $tablesome_data = [
                'columns' => $table_data['columns'],
                'meta' => array(
                    'last_column_id' => $table_data['last_column_id'],
                ),
            ];
            set_tablesome_data($post_id, $tablesome_data);
            $table_meta = get_tablesome_data($post_id);

            $tablesome_db = new \Tablesome\Includes\Modules\TablesomeDB\TablesomeDB();
            $table = $tablesome_db->create_table_instance($post_id);

            $tablesome_db->insert_many($post_id, $table_meta, $table_data['records_inserted']);

        }

        public function get_post_data()
        {
            return array(
                'post_title' => 'Sample Table',
                'post_type' => TABLESOME_CPT,
                'post_content' => '',
                'post_status' => 'publish',
            );
        }

        public function store_default_attachments($post_id, $limit)
        {
            //Ref: https://hotexamples.com/examples/-/-/media_handle_sideload/php-media_handle_sideload-function-examples.html

            $attachments = array();
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            foreach (range(1, $limit) as $no) {

                if ($limit > 11) {break;}

                $url = TABLESOME_URL . 'assets/images/ava' . $no . '.jpg';
                $tmp = download_url($url);

                preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches);
                $file_array = array(
                    'name' => basename($matches[0]),
                    'tmp_name' => $tmp,
                );

                if (is_wp_error($file_array['tmp_name'])) {
                    continue;
                }

                $attachment_id = media_handle_sideload($file_array, $post_id);

                if (is_wp_error($attachment_id)) {
                    continue;
                }
                $attachments[$no] = array(
                    'id' => $attachment_id,
                    'src' => wp_get_attachment_url($attachment_id),
                );
            }
            return $attachments;
        }

        public function get_table_data($post_id)
        {
            $columns = $this->get_default_columns();
            return array(
                'columns' => $columns,
                'last_column_id' => count($columns),
                'records_inserted' => $this->get_default_records($post_id),
            );
        }

        public function get_default_columns()
        {
            return array(
                array(
                    'id' => 1,
                    'name' => 'Image',
                    'format' => 'file',
                ),
                array(
                    'id' => 2,
                    'name' => 'Text',
                    'format' => 'text',
                ),
                array(
                    'id' => 3,
                    'name' => 'Rich Text',
                    'format' => 'textarea',
                ),
                array(
                    'id' => 4,
                    'name' => 'Number',
                    'format' => 'number',
                ),
                array(
                    'id' => 5,
                    'name' => 'Date',
                    'format' => 'date',
                ),
                array(
                    'id' => 6,
                    'name' => 'Email',
                    'format' => 'email',
                ),
                array(
                    'id' => 7,
                    'name' => 'Link',
                    'format' => 'url',
                    'no_follow' => true,
                    'open_in_new_tab' => true,
                    'link_default_text' => 'View Profile',
                    'default_text' => 'View Profile',
                ),
            );
        }

        public function get_default_records($post_id)
        {
            $attachments = $this->store_default_attachments($post_id, 11);

            return array(
                array(
                    'record_id' => 0,
                    'content' => array(
                        array(
                            'value' => isset($attachments[1]['id']) && !empty($attachments[1]['id']) ? $attachments[1]['id'] : '',
                            'html' => '',
                        ),
                        array(
                            'value' => 'Vivian Harrell',
                            'html' => 'Vivian Harrell',
                        ),
                        array(
                            'value' => 'Marriage & Family Therapist License: California / LMFT 47915 School: Ryokan College Year Graduated: 2010',
                            'html' => '<p><span class="ql-size-large">Marriage &amp; Family Therapist</span></p><p><span class="ql-size-small"><span class="ql-cursor">﻿</span></span></p><ol><li><strong>License:</strong> California / LMFT 47915 </li><li><strong>School: Ryokan</strong> College </li><li><strong>Year Graduated: </strong>2010 </li></ol>',
                        ),
                        array(
                            'value' => 33,
                        ),
                        array(
                            'value' => strtotime('January 12, 2009') * 1000,
                            'html' => 'January 12, 2009',
                        ),
                        array(
                            'value' => 'vivianharrell@company.com',
                            'html' => 'vivianharrell@company.com',
                        ),
                        array(
                            'value' => 'https://pauple.com/wordpress-table-plugin/',
                            'linkText' => 'View Profile',
                            'html' => 'https://pauple.com/wordpress-table-plugin/',
                        ),
                    ),
                    'rank_order' => '0|100000:',
                ),
                array(
                    'record_id' => 0,
                    'content' => array(
                        array(
                            'value' => isset($attachments[2]['id']) && !empty($attachments[2]['id']) ? $attachments[2]['id'] : '',
                            'html' => '',
                        ),
                        array(
                            'value' => 'Ashton Cox',
                            'html' => 'Ashton Cox',
                        ),
                        array(
                            'value' => 'Accomplished Explorer Photographer Not so Creative Director Speaker & Author',
                            'html' => '<p class="ql-align-right">Accomplished Explorer</p><p class="ql-align-right">Photographer</p><p class="ql-align-right"><s>Not so</s> Creative Director </p><p class="ql-align-right">Speaker &amp;</p><p class="ql-align-right">Author</p>',
                        ),
                        array(
                            'value' => 40,
                        ),
                        array(
                            'value' => strtotime('November 27, 2008') * 1000,
                            'html' => 'November 27, 2008',
                        ),
                        array(
                            'value' => 'ashtoncox@company.com',
                            'html' => 'ashtoncox@company.com',
                        ),
                        array(
                            'value' => 'https://pauple.com/wordpress-table-plugin/',
                            'linkText' => 'View Profile',
                            'html' => 'https://pauple.com/wordpress-table-plugin/',
                        ),
                    ),
                    'rank_order' => '0|100008:',
                ),
                array(
                    'record_id' => 0,
                    'content' => array(
                        array(
                            'value' => isset($attachments[3]['id']) && !empty($attachments[3]['id']) ? $attachments[3]['id'] : '',
                            'html' => '',
                        ),
                        array(
                            'value' => 'Bruno Nash',
                            'html' => 'Bruno Nash',
                        ),
                        array(
                            'value' => 'TED and TEDx speaker',
                            'html' => '<p class="ql-align-center"><span class="ql-size-huge">TED and TEDx speaker</span><span class="ql-size-large">﻿</span></p>',
                        ),
                        array(
                            'value' => 38,
                        ),
                        array(
                            'value' => strtotime('October 8, 2009') * 1000,
                            'html' => 'October 8, 2009',
                        ),
                        array(
                            'value' => 'brunonash@company.com',
                            'html' => 'brunonash@company.com',
                        ),
                        array(
                            'value' => 'https://pauple.com/wordpress-table-plugin/',
                            'linkText' => 'View Profile',
                            'html' => 'https://pauple.com/wordpress-table-plugin/',
                        ),
                    ),
                    'rank_order' => '0|10000g:',
                ),
                array(
                    'record_id' => 0,
                    'content' => array(
                        array(
                            'value' => isset($attachments[4]['id']) && !empty($attachments[4]['id']) ? $attachments[4]['id'] : '',
                            'html' => '',
                        ),
                        array(
                            'value' => 'Brenden Brenden',
                            'html' => 'Brenden Brenden',
                        ),
                        array(
                            'value' => 'Freelance SEO content writer Content strategist for SaaS and tech brands',
                            'html' => '<ul><li>Freelance SEO <strong>content writer</strong></li><li>Content strategist for <em>SaaS and tech brands</em></li></ul><p>    </p>',
                        ),
                        array(
                            'value' => 39,
                        ),
                        array(
                            'value' => strtotime('December 2, 2012') * 1000,
                            'html' => 'December 2, 2012',
                        ),
                        array(
                            'value' => 'brendenwagner@company.com',
                            'html' => 'brendenwagner@company.com',
                        ),
                        array(
                            'value' => 'https://pauple.com/wordpress-table-plugin/',
                            'linkText' => 'View Profile',
                            'html' => 'https://pauple.com/wordpress-table-plugin/',
                        ),
                    ),
                    'rank_order' => '0|10000o:',
                ),
                array(
                    'record_id' => 0,
                    'content' => array(
                        array(
                            'value' => isset($attachments[5]['id']) && !empty($attachments[5]['id']) ? $attachments[5]['id'] : '',
                            'html' => '',
                        ),
                        array(
                            'value' => 'Cara Stevens',
                            'html' => 'Cara Stevens',
                        ),
                        array(
                            'value' => 'Political Anthropologist 🏳️‍🌈',
                            'html' => '<p><span class="ql-size-large">Political Anthropologist 🏳️‍🌈</span></p>',
                        ),
                        array(
                            'value' => 31,
                        ),
                        array(
                            'value' => strtotime('December 5, 2011') * 1000,
                            'html' => 'December 5, 2011',
                        ),
                        array(
                            'value' => 'carastevens@company.com',
                            'html' => 'carastevens@company.com',
                        ),
                        array(
                            'value' => 'https://pauple.com/wordpress-table-plugin/',
                            'linkText' => 'View Profile',
                            'html' => 'https://pauple.com/wordpress-table-plugin/',
                        ),
                    ),
                    'rank_order' => '0|10000w:',
                ),
                array(
                    'record_id' => 0,
                    'content' => array(
                        array(
                            'value' => isset($attachments[6]['id']) && !empty($attachments[6]['id']) ? $attachments[6]['id'] : '',
                            'html' => '',
                        ),
                        array(
                            'value' => 'Sakura Yamamoto',
                            'html' => 'Sakura Yamamoto',
                        ),
                        array(
                            'value' => 'Creative Consultant for: Murals Lettering Illustration Graphic design See my work here.',
                            'html' => '<p>Creative Consultant for:</p><p><br></p><ul><li>Murals</li><li>Lettering</li><li>Illustration</li><li>Graphic design</li></ul><p><a href="https://pauple.com/wordpress-table-plugin/" rel="noopener noreferrer" target="_blank">See my work here.</a></p>',
                        ),
                        array(
                            'value' => 51,
                        ),
                        array(
                            'value' => strtotime('April 9, 2009') * 1000,
                            'html' => 'April 9, 2009',
                        ),
                        array(
                            'value' => 'sakurayamamoto@company.com',
                            'html' => 'sakurayamamoto@company.com',
                        ),
                        array(
                            'value' => 'https://pauple.com/wordpress-table-plugin/',
                            'linkText' => 'View Profile',
                            'html' => 'https://pauple.com/wordpress-table-plugin/',
                        ),
                    ),
                    'rank_order' => '0|100014:',
                ),
                array(
                    'record_id' => 0,
                    'content' => array(
                        array(
                            'value' => isset($attachments[7]['id']) && !empty($attachments[7]['id']) ? $attachments[7]['id'] : '',
                            'html' => '',
                        ),
                        array(
                            'value' => 'Rhona Davidson',
                            'html' => 'Rhona Davidson',
                        ),
                        array(
                            'value' => 'Systems Administrator',
                            'html' => '<p>Systems Administrator</p>',
                        ),
                        array(
                            'value' => 60,
                        ),
                        array(
                            'value' => strtotime('October 12, 2012') * 1000,
                            'html' => 'October 12, 2012',
                        ),
                        array(
                            'value' => 'rhonadavidson@company.com',
                            'html' => 'rhonadavidson@company.com',
                        ),
                        array(
                            'value' => 'https://pauple.com/wordpress-table-plugin/',
                            'linkText' => 'View Profile',
                            'html' => 'https://pauple.com/wordpress-table-plugin/',
                        ),
                    ),
                    'rank_order' => '0|10001c:',
                ),
                array(
                    'record_id' => 0,
                    'content' => array(
                        array(
                            'value' => isset($attachments[8]['id']) && !empty($attachments[8]['id']) ? $attachments[8]['id'] : '',
                            'html' => '',
                        ),
                        array(
                            'value' => 'Bradley Greer',
                            'html' => 'Bradley Greer',
                        ),
                        array(
                            'value' => 'Marketing Designer',
                            'html' => '<p>Marketing Designer</p>',
                        ),
                        array(
                            'value' => 63,
                        ),

                        array(
                            'value' => strtotime('April 8, 2012') * 1000,
                            'html' => 'April 8, 2012',
                        ),
                        array(
                            'value' => 'bradleygreer@company.com',
                            'html' => 'bradleygreer@company.com',
                        ),
                        array(
                            'value' => 'https://pauple.com/wordpress-table-plugin/',
                            'linkText' => 'View Profile',
                            'html' => 'https://pauple.com/wordpress-table-plugin/',
                        ),
                    ),
                    'rank_order' => '0|10001k:',
                ),
                array(
                    'record_id' => 0,
                    'content' => array(
                        array(
                            'value' => isset($attachments[9]['id']) && !empty($attachments[9]['id']) ? $attachments[9]['id'] : '',
                            'html' => '',
                        ),
                        array(
                            'value' => 'Fiona Green',
                            'html' => 'Fiona Green',
                        ),
                        array(
                            'value' => 'Regional Director',
                            'html' => '<p>Regional Director</p>',
                        ),
                        array(
                            'value' => 25,
                        ),
                        array(
                            'value' => strtotime('December 1, 2012') * 1000,
                            'html' => 'December 1, 2012',
                        ),
                        array(
                            'value' => 'fionagreen@company.com',
                            'html' => 'fionagreen@company.com',
                        ),
                        array(
                            'value' => 'https://pauple.com/wordpress-table-plugin/',
                            'linkText' => 'View Profile',
                            'html' => 'https://pauple.com/wordpress-table-plugin/',
                        ),
                    ),
                    'rank_order' => '0|10001s:',
                ),
                array(
                    'record_id' => 0,
                    'content' => array(
                        array(
                            'value' => isset($attachments[10]['id']) && !empty($attachments[10]['id']) ? $attachments[10]['id'] : '',
                            'html' => '',
                        ),
                        array(
                            'value' => 'Tatyana Fitzpatrick',
                            'html' => 'Tatyana Fitzpatrick',
                        ),
                        array(
                            'value' => 'System Architect',
                            'html' => '<p>System Architect</p>',
                        ),
                        array(
                            'value' => 28,
                        ),
                        array(
                            'value' => strtotime('October 12, 2012') * 1000,
                            'html' => 'October 12, 2012',
                        ),
                        array(
                            'value' => 'tatyanafitzpatrick@company.com',
                            'html' => 'tatyanafitzpatrick@company.com',
                        ),
                        array(
                            'value' => 'https://pauple.com/wordpress-table-plugin/',
                            'linkText' => 'View Profile',
                        ),
                    ),
                    'rank_order' => '0|100020:',
                ),
            );
        }
    }
}