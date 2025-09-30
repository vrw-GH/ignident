<?php

/**
 * The Link class provides methods for generating URLs with query arguments.
 *
 * @package    WowPlugin
 * @subpackage Admin
 * @author     Dmytro Lobov <dev@wow-company.com>, Wow-Company
 * @copyright  2024 Dmytro Lobov
 * @license    GPL-2.0+
 */

namespace FloatingButton\Admin;

defined( 'ABSPATH' ) || exit;

use FloatingButton\WOWP_Plugin;

class Link {

	public static function add_new_item(): string {

		return add_query_arg( [
			'page'   => WOWP_Plugin::SLUG,
			'tab'    => 'settings',
			'action' => 'new'
		], admin_url( 'admin.php' ) );

	}

	public static function create( $arg = [] ): string {
		return add_query_arg( $arg, self::link() );
	}

	public static function activate_mode( $id = 0 ): string {
		return wp_nonce_url( add_query_arg( [
			'id' => $id,
		], self::link() ), WOWP_Plugin::PREFIX . '_nonce', WOWP_Plugin::PREFIX . '_activate_mode' );
	}

	public static function deactivate_mode( $id = 0 ): string {
		return wp_nonce_url( add_query_arg( [
			'id' => $id,
		], self::link() ), WOWP_Plugin::PREFIX . '_nonce', WOWP_Plugin::PREFIX . '_deactivate_mode' );
	}


	public static function activate_url( $id = 0 ): string {
		return wp_nonce_url( add_query_arg( [
			'id'     => $id,
			'action' => 'activate',
		], self::link() ), WOWP_Plugin::PREFIX . '_nonce', WOWP_Plugin::PREFIX . '_activate_item' );
	}

	public static function deactivate_url( $id = 0 ): string {
		return wp_nonce_url( add_query_arg( [
			'id'     => $id,
			'action' => 'deactivate',
		], self::link() ), WOWP_Plugin::PREFIX . '_nonce', WOWP_Plugin::PREFIX . '_deactivate_item' );
	}

	public static function remove_item(): string {
		return add_query_arg( [
			'notice' => 'remove_item',
		], self::link() );

	}

	public static function save_item( $id ): string {
		return add_query_arg( [
			'tab'    => 'settings',
			'action' => 'update',
			'id'     => $id,
			'notice' => 'save_item',
			'nonce'  => wp_create_nonce( 'save-item' )
		], self::link() );
	}

	public static function menu( $page, $action, $id ): string {
		if ( ! empty( $id ) && $action === 'update' ) {
			return add_query_arg( [
				'tab'    => $page,
				'action' => 'update',
				'id'     => $id,
			], self::link() );
		}

		return add_query_arg( [
			'tab' => $page,
		], self::link() );
	}

	public static function edit( $id ) {
		if ( ! $id ) {
			return false;
		}

		return add_query_arg( [
			'action' => 'update',
			'tab'    => 'settings',
			'id'     => $id
		], self::link() );
	}

	public static function duplicate( $id ) {
		if ( ! $id ) {
			return false;
		}

		return add_query_arg( [
			'action' => 'duplicate',
			'tab'    => 'settings',
			'id'     => $id
		], self::link() );
	}

	public static function export( $id ) {
		if ( ! $id ) {
			return false;
		}

		return wp_nonce_url( add_query_arg( [
			'action' => 'export',
			'id'     => $id
		], self::link() ), WOWP_Plugin::PREFIX . '_nonce', WOWP_Plugin::PREFIX . '_export_item' );
	}

	public static function remove( $id ) {
		if ( ! $id ) {
			return false;
		}

		return wp_nonce_url( add_query_arg( [
			'action' => 'delete',
			'id'     => $id
		], self::link() ), WOWP_Plugin::PREFIX . '_nonce', WOWP_Plugin::PREFIX . '_remove_item' );
	}

	private static function link(): string {
		return add_query_arg( [ 'page' => WOWP_Plugin::SLUG ], admin_url( 'admin.php' ) );
	}

}