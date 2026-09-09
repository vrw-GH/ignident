<?php
/**
 * Plugin Name: HT Contact Form – Drag & Drop Form Builder for WordPress
 * Description: The Contact Form Widget is a elementor addons and Gutenberg blocks for WordPress.
 * Plugin URI:  https://theplugindemo.com/ht-contactform/
 * Author:      HT Plugins
 * Author URI:  https://profiles.wordpress.org/htplugins/#content-plugins
 * Version:     2.10.1
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ht-contactform
 * Domain Path: /languages
 * Elementor tested up to: 4.2.3
 * Elementor Pro tested up to: 4.2.2
*/

use HTContactFormAdmin\Admin;
use HTContactForm\Base;

if( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

if ( ! function_exists('is_plugin_active')) { include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); }

define( 'HTCONTACTFORM_VERSION', '2.10.1' );
define( 'HTCONTACTFORM_PL_ROOT', __FILE__ );
define( 'HTCONTACTFORM_PL_URL', plugins_url( '/', HTCONTACTFORM_PL_ROOT ) );
define( 'HTCONTACTFORM_PL_PATH', plugin_dir_path( HTCONTACTFORM_PL_ROOT ) );

// Check Plugins is Installed or not
if( !function_exists( 'htcontactform_is_plugins_active' ) ){
    function htcontactform_is_plugins_active( $pl_file_path = NULL ){
        $installed_plugins_list = get_plugins();
        return isset( $installed_plugins_list[$pl_file_path] );
    }
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return strpos($haystack, $needle) !== false;
    }
}

class HT_FORM_BUILDER {
    private static $_instance;

