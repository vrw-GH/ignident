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
	public const FEATURE_SLUG    = 'wd_show_feature_quarantine';
	public const FEATURE_VERSION = '4.0.0';

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
		$title = esc_html__( 'NEW: Defender Safe Repair', 'defender-security' );

		$current_user = wp_get_current_user();

		$desc = sprintf(
		/* translators: 1: Name, 2: Quarantined & Deleted, 3: Replaced. */
			esc_html__(
				'Hey %1$s! With Defender\'s Safe Repair feature, you no longer have to be cautious about breaking your site when deleting suspicious files! Suspicious and modified files can now be %2$s, or %3$s with the latest file copies from their official plugin repository.',
				'defender-security'
			),
			esc_html( $current_user->display_name ),
			'<strong>' . esc_html__( 'Quarantined', 'defender-security' ) . '</strong>, <strong>' . esc_html__( 'Deleted', 'defender-security' ) . '</strong>',
			'<strong>' . esc_html__( 'Replaced', 'defender-security' ) . '</strong>'
		);
		$wpmudev = wd_di()->get( WPMUDEV::class );
		if ( $force_hide ) {
			$is_displayed = false;
		} elseif ( 'wd_show_feature_quarantine' === self::FEATURE_SLUG && ! $wpmudev->is_pro() ) {
			// @since 4.0.0 Highlight the Safe Repair feature only for Pro users.
			$is_displayed = false;
		} else {
			$is_displayed = $this->display_last_modal( self::FEATURE_SLUG );
		}

		return array(
			'show_welcome_modal' => $is_displayed,
			'welcome_modal'      => array(
				'title'              => $title,
				'desc'               => $desc,
				'banner_1x'          => defender_asset_url( '/assets/img/modal/welcome-modal.png' ),
				'banner_2x'          => defender_asset_url( '/assets/img/modal/welcome-modal@2x.png' ),
				'banner_alt'         => esc_html__( 'Modal for Quarantine', 'defender-security' ),
				'button_title'       => esc_html__( 'CHECK IT OUT!', 'defender-security' ),
				'button_title_free'  => esc_html__( 'Get it now', 'defender-security' ),
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
