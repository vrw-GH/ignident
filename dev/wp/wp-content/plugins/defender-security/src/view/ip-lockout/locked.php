<?php
/**
 * This template is used to display locked IP email.
 *
 * @package WP_Defender
 */

 /**
  * WPCS errors in this page are,
  * 1) Stylesheets must be registered/enqueued via wp_enqueue_style()
  * 2) Script must be registered/enqueued via wp_enqueue_script()
  * @codingStandardsIgnoreFile
  */
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>
		<?php
		$devman_img  = defender_asset_url( '/assets/img/def-stand.svg' );
		$devman_icon = defender_asset_url( '/assets/img/defender-30.svg' );
		$info        = defender_white_label_status();
		if ( strlen( $info['hero_image'] ) > 0 ) {
			$devman_img = $info['hero_image'];
		}
		bloginfo( 'name' )
		?>
	</title>
	<link rel="stylesheet"
			href="https://fonts.bunny.net/css?family=Roboto+Condensed:400,700|Roboto:400,500,300,300italic">
	<link rel="stylesheet" href="<?php defender_asset_url( '/assets/css/styles.css', true ); ?>">
	<style>
		html,
		body {
			margin: 0;
			padding: 0;
		}

		.wp-defender {
			display: grid;
			place-content: center;
			font-family: Roboto, sans-serif;
			color: #000;
			font-size: 13px;
			line-height: 18px;
			min-height: 100vh; /* Ensure content fills at least the viewport height */
		}

		.container {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			gap: 30px;
			max-width: 100%; /* Ensure container doesn't overflow on smaller screens */
			padding: 0 20px; /* Add padding for better readability and spacing */
		}

		.image,
		.plugin-icon {
			border-radius: 50%;
			background-repeat: no-repeat;
			background-size: contain;
			background-position: center;
			max-width: 100%; /* Ensure image doesn't overflow its container */
		}

		.image {
			width: 128px;
			height: 128px;
			background-image: url("<?php echo esc_url_raw( $devman_img ); ?>");
		}

		.plugin-icon {
			width: 30px;
			height: 30px;
			background-image: url("<?php echo esc_url_raw( $devman_icon ); ?>");
		}

		.powered {
			display: grid;
			justify-content: center;
			gap: 10px;
			font-size: 10px;
			color: #C0C0C0;
			justify-items: center;
		}

		.powered strong {
			color: #8A8A8A;
		}

		.locked_page_header {
			color: #333333;
		}

		.message {
			text-align: center;
			font-size: 15px;
			line-height: 30px;
			color: #666666;
		}

		#countdown-time {
			font-weight: bold;
			font-size: 28px;
			line-height: 40px;
			color: #666666;
			display: inline-flex;
			gap: 6px;
		}

		#remaining-time {
			margin-left: 10px;
		}

		.sui-icon-stopwatch::before {
			color: inherit !important;
			font-size: 24px !important;
		}

		.day-notation {
			font-weight: normal;
		}

		#wd_step_show_success .success_icon {
			width: 60px;
			height: 60px;
			margin: 0 auto;
			background-image: url("<?php defender_asset_url( '/assets/img/green-icon.svg', true ); ?>");
			background-repeat: no-repeat;
			background-size: contain;
			background-position: center;
		}

	</style>
	<?php if ( ! empty( $is_unlock_me ) ) { ?>
		<link rel="stylesheet" href="<?php echo esc_url( defender_asset_url( '/assets/css/unlock.css' ) ); ?>">
		<script src="<?php echo esc_url_raw( includes_url( '/js/jquery/jquery.min.js' ) ); ?>"></script>
	<?php } ?>
</head>

