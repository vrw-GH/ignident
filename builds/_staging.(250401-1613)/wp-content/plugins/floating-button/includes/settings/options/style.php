<?php

use FloatingButton\Dashboard\FieldHelper;

defined( 'ABSPATH' ) || exit;

return [
	'position' => [
		'type'    => 'select',
		'name'    => '[position]',
		'title'   => __( 'Position', 'floating-button' ),
		'default' => 'flBtn-position-br',
		'options' => FieldHelper::btn_position(),
	],
	'v_offset' => [
		'type'    => 'number',
		'name'    => '[v_offset]',
		'title'   => __( 'Vertical offset', 'floating-button' ),
		'default' => '0',
		'class'   => 'has-addon-right has-background',
		'addon'   => 'px',
	],

	'h_offset' => [
		'type'    => 'number',
		'name'    => '[h_offset]',
		'title'   => __( 'Horizontal offset', 'floating-button' ),
		'default' => '0',
		'class'   => 'has-addon-right has-background',
		'addon'   => 'px',
	],

	'sub_btn_animation' => [
		'type'    => 'select',
		'name'    => '[animation]',
		'title'   => __( 'Sub-buttons Animation', 'floating-button' ),
		'options' => FieldHelper::btn_animation(),
	],

	'shape' => [
		'type'    => 'select',
		'name'    => '[shape]',
		'title'   => __( 'Shape', 'floating-button' ),
		'default' => 'flBtn-shape-circle',
		'options' => FieldHelper::btn_shape(),
	],

	'size' => [
		'type'    => 'select',
		'name'    => '[size]',
		'title'   => __( 'Size', 'floating-button' ),
		'default' => 'flBtn-size-medium',
		'options' => FieldHelper::btn_size(),
	],

	'label_size' => [
		'type'    => 'number',
		'name'    => '[label_size]',
		'title'   => __( 'Button font size', 'floating-button' ),
		'default' => '24',
		'class'   => 'has-addon-right has-background',
		'addon'   => 'px',
	],

	'label_box' => [
		'type'    => 'number',
		'name'    => '[label_box]',
		'title'   => __( 'Button box size', 'floating-button' ),
		'default' => '60',
		'class'   => 'has-addon-right has-background',
		'addon'   => 'px',
	],


	'ul_size' => [
		'type'    => 'number',
		'name'    => '[ul_size]',
		'title'   => __( 'Sub Buttons size', 'floating-button' ),
		'default' => '16',
		'class'   => 'has-addon-right has-background',
		'addon'   => 'px',
	],

	'ul_box' => [
		'type'    => 'number',
		'name'    => '[ul_box]',
		'title'   => __( 'Sub Buttons box size', 'floating-button' ),
		'default' => '40',
		'class'   => 'has-addon-right has-background',
		'addon'   => 'px',
	],

	'shadow'             => [
		'type'    => 'select',
		'name'    => '[shadow]',
		'title'   => __( 'Shadow', 'floating-button' ),
		'default' => '',
		'options' => FieldHelper::btn_shadow(),
	],

	// Tooltip
	'tooltip_size_check' => [
		'type'    => 'select',
		'name'    => '[tooltip_size_check]',
		'title'   => __( 'Font size', 'floating-button' ),
		'options' => [
			'default' => __( 'Default', 'floating-button' ),
		],
	],

	'tooltip_size' => [
		'type'    => 'number',
		'name'    => '[tooltip_size]',
		'title'   => __( 'Size for main button', 'floating-button' ),
		'default' => '24',
		'class'   => 'has-addon-right has-background tooltip-size',
		'addon'   => 'px',
	],

	'tooltip_ul_size' => [
		'type'    => 'number',
		'name'    => '[tooltip_ul_size]',
		'title'   => __( 'Size for main sub buttons', 'floating-button' ),
		'default' => '16',
		'class'   => 'has-addon-right has-background tooltip-size',
		'addon'   => 'px',
	],

	'tooltip_bg'    => [
		'type'    => 'color',
		'name'    => '[tooltip_background]',
		'title'   => __( 'Tooltip background', 'floating-button' ),
		'default' => '#585858',
	],
	'tooltip_color' => [
		'type'    => 'color',
		'name'    => '[tooltip_color]',
		'title'   => __( 'Tooltip color', 'floating-button' ),
		'default' => '#ffffff',
	],

	'extra_style' => [
		'type'  => 'textarea',
		'name'  => '[extra_style]',
		'class' => 'is-full'
	],
];
