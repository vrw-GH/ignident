<?php

use FloatingButton\Dashboard\Field;
use FloatingButton\Dashboard\FieldHelper;

defined( 'ABSPATH' ) || exit;

$default = Field::getDefault();

$show = [
	'general_start' => __( 'General', 'floating-button' ),
	'everywhere'    => __( 'Everywhere', 'floating-button' ),
	'shortcode'     => __( 'Shortcode', 'floating-button' ),
	'general_end'   => __( 'General', 'floating-button' ),
];



$pages_type = [
	'is_front_page' => __( 'Home Page', 'floating-button' ),
	'is_home'       => __( 'Posts Page', 'floating-button' ),
	'is_search'     => __( 'Search Pages', 'floating-button' ),
	'is_404'        => __( '404 Pages', 'floating-button' ),
	'is_archive'    => __( 'Archive Page', 'floating-button' ),
];

$operator = [
	'1' => 'is',
	'0' => 'is not',
];

$weekdays = [
	'none' => __( 'Everyday', 'floating-button' ),
	'1'    => __( 'Monday', 'floating-button' ),
	'2'    => __( 'Tuesday', 'floating-button' ),
	'3'    => __( 'Wednesday', 'floating-button' ),
	'4'    => __( 'Thursday', 'floating-button' ),
	'5'    => __( 'Friday', 'floating-button' ),
	'6'    => __( 'Saturday', 'floating-button' ),
	'7'    => __( 'Sunday', 'floating-button' ),
];

return [
	'show' => [
		'type'    => 'select',
		'name'    => '[show]',
		'title'   => __( 'Display', 'floating-button' ),
		'options' => $show,
		'class'   => 'display-option',
		'default' => '',
	],

	'operator' => [
		'type'    => 'select',
		'name'    => '[operator]',
		'title'   => __( 'Is or is not', 'floating-button' ),
		'options' => $operator,
		'class'   => 'display-operator',
		'default' => '1',
	],

	'ids' => [
		'type'  => 'text',
		'name'  => '[ids]',
		'title' => __( 'Enter ID\'s', 'floating-button' ),
		'class' => 'display-ids',
		'info'  => __( 'Enter IDs, separated by comma.', 'floating-button' ),
	],

	'page_type'  => [
		'type'    => 'select',
		'name'    => '[page_type]',
		'title'   => __( 'Specific page types', 'floating-button' ),
		'options' => $pages_type,
		'class'   => 'display-pages',
	],

	// Devices Rules
	'is_desktop' => [
		'type'  => 'checkbox',
		'name'  => '[include_more_screen]',
		'title' => __( 'Don\'t show on screens more', 'floating-button' ),
		'text'  => __( 'Enable', 'floating-button' ),
	],

	'desktop_screen' => [
		'type'    => 'number',
		'name'    => '[screen_more]',
		'title'   => __( 'Screen width', 'floating-button' ),
		'class'   => 'has-addon-right has-background',
		'addon'   => 'px',
		'default' => 1024
	],

	'is_mobile' => [
		'type'  => 'checkbox',
		'name'  => '[include_mobile]',
		'title' => __( 'Don\'t show on screens more', 'floating-button' ),
		'text'  => __( 'Enable', 'floating-button' ),
	],

	'mobile_screen' => [
		'type'    => 'number',
		'name'    => '[screen]',
		'title'   => __( 'Screen width', 'floating-button' ),
		'class'   => 'has-addon-right has-background',
		'addon'   => 'px',
		'default' => 480
	],

	// Users

	'users' => [
		'type'    => 'select',
		'name'    => '[item_user]',
		'title'   => __( 'Users', 'floating-button' ),
		'options' => [
			1 => __( 'All users', 'floating-button' ),
			2 => __( 'Authorized Users', 'floating-button' ),
			3 => __( 'Unauthorized Users', 'floating-button' ),
		],
	],

	// Browsers
	'opera' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][opera]',
		'title' => __( 'Opera', 'floating-button' ),
		'text'  => __( 'Disable', 'floating-button' ),
	],

	'edge' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][edge]',
		'title' => __( 'Microsoft Edge', 'floating-button' ),
		'text'  => __( 'Disable', 'floating-button' ),
	],

	'chrome' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][chrome]',
		'title' => __( 'Chrome', 'floating-button' ),
		'text'  => __( 'Disable', 'floating-button' ),
	],

	'safari' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][safari]',
		'title' => __( 'Safari', 'floating-button' ),
		'text'  => __( 'Disable', 'floating-button' ),
	],

	'firefox' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][firefox]',
		'title' => __( 'Firefox', 'floating-button' ),
		'text'  => __( 'Disable', 'floating-button' ),
	],

	'ie' => [
		'type'  => 'checkbox',
		'name'  => '[browsers][ie]',
		'title' => __( 'Internet Explorer', 'floating-button' ),
		'text'  => __( 'Disable', 'floating-button' ),
	],

	'other'   => [
		'type'  => 'checkbox',
		'name'  => '[browsers][other]',
		'title' => __( 'Other', 'floating-button' ),
		'text'  => __( 'Disable', 'floating-button' ),
	],

	// Schedule
	'weekday' => [
		'type'    => 'select',
		'name'    => '[weekday]',
		'title'   => __( 'Weekday', 'floating-button' ),
		'options' => $weekdays,
	],

	'time_start' => [
		'type'  => 'time',
		'name'  => '[time_start]',
		'title' => __( 'Start time', 'floating-button' ),
	],

	'time_end' => [
		'type'  => 'time',
		'name'  => '[time_end]',
		'title' => __( 'End time', 'floating-button' ),
	],

	'dates' => [
		'type'  => 'checkbox',
		'name'  => '[dates]',
		'title' => __( 'Define Dates', 'floating-button' ),
		'text'  => __( 'Enable', 'floating-button' ),
		'class' => 'wowp-dates',
	],

	'date_start' => [
		'type'  => 'date',
		'name'  => '[date_start]',
		'title' => __( 'Date From', 'floating-button' ),
		'class' => 'wowp-date-input',
	],

	'date_end'    => [
		'type'  => 'date',
		'name'  => '[date_end]',
		'title' => __( 'Date To', 'floating-button' ),
		'class' => 'wowp-date-input',
	],

	// Other
	'language_on' => [
		'type'  => 'checkbox',
		'name'  => '[depending_language]',
		'title' => __( 'Depending on the language', 'floating-button' ),
		'text'  => __( 'Enable', 'floating-button' ),
	],

	'language' => [
		'type'    => 'select',
		'name'    => '[lang]',
		'title'   => __( 'Language', 'floating-button' ),
		'options' => FieldHelper::languages(),
	],

	'fontawesome' => [
		'type'  => 'checkbox',
		'name'  => '[disable_fontawesome]',
		'title' => __( 'Font Awesome', 'floating-button' ),
		'text'  => __( 'Disable', 'floating-button' ),
	],

];