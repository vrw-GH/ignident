<?php

/**
 * Class DashboardHelper
 *
 * Helper class for managing the dashboard.
 *
 * @package    WowPlugin
 * @subpackage Admin
 * @author     Dmytro Lobov <dev@wow-company.com>, Wow-Company
 * @copyright  2024 Dmytro Lobov
 * @license    GPL-2.0+
 *
 */

namespace FloatingButton\Admin;

defined( 'ABSPATH' ) || exit;

use FloatingButton\WOWP_Plugin;

class DashboardHelper {

	public static function first_file( $folder ) {

		$files = self::get_files( $folder );
		if ( empty( $files ) ) {
			return false;
		}

		return reset( $files )['file'];
	}

	public static function get_files( $folder ): array {
		$scan_files = scandir( self::get_folder_path( $folder ) );
		$files      = [];
		$license    = false;
		foreach ( $scan_files as $file ) {
			if ( $file === '.' || $file === '..' || $file === 'index.php' ) {
				continue;
			}
			$extension = pathinfo( $file, PATHINFO_EXTENSION );

			$matches = explode( '.', $file );

			if ( ! is_numeric( $matches[0] ) ) {
				continue;
			}

			$file_name = self::get_file_name( $file, $folder );

			$files[ $matches[0] ] = [ 'file' => $matches[1], 'name' => $file_name ];

		}

		return $files;
	}

	public static function get_file_name( $file, $folder ): string {

		$file_path = self::get_folder_path( $folder ) . '/' . $file;
		$file_data = get_file_data( $file_path, array( 'name' => 'Page Name' ) );

		return $file_data['name'];

	}

	public static function get_folder_path( $folder ): string {
		return WOWP_Plugin::dir() . 'admin/' . $folder;
	}

	public static function get_file( $current, $folder ) {
		$files = scandir( self::get_folder_path( $folder ) );
		foreach ( $files as $file ) {
			$matches = explode( '.', $file );
			if ( isset( $matches[1] ) && $matches[1] === $current ) {
				return $file;
			}
		}

		return false;
	}

	public static function search_value( $array, $value ): bool {

		foreach ( $array as $item ) {
			if ( is_array( $item ) ) {
				if ( self::search_value( $item, $value ) ) {
					return true;
				}
			} else {
				if ( $item === $value ) {
					return true;
				}
			}
		}

		return false;
	}

}