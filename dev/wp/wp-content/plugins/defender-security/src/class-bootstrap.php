<?php

namespace WP_Defender;

use WP_Defender\Traits\Defender_Bootstrap;

/**
 * Class Bootstrap
 *
 * @package WP_Defender
 */
class Bootstrap {
	use Defender_Bootstrap;

	/**
	 * Activation.
	 */
	public function activation_hook(): void {
		$this->activation_hook_common();
		$this->set_free_installation_timestamp();
	}

	/**
	 * Add option with plugin install date.
	 *
	 * @since 2.4
	 */
	protected function set_free_installation_timestamp(): void {
		// It's for both cases because don’t have a Pro checking during plugin activation.
		if ( empty( get_site_option( 'defender_free_install_date' ) ) ) {
			update_site_option( 'defender_free_install_date', time() );
		}
	}

	/**
	 * Load all modules.
	 */
	public function init_modules(): void {
		$this->init_modules_common();
		$this->init_free_dashboard();
	}

	/**
	 * Load Free Dashboard module.
	 */
	public function init_free_dashboard(): void {
		$file_path = defender_path( 'extra/free-dashboard/module.php' );
		if ( file_exists( $file_path ) ) {
			require_once $file_path;

			add_filter( 'wdev_email_message_' . DEFENDER_PLUGIN_BASENAME, array( &$this, 'defender_ads_message' ) );

			$screen_prefix = 'defender';
			$screen_suffix = is_multisite() ? '-network' : '';

			$free_install_date = get_site_option( 'defender_free_install_date', false );

			do_action(
				'wpmudev_register_notices',
				'defender',
				array(
					'basename'     => DEFENDER_PLUGIN_BASENAME,
					'title'        => 'Defender',
					'wp_slug'      => 'defender-security',
					'cta_email'    => __( 'Get Secure!', 'defender-security' ),
					'installed_on' => $free_install_date ?: time(),
					'screens'      => array(
						'toplevel_page_wp-defender' . $screen_suffix,
						$screen_prefix . '_page_wdf-hardener' . $screen_suffix,
						$screen_prefix . '_page_wdf-scan' . $screen_suffix,
						$screen_prefix . '_page_wdf-logging' . $screen_suffix,
						$screen_prefix . '_page_wdf-ip-lockout' . $screen_suffix,
						$screen_prefix . '_page_wdf-waf' . $screen_suffix,
						$screen_prefix . '_page_wdf-2fa' . $screen_suffix,
						$screen_prefix . '_page_wdf-advanced-tools' . $screen_suffix,
						$screen_prefix . '_page_wdf-notification' . $screen_suffix,
						$screen_prefix . '_page_wdf-setting' . $screen_suffix,
						$screen_prefix . '_page_wdf-tutorial' . $screen_suffix,
					),
				)
			);
		}
	}

	/**
	 * @param string $message
	 *
	 * @return string
	 */
	public function defender_ads_message( string $message ): string {
		return __( "You're awesome for installing Defender! Are you interested in how to make the most of this plugin? We've collected all the best security resources we know in a single email - just for users of Defender!", 'defender-security' );
	}
}
