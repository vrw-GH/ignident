<?php
/**
 * Helper functions for Input Output related tasks.
 *
 * @package WP_Defender\Traits
 */

namespace WP_Defender\Traits;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use WP_Defender\Helper\File as File_Helper;
use WP_Defender\Component\Logger\Rotation_Logger as Logger;

trait IO {

	/**
	 * A simple function to create & return the folder that we can use to write tmp files.
	 *
	 * @param  bool $main_site_path  If true then return main site's upload dir path for a multisite.
	 *
	 * @since 4.1.0 The `$main_site_path` parameter was added.
	 * @return string
	 */
	protected function get_tmp_path( bool $main_site_path = false ): string {
		global $wp_filesystem;
		// Initialize the WP filesystem, no more using 'file-put-contents' function.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$is_switch_to_main_site = $main_site_path && is_multisite() && ! is_main_site();
		if ( $is_switch_to_main_site ) {
			// Switch to the main site.
			switch_to_blog( get_main_site_id() );
		}

		$upload_dir = wp_upload_dir()['basedir'];

		if ( $is_switch_to_main_site ) {
			// Restore the current site if switched.
			restore_current_blog();
		}

		$tmp_dir = $upload_dir . DIRECTORY_SEPARATOR . 'wp-defender';
		if ( ! is_dir( $tmp_dir ) ) {
			wp_mkdir_p( $tmp_dir );
		}

		if ( ! is_file( $tmp_dir . DIRECTORY_SEPARATOR . 'index.php' ) ) {
			$wp_filesystem->put_contents( $tmp_dir . DIRECTORY_SEPARATOR . 'index.php', '' );
		}

		$file_helper = wd_di()->get( File_Helper::class );
		$file_helper->maybe_dir_access_deny( $tmp_dir );

		return $tmp_dir;
	}

	/**
	 * Returns the path to the log file for a given category.
	 *
	 * @param  string $category  The category of the log file. Defaults to an empty string.
	 *
	 * @return string The path to the log file.
	 */
	public function get_log_path( $category = '' ): string {
		$file = empty( $category ) ? 'defender.log' : $category;

		$logger    = new Logger();
		$file_name = $logger->generate_file_name( $file );

		return $this->get_tmp_path() . DIRECTORY_SEPARATOR . $file_name;
	}

	/**
	 * Create a lock. This will be used in scanning.
	 *
	 * @return string
	 */
	protected function get_lock_path(): string {
		return $this->get_tmp_path() . DIRECTORY_SEPARATOR . 'scan.lock';
	}

	/**
	 * Create a lock. This will be used for 2FA.
	 *
	 * @return string
	 */
	protected function get_2fa_lock_path(): string {
		return $this->get_tmp_path() . DIRECTORY_SEPARATOR . 'two-fa.lock';
	}

	/**
	 * Delete a folder with every content inside.
	 *
	 * @param  string $dir  The path to the folder.
	 *
	 * @return bool
	 */
	public function delete_dir( $dir ): bool {
		global $wp_filesystem;
		// Initialize the WP filesystem, no more using 'file-put-contents' function.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		if ( ! is_dir( $dir ) ) {
			return false;
		}
		$it    = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
		$files = new RecursiveIteratorIterator(
			$it,
			RecursiveIteratorIterator::CHILD_FIRST
		);
		$ret   = true;
		foreach ( $files as $file ) {
			if ( $file->isDir() ) {
				$ret = $wp_filesystem->rmdir( $file->getPathname(), true );
			} else {
				$wp_filesystem->delete( $file->getPathname() );
			}
			if ( false === $ret ) {
				return false;
			}
		}

		return $wp_filesystem->rmdir( $dir, true );
	}

	/**
	 * Not remove double quotes inside str_replace().
	 *
	 * @param  string $data  The string or array being searched and replaced on.
	 *
	 * @return array|string
	 */
	protected function convert_end_lines_dos_to_linux( $data ) {
		return str_replace( array( "\r\n", "\r" ), "\n", $data );
	}

	/**
	 * Not remove double quotes inside str_replace().
	 *
	 * @param  string $data  The string or array being searched and replaced on.
	 *
	 * @return array|string
	 */
	protected function convert_end_lines_linux_to_dos( $data ) {
		return str_replace( "\n", "\r\n", $this->convert_end_lines_dos_to_linux( $data ) );
	}

	/**
	 * Compare hashes on different OS.
	 *
	 * @param  string       $file_path  The filename.
	 * @param  string|array $file_hash  The user-supplied string to compare against.
	 *
	 * @return bool
	 */
	protected function compare_hashes_on_different_os( $file_path, $file_hash ) {
		if ( hash_equals( md5_file( $file_path ), $file_hash ) ) {
			return true;
		}
		if ( hash_equals( $this->hash_file( $file_path, 'linux' ), $file_hash ) ) {
			return true;
		}
		if ( hash_equals( $this->hash_file( $file_path, 'dos' ), $file_hash ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Compare hashes.
	 *
	 * @param  string       $file_path  Path to file.
	 * @param  string|array $file_hash  Hash or some hashes of file2, e.g. for readme.txt.
	 *
	 * @return bool
	 */
	public function compare_hashes( $file_path, $file_hash ) {
		if ( is_string( $file_hash ) ) {
			return $this->compare_hashes_on_different_os( $file_path, $file_hash );
		} elseif ( is_array( $file_hash ) ) {
			// Sometimes file has some hashes.
			foreach ( $file_hash as $hash_value ) {
				if ( $this->compare_hashes_on_different_os( $file_path, $hash_value ) ) {
					return true;
				}
			}

			return false;
		} else {
			return false;
		}
	}

	/**
	 * Hash a file in chunks.
	 *
	 * @param  string $file_path  Path to a file.
	 * @param  string $convert_to  Convert end of lines characters to linux or dos.
	 *
	 * @return bool|string
	 * @since 3.10.0
	 */
	protected function hash_file( string $file_path, string $convert_to = '' ) {
		global $wp_filesystem;
		// Initialize the WP filesystem, no more using 'file-put-contents' function.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		if ( ! file_exists( $file_path ) ) {
			return false;
		}

		$context = hash_init( 'md5' );
		$data    = $wp_filesystem->get_contents( $file_path );

		if ( 'linux' === $convert_to ) {
			$data = $this->convert_end_lines_dos_to_linux( $data );
		} elseif ( 'dos' === $convert_to ) {
			$data = $this->convert_end_lines_linux_to_dos( $data );
		}

		hash_update( $context, $data );

		return hash_final( $context, false );
	}
}
