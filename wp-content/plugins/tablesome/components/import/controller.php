<?php

namespace Tablesome\Components\Import;

if (!class_exists('\Tablesome\Components\Import\Controller')) {
    class Controller
    {
        public function __construct()
        {
            $this->view = new \Tablesome\Components\Import\View();
            $this->table = new \Tablesome\Includes\Core\Table();
            $this->model = new \Tablesome\Components\Import\Model();
        }

        public function render()
        {
            $html = $this->view->get_import_page_content();

            // $rows = $this->table->get_rows(262);
            // $row = $this->table->get_row(262,267520);
            // error_log('[$row] : ' . print_r($row, true));
            // $content = ['OLd','old','old','old'];
            // $update = $this->table->update_row(262,267520,$content);
            // $delete = $this->table->delete_row(262,267520);
            echo $html;
        }

        public function processing_the_importing_data()
        {
            $file_handler = new \Tablesome\Components\Import\File_Handler();

            //form attachment validations
            $file_handler->attachment_validation();

            // sanitizing the import form values
            $props = $this->get_sanitized_props();
            // error_log('$props : ' . print_r($props, true));

            $import_data = $this->model->import_data($props);

            // Get tablesome Edit Page URl
            $edit_page_url = $this->table->get_edit_table_url($import_data['post_id']);

            $response = array(
                'status' => 'success',
                'message' => 'Successfully imported the data.',
                'edit_page_url' => $edit_page_url,
            );

            wp_send_json($response);
            wp_die();
        }

        public function get_sanitized_props()
        {
            $props = [
                'read_first_row_as_column' => false,
                'table_title' => 'Untitled Table',
            ];

            if (isset($_REQUEST['read_first_row_as_column']) && !empty($_REQUEST['read_first_row_as_column'])) {
                $props['read_first_row_as_column'] = sanitize_text_field(wp_unslash($_REQUEST['read_first_row_as_column']));
            }

            if (isset($_REQUEST['table_title']) && !empty($_REQUEST['table_title'])) {
                $props['table_title'] = sanitize_text_field(wp_unslash($_REQUEST['table_title']));
            }

            return $props;
        }
    }
}
