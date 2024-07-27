<?php

namespace WP_Defender\Behavior;

use Calotes\Component\Behavior;
use WP_Defender\Traits\IO;
use WP_Defender\Traits\Formats;
use WP_Defender\Traits\Defender_Dashboard_Client;
use WP_Defender\Traits\Defender_Hub_Client;
use WP_Defender\Behavior\WPMUDEV_Const_Interface;

/**
 * This class contains everything relate to WPMUDEV.
 * Class WPMUDEV
 *
 * @package WP_Defender\Behavior
 * @since 2.2
 */
class WPMUDEV extends Behavior implements WPMUDEV_Const_Interface {
	use IO;
	use Formats;
	use Defender_Dashboard_Client;
	use Defender_Hub_Client;

	/**
	 * Get membership status.
	 *
	 * @return bool
	 */
	public function is_pro() {
		return false;
	}

	/**
	 * Get WPMUDEV API KEY.
	 *
	 * @return bool|string
	 */
	public function get_apikey() {
		if ( ! class_exists( '\WPMUDEV_Dashboard' ) ) {
			return false;
		}

		\WPMUDEV_Dashboard::instance();

		$membership_status = \WPMUDEV_Dashboard::$api->get_membership_data();
		$key               = \WPMUDEV_Dashboard::$api->get_key();

		if ( ! empty( $membership_status['hub_site_id'] ) && ! empty( $key ) ) {
			return $key;
		}

		return false;
	}

	/**
	 * @since 2.5.5 Use Whitelabel filters instead of calling the whitelabel functions directly.
	 * @return bool
	 */
	public function is_whitelabel_enabled() {
		return false;
	}

	/**
	 * Hide WPMU DEV urls for the current user if:
	 *  1) Whitelabel option is enabled,
	 *  2) the user is not listed in WPMU DEV > Settings > Permissions.
	 *
	 * @return bool
	 * @since 4.1.0
	 */
	public function hide_wpmu_dev_urls(): bool {
		return false;
	}

	/**
	 * Show support links if:
	 * plugin version isn't Free,
	 * Whitelabel is disabled.
	 *
	 * @return bool
	 * @since 2.5.5
	 */
	public function show_support_links() {
		return false;
	}
}
