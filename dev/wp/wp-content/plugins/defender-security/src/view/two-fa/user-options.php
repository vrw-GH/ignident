<?php
/**
 * This template is used to display 2FA user options.
 *
 * @package WP_Defender
 */

$admin_class = $is_admin ? 'admin_area' : '';
wp_nonce_field( 'wpdef_2fa_user_options', '_wpdef_2fa_nonce_user_options', false );
?>
<input type="hidden" name="<?php echo esc_attr( $enabled_providers_key ); ?>[]" value=""/>
<h2 class="defender-header"><?php esc_attr_e( 'Security', 'defender-security' ); ?></h2>
<table class="form-table" id="defender-security">
	<tr class="user-sessions-wrap hide-if-no-js">
		<th>
			<?php esc_attr_e( 'Two-Factor Authentication', 'defender-security' ); ?>
		</th>
		<td>
			<?php if ( $is_force_auth ) : ?>
				<div class="def-notification" style="margin-right: 0;margin-top: 0;">
					<i class="dashicons dashicons-warning" aria-hidden="true"></i>
					<?php echo wp_kses_post( ( ! empty( $force_auth_message ) ) ? $force_auth_message : $default_message ); ?>
				</div>
			<?php endif; ?>
			<table class="auth-methods-table">
				<thead>
				<tr>
					<th class="col-enabled" scope="col"><?php esc_html_e( 'Default', 'defender-security' ); ?></th>
					<th class="col-primary" scope="col" colspan="2">
						<?php esc_html_e( '2FA Method', 'defender-security' ); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ( $all_providers as $slug => $object ) :
					/**
					 * Fires before user options are shown.
					 *
					 * @param  WP_User  $user  The user.
					 *
					 * @since 2.8.0
					 */
					do_action( 'wd_2fa_init_provider_' . $slug, $user );
					?>
					<tr id="row-<?php echo esc_attr( $slug ); ?>">
						<th scope="row" class="radio-button">
							<input type="radio" name="<?php echo esc_attr( $default_provider_key ); ?>"
									value="<?php echo esc_attr( $slug ); ?>"
								<?php
								checked(
									$slug,
									$checked_def_provider_slug
								);
								?>
								<?php echo $webauthn_slug === $slug && ! $webauthn_requirements ? 'disabled="disabled" class="disabled"' : ''; ?> />
						</th>
						<th scope="row" class="toggles">
							<input type="checkbox"
									class="wpdef-ui-toggle<?php echo $webauthn_slug === $slug && ! $webauthn_requirements ? ' disabled' : ''; ?>"
									id="field-<?php echo esc_attr( $slug ); ?>"
									name="<?php echo esc_attr( $enabled_providers_key ); ?>[]"
									value="<?php echo esc_attr( $slug ); ?>"
								<?php checked( in_array( $slug, $checked_provider_slugs, true ) ); ?>
								<?php echo $webauthn_slug === $slug && ! $webauthn_requirements ? 'disabled="disabled"' : ''; ?> />
						</th>
						<td>
							<strong>
								<?php
								// It has button tag in src\component\two-factor\providers\class-totp.php.
								echo wp_kses_post( $object->get_label() );
								?>
							</strong>
							<p class="<?php echo esc_attr( $slug ); ?>-provider-text">
								<?php echo esc_html( $object->get_description() ); ?>
							</p>
							<?php
							/**
							 * Fires after user options are shown.
							 *
							 * @param  WP_User  $user  The user.
							 *
							 * @since 2.8.0
							 */
							do_action( 'wd_2fa_user_options_' . $slug, $user );
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</td>
	</tr>
