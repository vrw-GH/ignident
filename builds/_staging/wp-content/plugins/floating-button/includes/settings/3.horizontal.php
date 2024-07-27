<?php
/*
 * Page Name: 2 Sub Buttons
 */

use FloatingButton\Dashboard\Field;
use FloatingButton\Dashboard\FieldHelper;
use FloatingButton\Dashboard\Option;

$default = Field::getDefault();
$count   = ! empty( $default['param']['menu_2']['item_type'] ) ? count( $default['param']['menu_2']['item_type'] ) : 0;

defined( 'ABSPATH' ) || exit;

$sbBtnOpt = include( 'options/sub-buttons.php' );
$sbBtnOpt = Field::add_prefix( '[menu_2]', $sbBtnOpt );

?>
    <h4>
        <span class="wowp-icon fas fa-2"></span>
		<?php
		esc_html_e( 'Sub Buttons', 'floating-button' ); ?>
    </h4>

    <div class="menu-items" id="wowp-menu-2">
		<?php if ( $count > 0 ) :
			for ( $i = 0; $i < $count; $i ++ ):
				$attr_open = ( $i === 0 ) ? ' open' : '';
				?>
                <details class="wowp-item has-shadow"<?php echo esc_attr( $attr_open ); ?>>
                    <summary class="wowp-item_heading">
                        <span class="wowp-item_heading_icon"></span>
                        <span class="wowp-item_heading_label"></span>
                        <span class="wowp-item_heading_type"></span>
                        <span class="dashicons dashicons-move"></span>
                        <span class="dashicons dashicons-trash"></span>
                        <span class="wowp-item_heading_toogle">
                            <span class="dashicons dashicons-arrow-down"></span>
                            <span class="dashicons dashicons-arrow-up "></span>
                        </span>
                    </summary>
                    <div class="wowp-item_content">

                        <fieldset>
                            <div class="wowp-fields-group">
								<?php Option::init( [
									$sbBtnOpt['label'],
									$sbBtnOpt['label_on'],
								], $i ); ?>
                            </div>

                            <div class="wowp-tabs">

                                <div class="wowp-tabs-link">
                                    <a class="is-active"><?php esc_html_e( 'Type', 'floating-button' ); ?></a>
                                    <a><?php esc_html_e( 'Icons', 'floating-button' ); ?></a>
                                    <a><?php esc_html_e( 'Style', 'floating-button' ); ?></a>
                                    <a> <?php esc_html_e( 'Attributes', 'floating-button' ); ?></a>
                                </div>

                                <div class="wowp-tabs-content is-active">

									<?php Option::init( [
										$sbBtnOpt['btn_type'],
										$sbBtnOpt['link'],
									], $i ); ?>

                                </div>
                                <div class="wowp-tabs-content ">
									<?php
									Option::init( [
										$sbBtnOpt['icon_type'],
										$sbBtnOpt['item_icon'],
									], $i ); ?>

                                </div>

                                <div class="wowp-tabs-content ">


                                    <div class="wowp-fields-group">
		                                <?php Option::init( [
			                                $sbBtnOpt['btn_color'],
			                                $sbBtnOpt['btn_hover_color'],
		                                ], $i ); ?>
                                    </div>

                                    <div class="wowp-fields-group">
		                                <?php Option::init( [
			                                $sbBtnOpt['icon_color'],
			                                $sbBtnOpt['icon_hover_color'],
		                                ], $i ); ?>
                                    </div>


                                </div>

                                <div class="wowp-tabs-content ">

									<?php Option::init( [
										$sbBtnOpt['btn_id'],
										$sbBtnOpt['btn_class'],
										$sbBtnOpt['link_rel'],
									], $i ); ?>

                                </div>

                            </div>


                        </fieldset>

                    </div>
                </details>
			<?php
			endfor;
		endif; ?>
    </div>
    <p class="btn-add-item">
        <a class="button button-primary button-large"  id="add_menu_2"><?php esc_html_e( 'Add Item' ); ?></a>
    </p>

    <template id="clone-menu-2">
        <details class="wowp-item has-shadow" open>

            <summary class="wowp-item_heading">
                <span class="wowp-item_heading_icon"></span>
                <span class="wowp-item_heading_label"></span>
                <span class="wowp-item_heading_type"></span>
                <span class="dashicons dashicons-move"></span>
                <span class="dashicons dashicons-trash"></span>
                <span class="wowp-item_heading_toogle">
                <span class="dashicons dashicons-arrow-down"></span>
                <span class="dashicons dashicons-arrow-up "></span>
            </span>
            </summary>
            <div class="wowp-item_content">

                <fieldset>
                    <div class="wowp-fields-group">
						<?php Option::init( [
							$sbBtnOpt['label'],
							$sbBtnOpt['label_on'],
						], -1 ); ?>
                    </div>

                    <div class="wowp-tabs">

                        <div class="wowp-tabs-link">
                            <a class="is-active"><?php esc_html_e( 'Type', 'floating-button' ); ?></a>
                            <a><?php esc_html_e( 'Icons', 'floating-button' ); ?></a>
                            <a><?php esc_html_e( 'Style', 'floating-button' ); ?></a>
                            <a> <?php esc_html_e( 'Attributes', 'floating-button' ); ?></a>
                        </div>

                        <div class="wowp-tabs-content is-active">

							<?php Option::init( [
								$sbBtnOpt['btn_type'],
								$sbBtnOpt['link'],
							], -1 ); ?>

                        </div>
                        <div class="wowp-tabs-content ">
							<?php
							Option::init( [
								$sbBtnOpt['icon_type'],
								$sbBtnOpt['item_icon'],
							], -1 ); ?>

                        </div>

                        <div class="wowp-tabs-content ">

                            <div class="wowp-fields-group">
		                        <?php Option::init( [
			                        $sbBtnOpt['btn_color'],
			                        $sbBtnOpt['btn_hover_color'],
		                        ], -1 ); ?>
                            </div>

                            <div class="wowp-fields-group">
		                        <?php Option::init( [
			                        $sbBtnOpt['icon_color'],
			                        $sbBtnOpt['icon_hover_color'],
		                        ], -1 ); ?>
                            </div>

                        </div>

                        <div class="wowp-tabs-content ">

							<?php Option::init( [
								$sbBtnOpt['btn_id'],
								$sbBtnOpt['btn_class'],
								$sbBtnOpt['link_rel'],
							], -1 ); ?>

                        </div>

                    </div>


                </fieldset>

            </div>
        </details>
    </template>
<?php