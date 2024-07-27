<?php
/**
 * Handles IP and country-based access restrictions.
 *
 * @package WP_Defender\Component
 */

namespace WP_Defender\Component;

use PharData;
use WP_Defender\Component;
use WP_Defender\Traits\IP;
use Calotes\Helper\Array_Cache;
use WP_Defender\Traits\Country;
use WP_Defender\Model\Lockout_Log;
use MaxMind\Db\Reader\InvalidDatabaseException;
use WP_Defender\Integrations\MaxMind_Geolocation;
use WP_Defender\Model\Setting\Blacklist_Lockout as Model_Blacklist_Lockout;

/**
 * Handles operations related to IP and country-based blacklisting and whitelisting.
 */
class Blacklist_Lockout extends Component {

	use Country;
	use IP;

	/**
	 * Checks if a given IP is whitelisted based on the country.
	 *
	 * @param  string $ip  The IP address to check.
	 *
	 * @return bool True if the IP is whitelisted based on the country, false otherwise.
	 * @throws InvalidDatabaseException Thrown for unexpected data is found in DB.
	 */
	public function is_country_whitelist( $ip ): bool {
		// Check Firewall > IP Banning > Locations section is activated or not.
		$country = $this->get_current_country( $ip );
		if ( false === $country ) {
			return false;
		}
		$model     = new Model_Blacklist_Lockout();
		$whitelist = $model->get_country_whitelist();
		if ( empty( $whitelist ) ) {
			return false;
		}
		if ( ! empty( $country['iso'] ) && in_array( strtoupper( $country['iso'] ), $whitelist, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Return the default ips need to whitelisted, e.g. HUB ips.
	 *
	 * @return array
	 */
	private function get_default_ip_whitelisted(): array {
		$server_addr = defender_get_data_from_request( 'SERVER_ADDR', 's' );
		$remote_addr = defender_get_data_from_request( 'REMOTE_ADDR', 's' );
		$ips         = array(
			'18.204.159.253',
			'54.227.51.40',
			'3.93.131.0',
			'18.219.56.14',
			'45.55.78.242',
			'35.171.56.101',
			'192.241.140.159',
			'104.236.132.222',
			'192.241.148.185',
			'34.196.51.17',
			'35.157.144.199',
			'159.89.254.12',
			'18.219.161.157',
			'165.227.251.117',
			'165.227.251.120',
			'140.82.60.49',
			'45.63.10.140',
			...$this->get_blc_ip_whitelisted(),
			'127.0.0.1',
			isset( $server_addr ) ? $server_addr : $remote_addr,
		);

		return (array) apply_filters( 'ip_lockout_default_whitelist_ip', $ips );
	}

	/**
	 * Checks if an IP is whitelisted.
	 *
	 * @param  string $ip  The IP address to check.
	 *
	 * @return bool True if the IP is whitelisted, false otherwise.
	 */
	public function is_ip_whitelisted( $ip ): bool {
		if ( in_array( $ip, $this->get_default_ip_whitelisted(), true ) ) {
			return true;
		}

		$blacklist_settings = new Model_Blacklist_Lockout();

		return $this->is_ip_in_format( $ip, $blacklist_settings->get_list( 'allowlist' ) );
	}

	/**
	 * Checks if an IP is blacklisted.
	 *
	 * @param  string $ip  The IP address to check.
	 *
	 * @return bool True if the IP is blacklisted, false otherwise.
	 */
	public function is_blacklist( $ip ) {
		$blacklist_settings = new Model_Blacklist_Lockout();

		return $this->is_ip_in_format( $ip, $blacklist_settings->get_list( 'blocklist' ) );
	}

	/**
	 * Checks if a country is blacklisted based on the IP address.
	 *
	 * @param  string $ip  The IP address to check.
	 *
	 * @return bool True if the country is blacklisted, false otherwise.
	 * @throws InvalidDatabaseException Thrown for unexpected data is found in DB.
	 */
	public function is_country_blacklist( $ip ): bool {
		// Check Firewall > IP Banning > Locations section is activated or not.
		$country = $this->get_current_country( $ip );
		if ( false === $country ) {
			return false;
		}
		$blacklist_settings = new Model_Blacklist_Lockout();
		$blacklisted        = $blacklist_settings->get_country_blacklist();
		if ( empty( $blacklisted ) ) {
			return false;
		}
		if ( in_array( 'all', $blacklisted, true ) ) {
			return true;
		}
		if ( ! empty( $country['iso'] ) && in_array( strtoupper( $country['iso'] ), $blacklisted, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Verifies the format of an imported file for IP lockout settings.
	 *
	 * @param  string $file  Path to the file to verify.
	 *
	 * @return array|bool Array of data if the file is valid, false otherwise.
	 */
	public function verify_import_file( $file ) {
		global $wp_filesystem;
		// Initialize the WP filesystem, no more using 'file-put-contents' function.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
		$contents = $wp_filesystem->get_contents( $file );
		$lines    = explode( "\n", $contents );
		$data     = array();
		foreach ( $lines as $line ) {
			$line = str_getcsv( $line );
			if ( count( $line ) !== 2 ) {
				return false;
			}
			if ( ! in_array( $line[1], array( 'allowlist', 'blocklist' ), true ) ) {
				return false;
			}
			if ( false === $this->validate_ip( $line[0] ) ) {
				continue;
			}
			$data[] = $line;
		}
		return $data;
	}

	/**
	 * Adds a default whitelisted country to the model.
	 *
	 * @param  Model_Blacklist_Lockout $model  The model to update.
	 * @param  string                  $country_iso  The ISO code of the country to whitelist.
	 *
	 * @return Model_Blacklist_Lockout The updated model.
	 * @since 2.8.0
	 */
	public function add_default_whitelisted_country( Model_Blacklist_Lockout $model, $country_iso ) {
		if ( empty( $model->country_whitelist ) ) {
			$model->country_whitelist[] = $country_iso;
		} elseif ( ! in_array( $country_iso, $model->country_whitelist, true ) ) {
			$model->country_whitelist[] = $country_iso;
		}

		return $model;
	}

	/**
	 * Check downloaded GeoDB.
	 *
	 * @return bool
	 * @throws InvalidDatabaseException Thrown for unexpected data is found in DB.
	 */
	public function is_geodb_downloaded(): bool {
		$model = new Model_Blacklist_Lockout();
		// Likely the case after the config import with existed MaxMind license key.
		if (
			! empty( $model->maxmind_license_key )
			&& ( is_null( $model->geodb_path ) || ! is_file( $model->geodb_path ) )
		) {
			$service_geo = wd_di()->get( MaxMind_Geolocation::class );
			$tmp         = $service_geo->get_downloaded_url( $model->maxmind_license_key );
			if ( ! is_wp_error( $tmp ) ) {
				$phar = new PharData( $tmp );
				$path = $service_geo->get_db_base_path();
				if ( ! is_dir( $path ) ) {
					wp_mkdir_p( $path );
				}
				$phar->extractTo( $path, null, true );
				$model->geodb_path = $path . DIRECTORY_SEPARATOR . $phar->current()->getFileName() . DIRECTORY_SEPARATOR . $service_geo->get_db_full_name();
				// Save because we'll check for a saved path.
				$model->save();

				if ( file_exists( $tmp ) ) {
					wp_delete_file( $tmp );
				}

				if ( empty( $model->country_whitelist ) ) {
					$is_country = false;
					foreach ( $this->get_user_ip() as $ip ) {
						$country = $this->get_current_country( $ip );

						if ( ! empty( $country['iso'] ) ) {
							$model      = $this->add_default_whitelisted_country( $model, $country['iso'] );
							$is_country = true;
						}
					}

					if ( false === $is_country ) {
						return false;
					}
				}
				$model->save();
			}
		}

		// Check again.
		if ( is_null( $model->geodb_path ) || ! is_file( $model->geodb_path ) ) {
			return false;
		}

		// Check if the file exists on the site. The file can exist on the same server but for different sites.
		// For example, after config importing.
		$path_parts = pathinfo( $model->geodb_path );
		if ( preg_match( '/(\/wp-content\/.+)/', $path_parts['dirname'], $matches ) ) {
			$rel_path = $matches[1];
			$rel_path = ltrim( $rel_path, '/' );
			$abs_path = ABSPATH . $rel_path;
			if ( ! is_dir( $abs_path ) ) {
				wp_mkdir_p( $abs_path );
			}

			$rel_path = $abs_path . DIRECTORY_SEPARATOR . $path_parts['basename'];
			if ( file_exists( $rel_path ) ) {
				return true;
			} elseif ( ! empty( $model->geodb_path ) && file_exists( $model->geodb_path ) ) {
				// The case if ABSPATH was changed e.g. in wp-config.php.
				return true;
			}

			if ( move_uploaded_file( $model->geodb_path, $rel_path ) ) {
				$model->geodb_path = $rel_path;
				$model->save();
			} else {
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieves the top countries from which IPs have been blocked.
	 *
	 * @param  int $limit  The maximum number of countries to return.
	 * @param  int $max_age_days  The maximum age of log entries to consider.
	 *
	 * @return array List of countries and their blocked IP count.
	 */
	public function get_top_countries_blocked( $limit = 10, $max_age_days = 7 ) {
		$result = Array_Cache::get( 'countries', 'ip_lockout', array() );
		if ( empty( $result ) ) {
			global $wpdb;

			$result = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT country_iso_code, COUNT(ip) AS ip_count FROM {$wpdb->base_prefix}defender_lockout_log WHERE (type = %s OR type = %s OR type = %s) AND date >= %d AND country_iso_code IS NOT NULL GROUP BY country_iso_code ORDER BY ip_count DESC LIMIT %d",
					Lockout_Log::LOCKOUT_404,
					Lockout_Log::AUTH_LOCK,
					Lockout_Log::LOCKOUT_UA,
					strtotime( '-' . $max_age_days . ' days', time() ),
					$limit
				),
				ARRAY_A
			);
			// Get data from cache.
			Array_Cache::set( 'countries', $result, 'ip_lockout' );
		}

		return ! empty( $result ) ? $result : array();
	}

	/**
	 * Retrieves a list of IPs whitelisted by the Broken Link Checker plugin.
	 *
	 * @see   https://wpmudev.com/docs/wpmu-dev-plugins/broken-link-checker/#broken-link-checker-ip
	 * @return array List of whitelisted IPs.
	 * @since 4.2.0
	 */
	private function get_blc_ip_whitelisted(): array {
		return array(
			'165.227.127.103',
			'64.176.196.23',
			'144.202.86.106',
		);
	}

	/**
	 * Is IP on BLC Whitelist?
	 *
	 * @return bool
	 * @since 4.2.0
	 */
	public function is_blc_ip_whitelisted(): bool {
		$ips     = $this->get_user_ip();
		$blc_ips = $this->get_blc_ip_whitelisted();
		$diff    = array_diff( $ips, $blc_ips );

		return empty( $diff );
	}

	/**
	 * Checks if a list of IPs are all whitelisted.
	 *
	 * @param  array $ips  List of IPs to check.
	 *
	 * @return bool True if all IPs are whitelisted, false otherwise.
	 * @since 4.4.2
	 */
	public function are_ips_whitelisted( array $ips ): bool {
		foreach ( $ips as $ip ) {
			if ( ! $this->is_ip_whitelisted( $ip ) ) {
				return false;
			}
		}

		return true;
	}
}
