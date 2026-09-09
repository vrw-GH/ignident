<?php
/**
 * Tells a customer their update period is ending, before it does.
 *
 * This is the renewal driver, so it is also the piece most likely to be resented.
 * Two rules keep it on the right side of that line:
 *
 *  1. It NEVER disables anything. A licence that has run out stops updates and
 *     support; the theme itself keeps working exactly as before. Every string
 *     here says so, because a customer who thinks their site is about to break
 *     is a support ticket, not a renewal.
 *
 *  2. It only speaks when there is something to act on. A year of runway does not
 *     need a countdown, so nothing appears until thirty days out, and a dismissed
 *     notice stays dismissed until the next threshold genuinely changes the
 *     situation.
 *
 * The word "re-purchase" appears nowhere. They are renewing something they own.
 *
 * Reading the licence is deliberately cheap. `revokes_at` is a fixed date rather
 * than a moving state, so it is stored once at verification and simply compared
 * against the clock — no API call on an admin page load. The one exception is the
 * final thirty days, where a stale date would keep nagging somebody who has
 * already renewed; there, and only there, it re-checks at most once a day.
 *
 * @since 8.1
 * @package Avia_Framework
 */

if( ! defined( 'ABSPATH' ) ) {	exit;	}


if( ! class_exists( 'Avia_Licence_Reminder', false ) )
{
	class Avia_Licence_Reminder
	{
		/**
		 * Where the licence state is remembered between page loads.
		 */
		const OPTION = 'avia_licence_state';

		/**
		 * Which thresholds an admin has already waved away.
		 */
		const USER_META = 'avia_licence_reminder_dismissed';

		/**
		 * Where a customer goes to renew.
		 *
		 * The Plans page of our customer area, not the shop and not the dashboard
		 * root. That is deliberate: from Plans a customer can turn automatic
		 * renewal back ON, and that is the ONLY action that keeps the licence key
		 * they already hold. Buying again mints a NEW key, which means re-entering
		 * it on every site they own.
		 *
		 * The list view rather than a single subscription, because this URL ships
		 * to every customer and a record id would belong to exactly one of them.
		 * The theme has no way to derive their own - the public licence endpoint
		 * returns the product, price and release, but no purchase or subscription
		 * reference.
		 *
		 * Overridable via the avf_enfold_renew_url filter for anyone reselling or
		 * testing against another store.
		 */
		const RENEW_URL = 'https://kriesi.at/support/customer-dashboard/?action=index&model=subscription';

		/**
		 * Where a customer goes once the period has ALREADY ended.
		 *
		 * A different destination on purpose. Restoring a subscription keeps the
		 * existing licence key, but restoring is only offered while the
		 * subscription is still running - once it has genuinely lapsed there is
		 * nothing left to restore, and the only route back is a new purchase,
		 * which mints a NEW key.
		 *
		 * Sending a lapsed customer to the Plans page would be a dead end: nothing
		 * there to act on. This goes straight to a checkout instead - the fewest
		 * clicks between deciding to renew and having done it.
		 *
		 * Deliberately carries NO price id. Price ids change, and this string
		 * ships inside a theme on customers' own sites where we cannot update it.
		 * An empty checkout is seeded server-side with whatever we currently sell
		 * - see AviaShop\DefaultLineItem - so the link keeps working when the
		 * price behind it is replaced.
		 *
		 * The trailing slash is deliberate: without it WordPress 301s, and a
		 * redirect in a link we hand a paying customer is a needless round trip.
		 */
		const RENEW_URL_ENDED = 'https://kriesi.at/support/checkout/';

		const LEVEL_NONE = '';
		const LEVEL_MONTH = 'month';
		const LEVEL_WEEK = 'week';
		const LEVEL_ENDED = 'ended';

		/**
		 * Hook the admin notice up.
		 *
		 * Only ever called from the admin side. Nothing here runs on a front end
		 * request, and nothing here runs for a user who could not act on it anyway.
		 *
		 * @since 8.1
		 */
		static public function register()
		{
			if( ! is_admin() )
			{
				return;
			}

			add_action( 'admin_notices', array( __CLASS__, 'render_notice' ) );
			add_action( 'wp_ajax_avia_dismiss_licence_reminder', array( __CLASS__, 'ajax_dismiss' ) );
		}

		/**
		 * @since 8.1
		 */
		static public function render_notice()
		{
			/**
			 * Only people who could actually renew. Showing this to an editor is
			 * noise they cannot act on, and it leaks commercial state to somebody
			 * who has no business seeing it.
			 */
			if( ! current_user_can( 'update_themes' ) )
			{
				return;
			}

			$state = self::stored_state();

			if( empty( $state ) )
			{
				return;
			}

			$days = isset( $state['days_remaining'] ) ? $state['days_remaining'] : null;
			$status = isset( $state['status'] ) ? $state['status'] : '';

			/**
			 * The stored day count was correct when it was written and drifts as the
			 * clock moves, so it is recomputed from the date rather than trusted.
			 * Without this the notice would appear a day late for every day the
			 * licence went unchecked.
			 */
			if( ! empty( $state['revokes_at'] ) )
			{
				$days = (int) floor( ( (int) $state['revokes_at'] - time() ) / DAY_IN_SECONDS );
			}

			$level = self::notice_level( $days, $status );

			if( self::LEVEL_NONE === $level )
			{
				return;
			}

			if( self::is_dismissed( $level, get_user_meta( get_current_user_id(), self::USER_META, true ) ) )
			{
				return;
			}

			$date = empty( $state['revokes_at'] ) ? '' : date_i18n( get_option( 'date_format' ), (int) $state['revokes_at'] );
			$message = self::message( $level, $date, $days );

			if( '' === $message )
			{
				return;
			}

			$url = self::renew_url( $level );
			$class = self::LEVEL_MONTH === $level ? 'notice-info' : 'notice-warning';

			echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible av-licence-reminder" data-av-level="' . esc_attr( $level ) . '">';
			echo '<p>' . esc_html( $message );

			if( '' !== $url )
			{
				echo ' <a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Renew your licence', 'avia_framework' ) . '</a>';
			}

			echo '</p></div>';

			self::dismiss_script();
		}

		/**
		 * Remember a dismissal server side.
		 *
		 * WordPress's own dismiss button only hides the box for that page load, so
		 * without this the same notice returns on the next click and the reminder
		 * reads as broken rather than persistent.
		 *
		 * @since 8.1
		 */
		static protected function dismiss_script()
		{
			$nonce = wp_create_nonce( 'avia_dismiss_licence_reminder' );

			echo "<script>
			(function(){
				var box = document.querySelector( '.av-licence-reminder' );
				if( ! box ){ return; }
				box.addEventListener( 'click', function( e ){
					if( ! e.target.classList.contains( 'notice-dismiss' ) ){ return; }
					var body = new FormData();
					body.append( 'action', 'avia_dismiss_licence_reminder' );
					body.append( 'level', box.getAttribute( 'data-av-level' ) );
					body.append( '_wpnonce', '" . esc_js( $nonce ) . "' );
					fetch( ajaxurl, { method: 'POST', credentials: 'same-origin', body: body } );
				});
			})();
			</script>";
		}

		/**
		 * @since 8.1
		 */
		static public function ajax_dismiss()
		{
			check_ajax_referer( 'avia_dismiss_licence_reminder' );

			if( ! current_user_can( 'update_themes' ) )
			{
				wp_send_json_error();
			}

			$level = isset( $_POST['level'] ) ? sanitize_text_field( wp_unslash( $_POST['level'] ) ) : '';

			if( ! in_array( $level, array( self::LEVEL_MONTH, self::LEVEL_WEEK, self::LEVEL_ENDED ), true ) )
			{
				wp_send_json_error();
			}

			$user_id = get_current_user_id();
			$dismissed = get_user_meta( $user_id, self::USER_META, true );
			$dismissed = is_array( $dismissed ) ? $dismissed : array();
			$dismissed[ $level ] = 1;

			update_user_meta( $user_id, self::USER_META, $dismissed );

			wp_send_json_success();
		}

		/**
		 * Which notice, if any, this licence deserves right now.
		 *
		 * Pure, so the thing most likely to be wrong is the thing easiest to test.
		 * Getting it wrong in the quiet direction loses a renewal; getting it wrong
		 * in the loud direction nags a customer who has eleven months left.
		 *
		 * @since 8.1
		 * @param int|null $days		Days until the update period ends. Negative once past.
		 * @param string $status		As reported by Avia_SureCart_Licence::status().
		 * @return string				One of the LEVEL_* constants.
		 */
		static public function notice_level( $days, $status )
		{
			/**
			 * A revoked licence is a decision somebody made - a refund, a chargeback -
			 * rather than a date passing, so it is worth saying whatever the clock reads.
			 */
			if( Avia_SureCart_Licence::STATUS_REVOKED === $status )
			{
				return self::LEVEL_ENDED;
			}

			if( Avia_SureCart_Licence::STATUS_EXPIRED === $status )
			{
				return self::LEVEL_ENDED;
			}

			/**
			 * Anything we could not read stays silent. A customer whose licence check
			 * failed for an unrelated reason must not be told their updates are ending.
			 */
			if( Avia_SureCart_Licence::STATUS_VALID !== $status || is_null( $days ) )
			{
				return self::LEVEL_NONE;
			}

			if( $days < 0 )
			{
				return self::LEVEL_ENDED;
			}

			if( $days <= 7 )
			{
				return self::LEVEL_WEEK;
			}

			if( $days <= 30 )
			{
				return self::LEVEL_MONTH;
			}

			return self::LEVEL_NONE;
		}

		/**
		 * Has this admin already waved this notice away?
		 *
		 * Dismissal is per level, not global. Somebody who dismisses the thirty day
		 * notice has decided to deal with it later, not to never hear about it -
		 * and the week notice is a different message about a different situation.
		 *
		 * @since 8.1
		 * @param string $level
		 * @param mixed $dismissed		Whatever the user meta held.
		 * @return boolean
		 */
		static public function is_dismissed( $level, $dismissed )
		{
			if( self::LEVEL_NONE === $level || ! is_array( $dismissed ) )
			{
				return false;
			}

			return ! empty( $dismissed[ $level ] );
		}

		/**
		 * Should we spend an API call refreshing the stored date?
		 *
		 * Only inside the window where the answer can still change what we show. A
		 * customer who renews on the last day should stop seeing the notice without
		 * having to find the licence screen, and outside those thirty days a stored
		 * date cannot be wrong in a way that matters.
		 *
		 * @since 8.1
		 * @param mixed $state			The stored licence state.
		 * @param int $now
		 * @return boolean
		 */
		static public function needs_refresh( $state, $now )
		{
			if( ! is_array( $state ) || ! isset( $state['days_remaining'] ) )
			{
				return false;
			}

			$days = $state['days_remaining'];

			if( ! is_null( $days ) && $days > 30 )
			{
				return false;
			}

			$checked = isset( $state['checked_at'] ) ? (int) $state['checked_at'] : 0;

			return ( (int) $now - $checked ) >= DAY_IN_SECONDS;
		}

		/**
		 * Remember what a licence check told us.
		 *
		 * @since 8.1
		 * @param array $info			As returned by verify_credential().
		 * @param int $now
		 * @return array				The stored state.
		 */
		static public function remember( array $info, $now = null )
		{
			$now = is_null( $now ) ? time() : (int) $now;

			$state = array(
					'status'			=> isset( $info['licence_status'] ) ? (string) $info['licence_status'] : Avia_SureCart_Licence::STATUS_UNKNOWN,
					'revokes_at'		=> isset( $info['revokes_at'] ) && ! is_null( $info['revokes_at'] ) ? (int) $info['revokes_at'] : null,
					'days_remaining'	=> isset( $info['days_remaining'] ) && ! is_null( $info['days_remaining'] ) ? (int) $info['days_remaining'] : null,
					'checked_at'		=> $now
				);

			update_option( self::OPTION, $state, false );

			/**
			 * A fresh check describes a new situation, so earlier dismissals no longer
			 * apply. Without this, somebody who dismissed the week notice would never
			 * be told their renewal had failed to take.
			 */
			self::clear_dismissals();

			return $state;
		}

		/**
		 * @since 8.1
		 * @return array
		 */
		static public function stored_state()
		{
			$state = get_option( self::OPTION, array() );

			return is_array( $state ) ? $state : array();
		}

		/**
		 * @since 8.1
		 */
		static public function clear_dismissals()
		{
			$user_id = get_current_user_id();

			if( $user_id )
			{
				delete_user_meta( $user_id, self::USER_META );
			}
		}

		/**
		 * Where a customer goes to renew.
		 *
		 * Empty by default and rendered only when set, because a renewal prompt that
		 * links nowhere is worse than one with no link at all.
		 *
		 * @since 8.1
		 * @return string
		 */
		static public function renew_url( $level = '' )
		{
			/**
			 * A lapsed licence goes somewhere different, because it needs a
			 * different thing: a purchase rather than a restore. See the two
			 * constants above.
			 */
			$url = self::LEVEL_ENDED === $level ? self::RENEW_URL_ENDED : self::RENEW_URL;

			return esc_url_raw( (string) apply_filters( 'avf_enfold_renew_url', $url, $level ) );
		}

		/**
		 * The message for a given level.
		 *
		 * Kept beside the rules so the copy and the thresholds cannot drift apart.
		 *
		 * @since 8.1
		 * @param string $level
		 * @param string $date			Localised end date, may be empty.
		 * @param int|null $days
		 * @return string
		 */
		static public function message( $level, $date, $days )
		{
			switch( $level )
			{
				case self::LEVEL_ENDED:
					/**
					 * Says out loud that the key changes.
					 *
					 * A lapsed licence cannot be restored, so renewing means a new
					 * purchase and a new key - which the customer must enter on every
					 * site they run Enfold on. Discovering that after paying feels
					 * like a fault; being told before paying is just how it works.
					 */
					return '' !== $date
						? sprintf( __( 'Your Enfold update period ended on %s. Enfold keeps working - renew for another year of updates and support. You will receive a new licence key to enter.', 'avia_framework' ), $date )
						: __( 'Your Enfold update period has ended. Enfold keeps working - renew for another year of updates and support. You will receive a new licence key to enter.', 'avia_framework' );

				case self::LEVEL_WEEK:
				case self::LEVEL_MONTH:
					$days = (int) max( 0, (int) $days );

					return '' !== $date
						? sprintf(
								/* translators: 1: number of days, 2: end date */
								_n( 'Your Enfold update period ends in %1$s day, on %2$s. Enfold keeps working - renew to stay on updates and support.', 'Your Enfold update period ends in %1$s days, on %2$s. Enfold keeps working - renew to stay on updates and support.', $days, 'avia_framework' ),
								number_format_i18n( $days ),
								$date
							)
						: sprintf(
								_n( 'Your Enfold update period ends in %s day. Enfold keeps working - renew to stay on updates and support.', 'Your Enfold update period ends in %s days. Enfold keeps working - renew to stay on updates and support.', $days, 'avia_framework' ),
								number_format_i18n( $days )
							);
			}

			return '';
		}
	}
}
