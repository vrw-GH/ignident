<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Ays_Pb_Feedback {

	/**
	 * API feedback URL.
	 *
	 * Holds the URL of the feedback API.
	 *
	 * @access private
	 * @static
	 *
	 * @var string API feedback URL.
	 */
	private static $api_feedback_url = 'https://poll-plugin.com/popup-box/feedback/';

	/**
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'current_screen', function () {
			if ( ! $this->is_plugins_screen() ) {
				return;
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_feedback_dialog_scripts' ) );
		} );

		// Ajax.
		add_action( 'wp_ajax_ays_pb_deactivate_feedback', array( $this, 'ays_pb_deactivate_feedback' ) );
	}

	/**
	 * Get module name.
	 *
	 * Retrieve the module name.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string Module name.
	 */
	public function get_name() {
		return 'feedback';
	}

	/**
	 * Enqueue feedback dialog scripts.
	 *
	 * Registers the feedback dialog scripts and enqueues them.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue_feedback_dialog_scripts() {
		add_action( 'admin_footer', array( $this, 'print_deactivate_feedback_dialog' ) );
	}

	/**
	 * Print deactivate feedback dialog.
	 *
	 * Display a dialog box to ask the user why he deactivated Popup Box.
	 *
	 * Fired by `admin_footer` filter.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function print_deactivate_feedback_dialog() {
		$deactivate_reasons = array(
			'no_longer_needed' => array(
				'title' => esc_html__( 'I no longer need the plugin', 'ays-popup-box' ),
				'input_placeholder' => '',
			),
			'found_a_better_plugin' => array(
				'title' => esc_html__( 'I found a better alternative', 'ays-popup-box' ),
				'input_placeholder' => esc_html__( 'Other', 'ays-popup-box' ),
				'sub_reason' => array(
					'optin_monster' 		=> esc_html__( 'OptinMonster', 'ays-popup-box' ),
					'popup_maker' 			=> esc_html__( 'Popup Maker', 'ays-popup-box' ),
					'popup_builder' 		=> esc_html__( 'Popup Builder', 'ays-popup-box' ),
					'popup_slider_builder' 	=> esc_html__( 'Popup and Slider Builder', 'ays-popup-box' ),
					'poptin' 				=> esc_html__( 'Poptin', 'ays-popup-box' ),
				),
			),
			'couldnt_get_the_plugin_to_work' => array(
				'title' => esc_html__( "The plugin didn’t work as expected", 'ays-popup-box' ),
				'input_placeholder' => '',
			),
			'missing_features' => array(
				'title' => esc_html__( 'Missing essential features', 'ays-popup-box' ),
				'input_placeholder' => esc_html__( 'Please share which features', 'ays-popup-box' ),
			),
			'temporary_deactivation' => array(
				'title' => esc_html__( "I only needed it temporarily", 'ays-popup-box' ),
				'input_placeholder' => '',
			),
			'plugin_or_theme_conflict' => array(
				'title' => esc_html__( "Conflicts with other plugins or themes", 'ays-popup-box' ),
				// 'input_placeholder' => esc_html__( 'Please share which plugin or theme', 'ays-popup-box' ),
				'input_placeholder' => '',
				'alert' => sprintf( __("Contact our %s support team %s to find and fix the issue.", 'ays-popup-box'),
                                    "<a href='https://popup-plugin.com/contact-us/' target='_blank'>",
                                    "</a>"
                                ),
			),
			'popup_pro' => array(
				'title' => esc_html__( 'I’m using the premium version now', 'ays-popup-box' ),
				'input_placeholder' => '',
				// 'alert' => esc_html__( "Wait! Don't deactivate Popup Box. You have to activate both Popup Box and Popup Box Pro in order for the plugin to work.", 'ays-popup-box' ),
			),
			'other' => array(
				'title' => esc_html__( 'Other', 'ays-popup-box' ),
				'input_placeholder' => esc_html__( 'Please share the reason', 'ays-popup-box' ),
			),
		);

		$popup_deactivate_feedback_nonce = wp_create_nonce( 'ays_pb_deactivate_feedback_nonce' );

		?>
		<div class="ays-pb-dialog-widget ays-pb-dialog-lightbox-widget ays-pb-dialog-type-buttons ays-pb-dialog-type-lightbox" id="ays-pb-deactivate-feedback-modal" aria-modal="true" role="document" tabindex="0" style="display: none;">
		    <div class="ays-pb-dialog-widget-content ays-pb-dialog-lightbox-widget-content">
		        <div class="ays-pb-dialog-header ays-pb-dialog-lightbox-header">
		            <div id="ays-pb-deactivate-feedback-dialog-header">
						<img class="ays-pb-dialog-logo" src="<?php echo esc_url( AYS_PB_ADMIN_URL . '/images/icons/icon-popup-128x128.png' ); ?>" alt="<?php echo esc_attr( __( "Popup Box", 'ays-popup-box' ) ); ?>" title="<?php echo esc_attr( __( "Popup Box", 'ays-popup-box' ) ); ?>" width="20" height="20"/>
						<span id="ays-pb-deactivate-feedback-dialog-header-title"><?php echo esc_html__( 'Quick Feedback', 'ays-popup-box' ); ?></span>
					</div>
		        </div>
		        <div class="ays-pb-dialog-message ays-pb-dialog-lightbox-message">
					<form id="ays-pb-deactivate-feedback-dialog-form" method="post">
						<input type="hidden" id="ays_pb_deactivate_feedback_nonce" name="ays_pb_deactivate_feedback_nonce" value="<?php echo esc_attr($popup_deactivate_feedback_nonce) ; ?>">
						<input type="hidden" name="action" value="ays_pb_deactivate_feedback" />

						<div id="ays-pb-deactivate-feedback-dialog-form-caption"><?php echo esc_html__( 'If you have a moment, please share why you are deactivating Popup Box:', 'ays-popup-box' ); ?></div>
						<div id="ays-pb-deactivate-feedback-dialog-form-body">
							<?php foreach ( $deactivate_reasons as $reason_key => $reason ) : ?>
								<div class="ays-pb-deactivate-feedback-dialog-input-wrapper">
									<input id="ays-pb-deactivate-feedback-<?php echo esc_attr( $reason_key ); ?>" class="ays-pb-deactivate-feedback-dialog-input" type="radio" name="ays_pb_reason_key" value="<?php echo esc_attr( $reason_key ); ?>" />
									<label for="ays-pb-deactivate-feedback-<?php echo esc_attr( $reason_key ); ?>" class="ays-pb-deactivate-feedback-dialog-label"><?php echo esc_html( $reason['title'] ); ?>
									<?php if ( ! empty( $reason['input_placeholder'] ) && empty( $reason['sub_reason'] ) ) : ?>
										<input class="ays-pb-feedback-text" type="text" name="ays_pb_reason_<?php echo esc_attr( $reason_key ); ?>" placeholder="<?php echo esc_attr( $reason['input_placeholder'] ); ?>" />
									<?php endif; ?>
									<?php if ( ! empty( $reason['alert'] ) ) : ?>
										<div class="ays-pb-feedback-text ays-pb-feedback-text-color"><?php echo wp_kses_post( $reason['alert'] ); ?></div>
									<?php endif; ?>
									<?php if ( ! empty( $reason['sub_reason'] ) && is_array($reason['sub_reason']) ) : ?>
										<div class="ays-pb-deactivate-feedback-sub-dialog-input-wrapper">
										<?php foreach ( $reason['sub_reason'] as $sub_reason_key => $sub_reason ) : ?>
											<div class="ays-pb-deactivate-feedback-dialog-input-wrapper">
												<input id="ays-pb-deactivate-feedback-sub-<?php echo esc_attr( $sub_reason_key ); ?>" class="ays-pb-deactivate-feedback-dialog-input" type="radio" name="ays_pb_sub_reason_key" value="<?php echo esc_attr( $sub_reason_key ); ?>" />
												<label for="ays-pb-deactivate-feedback-sub-<?php echo esc_attr( $sub_reason_key ); ?>" class="ays-pb-deactivate-feedback-dialog-label"><?php echo esc_html( $sub_reason ); ?>
												</label>
											</div>
										<?php endforeach; ?>
										</div>
										<?php if ( ! empty( $reason['input_placeholder'] ) ) : ?>
											<input class="ays-pb-feedback-text" type="text" name="ays_pb_reason_<?php echo esc_attr( $reason_key ); ?>" placeholder="<?php echo esc_attr( $reason['input_placeholder'] ); ?>" />
										<?php endif; ?>
									<?php endif; ?>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
					</form>
		        </div>
		        <div class="ays-pb-dialog-buttons-wrapper ays-pb-dialog-lightbox-buttons-wrapper">
		            <button class="ays-pb-dialog-button ays-pb-dialog-skip ays-pb-dialog-lightbox-skip" data-type="skip"><?php echo esc_html__( 'Skip &amp; Deactivate', 'ays-popup-box' ); ?></button>
		            <button class="ays-pb-dialog-button ays-pb-dialog-submit ays-pb-dialog-lightbox-submit" data-type="submit"><?php echo esc_html__( 'Submit &amp; Deactivate', 'ays-popup-box' ); ?></button>
		        </div>
    		</div>
		</div>
		<?php
	}

	/**
	 * Ajax Popup Box deactivate feedback.
	 *
	 * Send the user feedback when Popup Box is deactivated.
	 *
	 * Fired by `wp_ajax_ays_pb_deactivate_feedback` action.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function ays_pb_deactivate_feedback() {

		if ( empty($_REQUEST['ays_pb_deactivate_feedback_nonce']) ) {
			wp_send_json_error();
		}

		// Run a security check.
        check_ajax_referer( 'ays_pb_deactivate_feedback_nonce', sanitize_key( $_REQUEST['_ajax_nonce'] ) );

		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		if (empty($_REQUEST['action']) || (isset($_REQUEST['action']) && $_REQUEST['action'] != 'ays_pb_deactivate_feedback')) {
			wp_send_json_error( 'Action error' );
		}

		$reason_key = !empty($_REQUEST['ays_pb_reason_key']) ? sanitize_text_field($_REQUEST['ays_pb_reason_key']) : "";
		$sub_reason_key = !empty($_REQUEST['ays_pb_sub_reason_key']) ? sanitize_text_field($_REQUEST['ays_pb_sub_reason_key']) : "";
		$reason_text = !empty($_REQUEST["ays_pb_reason_{$reason_key}"]) ? sanitize_text_field($_REQUEST["ays_pb_reason_{$reason_key}"]) : "";
		$type = !empty($_REQUEST["type"]) ? sanitize_text_field($_REQUEST["type"]) : "";

		self::send_feedback( $reason_key, $sub_reason_key, $reason_text, $type );

		wp_send_json_success();
	}

	/**
	 * @since 1.0.0
	 * @access private
	 */
	private function is_plugins_screen() {
		return in_array( get_current_screen()->id, array( 'plugins', 'plugins-network' ) );
	}

	/**
	 * Send Feedback.
	 *
	 * Fires a request to Popup Box server with the feedback data.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param string $feedback_key  Feedback key.
	 * @param string $feedback_text Feedback text.
	 *
	 * @return array The response of the request.
	 */
	public static function send_feedback( $feedback_key, $sub_feedback_key, $feedback_text, $type ) {
		return wp_remote_post( self::$api_feedback_url, array(
			'timeout' => 30,
			'body' => wp_json_encode(array(
				'type' 				=> 'popup-box',
				'version' 			=> AYS_PB_NAME_VERSION,
				'site_lang' 		=> get_bloginfo( 'language' ),
				'button' 			=> $type,
				'feedback_key' 		=> $feedback_key,
				'sub_feedback_key' 	=> $sub_feedback_key,
				'feedback' 			=> $feedback_text,
			)),
		) );
	}
}
new Ays_Pb_Feedback();
