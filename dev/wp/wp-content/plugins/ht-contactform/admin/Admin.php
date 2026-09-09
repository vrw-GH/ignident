<?php
namespace HTContactFormAdmin;
use HTContactFormAdmin\Includes\Api\ApiRegistry;
use HTContactFormAdmin\Includes\PostTypes\FormPostType; 
use HTContactFormAdmin\Includes\Config\Form as FormConfig;
use HTContactFormAdmin\Includes\Assets;
use HTContactFormAdmin\Includes\Notice;
use HTContactFormAdmin\Includes\DiagnosticData;
class Admin {
    private static $instance = null;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }   
    public function __construct() {
        ApiRegistry::get_instance();
        FormPostType::get_instance();
        Assets::get_instance();
        add_action('in_admin_header', [$this, 'remove_admin_notice']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_filter('submenu_file', [$this, 'sync_submenu_active']);
        add_action('init', [$this, 'update_global_settings']);
        add_action('admin_init', [$this, 'diagnostic_data']);
    }

    /**
     * Keep the WP sidebar submenu in sync with the SPA path param.
     * All submenus share one page slug, so WP can't pick the active one on its own.
     */
    public function sync_submenu_active($submenu_file) {
        $slug = 'htcontact-form';

        if (!isset($_GET['page']) || $_GET['page'] !== $slug) {
            return $submenu_file;
        }

        $path = isset($_GET['path']) ? sanitize_key(wp_unslash($_GET['path'])) : '';

        $map = [
            ''             => "admin.php?page=$slug",
            'forms'        => "admin.php?page=$slug&path=forms",
            'editor'       => "admin.php?page=$slug&path=editor",
            'templates'    => "admin.php?page=$slug&path=templates",
            'all_entries'  => "admin.php?page=$slug&path=all_entries",
            'entries'      => "admin.php?page=$slug&path=all_entries",
            'entry'        => "admin.php?page=$slug&path=all_entries",
            'settings'     => "admin.php?page=$slug&path=settings",
            'integrations' => "admin.php?page=$slug&path=integrations",
        ];

        return $map[$path] ?? $map[''];
    }
    
    /**
     * Remove all Notices on admin pages.
     * @return void
     */
    public function remove_admin_notice(){
        $current_screen = get_current_screen();
        $hide_screen = ['toplevel_page_htcontact-form', 'ht-contact-form_page_ht-contactform_extensions', 'update'];
        if(  in_array( $current_screen->id, $hide_screen) ){
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
        }
    }

    public function add_admin_menu() {
        global $submenu;

        $slug        = 'htcontact-form';
        $capability  = 'manage_options';

        add_menu_page(
            esc_html__( 'HT Contact Form', 'ht-contactform' ),
            esc_html__( 'HT Contact Form', 'ht-contactform' ), 
            $capability,
            $slug,
            [$this, 'render_admin_page'],
            'dashicons-email-alt',
            30
        );
        if ( current_user_can( $capability ) ) {
            $submenu[ $slug ][] = [
                esc_html__('Welcome', 'ht-contactform'),
                $capability,
                "admin.php?page=$slug"
            ];
            $submenu[ $slug ][] = [
                esc_html__('Forms', 'ht-contactform'),
                $capability,
                "admin.php?page=$slug&path=forms"
            ];
            $submenu[ $slug ][] = [
                esc_html__('New Form', 'ht-contactform'),
                $capability,
                "admin.php?page=$slug&path=editor"
            ];
            $submenu[ $slug ][] = [
                esc_html__('Templates', 'ht-contactform'),
                $capability,
                "admin.php?page=$slug&path=templates"
            ];
            $submenu[ $slug ][] = [
                esc_html__('Entries', 'ht-contactform'),
                $capability,
                "admin.php?page=$slug&path=all_entries"
            ];
            $submenu[ $slug ][] = [
                esc_html__('Global Settings', 'ht-contactform'),
                $capability,
                "admin.php?page=$slug&path=settings"
            ];
            $submenu[ $slug ][] = [
                esc_html__('Integrations', 'ht-contactform'),
                $capability,
                "admin.php?page=$slug&path=integrations"
            ];
        }
    }

    public function render_admin_page() {
        do_action('ht_contactform_admin_notices');
        echo '<div id="ht-contactform-admin"></div>';
    }

    public function update_global_settings() {
        if(!get_option('ht_form_global_settings')) {
            $global_settings = FormConfig::get_instance()->form_global_settings();
            $default = array_reduce($global_settings, function($carry, $section) {
                $carry[$section['id']] = array_reduce($section['settings'], function($carry, $field) {
                    $carry[$field['id']] = $field['value'];
                    return $carry;
                }, []);
                return $carry;
            }, []);
            update_option('ht_form_global_settings', $default);
        }
    }
    public function diagnostic_data() {
        $diagnostic_data = DiagnosticData::get_instance();
        if ( ! $diagnostic_data->should_show_notice() ) {
            return;
        }
        ob_start();
        $diagnostic_data->show_notices();
        $message = ob_get_clean();
        if (! empty( $message ) ) {
            $notice = Notice::instance();
            $notice::set_notice(
                [
                    'id'          => 'diagnostic-data',
                    'type'        => 'success',
                    'dismissible' => false,
                    'message_type' => 'html',
                    'message'     => $message,
                    'display_after'  => ( 7 * DAY_IN_SECONDS ),
                    'expire_time' => 0,
                    'close_by'    => 'transient'
                ]
            );
        }
    }
}