<body class="<?php echo esc_attr( 'sui-' . DEFENDER_SUI ); ?>">
<div class="wp-defender">
	<div class="container">
		<?php
		if (
			( false === $info['hide_branding'] ) ||
			( true === $info['hide_branding'] && ! empty( $info['hero_image'] ) )
		) {
			echo '<div class="image"></div>';
		}
		?>
		<h1 class="locked_page_header"><?php esc_html_e( 'Access Denied', 'defender-security' ); ?></h1>
		<p class="message">
			<?php
			echo wp_kses_post( $message ) . '<br/>';
			if ( ! empty( $is_unlock_me ) ) {
				printf(
				/* translators: %s: Button title. */
					esc_html__(
						'If you are a site admin, click on the %s button below to unlock yourself.',
						'defender-security'
					),
					'<strong>' . esc_html( $button_title ) . '</strong>',
				);
			}
			?>
		</p>
		<?php if ( ! empty( $is_unlock_me ) ) { ?>
			<div class="unlock_wrap sui-wrap">
				<!--Step#1-->
				<button type="button" id="wd_step_show_toggle"
						class="sui-button sui-button-lg sui-button-blue"
					<?php disabled( $button_disabled, true ); ?>
				>
					<i class="sui-icon-lock" aria-hidden="true"></i>
					<?php echo esc_html( $button_title ); ?>
				</button>
				<!--Step#2-->
				<form method="post" class="sui-box unlock_section" id="wd_step_show_form" action="">
					<div class="sui-form-field">
						<label for="unlock_user_field" id="label-unlock_user_field" class="sui-label">
							<?php esc_html_e( 'Enter your registered username or email', 'defender-security' ); ?>
						</label>
						<div class="sui-row">
							<div class="sui-col-md-9">
								<input type="text"
										placeholder="<?php esc_attr_e( 'Enter your username or email.', 'defender-security' ); ?>"
										id="unlock_user_field"
										class="sui-form-control"
										aria-labelledby="label-unlock_user_field"
								/>
							</div>
							<div class="sui-col-md-3">
								<button type="button" class="sui-button sui-button-lg sui-button-blue"
										id="wd_verify_user">
									<?php echo esc_html( $button_title ); ?>
								</button>
							</div>
						</div>
					</div>
				</form>
				<!--Step#3-->
				<div class="sui-box unlock_section" id="wd_step_show_success">
					<div class="success_icon"></div>
					<p id="unlock_sent_email">
						<?php
						esc_html_e(
							'If the username/email exists, an email will be sent to your registered email address.',
							'defender-security'
						);
						?>
					</p>
					<p>
						<?php
						printf(
						/* translators: %s: Resend link. */
							esc_html__( 'Didn\'t get the email? %s.', 'defender-security' ),
							'<a href="" id="unlock_sent_again_link">' . esc_html__( 'Try again', 'defender-security' ) . '</a>'
						);
						?>
					</p>
				</div>

			</div>
		<?php } ?>
		<?php if ( ! empty( $remaining_time ) && is_int( $remaining_time ) && $remaining_time > 0 ) { ?>
			<p class="message"><?php esc_html_e( 'You will be able to attempt to access again in:', 'defender-security' ); ?></p>
			<p id="countdown-time"><span class="sui-icon-stopwatch" aria-hidden="true"></span><span
						id="remaining-time"></span></p>
		<?php } ?>
	</div>
	<?php if ( ! $info['hide_doc_link'] ) { ?>
		<div class="powered">
			<div class="plugin-icon"></div>
			<div>
				<?php esc_html_e( 'Powered by', 'defender-security' ); ?>
				<strong><?php esc_html_e( 'Defender', 'defender-security' ); ?></strong>
			</div>
		</div>
		<?php
	}
	?>
