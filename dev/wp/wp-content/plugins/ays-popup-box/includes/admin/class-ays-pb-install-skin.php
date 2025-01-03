<?php

namespace AysPb\Helpers;

/** \WP_Upgrader_Skin class */
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

class Ays_Pb_Plugin_Silent_Upgrader_Skin extends \WP_Upgrader_Skin {

	/**
	 * Empty out the header of its HTML content and only check to see if it has
	 * been performed or not.
	 *
	 * @since 1.3.9
	 */
	public function header() {
	}

	/**
	 * Empty out the footer of its HTML contents.
	 *
	 * @since 1.3.9
	 */
	public function footer() {
	}

	/**
	 * Instead of outputting HTML for errors, just return them.
	 * Ajax request will just ignore it.
	 *
	 * @since 1.3.9
	 *
	 * @param array $errors Array of errors with the install process.
	 *
	 * @return array
	 */
	public function error( $errors ) {
		return $errors;
	}

	/**
	 * Empty out JavaScript output that calls function to decrement the update counts.
	 *
	 * @since 1.3.9
	 *
	 * @param string $type Type of update count to decrement.
	 */
	public function decrement_update_count( $type ) {
	}
}
