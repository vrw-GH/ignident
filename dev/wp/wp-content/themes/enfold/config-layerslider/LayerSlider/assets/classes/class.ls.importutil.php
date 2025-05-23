<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;

/**
 * Class for working with ZIP archives to import
 * sliders with images and other attachments.
 *
 * @package LS_ImportUtil
 * @since 5.0.3
 * @author John Gera
 * @copyright Copyright (c) 2024  John Gera, George Krupa, and Kreatura Media Kft.
 */

class LS_ImportUtil {

	// Counts the number of sliders imported.
	public $sliderCount = 0;


	// Database ID of the lastly imported slider.
	public $lastImportId;


	// Last import error code
	public $lastErrorCode;


	// Target folders
	private $uploadsDir, $uploadsURL, $unpackDir;


	// Imported images
	private $imported = [];

	private $isTemplate = false;


	// Accepts $_FILES
	public function __construct( $archive, $name = null, $groupName = null, $isTemplate = false ) {

		// Attempt to workaround memory limit & execution time issues
		@ini_set( 'max_execution_time', 0 );
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );

		if( empty( $name ) ) {
			$name = $archive;
		}

		// Get uploads folder
		$uploads = wp_upload_dir();

		// Check if /uploads dir is writable
		if( ! is_writable( $uploads['basedir'] ) ) {
			return false;
		}

		// Get target folders
		$this->uploadsDir 	= $uploads['basedir'];
		$this->uploadsURL 	= $uploads['baseurl'];
		$this->unpackDir 	= LS_FileSystem::createUniqueTmpFolder( pathinfo( $name, PATHINFO_FILENAME ).'_template' );
		$this->isTemplate 	= $isTemplate;

		$type = wp_check_filetype( basename( $name ), [
			'zip' => 'application/zip',
			'json' => 'application/json'
		]);

		// Check for ZIP
		if( ! empty( $type['ext'] ) && $type['ext'] == 'zip') {


			// Remove previous uploads (if any)
			$this->cleanup();

			// Extract ZIP
			if( $this->unpack( $archive ) ) {

				// Make sure all imported items have the same creation date for sorting purposes
				$addProperties['date_c'] = time();

				// Uploaded folders
				$folders = glob( $this->unpackDir.'/*', GLOB_ONLYDIR );
				$folders = $this->reduceSliders( $folders );

				// Sort projects by name
				natsort( $folders );

				$groupId = NULL;
				if( ! empty( $groupName ) && count( $folders ) > 1 ) {
					$groupId = LS_Sliders::addGroup( $groupName );
				}

				foreach( $folders as $key => $dir ) {

					$this->imported = [];

					if( ! isset( $_POST['skip_images'] ) ) {
						$this->uploadMedia( $dir, 'uploads' );
						$this->uploadMedia( $dir, 'assets' );
					}

					if( file_exists($dir.'/settings.json') ) {
						$this->lastImportId = $this->addSlider(
							$dir.'/settings.json',
							$groupId,
							$addProperties
						);
					}
				}

				// Finishing up
				$this->cleanup();
				return true;
			}



		// Check for JSON
		} elseif( ! empty( $type['ext'] ) && $type['ext'] == 'json') {

			// Get decoded file data
			$data = file_get_contents( $archive );
			if( $decoded = base64_decode( $data, true ) ) {
				if( ! $parsed = json_decode( $decoded, true ) ) {
					$parsed = unserialize( $decoded );
				}

			// Since v5.1.1
			} else {
				$parsed = [ json_decode( $data, true ) ];
			}

			// Iterate over imported sliders
			if( is_array( $parsed ) ) {

				// Import sliders
				foreach( $parsed as $item ) {

					// Increment the slider counter
					$this->sliderCount++;

					// Fix for export issue in v4.6.4
					if( is_string( $item ) ) { $item = json_decode($item, true); }

					$this->lastImportId = LS_Sliders::add(
						$item['properties']['title'],
						$item
					);
				}
			}
		}

