<?php
/*
 * Page Name: Targeting & Rules
 */

use FloatingButton\Admin\CreateFields;
use FloatingButton\Settings_Helper;
use FloatingButton\WOWP_Plugin;

defined( 'ABSPATH' ) || exit;

$data  = include( 'options/5.rules.php' );
$field = new CreateFields( $options, $data['opt'] );

foreach ( $data['args'] as $key => $value ) {
	$item_order = ! empty( $options['item_order'][ $key ] ) ? 1 : 0;
	$open       = ! empty( $item_order ) ? ' open' : '';
	?>

    <details class="wpie-item"<?php echo esc_attr( $open ); ?>>
        <input type="hidden" name="param[item_order][<?php echo esc_attr( $key ); ?>]" class="wpie-item__toggle"
               value="<?php echo absint( $item_order ); ?>">
        <summary class="wpie-item_heading">
            <span class="wpie-item_heading_icon">
                <span class="wpie-icon <?php echo esc_attr( $value['icon'] ); ?>"></span>
            </span>
            <span class="wpie-item_heading_label"><?php echo esc_html( $value['title'] ); ?></span>
            <span class="wpie-item_heading_type"></span>
            <span class="wpie-item_heading_toogle">
                <span class="wpie-icon wpie_icon-chevron-down"></span>
                <span class="wpie-icon wpie_icon-chevron-up"></span>
            </span>
        </summary>
        <div class="wpie-item_content">
            <div class="wpie-fieldset wpie-<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>">
				<?php
				foreach ( $value as $k => $v ) {

					if ( is_array( $v ) ) {

						echo '<div class="wpie-fields">';
						foreach ( $v as $k2 => $v2 ) {
							if ( $key === 'rules' ) {
								$field->create( $k2, 0 );
							} elseif ( $key === 'schedule' ) {
                                continue;
                            }
                            else {
								$field->create( $k2 );
							}
						}
						echo '</div>';
						if ( $key === 'rules' ) {
							do_action( WOWP_Plugin::PREFIX . '_rules_display', $options, $v, $field );
						}

						if ( $key === 'schedule' ) {
							do_action( WOWP_Plugin::PREFIX . '_rules_schedule', $options, $v, $field );
						}

					}

				}

				?>
            </div>
        </div>
    </details>
	<?php
}