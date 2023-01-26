<?php

namespace Tablesome\Includes\Pages;

if (!class_exists('\Tablesome\Includes\Pages\Onboarding')) {
    class Onboarding
    {
        public function render()
        {
            echo '<div id="tablesome-onboarding-page"><h1> Welcome to Tablesome</h1></div>';
        }
    }
}
