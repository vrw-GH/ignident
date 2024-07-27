<?php
/**
 * Handles onboarding.
 *
 * @package WP_Defender\Controller
 */

namespace WP_Defender\Controller;

use WP_Defender\Event;
use Calotes\Helper\HTTP;
use Calotes\Helper\Route;
use WP_Defender\Behavior\WPMUDEV;
use WP_Defender\Model\Setting\Login_Lockout;
use WP_Defender\Model\Setting\Notfound_Lockout;
use WP_Defender\Model\Setting\User_Agent_Lockout;
use WP_Defender\Model\Setting\Main_Setting as Model_Main_Setting;

/**
 * This class is only used once, after the activation on a fresh install.
 * We will use this for activating & presets other module settings.
 */
class Onboard extends Event {

	/**
	 * The slug identifier for this controller.
	 *
	 * @var string
	 */
	public $slug = 'wp-defender';

	/**
	 * Initializes the model and service, registers routes, and sets up scheduled events if the model is active.
	 */
	public function __construct() {
		$this->attach_behavior( WPMUDEV::class, WPMUDEV::class );
		$this->add_main_page();
		add_action( 'defender_enqueue_assets', array( &$this, 'enqueue_assets' ) );
	}

	/**
	 * Registers the main page in the WordPress admin menu.
	 */
	protected function add_main_page() {
		$this->register_page(
			$this->get_menu_title(),
			$this->parent_slug,
			array(
				&$this,
				'main_view',
			),
			null,
			$this->get_menu_icon()
		);
	}

	/**
	 * Renders the main view for this page.
	 */
	public function main_view() {
		$class = wd_di()->get( Security_Tweaks::class );
		$class->refresh_tweaks_status();
		$this->render( 'main' );
	}

