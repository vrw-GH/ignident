<?php
/**
 * The firewall logs class.
 *
 * @package WP_Defender\Component
 */

namespace WP_Defender\Component;

use WP_Defender\Component;
use WP_Defender\Model\Lockout_Log;
use WP_Defender\Model\Spam_Comment;

/**
 * Class Firewall_Logs
 */
class Firewall_Logs extends Component {

	/**
	 * Fetch compact Firewall logs.
	 *
	 * @param  int $from  Fetch Logs from this time to current time.
	 *
	 * @return array
	 */
	public function get_compact_logs( int $from ): array {
		global $wpdb;

		$table   = $wpdb->base_prefix . ( new Lockout_Log() )->get_table();
		$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT IP, type, COUNT(*) AS frequency FROM {$table}" . // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				" WHERE `date` >= %s AND type IN ('auth_fail', '404_error', 'ua_lockout')" .
				' GROUP BY IP, `type`',
				$from
			),
			ARRAY_A
		);

		$logs = array();
		if ( is_array( $results ) ) {
			foreach ( $results as $row ) {
				$ip = $row['IP'];
				if ( ! isset( $logs[ $ip ] ) ) {
					$logs[ $ip ] = array( 'ip' => $ip );
				}

				$type = '';
				switch ( $row['type'] ) {
					case 'auth_fail':
						$type = 'login';
						break;
					case '404_error':
						$type = 'not_found';
						break;
					case 'ua_lockout':
						$type = 'user_agent';
						break;
					default:
						continue 2;
				}

				$logs[ $ip ]['reason'][ $type ] = (int) $row['frequency'];
			}
		}

		// Add spam comments IP to the compact log.
		$spam_comments_ip = Spam_Comment::get_spam_comments_ip();
		$this->log( $spam_comments_ip, 'spam-comment.log' );

		if ( is_array( $spam_comments_ip ) && ! empty( $spam_comments_ip ) ) {
			foreach ( $spam_comments_ip as $ip => $count ) {
				if ( ! isset( $logs[ $ip ] ) ) {
					$logs[ $ip ] = array( 'ip' => $ip );
				}

				$logs[ $ip ]['reason']['spam_comment'] = $count;
			}
		}

		return array_values( $logs );
	}
}
