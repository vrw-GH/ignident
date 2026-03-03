<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

new Image_Watermark_Update( );

/**
 * Image Watermark update class.
 *
 * @class Image_Watermark_Update
 */
class Image_Watermark_Update {

	/**
	 * Class constructor.
	 */
	public function __construct( )	{
		// actions
		add_action( 'admin_init', [ $this, 'check_update' ] );
	}

	/**
	 * Check if update is required.
	 *
	 * @return void
	 */
	public function check_update() {
		if( ! current_user_can( 'manage_options' ) || ! current_user_can( 'install_plugins' ) )
			return;

		// gets current database version
		$current_db_version = get_option( 'image_watermark_version', '1.0.0' );

		if ( version_compare( $current_db_version, '2.0.4', '<' ) ) {
			$options = get_option( 'image_watermark_options', [] );

			if ( ! is_array( $options ) ) {
				$options = [];
			}

			$apply_on = 'everywhere';
			$cpt_on = [];

			if ( isset( $options['watermark_cpt_on'] ) && is_array( $options['watermark_cpt_on'] ) ) {
				$cpt_on = $options['watermark_cpt_on'];
				$is_list = array_values( $cpt_on ) === $cpt_on;
				$has_everywhere = $is_list ? in_array( 'everywhere', $cpt_on, true ) : array_key_exists( 'everywhere', $cpt_on );

				$apply_on = $has_everywhere ? 'everywhere' : 'post_types';

				if ( $is_list ) {
					$cpt_on = array_values( array_diff( $cpt_on, [ 'everywhere' ] ) );
				} elseif ( array_key_exists( 'everywhere', $cpt_on ) ) {
					unset( $cpt_on['everywhere'] );
				}
			}

			$options['watermark_cpt_on'] = $cpt_on;
			$options['watermark_apply_on'] = $apply_on;

			update_option( 'image_watermark_options', $options );
		}

		// new version?
		if ( version_compare( $current_db_version, Image_Watermark()->defaults['version'], '<' ) ) {
			// update plugin version
			update_option( 'image_watermark_version', Image_Watermark()->defaults['version'], false );
		}
	}
}
