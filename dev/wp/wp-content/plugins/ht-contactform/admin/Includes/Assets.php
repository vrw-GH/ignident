<?php
namespace HTContactFormAdmin\Includes;

use HTContactFormAdmin\Includes\Services\Helper;
use HTContactFormAdmin\Includes\Config\Form as FormConfig;
use HTContactFormAdmin\Includes\Config\Welcome;

class Assets {
    private static $instance = null;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_filter('upload_mimes', [$this, 'allow_csv_uploads']);
    }

    /**
     * Allow CSV file uploads in WordPress Media Library
     *
     * @param array $mimes Allowed mime types
     * @return array Modified mime types
     */
    public function allow_csv_uploads($mimes) {
        $mimes['csv'] = 'text/csv';
        return $mimes;
    }

    /**
     * Enqueue admin scripts
     * @param mixed $hook
     * @return void
     */
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_htcontact-form' === $hook || 
        'ht-contact-form_page_ht-contactform_extensions' === $hook) {
            wp_enqueue_script(
                'ht-contactform-menu',
                HTCONTACTFORM_PL_URL . 'assets/js/menu.js',
                [],
                '1.0.0',
                true
            );
        }
        if ('toplevel_page_htcontact-form' === $hook) {
            wp_enqueue_style('htcontact-form-admin-styles');
            wp_enqueue_style('ht-form');

            // Enqueue WordPress Media Library for file uploads (CSV, etc.)
            wp_enqueue_media();

            $form_config = FormConfig::get_instance();
            $welcome_config = Welcome::get_instance();
            wp_enqueue_script(
                'ht-contactform-admin',
                HTCONTACTFORM_PL_URL . 'admin/dist/bundle.js',
                [],
                '1.0.0',
                true
            );
            wp_localize_script(
                'ht-contactform-admin',
                'ht_form',
                [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'rest_url' => esc_url_raw(rest_url()),
                    'nonce' => wp_create_nonce('wp_rest'),
                    'site_url' => get_site_url(),
                    'fields' => $form_config->fields(),
                    'body_tags' => Helper::get_instance()->get_body_tags(),
                    'form_settings' => $form_config->form_settings(),
                    'form_integrations' => $form_config->form_editor_integrations(),
                    'global_settings' => $form_config->form_global_settings(),
                    'integrations' => $form_config->form_integrations(),
                    'data' => [
                        'global_settings' => get_option('ht_form_global_settings', []),
                        'integrations' => get_option('ht_form_integrations', [])
                    ],
                    'welcome' => $welcome_config->welcome(),
                    'banners' => $welcome_config->banners(),
                    'version' => HTCONTACTFORM_VERSION
                ]
            );
        }
    }
}