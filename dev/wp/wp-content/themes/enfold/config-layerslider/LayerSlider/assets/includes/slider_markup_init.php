<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;

// Get init code
foreach($projectAttrs as $key => $val) {

	if(is_bool($val)) {
		$val = $val ? 'true' : 'false';
		$init[] = $key.': '.$val;
	} elseif(is_numeric($val)) { $init[] = $key.': '.$val;
	} else { $init[] = "$key: '$val'"; }
}

// Full-size sliders
if( $projectLayout === 'fullsize' && ( empty($projectAttrs['fullSizeMode']) || $projectAttrs['fullSizeMode'] !== 'fitheight' ) ) {
	$init[] = 'height: '.$projectProps['height'].'';
}

// Popup
if( $projectLayout === 'popup' ) {
	$lsPlugins[] = 'popup';
}


if( ! empty( $lsPlugins ) ) {
	$lsPlugins = array_unique( $lsPlugins );
	sort( $lsPlugins );

	// openPopup layer action, we need to pass plugin deps with URLs
	if( ! empty( $GLOBALS['lsAjaxOverridePopupSettings'] ) ) {
		$plugins = [];
		foreach( $lsPlugins as $item ) {

			if( $item === 'popup' ) {
				$plugins[] = 'popup';
				continue;
			}

			$handle = 'layerslider-' . $item;

			$js_src  = isset( wp_scripts()->registered[ $handle ] ) ? wp_scripts()->registered[ $handle ]->src : null;
			$css_src = isset( wp_styles()->registered[ $handle ] )  ? wp_styles()->registered[ $handle ]->src  : null;

			if( $js_src || $css_src ) {
				$entry = [ 'namespace' => $item ];
				if( $js_src )  $entry['js']  = $js_src;
				if( $css_src ) $entry['css'] = $css_src;
				$plugins[] = $entry;
			}
		}

		$lsPlugins = $plugins;
	}



	$init[] = 'plugins: ' . json_encode( $lsPlugins );
}

if( get_option('ls_suppress_debug_info', false ) ) {
	$init[] = 'hideWelcomeMessage: true';
}


if( ! empty( $GLOBALS['lsInitAjaxURL'] ) ) {
	$init[] = "ajaxURL: '".admin_url( 'admin-ajax.php' )."'";
}

$callbacks = [];

if( ! empty( $projectData['callbacks'] ) && is_array( $projectData['callbacks'] ) ) {
	foreach( $projectData['callbacks'] as $event => $function ) {
		$callbacks[] = $event.': '.stripslashes( $function );
	}
}

$separator = apply_filters( 'layerslider_init_props_separator', ', ');
$initObj = implode( $separator, $init );
$eventsObj = ! empty( $callbacks ) ? ', {'.implode( $separator, $callbacks ).'}' : '';

if( ! empty( $projectProps['loadOrder'] ) ) {
	$loadOrder = $projectProps['loadOrder'];

	$lsInit[] = 'window._layerSlidersOrder = window._layerSlidersOrder || [];';
	$lsInit[] = 'window._layerSlidersOrder['.$loadOrder.'] = window._layerSlidersOrder['.$loadOrder.'] || [];';
	$lsInit[] = 'window._layerSlidersOrder['.$loadOrder.'].push( \'#'.$projectInitId.'\' );';
}

$lsInit[] = 'jQuery(function() { _initLayerSlider( \'#'.$projectInitId.'\', {'.$initObj.'}'.$eventsObj.'); });';