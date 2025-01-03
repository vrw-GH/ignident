<?php
/**
 * How to Use:
 * Pointers are defined in an associative array and passed to the class upon instantiation.
 * First we hook into the 'admin_enqueue_scripts' hook with our function:
 *
 *   add_action('admin_enqueue_scripts', 'myHelpPointers');
 *
 *   function myHelpPointers() {
 *      //First we define our pointers
 *      $pointers = array(
 *                       array(
 *                           'id' => 'xyz123',   // unique id for this pointer
 *                           'screen' => 'page', // this is the page hook we want our pointer to show on
 *                           'target' => '#element-selector', // the css selector for the pointer to be tied to, best to use ID's
 *                           'title' => 'My ToolTip',
 *                           'content' => 'My tooltips Description',
 *                           'position' => array(
 *                                              'edge' => 'top', //top, bottom, left, right
 *                                              'align' => 'middle' //top, bottom, left, right, middle
 *                                              )
 *                           )
 *                        // more as needed
 *                        );
 *      //Now we instantiate the class and pass our pointer array to the constructor
 *      $myPointers = new WP_Help_Pointer($pointers);
 *    }
 *
 *
 * @package AviaPointer
 * @version 0.1
 * @author Tim Debo <tim@rawcreativestudios.com>
 * @copyright Copyright (c) 2012, Raw Creative Studios
 * @link https://github.com/rawcreative/wp-help-pointers
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since ????
 * @since 5.3			small modifications
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( 'AviaPointer', false ) )
{
	class AviaPointer
	{
		/**
		 * @since ????
		 * @var boolean|string
		 */
		public $screen_id;

		/**
		 * @since ????
		 * @var array
		 */
		public $valid;

		/**
		 * @since ????
		 * @var array
		 */
		public $pointers;

		/**
		 * @since ????
		 * @param array $pntrs
		 */
		public function __construct( $pntrs = array() )
		{
			$this->screen_id = false;
			$this->valid = array();
			$this->pointers = array();

			// Don't run on WP < 3.3
			if ( get_bloginfo( 'version' ) < '3.3' || ! function_exists( 'get_current_screen' ) )
			{
				return;
			}

			$screen = get_current_screen();
			$this->screen_id = isset( $screen->id ) ? $screen->id : false;

			$this->register_pointers( $pntrs );

			add_action( 'admin_enqueue_scripts', array( $this, 'add_pointers' ), 1000 );
			add_action( 'admin_head', array( $this, 'add_scripts' ) );
		}

		/**
		 * @since 5.3
		 */
		public function __destruct()
		{
			unset( $this->valid );
			unset( $this->pointers );
		}

		/**
		 * @since ????
		 * @param array $pntrs
		 */
		protected function register_pointers( $pntrs )
		{
			foreach( $pntrs as $ptr )
			{
				if( $ptr['screen'] == $this->screen_id )
				{
					$pointers[ $ptr['id'] ] = array(
												'screen'	=> $ptr['screen'],
												'target'	=> $ptr['target'],
												'options'	=> array(
																'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
																						__( $ptr['title'] , 'avia_framework' ),
																						__( $ptr['content'], 'avia_framework' )
																					),
												'position'	=> $ptr['position']
						)
					);

				}
			}

			if( ! empty( $pointers ) )
			{
				$this->pointers = $pointers;
			}
		}

		/**
		 * @since ????
		 */
		public function add_pointers()
		{
			$pointers = $this->pointers;

			if ( ! $pointers || ! is_array( $pointers ) || 0 == count( $pointers ) )
			{
				return;
			}

			// Get dismissed pointers
			$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

			$valid_pointers = array();

			// Check pointers and remove dismissed ones.
			foreach( $pointers as $pointer_id => $pointer )
			{
				// Make sure we have pointers & check if they have been dismissed
				if ( in_array( $pointer_id, $dismissed ) || empty( $pointer ) || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
				{
					continue;
				}

				$pointer['pointer_id'] = $pointer_id;

				// Add the pointer to $valid_pointers array
				$valid_pointers['pointers'][] =  $pointer;
			}

			// No valid pointers? Stop here.
			if ( empty( $valid_pointers ) )
			{
				return;
			}

			$this->valid = $valid_pointers;

			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
		}

		/**
		 * @since ????
		 */
		public function add_scripts()
		{
			$pointers = $this->valid;

			if( empty( $pointers ) )
			{
				return;
			}

			$pointers = json_encode( $pointers );

			echo <<<HTML
        <script>
        jQuery( function($) {
            var WPHelpPointer = {$pointers};

            $.each(WPHelpPointer.pointers, function(i) {
                wp_help_pointer_open(i);
            });

            function wp_help_pointer_open(i) {
                pointer = WPHelpPointer.pointers[i];
                options = $.extend( pointer.options, {
                    close: function() {
                        $.post( ajaxurl, {
                            pointer: pointer.pointer_id,
                            action: 'dismiss-wp-pointer'
                        });
                    }
                });
                $(pointer.target).pointer( options ).pointer('open');
            }
        });
        </script>
HTML;

		}

	} // end class

}
