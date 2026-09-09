<?php
namespace HTContactForm\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Dashboard_Widget {

	public function __construct() {
		// Priority 1 WooLentor, priority 2 HT Mega — defer to either if active.
		if ( ! is_plugin_active( 'woolentor-addons/woolentor_addons_elementor.php' ) &&
			 ! is_plugin_active( 'ht-mega-for-elementor/htmega_addons_elementor.php' ) ) {
			add_action( 'wp_dashboard_setup', [ $this, 'dashboard_widget' ], 9999 );
		}
	}

	/**
	 * Register Dashboard Widget
	 * @return void
	 */
	public function dashboard_widget() {
		// Another HasThemes plugin already registered this widget — skip.
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes['dashboard']['normal']['core']['hasthemes-dashboard-stories'] ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'hasthemes-dashboard-stories',
			esc_html__( 'HasThemes Stories', 'ht-contactform' ),
			[ $this, 'dashboard_hasthemes_widget' ]
		);

		$dashboard_widget_list = $wp_meta_boxes['dashboard']['normal']['core'];

		$hastheme_dashboard_widget = [
			'hasthemes-dashboard-stories' => $dashboard_widget_list['hasthemes-dashboard-stories']
		];

		$all_dashboard_widget = array_merge( $hastheme_dashboard_widget, $dashboard_widget_list );

		$wp_meta_boxes['dashboard']['normal']['core'] = $all_dashboard_widget;
	}

	/**
	 * Dashboard Stories Widget
	 * @return void
	 */
	public function dashboard_hasthemes_widget() {
		ob_start();
		self::load_template( 'widget' );
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Template load
	 * @param string $template template suffix
	 * @return void
	 */
	private static function load_template( $template ) {
		$tmp_file = HTCONTACTFORM_PL_PATH . 'include/Admin/templates/dashboard-' . $template . '.php';
		if ( file_exists( $tmp_file ) ) {
			include_once( $tmp_file );
		}
	}

}

new Dashboard_Widget();
