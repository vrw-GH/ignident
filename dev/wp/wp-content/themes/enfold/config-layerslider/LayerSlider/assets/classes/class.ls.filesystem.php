<?php

class LS_FileSystem {


	public static function addIndexPHP( $dir ) {

		if( ! file_exists( $dir.'/index.php' ) ) {
			@file_put_contents( $dir.'/index.php', '<?php // Silence is golden.');
		}
	}


	public static function emptyDir( $path, $removeDir = false ) {

		if( ! file_exists( $path ) ) {
			return true;
		}

		if( is_file( $path ) ) {
			return @unlink($path);

		} elseif( is_dir( $path ) ) {
			$path = rtrim( $path, '/' );

			// Attempt to also find hidden files
			if( defined('GLOB_BRACE') ) {
				$scan = glob( $path.'/{*,.[!.]*,..?*}*', GLOB_BRACE );

			// Fallback if PHP version does not support GLOB_BRACE
			} else {
				$scan = glob( $path.'/*' );
			}

			foreach( $scan as $index => $item) {
				self::emptyDir( $item, true );
			}

			if( $removeDir ) {
				return @rmdir($path);
			}
		}

		return true;
	}



	public static function deleteDir( $dir ) {

		if( ! self::emptyDir( $dir ) ) {
			return false;
		}

		return @rmdir( $dir );
	}



	public static function createUploadDirs() {

		$uploads 		= wp_upload_dir();
		$uploadsBaseDir = $uploads['basedir'];

		// Check if /uploads dir is writable
		if( ! is_writable( $uploadsBaseDir ) ) {
			return false;
		}

		$directories = [
			$uploadsBaseDir.'/layerslider',
			$uploadsBaseDir.'/layerslider/tmp',
			$uploadsBaseDir.'/layerslider/addons',
			$uploadsBaseDir.'/layerslider/modules',
			$uploadsBaseDir.'/layerslider/projects',
			$uploadsBaseDir.'/layerslider/fonts',
			$uploadsBaseDir.'/layerslider/google-fonts',
			$uploadsBaseDir.'/layerslider/assets',
			$uploadsBaseDir.'/layerslider/assets/objects',
			$uploadsBaseDir.'/layerslider/assets/remote',
			$uploadsBaseDir.'/layerslider/assets/imported',
		];

		foreach( $directories as $dir ) {

			if( ! is_dir( $dir ) ) {
				wp_mkdir_p( $dir );
				LS_FileSystem::addIndexPHP( $dir );
			}
		}

		return true;
	}



	public static function unzip( $archive, $to ) {

		require_once( ABSPATH.'wp-admin/includes/file.php' );

		// Unpack archive
		WP_Filesystem();
		global $wp_filesystem;

		$file = unzip_file( $archive, $to );

		if( is_wp_error( $file ) ) {

			if( ! defined('FS_METHOD') ) {
				define('FS_METHOD', 'direct');
				WP_Filesystem();
			}

			$file = unzip_file( $archive, $to );

			if( is_wp_error( $file ) ) {
				return false;
			}
		}

		return true;
	}


	public static function createUniqueTmpFolder( $prefix = 'upload' ) {

		self::createUploadDirs();
		self::cleanupTmpFiles();

		$uploads 		= wp_upload_dir();
		$uploadsBaseDir = $uploads['basedir'];
		$tmpFolder 		= $uploadsBaseDir.'/layerslider/tmp';

		$uniqueFolder = $tmpFolder . '/' . uniqid( $prefix.'_', true);
		wp_mkdir_p( $uniqueFolder );

		return $uniqueFolder;
	}


	public static function cleanupTmpFiles( $expSeconds = 3600 ) {

		$uploads 		= wp_upload_dir();
		$uploadsBaseDir = $uploads['basedir'];
		$tmpFolder 		= $uploadsBaseDir.'/layerslider/tmp';

		$files = @scandir( $tmpFolder );
		$now = time();

		if( ! $files || ! is_array( $files ) ) {
			return;
		}

		foreach( $files as $file ) {
			if( $file !== '.' && $file !== '..' ) {
				$filePath = $tmpFolder . '/' . $file;

				if( is_dir( $filePath ) && ( $now - filemtime($filePath ) > $expSeconds ) ) {
					self::deleteDir( $filePath );
				}
			}
		}
	}
}