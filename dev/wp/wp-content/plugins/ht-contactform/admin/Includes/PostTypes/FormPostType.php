<?php

namespace HTContactFormAdmin\Includes\PostTypes;

class FormPostType {
    private static $instance = null;

    public static function get_instance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('init', [$this, 'register_post_type']);
    }

    public function register_post_type() {
        register_post_type('ht_form', [
            'labels' => [
                'name' => __('Forms', 'ht-contactform'),
                'singular_name' => __('Form', 'ht-contactform')
            ],
            'public' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_rest' => false,
            'capability_type' => 'post',
            'supports' => ['title', 'custom-fields', 'revisions'],
            'has_archive' => false,
            'hierarchical' => false
        ]);
    }
}