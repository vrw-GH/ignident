<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Image_Watermark_Upload_Handler {

	/**
	 * Plugin instance.
	 *
	 * @var Image_Watermark
	 */
	private $plugin;

	/**
	 * Tracks whether the current request originates from the admin.
	 *
	 * @var bool
	 */
	private $is_admin = true;

	/**
	 * Tracks if the missing font notice was added.
	 *
	 * @var bool
	 */
	private $missing_font_notice_added = false;

	/**
	 * Tracks if the backup failure notice was added.
	 *
	 * @var bool
	 */
	private $backup_failure_notice_added = false;

	/**
	 * Tracks if the timestamp preservation notice was added.
	 *
	 * @var bool
	 */
	private $timestamp_preserve_notice_added = false;

	/**
	 * Upload handler constructor.
	 *
	 * @param Image_Watermark $plugin
	 */
	public function __construct( Image_Watermark $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Handles uploads and registers metadata generation filter when needed.
	 *
	 * @param array $file
	 *
	 * @return array
	 */
	public function handle_upload_files( $file ) {
		if ( ! $this->plugin->get_extension() ) {
			return $file;
		}

		$script_filename = isset( $_SERVER['SCRIPT_FILENAME'] ) ? $_SERVER['SCRIPT_FILENAME'] : '';

		if ( wp_doing_ajax() ) {
			$ref = '';

			if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
				$ref = wp_unslash( $_REQUEST['_wp_http_referer'] );
			} elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
				$ref = wp_unslash( $_SERVER['HTTP_REFERER'] );
			}

			if ( ( strpos( $ref, admin_url() ) === false ) && ( basename( $script_filename ) === 'admin-ajax.php' ) ) {
				$this->is_admin = false;
			} else {
				$this->is_admin = true;
			}
		} else {
			$this->is_admin = is_admin();
		}

		$upload_context = isset( $_REQUEST['iw_watermark_upload'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['iw_watermark_upload'] ) ) : '';

		if ( $upload_context === '1' ) {
			return $file;
		}

		$options = $this->plugin->options;
		$allowed_mime = $this->plugin->get_allowed_mime_types();
		$watermark_type = isset( $options['watermark_image']['type'] ) ? $options['watermark_image']['type'] : 'image';

		if ( $this->is_admin === true ) {
			if ( $options['watermark_image']['plugin_off'] == 1 && in_array( $file['type'], $allowed_mime, true ) ) {
				$should_apply = false;

				if ( $watermark_type === 'image' ) {
					$should_apply = wp_attachment_is_image( $options['watermark_image']['url'] );
				} elseif ( $watermark_type === 'text' ) {
					$text_string = isset( $options['watermark_image']['text_string'] ) ? trim( $options['watermark_image']['text_string'] ) : '';
					if ( ! empty( $text_string ) ) {
						// Validate font availability
						$font = isset( $options['watermark_image']['text_font'] ) ? $options['watermark_image']['text_font'] : 'Lato-Regular.ttf';
						$font_path = $this->plugin->get_font_path( $font );
						$should_apply = $font_path && file_exists( $font_path );
						if ( ! $should_apply ) {
							$this->maybe_add_missing_font_notice( $font );
						}
					}
				}

				if ( $should_apply ) {
					add_filter( 'wp_generate_attachment_metadata', [ $this, 'apply_watermark' ], 10, 2 );
				}
			}
		} else {
			if ( $options['watermark_image']['frontend_active'] == 1 && in_array( $file['type'], $allowed_mime, true ) ) {
				$should_apply = false;

				if ( $watermark_type === 'image' ) {
					$should_apply = wp_attachment_is_image( $options['watermark_image']['url'] );
				} elseif ( $watermark_type === 'text' ) {
					$text_string = isset( $options['watermark_image']['text_string'] ) ? trim( $options['watermark_image']['text_string'] ) : '';
					if ( ! empty( $text_string ) ) {
						// Validate font availability
						$font = isset( $options['watermark_image']['text_font'] ) ? $options['watermark_image']['text_font'] : 'Lato-Regular.ttf';
						$font_path = $this->plugin->get_font_path( $font );
						$should_apply = $font_path && file_exists( $font_path );
					}
				}

				if ( $should_apply ) {
					add_filter( 'wp_generate_attachment_metadata', [ $this, 'apply_watermark' ], 10, 2 );
				}
			}
		}

		return $file;
	}

	/**
	 * Adds an admin notice when a text watermark font is missing (debug only).
	 *
	 * @param string $font Font filename.
	 * @return void
	 */
	private function maybe_add_missing_font_notice( $font ) {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		if ( ! is_admin() || ! current_user_can( 'upload_files' ) ) {
			return;
		}

		if ( $this->missing_font_notice_added ) {
			return;
		}

		$this->missing_font_notice_added = true;

		add_action( 'admin_notices', function() use ( $font ) {
			$label = $font ? $font : __( '(unknown)', 'image-watermark' );
			echo '<div class="notice notice-warning"><p>' . sprintf( esc_html__( 'Image Watermark: Text watermark skipped because the font file "%s" is missing.', 'image-watermark' ), esc_html( $label ) ) . '</p></div>';
		} );
	}

	/**
	 * Adds an admin notice for auto-apply issues.
	 *
	 * @param string $message Notice message.
	 * @param string $type Notice type: 'warning' or 'error'.
	 * @return void
	 */
	private function add_admin_notice( $message, $type = 'warning' ) {
		if ( ! is_admin() || ! current_user_can( 'upload_files' ) ) {
			return;
		}

		$notice_type = in_array( $type, [ 'error', 'warning', 'success', 'info' ], true ) ? $type : 'warning';

		add_action( 'admin_notices', function() use ( $message, $notice_type ) {
			echo '<div class="notice notice-' . esc_attr( $notice_type ) . '"><p>' . esc_html( $message ) . '</p></div>';
		} );
	}

	/**
	 * Validates watermark eligibility for apply/remove operations.
	 *
	 * Shared validation logic used by both automatic and manual watermarking flows.
	 * Context determines whether issues are blocking (manual) or non-blocking (auto).
	 *
	 * @param array $options Plugin options.
	 * @param int $attachment_id Attachment being processed.
	 * @param string $context Operation context: 'auto-apply', 'manual-apply', or 'manual-remove'.
	 * @return array Array with 'valid' (bool), 'error' (string|null), 'warning' (string|null).
	 */
	private function validate_watermark_eligibility( $options, $attachment_id, $context ) {
		$result = [
			'valid'   => true,
			'error'   => null,
			'warning' => null,
		];

		// Check if engine is available
		if ( ! $this->plugin->get_extension() ) {
			$result['valid'] = false;
			$result['error'] = __( 'No image processing engine available (GD or Imagick required).', 'image-watermark' );
			return $result;
		}

		// Check MIME type
		$allowed_mime = $this->plugin->get_allowed_mime_types();
		$mime_type = get_post_mime_type( $attachment_id );
		if ( ! in_array( $mime_type, $allowed_mime, true ) ) {
			$result['valid'] = false;
			$result['error'] = sprintf(
				__( 'Unsupported file type (%s). Only JPEG, PNG, and WebP are supported.', 'image-watermark' ),
				$mime_type ?: 'unknown'
			);
			return $result;
		}

		// Prevent watermarking the watermark image itself
		$watermark_id = isset( $options['watermark_image']['url'] ) ? (int) $options['watermark_image']['url'] : 0;
		if ( $attachment_id === $watermark_id ) {
			$result['valid'] = false;
			$result['error'] = __( 'Cannot watermark the selected watermark image itself.', 'image-watermark' );
			return $result;
		}

		// For manual operations, check if manual watermarking is enabled
		if ( in_array( $context, [ 'manual-apply', 'manual-remove' ], true ) ) {
			if ( empty( $options['watermark_image']['manual_watermarking'] ) ) {
				$result['valid'] = false;
				$result['error'] = __( 'Manual watermarking is disabled in settings.', 'image-watermark' );
				return $result;
			}
		}

		// Validate watermark configuration (for apply operations)
		if ( in_array( $context, [ 'auto-apply', 'manual-apply' ], true ) ) {
			$watermark_type = isset( $options['watermark_image']['type'] ) ? $options['watermark_image']['type'] : 'image';

			if ( $watermark_type === 'image' ) {
				if ( ! wp_attachment_is_image( $watermark_id ) ) {
					$result['valid'] = false;
					$result['error'] = __( 'Please select a valid watermark image.', 'image-watermark' );
					return $result;
				}
			} elseif ( $watermark_type === 'text' ) {
				$text_string = isset( $options['watermark_image']['text_string'] ) ? trim( $options['watermark_image']['text_string'] ) : '';
				if ( empty( $text_string ) ) {
					$result['valid'] = false;
					$result['error'] = __( 'Please enter watermark text.', 'image-watermark' );
					return $result;
				}

				// Validate font availability
				$font = isset( $options['watermark_image']['text_font'] ) ? $options['watermark_image']['text_font'] : 'Lato-Regular.ttf';
				$font_path = $this->plugin->get_font_path( $font );

				if ( ! $font_path || ! file_exists( $font_path ) ) {
					$result['valid'] = false;
					$result['error'] = sprintf(
						__( 'Selected font "%s" is not available. Please choose a different font.', 'image-watermark' ),
						$font
					);
					return $result;
				}
			}
		}

		// For remove operations, check backup availability
		if ( $context === 'manual-remove' ) {
			$data = wp_get_attachment_metadata( $attachment_id, false );
			if ( ! is_array( $data ) || empty( $data['file'] ) ) {
				$result['valid'] = false;
				$result['error'] = __( 'Invalid attachment metadata.', 'image-watermark' );
				return $result;
			}

			$backup_filepath = $this->get_image_backup_filepath( $data['file'] );
			if ( ! file_exists( $backup_filepath ) ) {
				$result['valid'] = false;
				$result['error'] = __( 'No watermark backup found for this image.', 'image-watermark' );
				return $result;
			}
		}

		return $result;
	}

	/**
	 * Applies watermark to attachment sizes.
	 *
	 * @param array $data
	 * @param int|string $attachment_id
	 * @param string $method
	 *
	 * @return array
	 */
	public function apply_watermark( $data, $attachment_id, $method = '' ) {
		$attachment_id = (int) $attachment_id;
		$post = get_post( $attachment_id );
		$post_id = ( ! empty( $post ) ? (int) $post->post_parent : 0 );

		// Bail early if metadata is not an array or missing file info
		if ( ! is_array( $data ) || empty( $data['file'] ) || ! is_string( $data['file'] ) ) {
			return $data;
		}

		$options = apply_filters( 'iw_watermark_options', $this->plugin->options );
		$context = $method === 'manual' ? 'manual-apply' : 'auto-apply';

		// Use shared validation
		$validation = $this->validate_watermark_eligibility( $options, $attachment_id, $context );

		if ( ! $validation['valid'] ) {
			if ( $method === 'manual' ) {
				// Manual operations return error
				return [ 'error' => $validation['error'] ];
			} else {
				// Auto operations show admin notice and skip
				if ( $validation['error'] ) {
					$this->add_admin_notice(
						sprintf(
							__( 'Image Watermark: Upload successful, but watermark skipped - %s', 'image-watermark' ),
							$validation['error']
						),
						'warning'
					);
				}
				return $data;
			}
		}

		$apply_on = isset( $options['watermark_apply_on'] ) ? $options['watermark_apply_on'] : '';

		if ( ! in_array( $apply_on, [ 'everywhere', 'post_types' ], true ) ) {
			$legacy_cpt_on = isset( $options['watermark_cpt_on'] ) && is_array( $options['watermark_cpt_on'] ) ? $options['watermark_cpt_on'] : [];
			$is_list = array_values( $legacy_cpt_on ) === $legacy_cpt_on;
			$apply_on = $is_list
				? ( in_array( 'everywhere', $legacy_cpt_on, true ) ? 'everywhere' : 'post_types' )
				: ( array_key_exists( 'everywhere', $legacy_cpt_on ) ? 'everywhere' : 'post_types' );
		}

		if ( $method !== 'manual' && $this->is_admin === true && $apply_on === 'post_types' ) {
			$selected_post_types = [];

			if ( ! empty( $options['watermark_cpt_on'] ) && is_array( $options['watermark_cpt_on'] ) ) {
				$selected_post_types = $options['watermark_cpt_on'];
				if ( array_values( $selected_post_types ) !== $selected_post_types ) {
					$selected_post_types = array_keys( $selected_post_types );
				}
			}

			if ( $post_id <= 0 || ! in_array( get_post_type( $post_id ), $selected_post_types, true ) ) {
				return $data;
			}
		}

		if ( apply_filters( 'iw_watermark_display', $attachment_id ) === false ) {
			return $data;
		}

		$upload_dir = wp_upload_dir();
		$original_file = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $data['file'];

		// Ensure the target file exists and is a regular file before processing
		if ( ! is_file( $original_file ) ) {
			return $data;
		}

		if ( getimagesize( $original_file, $original_image_info ) !== false ) {
			$metadata = $this->get_image_metadata( $original_image_info );

			if ( (int) get_post_meta( $attachment_id, $this->plugin->get_watermarked_meta_key(), true ) === 1 ) {
				$backup_available = false;

				if ( ! empty( $options['backup']['backup_image'] ) && ! empty( $data['file'] ) && is_string( $data['file'] ) ) {
					$backup_path = $this->get_image_backup_filepath( $data['file'] );
					$backup_available = $backup_path && file_exists( $backup_path );
				}

				// If no backup is present we should not stack another watermark.
				if ( ! $backup_available ) {
					if ( $method === 'manual' ) {
						return [ 'error' => __( 'Watermark not applied because the original backup is missing.', 'image-watermark' ) ];
					}

					return $data;
				}

				$this->remove_watermark( $data, $attachment_id, 'manual' );
			}

			if ( $options['backup']['backup_image'] ) {
				$backup_result = $this->do_backup( $data, $upload_dir, $attachment_id );

				if ( ! $backup_result['success'] ) {
					if ( $method === 'manual' ) {
						// Manual operations return error immediately
						return [ 'error' => $backup_result['error'] ];
					} else {
						// Auto operations show admin notice and skip watermarking
						if ( ! $this->backup_failure_notice_added ) {
							$this->backup_failure_notice_added = true;
							$this->add_admin_notice(
								sprintf(
									__( 'Image Watermark: Backup failed - %s. Watermark not applied. Please check backup folder permissions.', 'image-watermark' ),
									$backup_result['error']
								),
								'error'
							);
						}
						return $data;
					}
				}
			}

			foreach ( $options['watermark_on'] as $image_size => $active_size ) {
				if ( $active_size === 1 ) {
					switch ( $image_size ) {
						case 'full':
							$filepath = $original_file;
							break;

						default:
							if ( ! empty( $data['sizes'] ) && array_key_exists( $image_size, $data['sizes'] ) ) {
								$filepath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . dirname( $data['file'] ) . DIRECTORY_SEPARATOR . $data['sizes'][ $image_size ]['file'];
							} else {
								continue 2;
							}
				}

				do_action( 'iw_before_apply_watermark', $attachment_id, $image_size );

				$this->do_watermark( $attachment_id, $filepath, $image_size, $upload_dir, $metadata );

				$this->save_image_metadata( $metadata, $filepath );

				do_action( 'iw_after_apply_watermark', $attachment_id, $image_size );
				}
			}

			update_post_meta( $attachment_id, $this->plugin->get_watermarked_meta_key(), 1 );
		}

		return $data;
	}

	/**
	 * Removes a watermark from an image.
	 *
	 * @param array $data Attachment metadata.
	 * @param int|string $attachment_id Attachment post ID.
	 * @param string $method Operation method ('manual' or empty).
	 *
	 * @return array|false Updated metadata on success, array with 'error' key on failure.
	 */
	public function remove_watermark( $data, $attachment_id, $method = '' ) {
		if ( $method !== 'manual' ) {
			return $data;
		}

		if ( ! is_array( $data ) || empty( $data['file'] ) || ! is_string( $data['file'] ) ) {
			return [ 'error' => __( 'Invalid attachment metadata.', 'image-watermark' ) ];
		}

		$options = apply_filters( 'iw_watermark_options', $this->plugin->options );

		// Use shared validation for remove operations
		$validation = $this->validate_watermark_eligibility( $options, $attachment_id, 'manual-remove' );

		if ( ! $validation['valid'] ) {
			return [ 'error' => $validation['error'] ];
		}

		$upload_dir = wp_upload_dir();

		$full_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $data['file'];

		if ( ! is_file( $full_path ) || getimagesize( $full_path ) === false ) {
			return [ 'error' => __( 'Original file not found or invalid.', 'image-watermark' ) ];
		}

		$filepath = get_attached_file( $attachment_id );
		$backup_filepath = $this->get_image_backup_filepath( get_post_meta( $attachment_id, '_wp_attached_file', true ) );

		// Backup must exist (already checked by validation, but double-check for safety)
		if ( ! file_exists( $backup_filepath ) ) {
			return [ 'error' => __( 'No watermark backup found for this image.', 'image-watermark' ) ];
		}

		// Restore from backup
		if ( ! $this->copy_file( $backup_filepath, $filepath, 'restore', $attachment_id ) ) {
			return [ 'error' => __( 'Failed to restore from backup.', 'image-watermark' ) ];
		}

		// Regenerate metadata
		$metadata = wp_generate_attachment_metadata( $attachment_id, $filepath );
		wp_update_attachment_metadata( $attachment_id, $metadata );
		update_post_meta( $attachment_id, $this->plugin->get_watermarked_meta_key(), 0 );

		return wp_get_attachment_metadata( $attachment_id );
	}

	/**
	 * Returns image metadata.
	 *
	 * @param array $imageinfo
	 *
	 * @return array
	 */
	private function get_image_metadata( $imageinfo ) {
		$metadata = [
			'exif' => null,
			'iptc' => null,
		];

		if ( is_array( $imageinfo ) ) {
			$exifdata = key_exists( 'APP1', $imageinfo ) ? $imageinfo['APP1'] : null;

			if ( $exifdata ) {
				$exiflength = strlen( $exifdata ) + 2;

				if ( $exiflength > 0xFFFF ) {
					return $metadata;
				}

				$metadata['exif'] = chr( 0xFF ) . chr( 0xE1 ) . chr( ( $exiflength >> 8 ) & 0xFF ) . chr( $exiflength & 0xFF ) . $exifdata;
			}

			$iptcdata = key_exists( 'APP13', $imageinfo ) ? $imageinfo['APP13'] : null;

			if ( $iptcdata ) {
				$iptclength = strlen( $iptcdata ) + 2;

				if ( $iptclength > 0xFFFF ) {
					return $metadata;
				}

				$metadata['iptc'] = chr( 0xFF ) . chr( 0xED ) . chr( ( $iptclength >> 8 ) & 0xFF ) . chr( $iptclength & 0xFF ) . $iptcdata;
			}
		}

		return $metadata;
	}

	/**
	 * Saves EXIF/IPTC metadata into the destination file.
	 *
	 * @param array $metadata
	 * @param string $file
	 *
	 * @return bool|int
	 */
	private function save_image_metadata( $metadata, $file ) {
		$mime = wp_check_filetype( $file );

		if ( file_exists( $file ) && $mime['type'] !== 'image/webp' && $mime['type'] !== 'image/png' ) {
			$exifdata = $metadata['exif'];
			$iptcdata = $metadata['iptc'];

			$destfilecontent = @file_get_contents( $file );

			if ( ! $destfilecontent ) {
				return false;
			}

			if ( strlen( $destfilecontent ) > 0 ) {
				$destfilecontent = substr( $destfilecontent, 2 );
				$portiontoadd = chr( 0xFF ) . chr( 0xD8 );
				$exifadded = ! $exifdata;
				$iptcadded = ! $iptcdata;

				while ( ( $this->get_safe_chunk( substr( $destfilecontent, 0, 2 ) ) & 0xFFF0 ) === 0xFFE0 ) {
					$segmentlen = ( $this->get_safe_chunk( substr( $destfilecontent, 2, 2 ) ) & 0xFFFF );
					$iptcsegmentnumber = ( $this->get_safe_chunk( substr( $destfilecontent, 1, 1 ) ) & 0x0F );

					if ( $segmentlen <= 2 ) {
						return false;
					}

					$thisexistingsegment = substr( $destfilecontent, 0, $segmentlen + 2 );

					if ( ( $iptcsegmentnumber >= 1 ) && ( ! $exifadded ) ) {
						$portiontoadd .= $exifdata;
						$exifadded = true;

						if ( $iptcsegmentnumber === 1 ) {
							$thisexistingsegment = '';
						}
					}

					if ( ( $iptcsegmentnumber >= 13 ) && ( ! $iptcadded ) ) {
						$portiontoadd .= $iptcdata;
						$iptcadded = true;

						if ( $iptcsegmentnumber === 13 ) {
							$thisexistingsegment = '';
						}
					}

					$portiontoadd .= $thisexistingsegment;
					$destfilecontent = substr( $destfilecontent, $segmentlen + 2 );
				}

				if ( ! $exifadded ) {
					$portiontoadd .= $exifdata;
				}

				if ( ! $iptcadded ) {
					$portiontoadd .= $iptcdata;
				}

				$outputfile = fopen( $file, 'w' );

				if ( $outputfile ) {
					return fwrite( $outputfile, $portiontoadd . $destfilecontent );
				}
			}
		}

		return false;
	}

	/**
	 * Helper to interpret binary segments safely.
	 *
	 * @param string|int $value
	 *
	 * @return int
	 */
	private function get_safe_chunk( $value ) {
		if ( is_numeric( $value ) ) {
			return (int) $value;
		}

		return 0;
	}

	/**
	 * Applies the watermark to a single image path.
	 *
	 * @param int $attachment_id
	 * @param string $image_path
	 * @param string $image_size
	 * @param array $upload_dir
	 * @param array $metadata
	 */
	public function do_watermark( $attachment_id, $image_path, $image_size, $upload_dir, $metadata = [] ) {
		$options = apply_filters( 'iw_watermark_options', $this->plugin->options );
		$mime = wp_check_filetype( $image_path );

		$watermark_type = isset( $options['watermark_image']['type'] ) ? $options['watermark_image']['type'] : 'image';

		if ( $watermark_type === 'image' ) {
			if ( ! wp_attachment_is_image( $options['watermark_image']['url'] ) ) {
				return;
			}

			$watermark_file = wp_get_attachment_metadata( $options['watermark_image']['url'], true );

			if ( ! is_array( $watermark_file ) || empty( $watermark_file['file'] ) ) {
				return;
			}

			$watermark_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $watermark_file['file'];

			if ( ! is_file( $watermark_path ) ) {
				return;
			}
		} elseif ( $watermark_type === 'text' ) {
			// For text watermark, we don't need a file path
		} else {
			return; // Unknown type
		}

		if ( $this->plugin->get_extension() === 'imagick' ) {
			$image = new Imagick( $image_path );

			if ( $watermark_type === 'image' ) {
				$watermark = new Imagick( $watermark_path );

				if ( $watermark->getImageAlphaChannel() > 0 ) {
					$watermark->evaluateImage( Imagick::EVALUATE_MULTIPLY, round( (float) ( $options['watermark_image']['transparent'] / 100 ), 2 ), Imagick::CHANNEL_ALPHA );
				} else {
					$watermark->setImageOpacity( round( (float) ( $options['watermark_image']['transparent'] / 100 ), 2 ) );
				}
			}

			if ( $mime['type'] === 'image/jpeg' ) {
				$image->setImageCompressionQuality( $options['watermark_image']['quality'] );
				$image->setImageCompression( imagick::COMPRESSION_JPEG );
			} else {
				$image->setImageCompressionQuality( $options['watermark_image']['quality'] );
			}

			if ( $options['watermark_image']['jpeg_format'] === 'progressive' ) {
				$image->setImageInterlaceScheme( Imagick::INTERLACE_PLANE );
			}

			$image_dim = $image->getImageGeometry();

			if ( $watermark_type === 'image' ) {
				$watermark_dim = $watermark->getImageGeometry();

				list( $width, $height ) = $this->calculate_watermark_dimensions( $image_dim['width'], $image_dim['height'], $watermark_dim['width'], $watermark_dim['height'], $options );

				$watermark->resizeImage( $width, $height, imagick::FILTER_CATROM, 1 );

				list( $dest_x, $dest_y ) = $this->calculate_image_coordinates( $image_dim['width'], $image_dim['height'], $width, $height, $options );

				$image->compositeImage( $watermark, Imagick::COMPOSITE_DEFAULT, $dest_x, $dest_y, Imagick::CHANNEL_ALL );

				$watermark->clear();
				$watermark->destroy();
				$watermark = null;
			} elseif ( $watermark_type === 'text' ) {
				$this->apply_text_watermark_imagick( $image, $options, $image_dim['width'], $image_dim['height'] );
			}

			$image->writeImage( $image_path );
			$image->clear();
			$image->destroy();
			$image = null;
		} else {
			$image = $this->get_image_resource( $image_path, $mime['type'] );

			if ( $image !== false ) {
				if ( $watermark_type === 'image' ) {
					$image = $this->add_watermark_image( $image, $options, $upload_dir );
				} elseif ( $watermark_type === 'text' ) {
					$image = $this->apply_text_watermark_gd( $image, $options );
				}

				if ( $image !== false ) {
					$this->save_image_file( $image, $mime['type'], $image_path, $options['watermark_image']['quality'] );
					imagedestroy( $image );
					$image = null;
				}
			}
		}
	}

	/**
	 * Apply text watermark using Imagick.
	 *
	 * @param Imagick $image The image object.
	 * @param array $options The watermark options.
	 * @param int $image_width Image width.
	 * @param int $image_height Image height.
	 */
	private function apply_text_watermark_imagick( $image, $options, $image_width, $image_height ) {
		$text = isset( $options['watermark_image']['text_string'] ) ? $options['watermark_image']['text_string'] : '';
		if ( empty( $text ) ) {
			return;
		}

		$font = isset( $options['watermark_image']['text_font'] ) ? $options['watermark_image']['text_font'] : 'Lato-Regular.ttf';
		$font_path = $this->plugin->get_font_path( $font );
		if ( ! $font_path || ! file_exists( $font_path ) ) {
			return;
		}

		$size = isset( $options['watermark_image']['text_size'] ) ? (int) $options['watermark_image']['text_size'] : 20;
		$color = isset( $options['watermark_image']['text_color'] ) ? $options['watermark_image']['text_color'] : '#ffffff';
		$opacity = isset( $options['watermark_image']['transparent'] ) ? (int) $options['watermark_image']['transparent'] : 50;

		$base_metrics = $this->measure_text_imagick( $font_path, $size, $text );
		$base_width = max( 1, (int) round( $base_metrics['width'] ) );
		$base_height = max( 1, (int) round( $base_metrics['height'] ) );

		list( $target_width, $target_height ) = $this->calculate_text_target_dimensions( $base_width, $base_height, $image_width, $image_height, $options['watermark_image'] );

		$scale = min( $target_width / $base_width, $target_height / $base_height, 1 );
		$render_size = max( 1, (int) round( $size * $scale ) );

		$scaled_metrics = $this->measure_text_imagick( $font_path, $render_size, $text );
		$render_width = max( 1, (int) round( $scaled_metrics['width'] ) );
		$render_height = max( 1, (int) round( $scaled_metrics['height'] ) );
		$render_ascent = max( 0, (int) round( $scaled_metrics['ascent'] ) );

		list( $x, $y ) = $this->calculate_image_coordinates( $image_width, $image_height, $render_width, $render_height, $options );

		// Convert hex color to RGB
		$color = ltrim( $color, '#' );
		$r = hexdec( substr( $color, 0, 2 ) );
		$g = hexdec( substr( $color, 2, 2 ) );
		$b = hexdec( substr( $color, 4, 2 ) );

		$draw = new ImagickDraw();
		$draw->setFont( $font_path );
		$draw->setFontSize( $render_size );
		$draw->setFillColor( "rgb($r, $g, $b)" );
		$draw->setFillOpacity( $opacity / 100 );

		// Imagick annotate uses baseline for Y; align baseline to top + ascent.
		$image->annotateImage( $draw, $x, $y + $render_ascent, 0, $text );

		$draw->clear();
		$draw->destroy();
	}

	/**
	 * Apply text watermark using GD.
	 *
	 * @param resource $image The GD image resource.
	 * @param array $options The watermark options.
	 * @return resource|false The modified image resource or false on failure.
	 */
	private function apply_text_watermark_gd( $image, $options ) {
		$text = isset( $options['watermark_image']['text_string'] ) ? $options['watermark_image']['text_string'] : '';
		if ( empty( $text ) ) {
			return $image;
		}

		$font = isset( $options['watermark_image']['text_font'] ) ? $options['watermark_image']['text_font'] : 'Lato-Regular.ttf';
		$font_path = $this->plugin->get_font_path( $font );
		if ( ! $font_path || ! file_exists( $font_path ) ) {
			return $image;
		}

		$size = isset( $options['watermark_image']['text_size'] ) ? (int) $options['watermark_image']['text_size'] : 20;
		$color = isset( $options['watermark_image']['text_color'] ) ? $options['watermark_image']['text_color'] : '#ffffff';
		$opacity = isset( $options['watermark_image']['transparent'] ) ? (int) $options['watermark_image']['transparent'] : 50;

		$image_width = imagesx( $image );
		$image_height = imagesy( $image );

		$base_bbox = imagettfbbox( $size, 0, $font_path, $text );
		$base_width = max( 1, $base_bbox[2] - $base_bbox[0] );
		$base_height = max( 1, $base_bbox[1] - $base_bbox[7] );

		list( $target_width, $target_height ) = $this->calculate_text_target_dimensions( $base_width, $base_height, $image_width, $image_height, $options['watermark_image'] );
		$scale = min( $target_width / $base_width, $target_height / $base_height, 1 );
		$render_size = max( 1, (int) round( $size * $scale ) );

		$render_bbox = imagettfbbox( $render_size, 0, $font_path, $text );
		$render_width = max( 1, $render_bbox[2] - $render_bbox[0] );
		$render_height = max( 1, $render_bbox[1] - $render_bbox[7] );
		$render_ascent = max( 0, abs( $render_bbox[7] ) );

		list( $x, $y ) = $this->calculate_image_coordinates( $image_width, $image_height, $render_width, $render_height, $options );

		// Convert hex color to RGB
		$color = ltrim( $color, '#' );
		$r = hexdec( substr( $color, 0, 2 ) );
		$g = hexdec( substr( $color, 2, 2 ) );
		$b = hexdec( substr( $color, 4, 2 ) );

		$alpha = (int) round( ( 127 * ( 100 - $opacity ) ) / 100 );
		$text_color = imagecolorallocatealpha( $image, $r, $g, $b, $alpha );

		// imagettftext expects baseline Y; align baseline to top + ascent.
		imagettftext( $image, $render_size, 0, $x, $y + $render_ascent, $text_color, $font_path, $text );

		return $image;
	}

	/**
	 * Calculate text coordinates for Imagick.
	 *
	 * @param int $image_width Image width.
	 * @param int $image_height Image height.
	 * @param string $text The text.
	 * @param ImagickDraw $draw The draw object.
	 * @param string $position Position string.
	 * @return array [x, y]
	 */
	private function calculate_text_coordinates( $image_width, $image_height, $text, $draw, $position ) {
		$metrics = $draw->getFontMetrics( new Imagick(), $text );
		$text_width = $metrics['textWidth'];
		$text_height = $metrics['textHeight'];

		return $this->calculate_text_coordinates_gd( $image_width, $image_height, $text_width, $text_height, $position );
	}

	/**
	 * Measure text dimensions using Imagick at a given font size.
	 *
	 * @param string $font_path
	 * @param int $size
	 * @param string $text
	 * @return array{width:float,height:float}
	 */
	private function measure_text_imagick( $font_path, $size, $text ) {
		$draw = new ImagickDraw();
		$draw->setFont( $font_path );
		$draw->setFontSize( $size );
		$metrics = $draw->getFontMetrics( new Imagick(), $text );
		$draw->clear();
		$draw->destroy();

		return [
			'width'  => isset( $metrics['textWidth'] ) ? (float) $metrics['textWidth'] : 0.0,
			'height' => isset( $metrics['textHeight'] ) ? (float) $metrics['textHeight'] : 0.0,
			'ascent' => isset( $metrics['ascender'] ) ? (float) $metrics['ascender'] : 0.0,
			'descent' => isset( $metrics['descender'] ) ? abs( (float) $metrics['descender'] ) : 0.0,
		];
	}

	/**
	 * Calculate target text box dimensions according to size type settings.
	 *
	 * @param int $base_width
	 * @param int $base_height
	 * @param int $image_width
	 * @param int $image_height
	 * @param array $watermark_options
	 * @return array{0:int,1:int}
	 */
	private function calculate_text_target_dimensions( $base_width, $base_height, $image_width, $image_height, $watermark_options ) {
		$width = $base_width;
		$height = $base_height;
		$size_type = isset( $watermark_options['watermark_size_type'] ) ? (int) $watermark_options['watermark_size_type'] : 0;

		if ( $size_type === 1 ) {
			if ( ! empty( $watermark_options['absolute_width'] ) ) {
				$width = (int) $watermark_options['absolute_width'];
			}

			if ( ! empty( $watermark_options['absolute_height'] ) ) {
				$height = (int) $watermark_options['absolute_height'];
			}
		} elseif ( $size_type === 2 ) {
			$percent = isset( $watermark_options['width'] ) ? (int) $watermark_options['width'] : 100;
			$target_width = $image_width * ( $percent / 100 );
			$ratio = $base_width > 0 ? $target_width / $base_width : 1;

			$width = $target_width;
			$height = $base_height * $ratio;
		}

		$max_scale = min(
			( $width > 0 ) ? ( $image_width / $width ) : 1,
			( $height > 0 ) ? ( $image_height / $height ) : 1,
			1
		);

		$width = max( 1, (int) round( $width * $max_scale ) );
		$height = max( 1, (int) round( $height * $max_scale ) );

		return [ $width, $height ];
	}

	/**
	 * Calculate text coordinates for GD.
	 *
	 * @param int $image_width Image width.
	 * @param int $image_height Image height.
	 * @param int $text_width Text width.
	 * @param int $text_height Text height.
	 * @param string $position Position string.
	 * @return array [x, y]
	 */
	private function calculate_text_coordinates_gd( $image_width, $image_height, $text_width, $text_height, $position ) {
		$margin = 10; // Margin from edges

		switch ( $position ) {
			case 'top_left':
				$x = $margin;
				$y = $margin + $text_height;
				break;
			case 'top_center':
				$x = ( $image_width - $text_width ) / 2;
				$y = $margin + $text_height;
				break;
			case 'top_right':
				$x = $image_width - $text_width - $margin;
				$y = $margin + $text_height;
				break;
			case 'middle_left':
				$x = $margin;
				$y = ( $image_height + $text_height ) / 2;
				break;
			case 'middle_center':
				$x = ( $image_width - $text_width ) / 2;
				$y = ( $image_height + $text_height ) / 2;
				break;
			case 'middle_right':
				$x = $image_width - $text_width - $margin;
				$y = ( $image_height + $text_height ) / 2;
				break;
			case 'bottom_left':
				$x = $margin;
				$y = $image_height - $margin;
				break;
			case 'bottom_center':
				$x = ( $image_width - $text_width ) / 2;
				$y = $image_height - $margin;
				break;
			case 'bottom_right':
			default:
				$x = $image_width - $text_width - $margin;
				$y = $image_height - $margin;
				break;
		}

		return [ (int) $x, (int) $y ];
	}

	/**
	 * Creates a backup of the original image.
	 *
	 * @param array $data Attachment metadata.
	 * @param array $upload_dir Upload directory info from wp_upload_dir().
	 * @param int $attachment_id Attachment post ID.
	 * @return array Array with 'success' (bool), 'error' (string|null), 'path' (string|null).
	 */
	private function do_backup( $data, $upload_dir, $attachment_id ) {
		if ( ! is_array( $data ) || empty( $data['file'] ) || ! is_string( $data['file'] ) ) {
			return [
				'success' => false,
				'error'   => __( 'Invalid attachment metadata.', 'image-watermark' ),
				'path'    => null,
			];
		}

		$backup_filepath = $this->get_image_backup_filepath( $data['file'] );

		$filepath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $data['file'];

		if ( ! is_file( $filepath ) ) {
			return [
				'success' => false,
				'error'   => __( 'Original file not found.', 'image-watermark' ),
				'path'    => null,
			];
		}

		$current_watermark_id = isset( $this->plugin->options['watermark_image']['url'] ) ? (int) $this->plugin->options['watermark_image']['url'] : 0;
		$backup_watermark_id = (int) get_post_meta( $attachment_id, '_iw_backup_watermark_id', true );

		// If a backup exists but was created with a different watermark image, refresh it to avoid stale overlays.
		if ( file_exists( $backup_filepath ) && $backup_watermark_id === $current_watermark_id ) {
			return [
				'success' => true,
				'error'   => null,
				'path'    => $backup_filepath,
			];
		}

		$mime = wp_check_filetype( $filepath );
		$image = $this->get_image_resource( $filepath, $mime['type'] );

		if ( $image !== false ) {
			$backup_folder = $this->get_image_backup_folder_location( $data['file'] );
			$path = pathinfo( $backup_filepath );

			// Check if backup folder can be created
			if ( ! wp_mkdir_p( $backup_folder ) ) {
				imagedestroy( $image );
				return [
					'success' => false,
					'error'   => sprintf( __( 'Backup folder could not be created: %s', 'image-watermark' ), $backup_folder ),
					'path'    => null,
				];
			}

			if ( ! wp_mkdir_p( $path['dirname'] ) ) {
				imagedestroy( $image );
				return [
					'success' => false,
					'error'   => sprintf( __( 'Backup subfolder could not be created: %s', 'image-watermark' ), $path['dirname'] ),
					'path'    => null,
				];
			}

			// Check if folder is writable
			if ( ! wp_is_writable( $path['dirname'] ) ) {
				imagedestroy( $image );
				return [
					'success' => false,
					'error'   => sprintf( __( 'Backup folder is not writable: %s', 'image-watermark' ), $path['dirname'] ),
					'path'    => null,
				];
			}

			// Copy the original file bit-for-bit after validating it is a decodable image.
			if ( ! $this->copy_file( $filepath, $backup_filepath, 'backup', $attachment_id ) ) {
				imagedestroy( $image );
				return [
					'success' => false,
					'error'   => __( 'Failed to copy original file to backup location.', 'image-watermark' ),
					'path'    => null,
				];
			}

			imagedestroy( $image );
			$image = null;

			update_post_meta( $attachment_id, '_iw_backup_watermark_id', $current_watermark_id );

			return [
				'success' => true,
				'error'   => null,
				'path'    => $backup_filepath,
			];
		}

		return [
			'success' => false,
			'error'   => __( 'Could not read original image file.', 'image-watermark' ),
			'path'    => null,
		];
	}

	/**
	 * Copy a file, optionally preserving timestamps.
	 *
	 * @param string $source Source file path.
	 * @param string $destination Destination file path.
	 * @return bool True on success, false on failure.
	 */
	private function copy_file( $source, $destination, $context = '', $attachment_id = 0 ) {
		$preserve = ! empty( $this->plugin->options['backup']['preserve_timestamps'] );
		$preserve = apply_filters( 'iw_preserve_backup_timestamps', $preserve, $source, $destination, $context, $attachment_id );

		if ( $preserve ) {
			return $this->copy_with_timestamps( $source, $destination );
		}

		return copy( $source, $destination );
	}

	/**
	 * Copy file preserving original timestamps.
	 *
	 * @param string $source Source file path.
	 * @param string $destination Destination file path.
	 * @return bool True on success, false on failure.
	 */
	private function copy_with_timestamps( $source, $destination ) {
		if ( ! is_file( $source ) ) {
			return false;
		}

		$mtime = filemtime( $source );
		$atime = fileatime( $source );

		if ( ! copy( $source, $destination ) ) {
			return false;
		}

		if ( $mtime !== false ) {
			$atime = ( $atime !== false ) ? $atime : $mtime;
			if ( @touch( $destination, $mtime, $atime ) === false ) {
				$this->maybe_add_timestamp_notice();
			}
		}

		return true;
	}

	/**
	 * Adds an admin notice when timestamp preservation fails.
	 *
	 * @return void
	 */
	private function maybe_add_timestamp_notice() {
		if ( $this->timestamp_preserve_notice_added ) {
			return;
		}

		$this->timestamp_preserve_notice_added = true;
		$this->add_admin_notice(
			__( 'Image Watermark: File timestamps could not be preserved on this server. Copies were created, but dates may differ.', 'image-watermark' ),
			'warning'
		);
	}

	/**
	 * Returns image resource based on mime type.
	 *
	 * @param string $filepath
	 * @param string $mime_type
	 *
	 * @return resource|false
	 */
	private function get_image_resource( $filepath, $mime_type ) {
		switch ( $mime_type ) {
			case 'image/jpeg':
			case 'image/pjpeg':
				if ( function_exists( 'imagecreatefromjpeg' ) ) {
					$image = imagecreatefromjpeg( $filepath );
				} else {
					$image = false;
				}
				break;

			case 'image/png':
				if ( function_exists( 'imagecreatefrompng' ) ) {
					$image = imagecreatefrompng( $filepath );
				} else {
					$image = false;
				}

				break;

			case 'image/webp':
				if ( function_exists( 'imagecreatefromwebp' ) ) {
					$image = imagecreatefromwebp( $filepath );
				} else {
					$image = false;
				}

				break;

			default:
				$image = false;
		}

		if ( is_resource( $image ) ) {
			imagealphablending( $image, false );
			imagesavealpha( $image, true );
		}

		return $image;
	}

	/**
	 * Returns filename without directory structure.
	 *
	 * @param string $filepath
	 *
	 * @return string
	 */
	private function get_image_filename( $filepath ) {
		return basename( $filepath );
	}

	/**
	 * Returns backup folder path for an attachment.
	 *
	 * @param string $filepath
	 *
	 * @return string
	 */
	private function get_image_backup_folder_location( $filepath ) {
		$path = explode( DIRECTORY_SEPARATOR, $filepath );
		array_pop( $path );
		$path = implode( DIRECTORY_SEPARATOR, $path );

		return IMAGE_WATERMARK_BACKUP_DIR . DIRECTORY_SEPARATOR . $path;
	}

	/**
	 * Returns backup file path for an attachment.
	 *
	 * @param string $filepath
	 *
	 * @return string
	 */
	private function get_image_backup_filepath( $filepath ) {
		return IMAGE_WATERMARK_BACKUP_DIR . DIRECTORY_SEPARATOR . $filepath;
	}

	/**
	 * Adds watermark image using GD.
	 *
	 * @param resource $image
	 * @param array $options
	 * @param array $upload_dir
	 *
	 * @return bool|resource
	 */
	private function add_watermark_image( $image, $options, $upload_dir ) {
		if ( ! wp_attachment_is_image( $options['watermark_image']['url'] ) ) {
			return false;
		}

		$watermark_file = wp_get_attachment_metadata( $options['watermark_image']['url'], true );
		if ( ! is_array( $watermark_file ) || empty( $watermark_file['file'] ) ) {
			return false;
		}

		$url = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $watermark_file['file'];

		if ( ! is_file( $url ) ) {
			return false;
		}
		$watermark_file_info = getimagesize( $url );

		switch ( $watermark_file_info['mime'] ) {
			case 'image/jpeg':
			case 'image/pjpeg':
				$watermark = imagecreatefromjpeg( $url );
				break;

			case 'image/gif':
				$watermark = imagecreatefromgif( $url );
				break;

			case 'image/png':
				$watermark = imagecreatefrompng( $url );
				break;

			case 'image/webp':
				$watermark = imagecreatefromwebp( $url );
				break;

			default:
				return false;
		}

		$image_width = imagesx( $image );
		$image_height = imagesy( $image );

		list( $w, $h ) = $this->calculate_watermark_dimensions( $image_width, $image_height, imagesx( $watermark ), imagesy( $watermark ), $options );

		list( $dest_x, $dest_y ) = $this->calculate_image_coordinates( $image_width, $image_height, $w, $h, $options );

		$this->imagecopymerge_alpha( $image, $this->resize( $watermark, $w, $h, $watermark_file_info ), $dest_x, $dest_y, 0, 0, $w, $h, $options['watermark_image']['transparent'] );

		if ( $options['watermark_image']['jpeg_format'] === 'progressive' ) {
			imageinterlace( $image, true );
		}

		return $image;
	}

	/**
	 * Copies image with transparency when merging.
	 *
	 * @param resource $dst_im Destination image resource.
	 * @param resource $src_im Source image resource to merge.
	 * @param int $dst_x Destination X coordinate.
	 * @param int $dst_y Destination Y coordinate.
	 * @param int $src_x Source X coordinate.
	 * @param int $src_y Source Y coordinate.
	 * @param int $src_w Source width.
	 * @param int $src_h Source height.
	 * @param int $pct Merge percentage (0-100).
	 * @return void
	 */
	private function imagecopymerge_alpha( $dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct ) {
		// Clamp percentage to 0-100
		$pct = max( 0, min( 100, (int) $pct ) );

		// Prepare an overlay copy with preserved alpha
		$overlay = imagecreatetruecolor( $src_w, $src_h );
		imagealphablending( $overlay, false );
		imagesavealpha( $overlay, true );
		$transparent = imagecolorallocatealpha( $overlay, 255, 255, 255, 127 );
		imagefilledrectangle( $overlay, 0, 0, $src_w, $src_h, $transparent );

		// Copy the watermark region into the overlay while keeping its alpha
		imagecopy( $overlay, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h );

		// If opacity is below 100%, adjust alpha per pixel to preserve transparency.
		if ( $pct < 100 ) {
			$width = imagesx( $overlay );
			$height = imagesy( $overlay );
			$color_cache = [];

			for ( $x = 0; $x < $width; $x++ ) {
				for ( $y = 0; $y < $height; $y++ ) {
					$rgba = imagecolorat( $overlay, $x, $y );
					$alpha = ( $rgba & 0x7F000000 ) >> 24;

					if ( $alpha === 127 ) {
						continue;
					}

					$r = ( $rgba >> 16 ) & 0xFF;
					$g = ( $rgba >> 8 ) & 0xFF;
					$b = $rgba & 0xFF;
					$new_alpha = (int) round( 127 - ( ( 127 - $alpha ) * $pct / 100 ) );
					$cache_key = $r . ',' . $g . ',' . $b . ',' . $new_alpha;

					if ( ! isset( $color_cache[ $cache_key ] ) ) {
						$color_cache[ $cache_key ] = imagecolorallocatealpha( $overlay, $r, $g, $b, $new_alpha );
					}

					imagesetpixel( $overlay, $x, $y, $color_cache[ $cache_key ] );
				}
			}
		}

		// Blend onto the destination using the overlay's alpha channel
		imagealphablending( $dst_im, true );
		imagecopy( $dst_im, $overlay, $dst_x, $dst_y, 0, 0, $src_w, $src_h );
		imagealphablending( $dst_im, false );
		imagesavealpha( $dst_im, true );
		imagedestroy( $overlay );
	}

	/**
	 * Resizes a watermark resource.
	 *
	 * @param resource $image Source image resource.
	 * @param int $width Target width.
	 * @param int $height Target height.
	 * @param array $info Array returned by getimagesize() for the source image.
	 * @return resource New image resource on success.
	 */
	private function resize( $image, $width, $height, $info ) {
		$new_image = imagecreatetruecolor( $width, $height );

		// PNG (3) and WebP (18) need transparent background
		if ( $info[2] === 3 || $info[2] === 18 ) {
			imagealphablending( $new_image, false );
			imagesavealpha( $new_image, true );
			imagefilledrectangle( $new_image, 0, 0, $width, $height, imagecolorallocatealpha( $new_image, 255, 255, 255, 127 ) );
		}

		imagecopyresampled( $new_image, $image, 0, 0, 0, 0, $width, $height, $info[0], $info[1] );

		return $new_image;
	}

	/**
	 * Writes an image resource to a file.
	 *
	 * @param resource $image Image resource to write.
	 * @param string $mime_type MIME type for the output image.
	 * @param string $filepath Destination filesystem path.
	 * @param int $quality Quality parameter (0-100) for lossy formats.
	 * @return void
	 */
	private function save_image_file( $image, $mime_type, $filepath, $quality ) {
		switch ( $mime_type ) {
			case 'image/jpeg':
			case 'image/pjpeg':
				imagejpeg( $image, $filepath, $quality );
				break;

			case 'image/png':
				imagepng( $image, $filepath, (int) round( 9 - ( 9 * $quality / 100 ), 0 ) );
				break;

			case 'image/webp':
				imagewebp( $image, $filepath, $quality );
				break;
		}
	}

	/**
	 * Calculates watermark dimensions based on settings.
	 *
	 * @param int $image_width Width of the target image.
	 * @param int $image_height Height of the target image.
	 * @param int $watermark_width Original watermark width.
	 * @param int $watermark_height Original watermark height.
	 * @param array $options Plugin options influencing size calculation.
	 * @return int[] Array containing [width, height] for the watermark.
	 */
	private function calculate_watermark_dimensions( $image_width, $image_height, $watermark_width, $watermark_height, $options ) {
		if ( $options['watermark_image']['watermark_size_type'] === 1 ) {
			$width = $options['watermark_image']['absolute_width'];
			$height = $options['watermark_image']['absolute_height'];
		} elseif ( $options['watermark_image']['watermark_size_type'] === 2 ) {
			$ratio = $image_width * $options['watermark_image']['width'] / 100 / $watermark_width;
			$width = (int) ( $watermark_width * $ratio );
			$height = (int) ( $watermark_height * $ratio );

			if ( $height > $image_height ) {
				$width = (int) ( $image_height * $width / $height );
				$height = $image_height;
			}
		} else {
			$width = $watermark_width;
			$height = $watermark_height;
		}

		return [ $width, $height ];
	}

	/**
	 * Calculates watermark coordinates based on alignment and offsets.
	 *
	 * @param int $image_width Width of the target image.
	 * @param int $image_height Height of the target image.
	 * @param int $watermark_width Calculated watermark width.
	 * @param int $watermark_height Calculated watermark height.
	 * @param array $options Plugin options influencing position and offsets.
	 * @return int[] Array containing [x, y] destination coordinates.
	 */
	private function calculate_image_coordinates( $image_width, $image_height, $watermark_width, $watermark_height, $options ) {
		$position = isset( $options['watermark_image']['position'] ) ? $options['watermark_image']['position'] : 'bottom_right';

		switch ( $position ) {
			case 'top_left':
				$dest_x = $dest_y = 0;
				break;

			case 'top_center':
				$dest_x = (int) round( ( $image_width / 2 ) - ( $watermark_width / 2 ), 0 );
				$dest_y = 0;
				break;

			case 'top_right':
				$dest_x = $image_width - $watermark_width;
				$dest_y = 0;
				break;

			case 'middle_left':
				$dest_x = 0;
				$dest_y = (int) round( ( $image_height / 2 ) - ( $watermark_height / 2 ), 0 );
				break;

			case 'middle_right':
				$dest_x = $image_width - $watermark_width;
				$dest_y = (int) round( ( $image_height / 2 ) - ( $watermark_height / 2 ), 0 );
				break;

			case 'bottom_left':
				$dest_x = 0;
				$dest_y = $image_height - $watermark_height;
				break;

			case 'bottom_center':
				$dest_x = (int) round( ( $image_width / 2 ) - ( $watermark_width / 2 ), 0 );
				$dest_y = $image_height - $watermark_height;
				break;

			case 'bottom_right':
				$dest_x = $image_width - $watermark_width;
				$dest_y = $image_height - $watermark_height;
				break;

			case 'middle_center':
			default:
				$dest_x = (int) round( ( $image_width / 2 ) - ( $watermark_width / 2 ), 0 );
				$dest_y = (int) round( ( $image_height / 2 ) - ( $watermark_height / 2 ), 0 );
		}

		$offset_x = isset( $options['watermark_image']['offset_width'] ) ? (int) $options['watermark_image']['offset_width'] : 0;
		$offset_y = isset( $options['watermark_image']['offset_height'] ) ? (int) $options['watermark_image']['offset_height'] : 0;

		if ( $options['watermark_image']['offset_unit'] === 'pixels' ) {
			$offset_x = (int) $offset_x;
			$offset_y = (int) $offset_y;
		} else {
			$offset_x = (int) round( $image_width * $offset_x / 100, 0 );
			$offset_y = (int) round( $image_height * $offset_y / 100, 0 );
		}

		// Apply offset directionally: right/bottom positions move inward by subtracting, left/top move outward by adding.
		if ( strpos( $position, 'right' ) !== false ) {
			$dest_x -= $offset_x;
		} elseif ( strpos( $position, 'left' ) !== false ) {
			$dest_x += $offset_x;
		} else { // center
			$dest_x += $offset_x;
		}

		if ( strpos( $position, 'bottom' ) !== false ) {
			$dest_y -= $offset_y;
		} elseif ( strpos( $position, 'top' ) !== false ) {
			$dest_y += $offset_y;
		} else { // middle
			$dest_y += $offset_y;
		}

		return [ (int) $dest_x, (int) $dest_y ];
	}

	/**
	 * Removes stored backup when an attachment is deleted.
	 *
	 * @param int $attachment_id
	 *
	 * @return void
	 */
	public function delete_attachment( $attachment_id ) {
		$filepath = get_post_meta( $attachment_id, '_wp_attached_file', true );

		if ( ! $filepath ) {
			return;
		}

		$backup_filepath = $this->get_image_backup_filepath( $filepath );

		if ( $backup_filepath && file_exists( $backup_filepath ) ) {
			unlink( $backup_filepath );
		}
	}
}
