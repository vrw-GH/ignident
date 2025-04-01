<?php

use FloatingButton\Dashboard\FieldHelper;

defined( 'ABSPATH' ) || exit;

return [
	// Tooltip options
	'label' => [
		'type'  => 'text',
		'name'  => '[item_tooltip]',
		'title' => __( 'Label Text', 'floating-button' ),
		'class' => 'label-text',
	],

	'label_on' => [
		'type'  => 'checkbox',
		'name'  => '[item_tooltip_include]',
		'title' => __( 'Tooltip', 'floating-button' ),
		'class' => 'tooltip-checkbox',
		'text'  => __( 'Enable', 'floating-button' ),
	],

	'label_open' => [
		'type'  => 'checkbox',
		'name'  => '[item_tooltip_open]',
		'title' => __( 'Hold open', 'floating-button' ),
		'class' => 'tooltip-open',
		'text'  => __( 'Enable', 'floating-button' ),
	],

	// Type options
	'btn_type'   => [
		'type'    => 'select',
		'name'    => '[item_type]',
		'title'   => __( 'Button type', 'floating-button' ),
		'class'   => 'button-type',
		'options' => FieldHelper::item_type(),
	],

	'link' => [
		'type'  => 'text',
		'name'  => '[item_link]',
		'title' => __( 'Link', 'floating-button' ),
		'class' => 'button-type-link',
	],

	'share' => [
		'type'    => 'select',
		'name'    => '[item_share]',
		'title'   => __( 'Social Networks', 'floating-button' ),
		'class'   => 'button-type-share',
		'options' => FieldHelper::share_services(),
	],

	'link_open' => [
		'type'  => 'checkbox',
		'name'  => '[new_tab]',
		'title' => __( 'Open in a new tab', 'floating-button' ),
		'class' => 'button-type-link-open',
		'text'  => __( 'Enable', 'floating-button' ),
	],

	'translate' => [
		'type'    => 'select',
		'name'    => '[gtranslate]',
		'title'   => __( 'Select Language', 'floating-button' ),
		'class'   => 'button-type-gtranslate',
		'options' => FieldHelper::gtranslate(),
	],

	'menu' => [
		'type'    => 'select',
		'name'    => '[item_menu]',
		'title'   => __( 'Menus', 'floating-button' ),
		'class'   => 'button-type-menus',
		'options' => FieldHelper::site_menus(),
	],

	// Icon options
	'icon_type' => [
		'type'  => 'select',
		'name' => '[icon_type]',
		'title' => __( 'Icon type', 'floating-button' ),
		'class' => 'icon-type',
		'default' => '',
		'options' => FieldHelper::icon_type(),
	],

	'item_icon' => [
		'type'  => 'select',
		'name' => '[item_icon]',
		'title' => __( 'Icon', 'floating-button' ),
		'class' => 'icon-type-default choose-icon',
		'default' => 'fas fa-hand-point-up',
		'options' => FieldHelper::icons(),
	],

	'icon_animation' => [
		'type'  => 'select',
		'name' => '[item_icon_anomate]',
		'title' => __( 'Animation', 'floating-button' ),
		'class' => 'icon-type-default',
		'options' => FieldHelper::btn_self_anim(),
	],

	'img_url' => [
		'type'  => 'text',
		'name' => '[custom_icon_url]',
		'title' => __( 'Image URL', 'floating-button' ),
		'class' => 'icon-type-img icon-type-img-url',
	],

	'img_alt' => [
		'type'  => 'text',
		'name' => '[custom_icon_alt]',
		'title' => __( 'Image Alt', 'floating-button' ),
		'class' => 'icon-type-img icon-type-img-alt',
	],

	'emoji' => [
		'type'  => 'text',
		'name' => '[custom_icon_emoji]',
		'title' => __( 'Emoji, Symbol', 'floating-button' ),
		'class' => 'icon-type-emoji',
	],

	'icon_class' => [
		'type'  => 'text',
		'name' => '[custom_icon_class]',
		'title' => __( 'Set Class', 'floating-button' ),
		'class' => 'icon-type-class',
	],

	'icon_close_on' => [
		'type'  => 'checkbox',
		'name' => '[close_button_enable]',
		'title' => __( 'Close Icon', 'floating-button' ),
		'class' => 'icon-type-close',
		'text' => __( 'Enable', 'floating-button' ),
	],

	'icon_close' => [
		'type'  => 'select',
		'name' => '[close_button_icon]',
		'title' => __( 'Icon', 'floating-button' ),
		'class' => 'icon-type-close-choose',
		'default' => 'fas fa-xmark',
		'options' => FieldHelper::icons(),
	],

	// Style options
	'btn_color' => [
		'type'  => 'color',
		'name' => '[button_color]',
		'title' => __( 'Button color', 'floating-button' ),
		'default' => '#009688',
		'class' => 'item-icon-bg',
	],

	'btn_hover_color' => [
		'type'  => 'color',
		'name' => '[button_hcolor]',
		'title' => __( 'Button hover color', 'floating-button' ),
		'default' => '#009688',
	],

	'icon_color' => [
		'type'  => 'color',
		'name' => '[icon_color]',
		'title' => __( 'Icon color', 'floating-button' ),
		'class' => 'item-icon-color',
		'default' => '#ffffff',
	],

	'icon_hover_color' => [
		'type'    => 'color',
		'name'    => '[icon_hcolor]',
		'title'   => __( 'Icon hover color', 'floating-button' ),
		'default' => '#ffffff',
	],

	// Attributes
	'btn_id' => [
		'type'  => 'text',
		'name' => '[button_id]',
		'title' => __( 'ID for element', 'floating-button' ),
	],

	'btn_class' => [
		'type'  => 'text',
		'name' => '[button_class]',
		'title' => __( 'Class for element', 'floating-button' ),
	],

	'link_rel' => [
		'type'  => 'text',
		'name' => '[link_rel]',
		'title' => __( 'Attribute: rel', 'floating-button' ),
	],


];