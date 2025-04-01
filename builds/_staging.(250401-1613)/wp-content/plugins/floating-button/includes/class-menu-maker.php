<?php

namespace FloatingButton;

use FloatingButton\Publisher\Navigation;

defined( 'ABSPATH' ) || exit;

class Menu_Maker {

	/**
	 * @var mixed
	 */
	private $id;
	/**
	 * @var mixed
	 */
	private $param;
	/**
	 * @var mixed
	 */
	private $title;
	private array $menus;

	public function __construct( $id, $param, $title ) {
		$this->id    = $id;
		$this->param = $param;
		$this->title = $title;
		$this->menus = [];
	}

	public function init(): string {

		$menu = $this->wrapper();
		$menu .= $this->main_button();
		if ( $this->is_main() ) {
			$menu .= $this->sub_buttons();
		}
		$menu .= '</div>';
		$menu .= $this->get_menu();

		return $menu;

	}

	public function wrapper(): string {
		$position      = ! empty( $this->param['position'] ) ? ' ' . $this->param['position'] : ' flBtn-position-br';
		$shape         = ! empty( $this->param['shape'] ) ? ' ' . $this->param['shape'] : ' flBtn-shape-circle';
		$size          = ! empty( $this->param['size'] ) ? ' ' . $this->param['size'] : ' flBtn-size-medium';
		$animation     = ! empty( $this->param['animation'] ) ? ' ' . $this->param['animation'] : '';
		$btn_animation = ! empty( $this->param['button_animation'] ) ? ' flBtn-animated ' . $this->param['button_animation'] : '';
		$shadow        = empty( $this->param['shadow'] ) ? ' -shadow' : '';
		$classes       = $position . $shape . $size . $shadow . $animation . $btn_animation;

		return '<div class="flBtn ' . esc_attr( $classes ) . '" id="floatBtn-' . absint( $this->id ) . '">';
	}

