<?php

use FloatingButton\WOWP_Plugin;

defined( 'ABSPATH' ) || exit;

$args = [
	'rules' => [
		'title' => __( 'Display Rules', 'floating-button' ),
		'icon'  => 'wpie_icon-roadmap',
		[
			'show' => [
				'type'  => 'select',
				'title' => __( 'Display', 'floating-button' ),
				'val'   => 'everywhere',
				'atts'  => [
					'general_start' => __( 'General', 'floating-button' ),
					'shortcode'     => __( 'Shortcode', 'floating-button' ),
					'everywhere'    => __( 'Everywhere', 'floating-button' ),
					'general_end'   => __( 'General', 'floating-button' ),
				],
			],
		],
	],

	'responsive' => [
		'title' => __( 'Responsive Visibility', 'floating-button' ),
		'icon'  => 'wpie_icon-laptop-mobile',
		[

			'mobile' => [
				'type'  => 'number',
				'title' => [
					'label'  => __( 'Hide on smaller screens', 'floating-button' ),
					'name'   => 'mobile_on',
					'toggle' => true,
				],
				'val'   => 480,
				'addon' => 'px',
			],

			'desktop' => [
				'type'  => 'number',
				'title' => [
					'label'  => __( 'Hide on larger screens', 'floating-button' ),
					'name'   => 'desktop_on',
					'toggle' => true,
				],
				'val'   => 1024,
				'addon' => 'px'
			],
		],


	],

	'other' => [
		'title' => __( 'Other', 'floating-button' ),
		'icon'  => 'wpie_icon-gear',
		[
			'fontawesome' => [
				'type'  => 'checkbox',
				'title' => __( 'Disable Font Awesome Icon', 'floating-button' ),
				'val'   => 0,
				'label' => __( 'Disable', 'floating-button' ),
			],
		],
	],

];

$args = apply_filters( WOWP_Plugin::PREFIX . '_rules_options', $args );

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
