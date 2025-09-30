<?php

namespace FloatingButton\Admin;

defined( 'ABSPATH' ) || exit;

use FloatingButton\WOWP_Plugin;

class SupportForm {

	public static function init(): void {

		$plugin  = WOWP_Plugin::info( 'name' ) . ' v.' . WOWP_Plugin::info( 'version' );
		$license = get_option( 'wow_license_key_' . WOWP_Plugin::PREFIX, 'no' );

		self::send();

		?>

        <form method="post">

            <fieldset class="wpie-fieldset">
                <legend>
					<?php esc_html_e( 'Support Form', 'floating-button' ); ?>
                </legend>

                <div class="wpie-fields is-column-2">
                    <div class="wpie-field">
                        <div class="wpie-field__title"><?php esc_html_e( 'Your Name', 'floating-button' ); ?></div>
                        <label class="wpie-field__label has-icon">
                            <span class="dashicons dashicons-admin-users"></span>
                            <input type="text" name="support[name]" id="support-name" value="">
                        </label>
                    </div>

                    <div class="wpie-field">
                        <div class="wpie-field__title"><?php esc_html_e( 'Contact email', 'floating-button' ); ?></div>
                        <label class="wpie-field__label has-icon">
                            <span class="dashicons dashicons-email"></span>
                            <input type="email" name="support[email]" id="support-email"
                                   value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
                        </label>
                    </div>
                </div>

                <div class="wpie-fields is-column-2">

                    <div class="wpie-field">
                        <div class="wpie-field__title"><?php esc_html_e( 'Link to the issue', 'floating-button' ); ?></div>
                        <label class="wpie-field__label has-icon">
                            <span class="dashicons dashicons-admin-links"></span>
                            <input type="url" name="support[link]" id="support-link"
                                   value="<?php echo esc_url( get_option( 'home' ) ); ?>">
                        </label>
                    </div>

                    <div class="wpie-field" data-field-box="menu_open">
                        <div class="wpie-field__title"><?php esc_html_e( 'Message type', 'floating-button' ); ?></div>
                        <label class="wpie-field__label">
                            <select name="support[type]" id="support-type">
                                <option value="Issue"><?php esc_html_e( 'Issue', 'floating-button' ); ?></option>
                                <option value="Idea"><?php esc_html_e( 'Idea', 'floating-button' ); ?></option>
                            </select>
                        </label>
                    </div>

                </div>
                <div class="wpie-fields is-column-2">
                    <div class="wpie-field">
                        <div class="wpie-field__title"><?php esc_html_e( 'Plugin', 'floating-button' ); ?></div>
                        <label class="wpie-field__label has-icon">
                            <span class="dashicons dashicons-admin-plugins"></span>
                            <input type="text" readonly name="support[plugin]" id="support-plugin"
                                   value="<?php echo esc_attr( $plugin ); ?>">
                        </label>
                    </div>

                    <div class="wpie-field">
                        <div class="wpie-field__title"><?php esc_html_e( 'License Key', 'floating-button' ); ?></div>
                        <label class="wpie-field__label has-icon">
                            <span>🔑</span>
                            <input type="text" readonly name="support[license]" id="support-license"
                                   value="<?php echo esc_attr( $license ); ?>">
                        </label>
                    </div>

                </div>
                <div class="wpie-fields is-column">
					<?php
					$content   = esc_attr__( 'Enter Your Message', 'floating-button' );
					$editor_id = 'support-message';
					$settings  = array(
						'textarea_name' => 'support[message]',
					);
					wp_editor( $content, $editor_id, $settings ); ?>
                </div>

                <div class="wpie-fields is-column">

                    <div class="wpie-field">
						<?php submit_button( __( 'Send to Support', 'floating-button' ), 'primary', 'submit', false ); ?>
                    </div>
                </div>
				<?php wp_nonce_field( WOWP_Plugin::PREFIX . '_nonce', WOWP_Plugin::PREFIX . '_support' ); ?>
            </fieldset>

        </form>

		<?php


	}

	private static function send(): void {
		$verify = AdminActions::verify( WOWP_Plugin::PREFIX . '_support' );

		if ( ! $verify ) {
			return;
		}

		$error = self::error();
		if ( ! empty( $error ) ) {
			echo '<p class="notice notice-error">' . esc_html( $error ) . '</p>';

			return;
		}
        // phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verification is handled elsewhere.
		$from    = isset( $_POST['support']['name'] ) ? sanitize_text_field( wp_unslash( $_POST['support']['name'] ) ) : '';
		$email   = isset( $_POST['support']['email'] ) ? sanitize_email( wp_unslash( $_POST['support']['email'] ) ) : '';
		$license = isset( $_POST['support']['license'] ) ? sanitize_text_field( wp_unslash( $_POST['support']['license'] ) ) : '';
		$plugin  = isset( $_POST['support']['plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['support']['plugin'] ) ) : '';
		$link    = isset( $_POST['support']['link'] ) ? sanitize_url( wp_unslash( $_POST['support']['link'] ) ) : '';
		$message = isset( $_POST['support']['message'] ) ? wp_kses_post( wp_unslash( $_POST['support']['message'] ) ) : '';
		$type    = isset( $_POST['support']['type'] ) ? sanitize_text_field( wp_unslash( $_POST['support']['type'] ) ) : '';
		// phpcs:enable

		$headers = array(
			'From: ' . esc_attr( $from ) . ' <' . esc_attr( $email ) . '>',
			'content-type: text/html',
		);


		$message_mail = '<html>
                        <head></head>
                        <body>
                        <table>
                        <tr>
                        <td><strong>License Key:</strong></td>
                        <td>' . esc_attr( $license ) . '</td>
                        </tr>
                        <tr>
                        <td><strong>Plugin:</strong></td>
                        <td>' . esc_attr( $plugin ) . '</td>
                        </tr>
                        <tr>
                        <td><strong>Website:</strong></td>
                        <td><a href="' . esc_url( $link ) . '" target="_blank">' . esc_url( $link ) . '</a></td>
                        </tr>
                        </table>
                        <p/>
                        ' . nl2br( wp_kses_post( $message ) ) . ' 
                        </body>
                        </html>';
		$to_mail      = WOWP_Plugin::info( 'email' );
		$send         = wp_mail( $to_mail, 'Support Request: ' . $type, $message_mail, $headers );

		if ( $send ) {
			$text = __( 'Your message has been sent to the support team.', 'floating-button' );
			echo '<p class="notice notice-success">' . esc_html( $text ) . '</p>';
		} else {
			$text = __( 'Sorry, but message did not send. Please, contact us via support page.', 'floating-button' );
			echo '<p class="notice notice-error">' . esc_html( $text ) . '</p>';
		}

	}

	private static function error(): ?string {

		$fields = [ 'name', 'email', 'link', 'type', 'plugin', 'license', 'message' ];

		foreach ( $fields as $field ) {
			// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verification is handled elsewhere.
			if ( empty( $_POST['support'][ $field ] ) ) {
				return __( 'Please fill in all the form fields below.', 'floating-button' );
			}
		}

		return '';
	}


}
