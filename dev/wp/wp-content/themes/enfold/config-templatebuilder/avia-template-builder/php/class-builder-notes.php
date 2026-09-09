<?php
/**
 * Dismissable notes above the Layout Builder
 * ==========================================
 *
 * The note that appears above the builder button is filtered in through
 * 'avf_builder_button_params'. A note that only gives advice becomes dismissable simply
 * by adding a 'note_key' to those params - without a key nothing changes, so notes that
 * explain why a button is disabled stay on screen where they belong.
 *
 * The dismissed list is stored with update_user_option() rather than user meta, so it is
 * kept per user and per site. Plain user meta is shared across a whole multisite network,
 * which would hide a note on every site of the network at once.
 *
 * @since 8.0
 */

if( ! defined( 'AVIA_FW' ) ) { exit( 'No direct script access allowed' ); }


if( ! class_exists( 'aviaBuilderNotes', false ) )
{
	class aviaBuilderNotes
	{
		/**
		 * Per user, per site list of note keys the user has closed
		 */
		const OPT_DISMISSED = 'avia_builder_notes_dismissed';

		/**
		 * @since 8.0
		 * @var aviaBuilderNotes|null
		 */
		static private $_instance = null;

		/**
		 * @since 8.0
		 * @var array|null
		 */
		private $dismissed = null;

		/**
		 * @since 8.0
		 * @return aviaBuilderNotes
		 */
		static public function instance()
		{
			if( is_null( aviaBuilderNotes::$_instance ) )
			{
				aviaBuilderNotes::$_instance = new aviaBuilderNotes();
			}

			return aviaBuilderNotes::$_instance;
		}

		/**
		 * @since 8.0
		 */
		protected function __construct()
		{
			add_action( 'wp_ajax_avia_dismiss_builder_note', array( $this, 'handler_ajax_dismiss' ), 10 );
		}

		/**
		 * @since 8.0
		 */
		public function __destruct()
		{
			unset( $this->dismissed );
		}

		/**
		 * Note keys the current user has closed on this site.
		 *
		 * @since 8.0
		 * @return array
		 */
		public function get_dismissed()
		{
			if( is_null( $this->dismissed ) )
			{
				$user_id = get_current_user_id();
				$stored = $user_id > 0 ? get_user_option( aviaBuilderNotes::OPT_DISMISSED, $user_id ) : array();

				$this->dismissed = is_array( $stored ) ? $stored : array();
			}

			return $this->dismissed;
		}

		/**
		 * @since 8.0
		 * @param string $key
		 * @return boolean
		 */
		public function is_dismissed( $key )
		{
			$key = $this->sanitize_key( $key );

			if( '' === $key )
			{
				return false;
			}

			return in_array( $key, $this->get_dismissed(), true );
		}

		/**
		 * Builds the note markup.
		 *
		 * Returns an empty string when the note has a key the user already closed - the
		 * caller can then skip the wrapper altogether instead of printing an empty box.
		 *
		 * @since 8.0
		 * @param string $note				html of the message
		 * @param string $noteclass			extra classes for the wrapper
		 * @param string $key				'' for a note that cannot be dismissed
		 * @return string
		 */
		public function get_note_html( $note, $noteclass = '', $key = '' )
		{
			if( '' === trim( (string) $note ) )
			{
				return '';
			}

			$key = $this->sanitize_key( $key );

			if( '' !== $key && $this->is_dismissed( $key ) )
			{
				return '';
			}

			$classes = trim( 'av-builder-note ' . $noteclass );
			$data = '';
			$button = '';

			if( '' !== $key )
			{
				$classes .= ' av-builder-note-dismissable';
				$data = " data-av-note-key='" . esc_attr( $key ) . "'";

				$button  = "<button type='button' class='av-builder-note-dismiss' aria-label='" . esc_attr__( 'Dismiss this note', 'avia_framework' ) . "'>";
				$button .=		"<span class='dashicons dashicons-no-alt' aria-hidden='true'></span>";
				$button .= '</button>';
			}

			return "<div class='{$classes}'{$data}>{$note}{$button}</div>";
		}

		/**
		 * Stores that the user closed a note.
		 *
		 * @since 8.0
		 */
		public function handler_ajax_dismiss()
		{
			$response = array( 'success' => false, 'message' => '' );

			if( false === check_ajax_referer( 'avia_nonce_loader', '_ajax_nonce', false ) )
			{
				$response['message'] = __( 'Expired nonce', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			//	the note only ever appears on an edit screen, so this is the matching capability
			if( ! current_user_can( 'edit_posts' ) )
			{
				$response['message'] = __( 'Sorry, you have not enough rights to perform requested action.', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			$user_id = get_current_user_id();
			$key = isset( $_POST['note_key'] ) ? $this->sanitize_key( wp_unslash( $_POST['note_key'] ) ) : '';

			if( $user_id <= 0 || '' === $key )
			{
				$response['message'] = __( 'Missing note key', 'avia_framework' );
				echo json_encode( $response );
				exit;
			}

			$dismissed = $this->get_dismissed();

			if( ! in_array( $key, $dismissed, true ) )
			{
				$dismissed[] = $key;
				update_user_option( $user_id, aviaBuilderNotes::OPT_DISMISSED, $dismissed );
				$this->dismissed = $dismissed;
			}

			$response['success'] = true;
			$response['message'] = $key;

			echo json_encode( $response );
			exit;
		}

		/**
		 * Restores all notes for a user - used by the theme options reset.
		 *
		 * @since 8.0
		 * @param int $user_id				0 for the current user
		 * @return boolean
		 */
		public function reset( $user_id = 0 )
		{
			$user_id = $user_id > 0 ? $user_id : get_current_user_id();

			if( $user_id <= 0 )
			{
				return false;
			}

			$this->dismissed = array();

			return (bool) update_user_option( $user_id, aviaBuilderNotes::OPT_DISMISSED, array() );
		}

		/**
		 * Keys come from theme code and from ajax, so both go through the same filter.
		 *
		 * @since 8.0
		 * @param string $key
		 * @return string
		 */
		protected function sanitize_key( $key )
		{
			if( ! is_string( $key ) )
			{
				return '';
			}

			return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
		}
	}
}

if( ! function_exists( 'Avia_Builder_Notes' ) )
{
	/**
	 * @since 8.0
	 * @return aviaBuilderNotes
	 */
	function Avia_Builder_Notes()
	{
		return aviaBuilderNotes::instance();
	}
}
