<?php

namespace FloatingButton\Admin;

use FloatingButton\Update\UpdateDB;
use FloatingButton\WOWP_Plugin;

class Demo {

	public static function init() {
		add_action( WOWP_Plugin::PREFIX . '_admin_after_button', [ __CLASS__, 'demo_button' ] );
		add_action( 'wp_ajax_' . WOWP_Plugin::PREFIX . '_upload_demo', [ __CLASS__, 'upload_demo' ] );
	}

	public static function demo_button(): void {
		$demo = [
			'Simple-Floating-Menu' => [
				'name' => __( 'Simple Floating Menu', 'floating-button' ),
				'link' => 'https://lite.wow-estore.com/float-menu/',
			],
			'Social-Media-Menu'    => [
				'name' => __( 'Social Media Menu', 'floating-button' ),
				'link' => 'https://lite.wow-estore.com/float-menu/',
			],
			'Navigation-Menu'      => [
				'name' => __( 'Navigation Menu', 'floating-button' ),
				'link' => 'https://lite.wow-estore.com/float-menu/',
			],
			'Quick-Actions-Menu'   => [
				'name' => __( 'Quick Actions Menu', 'floating-button' ),
				'link' => 'https://lite.wow-estore.com/float-menu/',
				'in'   => 1,
			],

			'Share_pro' => [
				'name' => __( 'Share Buttons', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/share/',
			],

			'Save-Content_pro' => [
				'name' => __( 'Save Content', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/share/',
			],

			'Messaging_pro' => [
				'name' => __( 'Messaging', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/share/',
			],

			'Translate_pro' => [
				'name' => __( 'Translate', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/translate-page/',
			],

			'Icon-with-Text_pro' => [
				'name' => __( 'Icon with Text', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/icon-with-text/',
			],

			'Scrolling_pro' => [
				'name' => __( 'Scrolling', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/scrolling/',
			],

			'Icon-animation_pro' => [
				'name' => __( 'Icon animations', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/icon-animations/',
			],

			'Actions_pro' => [
				'name' => __( 'Actions', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/actions/',
				'in'   => 1,
			],

			'Show-After-Position_pro' => [
				'name' => __( 'Show after Position ', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/show-after-position/',
			],

			'Hide-after-Position_pro' => [
				'name' => __( ' Hide after Position ', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/hide-after-position/',
			],

            'menu-with-radius_pro' => [
				'name' => __( 'Menu with Custom Radius', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/custom-border-radius/',
			],

            'horizontal-menu_pro' => [
				'name' => __( 'Horizontal Menu', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/horizontal-menu/',
			],


			'Contact_pro' => [
				'name' => __( 'Contact', 'floating-button' ),
				'link' => 'https://demo.wow-estore.com/floating-button-pro/contact/',
			],
		];


		?>

        <button type="button" class="button" onclick="window.demoUpload.showModal()">
			<?php esc_html_e( 'Load Example', 'floating-button' ); ?>
        </button>

        <dialog id="demoUpload" class="wpie-dialog">
            <button type="button" class="wpie-dialog-close" onclick="window.demoUpload.close()">
                <span class="wpie-icon wpie_icon-xmark"></span>
            </button>
            <table>
                <thead>
                <tr>
                    <th>
						<?php esc_html_e( 'Name', 'floating-button' ); ?>
                    </th>
                    <th>
						<?php esc_html_e( 'Preview Link', 'floating-button' ); ?>
                    </th>
                    <th>
						<?php esc_html_e( 'Action', 'floating-button' ); ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ( $demo as $file => $item ) : ?>
                    <tr>
                        <td><?php echo esc_html( $item['name'] ); ?></td>
                        <td><a href="<?php echo esc_url( $item['link'] ); ?>"
                               target="_blank"><?php esc_html_e( 'Preview', 'floating-button' ); ?></a></td>
                        <td>
			                <?php if ( strpos( $file, '_pro' ) === false || class_exists( '\FloatingButton\WOWP_PRO' ) )  : ?>
                                <button class="wpie-download"
                                        data-menu="<?php echo esc_attr( strtolower( $file ) ); ?>">
					                <?php esc_html_e( 'Download', 'floating-button' ); ?>
                                </button>
			                <?php else: ?>
                                <a href="<?php echo esc_url( WOWP_Plugin::info( 'pro' ) ); ?>"
                                   class="wpie-pro"><?php esc_html_e( 'Available in Pro', 'floating-button' ); ?></a>
			                <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </dialog>

		<?php
	}

	public static function upload_demo(): void {
		$menu = sanitize_key( $_POST['menu'] ?? '' );
		if ( strpos( $menu, '_pro' ) !== false ) {
			$demo_path = WOWP_Plugin::dir() . 'includes/pro/demo/' . $menu . '.json';
		} else {
			$demo_path = WOWP_Plugin::dir() . 'admin/demo/' . $menu . '.json';
		}


		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
		}

		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), WOWP_Plugin::PREFIX . '_settings' ) ) {
			wp_send_json_error( [ 'message' => 'Invalid nonce' ], 400 );
		}

		if ( file_exists( $demo_path ) ) {
			$settings = wp_json_file_decode( $demo_path );
			$columns  = DBManager::get_columns();

			foreach ( $settings as $key => $val ) {
				$data    = [];
				$formats = [];

				foreach ( $columns as $column ) {
					$name = $column->Field;

					$data[ $name ] = ! empty( $val->$name ) ? $val->$name : '';

					if ( $name === 'id' || $name === 'status' || $name === 'mode' ) {
						$formats[] = '%d';
					} else {
						$formats[] = '%s';
					}
				}

				$index = array_search( 'id', array_keys( $data ), true );
				unset( $data['id'], $formats[ $index ] );
				DBManager::insert( $data, $formats );
			}
		}

		wp_send_json_success( [ 'path' => $demo_path ] );
	}

}