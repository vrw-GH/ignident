<?php
namespace HTContactForm\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Dashboard_Widget_Api {

	/**
	 * Define necessary variables
	 */
	const REMOTE_BASE_URL = 'https://feed.hasthemes.com/notices/news-feed';
	// const REMOTE_BASE_URL = 'http://news-feed.test'; // local dev — swap back before release
	const ENDPOINT_FILE   = 'news-data.json';
	const TRANSIENT_KEY   = 'ht_contactform_news_feed_data';

	/**
	 * Get news feed data.
	 * Retrieve the banner + feed data from the HasThemes news-feed server.
	 *
	 * @param bool $force_update Optional. Whether to force the data update.
	 * @return array News Feed data ( 'banner' => [...], 'feed' => [...] ).
	 */
	public static function get_remote_data( $force_update = false ) {
		$cache_key = self::TRANSIENT_KEY;

		$info_data = get_transient( $cache_key );

		if ( $force_update || false === $info_data ) {
			$timeout = ( $force_update ) ? 25 : 8;

			$response = wp_remote_get( sprintf( '%s/%s', self::REMOTE_BASE_URL, self::ENDPOINT_FILE ), [
				'timeout' => $timeout,
			] );

			if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				set_transient( $cache_key, [], HOUR_IN_SECONDS );
				return [];
			}

			$info_data = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( empty( $info_data ) || ! is_array( $info_data ) ) {
				set_transient( $cache_key, [], HOUR_IN_SECONDS );
				return [];
			}

			set_transient( $cache_key, $info_data, 12 * HOUR_IN_SECONDS );
		}

		return empty( $info_data ) ? [] : $info_data;
	}

}
