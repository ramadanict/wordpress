<?php

namespace Tablesome\Components\Table\Settings\Config;

use \Tablesome\Includes\Settings\Typography;

if (!class_exists('\Tablesome\Components\Table\Settings\Config\Common')) {
    class Common
    {
        public static function get_default()
        {
            return [
                "primary" => "#1d1f20",
                "secondary" => "#3e4244",
                "white" => "#ffffff",
                "border" => "#1d1f20",
                "font-family" => "Trebuchet MS",
            ];
        }

        public static function get_breakpoint()
        {
            $devices = [
                [
                    "id" => "desktop",
                    "icon" => "&#xe30c;",
                ],
                [
                    "id" => "mobile",
                    "icon" => "&#xe32c;",
                    "is_premium" => tablesome_fs()->can_use_premium_code__premium_only(),
                ],
            ];

            return $devices;
        }

        public static function get_font_family()
        {
            $font_family_group = [];
            $webfonts = Typography::get_csf_fonts()["webfonts"];

            $font_index = 0;
            foreach ($webfonts as $font_key => $font) {

                $font_family_group[$font_index] = [
                    "id" => $font_key,
                    "group" => $font["label"],
                    "options" => [],
                ];

                foreach ($font["fonts"] as $font_family_key => $font_family) {
                    $value = $font_key == "google" ? $font_family_key : $font_family;

                    array_push($font_family_group[$font_index]["options"], [
                        "id" => $value,
                        "label" => $value,
                    ]);
                }
                $font_index++;
            }

            return $font_family_group;
        }

        public static function get_border_style()
        {
            return [
                [
                    "id" => "solid",
                    "label" => "Solid",
                ],
                [
                    "id" => "dashed",
                    "label" => "Dashed",
                ],
                [
                    "id" => "dotted",
                    "label" => "Dotted",
                ],
                [
                    "id" => "double",
                    "label" => "Double",
                ],
                [
                    "id" => "inset",
                    "label" => "Inset",
                ],
                [
                    "id" => "outset",
                    "label" => "Outset",
                ],
                [
                    "id" => "groove",
                    "label" => "Groove",
                ],
                [
                    "id" => "ridge",
                    "label" => "Ridge",
                ],
                [
                    "id" => "none",
                    "label" => "None",
                ],
            ];
        }

        public static function get_text_alignment()
        {
            return [
                [
                    "id" => "left",
                    "icon" => "&#xe236;",
                    "label" => "Left",
                ],
                [
                    "id" => "center",
                    "icon" => "&#xe234;",
                    "label" => "Center",
                ],
                [
                    "id" => "right",
                    "icon" => "&#xe237;",
                    "label" => "Right",
                ],
                [
                    "id" => "justify",
                    "icon" => "&#xe235;",
                    "label" => "Justify",
                ],
            ];
        }

        public static function get_content_alignment()
        {
            return [
                [
                    "id" => "start",
                    "icon" => "&#xe236;",
                    "label" => "Left",
                ],
                [
                    "id" => "center",
                    "icon" => "&#xe234;",
                    "label" => "Center",
                ],
                [
                    "id" => "end",
                    "icon" => "&#xe237;",
                    "label" => "Right",
                ],
                [
                    "id" => "space-between",
                    "icon" => "&#xe235;",
                    "label" => "Between",
                ],
            ];
        }

    }
}