	/**
	 * Method to handle the activation process.
	 *
	 * @defender_route
	 */
	public function activating() {
		if ( ! $this->check_permission() || ! $this->verify_nonce( 'activatingonboard' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid', 'defender-security' ) ) );
		}

		$this->attach_behavior( WPMUDEV::class, WPMUDEV::class );

		update_site_option( 'wp_defender_shown_activator', true );
		delete_site_option( 'wp_defender_is_free_activated' );
		if ( $this->is_pro() ) {
			$this->preset_audit();
			$this->preset_blacklist_monitor();
		}
		$this->preset_firewall();
		$this->resolve_security_tweaks();
		$this->preset_scanning();

		$this->maybe_tracking( 'Activate & Configure' );
		// @since 4.2.0 No display the Data Tracking after the Onboarding.
		Data_Tracking::delete_modal_key();

		wp_send_json_success();
	}

	/**
	 * Enable blacklist status.
	 */
	private function preset_blacklist_monitor() {
		$this->make_wpmu_request(
			WPMUDEV::API_BLACKLIST,
			array(),
			array(
				'method' => 'POST',
			)
		);
	}

	/**
	 * Sets the audit logging to enabled and saves the changes.
	 *
	 * @return void
	 */
	private function preset_audit() {
		$audit          = new \WP_Defender\Model\Setting\Audit_Logging();
		$audit->enabled = true;
		$audit->save();
	}

	/**
	 * Sets up the preset scanning configuration and creates a new scan.
	 *
	 * @return void
	 */
	private function preset_scanning() {
		$model = new \WP_Defender\Model\Setting\Scan();
		$model->save();
		// Create new scan.
		$ret = \WP_Defender\Model\Scan::create();
		if ( ! is_wp_error( $ret ) ) {
			$scan_controller = wd_di()->get( Scan::class );

			$scan_controller->scan_started_analytics(
				array(
					'Triggered From' => 'Plugin',
					'Scan Type'      => 'Install',
				)
			);

			$scan_controller->do_async_scan( 'install' );
		}
	}

	/**
	 * Sets up the preset firewall configuration.
	 *
	 * @return void
	 */
	private function preset_firewall() {
		$lockout          = new Login_Lockout();
		$lockout->enabled = true;
		$lockout->save();
		$nf          = new Notfound_Lockout();
		$nf->enabled = true;
		$nf->save();
		$ua          = new User_Agent_Lockout();
		$ua->enabled = true;
		$ua->save();
	}

	/**
	 * Resolve all tweaks that we can.
	 *
	 * @since 2.4.6 Remove tweaks that can be added to wp-config.php manually: 'hide-error', 'disable-file-editor'.
	 */
	private function resolve_security_tweaks() {
		$slugs = array(
			'disable-xml-rpc',
			'login-duration',
			'disable-trackback',
			'prevent-enum-users',
		);
		$class = wd_di()->get( Security_Tweaks::class );
		$class->refresh_tweaks_status();
		$class->security_tweaks_auto_action( $slugs, 'resolve' );
	}

	/**
	 *  Get modules in quick setup.
	 *
	 * @return array
	 */
	private function get_modules(): array {
		$modules = array(
			'Firewall',
			'Recommendations',
		);
		if ( $this->is_pro() ) {
			$modules[] = 'Malware Scanning';
			$modules[] = 'Audit Logging';
			$modules[] = 'Blocklist Monitor';
		} else {
			$modules[] = 'WP file scanning';
		}

		return $modules;
	}

	/**
	 *  Maybe track usage data.
	 *
	 * @param  string $action  Action name.
	 */
	private function maybe_tracking( string $action ) {
		$usage_data_state = HTTP::post( 'usage_tracking', '' );
		// Track it, the default option value is changed to True.
		if ( 'true' === $usage_data_state ) {
			wd_di()->get( Model_Main_Setting::class )->toggle_tracking( true );
			$this->track_opt_toggle( true, 'Wizard' );
			$this->track_feature(
				'def_quick_setup',
				array(
					'module' => $this->get_modules(),
					'action' => $action,
				)
			);
		}
	}

	/**
	 *  Skip onboarding.
	 *
	 * @defender_route
	 */
	public function skip() {
		if ( ! $this->check_permission() || ! $this->verify_nonce( 'skiponboard' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid', 'defender-security' ) ) );
		}

		update_site_option( 'wp_defender_shown_activator', true );
		delete_site_option( 'wp_defender_is_free_activated' );
		// @since 4.2.0 No display the Data Tracking after the Onboarding.
		Data_Tracking::delete_modal_key();

		$this->maybe_tracking( 'Start from scratch' );
		wp_send_json_success();
	}

	/**
	 * Enqueues scripts and styles for this page.
	 * Only enqueues assets if the page is active.
	 */
	public function enqueue_assets() {
		if ( ! $this->is_page_active() ) {
			return;
		}

		wp_localize_script( 'def-onboard', 'onboard', $this->data_frontend() );
		wp_enqueue_script( 'def-onboard' );
		$this->enqueue_main_assets();
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
	}

	/**
	 *  Add classes to admin body.
	 *
	 * @param  string $classes  Admin body classes.
	 *
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		$classes .= ' wdf-full-screen ';

		return $classes;
	}

	/**
	 * Removes settings for all submodules.
	 */
	public function remove_settings() {
	}

	/**
	 * Delete all the data & the cache.
	 */
	public function remove_data() {
	}

	/**
	 * Exports strings.
	 *
	 * @return void An array of strings.
	 */
	public function export_strings() {
	}

	/**
	 * Converts the current object state to an array.
	 *
	 * @return array The array representation of the object.
	 */
	public function to_array(): array {
		return array();
	}

	/**
	 * Imports data into the model.
	 *
	 * @param  array $data  Data to be imported into the model.
	 */
	public function import_data( array $data ) {
	}

	/**
	 * Provides data for the frontend.
	 *
	 * @return array An array of data for the frontend.
	 */
	public function data_frontend(): array {
		[ $endpoints, $nonces ] = Route::export_routes( 'onboard' );

		return array(
			'endpoints' => $endpoints,
			'nonces'    => $nonces,
			'misc'      => array(
				'state_usage_tracking' => wd_di()->get( Model_Main_Setting::class )->usage_tracking,
				'privacy_link'         => Model_Main_Setting::PRIVACY_LINK,
			),
		);
	}
}
