<?php
namespace aviaFramework;

/**
 * Base class to handle a dismissable notice box on admin screens.
 * It can be dismissed by each user or global for all users by first user clicking.
 * Messages are stored in WP option and will be displayed until deleted, outdated or dismissed by a user if global.
 *
 * Close button supports:
 *
 *		- close only and show on next pageload again
 *		- dismiss content
 *
 * To add a notice or multiple notices:
 *
 *		add_notice( 'gdpr_update_2' );
 *		add_notice( [ 'gdpr_update', 'gdpr_update_2' ] );
 *
 *
 * To clear a notice or multiple notices or all:
 *
 *		delete_notice( 'gdpr_update_2' );
 *		delete_notice( [ 'gdpr_update', 'gdpr_update_2' ] );
 *		delete_notice();
 *
 * @since 6.0
 * @added_by Günter
 */
if( ! defined( 'AVIA_FW' ) ) { exit( 'No direct script access allowed' ); }


if( ! class_exists( __NAMESPACE__ . '\aviaAdminNotices', false ) )
{
	class aviaAdminNotices
	{
		const OPT_NOTICE = 'avia_admin_show_notices';
		const NONCE = 'avia_admin_notices';

		/**
		 *
		 * @since 6.0
		 * @var \aviaFramework\aviaAdminNotices
		 */
		static protected $_instance = null;

		/**
		 * Current notices to display
		 *
		 *			$notice_key		=> $expire			false | timestamp using time()
		 *
		 * @since 6.0
		 * @var array
		 */
		protected $current_notices;

		/**
		 * Holds the possible messages to display
		 *
		 * @since 6.0
		 * @var array|null
		 */
		protected $all_notices;

		/**
		 * Default expire time in seconds for a notice box
		 *
		 * @since 6.0
		 * @var int
		 */
		protected $default_expire_time;

		/**
		 * Flag to use cron job to clean up internal notice data
		 *
		 * @since 6.0
		 * @var boolean
		 */
		protected $activate_cron;

		/**
		 * Return the instance of this class
		 *
		 * @since 6.0
		 * @return \aviaFramework\aviaAdminNotices
		 */
		static public function instance()
		{
			if( is_null( aviaAdminNotices::$_instance ) )
			{
				aviaAdminNotices::$_instance = new aviaAdminNotices();
			}

			return aviaAdminNotices::$_instance;
		}

		/**
		 * @since 6.0
		 */
		protected function __construct()
		{
			$this->all_notices = null;
			$this->current_notices = null;
			$this->default_expire_time = 20 * DAY_IN_SECONDS;

			$this->activate_cron = ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON );

			/**
			 * Activate/Deactivate cron job to delete CSS files
			 *
			 * @since 6.0
			 * @param boolean $this->activate_cron
			 * @return boolean
			 */
			$this->activate_cron = apply_filters( 'avf_admin_notices_activate_cron', $this->activate_cron );

			/**
			 * WP Cron job events
			 */
			if( $this->activate_cron )
			{
				add_action( 'wp_loaded', array( $this, 'handler_wp_loaded' ), 100 );
				add_action( 'ava_cron_admin_notices_clean_up', array( $this, 'handler_cron_admin_notices_clean_up' ), 10 );
			}

			add_action( 'admin_enqueue_scripts', [ $this, 'handler_wp_admin_enqueue_scripts' ], 500 );
			add_action( 'admin_notices', [ $this, 'handler_wp_admin_notices' ], 5 );
			add_action( 'ava_before_theme_update_check', [ $this, 'handler_ava_before_theme_update_check' ], 10, 3 );

			add_action( 'wp_ajax_avia_admin_notice_dismissed', [ $this, 'handler_avia_admin_notice_dismissed' ], 10 );
		}

		/**
		 * @since 6.0
		 */
		public function __destruct()
		{
			unset( $this->all_notices );
			unset( $this->current_notices );
		}

		/**
		 * Add script
		 * (stylings are added to avia_global_admin.css)
		 *
		 * @since 6.0
		 */
		public function handler_wp_admin_enqueue_scripts()
		{
			if( empty( $this->get_current_notices() ) )
			{
				return;
			}

			$vn = avia_get_theme_version();
			$min_js = avia_minify_extension( 'js' );

			wp_enqueue_script( 'avia_admin_notices_script' , AVIA_JS_URL . "conditional_load/avia_admin_notices{$min_js}.js", array( 'jquery' ), $vn, true );
		}

		/**
		 * @since 6.0
		 */
		public function handler_wp_loaded()
		{
			if( $this->activate_cron )
			{
				$this->init_cron_clean_up();
			}
		}

		/**
		 * Output the admin notices
		 *
		 * @since 6.0
		 */
		public function handler_wp_admin_notices( )
		{
			$user_id = get_current_user_id();

			//	fallback for non logged in user
			if( $user_id <= 0 )
			{
				return;
			}

			if( empty( $this->get_current_notices() ) )
			{
				//	remove all dismissed notices if none are to display
				update_user_meta( $user_id, aviaAdminNotices::OPT_NOTICE, [] );
				return;
			}

			$all_notices = $this->get_all_notices();

			$filtered_notices = $this->get_filtered_notices_to_show( $user_id );

			if( empty( $filtered_notices ) )
			{
				//	remove all dismissed notices if none are to display
				update_user_meta( $user_id, aviaAdminNotices::OPT_NOTICE, [] );
				return;
			}

			$clicked = get_user_meta( $user_id, aviaAdminNotices::OPT_NOTICE, true );

			if( ! is_array( $clicked ) )
			{
				$clicked = [];
			}

			//	clean up user meta - already clicked notices that are no longer visible to user can be removed to allow a reactivation later
			$deleted = false;
			foreach( $clicked as $index => $notice_key )
			{
				if( ! isset( $filtered_notices[ $notice_key ] ) )
				{
					unset( $clicked[ $index ] );
					$deleted = true;
				}
			}

			if( $deleted )
			{
				update_user_meta( $user_id, aviaAdminNotices::OPT_NOTICE, $clicked );
			}

			foreach( $filtered_notices as $notice_key => $expire )
			{
				if( in_array( $notice_key, $clicked ) )
				{
					unset( $filtered_notices[ $notice_key ] );
				}
			}

			foreach( $filtered_notices as $notice_key => $expire )
			{
				if( ! isset( $all_notices[ $notice_key ] ) )
				{
					continue;
				}

				$this->output_single_notice( $notice_key );
			}
		}

		/**
		 *
		 * @since 6.0
		 */
		public function handler_avia_admin_notice_dismissed()
		{
			header( 'Content-Type: application/json' );

			$return = check_ajax_referer( aviaAdminNotices::NONCE, aviaAdminNotices::NONCE . '_nonce', false );

			$user_id = get_current_user_id();

			//security improvement. only allow certain permissions to execute this function
			if( ! current_user_can( 'edit_posts' ) )
			{
				$return = false;
			}

			// response output
			$response = array( aviaAdminNotices::NONCE . '_nonce' => wp_create_nonce( aviaAdminNotices::NONCE ) );

			/**
			 * Return error
			 */
			if( false === $return )
			{
				$response['success'] = false;
				$response['message'] = __( 'Expired nonce', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			$settings = isset( $_POST['settings'] ) ? $_POST['settings'] : [];

			if( empty( $this->get_current_notices() ) )
			{
				//	remove all dismissed notices if none are to display
				update_user_meta( $user_id, aviaAdminNotices::OPT_NOTICE, [] );

				if( ! $this->activate_cron )
				{
					$this->handler_cron_admin_notices_clean_up();
				}

				$response['success'] = true;
				$response['message'] = __( 'Notice box was already removed from display list (empty list).', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			$meta = get_user_meta( $user_id, aviaAdminNotices::OPT_NOTICE, true );

			if( ! is_array( $meta ) )
			{
				$meta = [];
			}

			//	must have been closed by another user, expired and deleted already
			if( ! isset( $this->current_notices[ $settings['key'] ] ) )
			{
				$index = array_search( $settings['key'], $meta );
				if( false !== $index )
				{
					unset( $meta[ $index ] );
				}

				update_user_meta( $user_id, aviaAdminNotices::OPT_NOTICE, $meta );

				$response['message'] = __( 'Notice box was already removed from display list:', 'avia_framework' ) . " {$settings['key']}";
			}
			else
			{
				if( 'all_users' == $settings['dismiss'] )
				{
					$this->delete_notice( $settings['key'] );

					$response['message'] = __( 'Notice box was removed from display list:', 'avia_framework' ) . " {$settings['key']}";
				}
				else
				{
					if( ! in_array( $settings['key'], $meta ) )
					{
						$meta[] = $settings['key'];
					}

					update_user_meta( $user_id, aviaAdminNotices::OPT_NOTICE, $meta );

					$response['message'] = __( 'Notice box was added to users clicked list: ', 'avia_framework' ) . " {$settings['key']}";
				}
			}

			if( ! $this->activate_cron )
			{
				$this->handler_cron_admin_notices_clean_up();
			}

			$response['success'] = true;
			echo json_encode( $response );
			exit;
		}

		/**
		 * Checks that all notices in $this->current_notices exist
		 * Scans all users that have dismissed metadata field and limits array to active notices
		 *
		 * Should only be called in an ajax call that does not return any values
		 *
		 * @since 6.0
		 * @param boolean $start_new_cron
		 */
		public function handler_cron_admin_notices_clean_up( $start_new_cron = true )
		{
			global $wpdb;

			$notices = $this->get_all_notices();

			if( current_theme_supports( 'avia_log_cron_job_messages' ) )
			{
				error_log( '******************  In \aviaFramework\aviaAdminNotices::handler_cron_admin_notices_clean_up started' );
			}

			//	ensure we have loaded it
			$this->get_current_notices();

			$changed = $this->clean_up_expired_current_notices( false );

			foreach( $this->current_notices as $key => $expire )
			{
				if( ! isset( $notices[ $key ] )  )
				{
					unset( $this->current_notices[ $key ] );
					$changed = true;
				}
			}

			if( $changed )
			{
				update_option( aviaAdminNotices::OPT_NOTICE, $this->current_notices, false );
			}

			$users = $this->get_users_with_dismiss();

			if( ! empty( trim( $wpdb->last_error ) ) || ! is_array( $users ) )
			{
				if( current_theme_supports( 'avia_log_cron_job_messages' ) )
				{
					error_log( '******************  In \aviaFramework\aviaAdminNotices::handler_cron_admin_notices_clean_up ended - DB error' );
				}

				if( $start_new_cron )
				{
					$this->init_cron_clean_up();
				}

				return;
			}

			foreach( $users as $key => $user )
			{
				$meta = maybe_unserialize( $user['notices_show'] );
				if( empty( $meta ) || ! is_array( $meta ) )
				{
					continue;
				}

				if( empty( $this->current_notices ) )
				{
					update_user_meta( $user['ID'], aviaAdminNotices::OPT_NOTICE, [] );
					continue;
				}

				$changed = false;

				foreach( $meta as $key => $notice_key )
				{
					if( ! isset( $this->current_notices[ $notice_key ] ) )
					{
						unset( $meta[ $key ] );
						$changed = true;
					}
				}

				if( $changed )
				{
					update_user_meta( $user['ID'], aviaAdminNotices::OPT_NOTICE, $meta );
				}
			}

			if( current_theme_supports( 'avia_log_cron_job_messages' ) )
			{
				error_log( '******************  In \aviaFramework\aviaAdminNotices::handler_cron_admin_notices_clean_up ended' );
			}

			if( $start_new_cron )
			{
				$this->init_cron_clean_up();
			}
		}

		/**
		 * To manage admin notices behaviour extend url in a backend page:
		 *
		 *		- clear all admin notices and user clicks:		"avia-admin-notices=clear-all"
		 *		- clear user clicks of current user:			"avia-admin-notices=clear-current-user"
		 *		- clear user clicks of all user:				"avia-admin-notices=clear-all-users"
		 *
		 * e.g. ../wp-admin/admin.php?page=avia&avia-admin-notices=clear-all
		 *
		 * @since 6.0
		 * @param string $theme_version
		 * @param string $db_version
		 * @param string $option_key
		 */
		public function handler_ava_before_theme_update_check( $theme_version, $db_version, $option_key )
		{
			if( ! ( is_admin() && isset( $_REQUEST['avia-admin-notices'] ) ) )
			{
				return;
			}

			switch( $_REQUEST['avia-admin-notices'] )
			{
				case 'clear-all':
					//	Remove all notices from option
					$this->delete_notice();

					$this->handler_cron_admin_notices_clean_up( false );
					break;
				case 'clear-current-user':
					$user_id = get_current_user_id();

					if( $user_id > 0 )
					{
						update_user_meta( $user_id, aviaAdminNotices::OPT_NOTICE, [] );
					}
					break;
				case 'clear-all-users':
					$users = $this->get_users_with_dismiss();

					if( is_array( $users ) )
					{
						foreach( $users as $user )
						{
							update_user_meta( $user['ID'], aviaAdminNotices::OPT_NOTICE, [] );
						}
					}
					break;
			}
		}

		/**
		 * Try to init a cron job for deleting files
		 *
		 * @since 6.0
		 * @return boolean
		 */
		protected function init_cron_clean_up()
		{
			if( ! $this->activate_cron )
			{
				return false;
			}

			if( false !== wp_next_scheduled( 'ava_cron_admin_notices_clean_up' ) )
			{
				return false;
			}

			if( current_theme_supports( 'avia_log_cron_job_messages' ) )
			{
				error_log( '******************  In \aviaFramework\aviaAdminNotices::init_cron_clean_up new cron job scheduled' );
			}

			$started = wp_schedule_single_event( time() + 10 * MINUTE_IN_SECONDS, 'ava_cron_admin_notices_clean_up' );

			if( current_theme_supports( 'avia_log_cron_job_messages' ) && true !== $started )
			{
				error_log( '******************  In \aviaFramework\aviaAdminNotices::init_cron_clean_up new cron job could not be started' );
			}

			return ( true === $started ) ? $started : false;
		}

		/**
		 * Get all the defined notices
		 *
		 * @since 6.0
		 * @return array
		 */
		protected function init_notices()
		{
			$notices = [];

			$custom_include_files = [ AVIA_PHP . 'admin-notices/admin-notices-def.php' ];

			/**
			 * Add custom definition files for admin notice boxes messages.
			 * Make sure to return valid file paths as there is no check if the file exists
			 *
			 * @used_by								\enfold\updates\helperCompatUpdate		10
			 * @since 6.0
			 * @param string[] $custom_include_files
			 * @return string[]
			 */
			$custom_include_files = apply_filters( 'avf_admin_notices_definition_files', $custom_include_files );

			if( is_array( $custom_include_files ) )
			{
				foreach( $custom_include_files as $include_file )
				{
					include( $include_file );
				}
			}

			/**
			 * Add custom notices to display in backend
			 *
			 * @since 6.0
			 * @param array $notices
			 * @return array
			 */
			return apply_filters( 'avf_init_admin_notices', $notices );
		}

		/**
		 * Returns the filtered default expire time
		 *
		 * @since 6.0
		 * @param string $context
		 * @return int
		 */
		public function get_default_expire_time( $context = '' )
		{
			/**
			 * @since 6.0
			 * @param int $this->default_expire_time
			 * @param string $context
			 * @return int
			 */
			return apply_filters( 'avf_admin_notice_default_expire_time', $this->default_expire_time, $context );
		}

		/**
		 * Returns an array of all notices to display.
		 * Expired notices are removed from list.
		 *
		 * @since 6.0
		 * @return array
		 */
		public function get_current_notices()
		{
			if( ! is_array( $this->current_notices ) )
			{
				$this->current_notices = get_option( aviaAdminNotices::OPT_NOTICE, [] );
				$this->clean_up_expired_current_notices();
			}

			return $this->current_notices;
		}

		/**
		 * Returns an array of all defined notice messages
		 *
		 * @since 6.0
		 * @return array
		 */
		public function get_all_notices()
		{
			if( ! is_array( $this->all_notices ) )
			{
				$this->all_notices = $this->init_notices();
			}

			return $this->all_notices;
		}

		/**
		 * Adds notices to be displayed. It is highly recommended to set $expire especially if 'dismiss' = 'user_only'
		 * to allow clean up of user meta data if option is expired ( or you must remove it with delete_notice() ).
		 *
		 * @since 6.0
		 * @param string|string[] $notice_keys
		 * @param timestamp|false $expire
		 * @param boolean $save
		 */
		public function add_notice( $notice_keys, $expire = false, $save = true )
		{
			if( ! is_array( $notice_keys ) )
			{
				$notice_keys = [ $notice_keys ];
			}

			$this->get_current_notices();

			foreach( $notice_keys as $notice_key )
			{
				$this->current_notices[ $notice_key ] = $expire;
			}

			if( true === $save )
			{
				update_option( aviaAdminNotices::OPT_NOTICE, $this->current_notices, false );
			}
		}

		/**
		 * Checks, if one of the $notice_keys already exists in current notice list.
		 * Allows e.g. to override frameworks default messages
		 *
		 * @since 6.0
		 * @param string|array $notice_keys
		 * @return boolean
		 */
		public function exists_current_notice( $notice_keys )
		{
			if( ! is_array( $notice_keys ) )
			{
				$notice_keys = [ $notice_keys ];
			}

			$current = $this->get_current_notices();

			foreach( $notice_keys as $notice_key )
			{
				if( isset( $current[ $notice_key ] ) )
				{
					return true;
				}
			}

			return false;
		}

		/**
		 * Delete notices to display
		 *
		 * @since 6.0
		 * @param array|null $notice_keys				null for all notices
		 * @param type $save
		 */
		public function delete_notice( $notice_keys = null, $save = true )
		{
			$this->get_current_notices();

			if( is_null( $notice_keys ) )
			{
				$this->current_notices = [];
			}
			else
			{
				if( ! is_array( $notice_keys ) )
				{
					$notice_keys = [ $notice_keys ];
				}

				foreach( $notice_keys as $notice_key )
				{
					if( isset( $this->current_notices[ $notice_key ] ) )
					{
						unset( $this->current_notices[ $notice_key ] );
					}
				}
			}

			if( true === $save )
			{
				update_option( aviaAdminNotices::OPT_NOTICE, $this->current_notices, false );
			}
		}

		/**
		 * Remove expired notices
		 *
		 * @since 6.0
		 * @param boolean $save
		 * @return boolean					true, if entry was removed
		 */
		protected function clean_up_expired_current_notices( $save = true )
		{
			$changed = false;

			if( empty( $this->current_notices ) )
			{
				return $changed;
			}

			$timestamp = time();

			foreach( $this->current_notices as $notice_key => $expire )
			{
				if( false === $expire || $timestamp <= $expire )
				{
					continue;
				}

				unset( $this->current_notices[ $notice_key ] );
				$changed = true;
			}

			if( true === $save && $changed )
			{
				update_option( aviaAdminNotices::OPT_NOTICE, $this->current_notices, false );
			}

			return $changed;
		}

		/**
		 * Returns the notices to show for given user ID based on $this->current_notices
		 *
		 * @since 6.0
		 * @param int $user_id
		 * @return array
		 */
		protected function get_filtered_notices_to_show( $user_id )
		{
			$filtered = [];

			if( empty( $this->current_notices ) )
			{
				return $filtered;
			}

			foreach( $this->current_notices as $notice_key => $expire )
			{
				if( ! isset( $this->all_notices[ $notice_key ] ) )
				{
					continue;
				}

				$notice = $this->all_notices[ $notice_key ];

				$cap = isset( $notice['capability'] ) ? $notice['capability'] : 'all';

				if( 'all' == $cap || user_can( $user_id, $cap ) )
				{
					$filtered[ $notice_key ] = $expire;
				}
			}

			/**
			 *
			 * @since 6.0
			 * @param array $filtered
			 * @param int $user_id
			 * @param \aviaFramework\aviaAdminNotices $this
			 * @return array
			 */
			return apply_filters( 'avf_admin_notices_filtered', $filtered, $user_id, $this );
		}

		/**
		 * Returns user array where "notices_show" = metakey for clicked admin notices
		 *
		 * @since 6.0
		 * @return array|null
		 */
		protected function get_users_with_dismiss()
		{
			global $wpdb;

			$sql  = "SELECT {$wpdb->users}.*, {$wpdb->usermeta}.meta_value as notices_show ";
			$sql .= "FROM {$wpdb->users} ";
			$sql .= "LEFT JOIN {$wpdb->usermeta} ON {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ";
			$sql .= "WHERE {$wpdb->usermeta}.meta_key = '" . self::OPT_NOTICE . "' ";
			$sql .= "ORDER BY {$wpdb->users}.ID ";

			$users = $wpdb->get_results( $sql, ARRAY_A );

			return $users;
		}

		/**
		 * Outputs a single notice box
		 *
		 * @since 6.0
		 * @param string $notice_key
		 */
		protected function output_single_notice( $notice_key )
		{
			global $pagenow;

			$notice = $this->all_notices[ $notice_key ];

			if( isset( $notice['screens_only'] ) && ! empty( $notice['screens_only'] ) )
			{
				$screens = is_array( $notice['screens_only'] ) ? $notice['screens_only'] : [ $notice['screens_only'] ];

				if( ! in_array( $pagenow, $screens ) )
				{
					return;
				}
			}

			if( isset( $notice['screens_exclude'] ) && ! empty( $notice['screens_exclude'] ) )
			{
				$screens = is_array( $notice['screens_exclude'] ) ? $notice['screens_exclude'] : [ $notice['screens_exclude'] ];

				if( in_array( $pagenow, $screens ) )
				{
					return;
				}
			}

			/**
			 * Filter that allows to create and output your own layout.
			 * Return true if you use this.
			 *
			 * @since 6.0
			 * @param boolean $skip
			 * @param \aviaFramework\aviaAdminNotices $this
			 * @return boolean
			 */
			if( false !== apply_filters( 'avf_skip_output_single_notice', false, $notice_key, $this ) )
			{
				return;
			}

			$classes = [
						'notice',							//	WP needed for default js handling and adding "X"
						"notice-{$notice['class']}",
						'is-dismissible',					//	"X" handles hiding
						'avia-admin-notices',
						isset( $notice['add_class'] ) ? $notice['add_class'] : '',
						'av-notice-' . avia_backend_safe_string( $notice_key )
					];

			$settings = [
					'key'		=> $notice_key,
					'close'		=> $notice['close'],
					'dismiss'	=> $notice['dismiss']
				];

			$dismiss = '';

			if( 'hide' == $notice['close'] )
			{
				$dismiss = '<button class="notice-dismiss-text" title="' . esc_attr( __( 'Dismiss and hide', 'avia_framework' ) ) . '">' . __( 'Dismiss', 'avia_framework' ) . '</button>';
				$title_button = esc_attr( __( 'Hide notice', 'avia_framework' ) );
				$classes[] = 'avia-notice-hide';
			}
			else
			{
				$title_button = esc_attr( __( 'Dismiss and hide notice', 'avia_framework' ) );
				$classes[] = 'avia-notice-dismiss';
			}


			$classes = 'class="' . implode( ' ', $classes ) . '"';
			$data_settings = 'data-avia-notice-settings="' .  esc_attr( json_encode( $settings ) ) . '"';
			$data_nonce = 'data-' . aviaAdminNotices::NONCE . '_nonce="' . wp_create_nonce( aviaAdminNotices::NONCE ) . '"';

			$msg_added = false;

			/**
			 * see WP core ..\wp-admin\js\common.js makeNoticesDismissible()
			 * To avoid DOM problems we add this here hardcoded and in ..\enfold\framework\js\conditional_load\avia_admin_notices.js
			 */
			$output = '';
			$output .= "<div {$classes} {$data_settings} {$data_nonce}>";
			$output .=		'<button type="button" class="notice-dismiss" title="' . esc_attr( $title_button ) . '"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'avia_framework' ) . '</span></button>';
			$output .=		$dismiss;

			if( ! empty( $notice['msg'] ) )
			{
				$output .=	"<p>{$notice['msg']}</p>";
				$msg_added = true;
			}

			if( ! empty( $notice['html'] ) )
			{
				$output .=	"<div class=''>{$notice['msg']}</div>";
				$msg_added = true;
			}

			if( ! empty( $notice['template'] ) )
			{
				ob_start();

				include $notice['template'];

				$output .= ob_get_clean();

				$msg_added = true;
			}

			$output .= '</div>';		//	close notice container

			/**
			 * To remove a default message do not add any content
			 */
			if( $msg_added )
			{
				echo $output;
			}
		}

	}

	/**
	 * Returns the main instance of aviaAdminNotices to prevent the need to use globals.
	 *
	 * @since 6.0
	 * @return \aviaFramework\aviaAdminNotices
	 */
	function avia_AdminNotices()
	{
		return aviaAdminNotices::instance();
	}

	//	activate class
	avia_AdminNotices();

}
