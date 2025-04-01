<?php
namespace enfold\updates;
/**
 * This file holds all hooks to keep options, .... compatible between theme versions
 *
 * @since 6.0				moved from helper-compat-update.php and modified to class
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( __NAMESPACE__ . '\helperCompatUpdate', false ) )
{
	class helperCompatUpdate
	{
		/**
		 *
		 * @since 6.0
		 * @var \enfold\updates\helperCompatUpdate
		 */
		static protected $_instance = null;

		/**
		 * Return the instance of this class
		 *
		 * @since 6.0
		 * @return \enfold\updates\helperCompatUpdate
		 */
		static public function instance()
		{
			if( is_null( helperCompatUpdate::$_instance ) )
			{
				helperCompatUpdate::$_instance = new helperCompatUpdate();
			}

			return helperCompatUpdate::$_instance;
		}

		/**
		 * @since 6.0
		 */
		public function __construct()
		{
			add_filter( 'avf_admin_notices_definition_files', [ $this, 'handler_avf_admin_notices_definition_files' ], 10, 1 );

			$this->add_update_filters();
		}

		/**
		 * Add admin notice definition files
		 *
		 * @since 6.0
		 * @param array $files
		 */
		public function handler_avf_admin_notices_definition_files( array $files )
		{
			$files[] = trailingslashit( dirname( __FILE__ ) ) . 'admin-notices/admin-notices-def-enfold.php';

			return $files;
		}

		/**
		 * Add the update scripts. Will be triggered so multiple version jumps are possible
		 *
		 * @since 6.0
		 */
		protected function add_update_filters()
		{
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_2_6' ], 10, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_3_0' ], 11, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_3_1' ], 12, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_3_1_4' ], 13, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_4_0' ], 14, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_4_1' ], 15, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_4_3' ], 16, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_4_4' ], 17, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_4_4_1' ], 18, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_4_5_7_2' ], 19, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_4_6_2' ], 20, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_4_8_4_1' ], 21, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_4_8_8' ], 22, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_4_9' ], 23, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_5_3' ], 24, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_6_0' ], 25, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_6_0_1' ], 26, 2 );
			add_action( 'ava_trigger_updates', [ $this, 'handler_update_7_0' ], 27, 2 );
			
		}


		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_2_6( $prev_version, $new_version )
		{
			//if the previous theme version is equal or bigger to 2.6 we don't need to update
			if( version_compare( $prev_version, '2.6', ">=") )
			{
				return;
			}

			include( 'update_scripts/v2_6.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_3_0( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '3.0', ">=") )
			{
				return;
			}

			include( 'update_scripts/v3_0.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_3_1( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '3.1', ">=") )
			{
				return;
			}

			include( 'update_scripts/v3_1.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_3_1_4( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '3.1.4', ">=") )
			{
				return;
			}

			include( 'update_scripts/v3_1_4.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_4_0( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '4.0', ">=") )
			{
				return;
			}

			include( 'update_scripts/v4_0.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_4_1( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '4.1', ">=") )
			{
				return;
			}

			include( 'update_scripts/v4_1.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_4_3( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '4.3', ">=") )
			{
				return;
			}

			include( 'update_scripts/v4_3.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_4_4( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '4.4', ">=") )
			{
				return;
			}

			include( 'update_scripts/v4_4.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_4_4_1( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '4.4.1', ">=") )
			{
				return;
			}

			include( 'update_scripts/v4_4_1.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_4_5_7_2( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '4.5.7.2', ">=" ) )
			{
				return;
			}

			include( 'update_scripts/v4_5_7_2.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_4_6_2( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '4.6.2', ">=" ) )
			{
				return;
			}

			\aviaFramework\avia_AdminNotices()->add_notice( 'gdpr_update_2', time() + \aviaFramework\avia_AdminNotices()->get_default_expire_time( 'handler_update_4_6_2' ) );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_4_8_4_1( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '4.8.4.1', ">=" ) )
			{
				return;
			}

			include( 'update_scripts/v4_8_4_1.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_4_8_8( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '4.8.8', ">=" ) )
			{
				return;
			}

			include( 'update_scripts/v4_8_8.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_4_9( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '4.9', ">=" ) )
			{
				return;
			}

			include( 'update_scripts/v4_9.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_5_3( $prev_version, $new_version )
		{
			if( version_compare( $prev_version, '5.3', ">=" ) )
			{
				return;
			}

			include( 'update_scripts/v5_3.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_6_0( $prev_version, $new_version )
		{
			//if the previous theme version is equal or bigger to 6.0 we don't need to update
			if( version_compare( $prev_version, '6.0', ">=" ) )
			{
				return;
			}

			include( 'update_scripts/v6_0.php' );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_6_0_1( $prev_version, $new_version )
		{
			//if the previous theme version is equal or bigger to 6.0.1 we don't need to update
			if( version_compare( $prev_version, '6.0.1', ">=" ) )
			{
				return;
			}

			\aviaFramework\avia_AdminNotices()->add_notice( 'enfold_601_welcome', time() + \aviaFramework\avia_AdminNotices()->get_default_expire_time( 'handler_update_6_0_1' ) );
			\aviaFramework\updates\aviaThemeDataUpdater()->show_default_notice( false );
		}

		/**
		 *
		 * @param string $prev_version
		 * @param string $new_version
		 */
		public function handler_update_7_0( $prev_version, $new_version )
		{
			//if the previous theme version is equal or bigger to 7.0 we don't need to update
			if( version_compare( $prev_version, '7.0', ">=" ) )
			{
				return;
			}

			\aviaFramework\avia_AdminNotices()->add_notice( 'enfold_70_welcome', time() + \aviaFramework\avia_AdminNotices()->get_default_expire_time( 'handler_update_7_0' ) );
			\aviaFramework\updates\aviaThemeDataUpdater()->show_default_notice( false );
		}
	}

	/**
	 * Returns the main instance of \enfold\updates\helperCompatUpdate to prevent the need to use globals.
	 *
	 * @since 6.0
	 * @return \enfold\updates\helperCompatUpdate
	 */
	function avia_helperCompatUpdate()
	{
		return helperCompatUpdate::instance();
	}

	//	activate class
	avia_helperCompatUpdate();

}

