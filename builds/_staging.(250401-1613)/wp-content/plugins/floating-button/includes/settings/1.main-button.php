<?php
/*
 * Page Name: Main
 */

use FloatingButton\Dashboard\Field;
use FloatingButton\Dashboard\FieldHelper;
use FloatingButton\Dashboard\Option;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();


$btnOpt = include( 'options/main-button.php' );

?>

    <h4>
        <span class="wowp-icon fas fa-circle-dot"></span>
		<?php
		esc_html_e( 'Main Button', 'floating-button' ); ?>
    </h4>

    <div class="wowp-item has-shadow ">
        <div class="wowp-item_heading">
            <div class="wowp-item_heading_icon">
                <i class="fas fa-hand-point-up"></i>
            </div>
            <div class="wowp-item_heading_label">
                (no label)
            </div>
            <div class="wowp-item_heading_type">
                Main Button
            </div>
        </div>
        <div class="wowp-item_content">
            <fieldset>
                <div class="wowp-fields-group">
					<?php Option::init( [
						$btnOpt['label'],
						$btnOpt['label_on'],
					] ); ?>

                    <div class="wowp-tabs">

                        <div class="wowp-tabs-link">
                            <a class="is-active"><?php esc_html_e( 'Type', 'floating-button' ); ?></a>
                            <a><?php esc_html_e( 'Icons', 'floating-button' ); ?></a>
                            <a><?php esc_html_e( 'Style', 'floating-button' ); ?></a>
                            <a> <?php esc_html_e( 'Attributes', 'floating-button' ); ?></a>
                        </div>

                        <div class="wowp-tabs-content is-active">

							<?php Option::init( [
								$btnOpt['btn_type'],
								$btnOpt['link'],
							] ); ?>

                        </div>
                        <div class="wowp-tabs-content ">
                            <div class="wowp-fields-group ">
								<?php Option::init( [
									$btnOpt['icon_type'],
									$btnOpt['btn_icon'],
								] ); ?>
                            </div>

                            <div class="wowp-fields-group ">
		                        <?php Option::init( [
			                        $btnOpt['icon_close_on'],
			                        $btnOpt['icon_close'],
		                        ] ); ?>
                            </div>

                        </div>
                        <div class="wowp-tabs-content">

                            <div class="wowp-fields-group">
								<?php Option::init( [
									$btnOpt['btn_color'],
									$btnOpt['btn_hover_color'],
								] ); ?>
                            </div>

                            <div class="wowp-fields-group">
		                        <?php Option::init( [
			                        $btnOpt['icon_color'],
			                        $btnOpt['icon_hover_color'],
		                        ] ); ?>
                            </div>

                        </div>
                        <div class="wowp-tabs-content">

							<?php Option::init( [
								$btnOpt['btn_class'],
								$btnOpt['link_rel'],
								$btnOpt['extra_class'],
							] ); ?>
                        </div>

                    </div>
            </fieldset>
        </div>
    </div>

<?php