		// Return false otherwise
		return false;
	}



	public function unpack( $archive ) {

		if( LS_FileSystem::createUploadDirs() ) {
			return LS_FileSystem::unzip( $archive, $this->unpackDir );
		}

		return false;
	}




	public function uploadMedia( $dir = null, $subdir = null) {

		// Check provided data
		if(
			empty( $dir ) ||
			empty( $subdir ) ||
			! is_string( $dir ) ||
			! is_string( $subdir ) ||
			! file_exists( $dir.'/'.$subdir )
		) {
			return false;
		}

		// Create folder if it isn't exists already
		$baseDir 	= ( $subdir === 'assets' ) ? $this->uploadsDir.'/layerslider/assets/imported' : $this->uploadsDir.'/layerslider/projects';
		$baseURL 	= ( $subdir === 'assets' ) ? $this->uploadsURL.'/layerslider/assets/imported' : $this->uploadsURL.'/layerslider/projects';

		$targetDir 	= $baseDir.'/'.basename( $dir );
		$targetURL 	= $baseURL.'/'.basename( $dir );

		if( ! file_exists( $targetDir ) ) {
			mkdir( $targetDir, 0755 );
		}

		// Include image.php for media library upload
		require_once( ABSPATH.'wp-admin/includes/image.php' );

		// Iterate through directory
		foreach( glob( "$dir/$subdir/*" ) as $imagePath ) {

			$fileName 	= sanitize_file_name( basename( $imagePath ) );
			$filePath 	= $targetDir.'/'.$fileName;
			$fileURL 	= $targetURL.'/'.$fileName;

			// Validate media
			$filetype = wp_check_filetype($fileName, null);
			if( ! empty( $filetype['ext'] ) && $filetype['ext'] != 'php' ) {

				// New upload
				if( ! $attach_id = $this->attachIDForURL( $fileURL, $filePath ) ) {

					// Move item to place
					rename($imagePath, $filePath);

					// Upload to media library
					$attachment = [
						'guid' => $filePath,
						'post_mime_type' => $filetype['type'],
						'post_title' => preg_replace( '/\.[^.]+$/', '', $fileName),
						'post_content' => '',
						'post_status' => 'inherit'
					];

					$attach_id = wp_insert_attachment($attachment, $filePath, 37);
					if($attach_data = wp_generate_attachment_metadata($attach_id, $filePath)) {
						wp_update_attachment_metadata($attach_id, $attach_data);
					}

					$this->imported[$fileName] = [
						'id' => $attach_id,
						'url' => $fileURL
					];

				// Already uploaded
				} else {

					$this->imported[$fileName] = [
						'id' => $attach_id,
						'url' => $fileURL
					];
				}
			}
		}

		return true;
	}




	public function addSlider( $file, $groupId = NULL, $addProperties = [] ) {

		// Increment the slider counter
		$this->sliderCount++;

		// Get slider data and title
		$data = json_decode(file_get_contents($file), true);
		$title = $data['properties']['title'];
		$slug = !empty($data['properties']['slug']) ? $data['properties']['slug'] : '';

		// Import Google Fonts used in slider
		if( empty( $data['googlefonts'] ) || ! is_array( $data['googlefonts'] ) ) {
			$data['googlefonts'] = [];
		}

		foreach( $data['googlefonts'] as $fontIndex => $font ) {
			$fontParam = explode(':', $font['param'] );
			$font = urldecode( $fontParam[0] );
			$font = str_replace(['+', '"', "'"], [' ', '', ''], $font);

			$data['googlefonts'][ $fontIndex ] = [ 'param' => $font ];
		}

		// Slider Preview
		if( ! empty($data['meta']) && ! empty($data['meta']['preview']) ) {
			$data['meta']['previewId'] = $this->attachIDForImage($data['meta']['preview']);
			$data['meta']['preview'] = $this->attachURLForImage($data['meta']['preview']);
		}

		// Slider settings
		if(!empty($data['properties']['backgroundimage'])) {
			$data['properties']['backgroundimageId'] = $this->attachIDForImage($data['properties']['backgroundimage']);
			$data['properties']['backgroundimage'] = $this->attachURLForImage($data['properties']['backgroundimage']);
		}


		// Slides
		if(!empty($data['layers']) && is_array($data['layers'])) {
		foreach($data['layers'] as &$slide) {

			if(!empty($slide['properties']['background'])) {
				$slide['properties']['backgroundId'] = $this->attachIDForImage($slide['properties']['background']);
				$slide['properties']['background'] = $this->attachURLForImage($slide['properties']['background']);
			}

			if(!empty($slide['properties']['thumbnail'])) {
				$slide['properties']['thumbnailId'] = $this->attachIDForImage($slide['properties']['thumbnail']);
				$slide['properties']['thumbnail'] = $this->attachURLForImage($slide['properties']['thumbnail']);
			}

			// Layers
			if(!empty($slide['sublayers']) && is_array($slide['sublayers'])) {
			foreach($slide['sublayers'] as &$layer) {

				if( ! empty($layer['image']) ) {
					$layer['imageId'] = $this->attachIDForImage($layer['image']);
					$layer['image'] = $this->attachURLForImage($layer['image']);
				}

				if( ! empty($layer['poster']) ) {
					$layer['posterId'] = $this->attachIDForImage($layer['poster']);
					$layer['poster'] = $this->attachURLForImage($layer['poster']);
				}

				if( ! empty($layer['layerBackground']) ) {
					$layer['layerBackgroundId'] = $this->attachIDForImage($layer['layerBackground']);
					$layer['layerBackground'] = $this->attachURLForImage($layer['layerBackground']);
				}

				if( ! empty( $layer['mediaAttachments'] ) ) {
					foreach( $layer['mediaAttachments'] as $mediaKey => $media ) {
						$layer['mediaAttachments'][$mediaKey]['id'] = $this->attachIDForImage( $media['url'] );
						$layer['mediaAttachments'][$mediaKey]['url'] = $this->attachURLForImage( $media['url'] );
					}
				}
			}}
		}}

		// Add slider
		return LS_Sliders::add( $title, $data, $slug, $groupId, $addProperties );
	}


	public function reduceSliders( $folders ) {

		if( empty( $folders ) ) {
			return [];
		}

		if( LS_Config::isActivatedSite() ) {
			return $folders;
		}

		foreach( $folders as $key => $dir ) {

			if( file_exists($dir.'/settings.json') ) {
				$data = json_decode(file_get_contents( $dir.'/settings.json' ), true);
				if( ! empty( $data['properties']['pt'] ) ) {
					unset( $folders[ $key ] );
					$this->lastErrorCode = 'LR_PARTIAL_IMPORT';
				}
			}
		}

		$folders = array_values( $folders );

		if( empty( $folders ) ) {
			$this->lastErrorCode = 'LR_EMPTY_IMPORT';
		}

		return $folders;
	}


	// DEPRECATED: Should not be used
	// It does nothing. It's only here as a compatibility measure.
	public function addGoogleFonts( $data ) {

	}



	public function attachURLForImage($file = '') {

		$file = sanitize_file_name( basename($file) );

		if( isset($this->imported[ $file ]) ) {
			return $this->imported[ $file ]['url'];
		}

		return $file;
	}


	public function attachIDForImage( $file = '' ) {

		$file = sanitize_file_name( basename($file) );

		if( isset($this->imported[ $file ]) ) {
			return $this->imported[ $file ]['id'];
		}

		return '';
	}

	public function attachIDForURL( $url, $path ) {

		// Attempt to retrieve the post ID from the built in
		// attachment_url_to_postid() WP function when available.
		if( function_exists('attachment_url_to_postid') ) {
			if( $attachID = attachment_url_to_postid( $url ) ) {
				return $attachID;
			}
		}

		global $wpdb;

		if( empty( $this->uploadsDir ) ) {
			$uploads = wp_upload_dir();
			$this->uploadsDir = trailingslashit($uploads['basedir']);
		}

		$imgPath  = explode( parse_url( $this->uploadsDir, PHP_URL_PATH ), $path );
		$attachs = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $imgPath[1] ) );


		return ! empty( $attachs[0] ) ? $attachs[0] : 0;
	}

	public function cleanup() {
		LS_FileSystem::deleteDir( $this->unpackDir );
		LS_FileSystem::cleanupTmpFiles();
	}
}
?>