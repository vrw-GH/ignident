<?php
/**
 * Helper functions for formatting data.
 *
 * @package WP_Defender\Traits
 */

namespace WP_Defender\Traits;

use DateTime;
use Exception;
use DateTimeZone;

trait Formats {

	/**
	 * Convert a unix timestamp into the blog datetime.
	 *
	 * @param  mixed $timestamp  Timestamp to convert.
	 * @param  bool  $i18n  Should return date in localized format.
	 *
	 * @return bool|string
	 */
	public function format_date_time( $timestamp, $i18n = true ) {
		if ( ! filter_var( $timestamp, FILTER_VALIDATE_INT ) ) {
			$timestamp = strtotime( $timestamp );
		}
		if ( false === $timestamp ) {
			return 'n/a';
		}
		$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		if ( false === $i18n ) {
			return gmdate( $format, $timestamp );
		}
		$time = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $timestamp ) );

		return date_i18n( $format, strtotime( $time ) );
	}

	/**
	 * Persistent date & time formats for Hub data.
	 *
	 * @param  int $timestamp  Unix timestamp that defaults to the current local time.
	 *
	 * @return string|bool
	 */
	public function persistent_hub_datetime_format( $timestamp ) {
		return gmdate( 'Y-m-d g:i a', $timestamp );
	}

	/**
	 * Formats a given number of bytes into a human-readable string representation.
	 *
	 * @param  int $bytes  The number of bytes to format.
	 *
	 * @return string The formatted string representation of the number of bytes.
	 */
	public function format_bytes_into_readable( $bytes ): string {
		if ( $bytes >= 1073741824 ) {
			$bytes = number_format( $bytes / 1073741824, 2 ) . ' GB';
		} elseif ( $bytes >= 1048576 ) {
			$bytes = number_format( $bytes / 1048576, 2 ) . ' MB';
		} elseif ( $bytes >= 1024 ) {
			$bytes = number_format( $bytes / 1024, 2 ) . ' KB';
		} elseif ( $bytes > 1 ) {
			$bytes .= ' bytes';
		} elseif ( 1 === $bytes ) {
			$bytes .= ' byte';
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

	/**
	 * Calculates the time elapsed since a given timestamp and returns a human-readable string representation.
	 *
	 * @param  int $since  The timestamp to calculate the time elapsed from.
	 *
	 * @return string The human-readable string representation of the time elapsed.
	 */
	public function time_since( $since ): string {
		$since = time() - $since;
		if ( $since < 0 ) {
			$since = 0;
		}
		$chunks = array(
			array( 60 * 60 * 24 * 365, esc_html__( 'year', 'defender-security' ) ),
			array( 60 * 60 * 24 * 30, esc_html__( 'month', 'defender-security' ) ),
			array( 60 * 60 * 24 * 7, esc_html__( 'week', 'defender-security' ) ),
			array( 60 * 60 * 24, esc_html__( 'day', 'defender-security' ) ),
			array( 60 * 60, esc_html__( 'hour', 'defender-security' ) ),
			array( 60, esc_html__( 'minute', 'defender-security' ) ),
			array( 1, esc_html__( 'second', 'defender-security' ) ),
		);

		for ( $i = 0, $j = count( $chunks ); $i < $j; $i++ ) {
			$seconds = $chunks[ $i ][0];
			$name    = $chunks[ $i ][1];
			$count   = floor( $since / $seconds );
			if ( 0 !== $count ) {
				break;
			}
		}

		$print = ( 1 === $count ) ? '1 ' . $name : "$count {$name}s";

		return $print;
	}

	/**
	 * Get formatted date.
	 *
	 * @param  int $date  Date timestamp.
	 *
	 * @return false|string
	 */
	public function get_date( $date ) {
		if ( strtotime( '-24 hours' ) > $date ) {
			return $this->format_date_time( wp_date( 'Y-m-d H:i:s', $date ) );
		} else {
			return human_time_diff( $date, time() ) . ' ' . esc_html__( 'ago', 'defender-security' );
		}
	}

	/**
	 * Return times frame for selectbox.
	 *
	 * @return array
	 */
	public function get_times() {
		$times_interval = (array) apply_filters( 'defender_get_times_interval', array( '00', '30' ) );
		$data           = array();
		for ( $i = 0; $i < 24; $i++ ) {
			foreach ( $times_interval as $min ) {
				$time          = $i . ':' . $min;
				$data[ $time ] = date_i18n( 'h:i A', strtotime( $time ) );
			}
		}

		return $data;
	}

	/**
	 * Converts a local timestamp to UTC.
	 *
	 * @param  string $timestring  The local timestamp to convert.
	 *
	 * @return false|int The UTC timestamp, or false on failure.
	 * @throws Exception If an error occurs during the conversion.
	 */
	public function local_to_utc( $timestring ) {
		$tz = get_option( 'timezone_string' );
		if ( ! $tz ) {
			$gmt_offset = get_option( 'gmt_offset' );
			if ( 0 === $gmt_offset ) {
				return strtotime( $timestring );
			}
			$tz = $this->get_timezone_string( $gmt_offset );
		}
		if ( ! $tz ) {
			$tz = 'UTC';
		}
		$timezone = new DateTimeZone( $tz );
		$time     = new DateTime( $timestring, $timezone );

		return $time->getTimestamp();
	}

	/**
	 * Returns the timezone string based on the given offset.
	 *
	 * @param  string $timezone  The timezone offset in the format '+/-HH.MM'.
	 *
	 * @return false|string The timezone string or false if it cannot be determined.
	 */
	public function get_timezone_string( $timezone ) {
		$timezone = explode( '.', $timezone );
		if ( isset( $timezone[1] ) ) {
			$timezone[1] = 30;
		} else {
			$timezone[1] = '00';
		}
		$offset = implode( ':', $timezone );

		[ $hours, $minutes ] = explode( ':', $offset );
		$seconds             = $hours * 60 * 60 + $minutes * 60;
		$lc                  = localtime( time(), true );
		if ( isset( $lc['tm_isdst'] ) ) {
			$isdst = $lc['tm_isdst'];
		} else {
			$isdst = 0;
		}
		$tz = timezone_name_from_abbr( '', $seconds, $isdst );
		if ( false === $tz ) {
			$tz = timezone_name_from_abbr( '', $seconds, 0 );
		}

		return $tz;
	}

	/**
	 * Get days of week.
	 *
	 * @return array
	 */
	public function get_days_of_week() {
		$timestamp = strtotime( 'next Sunday' );
		$days      = array();
		for ( $i = 0; $i < 7; $i++ ) {
			$days[ strtolower( gmdate( 'l', $timestamp ) ) ] = date_i18n( 'l', $timestamp );
			$timestamp                                       = strtotime( '+1 day', $timestamp );
		}

		return $days;
	}

	/**
	 * Translates the datetime format from PHP to something that moment.js can understand.
	 *
	 * @param  string $format  The PHP datetime format to be translated.
	 *
	 * @return string The translated datetime format for moment.js.
	 */
	public function moment_datetime_format_from( $format ) {
		$replacements = array(
			'd' => 'DD',
			'D' => 'ddd',
			'j' => 'D',
			'l' => 'dddd',
			'N' => 'E',
			'S' => 'o',
			'w' => 'e',
			'z' => 'DDD',
			'W' => 'W',
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'MMM',
			'n' => 'M',
			't' => '', // no equivalent.
			'L' => '', // no equivalent.
			'o' => 'YYYY',
			'Y' => 'YYYY',
			'y' => 'YY',
			'a' => 'a',
			'A' => 'A',
			'B' => '', // no equivalent.
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => 'SSS',
			'e' => 'zz', // deprecated since version 1.6.0 of moment.js .
			'I' => '', // no equivalent.
			'O' => '', // no equivalent.
			'P' => '', // no equivalent.
			'T' => '', // no equivalent.
			'Z' => '', // no equivalent.
			'c' => '', // no equivalent.
			'r' => '', // no equivalent.
			'U' => 'X',
		);

		return strtr( $format, $replacements );
	}

	/**
	 * Calculates the date interval based on the given date.
	 *
	 * @param  string $date  The date to calculate the interval for. Can be '24 hours', '7 days', '30 days',
	 *   '3 months', '6 months' or '12 months'.
	 *
	 * @return string The date interval in ISO 8601 format. Returns an empty string if the given date is not
	 *     recognized.
	 */
	public function calculate_date_interval( $date ): string {
		$interval = '';
		if ( '24 hours' === $date ) {
			$interval = 'P1D';
		} elseif ( '7 days' === $date ) {
			$interval = 'P7D';
		} elseif ( '30 days' === $date ) {
			$interval = 'P30D';
		} elseif ( '3 months' === $date ) {
			$interval = 'P3M';
		} elseif ( '6 months' === $date ) {
			$interval = 'P6M';
		} elseif ( '12 months' === $date ) {
			$interval = 'P12M';
		}

		return $interval;
	}

	/**
	 * Returns a human-readable date string in the local timezone, based on the given timestamp.
	 *
	 * @param  int $timestamp  The timestamp to convert to a local human-readable date string.
	 *
	 * @return string The local human-readable date string in the format 'Day, Date of Month Year at Hour:Minute:Second
	 *     AM/PM'.
	 */
	public function get_local_human_date( int $timestamp ): string {
		return wp_date( 'l, jS \of F, Y \a\t h:i:s A', $timestamp + ( 3600 * get_option( 'gmt_offset' ) ) );
	}
}
