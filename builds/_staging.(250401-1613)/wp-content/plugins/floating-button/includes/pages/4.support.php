<?php
/**
 * Page Name: Support
 *
 */

use FloatingButton\Dashboard\SupportForm;
use FloatingButton\WOW_Plugin;

defined( 'ABSPATH' ) || exit;
$emil = WOW_Plugin::info('email');
?>

    <div class="w_block w_has-border">

        <p>
		    <?php
		    esc_html_e( 'To get your support related question answered in the fastest timing, please send a message via the form below or write to us via', 'floating-button' );
		    echo ' <a href="' . esc_url( WOW_Plugin::info( 'support' ) ) . '">' . esc_html__( 'support page', 'floating-button' ) . '</a>';
		    ?>
        </p>

        <p>
			<?php esc_html_e( 'Also, you can send us your ideas and suggestions for improving the plugin.', 'floating-button' ); ?>
        </p>

		<?php SupportForm::init(); ?>

    </div>

<?php
