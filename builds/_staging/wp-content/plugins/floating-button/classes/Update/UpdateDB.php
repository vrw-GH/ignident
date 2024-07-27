<?php

namespace FloatingButton\Update;

defined( 'ABSPATH' ) || exit;

use FloatingButton\Dashboard\DBManager;
use FloatingButton\WOW_Plugin;

class UpdateDB {

	public static function init(): void {
		$current_db_version = get_option( WOW_Plugin::PREFIX . '_db_version' );

		if ( $current_db_version && version_compare( $current_db_version, '6.0', '>=' ) ) {
			return;
		}

		self::updateDB();
		self::updateDBFields();
		self::updateOption();

		update_option( WOW_Plugin::PREFIX . '_db_version', '6.0' );

	}

	private static function updateDB(): void {
		global $wpdb;
		$table = $wpdb->prefix . WOW_Plugin::PREFIX;

		$columns = "
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			title VARCHAR(200) NOT NULL,
			param LONGTEXT,
			status BOOLEAN,
			mode BOOLEAN,
			tag TEXT,
			UNIQUE KEY id (id)
			";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE $table ($columns) DEFAULT CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->collate};";
		dbDelta( $sql );
	}

	private static function updateOption(): void {
		$license = get_option( 'wow_license_key_fbtnp' );
		$status  = get_option( 'wow_license_status_fbtnp' );
		if($license !== false) {
			update_option( 'wow_license_key_' . WOW_Plugin::PREFIX, $license );
		}

		if($status !== false ) {
			update_option( 'wow_license_status_' . WOW_Plugin::PREFIX, $status );
		}
	}

