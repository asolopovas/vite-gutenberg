<?php

add_filter('deprecated_constructor_trigger_error', '__return_false');
add_filter('deprecated_function_trigger_error', '__return_false');
add_filter('deprecated_file_trigger_error', '__return_false');
add_filter('deprecated_argument_trigger_error', '__return_false');
add_filter('deprecated_hook_trigger_error', '__return_false');

function vite_modules_wp_enqueue($tag)
{
    if (str_contains($tag, "#module")) {
        $tag = str_replace("type='text/javascript'", "", $tag);
        $tag = str_replace("#module", "", $tag);
        $tag = str_replace('src=', "type=\"module\" src=", $tag);
    }

    return $tag;
}

add_filter('script_loader_tag', 'vite_modules_wp_enqueue', 10, 3);
