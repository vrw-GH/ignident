<?php

add_filter( 'wpml_active_string_package_kinds', function( $kinds ) {
	$kinds[ LS_WPML_SP_SLUG ] = [
		'title'  => LS_WPML_SP_TITLE,
		'plural' => LS_WPML_SP_TITLE,
		'slug'   => LS_WPML_SP_SLUG
	];

	return $kinds;
});

function ls_should_use_string_translation() {
	return ( has_action( 'wpml_register_single_string' ) && get_option('ls_wpml_string_translation', true ) );
}

function ls_should_use_media_translation() {
	return ( has_filter('wpml_object_id') && get_option('ls_wpml_media_translation', true ) );
}

function ls_should_auto_cleanup_translation_strings() {
	return ( get_option('ls_wpml_auto_cleanup', true ) );
}

function ls_should_use_wpml_string_packages( $createdWith = null, $importVersion = null ) {

	$compareVersion = ! empty( $importVersion ) ? $importVersion : $createdWith;

	return (
		has_action( 'wpml_register_string' ) &&
		get_option('ls_wpml_string_translation', true ) &&
		! empty( $compareVersion ) &&
		version_compare( $compareVersion, '7.14.2', '>=' )
	);
}

function ls_wpml_get_layer_text_label( $layerMedia, $textType ) {

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

function ls_wpml_get_string_title( $rawContent, $slideIndex, $slide, $layerIndex, $layer, $textType ) {

	// Settings
	$maxPreviewLength = 40;

	// Details
	$slideNumber = $slideIndex + 1;
	$layerNumber = $layerIndex + 1;

	$slideName = ! empty( $slide['properties']['title'] ) ? " ({$slide['properties']['title']})" : '';
	$layerName = ! empty( $layer['subtitle'] ) ? " ({$layer['subtitle']})" : '';

	$layerMedia = ! empty( $layer['media'] ) ? $layer['media'] : '';
	$typeLabel = ls_wpml_get_layer_text_label( $layerMedia, $textType );

	$rawContent = ! empty( $rawContent ) ? $rawContent : '';
	$contentPreview = wp_strip_all_tags( $rawContent );
	if( mb_strlen( $contentPreview ) > $maxPreviewLength ) {
		$contentPreview = mb_substr( $contentPreview, 0, $maxPreviewLength ) . '...';
	}

	return sprintf(
		'Slide %d%s / Layer %d / %s',
		$slideNumber,
		$slideName,
		$layerNumber,
		$typeLabel
	);
}