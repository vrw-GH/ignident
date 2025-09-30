<?php
/*
 * Page Name: 1 Sub Buttons
 */

use FloatingButton\Admin\CreateFields;

defined( 'ABSPATH' ) || exit;

$data = include( 'options/2.vertical.php' );

$field = new CreateFields( $options, $data['opt'] );

$count = ( ! empty( $options['menu_1']['item_type'] ) ) ? count( $options['menu_1']['item_type'] ) : '0';
?>
    <div class="wpie-items__list" id="wpie-items-list">
		<?php if ( $count > 0 ) :
			for ( $i = 0; $i < $count; $i ++ ):
				$order = $i + 1;
				$item_order = ! empty( $options['item_order'][ $i ] ) ? 1 : 0;
				$open = ! empty( $item_order ) ? ' open' : '';
				$item_parent = $options['menu_1']['item_sub'][ $i ] ?? 0;
				$item_class = '';
				if ( absint( $item_parent ) === 1 ) {
					$item_class = ' shifted-right';
				}
				?>
                <details
                        class="wpie-item menu-item<?php echo esc_attr( $item_class ); ?>"<?php echo esc_attr( $open ); ?>>
                    <input type="hidden" name="param[item_order][]" class="wpie-item__toggle"
                           value="<?php echo absint( $item_order ); ?>">
                    <input type="hidden" name="param[menu_1][item_sub][]" class="wpie-item__parent"
                           value="<?php echo absint( $item_parent ); ?>">
                    <summary class="wpie-item_heading">
                        <span class="wpie-item_heading_icon"></span>
                        <span class="wpie-item_heading_label"></span>
                        <span class="wpie-item_heading_type"></span>
                        <span class="wpie-icon wpie_icon-copy"></span>
                        <span class="wpie-icon wpie_icon-chevron-expand-y"></span>
                        <span class="wpie-icon wpie_icon-trash"></span>
                        <span class="wpie-item_heading_toogle">
                            <span class="wpie-icon wpie_icon-chevron-down"></span>
                            <span class="wpie-icon wpie_icon-chevron-up "></span>
                        </span>
                    </summary>
                    <div class="wpie-item_content">

                        <div class="wpie-tabs-wrapper">

                            <div class="wpie-tabs-link">
								<?php
								$tab_i = 1;
								foreach ( $data['tabs'] as $tab ) {
									$active = $tab_i === 1 ? ' is-active' : '';
									echo '<a class="wpie-tab__link' . esc_attr( $active ) . '">' . esc_html( ucfirst( $tab ) ) . '</a>';
									$tab_i ++;
								}
								?>
                            </div>

							<?php
							$tabs_i = 1;
							foreach ( $data['args'] as $tabs ) {
								$active = $tabs_i === 1 ? ' is-active' : '';
								echo '<div class="wpie-tab-settings' . esc_attr( $active ) . '">';
								echo '<div class="wpie-fieldset">';

								foreach ( $tabs as $tab ) {
									echo '<div class="wpie-fields">';

									foreach ( $tab as $option => $optionVal ) {
										$field->create( 'menu_1-' . $option, $i );
									}
									echo '</div>';
								}

								echo '</div>';
								echo '</div>';

								$tabs_i ++;
							}
							?>
                        </div>
                    </div>
                </details>
			<?php endfor; endif; ?>

        <hr class="wpie-buttons__hr">
        <div class="wpie-fields">
            <button class="button button-primary wpie-add-button" type="button" data-template="template-button-1">
                <?php esc_html_e( 'Add Button', 'floating-button' ); ?>
            </button>
        </div>

    </div>


    <template id="template-button-1">
        <details class="wpie-item" open>
            <input type="hidden" name="param[item_order][]" class="wpie-item__toggle" value="1">
            <input type="hidden" name="param[menu_1][item_sub][]" class="wpie-item__parent"
                   value="0">
            <summary class="wpie-item_heading">
                <span class="wpie-item_heading_icon"></span>
                <span class="wpie-item_heading_label"></span>
                <span class="wpie-item_heading_type"></span>
                <span class="wpie-icon wpie_icon-copy"></span>
                <span class="wpie-icon wpie_icon-chevron-expand-y"></span>
                <span class="wpie-icon wpie_icon-trash"></span>
                <span class="wpie-item_heading_toogle">
                    <span class="wpie-icon wpie_icon-chevron-down"></span>
                    <span class="wpie-icon wpie_icon-chevron-up "></span>
            </span>
            </summary>
            <div class="wpie-item_content">

                <div class="wpie-tabs-wrapper">

                    <div class="wpie-tabs-link">
						<?php
						$tab_i = 1;
						foreach ( $data['tabs'] as $tab ) {
							$active = $tab_i === 1 ? ' is-active' : '';
							echo '<a class="wpie-tab__link' . esc_attr( $active ) . '">' . esc_html( ucfirst( $tab ) ) . '</a>';
							$tab_i ++;
						}
						?>
                    </div>

					<?php
					$tabs_i = 1;
					foreach ( $data['args'] as $tabs ) {
						$active = $tabs_i === 1 ? ' is-active' : '';
						echo '<div class="wpie-tab-settings' . esc_attr( $active ) . '">';
						echo '<div class="wpie-fieldset">';

						foreach ( $tabs as $tab ) {
							echo '<div class="wpie-fields">';

							foreach ( $tab as $option => $optionVal ) {
								$field->create( 'menu_1-' . $option, -1 );
							}
							echo '</div>';
						}

						echo '</div>';
						echo '</div>';

						$tabs_i ++;
					}
					?>
                </div>


            </div>
        </details>
    </template>

<?php

