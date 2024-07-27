<?php

namespace FloatingButton\Publisher;

defined( 'ABSPATH' ) || exit;

use FloatingButton\Dashboard\DBManager;

class Display {

	public static function init(): array {

		$results = DBManager::get_all_data();
		$arr = [];
		if(empty($results)) {
			return $arr;
		}
		foreach ( $results as $result ) {

			if ( Conditions::init( $result ) === false ) {
				continue;
			}

			$param = maybe_unserialize( $result->param );

			$count = 0;
			if ( ! empty( $param['show'] ) && is_array( $param['show'] ) ) {
				$count = count( $param['show'] );
			}

			if ( $count === 0 ) {
				$param['show'] = [ 0 => 'shortcode' ];
			}

			$id = $result->id;

			if ( $count > 0 ) {
				for ( $i = 0; $i < $count; $i ++ ) {

					$show = $param['show'][ $i ];
					if ( str_contains( $show, 'custom_post_' ) && self::custom_post( $i, $param ) ) {
						$arr[ $id ] = $result;
						continue;

					}

					switch ( $show ) {
						case 'everywhere':
							$arr[ $id ] = $result;
							break;
						case 'post_all':
							if ( is_singular( 'post' ) ) {
								$arr[ $id ] = $result;
							}
							break;
						case 'page_all':
							if ( is_singular( 'page' ) ) {
								$arr[ $id ] = $result;
							}
							break;
						case 'page_type':
							if ( (bool) $param['operator'][ $i ] === call_user_func( $param['page_type'][ $i ] ) ) {
								$arr[ $id ] = $result;
							}
							break;
						case 'page_selected':
							if ( self::page_selected( $i, $param ) === true ) {
								$arr[ $id ] = $result;
							}
							break;
						case 'post_selected':
							if ( self::post_selected( $i, $param ) === true ) {
								$arr[ $id ] = $result;
							}
							break;

						case 'post_category':
							if ( self::post_category( $i, $param ) === true ) {
								$arr[ $id ] = $result;

							}
							break;


					}

				}
			}


		}

		return $arr;
	}

	private static function custom_post( $i, $param ): bool {
		$show = $param['show'][ $i ];
		$post = explode( '_', $param['show'][ $i ] )[3];

		if ( str_contains( $show, 'custom_post_selected' ) ) {
			if ( is_singular( $post ) ) {
				return (bool) $param['operator'][ $i ] === is_single( explode( ',', $param['ids'][ $i ] ) );
			}
		}
		if ( str_contains( $show, 'custom_post_tax' ) ) {

			$args = [
				'object_type' => [ $post ]
			];

			$taxonomies = get_taxonomies( $args );
			$ids        = preg_split( "/[,]+/", $param['ids'][ $i ] );

			if ( is_single() ) {
				return (bool) $param['operator'][ $i ] === has_term( $ids, reset($taxonomies), get_the_ID() );
			}

			if ( is_tax() ) {
				return (bool) $param['operator'][ $i ] === is_tax( $taxonomies, $ids );
			}
			return false;


		}
		if ( str_contains( $show, 'custom_post_all' ) ) {
			return is_singular( $post );
		}

		return false;
	}

	private static function post_category( $i, $param ): bool {

		if ( is_single() ) {
			return (bool) $param['operator'][ $i ] === in_category( explode( ',', $param['ids'][ $i ] ) );
		}

		if ( is_category() ) {

			return (bool) $param['operator'][ $i ] === is_category( explode( ',', $param['ids'][ $i ] ) );
		}

		return false;
	}

	private static function page_selected( $i, $param ): bool {
		if ( is_page() ) {
			$pages     = preg_split( "/[,]+/", $param['ids'][ $i ] );
			$pages_arr = map_deep( $pages, 'trim' );

			return (bool) $param['operator'][ $i ] === is_page( $pages_arr );
		}

		return false;

	}

	private static function post_selected( $i, $param ): bool {
		if ( is_singular( 'post' ) ) {
			return (bool) $param['operator'][ $i ] === is_single( explode( ',', $param['ids'][ $i ] ) );
		}

		return false;
	}

}