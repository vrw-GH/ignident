<?php
/*
 * Page Name: Display
 */

use FloatingButton\Dashboard\Field;
use FloatingButton\Dashboard\FieldHelper;
use FloatingButton\Dashboard\Option;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();
$setOpt  = include( 'options/display.php' );

$count = 0;
if ( array_key_exists( 'show', $default['param'] ) && is_array( $default['param']['show'] ) ) {
	$count = count( $default['param']['show'] );
}

?>

    <h4>
        <span class="wowp-icon fas fa-list-check"></span>
		<?php
		esc_html_e( 'Display Conditions', 'floating-button' ); ?>
    </h4>

    <fieldset id="display-rules" class="wowp-item">
        <legend><?php
			esc_html_e( 'Display Rules', 'floating-button' ); ?></legend>
        <div class="wowp-fields-group">
			<?php
			Option::init( [
				$setOpt['show'],
			], 0 ); ?>

        </div>


    </fieldset>


    <fieldset class="wowp-item">
        <legend><?php
			esc_html_e( 'Devices Rules', 'floating-button' ); ?></legend>
        <div class="wowp-fields-group">
			<?php
			Option::init( [
				$setOpt['is_desktop'],
				$setOpt['desktop_screen']
			] ); ?>
        </div>
        <div class="wowp-fields-group">
			<?php
			Option::init( [
				$setOpt['is_mobile'],
				$setOpt['mobile_screen']
			] ); ?>
        </div>

    </fieldset>


    <fieldset class="wowp-item">
        <legend><?php
			esc_html_e( 'Other', 'floating-button' ); ?></legend>

        <div class="wowp-fields-group">
			<?php
			Option::init( [ $setOpt['fontawesome'] ] ); ?>
        </div>

    </fieldset>

<?php
