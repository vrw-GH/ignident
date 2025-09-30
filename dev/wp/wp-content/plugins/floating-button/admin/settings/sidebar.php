<?php

use FloatingButton\Admin\DBManager;
use FloatingButton\Admin\Link;
use FloatingButton\Admin\Settings;
use FloatingButton\WOWP_Plugin;

defined( 'ABSPATH' ) || exit;

$link   = $options['link'] ?? '';
$tag    = $options['tag'] ?? '';
$status = ! empty( $options['status'] ) ? 'true' : 'false';
$test   = ! empty( $options['mode'] ) ? 'true' : 'false';

$shortcode = '';
if ( ! empty( $options['id'] ) ) {
	$shortcode = '[' . WOWP_Plugin::SHORTCODE . ' id="' . absint( $options['id'] ) . '"]';
}

?>

    <div class="wpie-sidebar">

        <h2 class="wpie-title"><?php esc_html_e( 'Publish', 'floating-button' ); ?></h2>
        <div class="wpie-fields__box">

            <div class="wpie-field">
                <div class="wpie-field__title"><?php esc_html_e( 'Status', 'floating-button' ); ?></div>
                <label class="wpie-field__label">
                    <input type="checkbox" name="status" value="true" <?php checked( "true", $status ); ?>>
                    <span class=""><?php esc_html_e( 'Deactivate', 'floating-button' ); ?></span>
                </label>
            </div>

            <div class="wpie-field">
                <div class="wpie-field__title"><?php esc_html_e( 'Test mode', 'floating-button' ); ?></div>
                <label class="wpie-field__label">
                    <input type="checkbox" name="mode" value="true" <?php checked( "true", $test ); ?>>
                    <span class=""><?php esc_html_e( 'Activate', 'floating-button' ); ?></span>
                </label>
            </div>

            <div class="wpie-field">
                <label class="wpie-field__label has-icon">
                    <span class="wpie-icon wpie_icon-tag"></span>
                    <input list="wpie-tags" type="text" name="tag" id="tag"
                           value="<?php echo esc_attr( $tag ); ?>">
                    <datalist id="wpie-tags">
						<?php DBManager::display_tags(); ?>
                    </datalist>
                </label>
            </div>

            <div class="wpie-field">
                <label class="wpie-field__label has-icon">
					<?php if ( ! empty( $link ) ): ?>
                        <a href="<?php echo esc_url( $link ); ?>" target="_blank">
                            <span class="has-tooltip on-right is-pointer" data-tooltip="Open Link"><span
                                        class="wpie-icon wpie_icon-link"></span></span>
                        </a>
					<?php else: ?>
                        <span class="wpie-icon wpie_icon-link"></span>
					<?php endif; ?>
                    <input type="url" name="link" id="link" value="<?php echo esc_url( $link ); ?>">
                </label>
            </div>

			<?php if ( ! empty( $shortcode ) ) : ?>
                <div class="wpie-field">
                    <label class="wpie-field__label has-icon">
                        <span class="has-tooltip on-right is-pointer can-copy" data-tooltip="Copy"><span
                                    class="dashicons dashicons-shortcode"></span></span>
                        <input type="text" id="shortcode" value="<?php echo esc_attr( $shortcode ); ?>" readonly>
                    </label>
                </div>
			<?php endif; ?>

        </div>

        <div class="wpie-actions__box">

            <div class="wpie-action__link">
				<?php if ( ! empty( $options['id'] ) ): ?>
                    <a class="wpie-link-delete"
                       href="<?php echo esc_url( Link::remove( $options['id'] ) ); ?>">
						<?php esc_html_e( 'Delete', 'floating-button' ); ?>
                    </a>
				<?php endif; ?>
            </div>

            <div class="wpie-action__btn">
                <span class="spinner"></span>
				<?php submit_button( null, 'primary', 'submit_settings', false ); ?>
            </div>
        </div>

    </div>

<?php if ( !class_exists( '\FloatingButton\WOWP_PRO' ) ) : ?>
    <div class="wpie-sidebar wpie-sidebar-features">
        <div class="wpie-item_heading">
            <span class="wpie-item_heading_icon">
                <span class="wpie-icon wpie_icon-rocket wpie-color-danger"></span>
            </span>
            <span class="wpie-item_heading_label">
                <?php esc_html_e( 'PRO VERSION', 'floating-button' ); ?>
            </span>
            <span class="wpie-item_heading_type"></span>
        </div>

        <div class="wpie-buttons">
            <a href="<?php echo esc_url( WOWP_Plugin::info( 'demo' ) ); ?>" target="_blank" class="wpie-button is-demo">Demo</a>
            <a href="<?php echo esc_url( WOWP_Plugin::info( 'pro' ) ); ?>" target="_blank" class="wpie-button is-pro">GET
                PRO</a>
        </div>
    </div>

<?php
endif;

