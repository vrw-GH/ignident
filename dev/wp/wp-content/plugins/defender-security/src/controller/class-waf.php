<?php
/**
 * Handles Web Application Firewall related functionality.
 *
 * @package WP_Defender\Controller
 */

namespace WP_Defender\Controller;

use WP_Defender\Controller;
use Calotes\Component\Response;
use WP_Defender\Behavior\WPMUDEV;

/**
 * Handles the settings, data, and other functionality related to the Web
 * Application Firewall (WAF).
 */
class WAF extends Controller {

	/**
	 * The slug identifier for this controller.
	 *
	 * @var string
	 */
	public $slug = 'wdf-waf';
	/**
	 * The WPMUDEV instance used for interacting with WPMUDEV services.
	 *
	 * @var WPMUDEV
	 */
	private $wpmudev;

	/**
	 * Initializes the model and service, registers routes, and sets up scheduled events if the model is active.
	 */
	public function __construct() {
		$this->wpmudev = wd_di()->get( WPMUDEV::class );

		// Return the constructor and do not register WAF page if Whitelable is enabled.
		if ( $this->wpmudev->is_whitelabel_enabled() ) {
			return;
		}

		$this->register_page(
			esc_html__( 'WAF', 'defender-security' ),
			$this->slug,
			array(
				&$this,
				'main_view',
			),
			$this->parent_slug
		);
		add_action( 'defender_enqueue_assets', array( &$this, 'enqueue_assets' ) );
		$this->register_routes();
	}

	/**
	 * Enqueues scripts and styles for this page.
	 * Only enqueues assets if the page is active.
	 */
	public function enqueue_assets() {
		if ( ! $this->is_page_active() ) {
			return;
		}
		wp_localize_script( 'def-waf', 'waf', $this->data_frontend() );
		wp_enqueue_script( 'def-waf' );
		$this->enqueue_main_assets();
	}

	/**
	 * Renders the main view for this page.
	 */
	public function main_view() {
		$this->render( 'main' );
	}

	/**
	 * Retrieves the WAF status for a given site.
	 *
	 * @param  int $site_id  Site ID to check.
	 *
	 * @return mixed Returns false on failure, true if WAF is enabled, or a cached value of "enabled" or "disabled".
	 */
	public function get_waf_status( $site_id ) {
		if ( false === $site_id ) {
			return false;
		}

		$cached = get_site_transient( 'def_waf_status' );
		if ( in_array( $cached, array( 'enabled', 'disabled' ), true ) ) {
			return 'enabled' === $cached;
		}

		$ret = $this->wpmudev->make_wpmu_request( WPMUDEV::API_WAF );
		if ( is_wp_error( $ret ) ) {
			return false;
		}
		$status = $ret['waf']['is_active'];
		set_site_transient( 'def_waf_status', true === $status ? 'enabled' : 'disabled', 300 );

		return $status;
	}

	/**
	 * Remove the cache and return latest data.
	 *
	 * @return Response
	 * @defender_route
	 */
	public function recheck(): Response {
		delete_site_transient( 'def_waf_status' );

		return new Response( true, array( 'waf' => $this->data_frontend()['waf'] ) );
	}

	/**
	 * Checks if the WAF dashboard widget should be shown.
	 *
	 * @return bool Whether to show the widget or not.
	 */
	public function maybe_show_dashboard_widget(): bool {
		if (
			// Not hosted on us.
			! $this->wpmudev->is_wpmu_hosting()
			// Pro.
			&& true === $this->wpmudev->is_pro()
			// Enable whitelabel.
			&& $this->wpmudev->is_whitelabel_enabled()
		) {
			// Hide it.
			return false;
		}

		return true;
	}

	/**
	 * Converts the current object state to an array.
	 *
	 * @return array The array representation of the object.
	 */
	public function to_array(): array {
		$site_id = $this->wpmudev->get_site_id();

		return array(
			'waf' => array(
				'hosted'     => $this->wpmudev->is_wpmu_hosting(),
				'status'     => $this->get_waf_status( $site_id ),
				'maybe_show' => $this->maybe_show_dashboard_widget(),
			),
		);
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
	 * Provides data for the frontend.
	 *
	 * @return array An array of data for the frontend.
	 */
	public function data_frontend(): array {
		$site_id = $this->wpmudev->get_site_id();

		return array_merge(
			array(
				'site_id' => $site_id,
				'waf'     => array(
					'hosted' => $this->wpmudev->is_wpmu_hosting(),
					'status' => $this->get_waf_status( $site_id ),
				),
			),
			$this->dump_routes_and_nonces()
		);
	}

	/**
	 * Imports data into the model.
	 *
	 * @param  array $data  Data to be imported into the model.
	 */
	public function import_data( array $data ) {
	}

	/**
	 * Exports strings.
	 *
	 * @return array An array of strings.
	 */
	public function export_strings(): array {
		return array();
	}
}
