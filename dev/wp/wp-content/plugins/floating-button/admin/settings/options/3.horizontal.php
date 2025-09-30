<?php


use FloatingButton\WOWP_Plugin;

defined( 'ABSPATH' ) || exit;

$args = [

	'type' => [
		[
			'item_tooltip' => [
				'type'  => 'text',
				'title' => __( 'Label Text', 'floating-button' ),
			],

			'item_tooltip_include' => [
				'type'  => 'checkbox',
				'title' => __( 'Tooltip', 'floating-button' ),
				'label' => __( 'Enable', 'floating-button' ),
			],

		],

		[
			'item_type' => [
				'type'  => 'select',
				'title' => __( 'Button type', 'floating-button' ),
				'atts'  => [
					'link'         => __( 'Link', 'floating-button' ),
					'login'        => __( 'Login', 'floating-button' ),
					'logout'       => __( 'Logout', 'floating-button' ),
					'register'     => __( 'Register', 'floating-button' ),
					'lostpassword' => __( 'Lostpassword', 'floating-button' ),
					'email'        => __( 'Email', 'floating-button' ),
					'telephone'    => __( 'Telephone', 'floating-button' ),
				],
			],

			'item_link' => [
				'type'  => 'text',
				'title' => __( 'Link', 'floating-button' ),
				'class' => 'is-hidden',
			],
		],
	],

	'style' => [

		[
			'button_color' => [
				'type'  => 'text',
				'val'   => '#009688',
				'atts'  => [
					'class'              => 'wpie-color',
					'data-alpha-enabled' => 'true',
				],
				'title' => __( 'Background', 'floating-button' ),
			],

			'button_hcolor' => [
				'type'  => 'text',
				'val'   => '#009688',
				'atts'  => [
					'class'              => 'wpie-color',
					'data-alpha-enabled' => 'true',
				],
				'title' => __( 'Hover Background', 'floating-button' ),
			],

			'icon_color' => [
				'type'  => 'text',
				'val'   => '#ffffff',
				'atts'  => [
					'class'              => 'wpie-color',
					'data-alpha-enabled' => 'true',
				],
				'title' => __( 'Color', 'floating-button' ),
			],

			'icon_hcolor' => [
				'type'  => 'text',
				'val'   => '#ffffff',
				'atts'  => [
					'class'              => 'wpie-color',
					'data-alpha-enabled' => 'true',
				],
				'title' => __( 'Hover Color', 'floating-button' ),
			],

		],

	],

	'icon' => [
		[
			'icon_type' => [
				'type'    => 'select',
				'title'   => __( 'Icon Type', 'floating-button' ),
				'value'   => 'icon',
				'options' => [
					'default' => __( 'Icon', 'floating-button' ),
				]
			],

			'item_icon' => [
				'type'    => 'text',
				'title'   => __( 'Icon', 'floating-button' ),
				'value'   => 'fas fa-hand-point-up',
				'options' => [
					'class' => 'wpie-icon-box',
				],
			],
		],

	],

	'attributes' => [
		[

			'button_id' => [
				'type'  => 'text',
				'title' => __( 'ID for element', 'floating-button' ),
			],

			'button_class' => [
				'type'  => 'text',
				'title' => __( 'Class for element', 'floating-button' ),
			],

			'link_rel' => [
				'type'  => 'text',
				'title' => __( 'Attribute: rel', 'floating-button' ),
			],
		],
	],
];

$args = apply_filters( WOWP_Plugin::PREFIX . '_vertical_options', $args );

$prefix = 'menu_2-';
$data   = [
	'args' => $args,
	'opt'  => [],
	'tabs' => [],
];

foreach ( $args as $i => $group ) {
	$data['tabs'][] = $i;

	if ( is_array( $group ) ) {

		foreach ( $group as $k => $v ) {

			foreach ( $v as $key => $val ) {
				$group_key                 = $prefix . $key;
				$data['opt'][ $group_key ] = $val;
			}

		}
	}
}

return $data;