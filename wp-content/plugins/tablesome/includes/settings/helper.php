<?php

if (!function_exists('csf_validate_number_of_records_per_page')) {

    function csf_validate_number_of_records_per_page($value)
    {
        if ($value > 100) {
            return esc_html__('Value should be 100 or below', 'tablesome');
        } else if ($value < 1) {
            return esc_html__('Value should be 1 at least', 'tablesome');
        }
    }
}
