<?php
/**
 * Displays common data on different plugin pages, such as a notice about extended IP detection logic.
 *
 * @package WP_Defender\Controller
 */

namespace WP_Defender\Controller;

use WP_Defender\Controller;
use Calotes\Component\Response;

/**
 * Displays common data on different plugin pages, e.g. a notice about extended IP detection logic.
 *
 * @since 4.4.2
 */
class General_Notice extends Controller {

	public const IP_DETECTION_SLUG = 'wd_show_ip_detection_notice';

	/**
	 * Initializes the model and service, registers routes, and sets up scheduled events if the model is active.
	 */
	public function __construct() {
		$this->register_routes();
	}

	/**
	 * Get the notice data.
	 *
	 * @return array
	 */
	public function get_notice_data(): array {
		$result = $this->dump_routes_and_nonces();

		return array(
			'routes' => $result['routes'],
			'nonces' => $result['nonces'],
		);
	}

	/**
	 * Close the IP detection notice.
	 *
	 * @return Response
	 * @defender_route
	 */
	public function close_ip_detection_notice(): Response {
		self::delete_slugs();

		return new Response( true, array() );
	}

	/**
	 * Delete the IP detection slug.
	 */
	public static function delete_slugs(): void {
		delete_site_option( self::IP_DETECTION_SLUG );
	}

	/**
	 * Check if the notice should be shown.
	 *
	 * @return bool
	 */
	public function show_notice(): bool {
		return (bool) get_site_option( self::IP_DETECTION_SLUG );
	}


	/**
	 * Delete all the data & the cache.
	 */
	public function remove_data() {
	}

	/**
	 * Exports strings.
	 *
	 * @return array An array of strings.
	 */
	public function export_strings(): array {
		return array();
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
	 * Remove settings method.
	 *
	 * @return void
	 */
	public function remove_settings(): void {
	}

	/**
	 * Provides data for the frontend.
	 *
	 * @return array An array of data for the frontend.
	 */
	public function data_frontend(): array {
		return array();
	}
}
