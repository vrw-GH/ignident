<?php

namespace FloatingButton\Dashboard;

defined( 'ABSPATH' ) || exit;

use FloatingButton\WOW_Plugin;

class Settings {

	public static function init(): void {
		$pages = DashboardHelper::get_files( 'settings' );

		$i = 0;
		echo '<h3 class="nav-tab-wrapper wowp-tab" id="settings-tab">';
		foreach ( $pages as $key => $page ) {
			$current = ( $i === 0 ) ? 'nav-tab nav-tab-active' : 'nav-tab';
			echo '<a class="' . esc_attr( $current ) . '" data-tab="' . esc_attr( $page['file'] ) . '">' . esc_html( $page['name'] ) . '</a>';
			$i ++;
		}
		echo '</h3>';

		$i = 0;

		echo '<div class="tab-content-wrapper wowp-tab-content" id="settings-content">';
		foreach ( $pages as $key => $page ) {
			$current = ( $i === 0 ) ? 'tab-content tab-content-active' : 'tab-content';
			$file    = DashboardHelper::get_folder_path( 'settings' ) . '/' . $key . '.' . $page['file'] . '.php';

			if ( file_exists( $file ) ) {
				echo '<div class="' . esc_attr( $current ) . '" data-content="' . esc_attr( $page['file'] ) . '">';
				require_once $file;
				echo '</div>';
			}
			$i ++;
		}
		echo '</div>';
	}

	public static function save_item() {
		if ( empty( $_POST['submit_settings'] ) ) {
			return false;
		}

		$id       = isset( $_POST['tool_id'] ) ? absint( wp_unslash( $_POST['tool_id'] ) ) : '';
		$settings = apply_filters( WOW_Plugin::PREFIX . '_save_settings', [ 'data' => [], 'formats' => [] ], $_POST );

		if ( empty( $id ) ) {
			$id_item = DBManager::insert( $settings['data'], $settings['formats'] );
		} else {
			$where = [
				'id' => absint( $id ),
			];
			DBManager::update( $settings['data'], $where, $settings['formats'] );
			$id_item = $id;
		}

		wp_safe_redirect( Link::save_item( $id_item ) );
		exit;
	}

	public static function deactivate_item( $id = 0 ): void {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : $id;

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'status' => '1' ], [ 'ID' => $id ], [ '%d' ] );
		}
	}

	public static function activate_item( $id = 0 ): void {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : $id;

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'status' => '' ], [ 'ID' => $id ], [ '%d' ] );
		}
	}

	public static function deactivate_mode( $id = 0 ): void {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : $id;

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'mode' => '' ], [ 'ID' => $id ], [ '%d' ] );
		}
	}

	public static function activate_mode( $id = 0 ): void {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : $id;

		if ( ! empty( $id ) ) {
			DBManager::update( [ 'mode' => '1' ], [ 'ID' => $id ], [ '%d' ] );
		}
	}

}