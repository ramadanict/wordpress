<?php

namespace Tablesome\Includes\Modules;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

// Purpose: Proxy for the any module to measure performance
// Example Usage:

/*
$myque = new \Tablesome\Includes\Modules\Myque\Myque();
$proxy = new Proxy();
$records = $proxy->get_rows($args); // call the method of original class through proxy
 */

if (!class_exists('\Tablesome\Includes\Modules\Proxy')) {
    class Proxy
    {
        public function __construct($object)
        {
            $this->object = $object;
        }

        public function __call($method, $args)
        {
            // Run before code here
            $starttime = microtime(true);
            $start_memory = memory_get_peak_usage();

            // Invoke original method on our proxied object
            $output = call_user_func_array(array($this->object, $method), $args);

            // Run after code here
            $endtime = microtime(true);
            $end_memory = memory_get_peak_usage();
            $duration = $this->get_duration($endtime, $starttime); //calculates total time taken
            $memory_used = $this->convert($end_memory - $start_memory); // in KB
            error_log('$memory_used; : ' . $memory_used);
            error_log('$end_memory; : ' . $this->convert($end_memory));
            error_log(' - duration : ' . $duration);

            return $output;
        }

        public function convert($size)
        {

            if ($size == 0) {
                return 0;
            }

            $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
            return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
        }

        public function get_duration($endtime, $starttime)
        {
            $duration = $endtime - $starttime;
            $hours = (int) ($duration / 60 / 60);
            $minutes = (int) ($duration / 60) - $hours * 60;
            $seconds = (float) $duration - $hours * 60 * 60 - $minutes * 60;

            return $seconds;
        }
    }
}