</div>
<?php if ( ! empty( $remaining_time ) && is_int( $remaining_time ) && $remaining_time > 0 ) { ?>
	<script>
		function CountDownTimer(duration, granularity) {
			this.duration = duration;
			this.granularity = granularity || 1000;
			this.tickFtns = [];
			this.running = false;
		}

		CountDownTimer.prototype.start = function () {
			if (this.running) {
				return;
			}
			this.running = true;
			var start = Date.now(),
				that = this,
				diff, obj;

			(function timer() {
				diff = that.duration - (((Date.now() - start) / 1000) | 0);

				if (diff > 0) {
					setTimeout(timer, that.granularity);
				} else {
					diff = 0;
					that.running = false;
				}

				obj = CountDownTimer.parse(diff);
				that.tickFtns.forEach(function (ftn) {
					ftn.call(this, obj);
				}, that);
			}());
		};

		CountDownTimer.prototype.onTick = function (ftn) {
			if (typeof ftn === 'function') {
				this.tickFtns.push(ftn);
			}
			return this;
		};

		CountDownTimer.prototype.expired = function () {
			return !this.running;
		};

		CountDownTimer.parse = function (seconds) {
			const DAY_IN_SECONDS = 86400;
			const HOUR_IN_SECONDS = 3600;
			const MINUTES_IN_SECONDS = 60;

			seconds = Number(seconds);

			let days = Math.floor(seconds / DAY_IN_SECONDS);

			let dayNotation = days > 1 ? 'Days' : 'Day';

			let displayDays = days > 0 ? (days + '<span class="day-notation">&nbsp;' + dayNotation + '&nbsp;</span>') : '';

			seconds %= DAY_IN_SECONDS;
			let hours = String(Math.floor(seconds / HOUR_IN_SECONDS)).padStart(2, 0);

			seconds %= HOUR_IN_SECONDS;
			let minutes = String(Math.floor(seconds / MINUTES_IN_SECONDS)).padStart(2, 0);

			seconds = String(seconds % MINUTES_IN_SECONDS).padStart(2, 0);

			return displayDays + hours + ':' + minutes + ':' + seconds;
		};

		window.onload = function () {
			let display = document.getElementById("remaining-time"),
				timer = new CountDownTimer(<?php echo esc_attr( $remaining_time ); ?>);

			timer.onTick(format).onTick(pageRefresh).start();

			function pageRefresh() {
				if (this.expired()) {
					setTimeout(
						() => {
							window.location.href = window.location.href;
						},
						1000 // Intentional 1 second delay to allow browser parse headers and redirect.
					);
				}
			}

			function format(formattedTime) {
				display.innerHTML = formattedTime;
			}
		}
	</script>
<?php } ?>
<script>
	<?php if ( ! empty( $is_unlock_me ) ) { ?>
	jQuery(function ($) {
		//Verify user.
		function verifyUser(that) {
			let userField = $.trim($('#unlock_user_field').val());
			//No action if the field is empty.
			if ('' == userField) {
				return;
			}
			let data = {
				data: JSON.stringify({
					'user_data': userField
				})
			};
			$.ajax({
				type: 'POST',
				url: '<?php echo $action_verify_blocked_user; ?>',
				data: data,
				beforeSend: function () {
					that.prop('disabled', true);
				},
				success: function (response) {
					// Enable button.
					that.prop('disabled', false);
					// Hide the current step and show the next one.
					$('#wd_step_show_form').hide();
					$('#wd_step_show_success').show();
				},
				error: function (e) {
					console.log('Unexpected error occurred: ', e);
				}
			})
		}

		//Show a form for communication with the user.
		$('body').on('click', '#wd_step_show_toggle', function () {
			$(this).hide();
			$('#wd_step_show_form').show();
		});
		// Verify a blocked user.
		$('body').on('click', '#wd_verify_user', function (e) {
			e.preventDefault();
			verifyUser($(this));
		});
		$(window).on('keydown', function (event) {
			if (event.keyCode == 13 && jQuery(event.target).attr('id') === 'unlock_user_field') {
				verifyUser(jQuery(event.target))
			}
		});
		//Show the form again.
		$('body').on('click', '#unlock_sent_again_link', function (e) {
			let that = $(this);
			e.preventDefault();
			//Check the attempt limit.
			$.ajax({
				type: 'POST',
				url: '<?php echo $action_send_again; ?>',
				data: {},
				success: function (response) {
					if (response.success === false) {
						location.reload();
					}
					$('#wd_step_show_success').hide();
					$('#wd_step_show_form').show();
				}
			});
		});
	})
	<?php } ?>
</script>
</body>
</html>