	private static function updateDBFields(): void {
		$results = DBManager::get_all_data();

		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $val ) {
				$param     = maybe_unserialize( $val->param );
				$status    = ! empty( $param['menu_status'] ) ? 0 : 1;
				$test_mode = ! empty( $param['test_mode'] ) ? 1 : 0;

				$param = self::updateShow( $param );
				$param = self::updateBrowsers( $param );
				$param = self::updateSchedule( $param );
				$param = self::updateImgType( $param );
				$param = self::updateImgColor( $param );
				$param = self::updateUsers( $param );
				$param = self::updateStyles( $param );


				$data = [
					'status' => absint( $status ),
					'mode'   => absint( $test_mode ),
					'tag'    => '',
					'param'  => maybe_serialize( $param ),
				];

				$where = [ 'id' => $val->id ];

				$data_formats = [ '%d', '%d', '%s', '%s' ];

				DBManager::update( $data, $where, $data_formats );
			}
		}
	}

	private static function updateStyles( $param ) {

		return $param;
	}

	private static function updateUsers( $param ) {
		$old_user           = $param['user_role'];
		$param['user_role'] = [
			$old_user => 1
		];

		return $param;
	}

	private static function updateImgType( $param ) {

		$param['button_icon_type'] = 'default';

		if ( ! empty( $param['custom_icon'] ) ) {
			$param['button_icon_type'] = 'img';
		}

		if ( ! empty( $param['menu_1']['custom_icon'] ) && is_array( $param['menu_1']['custom_icon'] ) ) {
			$param['menu_1']['icon_type'] = [];
			foreach ( $param['menu_1']['custom_icon'] as $key => $val ) {
				if ( ! empty( $param['menu_1']['custom_icon'][ $key ] ) ) {
					$param['menu_1']['icon_type'][ $key ] = 'img';
				} else {
					$param['menu_1']['icon_type'][ $key ] = 'default';
				}

			}
		}

		if ( ! empty( $param['menu_2']['custom_icon'] ) && is_array( $param['menu_2']['custom_icon'] ) ) {
			$param['menu_2']['icon_type'] = [];
			foreach ( $param['menu_2']['custom_icon'] as $key => $val ) {
				if ( ! empty( $param['menu_2']['custom_icon'][ $key ] ) ) {
					$param['menu_2']['icon_type'][ $key ] = 'img';
				} else {
					$param['menu_2']['icon_type'][ $key ] = 'default';
				}
			}
		}

		return $param;
	}

	private static function updateImgColor($param) {
		$param['icon_hcolor'] = $param['icon_color'];

		if ( ! empty( $param['menu_1']['icon_color'] ) && is_array( $param['menu_1']['icon_color'] ) ) {
			foreach ( $param['menu_1']['icon_color'] as $key => $val ) {
				$param['menu_1']['icon_hcolor'][ $key ] = $val;
			}
		}

		if ( ! empty( $param['menu_2']['icon_color'] ) && is_array( $param['menu_2']['icon_color'] ) ) {
			foreach ( $param['menu_2']['icon_color'] as $key => $val ) {
				$param['menu_2']['icon_hcolor'][ $key ] = $val;
			}
		}

		return $param;

	}

	private static function updateSchedule( $param ) {

		$week_old       = $param['weekday'] ?? '';
		$time_start_old = $param['time_start'] ?? '';
		$time_end_old   = $param['time_end'] ?? '';
		$dates_old      = ! empty( $param['set_dates'] ) ? 1 : 0;
		$date_start_old = $param['date_start'] ?? '';
		$date_end_old   = $param['date_end'] ?? '';

		$param['weekday']    = [];
		$param['time_start'] = [];
		$param['time_end']   = [];
		$param['dates']      = [];
		$param['date_start'] = [];
		$param['date_end']   = [];

		$param['weekday'][0]    = $week_old;
		$param['time_start'][0] = $time_start_old;
		$param['time_end'][0]   = $time_end_old;
		$param['dates'][0]      = $dates_old;
		$param['date_start'][0] = $date_start_old;
		$param['date_end'][0]   = $date_end_old;


		return $param;
	}

	private static function updateBrowsers( $param ) {

		if ( empty( $param['all_browser'] ) ) {
			return $param;
		}
		$param['browsers']            = [];
		$param['browsers']['opera']   = ! empty( $param['br_opera'] ) ? 1 : 0;
		$param['browsers']['edge']    = ! empty( $param['br_edge'] ) ? 1 : 0;
		$param['browsers']['chrome']  = ! empty( $param['br_chrome'] ) ? 1 : 0;
		$param['browsers']['safari']  = ! empty( $param['br_safari'] ) ? 1 : 0;
		$param['browsers']['firefox'] = ! empty( $param['br_firefox'] ) ? 1 : 0;
		$param['browsers']['ie']      = ! empty( $param['br_ie'] ) ? 1 : 0;
		$param['browsers']['other']   = ! empty( $param['br_other'] ) ? 1 : 0;

		return $param;
	}

	private static function updateShow( $param ): array {
		$show_old = ! empty( $param['show'] ) ? $param['show'] : 'shortcode';

		$param['show']      = [];
		$param['operator']  = [];
		$param['page_type'] = [];
		$param['ids']       = [];

		$param['show'][0]      = 'shortcode';
		$param['operator'][0]  = '1';
		$param['page_type'][0] = 'is_front_page';
		$param['ids'][0]       = '';


		switch ( $show_old ) {
			case 'all':
				$param['show'][0] = 'everywhere';
				break;
			case 'onlypost':
				$param['show'][0] = 'post_all';
				break;
			case 'posts':
				$param['show'][0] = 'post_selected';
				$param['ids'][0]  = ! empty( $param['id_post'] ) ? $param['id_post'] : '';
				break;
			case 'postsincat':
				$param['show'][0] = 'post_category';
				$param['ids'][0]  = ! empty( $param['id_post'] ) ? $param['id_post'] : '';
				break;
			case 'expost':
				$param['show'][0]     = 'post_selected';
				$param['operator'][0] = 0;
				$param['ids'][0]      = ! empty( $param['id_post'] ) ? $param['id_post'] : '';
				break;
			case 'onlypage':
				$param['show'][0] = 'page_all';
				break;
			case 'pages':
				$param['show'][0] = 'page_selected';
				$param['ids'][0]  = ! empty( $param['id_post'] ) ? $param['id_post'] : '';
				break;
			case 'expage':
				$param['show'][0]     = 'page_selected';
				$param['operator'][0] = 0;
				$param['ids'][0]      = ! empty( $param['id_post'] ) ? $param['id_post'] : '';
				break;
			case 'homepage':
				$param['show'][0]      = 'page_type';
				$param['page_type'][0] = 'is_front_page';
				break;
			case 'searchpage':
				$param['show'][0]      = 'page_type';
				$param['page_type'][0] = 'is_search';
				break;
			case 'archivepage':
				$param['show'][0]      = 'page_type';
				$param['page_type'][0] = 'is_archive';
				break;
			case 'error_page':
				$param['show'][0]      = 'page_type';
				$param['page_type'][0] = 'is_404';
				break;
			case 'post-page':
				$param['show'][0]      = 'page_type';
				$param['page_type'][0] = 'is_home';
				break;
		}

		return $param;

	}


}