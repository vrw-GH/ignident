<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Image_Watermark_Actions_Controller {

	/**
	 * Plugin instance.
	 *
	 * @var Image_Watermark
	 */
	private $plugin;

	/**
	 * Upload handler service.
	 *
	 * @var Image_Watermark_Upload_Handler
	 */
	private $upload_handler;

	/**
	 * Controller constructor.
	 *
	 * @param Image_Watermark $plugin
	 * @param Image_Watermark_Upload_Handler $upload_handler
	 */
	public function __construct( Image_Watermark $plugin, Image_Watermark_Upload_Handler $upload_handler ) {
		$this->plugin = $plugin;
		$this->upload_handler = $upload_handler;
	}

	/**
	 * Handles manual AJAX watermark requests.
	 * 
	 * Validates request parameters, user permissions, and performs watermark
	 * apply/remove actions on individual attachments. Returns JSON responses
	 * with specific error messages for better debugging.
	 * 
	 * Expected POST parameters:
	 * - _iw_nonce: Security nonce
	 * - iw-action: 'applywatermark' or 'removewatermark'
	 * - attachment_id: Image attachment post ID
	 * 
	 * Success responses:
	 * - 'watermarked': Watermark successfully applied
	 * - 'watermarkremoved': Watermark successfully removed
	 * 
	 * Error responses return specific messages about what failed.
	 * 
	 * @since 2.0.0
	 * @return void Outputs JSON response and exits
	 */
	public function watermark_action_ajax() {
		// Check if this is an AJAX request
		if ( ! wp_doing_ajax() ) {
			wp_send_json_error( __( 'You are not allowed to perform this action.', 'image-watermark' ) );
		}

		// Check required parameters
		if ( ! isset( $_POST['_iw_nonce'], $_POST['iw-action'], $_POST['attachment_id'] ) ) {
			wp_send_json_error( __( 'Missing required parameters.', 'image-watermark' ) );
		}

		// Validate attachment ID
		if ( ! is_numeric( $_POST['attachment_id'] ) ) {
			wp_send_json_error( __( 'Invalid attachment ID.', 'image-watermark' ) );
		}

		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['_iw_nonce'], 'image-watermark' ) ) {
			wp_send_json_error( __( 'Security check failed. Please refresh the page and try again.', 'image-watermark' ) );
		}

		// Check user capability
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( __( 'You do not have permission to manage images.', 'image-watermark' ) );
		}

		$post_id = (int) $_POST['attachment_id'];
		$action = sanitize_key( $_POST['iw-action'] );
		$action = in_array( $action, [ 'applywatermark', 'removewatermark' ], true ) ? $action : false;
		$options = $this->plugin->options;

		if ( ! $action ) {
			wp_send_json_error( __( 'Invalid action.', 'image-watermark' ) );
		}

		if ( $options['watermark_image']['manual_watermarking'] != 1 ) {
			wp_send_json_error( __( 'Manual watermarking is disabled.', 'image-watermark' ) );
		}

		// Debug logging (enable WP_DEBUG_LOG to see these)
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( sprintf( 
				'Image Watermark: Action=%s, PostID=%d, ManualEnabled=%s, WatermarkURL=%s',
				$action ?: 'invalid',
				$post_id,
				$options['watermark_image']['manual_watermarking'] ? 'yes' : 'no',
				$options['watermark_image']['url'] ?? 'not-set'
			) );
		}

		if ( $post_id > 0 ) {
			$data = wp_get_attachment_metadata( $post_id, false );

			if ( in_array( get_post_mime_type( $post_id ), $this->plugin->get_allowed_mime_types(), true ) && is_array( $data ) ) {
				if ( $action === 'applywatermark' ) {
					$success = $this->upload_handler->apply_watermark( $data, $post_id, 'manual' );

					if ( ! empty( $success['error'] ) ) {
						if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
							error_log( sprintf(
								'Image Watermark: Manual apply failed. PostID=%d, Error=%s',
								$post_id,
								$success['error']
							) );
						}
						wp_send_json_error( $success['error'] );
					}

					wp_send_json_success( 'watermarked' );
				} elseif ( $action === 'removewatermark' ) {
					$success = $this->upload_handler->remove_watermark( $data, $post_id, 'manual' );

					if ( is_array( $success ) && ! empty( $success['error'] ) ) {
						if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
							error_log( sprintf(
								'Image Watermark: Manual remove failed. PostID=%d, Error=%s',
								$post_id,
								$success['error']
							) );
						}
						wp_send_json_error( $success['error'] );
					}

					if ( $success ) {
						wp_send_json_success( 'watermarkremoved' );
					} else {
						if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
							error_log( sprintf(
								'Image Watermark: Manual remove failed. PostID=%d, Error=%s',
								$post_id,
								__( 'Failed to remove watermark.', 'image-watermark' )
							) );
						}
						wp_send_json_error( __( 'Failed to remove watermark.', 'image-watermark' ) );
					}
				}
			} else {
				$mime = get_post_mime_type( $post_id );
				wp_send_json_error( sprintf( __( 'Unsupported file type (%s). Only JPEG, PNG, and WebP are supported.', 'image-watermark' ), $mime ?: 'unknown' ) );
			}
		}

		// Fallback: should not reach here if all checks above are correct
		wp_send_json_error( __( 'Unable to perform action. Invalid attachment or request.', 'image-watermark' ) );
	}

	/**
	 * Handles bulk actions from the media list table.
	 */
	public function watermark_bulk_action() {
		global $pagenow;

		if ( $pagenow !== 'upload.php' || ! $this->plugin->get_extension() ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Media_List_Table' );
		$action = $wp_list_table->current_action();
		$action = in_array( $action, [ 'applywatermark', 'removewatermark' ], true ) ? $action : false;
		$options = $this->plugin->options;

		// Only proceed if manual watermarking is enabled
		if ( ! $action || $options['watermark_image']['manual_watermarking'] != 1 ) {
			return;
		}

		check_admin_referer( 'bulk-media' );

		$location = esc_url( remove_query_arg( [ 'watermarked', 'watermarkremoved', 'skipped', 'trashed', 'untrashed', 'deleted', 'message', 'ids', 'posted' ], wp_get_referer() ) );

		if ( ! $location ) {
			$location = 'upload.php';
		}

		$location = esc_url( add_query_arg( 'paged', $wp_list_table->get_pagenum(), $location ) );

		$post_ids = isset( $_REQUEST['media'] ) ? array_map( 'intval', $_REQUEST['media'] ) : [];

		if ( $post_ids ) {
			$watermarked = $watermarkremoved = $skipped = 0;
			$messages = [];

			foreach ( $post_ids as $post_id ) {
					$data = wp_get_attachment_metadata( $post_id, false );

					if ( in_array( get_post_mime_type( $post_id ), $this->plugin->get_allowed_mime_types(), true ) && is_array( $data ) ) {
						if ( $action === 'applywatermark' ) {
							$success = $this->upload_handler->apply_watermark( $data, $post_id, 'manual' );

							if ( ! empty( $success['error'] ) ) {
								$messages[] = sprintf(
									__( 'ID %d: %s', 'image-watermark' ),
									$post_id,
									$success['error']
								);
								$skipped++;
							} else {
								$watermarked++;
								$watermarkremoved = -1;
							}
						} elseif ( $action === 'removewatermark' ) {
							$success = $this->upload_handler->remove_watermark( $data, $post_id, 'manual' );

							if ( is_array( $success ) && ! empty( $success['error'] ) ) {
								$messages[] = sprintf(
									__( 'ID %d: %s', 'image-watermark' ),
									$post_id,
									$success['error']
								);
								$skipped++;
							} elseif ( $success ) {
								$watermarkremoved++;
							} else {
								$skipped++;
							}

							$watermarked = -1;
						}
					} else {
						$skipped++;
					}
				}

				$args = [
					'watermarked'      => $watermarked,
					'watermarkremoved' => $watermarkremoved,
					'skipped'          => $skipped,
				];

				if ( ! empty( $messages ) ) {
					$args['messages'] = $messages;
				}

				$location = esc_url( add_query_arg( $args, $location ), null, '' );
			}

			wp_redirect( $location );
			exit;
		}
	}

