<?php

/**
 * Class UpdateDB
 *
 * Contains methods for updating the database structure and data
 *
 * @package    FloatingButton
 * @subpackage Update
 * @author     Dmytro Lobov <dev@wow-company.com>, Wow-Company
 * @copyright  2024 Dmytro Lobov
 * @license    GPL-2.0+
 *
 */

namespace FloatingButton\Update;

use FloatingButton\Admin\DBManager;
use FloatingButton\WOWP_Plugin;

class UpdateDB {

	public static function init(): void {
		$current_db_version = get_site_option( WOWP_Plugin::PREFIX . '_db_version' );

		if ( $current_db_version && version_compare( $current_db_version, '7.0', '>=' ) ) {
			return;
		}

		self::start_update();

		update_site_option( WOWP_Plugin::PREFIX . '_db_version', '7.0' );
	}

	public static function start_update(): void {
		self::update_fields();
	}

	public static function update_fields(): void {
		$results = DBManager::get_all_data();

		if ( empty( $results ) || ! is_array( $results ) ) {
			return;
		}
		foreach ( $results as $result ) {
			$param = maybe_unserialize( $result->param );

			$param = self::update_param( $param );

			$data = [
				'param' => maybe_serialize( $param ),
			];

			$where = [ 'id' => $result->id ];

			$data_formats = [ '%s' ];

			DBManager::update( $data, $where, $data_formats );
		}
	}

	public static function update_param( $param ) {
		$param['mobile_on'] = ! empty( $param['include_mobile'] ) ? 1 : 0;
		$param['mobile']    = ! empty( $param['screen'] ) ? $param['screen'] : '480';

		$param['desktop_on'] = ! empty( $param['include_mobile'] ) ? 1 : 0;
		$param['desktop']    = ! empty( $param['screen_more'] ) ? $param['screen_more'] : '1024';


		$param['language_on'] = ! empty( $param['depending_language'] ) ? 1 : 0;
		$param['language']    = ! empty( $param['screen_more'] ) ? $param['lang'] : '';

		$param['users'] = ! empty( $param['item_user'] ) ? $param['item_user'] : 1;

		$param['browser_opera']   = ! empty( $param['browsers']['opera'] ) ? 1 : 0;
		$param['browser_edge']    = ! empty( $param['browsers']['edge'] ) ? 1 : 0;
		$param['browser_chrome']  = ! empty( $param['browsers']['chrome'] ) ? 1 : 0;
		$param['browser_safari']  = ! empty( $param['browsers']['safari'] ) ? 1 : 0;
		$param['browser_firefox'] = ! empty( $param['browsers']['firefox'] ) ? 1 : 0;
		$param['browser_ie']      = ! empty( $param['browsers']['ie'] ) ? 1 : 0;
		$param['browser_other']   = ! empty( $param['browsers']['other'] ) ? 1 : 0;


		return $param;
	}

}