<?php
namespace aviaFramework\updates;
/**
 * Small update class that manages theme version updates, e.g. options in DB
 * Fires the ava_trigger_updates hook
 *
 * Class and checks MUST be run in frontend also !!!
 *
 * @since ????
 * @since 6.0						Moved from function-set-avia-backend.php
 */
if( ! defined( 'AVIA_FW' ) ) { exit( 'No direct script access allowed' ); }


if( ! class_exists( __NAMESPACE__ . '\Avia_Theme_Data_Updater', false ) )
{
	class Avia_Theme_Data_Updater
	{
		/**
		 *
		 * @since 6.0
		 * @var \aviaFramework\updates\Avia_Theme_Data_Updater
		 */
		static protected $_instance = null;

		/**
		 * @since 6.0
		 * @var string
		 */
		protected $db_version;

		/**
		 * @since 6.0
		 * @var string
		 */
		protected $theme_version;

		/**
		 * @since 6.0
		 * @var string
		 */
		protected $option_key;

		/**
		 * Flag to show or ignore default message - can be unset in triggered update scripts
		 *
		 * @since 6.0
		 * @var boolean
		 */
		protected $show_default_notice_flag;

		/**
		 * Return the instance of this class
		 *
		 * @since 6.0
		 * @return \aviaFramework\updates\Avia_Theme_Data_Updater
		 */
		static public function instance()
		{
			if( is_null( Avia_Theme_Data_Updater::$_instance ) )
			{
				Avia_Theme_Data_Updater::$_instance = new Avia_Theme_Data_Updater();
			}

			return Avia_Theme_Data_Updater::$_instance;
		}

		/**
		 * @since 6.0
		 */
		public function __construct()
		{
			$theme = wp_get_theme();
			if( is_child_theme() )
			{
				$theme = wp_get_theme( $theme->get('Template') );
			}

			$this->theme_version = $theme->get( 'Version' );
			$this->option_key = $theme->get( 'Name' ) . '_version';
			$this->db_version = get_option( $this->option_key, '0' );
			$this->show_default_notice_flag = true;

//			$this->db_version = '5.2';		// for testing

			add_action( 'wp_loaded', array( $this, 'handler_update_version' ) );
		}

		/**
		 * provide a hook for update functions and update the version number
		 *
		 * @since 6.0
		 */
		public function handler_update_version()
		{
			/**
			 * @used_by								\aviaFramework\aviaAdminNotices				10
			 * @since 6.0
			 * @param string $this->theme_version
			 * @param string $this->db_version
			 * @param string $this->option_key
			 */
			do_action( 'ava_before_theme_update_check', $this->theme_version, $this->db_version, $this->option_key );

			//if we are on a new installation do not do any updates to the db
			if( $this->db_version == '0' )
			{
				//	not loaded in frontend !!!!
				if( ! class_exists( '\aviaFramework\aviaAdminNotices' ) )
				{
					require_once ( AVIA_PHP . 'class-admin-notices.php' );
				}

				//	force to clear all admin notices
				\aviaFramework\avia_AdminNotices()->delete_notice();

				/**
				 * Trigger new install scripts
				 *
				 * @since 6.0
				 * @param string $this->theme_version
				 */
				do_action( 'ava_trigger_new_install', $this->theme_version );

				update_option( $this->option_key, $this->theme_version );

				/**
				 * @since 6.0
				 */
				do_action( 'ava_after_theme_update' );

				if( true === $this->show_default_notice_flag && ! \aviaFramework\avia_AdminNotices()->exists_current_notice( 'welcome_new' ) )
				{
					\aviaFramework\avia_AdminNotices()->add_notice( 'welcome_new', time() + \aviaFramework\avia_AdminNotices()->get_default_expire_time( 'welcome_new' )  );
				}
			}
			else if( version_compare( $this->theme_version, $this->db_version, '>' ) )
			{
				//	not loaded in frontend !!!!
				if( ! class_exists( '\aviaFramework\aviaAdminNotices' ) )
				{
					require_once ( AVIA_PHP . 'class-admin-notices.php' );
				}

				/**
				 * Trigger update scripts
				 *
				 * @used_by							\enfold\updates\helperCompatUpdate
				 * @since ???
				 * @param string $this->db_version
				 * @param string $this->theme_version
				 */
				do_action( 'ava_trigger_updates', $this->db_version, $this->theme_version );

				update_option( $this->option_key, $this->theme_version );

				/**
				 * @since ???
				 */
				do_action( 'ava_after_theme_update' );

				if( true === $this->show_default_notice_flag && ! \aviaFramework\avia_AdminNotices()->exists_current_notice( 'welcome_update' ) )
				{
					\aviaFramework\avia_AdminNotices()->add_notice( 'welcome_update', \aviaFramework\avia_AdminNotices()->get_default_expire_time( 'welcome_update' )  );
				}
			}
			else if( version_compare( $this->theme_version, $this->db_version, '<' ) )
			{
				/**
				 * This is only a fallback intended for testing and debugging purpose
				 *
				 * @since 6.0
				 */
				if( defined( 'WP_DEBUG' ) && true === WP_DEBUG && current_user_can( 'manage_options' ) )
				{
					error_log( "**** Theme version was downgraded in database from '{$this->db_version}' to '{$this->theme_version}'  *********************" );

					update_option( $this->option_key, $this->theme_version );
				}
			}

//			 update_option( $this->option_key, '5.6' ); // for testing
		}

		/**
		 * Allows to set or ignore to show the default notice
		 *
		 * @since 6.0
		 * @param boolean $show
		 */
		public function show_default_notice( $show )
		{
			$this->show_default_notice_flag = true === $show;
		}
	}

	/**
	 * Returns the main instance of aviaAdminNotices to prevent the need to use globals.
	 *
	 * @since 6.0
	 * @return \aviaFramework\updates\Avia_Theme_Data_Updater
	 */
	function aviaThemeDataUpdater()
	{
		return Avia_Theme_Data_Updater::instance();
	}

	//	activate class
	aviaThemeDataUpdater();
}
