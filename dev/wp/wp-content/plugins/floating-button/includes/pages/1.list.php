<?php
/*
 * Page Name: List
 */

use FloatingButton\Dashboard\ListTable;
use FloatingButton\WOW_Plugin;

defined( 'ABSPATH' ) || exit;

$list_table = new ListTable();
$list_table->prepare_items();
$table_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';

$wp_plugins = [
	[
		'free'    => 'https://wordpress.org/plugins/modal-window/',
		'pro'     => 'https://wow-estore.com/item/wow-modal-windows-pro/',
		'icon'    => 'modal-windows.png',
		'title'   => 'Modal Windows',
		'content' => 'Designed to ease the process of creating and setting the modal windows on the WordPress site.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/mwp-herd-effect/',
		'pro'     => 'https://wow-estore.com/item/wow-herd-effects-pro/',
		'icon'    => 'herd-effects.jpg',
		'title'   => 'Herd Effects',
		'content' => 'Designed to create a “sense of queue” or “herd effect”, motivating the visitors of the page to perform any actions.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/counter-box/',
		'title'   => 'Counter Box',
		'content' => 'Quickly and easily create countdowns, counters, and timers with a live preview.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/buttons/',
		'title'   => 'Buttons',
		'content' => 'Easily create beautiful, customizable standard, floating, and social sharing buttons. Increase click-through rates and enhance your user experience.'
	],
	[
		'free'    => 'https://wordpress.org/plugins/calculator-builder/',
		'title'   => 'Calculator Builder',
		'content' => 'A simple way to create an online calculator.'
	],
];
?>

    <div class="wowp-notice notice-info notice">
        <strong>Works Great With:</strong>
		<?php
		foreach ( $wp_plugins as $plugin ) {
			echo '<a href="' . esc_url( $plugin['free'] ) . '" target="_blank" class="has-tooltip on-bottom" data-tooltip="' . esc_attr( $plugin['content'] ) . '">' . esc_html( $plugin['title'] ) . '</a> <span class="wpie-separator">|</span> ';
		} ?>
    </div>

    <form method="post" class="wowp-list">
		<?php
		$list_table->search_box( esc_attr__( 'Search', 'floating-button' ), WOW_Plugin::PREFIX );
		$list_table->display();
		?>
        <input type="hidden" name="page" value="<?php echo esc_attr( $table_page ); ?>"/>
		<?php wp_nonce_field( WOW_Plugin::PREFIX . '_nonce', WOW_Plugin::PREFIX . '_list_action' ); ?>
    </form>
<?php
