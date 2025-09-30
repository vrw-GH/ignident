<?php
/*
 * Page Name: Settings
 */

use FloatingButton\Admin\CreateFields;

defined( 'ABSPATH' ) || exit;

$data = include( 'options/4.settings.php' );

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
                <span class="wpie-icon <?php echo esc_attr($value['icon']);?>"></span>
            </span>
            <span class="wpie-item_heading_label"><?php echo esc_html($value['title']);?></span>
            <span class="wpie-item_heading_type"></span>
            <span class="wpie-item_heading_toogle">
                <span class="wpie-icon wpie_icon-chevron-down"></span>
                <span class="wpie-icon wpie_icon-chevron-up"></span>
            </span>
        </summary>
        <div class="wpie-item_content">
            <div class="wpie-fieldset">
				<?php
				foreach ( $value as $k => $v ) {

					if(is_array($v)) {
						echo '<div class="wpie-fields">';
						foreach ( $v as $k2 => $v2 ) {
							$field->create( $k2 );
						}
						echo '</div>';
					}

				}

				?>
            </div>
        </div>
    </details>
	<?php
}

