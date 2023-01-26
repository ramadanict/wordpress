<?php

namespace Tablesome\Components\Table;

if (!class_exists('\Tablesome\Components\Table\Controller')) {
    class Controller
    {
        public function __construct()
        {
            $this->model = new \Tablesome\Components\Table\Model();
            $this->other_cpt_model = new \Tablesome\Components\Table\Other_CPT_Model();
            $this->filter_table_model = new \Tablesome\Components\Table\Filter_Table_Model();
            $this->view = new \Tablesome\Components\Table\View();

            new \Tablesome\Components\CellTypes\File\Controller();
            new \Tablesome\Components\CellTypes\Text();
            new \Tablesome\Components\CellTypes\Textarea();
            new \Tablesome\Components\CellTypes\Number();
            new \Tablesome\Components\CellTypes\Email();
            new \Tablesome\Components\CellTypes\URL();
            new \Tablesome\Components\CellTypes\Email();
            new \Tablesome\Components\CellTypes\Date();
            new \Tablesome\Components\CellTypes\Button();
        }

        public function get_view($args = [])
        {
            $viewProps = $this->get_table_viewProps($args);
            return $this->view->get_table($viewProps);
        }

        public function get_table_level_settings($table_id = 0)
        {
            return [
                "editorState" => $this->model->get_editor_state($table_id),
                "display" => $this->model->get_display_settings($table_id),
                "style" => $this->model->get_style_settings($table_id),
            ];
        }

        public function get_table_viewProps($args = [])
        {
            global $tablesome_tables_collection;

            $utils = new \Tablesome\Includes\Utils();
            $table_id = isset($args['post_id']) ? $args['post_id'] : $args['table_id'];
            $is_premium_and_not_dashboard_page = tablesome_fs()->can_use_premium_code__premium_only() && !is_admin();

            $load_table_action_meta = $utils->get_workflow_action_meta($table_id);
            $can_load_other_cpt_data = !empty($load_table_action_meta) && $is_premium_and_not_dashboard_page;

            $filter_table_action_meta = $utils->get_workflow_action_meta($table_id, 9);
            $can_filter_table_data = !empty($filter_table_action_meta) && $is_premium_and_not_dashboard_page && pauple_is_feature_active("filter_table_action");

            if ($can_load_other_cpt_data) {
                $collectionProps = $this->model->get_collectionProps($args);
                $viewProps = $this->other_cpt_model->get_viewProps($collectionProps);

                $collectionProps["pagination"] = false;
                $tablesome_tables_collection[] = $this->other_cpt_model->get_viewProps($collectionProps);
            } elseif ($can_filter_table_data) {
                $collectionProps = $this->model->get_collectionProps($args);
                $collectionProps["table_id"] = $table_id;
                $collectionProps["filter_table_action_meta"] = $filter_table_action_meta;
                $viewProps = $this->filter_table_model->get_viewProps($collectionProps);
                $tablesome_tables_collection[] = $viewProps;
            } else {
                $viewProps = $this->model->get_viewProps($args);
                $args["pagination"] = false;
                $tablesome_tables_collection[] = $this->model->get_viewProps($args);
            }

            return $viewProps;
        }
    } // END CLASS
}