</table>
<div class="defender-biometric-wrap" <?php echo true !== $webauthn_enabled ? 'style="display:none;"' : ''; ?>>
	<h3><?php esc_html_e( 'Registered Device', 'defender-security' ); ?></h3>
	<table class="form-table" id="defender-biometric-tbl">
		<thead>
		<tr>
			<th><?php esc_html_e( 'Identifier', 'defender-security' ); ?></th>
			<th><?php esc_html_e( 'Type', 'defender-security' ); ?></th>
			<th><?php esc_html_e( 'Date registered', 'defender-security' ); ?></th>
			<th><?php esc_html_e( 'Action', 'defender-security' ); ?></th>
		</tr>
		</thead>
		<tbody class="records" style="display:none;"></tbody>
		<tbody class="no-record" style="display:none;">
		<tr>
			<td colspan="4"><?php esc_html_e( 'No registered authenticator', 'defender-security' ); ?></td>
		</tr>
		</tbody>
		<tfoot>
		<tr>
			<td><?php esc_html_e( 'Identifier', 'defender-security' ); ?></td>
			<td><?php esc_html_e( 'Type', 'defender-security' ); ?></td>
			<td><?php esc_html_e( 'Date registered', 'defender-security' ); ?></td>
			<td><?php esc_html_e( 'Action', 'defender-security' ); ?></td>
		</tr>
		</tfoot>
	</table>
	<div class="wpdef-control">
		<button type="button" class="button wpdef-new-btn wpdef-device-btn <?php echo esc_attr( $admin_class ); ?>">
			<?php esc_html_e( 'Register Device', 'defender-security' ); ?>
		</button>
		<button type="button" class="button wpdef-verify-btn wpdef-device-btn <?php echo esc_attr( $admin_class ); ?>">
			<?php esc_html_e( 'Authenticate Device', 'defender-security' ); ?>
		</button>
	</div>
	<div class="process-auth-desc"></div>
	<div class="register-authenticator-box" style="display:none;">
		<h2><?php esc_html_e( 'Register New Authenticator', 'defender-security' ); ?></h2>
		<div class="desc">
			<?php
			esc_html_e(
				'Register a new authenticator for the current user account. You can register multiple authenticators for an account.',
				'defender-security'
			);
			?>
		</div>
		<table class="form-table">
			<tr id="row-auth-type">
				<th>
					<?php esc_html_e( 'Select an Authenticator Type', 'defender-security' ); ?>
					<span class="required">*</span>
				</th>
				<td>
					<div class="field-group">
						<input type="radio" name="authenticator-type" value="platform"/>
						<span><?php esc_html_e( 'Platform (Fingerprint and facial recognition)', 'defender-security' ); ?></span>
					</div>
					<div class="field-group">
						<input type="radio" name="authenticator-type" value="cross-platform"/>
						<span><?php esc_html_e( 'Roaming (e.g. USB security keys)', 'defender-security' ); ?></span>
					</div>
					<div class="field-error" style="display:none;">
						<?php esc_html_e( 'Choose an authenticator type.', 'defender-security' ); ?>
					</div>
				</td>
			</tr>
			<tr>
				<th>
					<?php esc_html_e( 'Authenticator Identifier', 'defender-security' ); ?>
					<span class="required">*</span>
				</th>
				<td>
					<input type="text" class="regular-text" id="authenticator-identifier"/>
					<div class="field-error" style="display:none;">
						<?php esc_html_e( 'Add an authenticator identifier.', 'defender-security' ); ?>
					</div>
					<div class="field-desc">
						<?php esc_html_e( 'Provide name to identify authenticator easily.', 'defender-security' ); ?>
					</div>
				</td>
			</tr>
		</table>
		<button type="button" id="wpdef-register-authenticator-btn"
				class="button"><?php esc_html_e( 'Start Registration', 'defender-security' ); ?></button>
		<button type="button" id="wpdef-register-authenticator-close-btn"
				class="button"><?php esc_html_e( 'Cancel', 'defender-security' ); ?></button>
		<div class="process-desc"></div>
	</div>
</div>
<script type="text/javascript">
	jQuery(function ($) {
		<?php if ( $is_force_auth ) { ?>
		$('html, body').animate({scrollTop: $(".auth-methods-table").offset().top}, 1000);
		<?php } ?>

		$('body').on('click', '#defender-security #field-<?php echo esc_html( $webauthn_slug ); ?>', function (e) {
			if ($(this).is(':checked')) {
				$('.defender-biometric-wrap').show();
			} else {
				$('.defender-biometric-wrap').hide();
			}
		});
	})
</script>
