<?php

// This function converts a number to a string of letters.. Ex: 1 is convert into A, 2 is B, 26 is Z, 27 is AA, 28 is AB, etc.
if (!function_exists('tablesome_num2alpha')) {
    function tablesome_num2alpha($n)
    {
        for ($r = ''; $n >= 0; $n = intval($n / 26) - 1) {
            $r = chr($n % 26 + 0x41) . $r;
        }
        return $r;
    }
}
