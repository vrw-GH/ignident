<?php
/**
 * Manages the display of "What's New" modals  dashboard.
 *
 * @package WP_Defender\Component
 */

namespace WP_Defender\Component;

use WP_Defender\Component;
use WP_Defender\Behavior\WPMUDEV;

/**
 * Use different actions for "What's new" modals.
 *
 * @since 2.5.5
 */
class Feature_Modal extends Component {

	/**
	 * Feature data for the last active "What's new" modal.
	 */
	public const FEATURE_SLUG    = 'wd_show_feature_automatic_ip_detection';
	public const FEATURE_VERSION = '4.9.0';

	/**
	 * Get modals that are displayed on the Dashboard page.
	 *
	 * @param  bool $force_hide  The modal is not displayed in every version, so we need a flag that will control the
	 *                       display process.
	 *
	 * @return array
	 * @since 2.7.0 Use one template for Welcome modal and dynamic data.
	 */
	public function get_dashboard_modals( $force_hide = false ): array {
		$wpmudev      = wd_di()->get( WPMUDEV::class );
		$is_displayed = $force_hide ? false : $this->display_last_modal( self::FEATURE_SLUG );
		$title        = esc_html__( 'Enhanced Security with Automatic IP Detection', 'defender-security' );
		$desc         = sprintf(
		/* translators: 1. Open tag. 2. Close tag. */
			esc_html__(
				'Defender now identifies IP headers with improved accuracy and can effectively avoid false blocks. This ensures enhanced site security and compatibility across different hosting environments. For more details about the %1$sAutomatic IP Detection%2$s feature, please visit the Firewall Settings page.',
				'defender-security'
			),
			'<strong style="font-weight: 700;">',
			'</strong>'
		);
		$button_title      = esc_html__( 'Go to Settings', 'defender-security' );
		$button_title_free = $button_title;

		return array(
			'show_welcome_modal' => $is_displayed,
			'welcome_modal'      => array(
				'title'              => $title,
				'desc'               => $desc,
				'banner_1x'          => defender_asset_url( '/assets/img/modal/welcome-modal.png' ),
				'banner_2x'          => defender_asset_url( '/assets/img/modal/welcome-modal@2x.png' ),
				'banner_alt'         => esc_html__( 'Modal for Automatic IP Detection', 'defender-security' ),
				'button_title'       => $button_title,
				'button_title_free'  => $button_title_free,
				// Additional information.
				'additional_text'    => $this->additional_text(),
				'is_disabled_option' => $wpmudev->is_disabled_hub_option(),
			),
		);
	}

	/**
	 * Display modal if:
	 * plugin version has important changes,
	 * plugin settings have been reset before -> this is not fresh install,
	 * Whitelabel > Documentation, Tutorials and What’s New Modal > checked Show tab OR Whitelabel is disabled.
	 *
	 * @param  string $key  The feature slug to check.
	 *
	 * @return bool
	 */
	protected function display_last_modal( $key ): bool {
		$info = defender_white_label_status();

		if ( defined( 'WP_DEFENDER_PRO' ) && WP_DEFENDER_PRO ) {
			$allowed_fresh_install = true;
		} else {
			$allowed_fresh_install = (bool) get_site_option( 'wd_nofresh_install' );
		}

		return $allowed_fresh_install && (bool) get_site_option( $key ) && ! $info['hide_doc_link'];
	}

	/**
	 * Upgrades site options related to feature modals based on the database version.
	 */
	public function upgrade_site_options(): void {
		$db_version    = get_site_option( 'wd_db_version' );
		$feature_slugs = array(
			// Important slugs to display Onboarding, e.g. after the click on Reset settings.
			array(
				'slug' => 'wp_defender_shown_activator',
				'vers' => '2.4.0',
			),
			array(
				'slug' => 'wp_defender_is_free_activated',
				'vers' => '2.4.0',
			),
			// The latest feature.
			array(
				'slug' => 'wd_show_feature_global_ip',
				'vers' => '3.6.0',
			),
			// The current feature.
			array(
				'slug' => self::FEATURE_SLUG,
				'vers' => self::FEATURE_VERSION,
			),
		);
		foreach ( $feature_slugs as $feature ) {
			if ( version_compare( $db_version, $feature['vers'], '==' ) ) {
				// The current feature.
				update_site_option( $feature['slug'], true );
			} else {
				// Old one.
				delete_site_option( $feature['slug'] );
			}
		}
	}

	/**
	 * Get additional text.
	 *
	 * @return string
	 */
	private function additional_text(): string {
		return '';
	}

	/**
	 * Delete welcome modal key.
	 *
	 * @return void
	 */
	public static function delete_modal_key(): void {
		delete_site_option( self::FEATURE_SLUG );
	}
}
