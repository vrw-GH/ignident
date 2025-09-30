<?php

use FloatingButton\WOWP_Plugin;

defined( 'ABSPATH' ) || exit;

$args = [

	'position' => [
		'title' => __( 'Position', 'floating-button' ),
		'icon'  => 'wpie_icon-pointer',
		[
			'position' => [
				'type'  => 'select',
				'title' => __( 'Position', 'floating-button' ),
				'val'   => 'flBtn-position-br',
				'atts'  => [
					'flBtn-position-br' => __( 'Bottom Right', 'floating-button' ),
					'flBtn-position-bl' => __( 'Bottom Left', 'floating-button' ),
					'flBtn-position-b'  => __( 'Bottom Center', 'floating-button' ),
					'flBtn-position-tr' => __( 'Top Right', 'floating-button' ),
					'flBtn-position-tl' => __( 'Top Left', 'floating-button' ),
					'flBtn-position-t'  => __( 'Top Center', 'floating-button' ),
					'flBtn-position-l'  => __( 'Left', 'floating-button' ),
					'flBtn-position-r'  => __( 'Right', 'floating-button' ),
				],
			],
		],
	],

	'appearance' => [
		'title' => __( 'Appearance', 'floating-button' ),
		'icon'  => 'wpie_icon-paintbrush',
		[
			'shape' => [
				'type'  => 'select',
				'title' => __( 'Shape', 'floating-button' ),
				'val'   => 'square',
				'atts'  => [
					'flBtn-shape-circle'  => __( 'Circle', 'floating-button' ),
					'flBtn-shape-ellipse' => __( 'Ellipse', 'floating-button' ),
					'flBtn-shape-square'  => __( 'Square', 'floating-button' ),
					'flBtn-shape-rsquare' => __( 'Rounded square', 'floating-button' ),
				],
			],

			'shadow' => [
				'type'    => 'select',
				'title'   => __( 'Shadow', 'floating-button' ),
				'options' => [
					''  => __( 'Yes', 'floating-button' ),
					'1' => __( 'No', 'floating-button' ),
				],
			],

			'animation' => [
				'type'    => 'select',
				'title'   => __( 'Sub-buttons Animation', 'floating-button' ),
				'options' => [
					'' => __( 'Fade', 'floating-button' ),
				]
			]

		],

	],

	'size' => [
		'title' => __( 'Size', 'floating-button' ),
		'icon'  => 'wpie_icon-text',
		[
			'size' => [
				'type'  => 'select',
				'title' => __( 'Size', 'floating-button' ),
				'options' => [
					'flBtn-size-small'  => __( 'Small', 'floating-button' ),
					'flBtn-size-medium' => __( 'Medium', 'floating-button' ),
					'flBtn-size-large'  => __( 'Large', 'floating-button' ),
				]
			],
		]
	],

	'tooltip' => [
		'title' => __( 'Tooltip', 'floating-button' ),
		'icon'  => 'wpie_icon-new',
		[
			'tooltip_size_check' => [
				'type'  => 'select',
				'title' => __( 'Font size', 'floating-button' ),
				'options' => [
					'default' => __( 'Default', 'floating-button' ),
				]
			],
		],

		[
			'tooltip_background' => [
				'type'  => 'text',
				'val'   => '#585858',
				'atts'  => [
					'class'              => 'wpie-color',
					'data-alpha-enabled' => 'true',
				],
				'title' => __( 'Background', 'floating-button' ),
			],

			'tooltip_color' => [
				'type'  => 'text',
				'val'   => '#ffffff',
				'atts'  => [
					'class'              => 'wpie-color',
					'data-alpha-enabled' => 'true',
				],
				'title' => __( 'Color', 'floating-button' ),
			],
		]
	],


];

$args = apply_filters( WOWP_Plugin::PREFIX . '_settings_options', $args );

$data = [
	'args' => $args,
	'opt'  => [],
];

foreach ( $args as $i => $group ) {
	if ( is_array( $group ) ) {
		foreach ( $group as $k => $v ) {
			if ( is_array( $v ) ) {
				foreach ( $v as $key => $val ) {
					$data['opt'][ $key ] = $val;
				}
			}
		}
	}
}

return $data;