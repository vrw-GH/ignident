<?php
/**
 * Fetches the "What's new" list from news.kriesi.at and renders it in the backend.
 *
 * The same request reports the install (domain, theme version, WP version) - see
 * the disclosure in the documentation. Users can opt out completely with
 *
 *		add_theme_support( 'avia_no_news' );
 *
 * Nothing here runs in frontend and nothing blocks a page load: the request is made
 * on 'shutdown', after the response has been sent to the browser.
 *
 * @since 8.0
 */
namespace enfold\news;

if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( __NAMESPACE__ . '\aviaNews', false ) )
{
	class aviaNews
	{
		/**
		 * Option holding the fetched list and the timer. Site option on multisite,
		 * so a network fetches once.
		 */
		const OPTION = '_av_enfold_news';

		/**
		 * User meta holding the id of the newest item the user has seen
		 */
		const USER_META = '_av_enfold_news_read';

		const NONCE = 'avia_news';

		/**
		 * Highest payload schema this version understands
		 */
		const SCHEMA = 1;

		/**
		 * Never render more than this, whatever the feed contains
		 */
		const MAX_ITEMS = 5;

		/**
		 * How many items are kept in the option. Higher than MAX_ITEMS because scheduled
		 * items are stored long before they are shown - without the headroom a couple of
		 * future entries would push the visible ones out of the list.
		 */
		const MAX_STORED = 12;

		/**
		 * Once every item has been read and the newest one is older than this many days,
		 * the trigger disappears until something newer arrives.
		 */
		const STALE_DAYS = 45;

		/**
		 * @since 8.0
		 * @var \enfold\news\aviaNews
		 */
		static private $_instance = null;

		/**
		 * Cached for the request
		 *
		 * @since 8.0
		 * @var array|null
		 */
		private $state = null;

		/**
		 * @since 8.0
		 * @return \enfold\news\aviaNews
		 */
		static public function instance()
		{
			if( is_null( aviaNews::$_instance ) )
			{
				aviaNews::$_instance = new aviaNews();
			}

			return aviaNews::$_instance;
		}

		/**
		 * Hooks are registered unconditionally and each handler checks the theme support.
		 * A check at include time would miss child themes that call add_theme_support()
		 * in an 'after_setup_theme' callback.
		 *
		 * @since 8.0
		 */
		protected function __construct()
		{
			add_action( 'admin_init', array( $this, 'handler_admin_init' ) );
			/*
			 *  Must be registered before framework/avia_framework.php is included - the
			 *  framework builds the option arrays while it loads, not on a hook, so this
			 *  filter has already fired by admin_init. That is why functions.php requires
			 *  this file above the framework include.
			 */
			add_filter( 'avf_option_page_data_init', array( $this, 'handler_option_page_data' ), 20, 1 );
			add_action( 'admin_bar_menu', array( $this, 'handler_admin_bar_menu' ), 100 );
			add_filter( 'avia_options_page_header', array( $this, 'handler_options_page_header' ), 10, 1 );
			add_action( 'admin_footer', array( $this, 'handler_admin_footer' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'handler_admin_enqueue_scripts' ) );
			add_filter( 'admin_body_class', array( $this, 'handler_admin_body_class' ), 10, 1 );
			add_action( 'wp_ajax_avia_news_mark_read', array( $this, 'handler_ajax_mark_read' ) );
		}

		/**
		 * @since 8.0
		 */
		public function __destruct()
		{
			unset( $this->state );
		}


		/*	=====================================================================
			state
			===================================================================== */

		/**
		 * @since 8.0
		 * @return boolean
		 */
		public function is_enabled()
		{
			if( current_theme_supports( 'avia_no_news' ) )
			{
				return false;
			}

			/**
			 * Allows to switch off the news panel and the request it makes
			 *
			 * @since 8.0
			 * @param boolean $enabled
			 * @return boolean
			 */
			return true === apply_filters( 'avf_news_enabled', true );
		}

		/**
		 * @since 8.0
		 * @return array
		 */
		protected function get_state()
		{
			if( is_null( $this->state ) )
			{
				$state = is_multisite() ? get_site_option( aviaNews::OPTION, array() ) : get_option( aviaNews::OPTION, array() );
				$this->state = is_array( $state ) ? $state : array();
			}

			return $this->state;
		}

		/**
		 * @since 8.0
		 * @param array $state
		 */
		protected function set_state( array $state )
		{
			$this->state = $state;

			if( is_multisite() )
			{
				update_site_option( aviaNews::OPTION, $state );
			}
			else
			{
				//	not autoloaded - only read on admin screens
				update_option( aviaNews::OPTION, $state, false );
			}
		}

		/**
		 * @since 8.0
		 */
		protected function delete_state()
		{
			$this->state = array();

			if( is_multisite() )
			{
				delete_site_option( aviaNews::OPTION );
			}
			else
			{
				delete_option( aviaNews::OPTION );
			}
		}

		/**
		 * Sanitised, render ready items - the ones that are due.
		 *
		 * An item dated in the future is fetched and stored like any other but stays out
		 * of this list until its date passes. That is what lets an announcement be put
		 * in the feed weeks ahead: every install has already downloaded it during a
		 * normal recheck, and it appears on all of them at the same moment, with no
		 * request at the time it goes live.
		 *
		 * Schedule further out than the longest recheck interval (8 days) so slow
		 * installs have it in hand before the date arrives.
		 *
		 * @since 8.0
		 * @return array
		 */
		public function get_items()
		{
			if( ! $this->is_enabled() )
			{
				return array();
			}

			$state = $this->get_state();
			$stored = ! empty( $state['items'] ) && is_array( $state['items'] ) ? $state['items'] : array();

			$now = time();
			$items = array();

			foreach( $stored as $item )
			{
				//	dateless items are always due - see clean_date()
				if( ! empty( $item['date'] ) && (int) $item['date'] > $now )
				{
					continue;
				}

				$items[] = $item;

				if( count( $items ) >= aviaNews::MAX_ITEMS )
				{
					break;
				}
			}

			return $items;
		}

		/**
		 * @since 8.0
		 * @return boolean
		 */
		public function has_items()
		{
			return ! empty( $this->get_items() );
		}

		/**
		 * Number of items the current user has not seen yet. An unknown read marker
		 * (e.g. the item was retired) counts everything as unread.
		 *
		 * @since 8.0
		 * @return int
		 */
		public function unread_count()
		{
			$items = $this->get_items();

			if( empty( $items ) )
			{
				return 0;
			}

			$read = get_user_meta( get_current_user_id(), aviaNews::USER_META, true );

			if( ! is_string( $read ) || '' === $read )
			{
				return count( $items );
			}

			$count = 0;

			foreach( $items as $item )
			{
				if( $item['id'] === $read )
				{
					break;
				}

				$count ++;
			}

			return $count;
		}


		/*	=====================================================================
			fetching
			===================================================================== */

		/**
		 * Decides if a request is needed and schedules it for 'shutdown'.
		 *
		 * The timer is advanced BEFORE the request is made. That way a failed, hanging
		 * or killed request can never produce a retry loop - the site simply tries again
		 * at the next interval - and no lock is needed.
		 *
		 * @since 8.0
		 */
		public function handler_admin_init()
		{
			if( ! $this->is_enabled() )
			{
				$this->cleanup();
				return;
			}

			if( wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) )
			{
				return;
			}

			if( defined( 'REST_REQUEST' ) && REST_REQUEST )
			{
				return;
			}

			if( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
			{
				return;
			}

			$state = $this->get_state();

			/**
			 * First contact with this install: do not fetch now. Spreading the very first
			 * request across 48 hours decoheres the install base permanently, so there is
			 * never a release day or monday morning wave.
			 */
			if( empty( $state['next_check'] ) )
			{
				$state['next_check'] = time() + wp_rand( 0, 48 * HOUR_IN_SECONDS );

				/*
				 *  Seed the list from the copy shipped with this release, so the panel has
				 *  something to show during those first hours instead of being invisible.
				 *  The first fetch replaces it with whatever the feed says by then.
				 *
				 *  This is display only. The install report still happens on the timer
				 *  above - seeding cannot report anything, it makes no request.
				 */
				$state['items'] = $this->seed_items();
				$state['schema'] = aviaNews::SCHEMA;

				$this->set_state( $state );
				return;
			}

			if( $state['next_check'] > time() )
			{
				return;
			}

			$state['next_check'] = $this->next_check_time();
			$this->set_state( $state );

			add_action( 'shutdown', array( $this, 'handler_shutdown' ), 1 );
		}

		/**
		 * The news list shipped with the theme, used until the first real fetch lands.
		 *
		 * Runs through the same sanitiser as a fetched payload, so it gets the same
		 * treatment - no html, the host allowlist, the date rules - and a broken seed
		 * file can only ever result in an empty list, never in a broken panel.
		 *
		 * @since 8.0
		 * @return array
		 */
		protected function seed_items()
		{
			$file = trailingslashit( dirname( __FILE__ ) ) . 'news/seed.json';

			if( ! is_readable( $file ) )
			{
				return array();
			}

			$raw = @file_get_contents( $file );

			if( ! is_string( $raw ) || '' === trim( $raw ) )
			{
				return array();
			}

			$data = json_decode( $raw, true );

			if( ! is_array( $data ) )
			{
				return array();
			}

			$schema = isset( $data['schema'] ) ? (int) $data['schema'] : 0;

			if( $schema < 1 || $schema > aviaNews::SCHEMA )
			{
				return array();
			}

			return $this->sanitize_items( $data );
		}

		/**
		 * @since 8.0
		 */
		public function handler_shutdown()
		{
			//	hand the response to the browser first - nobody waits for kriesi.at
			if( function_exists( 'fastcgi_finish_request' ) )
			{
				@fastcgi_finish_request();
			}

			try
			{
				$this->fetch();
			}
			catch( \Throwable $e )
			{
				//	runs after the response - a fatal here would be invisible but still fatal
			}
		}

		/**
		 * @since 8.0
		 * @return string
		 */
		protected function endpoint()
		{
			/**
			 * Allows to point the news request to another server - needed for testing
			 *
			 * @since 8.0
			 * @param string $endpoint
			 * @return string
			 */
			return apply_filters( 'avf_news_endpoint', 'https://news.kriesi.at/v1/' );
		}

		/**
		 * The install report. Contains no personal data: the domain is public anyway,
		 * the license flag is a boolean - the Envato token itself is never sent.
		 *
		 * @since 8.0
		 * @return array
		 */
		protected function request_args()
		{
			$home = is_multisite() ? network_home_url() : home_url();
			$host = wp_parse_url( $home, PHP_URL_HOST );
			$host = is_string( $host ) ? strtolower( rtrim( $host, '.' ) ) : '';

			$theme = wp_get_theme( get_template() );

			$token = trim( (string) avia_get_option( 'updates_envato_token' ) );
			$state = trim( (string) avia_get_option( 'updates_envato_token_state' ) );

			return array(
						'd'		=> $host,
						'v'		=> $theme->get( 'Version' ),
						'wp'	=> get_bloginfo( 'version' ),
						'ms'	=> is_multisite() ? 1 : 0,
						'lic'	=> ( '' !== $token && '' !== $state ) ? 1 : 0,
						'k'		=> aviaNews::SCHEMA
					);
		}

		/**
		 * @since 8.0
		 */
		protected function fetch()
		{
			$state = $this->get_state();

			$args = array(
						'timeout'		=> apply_filters( 'avf_news_http_timeout', 5 ),
						'redirection'	=> 2,
						'sslverify'		=> true,
						'headers'		=> array( 'Accept' => 'application/json' )
					);

			if( ! empty( $state['etag'] ) && is_string( $state['etag'] ) )
			{
				$args['headers']['If-None-Match'] = $state['etag'];
			}

			$response = wp_remote_get( add_query_arg( $this->request_args(), $this->endpoint() ), $args );

			if( is_wp_error( $response ) )
			{
				return;
			}

			$code = (int) wp_remote_retrieve_response_code( $response );

			//	nothing changed - the timer was advanced already
			if( 304 === $code )
			{
				$state['fetched_time'] = time();
				$this->set_state( $state );
				return;
			}

			if( 200 !== $code )
			{
				return;
			}

			$body = wp_remote_retrieve_body( $response );

			if( ! is_string( $body ) || '' === $body || strlen( $body ) > 64 * KB_IN_BYTES )
			{
				return;
			}

			$data = json_decode( $body, true );

			if( ! is_array( $data ) )
			{
				//	most likely a captive portal or a firewall challenge page
				return;
			}

			$schema = isset( $data['schema'] ) ? (int) $data['schema'] : 0;

			$state['fetched_time'] = time();
			$state['schema'] = $schema;
			$state['etag'] = (string) wp_remote_retrieve_header( $response, 'etag' );
			$state['next_check'] = $this->next_check_time( $data );

			/**
			 * A payload we are too old to understand is not an error - we simply show nothing
			 * and check again later. Retrying immediately would loop forever.
			 */
			$state['items'] = ( $schema >= 1 && $schema <= aviaNews::SCHEMA ) ? $this->sanitize_items( $data ) : array();

			$this->set_state( $state );
		}

		/**
		 * @since 8.0
		 * @param array $data
		 * @return int
		 */
		protected function next_check_time( array $data = array() )
		{
			$ttl = isset( $data['recheck_ttl'] ) ? (int) $data['recheck_ttl'] : 6 * DAY_IN_SECONDS;
			$jitter = isset( $data['recheck_jitter'] ) ? (int) $data['recheck_jitter'] : 2 * DAY_IN_SECONDS;

			//	the server may widen the interval, but never outside these bounds
			$ttl = min( max( $ttl, 2 * DAY_IN_SECONDS ), 30 * DAY_IN_SECONDS );
			$jitter = min( max( $jitter, 0 ), 7 * DAY_IN_SECONDS );

			/**
			 * @since 8.0
			 * @param int $ttl
			 * @return int
			 */
			$ttl = (int) apply_filters( 'avf_news_recheck_ttl', $ttl );

			return time() + $ttl + wp_rand( 0, $jitter );
		}


		/*	=====================================================================
			sanitising - no html is ever accepted, everything is escaped here once
			===================================================================== */

		/**
		 * @since 8.0
		 * @param array $data
		 * @return array
		 */
		protected function sanitize_items( array $data )
		{
			$raw = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();
			$items = array();

			foreach( $raw as $entry )
			{
				if( count( $items ) >= aviaNews::MAX_STORED )
				{
					break;
				}

				$item = $this->sanitize_item( $entry );

				if( ! empty( $item ) )
				{
					$items[] = $item;
				}
			}

			return $items;
		}

		/**
		 * @since 8.0
		 * @param mixed $entry
		 * @return array				empty array if the item is not usable
		 */
		protected function sanitize_item( $entry )
		{
			if( ! is_array( $entry ) )
			{
				return array();
			}

			$id = isset( $entry['id'] ) && is_scalar( $entry['id'] ) ? (string) $entry['id'] : '';

			if( ! preg_match( '/^[a-z0-9._-]{1,64}$/iD', $id ) )
			{
				return array();
			}

			$headline = $this->clean_text( $entry, 'headline', 120 );
			$link = $this->clean_link( isset( $entry['link_url'] ) ? $entry['link_url'] : '' );

			//	an item without a headline or with a link we do not trust is dropped
			if( '' === $headline || ( '' === $link['url'] && '' === $link['path'] ) )
			{
				return array();
			}

			$image = $this->clean_image( $entry, 'image' );
			$thumb = $this->clean_image( $entry, 'thumb' );
			$date = $this->clean_date( $entry );

			//	a supplied but rejected image or date means the payload cannot be trusted
			if( is_null( $image ) || is_null( $thumb ) || is_null( $date ) )
			{
				return array();
			}

			return array(
						'id'			=> $id,
						'headline'		=> $headline,
						'teaser'		=> $this->clean_text( $entry, 'teaser', 60 ),
						'description'	=> $this->clean_text( $entry, 'description', 400 ),
						'category'		=> $this->clean_text( $entry, 'category', 24 ),
						'date'			=> $date,
						'link_label'	=> $this->clean_text( $entry, 'link_label', 40 ),
						'link_url'		=> $link['url'],
						'link_path'		=> $link['path'],
						'link_scope'	=> $link['scope'],
						'image'			=> $image,
						'thumb'			=> $thumb
					);
		}

		/**
		 * Three outcomes, and the difference matters since 0 now means "show it now":
		 *
		 *		no date supplied	->	0, undated and always due
		 *		usable timestamp	->	the timestamp, future dates included
		 *		supplied but wrong	->	null, and the caller drops the whole item
		 *
		 * Falling back to 0 for a broken value would be the worst of the three: a date
		 * in milliseconds, or any other typo, would publish the item immediately instead
		 * of on the day it was scheduled for.
		 *
		 * @since 8.0
		 * @param array $entry
		 * @return int|null					null when the item should be dropped
		 */
		protected function clean_date( array $entry )
		{
			if( ! isset( $entry['date'] ) || '' === $entry['date'] )
			{
				return 0;
			}

			$date = $entry['date'];

			//	numeric first, so a timestamp still works whether or not it is quoted
			if( is_numeric( $date ) )
			{
				$date = (int) $date;
			}
			else if( is_string( $date ) )
			{
				$date = $this->parse_date( trim( $date ) );
			}
			else
			{
				$date = null;
			}

			if( is_null( $date ) )
			{
				return null;
			}

			//	2000-01-01 .. one year out - scheduling ahead is the point, 20000 AD is a typo
			return ( $date > 946684800 && $date < time() + YEAR_IN_SECONDS ) ? $date : null;
		}

		/**
		 * "2026-08-06" or "2026-08-06 14:00" or "2026-08-06 14:00:00", always read as UTC.
		 *
		 * UTC and not the site timezone on purpose: a scheduled item has to become visible
		 * at the same moment everywhere, and a date read in local time would go live at
		 * different points around the world - and twice a year, at a different local hour
		 * as well.
		 *
		 * strtotime() is deliberately not used. It would happily accept "next tuesday",
		 * "+1 week" and any number of near misses, all of which mean something different
		 * on the day the feed is written than on the day it is read.
		 *
		 * @since 8.0
		 * @param string $value
		 * @return int|null					null when it is not a date we recognise
		 */
		protected function parse_date( $value )
		{
			$utc = new \DateTimeZone( 'UTC' );

			foreach( array( 'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d' ) as $format )
			{
				//	'!' resets the fields the format does not carry, so a bare date is midnight
				$date = \DateTimeImmutable::createFromFormat( '!' . $format, $value, $utc );

				if( ! $date instanceof \DateTimeImmutable )
				{
					continue;
				}

				$errors = \DateTimeImmutable::getLastErrors();

				//	php 8.2 returns false instead of an empty array when nothing went wrong
				if( is_array( $errors ) && ( ! empty( $errors['warning_count'] ) || ! empty( $errors['error_count'] ) ) )
				{
					continue;
				}

				return $date->getTimestamp();
			}

			return null;
		}

		/**
		 * Escapes once, here. Templates output the result as is - escaping again
		 * would double encode.
		 *
		 * @since 8.0
		 * @param array $entry
		 * @param string $key
		 * @param int $max
		 * @return string
		 */
		protected function clean_text( array $entry, $key, $max )
		{
			if( ! isset( $entry[ $key ] ) || ! is_scalar( $entry[ $key ] ) )
			{
				return '';
			}

			$text = trim( (string) $entry[ $key ] );

			if( '' === $text )
			{
				return '';
			}

			if( function_exists( 'mb_strlen' ) && mb_strlen( $text ) > $max )
			{
				$text = trim( mb_substr( $text, 0, $max - 1 ) ) . '…';
			}
			else if( ! function_exists( 'mb_strlen' ) && strlen( $text ) > $max )
			{
				$text = trim( substr( $text, 0, $max - 1 ) ) . '…';
			}

			return esc_html( $text );
		}

		/**
		 * @since 8.0
		 * @return array
		 */
		protected function allowed_hosts()
		{
			$hosts = array(
						'news.kriesi.at',
						'kriesi.at',
						'www.kriesi.at',
						'omnalingo.com',
						'www.omnalingo.com'
					);

			/**
			 * Exact hostnames only - a wildcard would trust every subdomain
			 *
			 * @since 8.0
			 * @param array $hosts
			 * @return array
			 */
			return (array) apply_filters( 'avf_news_url_hosts', $hosts );
		}

		/**
		 * A news item may point at the customers own install instead of at kriesi.at -
		 * "the new demos are in your theme options" is only useful as a link that lands
		 * there. Two forms are understood:
		 *
		 *		admin:admin.php?page=avia#goto_demo		->	admin_url()
		 *		/shop/									->	home_url()
		 *
		 * The target stays RELATIVE in the stored item and is resolved when it is
		 * rendered. It cannot be resolved at ingest: on multisite the fetched list is a
		 * network option shared by every subsite, while admin_url() differs per blog -
		 * baking in the main site's url would send every subsite admin to the wrong one.
		 *
		 * Anything else falls through to the host allowlist, unchanged.
		 *
		 * @since 8.0
		 * @param mixed $url
		 * @return array				scope 'external'|'admin'|'home', url, path
		 */
		protected function clean_link( $url )
		{
			$none = array( 'scope' => 'external', 'url' => '', 'path' => '' );

			if( ! is_string( $url ) || '' === trim( $url ) )
			{
				return $none;
			}

			$url = trim( $url );

			if( 0 === stripos( $url, 'admin:' ) )
			{
				$path = $this->clean_internal_path( substr( $url, 6 ) );

				return '' === $path ? $none : array( 'scope' => 'admin', 'url' => '', 'path' => $path );
			}

			//	a single leading slash - '//host' is protocol relative and goes nowhere near this
			if( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) )
			{
				$path = $this->clean_internal_path( ltrim( $url, '/' ) );

				return '' === $path ? $none : array( 'scope' => 'home', 'url' => '', 'path' => $path );
			}

			$external = $this->clean_url( $url );

			return '' === $external ? $none : array( 'scope' => 'external', 'url' => $external, 'path' => '' );
		}

		/**
		 * Deliberately narrow. A relative target is appended to this site's own url, so
		 * the only thing that matters is that it cannot carry a scheme, escape upwards
		 * or turn into a host of its own. No colon at all, so 'javascript:' and friends
		 * cannot survive; encode one as %3A if a query value ever needs it.
		 *
		 * @since 8.0
		 * @param string $path
		 * @return string				empty string if it is not acceptable
		 */
		protected function clean_internal_path( $path )
		{
			$path = trim( (string) $path );

			if( '' === $path || false !== strpos( $path, '..' ) )
			{
				return '';
			}

			if( ! preg_match( '#^[a-z0-9\-._~/]*(\?[a-z0-9\-._~/=&%+,]*)?(\#[a-z0-9\-._~]*)?$#iD', $path ) )
			{
				return '';
			}

			return $path;
		}

		/**
		 * @since 8.0
		 * @param mixed $url
		 * @return string				empty string if the url is not acceptable
		 */
		protected function clean_url( $url )
		{
			if( ! is_string( $url ) || '' === trim( $url ) )
			{
				return '';
			}

			$url = trim( $url );

			if( ! wp_http_validate_url( $url ) )
			{
				return '';
			}

			$parts = wp_parse_url( $url );

			if( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) )
			{
				return '';
			}

			if( 'https' !== strtolower( $parts['scheme'] ) )
			{
				return '';
			}

			//	no credentials in the url
			if( ! empty( $parts['user'] ) || ! empty( $parts['pass'] ) )
			{
				return '';
			}

			$host = strtolower( rtrim( $parts['host'], '.' ) );

			if( ! in_array( $host, $this->allowed_hosts(), true ) )
			{
				return '';
			}

			return esc_url_raw( $url, array( 'https' ) );
		}

		/**
		 * @since 8.0
		 * @param array $entry
		 * @param string $key
		 * @return string|null			'' if not supplied, null if supplied but rejected
		 */
		protected function clean_image( array $entry, $key )
		{
			if( ! isset( $entry[ $key ] ) || ! is_string( $entry[ $key ] ) || '' === trim( $entry[ $key ] ) )
			{
				return '';
			}

			$url = $this->clean_url( $entry[ $key ] );

			return '' === $url ? null : $url;
		}


		/*	=====================================================================
			output
			===================================================================== */

		/**
		 * @since 8.0
		 * @return boolean
		 */
		protected function can_display()
		{
			if( ! $this->is_enabled() || ! is_admin() || ! current_user_can( 'manage_options' ) || ! $this->has_items() )
			{
				return false;
			}

			/**
			 * Read everything and nothing new for weeks: stand down completely rather
			 * than keep a permanent button in the backend for news the user has already
			 * seen. Anything unread brings it straight back, and so does a fresh item.
			 *
			 * Both halves matter - unread is per user, so one admin having read the list
			 * never hides it from another.
			 */
			return $this->unread_count() > 0 || ! $this->is_stale();
		}

		/**
		 * True when the newest item is older than the stale window.
		 *
		 * Items without a usable date are NOT counted as stale - a feed that forgot its
		 * dates should look wrong, not disappear silently.
		 *
		 * @since 8.0
		 * @return boolean
		 */
		protected function is_stale()
		{
			$newest = 0;

			//	not assuming the feed is sorted - the lead item is a position, not a date
			foreach( $this->get_items() as $item )
			{
				$newest = max( $newest, (int) $item['date'] );
			}

			if( $newest <= 0 )
			{
				return false;
			}

			/**
			 * Days after which a fully read list stops showing a trigger
			 *
			 * @since 8.0
			 * @param int $days
			 * @return int
			 */
			$days = (int) apply_filters( 'avf_news_stale_days', aviaNews::STALE_DAYS );

			return $days > 0 && $newest < time() - $days * DAY_IN_SECONDS;
		}

		/**
		 * The two options are display switches, one per surface. Unset means on, so an
		 * install that has never opened the tab behaves like a fresh one.
		 *
		 * @since 8.0
		 * @param string $surface			'adminbar' or 'options'
		 * @return boolean
		 */
		protected function surface_enabled( $surface )
		{
			return 'disabled' !== avia_get_option( 'news_show_' . $surface, 'enabled' );
		}

		/**
		 * Whether the trigger in the theme options header is rendered.
		 *
		 * WPML puts its language switcher into the same header cell through the same
		 * filter, positioned absolutely, and the two would sit on top of each other.
		 * The admin bar entry is on every screen anyway, so the options header is the
		 * one to give up.
		 *
		 * Asking whether that callback is actually hooked is exact - it is true only when
		 * a switcher really will be drawn into this cell. Inferring it from
		 * ICL_SITEPRESS_VERSION would also have to repeat the avia_exclude_wpml check
		 * from functions.php, and would be wrong the moment either changes.
		 *
		 * @since 8.0
		 * @return boolean
		 */
		public function shows_options_trigger()
		{
			if( false !== has_filter( 'avia_options_page_header', 'avia_backend_language_switch' ) )
			{
				return false;
			}

			return $this->can_display() && $this->surface_enabled( 'options' );
		}

		/**
		 * Marks the screens where the options header trigger really is present, so the
		 * css that strips the padding from that header cell cannot reach WPML's
		 * switcher when we are not rendering.
		 *
		 * @since 8.0
		 * @param string $classes
		 * @return string
		 */
		public function handler_admin_body_class( $classes )
		{
			return $this->shows_options_trigger() ? $classes . ' av-news-has-options-trigger' : $classes;
		}

		/**
		 * Nothing to enqueue and no template to print when neither surface can show it.
		 *
		 * @since 8.0
		 * @return boolean
		 */
		protected function any_surface_enabled()
		{
			return $this->surface_enabled( 'adminbar' ) || $this->surface_enabled( 'options' );
		}

		/**
		 * The css and js are loaded on every admin screen, because the admin bar entry
		 * is on every admin screen. Both are small and nothing is enqueued when there
		 * are no items or when neither surface may show them.
		 *
		 * @since 8.0
		 */
		public function handler_admin_enqueue_scripts()
		{
			if( ! $this->can_display() || ! $this->any_surface_enabled() )
			{
				return;
			}

			$vn = avia_get_theme_version();
			$base = trailingslashit( get_template_directory_uri() ) . 'includes/admin/news/';

			wp_enqueue_style( 'avia-news', $base . 'av-news.css', array(), $vn );
			wp_enqueue_script( 'avia-news', $base . 'av-news.js', array(), $vn, true );

			wp_localize_script( 'avia-news', 'aviaNewsL10n', array(
						'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
						'nonce'		=> wp_create_nonce( aviaNews::NONCE ),
						'newest'	=> $this->newest_id()
					) );
		}

		/**
		 * Id of the first item in the list - the marker written to user meta when the
		 * panel is opened, and the one unread_count() counts back from.
		 *
		 * @since 8.0
		 * @return string				empty when there is nothing to mark
		 */
		protected function newest_id()
		{
			$items = $this->get_items();

			return ! empty( $items[0]['id'] ) ? (string) $items[0]['id'] : '';
		}

		/**
		 * Adds the notification section to the Theme Update tab.
		 *
		 * Registered from here through the existing filter rather than by editing
		 * class-avia-auto-updates.php - the feature owns its own options.
		 *
		 * @since 8.0
		 * @param array $avia_elements
		 * @return array
		 */
		public function handler_option_page_data( $avia_elements )
		{
			if( ! $this->is_enabled() || ! is_array( $avia_elements ) )
			{
				return $avia_elements;
			}

			$avia_elements[] = array(
						'slug'			=> 'update',
						'name'			=> __( 'Notification Center', 'avia_framework' ),
						'desc'			=> __( 'Enfold can show news about new releases, demos and features. Choose where those notifications are allowed to appear.', 'avia_framework' ),
						'type'			=> 'heading',
						'std'			=> '',
						'nodescription'	=> true
					);

			$avia_elements[] = array(
						'slug'		=> 'update',
						'name'		=> __( 'Show Notifications In The Admin Bar', 'avia_framework' ),
						'desc'		=> __( 'Adds an "Enfold News" entry to the WordPress admin bar. Uncheck to remove the entry and its panel.', 'avia_framework' ),
						'id'		=> 'news_show_adminbar',
						'type'		=> 'checkbox',
						'std'		=> 'enabled'
					);

			$avia_elements[] = array(
						'slug'		=> 'update',
						'name'		=> __( 'Show Notifications In The Theme Options Panel', 'avia_framework' ),
						'desc'		=> __( 'Adds the "What\'s new" button to the top of this options panel. Uncheck to remove the button and its panel.', 'avia_framework' ),
						'id'		=> 'news_show_options',
						'type'		=> 'checkbox',
						'std'		=> 'enabled'
					);

			return $avia_elements;
		}

		/**
		 * Badge on the WP admin bar - available on every backend screen
		 *
		 * @since 8.0
		 * @param \WP_Admin_Bar $wp_admin_bar
		 */
		public function handler_admin_bar_menu( $wp_admin_bar )
		{
			if( ! $this->can_display() || ! is_object( $wp_admin_bar ) )
			{
				return;
			}

			if( ! $this->surface_enabled( 'adminbar' ) )
			{
				return;
			}

			$count = $this->unread_count();

			$title  = '<span class="av-news-ab-icon"></span>';
			$title .= '<span class="av-news-ab-label">' . esc_html__( 'Enfold News', 'avia_framework' ) . '</span>';

			if( $count > 0 )
			{
				$title .= '<span class="av-news-badge">' . (int) $count . '</span>';
			}

			/*
			 *  'top-secondary' is the right hand group. Its items are floated right in
			 *  insertion order, so the account menu - added by core first - stays on the
			 *  outside and this lands directly next to it.
			 */
			$wp_admin_bar->add_node( array(
						'id'		=> 'av-news',
						'parent'	=> 'top-secondary',
						'title'		=> $title,
						'href'		=> '#',
						'meta'		=> array(
										'class'	=> 'av-news-trigger av-news-trigger-adminbar',
										'title'	=> esc_attr__( 'Latest Enfold news', 'avia_framework' )
									)
					) );
		}

		/**
		 * Trigger in the theme options header - two lines, the second showing the
		 * teaser of the newest item
		 *
		 * @since 8.0
		 * @param string $html
		 * @return string
		 */
		public function handler_options_page_header( $html )
		{
			if( ! $this->shows_options_trigger() )
			{
				return $html;
			}

			$items = $this->get_items();
			$count = $this->unread_count();
			$teaser = ! empty( $items[0]['teaser'] ) ? $items[0]['teaser'] : $items[0]['headline'];

			/*
			 *  Three columns: the icon and the caret are centred against the two text
			 *  lines between them, so they line up with the block as a whole rather than
			 *  with the first line.
			 */
			$output  = '<a href="#" class="av-news-trigger av-news-trigger-options">';
			$output .=		'<span class="av-news-options-text">';
			$output .=			'<span class="av-news-options-head">';
			$output .=				'<span class="av-news-ab-icon"></span>';
			$output .=				'<span class="av-news-options-title">' . esc_html__( "What's new", 'avia_framework' ) . '</span>';

			if( $count > 0 )
			{
				$output .=			'<span class="av-news-badge">' . (int) $count . '</span>';
			}

			$output .=			'</span>';
			//	teaser is escaped at ingest
			$output .=			'<span class="av-news-options-teaser">' . $teaser . '</span>';
			$output .=		'</span>';
			//	flips to point up while the panel is open, see .av-news-open in the css
			$output .=		'<span class="av-news-caret" aria-hidden="true"></span>';
			$output .= '</a>';

			return $html . $output;
		}

		/**
		 * The panel is printed once into an inert template element and moved to whichever
		 * trigger was clicked. Template content is not rendered and its images are not
		 * requested until it is inserted.
		 *
		 * @since 8.0
		 */
		public function handler_admin_footer()
		{
			if( ! $this->can_display() || ! $this->any_surface_enabled() )
			{
				return;
			}

			echo '<template id="av-news-panel-template">';
			echo 		$this->render_panel();
			echo '</template>';
		}

		/**
		 * @since 8.0
		 * @return string
		 */
		public function render_panel()
		{
			try
			{
				$items = $this->get_items();

				if( empty( $items ) )
				{
					return '';
				}

				$lead = array_shift( $items );

				$output  = '<div class="av-news-panel">';
				$output .=		'<div class="av-news-lead">';
				$output .=			$this->render_image( $lead, 'image', 'av-news-cover' );
				$output .=			'<div class="av-news-lead-body">';
				$output .=				$this->render_meta( $lead );
				$output .=				'<h3 class="av-news-headline">' . $lead['headline'] . '</h3>';

				if( '' !== $lead['description'] )
				{
					$output .=			'<p class="av-news-description">' . $lead['description'] . '</p>';
				}

				$output .=				'<a class="av-news-link"' . $this->link_attributes( $lead ) . '>';
				$output .=					( '' !== $lead['link_label'] ? $lead['link_label'] : esc_html__( 'Read more', 'avia_framework' ) ) . ' &rarr;';
				$output .=				'</a>';
				$output .=			'</div>';
				$output .=		'</div>';

				foreach( $items as $item )
				{
					$output .= '<a class="av-news-row"' . $this->link_attributes( $item ) . '>';
					$output .=		$this->render_image( $item, 'thumb', 'av-news-thumb' );
					$output .=		'<span class="av-news-row-body">';
					//	same order as the lead item - date, category, then the headline
					$output .=			$this->render_meta( $item, 'span', 'av-news-meta av-news-row-meta' );
					$output .=			'<span class="av-news-row-headline">' . $item['headline'] . '</span>';

					if( '' !== $item['description'] )
					{
						$output .=		'<span class="av-news-row-description">' . $item['description'] . '</span>';
					}

					$output .=		'</span>';
					$output .= '</a>';
				}

				$output .= '</div>';

				return $output;
			}
			catch( \Throwable $e )
			{
				//	a broken cached item must never break an admin screen
				return '';
			}
		}

		/**
		 * @since 8.0
		 * @param array $item
		 * @param string $tag				the secondary rows are anchors built from spans
		 * @param string $class
		 * @return string
		 */
		protected function render_meta( array $item, $tag = 'div', $class = 'av-news-meta' )
		{
			$meta = array();

			if( ! empty( $item['date'] ) )
			{
				$meta[] = esc_html( $this->format_date( $item['date'] ) );
			}

			if( ! empty( $item['category'] ) )
			{
				$meta[] = $item['category'];
			}

			if( empty( $meta ) )
			{
				return '';
			}

			$tag = ( 'span' === $tag ) ? 'span' : 'div';

			return '<' . $tag . ' class="' . esc_attr( $class ) . '">' . implode( ' &middot; ', $meta ) . '</' . $tag . '>';
		}

		/**
		 * href, target and rel for one item.
		 *
		 * Internal targets are resolved here rather than at ingest, so each subsite of a
		 * network builds its own, and they open in the same tab - sending somebody to
		 * their own backend in a new window is just litter.
		 *
		 * @since 8.0
		 * @param array $item
		 * @return string					leading space included
		 */
		protected function link_attributes( array $item )
		{
			$scope = isset( $item['link_scope'] ) ? $item['link_scope'] : 'external';
			$path = isset( $item['link_path'] ) ? $item['link_path'] : '';

			switch( $scope )
			{
				case 'admin':
					//	http on a local install, so the scheme list cannot be https only
					return ' href="' . esc_url( admin_url( $path ) ) . '"';

				case 'home':
					return ' href="' . esc_url( home_url( '/' . $path ) ) . '"';
			}

			return ' href="' . esc_url( $item['link_url'], array( 'https' ) ) . '" target="_blank" rel="noopener noreferrer"';
		}

		/**
		 * The site's own date format and timezone, e.g. "August 12, 2026".
		 *
		 * Not human_time_diff(): "2 days ago" has to be decoded against today, it goes
		 * stale in a cached panel, and it is useless for anything older than a week -
		 * which most of this list will be.
		 *
		 * @since 8.0
		 * @param int $timestamp			utc
		 * @return string
		 */
		protected function format_date( $timestamp )
		{
			/**
			 * @since 8.0
			 * @param string $format
			 * @return string
			 */
			$format = apply_filters( 'avf_news_date_format', get_option( 'date_format' ) );

			//	wp_date() is timezone correct, date_i18n() is the fallback for older cores
			return function_exists( 'wp_date' ) ? wp_date( $format, $timestamp ) : date_i18n( $format, $timestamp );
		}

		/**
		 * Fixed size in css, so a wrong sized image can not break the layout.
		 * no-referrer keeps the customers backend url out of the request.
		 *
		 * @since 8.0
		 * @param array $item
		 * @param string $key
		 * @param string $class
		 * @return string
		 */
		protected function render_image( array $item, $key, $class )
		{
			//	no image means no box - an empty hatched placeholder is just noise
			if( empty( $item[ $key ] ) )
			{
				return '';
			}

			$html  = '<span class="' . esc_attr( $class ) . '">';
			$html .=	'<img src="' . esc_url( $item[ $key ], array( 'https' ) ) . '" alt="" loading="lazy" referrerpolicy="no-referrer" />';
			$html .= '</span>';

			return $html;
		}

		/**
		 * @since 8.0
		 */
		public function handler_ajax_mark_read()
		{
			check_ajax_referer( aviaNews::NONCE );

			if( ! current_user_can( 'manage_options' ) )
			{
				wp_send_json_error();
			}

			$newest = $this->newest_id();

			if( '' !== $newest )
			{
				update_user_meta( get_current_user_id(), aviaNews::USER_META, $newest );
			}

			wp_send_json_success();
		}

		/**
		 * Remove everything we stored when the feature is switched off
		 *
		 * @since 8.0
		 */
		protected function cleanup()
		{
			/*
			 *  Never on multisite. The list is one network option, but avia_no_news is
			 *  decided per subsite - so one subsite opting out would delete the list the
			 *  rest of the network is using, on every admin request. They would re-seed
			 *  and re-fetch, this would delete again, and the recheck interval would
			 *  collapse from days to hours. Opting out already stops this subsite
			 *  fetching and displaying; that is the whole job.
			 */
			if( is_multisite() )
			{
				return;
			}

			$state = get_option( aviaNews::OPTION, false );

			if( false !== $state )
			{
				$this->delete_state();
			}
		}
	}


	/**
	 * @since 8.0
	 * @return \enfold\news\aviaNews
	 */
	function avia_News()
	{
		return aviaNews::instance();
	}

	//	activate
	avia_News();
}
