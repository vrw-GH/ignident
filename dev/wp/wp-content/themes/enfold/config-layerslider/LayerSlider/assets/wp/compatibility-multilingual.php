<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;

// ---------------------------------------------------------------------------
//  MULTILINGUAL SUPPORT — SHARED, ENGINE-AGNOSTIC HELPERS
//
//  The two supported translation engines have strictly separated,
//  dedicated integrations: see wp/compatibility-wpml.php and
//  wp/compatibility-polylang.php. The helpers below decide which engine
//  is active and apply the user-facing options; they must remain free of
//  engine-specific behavior.
//
//  Note: the option keys are prefixed with "ls_wpml_" for historical
//  reasons, but they apply to both engines.
// ---------------------------------------------------------------------------

function ls_get_string_translation_engine() {

	// Polylang's WPML compatibility layer also hooks WPML actions, but it
	// never defines ICL_SITEPRESS_VERSION, so the two cannot be confused.
	if( defined( 'ICL_SITEPRESS_VERSION' ) && has_action( 'wpml_register_single_string' ) ) {
		return 'wpml';
	}

	if( function_exists( 'pll__' ) && function_exists( 'pll_register_string' ) ) {
		return 'polylang';
	}

	return false;
}

function ls_should_use_string_translation() {
	return ( ls_get_string_translation_engine() !== false && get_option('ls_wpml_string_translation', true ) );
}

function ls_should_use_wpml_string_translation() {
	return ( ls_get_string_translation_engine() === 'wpml' && get_option('ls_wpml_string_translation', true ) );
}

function ls_should_use_polylang_string_translation() {
	return ( ls_get_string_translation_engine() === 'polylang' && get_option('ls_wpml_string_translation', true ) );
}

function ls_should_use_link_translation() {
	return ( ls_should_use_string_translation() && get_option('ls_wpml_link_translation', true ) );
}

function ls_should_auto_cleanup_translation_strings() {
	return ( get_option('ls_wpml_auto_cleanup', true ) );
}

function ls_should_use_media_translation() {

	if( ! get_option('ls_wpml_media_translation', true ) ) {
		return false;
	}

	// Media translation is a simple attachment ID mapping, thus it's
	// independent from string translation and its engine selection.
	return ( function_exists( 'pll_get_post' ) || has_filter( 'wpml_object_id' ) );
}

function ls_translate_media_id( $mediaID, $type = 'attachment' ) {

	if( ! ls_should_use_media_translation() ) {
		return $mediaID;
	}

	// Polylang's native API takes precedence over its WPML compat layer
	if( defined( 'POLYLANG_VERSION' ) && function_exists( 'pll_get_post' ) ) {
		$translatedID = pll_get_post( $mediaID );
		return ! empty( $translatedID ) ? $translatedID : $mediaID;
	}

	if( has_filter( 'wpml_object_id' ) ) {
		return apply_filters( 'wpml_object_id', $mediaID, $type, true );
	}

	return $mediaID;
}

function ls_layer_has_translatable_text( $layer ) {

	$media = ! empty( $layer['media'] ) ? $layer['media'] : '';

	// Image layers have no text content; shape, icon and SVG layers hold
	// lengthy markup that has no practical use for translators and would
	// clutter the translation UI. All of them can still have translatable
	// link URLs.
	return ! in_array( $media, [ 'img', 'shape', 'icon', 'svg' ], true );
}

function ls_is_translatable_link( $url = '', $linkId = '' ) {

	if( ! ls_should_use_link_translation() ) {
		return false;
	}

	// Only manually entered static URLs. A non-empty $linkId means the URL
	// is auto-generated, which the translation engines resolve on their own.
	if( empty( $url ) || ! empty( $linkId ) ) {
		return false;
	}

	// Skip dynamic and same-page hash URLs
	if( $url === '[post-url]' || strpos( $url, '#' ) === 0 ) {
		return false;
	}

	return true;
}

function ls_append_lang_uri_param( $url = '' ) {

	// The 'lang' URI param is only present when the translation plugin
	// uses the "language as parameter" URL format.
	if( empty( $url ) || empty( $_GET['lang'] ) ) {
		return $url;
	}

	// Polylang only defines the constant when its WPML compat is enabled
	if( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$langCode = ICL_LANGUAGE_CODE;
	} elseif( function_exists( 'pll_current_language' ) ) {
		$langCode = pll_current_language();
	} else {
		$langCode = '';
	}

	if( empty( $langCode ) ) {
		return $url;
	}

	// Skip dynamic and same-page hash URLs
	if( $url === '[post-url]' || strpos( $url, '#' ) === 0 ) {
		return $url;
	}

	// Skip external URLs
	if( strpos( $url, 'http' ) === 0 ) {
		$serverName = ! empty( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : '';
		if( empty( $serverName ) || strpos( $url, $serverName ) === false ) {
			return $url;
		}
	}

	// Insert the lang param before the #fragment (if any), otherwise it
	// would become an unreachable part of the fragment.
	$fragment = '';
	$hashPos  = strpos( $url, '#' );
	if( $hashPos !== false ) {
		$fragment = substr( $url, $hashPos );
		$url 	  = substr( $url, 0, $hashPos );
	}

	$url .= ( strpos( $url, '?' ) !== false ) ? '&amp;lang=' : '?lang=';
	$url .= $langCode;

	return $url.$fragment;
}

function ls_get_translation_text_label( $layerMedia, $textType ) {

	if( $textType === 'link' ) {
		return 'Link URL';
	}

	// Layers with affixes (text before, text after)
	$affixTypes = ['countdown', 'counter'];

	if( in_array( $layerMedia, $affixTypes, true ) ) {
		switch( $textType ) {
			case 'affix-before':
				return ucfirst( $layerMedia ) . ' Text Before';

			case 'affix-after':
				return ucfirst( $layerMedia ) . ' Text After';
		}
	}

	// Layer content
	switch( $layerMedia ) {
		case 'button':
			return 'Button Label';

		case 'text':
			return 'Text Content';

		case 'html':
			return 'HTML Content';

		case 'post':
			return 'Post Content';

		case 'media':
			return 'Media Content';

		case 'shape':
			return 'Shape Content';

		case 'svg':
			return 'SVG Content';

		case 'icon':
			return 'Icon Content';

		default:
			return 'Generic Content';
	}
}

function ls_get_translation_string_title( $slideIndex, $slide, $layerIndex = null, $layer = null, $textType = '' ) {

	$slideNumber = $slideIndex + 1;
	$slideName   = ! empty( $slide['properties']['title'] ) ? " ({$slide['properties']['title']})" : '';

	// Layer-level string
	if( ! empty( $layer ) ) {
		$layerNumber = $layerIndex + 1;
		$layerMedia  = ! empty( $layer['media'] ) ? $layer['media'] : '';
		$typeLabel   = ls_get_translation_text_label( $layerMedia, $textType );

		return sprintf(
			'Slide %d%s / Layer %d / %s',
			$slideNumber,
			$slideName,
			$layerNumber,
			$typeLabel
		);
	}

	// Slide-level string
	$typeLabel = ls_get_translation_text_label( '', $textType );

	return sprintf(
		'Slide %d%s / %s',
		$slideNumber,
		$slideName,
		$typeLabel
	);
}
