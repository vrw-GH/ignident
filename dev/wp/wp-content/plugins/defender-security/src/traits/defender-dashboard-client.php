<?php
/**
 * Handle Dashboard based functionalities of WPMUDEV class.
 *
 * @package WP_Defender\Traits
 */

namespace WP_Defender\Traits;

use WPMUDEV_Dashboard;
use WP_Defender\Component\Config\Config_Hub_Helper;

trait Defender_Dashboard_Client {

	/**
	 * Get membership status.
	 *
	 * @return bool
	 */
	public function is_pro(): bool {
		return $this->get_apikey() !== false;
	}

	/**
	 * Check if user is a paid one in WPMU DEV.
	 *
	 * @return bool
	 */
	public function is_member(): bool {
		if (
			$this->is_dash_activated() && method_exists( WPMUDEV_Dashboard::$upgrader, 'user_can_install' )
		) {
			return WPMUDEV_Dashboard::$upgrader->user_can_install(
				Config_Hub_Helper::WDP_ID,
				true
			);
		}

		return false;
	}

	/**
	 * Check if user is a WPMU DEV admin.
	 *
	 * @return bool
	 * @since 2.6.3
	 */
	public function is_wpmu_dev_admin(): bool {
		if ( $this->is_dash_activated() && method_exists( 'WPMUDEV_Dashboard_Site', 'allowed_user' ) ) {
			return WPMUDEV_Dashboard::$site->allowed_user( get_current_user_id() );
		}

		return false;
	}

	/**
	 * Bring the plugin menu title.
	 *
	 * @return string Menu title.
	 */
	public function get_menu_title(): string {
		if ( $this->is_pro() ) {
			$menu_title = esc_html__( 'Defender Pro', 'defender-security' );
		} else {
			// Check if it's Pro but user logged the WPMU DEV Dashboard out.
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$menu_title = file_exists( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . WP_DEFENDER_PRO_PATH )
							&& is_plugin_active( WP_DEFENDER_PRO_PATH )
				? esc_html__( 'Defender Pro', 'defender-security' )
				: esc_html__( 'Defender', 'defender-security' );
		}

		return $menu_title;
	}

	/**
	 * Return icon svg image.
	 *
	 * @return string
	 */
	public function get_menu_icon(): string {
		ob_start();
		?>
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path fill-rule="evenodd" clip-rule="evenodd"
					d="M9.99999 2.08899L3 4.21792V9.99502H9.99912V18.001H10C13.47 18.001 17 13.9231 17 11.0045V9.99501H9.99999V2.08899ZM10 0L1 2.73862V11.0045C1 15.1125 5.49 20 10 20C14.51 20 19 15.1225 19 11.0045V2.73862L10 0Z"
					fill="#F0F6FC"/>
		</svg>
		<?php
		$svg = ob_get_clean();

		return 'data:image/svg+xml;base64,' . base64_encode( $svg ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}

	/**
	 * Check if Dash plugin is installed and activated.
	 *
	 * @return bool
	 * @since 3.4.0
	 */
	public function is_dash_activated(): bool {
		return class_exists( 'WPMUDEV_Dashboard' );
	}

	/**
	 * Check if site is connected to HUB.
	 *
	 * @return bool
	 * @since 3.6.0 Added changes after the implementation of TFH on the hub.
	 * @since 3.4.0
	 */
	public function is_site_connected_to_hub(): bool {
		// The case if Pro version is activated, it is TFH account and a site is from 3rd party hosting.
		if ( WP_DEFENDER_PRO_PATH === DEFENDER_PLUGIN_BASENAME && $this->is_another_hosted_site_connected_to_tfh() ) {
			return ! empty( WPMUDEV_Dashboard::$api->get_key() );
		} else {
			$hub_site_id = $this->get_site_id();

			return ! empty( $hub_site_id ) && is_int( $hub_site_id );
		}
	}

	/**
	 * Check if HUB option is disabled, e.g. Global IP.
	 *
	 * @return bool
	 */
	public function is_disabled_hub_option(): bool {
		return ! $this->is_dash_activated() || ! $this->is_site_connected_to_hub();
	}

	/**
	 * Get remote access.
	 */
	public function get_remote_access() {
		// Use backward compatibility.
		if ( WPMUDEV_Dashboard::$version > '4.11.9' ) {
			return WPMUDEV_Dashboard::$site->get( 'remote_access' );
		} else {
			return WPMUDEV_Dashboard::$site->get_option( 'remote_access' );
		}
	}
}
