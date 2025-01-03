<?php

use AysPb\Helpers\Ays_Pb_Plugin_Silent_Upgrader_Skin;

/**
 * Skin for on-the-fly addon installations.
 *
 * @since 1.0.0
 * @since 1.3.9 Extend Advanced_Related_Posts_Plugin_Silent_Upgrader_Skin and clean up the class.
 */
class Ays_Pb_Install_Skin extends Ays_Pb_Plugin_Silent_Upgrader_Skin {

	/**
	 * Instead of outputting HTML for errors, json_encode the errors and send them
	 * back to the Ajax script for processing.
	 *
	 * @since 1.0.0
	 *
	 * @param array $errors Array of errors with the install process.
	 */
	public function error( $errors ) {

		if ( ! empty( $errors ) ) {
			wp_send_json_error( $errors );
		}
	}
}
