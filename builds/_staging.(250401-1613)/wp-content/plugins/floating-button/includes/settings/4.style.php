<?php
/*
 * Page Name: Style
 */

use FloatingButton\Dashboard\Option;

defined( 'ABSPATH' ) || exit;

$styleOpt = include( 'options/style.php' );
?>

<h4>
    <span class="wowp-icon fas fa-brush"></span>
	<?php esc_html_e( 'Style', 'floating-button' ); ?>
</h4>

<fieldset class="wowp-item">
    <legend><?php esc_html_e( 'General', 'floating-button' ); ?></legend>
    <div class="wowp-fields-group">
		<?php Option::init( [
			$styleOpt['shape'],
			$styleOpt['shadow'],
			$styleOpt['sub_btn_animation'],
		] ); ?>
    </div>
</fieldset>

<fieldset class="wowp-item">
    <legend><?php esc_html_e( 'Position', 'floating-button' ); ?></legend>

    <div class="wowp-fields-group">
		<?php Option::init( [
			$styleOpt['position'],
		] ); ?>
    </div>

</fieldset>


<fieldset class="wowp-item">
    <legend><?php esc_html_e( 'Button Size', 'floating-button' ); ?></legend>
    <div class="wowp-fields-group">
		<?php Option::init( [
			$styleOpt['size'],
		] ); ?>
    </div>
</fieldset>


<fieldset class="wowp-item">
    <legend><?php esc_html_e( 'Tooltip', 'floating-button' ); ?></legend>
    <div class="wowp-fields-group">
		<?php Option::init( [
			$styleOpt['tooltip_size_check'],
		] ); ?>
    </div>
    <div class="wowp-fields-group">
		<?php Option::init( [
			$styleOpt['tooltip_bg'],
			$styleOpt['tooltip_color'],
		] ); ?>
    </div>
</fieldset>
