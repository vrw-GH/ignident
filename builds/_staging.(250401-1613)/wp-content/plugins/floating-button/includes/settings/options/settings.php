<?php

defined( 'ABSPATH' ) || exit;

return [

	// Show/Hide Settings
	'show_after' => [
		'type'  => 'number',
		'name' => '[showAfterPosition]',
		'title' => __( 'Show after Position', 'floating-button' ),
		'default' => '0',
		'class' => 'has-addon-right has-background',
		'addon' => 'px',
	],

	'hide_after' => [
		'type'  => 'number',
		'name' => '[hideAfterPosition]',
		'title' => __( 'Hide after Position', 'floating-button' ),
		'default' => '0',
		'class' => 'has-addon-right has-background',
		'addon' => 'px',
	],

	'timer_show' => [
		'type'  => 'number',
		'name' => '[showAfterTimer]',
		'title' => __( 'Timer for display', 'floating-button' ),
		'default' => 0,
		'class' => 'has-addon-right has-background',
		'addon' => 'sec',

	],

	'timer_hide' => [
		'type'  => 'number',
		'name' => '[hideAfterTimer]',
		'title' => __( 'Timer for hide', 'floating-button' ),
		'default' => 0,
		'class' => 'has-addon-right has-background',
		'addon' => 'sec',
	],

	// Hide sub-buttons
	'click_page' => [
		'type'  => 'checkbox',
		'name' => '[uncheckedBtn]',
		'title' => __( 'By click on page', 'floating-button' ),
		'text' => __( 'Enable', 'floating-button' ),
	],

	'click_subBtn' => [
		'type'  => 'checkbox',
		'name' => '[uncheckedSubBtn]',
		'title' => __( 'By click on Sub button', 'floating-button' ),
		'text' => __( 'Enable', 'floating-button' ),
	],

	'hide_btn' => [
		'type'  => 'checkbox',
		'name' => '[hideBtns]',
		'title' => __( 'Hide Other Buttons', 'floating-button' ),
		'text' => __( 'Enable', 'floating-button' ),
		'info' => __('Hide other buttons if current button open.','floating-button'),
	],


];