    public static function get_instance() {
        if ( ! isset( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    public function __construct() {
        require_once HTCONTACTFORM_PL_PATH . 'vendor/autoload.php';
        add_action('init', [$this, 'load_text_domain']);
        add_action('activated_plugin', [$this, 'redirection_page']);
        add_action('plugins_loaded', [$this, 'include_files']);
        add_action('elementor/widgets/widgets_registered', [$this, 'elementor_widgets']);
        add_action('init', [$this, 'register_scripts']);
        add_action('init', [$this, 'preview']);
    }

    /**
     * Load Text Domain
     * @return void
     */
    public function load_text_domain() {
        load_plugin_textdomain( 'ht-contactform', false, plugin_basename( dirname( HTCONTACTFORM_PL_ROOT ) ) . '/languages' );
    }

    /**
     * Handle redirection after plugin activation
     * @param mixed $plugin
     * @return void
     */
    public function redirection_page( $plugin ){
        if( plugin_basename( HTCONTACTFORM_PL_ROOT ) == $plugin ){
            if( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ){
                if( ( htcontactform_is_plugins_active( 'extension-for-cf7-pro/cf7-extensions-pro.php' ) && is_plugin_inactive( 'extension-for-cf7-pro/cf7-extensions-pro.php' ) ) || ! htcontactform_is_plugins_active( 'extension-for-cf7-pro/cf7-extensions-pro.php' ) ){
                    wp_redirect( admin_url("admin.php?page=htcontact-form") );
                }else{
                    wp_redirect( admin_url("admin.php?page=htcontact-form") );
                }
                die();
            }
        }
    }

    /**
     * Include files
     * @return void
     */
    public function include_files() {
        include HTCONTACTFORM_PL_PATH . 'include/Templates/TemplateRegistry.php';
        include HTCONTACTFORM_PL_PATH . 'blocks/block-init.php';
        include HTCONTACTFORM_PL_PATH . 'include/class/Api.php';
        include HTCONTACTFORM_PL_PATH . 'include/recommended-plugins/class.recommended-plugins.php';
        add_action('init', function() {
            include HTCONTACTFORM_PL_PATH . 'include/recommended-plugins/recommended-plugins.php';
            Admin::get_instance();
            Base::get_instance();
        });

        if ( is_admin() ) {
            include HTCONTACTFORM_PL_PATH . 'include/Admin/class-dashboard-widget-api.php';
            include HTCONTACTFORM_PL_PATH . 'include/Admin/class-dashboard-widget.php';
        }

    }

    /**
     * Elementor Widgets File Call
     * @return void
     */
    public function elementor_widgets() {
        include HTCONTACTFORM_PL_PATH . 'include/Widgets/elementor_widgets.php';
        include HTCONTACTFORM_PL_PATH . 'include/Widgets/ht_form_widgets.php';
    }
    
    /**
     * Register scripts
     * @return void
     */
    public function register_scripts() {
        $global_settings = get_option('ht_form_global_settings', []);
        // Register styles
        wp_register_style(
            'htcontact-form-admin-styles',
            HTCONTACTFORM_PL_URL . 'assets/css/htcontact-form-admin.css',
            [],
            '1.0.0'
        );
        wp_register_style(
            'ht-form',
            HTCONTACTFORM_PL_URL . 'assets/css/form.css',
            [],
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION
        );
        wp_register_style(
            'ht-select', 
            HTCONTACTFORM_PL_URL . 'assets/lib/choices/choices.min.css', 
            [], 
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION
        );
        wp_register_style(
            'ht-intl-tel-input', 
            HTCONTACTFORM_PL_URL . 'assets/lib/intl-tel-input/intlTelInput.min.css', 
            [], 
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION
        );
        wp_register_style(
            'ht-flatpickr', 
            HTCONTACTFORM_PL_URL . 'assets/lib/flatpickr/flatpickr.min.css', 
            [], 
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION
        );
        wp_register_style(
            'ht-country-select', 
            HTCONTACTFORM_PL_URL . 'assets/lib/country-select/countrySelect.min.css', 
            [], 
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION
        );
        // Register scripts
        wp_register_script(
            'ht-select', 
            HTCONTACTFORM_PL_URL . 'assets/lib/choices/choices.min.js', 
            [], 
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION, 
            true
        );
        wp_register_script(
            'ht-intl-tel-input', 
            HTCONTACTFORM_PL_URL . 'assets/lib/intl-tel-input/intlTelInput.min.js', 
            [], 
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION, 
            true
        );
        wp_register_script(
            'ht-imask', 
            HTCONTACTFORM_PL_URL . 'assets/lib/inputmask/inputmask.min.js', 
            [], 
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION, 
            true
        );
        wp_register_script(
            'ht-flatpickr', 
            HTCONTACTFORM_PL_URL . 'assets/lib/flatpickr/flatpickr.min.js', 
            [], 
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION, 
            true
        );
        wp_register_script(
            'ht-country-select', 
            HTCONTACTFORM_PL_URL . 'assets/lib/country-select/countrySelect.min.js', 
            ['jquery'], 
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION, 
            true
        );
        wp_register_script(
            'ht-axios', 
            HTCONTACTFORM_PL_URL . 'assets/lib/axios/axios.min.js', 
            [], 
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION, 
            true
        );
        wp_register_script(
            'ht-recaptcha-v2', 
            'https://www.google.com/recaptcha/api.js', 
            [], 
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION, 
            true
        );
        // Register reCAPTCHA v3 script with site key
        $v3_site_key = $global_settings['captcha']['recaptcha_v3_site_key'] ?? '';
        if(!empty($v3_site_key)){
            wp_register_script(
                'ht-recaptcha-v3',
                'https://www.google.com/recaptcha/api.js?render=' . esc_attr($v3_site_key),
                [],
                defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION,
                true
            );
        }
        // hCaptcha script
        wp_register_script(
            'ht-hcaptcha',
            'https://js.hcaptcha.com/1/api.js',
            [],
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION,
            true
        );
        wp_register_style('ht-filepond', 'https://unpkg.com/filepond/dist/filepond.css');
        wp_register_style('ht-filepond-preview', 'https://unpkg.com/filepond-plugin-image-preview@4.6.12/dist/filepond-plugin-image-preview.min.css');
        wp_register_script('ht-filepond', 'https://unpkg.com/filepond/dist/filepond.js', [], null, true);
        wp_register_script('ht-filepond-preview', 'https://unpkg.com/filepond-plugin-image-preview@4.6.12/dist/filepond-plugin-image-preview.min.js', [], null, true);
        wp_register_script('ht-filepond-size-validate', 'https://unpkg.com/filepond-plugin-file-validate-size@2.2.8/dist/filepond-plugin-file-validate-size.min.js', [], null, true);
        wp_register_script('ht-filepond-type-validate', 'https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js', [], null, true);

        // Quill.js for Rich Text Editor
        wp_register_style('ht-quill', HTCONTACTFORM_PL_URL . 'assets/lib/quill/quill.snow.css', [], '2.0.3');
        wp_register_script('ht-quill', HTCONTACTFORM_PL_URL . 'assets/lib/quill/quill.min.js', [], '2.0.3', true);

        // Signature Pad for Signature field
        wp_register_script('ht-signature-pad', HTCONTACTFORM_PL_URL . 'assets/lib/signature_pad/signature_pad.umd.min.js', [], '4.1.7', true);

        wp_register_script(
            'ht-form',
            HTCONTACTFORM_PL_URL . 'assets/js/form.js',
            ['jquery', 'wp-i18n'],
            defined('WP_DEBUG') && WP_DEBUG ? time() : HTCONTACTFORM_VERSION,
            true
        );
    }

    /**
     * Handle form preview requests
     * @return void
     */
    public function preview() {
        // Check if this is a form preview request
        if (!empty($_GET['ht_form_preview']) && !empty($_GET['form_id'])) {
            $form_id = intval($_GET['form_id']);
            
            // Enqueue necessary styles
            wp_enqueue_style('ht-form');
            
            // Output a minimal page with just the form
            add_action('template_redirect', function() use ($form_id) {
                // Create a minimal HTML page
                echo '<!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Form Preview</title>
                    ' . wp_head() . '
                    <style>
                        body {
                            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                            max-width: 800px;
                            margin: 40px auto;
                            padding: 20px;
                            background: #f5f5f5;
                        }
                        .preview-container {
                            background: #fff;
                            padding: 30px;
                            border-radius: 6px;
                            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                        }
                        .preview-header {
                            margin-bottom: 20px;
                            padding-bottom: 15px;
                            border-bottom: 1px solid #eee;
                        }
                        .preview-title {
                            margin: 0;
                            color: #333;
                        }
                    </style>
                </head>
                <body>
                    <div class="preview-container">
                        <div class="preview-header">
                            <h2 class="preview-title">Form Preview</h2>
                        </div>
                        ' . do_shortcode("[ht_form id=\"{$form_id}\"]") . '
                    </div>
                    ' . wp_footer() . '
                </body>
                </html>';
                exit;
            }, 5);
        }
    }
}
HT_FORM_BUILDER::get_instance();