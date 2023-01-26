<?php

namespace Tablesome\Components\Import;

if (!class_exists('\Tablesome\Components\Import\File_Handler')) {
    class File_Handler
    {

        public function attachment_validation()
        {

            $file = isset($_FILES['file_attachment']) ? $_FILES['file_attachment'] : '';
            if (empty($file)) {
                $response = array(
                    'status' => 'failed',
                    'message' => 'File Not Attached',
                );
                wp_send_json($response);
                wp_die();
            }

            return true;
        }

        public function uploading_the_file($props)
        {

            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $file = isset($_FILES['file_attachment']) ? $_FILES['file_attachment'] : '';
            $uploaded_file = array(
                'name' => $file['name'],
                'type' => $file['type'],
                'tmp_name' => $file['tmp_name'],
                'error' => $file['error'],
                'size' => $file['size'],
            );

            $_FILES['upload_file'] = $uploaded_file;
            $attachment_id = media_handle_upload('upload_file', $props['post_id']);

            if (is_wp_error($attachment_id)) {
                $response = array(
                    'status' => 'failed',
                    'message' => $attachment_id->get_error_message(),
                );
                wp_send_json($response);
                wp_die();
            }
            return $attachment_id;
        }
    }
}
