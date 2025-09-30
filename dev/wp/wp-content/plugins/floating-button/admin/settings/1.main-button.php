<?php
/*
 * Page Name: Main Button
 */

use FloatingButton\Admin\CreateFields;

defined( 'ABSPATH' ) || exit;

$data = include( 'options/1.main-button.php' );

$field = new CreateFields( $options, $data['opt'] );

?>
    <div class="wpie-items__list" id="wpie-items-list">

                <details class="wpie-item menu-item" open>
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
										$field->create( $option );
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



    </div>

<?php

