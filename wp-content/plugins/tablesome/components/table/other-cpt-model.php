<?php

namespace Tablesome\Components\Table;

if (!class_exists('\Tablesome\Components\Table\Other_CPT_Model')) {
    class Other_CPT_Model {

        public $column_formats = array(
            'textarea' => array('post_content', 'post_excerpt'),
            'date' => array('post_date', 'post_date_gmt', 'post_modified', 'post_modified', 'post_modified_gmt'),
            'number' => array('ID'),
            'file' => array('post_featured_image'),
        );

        public $map_operators = array(
            'array' => array(
                'contains' => 'IN',
                'does_not_contain' => 'NOT IN',
                'is' => 'AND',
                'is_not' => 'NOT IN',
                'empty' => 'NOT EXISTS',
                'not_empty' => 'EXISTS',
            ),
            'number' => array(
                '=' => '=',
                '!=' => '!=',
                '<' => '<',
                '>' => '>',
                '<=' => '<=',
                '>=' => '>=',
            ),
            'string' => array(
                'contains' => 'LIKE',
                'does_not_contain' => 'NOT LIKE',
                'starts_with' => 'LIKE',
                'ends_with' => 'LIKE',
                'is' => '=',
                'is_not' => '<>',
                'empty' => '=',
                'not_empty' => '<>',
            ),
        );

        public function get_viewProps($collectionProps) {
            $this->collection = $collectionProps;

            return [
                'collection' => $this->collection,
                'items' => $this->get_itemProps(),
            ];
        }

        public function get_itemProps() {

            $itemsProps = array(
                'columns' => [],
                'rows' => [],
            );

            $load_table_action_meta = $this->get_load_table_action_meta($this->collection);

            $can_load_other_cpt_data = !empty($load_table_action_meta);

            if (!$can_load_other_cpt_data) {
                return $itemsProps;
            }

            $this->map_fields = isset($load_table_action_meta['map_fields']) ? $load_table_action_meta['map_fields'] : [];
            $this->filters = isset($load_table_action_meta['filters']) ? $load_table_action_meta['filters'] : [];

            // Generate Columns
            $this->columns = $this->generate_columns();

            if (empty($this->columns)) {
                return $itemsProps;
            }

            // Get Posts from Params
            $params = $this->get_params();

            add_filter('posts_where', array($this, 'bind_custom_conditions'), 10, 2);
            // Get Posts
            $query = new \WP_Query($params);
            remove_filter('posts_where', array($this, 'bind_custom_conditions'), 10);

            $posts = is_wp_error($query) ? [] : $query->posts;

            // Format Data
            $posts = $this->get_formatted_data($posts);

            return [
                'columns' => $this->columns,
                'rows' => $posts,
            ];
        }

        public function get_load_table_action_meta($collection) {
            $action_meta = [];

            $table_id = isset($collection['post_id']) ? $collection['post_id'] : $collection['table_id'];

            $triggers_meta = get_tablesome_table_triggers($table_id);

            if (empty($triggers_meta)) {
                return $action_meta;
            }

            foreach ($triggers_meta as $trigger) {
                $trigger_id = isset($trigger['trigger_id']) ? $trigger['trigger_id'] : 0;
                $trigger_status = isset($trigger['status']) ? $trigger['status'] : false;
                $actions = isset($trigger['actions']) ? $trigger['actions'] : [];

                if ($trigger_id != 5 || !$trigger_status) {
                    continue;
                }

                foreach ($actions as $action) {
                    $action_id = isset($action['action_id']) ? $action['action_id'] : 0;
                    if ($action_id == 8) {
                        $action_meta = $action;
                        break;
                    }
                }
            }
            return $action_meta;
        }

        public function generate_columns() {
            $columns = [];

            $ID = 1;
            foreach ($this->map_fields as $mapField) {

                $source_field_id = isset($mapField['source_field']['id']) ? $mapField['source_field']['id'] : '';
                $source_field_object_type = isset($mapField['source_field']['object_type']) ? $mapField['source_field']['object_type'] : '';

                $format = 'text';
                if ('post' === $source_field_object_type) {
                    $format = $this->get_column_format_by_name($source_field_id);
                } else if ('taxonomies' === $source_field_object_type) {
                    $format = 'textarea';
                }

                $columns[] = [
                    'id' => $ID,
                    'name' => isset($mapField['destination_field']['label']) ? $mapField['destination_field']['label'] : '',
                    'format' => $format,
                ];
                $ID++;
            }

            return $columns;
        }

        private function get_column_format_by_name($name) {
            $format = 'text';
            foreach ($this->column_formats as $column_format => $field_types) {
                if (in_array($name, $field_types)) {
                    $format = $column_format;
                    break;
                }
            }
            return $format;
        }

        public function get_params() {
            $params = [];
            $number = isset($this->collection['pagination']) && $this->collection['pagination'] == 1 ? $this->collection["display"]['numOfRecordsPerPage'] : -1;

            // 1. Construct basic params
            $params['posts_per_page'] = $number;
            // Must need this property with this value `any` when querying posts by `post_type` field column
            $params['post_type'] = 'any';

            // 2.1 Construct meta_query params
            $meta_query_params = $this->get_meta_query_params();
            $does_meta_params_exist = (!empty($meta_query_params));
            $multiple_meta_params_exist = (!empty($meta_query_params) && (count($meta_query_params) > 1));
            if ($does_meta_params_exist) {
                $params['meta_query'] = $meta_query_params;
            }

            if ($multiple_meta_params_exist) {
                $params['meta_query'] = array_merge(
                    array(
                        'relation' => 'AND',
                    ),
                    $meta_query_params
                );
            }

            // 2.2 Construct tax_query params
            $tax_query_params = $this->get_tax_query_params();
            $does_tax_query_params = (!empty($tax_query_params));
            $multiple_tax_query_params = (!empty($tax_query_params) && (count($tax_query_params) > 1));
            if ($does_tax_query_params) {
                $params['tax_query'] = $tax_query_params;
            }

            if ($multiple_tax_query_params) {
                $params['tax_query'] = array_merge(
                    array(
                        'relation' => "AND",
                    ),
                    $tax_query_params
                );
            }

            // error_log('$params : ' . print_r($params, true));
            return $params;
        }

        private function get_formatted_data($posts) {
            $data = [];
            $default_row_data = $this->get_default_row_data();

            if (empty($posts)) {
                $data[] = $default_row_data;
                return $data;
            }

            foreach ($posts as $post) {
                $data[] = array_merge(
                    $default_row_data,
                    [
                        'record_id' => $post->ID,
                        'content' => $this->get_formatted_row($post),
                        'created_at' => $post->post_date,
                        'updated_at' => $post->modified,
                    ]
                );
            }
            return $data;
        }

        private function get_default_row_data() {
            $date = date('Y-m-d H:i:s');
            return [
                "record_id" => 0,
                "content" => [""],
                "rank_order" => "",
                "created_at" => $date,
                "updated_at" => $date,
            ];
        }

        private function get_formatted_row($post) {
            $single_row = array();

            foreach ($this->columns as $column_index => $column) {
                $column_id = isset($column['id']) ? $column['id'] : 0;
                $column_format = isset($column['format']) ? $column['format'] : 'text';

                $source_field_id = isset($this->map_fields[$column_index]['source_field']['id']) ? $this->map_fields[$column_index]['source_field']['id'] : '';
                $source_field_object_type = isset($this->map_fields[$column_index]['source_field']['object_type']) ? $this->map_fields[$column_index]['source_field']['object_type'] : '';

                $cell_content = '';
                if ('post' === $source_field_object_type) {
                    $cell_content = isset($post->$source_field_id) ? $post->$source_field_id : '';

                    if ('post_author' === $source_field_id) {
                        $cell_content = get_the_author_meta('display_name', $post->post_author);
                    } else if (!empty($cell_content) && $column_format == 'date') {
                        $timestamp = convert_tablesome_date_to_unix_timestamp($cell_content);
                        $cell_content = convert_into_js_timestamp($timestamp);
                    } else if (in_array($source_field_id, ['post_content', 'post_excerpt'])) {
                        $cell_content = $this->get_post_or_excerpt_content($post, $source_field_id);
                    } else if ('post_featured_image' === $source_field_id) {
                        $cell_content = get_post_thumbnail_id($post);
                    }
                } else if ('taxonomies' === $source_field_object_type) {
                    $cell_content = $this->get_post_terms_as_string($post, $source_field_id);
                } else if ('post_meta' === $source_field_object_type) {
                    $cell_content = $this->get_postmeta_content($post, $source_field_id);
                }

                $cell = [
                    'type' => $column_format,
                    'html' => $cell_content,
                    'value' => $cell_content,
                ];

                $cell = apply_filters("tablesome_get_cell_data", $cell);

                $single_row[$column_id] = $cell;
            }

            return $single_row;
        }

        private function get_post_terms_as_string($post, $taxonomy) {
            $terms = wp_get_post_terms($post->ID, $taxonomy, array('fields' => 'all'));

            if (is_wp_error($terms)) {
                return '';
            }
            $data = [];
            foreach ($terms as $term) {
                $permalink = get_term_link($term);
                $data[] = "<a href='{$permalink}' target='_blank'>{$term->name}<a>";
            }
            return implode(", ", $data);
        }

        private function get_post_or_excerpt_content($post, $field_name) {
            $content = $post->post_content;
            $permalink = get_permalink($post);
            $readmore_link = "<a href='{$permalink}' target='_blank'>Read More ></a>";

            if ('post_excerpt' === $field_name) {
                return empty($post->post_excerpt) ? "" : $post->post_excerpt . " " . $readmore_link;
            }

            if (empty($content)) {
                return '';
            }

            return $content;
        }

        private function get_postmeta_content($post, $meta_key) {
            $value = get_post_meta($post->ID, $meta_key, true);
            return isset($value) ? $value : '';
        }

        protected function get_operand_prop_value($condition, $operand_name, $prop_name) {
            return isset($condition[$operand_name][$prop_name]) ? $condition[$operand_name][$prop_name] : '';
        }

        private function get_tax_query_params() {
            $conditions = isset($this->filters[0]['conditions']) ? $this->filters[0]['conditions'] : [];

            $params = [];

            foreach ($conditions as $condition) {

                $source_operand_object_type = $this->get_operand_prop_value($condition, 'operand_1', 'object_type');
                $operator = isset($condition['operator']) ? $condition['operator'] : '';
                $source_operand_data_type = $this->get_operand_prop_value($condition, 'operand_1', 'data_type');
                $taxonomy_names = $this->get_meta_value_by_id($condition, 'operand_1', 'taxonomies_values');
                $taxonomy_name = isset($taxonomy_names[0]) ? $taxonomy_names[0] : '';
                $terms = $this->get_operand_prop_value($condition, 'operand_2', 'value');

                if (empty($operator) || $source_operand_object_type != 'taxonomies' || !taxonomy_exists($taxonomy_name) || empty($terms)) {
                    continue;
                }
                $operator_map_value = isset($this->map_operators[$source_operand_data_type][$operator]) ? $this->map_operators[$source_operand_data_type][$operator] : 'contains';
                $terms = array_filter($terms, 'intval');

                $params[] = array(
                    'taxonomy' => $taxonomy_name,
                    'field' => 'term_id',
                    'terms' => $terms,
                    'operator' => $operator_map_value,
                );
            }
            return $params;
        }

        private function get_meta_query_params() {
            $conditions = isset($this->filters[0]['conditions']) ? $this->filters[0]['conditions'] : [];

            $params = [];

            foreach ($conditions as $condition) {
                $source_operand_object_type = $this->get_operand_prop_value($condition, 'operand_1', 'object_type');
                $operator = isset($condition['operator']) ? $condition['operator'] : '';
                $source_operand_data_type = $this->get_operand_prop_value($condition, 'operand_1', 'data_type');
                $meta_key = $this->get_meta_value_by_id($condition, 'operand_1', 'meta_key');
                $destination_value = $this->get_operand_prop_value($condition, 'operand_2', 'value');

                $operator_map_value = isset($this->map_operators[$source_operand_data_type][$operator]) ? $this->map_operators[$source_operand_data_type][$operator] : '=';
                if ('post_meta' != $source_operand_object_type || empty($meta_key)) {
                    continue;
                }

                $params[] = array(
                    'key' => $meta_key,
                    'value' => $destination_value,
                    'compare' => $operator_map_value,
                );
            }

            return $params;
        }

        public function bind_custom_conditions($where, $query) {
            global $wpdb;
            // 'post_content' => [], 'post_author' => [], 'post_date' => []
            $supported_fields = ['post_title', 'post_content', 'post_type', 'post_status', 'post_author'];
            $conditions = isset($this->filters[0]['conditions']) ? $this->filters[0]['conditions'] : [];
            $statements = '';
            foreach ($conditions as $condition) {

                $source_operand_id = $this->get_operand_prop_value($condition, 'operand_1', 'id');
                $source_operand_data_type = $this->get_operand_prop_value($condition, 'operand_1', 'data_type');
                $destination_value = $this->get_operand_prop_value($condition, 'operand_2', 'value');

                $operator = isset($condition['operator']) ? $condition['operator'] : '';
                $operator_map_value = isset($this->map_operators[$source_operand_data_type][$operator]) ? $this->map_operators[$source_operand_data_type][$operator] : '=';

                if (!in_array($source_operand_id, $supported_fields)) {
                    continue;
                }

                if (in_array($source_operand_id, ['post_title', 'post_content', 'post_type', 'post_status'])) {

                    $content = esc_sql($destination_value);
                    $statements .= " AND {$wpdb->posts}.{$source_operand_id} {$operator_map_value} ";
                    if (in_array($operator, ['contains', 'does_not_contain'])) {
                        $statements .= '\'%' . $wpdb->esc_like($content) . '%\'';
                    } else if (in_array($operator, ['empty', 'not_empty'])) {
                        $statements .= '';
                    } else if (in_array($operator, ['is', 'is_not'])) {
                        $statements .= '\'' . $content . '\'';
                    } else if ('starts_with' === $operator) {
                        $statements .= '\'' . $wpdb->esc_like($content) . '%\'';
                    } else if ('ends_with' === $operator) {
                        $statements .= '\'%' . $wpdb->esc_like($content) . '\'';
                    } else {
                        // Avoid throw error.
                        $statements .= '';
                    }
                } else if ('post_author' === $source_operand_id) {
                    $statements .= " AND {$wpdb->posts}.{$source_operand_id} ";
                    if (in_array($operator, ['contains', 'does_not_contain'])) {
                        $author_ids = empty($destination_value) ? [0] : $destination_value;
                        $statements .= $operator_map_value . ' (' . implode(',', $author_ids) . ')';
                    } else if (in_array($operator, ['is', 'is_not']) && !empty($destination_value)) {
                        $operator_map_value = ('is' === $operator) ? '=' : '<>';
                        $author_id = is_array($destination_value) ? $destination_value[0] : $destination_value;
                        $statements .= $operator_map_value . '\'' . $author_id . '\'';
                    } else if (in_array($operator, ['empty', 'not_empty'])) {
                        $operator_map_value = ('empty' === $operator) ? '=' : '<>';
                        $statements .= $operator_map_value . '\'0\'';
                    } else {
                        // Avoid throw error.
                        $statements .= '= \'0\'';
                    }
                }
            }

            if (!empty($statements)) {
                $where = $where . $statements;
            }

            // error_log('$where : ' . $where);

            return $where;
        }

        public function get_meta_value_by_id($condition, $operand_name, $meta_id_prop_name) {
            $meta_items = isset($condition[$operand_name]['meta']) ? $condition[$operand_name]['meta'] : [];
            $meta_value = '';

            if (empty($meta_items)) {
                return $meta_value;
            }
            foreach ($meta_items as $item) {
                $meta_id_exist = (isset($item['id']) && $item['id'] == $meta_id_prop_name);
                if ($meta_id_exist) {
                    $meta_value = isset($item['value']) ? $item['value'] : '';
                    break;
                }
            }

            return $meta_value;
        }
    }
}