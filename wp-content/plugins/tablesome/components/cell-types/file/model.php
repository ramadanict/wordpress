<?php

namespace Tablesome\Components\CellTypes\File;

if (!class_exists('\Tablesome\Components\CellTypes\File\Model')) {
    class Model
    {
        public function __construct()
        {

        }

        public function get_media_data($data)
        {
            $attachment_id = $data["value"];
            $post = get_post($attachment_id);
            // $post_mime_type = get_post_mime_type($attachment_id);

            if (!isset($post->post_mime_type) && empty($post->post_mime_type)) {
                return $attachment_id;
            }

            $post_mime_type = $post->post_mime_type;
            $media_type = explode('/', $post_mime_type)[0]; // video|image
            // $meta_data = wp_get_attachment_metadata($attachment_id);
            // error_log('meta_data : ' . print_r($meta_data, true));
            $link = isset($data["link"]) && !empty($data["link"]) ? $data["link"] : $post->guid;
            $image_url = wp_get_attachment_image_url($attachment_id, "full");
            $data = [
                'attachment' => [
                    "url" => $image_url,
                ],
                'type' => $media_type,
                'url' => $image_url,
                'link' => $link,
                'name' => $post->post_name,
                'mime_type' => $post_mime_type,
            ];

            return $data;
        }

    } // END CLASS
}
