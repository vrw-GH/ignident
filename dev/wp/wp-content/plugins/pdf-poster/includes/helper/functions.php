<?php
if (!defined('ABSPATH')) exit;


if (!function_exists('pdfp_get_option')) {
    function pdfp_get_option($key)
    {
        $option = get_option($key);

        return function ($key, $default = null, $is_boolean = false, $key2 = null) use ($option) {
            $value = $default;
            if ($key2 !== null && isset($option[$key][$key2])) {
                $value = $option[$key][$key2];
            } elseif (isset($option[$key])) {
                $value = $option[$key];
            }
            if ($is_boolean) {
                return $value === '1';
            }
            return $value;
        };
    }
}

// pdfp_get_post_meta
if (!function_exists('pdfp__get_post_meta')) {
    function pdfp__get_post_meta($post_id, $key, $single = true)
    {
        $meta = get_post_meta($post_id, $key, $single);
        return function ($key, $default = null, $is_boolean = false) use ($meta) {
            if (isset($meta[$key])) {
                if ($is_boolean) {
                    return $meta[$key] === '1';
                }
                return $meta[$key];
            }
            return $default;
        };
    }
}