	public function main_button(): string {
		$param     = $this->param;
		$main_type = ! empty( $param['item_type'] ) ? $param['item_type'] : 'main';
		$btn       = '';
		$link      = ! empty( $param['item_link'] ) ? $param['item_link'] : '#';
		$item_tooltip = ! empty( $param['item_tooltip'] ) ? $param['item_tooltip'] : '';

		switch ( $main_type ) {
			case 'main':
				$btn .= $this->create_checkbox();
				$btn .= '<label for="flBtn-' . absint( $this->id ) . '" ' . $this->main_param( ' flBtn-label' ) . ' data-role="main" aria-label="' . esc_attr( $item_tooltip ) . '">' . $this->main_icon() . $this->close_icon() . '</label>';
				break;
			case 'link':
				$target = ! empty( $param['new_tab'] ) ? '_blank' : '_self';
				$btn    .= '<a href="' . esc_url( $link ) . '" target="' . esc_attr( $target ) . '" data-role="main" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'share':
				$share = mb_strtolower( $param['item_share'] );
				$btn   .= '<a rel="button" data-role="main" data-share="' . esc_attr( $share ) . '" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'translate':
				$glang = $param['gtranslate'] ?? '';
				$btn   .= '<a rel="button" data-role="main" data-google-lang="' . esc_attr( $glang ) . '" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'print':
				$btn .= '<a role="button" data-role="main" data-btn-type="print" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'login':
				$btn .= '<a rel="nofollow" data-role="main" href="' . wp_login_url( $link ) . '" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'logout':
				$btn .= '<a rel="nofollow" data-role="main" href="' . wp_logout_url( $link ) . '" ' . $this->main_param() . '>' . $this->main_icon() . '</a>';
				break;
			case 'register':
				$btn .= '<a rel="nofollow" data-role="main" href="' . wp_registration_url() . '" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'lostpassword':
				$btn .= '<a rel="nofollow" href="' . wp_lostpassword_url( $link ) . '" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'totop':
				$btn .= '<a role="button" data-role="main" data-btn-type="toTop" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'tobottom':
				$btn .= '<a role="button" data-role="main" data-btn-type="tobottom" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'smoothscroll':
				$btn .= '<a role="button" data-role="main" data-btn-type="scroll" href="' . esc_url( $link ) . '" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'goBack':
				$btn .= '<a role="button" data-role="main" data-btn-type="back" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'goForward':
				$btn .= '<a role="button" data-role="main" data-btn-type="forward" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
			case 'next_post':
				$next_post = get_next_post( true );

				if ( ! empty( $next_post ) ) {
					$link = get_permalink( $next_post );
					$btn  .= '<a href="' . esc_url( $link ) . '" data-role="main" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				}
				break;
			case 'previous_post':
				$previous_post = get_previous_post( true );
				if ( ! empty( $previous_post ) ) {
					$link = get_permalink( $previous_post );
					$btn  .= '<a href="' . esc_url( $link ) . '" data-role="main" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				}
				break;
			case 'menu':
				if ( ! empty( $param['item_menu'] ) ) {
					$menu_order    = $param['item_menu'];
					$this->menus[] = $menu_order;
					$btn           .= '<a role="button" data-role="main" data-btn-type="content-menu" data-btn-call-menu="' . esc_attr( $menu_order ) . '" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				}
				break;
			case 'like':
				if ( is_singular() ) {
					$likes = get_post_meta( get_the_ID(), '_wowp_likes', true );
				}
				$likes = !empty($likes) ? $likes : 0;
				$btn .= '<a rel="button" data-role="main" data-btn-type="like" data-counter="'.absint($likes).'" ' . $this->main_param( ' flBtn-label' ) . '>' . $this->main_icon() . '</a>';
				break;
		}

		return $btn;

	}

	public function main_param( $defClass = '' ): string {
		$param = $this->param;

		if ( ! empty( $param['item_tooltip_include'] ) ) {
			$open    = ! empty( $param['item_tooltip_open'] ) ? ' data-btn-tooltip="show"' : '';
			$tooltip = ' data-tooltip="' . esc_attr( $param['item_tooltip'] ) . '"' . wp_kses( $open, [ 'data-btn-tooltip' => [] ] );
		} else {
			$tooltip = '';
		}

		$class    = ! empty( $param['main_button_class'] ) ? $param['main_button_class'] : '';
		$link_rel = ! empty( $param['link_rel'] ) ? ' rel="' . esc_attr( $param['link_rel'] ) . '"' : '';

		return 'class="' . esc_attr( $class . $defClass ) . '"' . $tooltip . $link_rel;
	}

	public function main_icon(): string {
		$param  = $this->param;
		$type   = ! empty( $param['button_icon_type'] ) ? $param['button_icon_type'] : 'default';
		$action = ! empty( $param['close_button_enable'] ) ? ' data-action="open"' : '';

		switch ( $type ) {
			case 'default':
				$animate = ! empty( $param['button_icon_anomate'] ) ? ' ' . $param['button_icon_anomate'] : '';
				$icon    = '<i class="notranslate ' . esc_attr( $param['button_icon'] . $animate ) . '"' . wp_kses( $action, [ 'data-action' => [] ] ) . '></i>';
				break;
			case 'img':
				$alt  = ! empty( $param['custom_icon_alt'] ) ? $param['custom_icon_alt'] : '';
				$url  = $param['custom_icon_url'];

				$icon = '<img src="' . esc_url( $url ) . '"' . wp_kses( $action, [ 'data-action' => [] ] ) . ' alt="' . esc_attr( $alt ) . '"' . ' class="notranslate">'; // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
				break;
			case 'emoji':
				$icon = '<span class="notranslate flbtn-emoji"' . wp_kses( $action, [ 'data-action' => [] ] ) . '>' . esc_html( $param['custom_icon_emoji'] ) . '</span>';
				break;
			case 'class':
				$icon = '<i class="notranslate ' . esc_attr( $param['custom_icon_class'] ) . '"' . wp_kses( $action, [ 'data-action' => [] ] ) . '></i>';
				break;
			default:
				$icon = '';
				break;
		}

		return $icon;
	}

	public function close_icon(): string {
		$param      = $this->param;
		$icon_class = ! empty( $param['close_button_icon'] ) ? $param['close_button_icon'] : 'fas fa-xmark';

		if ( ! empty( $param['close_button_enable'] ) ) {
			return '<i class="notranslate ' . esc_attr( $icon_class ) . '" data-action="close"></i>';
		}

		return '';

	}

	public function create_checkbox(): string {
		$param   = $this->param;
		$checked = ! empty( $param['hold_buttons_open'] ) ? ' checked="checked"' : '';
		$class   = ! empty( $param['extra_checkbox_class'] ) ? ' class="' . esc_attr( $param['extra_checkbox_class'] ) . ' checkbox"' : 'class="checkbox"';
		$attr    = $checked . $class;

		return '<input type="checkbox" id="flBtn-' . absint( $this->id ) . '"' . wp_kses( $attr, [
				'checked' => [],
				'class'   => []
			] ) . '>';

	}

	public function sub_buttons() {
		$param = $this->param;
		$btns  = [
			'flBtn-first'  => $param['menu_1'] ?? [],
			'flBtn-second' => $param['menu_2'] ?? [],
		];

		$btn = '';

		foreach ( $btns as $key => $val ) {
			if ( empty( $val['item_type'] ) ) {
				continue;
			}
			$btn   .= '<ul class="' . esc_attr( $key ) . '">';
			$count = count( $val['item_type'] );
			for ( $i = 0; $i < $count; $i ++ ) {

				$sub_btn = $this->create_subbtn( $val, $i );
				if ( empty( $sub_btn ) ) {
					continue;
				}
				$btn .= '<li>';
				$btn .= $sub_btn;
				$btn .= '</li>';


			}

			$btn .= '</ul>';
		}

		return $btn;
	}

	public function create_subbtn( $arr, $i ) {
		$type       = ! empty( $arr['item_type'][ $i ] ) ? $arr['item_type'][ $i ] : '';
		$icon       = $this->subbtn_icon( $arr, $i );
		$link_param = $this->subbtn_param( $arr, $i );
		$link       = ! empty( $arr['item_link'][ $i ] ) ? $arr['item_link'][ $i ] : '#';
		$btn        = '';

		switch ( $type ) {
			case 'link':
				$target = ! empty( $arr['new_tab'][ $i ] ) ? '_blank' : '_self';
				$btn    .= '<a href="' . esc_url( $link ) . '" target="' . esc_attr( $target ) . '" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'share':
				$share = mb_strtolower( $arr['item_share'][ $i ] );
				$btn   .= '<a role="button" data-share="' . esc_attr( $share ) . '" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'translate':
				$link  = '#';
				$glang = $arr['gtranslate'][ $i ] ?? '';
				$btn   .= '<a href="' . $link . '" data-google-lang="' . esc_attr( $glang ) . '" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'print':
				$btn .= '<a role="button" data-btn-type="print" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'login':
				$btn .= '<a rel="nofollow" href="' . wp_login_url( $link ) . '" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'logout':
				$btn .= '<a rel="nofollow" href="' . wp_logout_url( $link ) . '" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'register':
				$btn .= '<a rel="nofollow" href="' . wp_registration_url() . '" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'lostpassword':
				$btn .= '<a rel="nofollow" href="' . wp_lostpassword_url( $link ) . '" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'totop':
				$btn .= '<a role="button" data-btn-type="toTop" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'tobottom':
				$btn .= '<a role="button" data-btn-type="tobottom" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'smoothscroll':
				$btn .= '<a href="' . esc_url( $link ) . '" data-btn-type="scroll" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'goBack':
				$btn .= '<a role="button" data-btn-type="back" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'goForward':
				$btn .= '<a role="button" data-btn-type="forward" ' . $link_param . '>' . $icon . '</a>';
				break;
			case 'menu':
				if ( ! empty( $arr['item_menu'][ $i ] ) ) {
					$menu_order    = $arr['item_menu'][ $i ];
					$this->menus[] = $menu_order;
					$btn           .= '<a role="button" data-btn-type="content-menu" data-btn-call-menu="' . esc_attr( $menu_order ) . '" ' . $link_param . '>' . $icon . '</a>';
				}
				break;

			case 'next_post':
				$next_post = get_next_post( true );
				if ( ! empty( $next_post ) ) {
					$link = get_permalink( $next_post );
					$btn  .= '<a href="' . esc_url( $link ) . '" ' . $link_param . '>' . $icon . '</a>';
				}
				break;
			case 'previous_post':
				$previous_post = get_previous_post( true );
				if ( ! empty( $previous_post ) ) {
					$link = get_permalink( $previous_post );
					$btn  .= '<a href="' . esc_url( $link ) . '" ' . $link_param . '>' . $icon . '</a>';
				}
				break;
			case 'like':
				if ( is_singular() ) {
					$likes = get_post_meta( get_the_ID(), '_wowp_likes', true );
				}
				$likes = !empty($likes) ? $likes : 0;
				$btn .= '<a rel="button" data-btn-type="like" data-counter="'.absint($likes).'" ' . $link_param . '>' . $icon . '</a>';
				break;
		}

		return $btn;

	}

	public function subbtn_icon( $arr, $i ): string {
		$type = ! empty( $arr['icon_type'][ $i ] ) ? $arr['icon_type'][ $i ] : 'default';

		switch ( $type ) {
			case 'default':
				$animate = ! empty( $arr['item_icon_anomate'][ $i ] ) ? ' ' . $arr['item_icon_anomate'][ $i ] : '';
				$icon    = '<i class="notranslate ' . esc_attr( $arr['item_icon'][ $i ] . $animate ) . '"></i>';
				break;
			case 'img':
				$alt  = ! empty( $arr['custom_icon_alt'][ $i ] ) ? $arr['custom_icon_alt'][ $i ] : '';
				$url  = $arr['custom_icon_url'][ $i ];
				$icon = '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '"' . ' class="notranslate">'; // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
				break;
			case 'emoji':
				$icon = '<span class="notranslate flbtn-emoji">' . esc_html( $arr['custom_icon_emoji'][ $i ] ) . '</span>';
				break;
			case 'class':
				$icon = '<i class="notranslate ' . esc_attr( $arr['custom_icon_class'][ $i ] ) . '"></i>';
				break;
			default:
				$icon = '';
				break;
		}

		return $icon;
	}

	public function subbtn_param( $arr, $i ) {
		$params = '';

		if ( ! empty( $arr['item_tooltip_include'][ $i ] ) ) {
			$open   = ! empty( $arr['item_tooltip_open'][ $i ] ) ? ' data-btn-tooltip="show"' : '';
			$params .= 'data-tooltip="' . esc_attr( $arr['item_tooltip'][ $i ] ) . '"' . wp_kses( $open, [ 'data-btn-tooltip' => [] ] );
		}
		$params .= ! empty( $arr['button_class'][ $i ] ) ? ' class="' . esc_attr( $arr['button_class'][ $i ] ) . '"' : '';
		$params .= ! empty( $arr['button_id'][ $i ] ) ? ' id="' . esc_attr( $arr['button_id'][ $i ] ) . '"' : '';
		$params .= ! empty( $arr['link_rel'][ $i ] ) ? ' rel="' . esc_attr( $arr['link_rel'][ $i ] ) . '"' : '';

		return $params;
	}

	public function get_menu(): string {
		$menus = '';
		if ( empty( $this->menus ) ) {
			return $menus;
		}

		foreach ( $this->menus as $menu ) {
			$menus_obj  = wp_get_nav_menu_object( absint( $menu ) );
			$menu_items = wp_get_nav_menu_items( $menu );

			$menus .= '<div class="flBtn_menu-wrapper is-hidden" data-btn-menu="' . absint( $menu ) . '">';
			$menus .= '<div class="flBtn_header-menu">';
			$menus .= '<div class="flBtn_title">' . esc_attr( $menus_obj->name ) . '</div>';
			$menus .= '<button class="flBtn_close">&times;</button>';
			$menus .= '</div>';

			$out = wp_nav_menu( [
				'theme_location' => '',
				'menu'           => $menu,
				'container'      => false,
				'menu_class'     => 'flBtn_menu-list',
				'echo'           => false,
				'fallback_cb'    => '',
				'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'depth'          => 0,
				'walker'         => new Navigation,
			] );

			$menus .= $out;
			$menus .= '</div>';
		}

		return $menus;
	}

	public function is_main(): bool {
		return $this->param['item_type'] === 'main';
	}

}