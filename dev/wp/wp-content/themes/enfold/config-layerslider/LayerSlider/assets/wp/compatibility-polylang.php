<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;

// ---------------------------------------------------------------------------
//  DEDICATED POLYLANG INTEGRATION
//
//  Built on Polylang's native API instead of its WPML compatibility layer,
//  whose persistent string registry can leave stale source strings behind
//  when project content changes. The native API cannot go stale by design:
//
//  - pll_register_string() only collects strings in memory on admin
//    requests, so the Strings translations table is always rebuilt from
//    the current project data.
//
//  - pll__() resolves translations on the front-end by the string value
//    itself. A changed source is simply a new, untranslated string that
//    falls back to displaying the current original.
//
//  Draft content is registered as well — marked "(draft)" — so
//  translations can be prepared ahead of publishing.
//
//  Shared helpers live in wp/compatibility-multilingual.php, the WPML
//  integration in wp/compatibility-wpml.php.
// ---------------------------------------------------------------------------

add_action( 'admin_init', 'ls_polylang_register_strings' );

function ls_polylang_register_strings() {

	if( ! ls_should_use_polylang_string_translation() ) {
		return;
	}

	// Strings are only needed when Polylang's Strings translations table
	// is displayed or saved (both happen on the 'mlang_strings' page).
	$isPolylangStringsPage = ( ! empty( $_GET['page'] ) && $_GET['page'] === 'mlang_strings' );

	if( ! apply_filters( 'ls_polylang_should_register_strings', $isPolylangStringsPage ) ) {
		return;
	}

	ls_polylang_cleanup_legacy_strings();

	// Load projects in batches to keep peak memory usage bounded
	$batchSize 	= 50;
	$page 		= 1;

	do {

		// Hidden projects are included deliberately
		$sliders = LS_Sliders::find([
			'exclude' 	=> [],
			'limit' 	=> $batchSize,
			'page' 		=> $page,
			'data' 		=> true,
			'drafts' 	=> true
		]);

		if( empty( $sliders ) || ! is_array( $sliders ) ) {
			break;
		}

		foreach( $sliders as $slider ) {

			// Draft first: Polylang keys strings by value, so re-registering
			// the published content afterwards keeps the canonical names on
			// unchanged strings, and only draft-only strings stay marked.
			if( ! empty( $slider['draft']['data']['layers'] ) && is_array( $slider['draft']['data']['layers'] ) ) {
				ls_polylang_register_project_strings( $slider['id'], $slider['draft']['data'], true );
			}

			if( ! empty( $slider['data']['layers'] ) && is_array( $slider['data']['layers'] ) ) {
				ls_polylang_register_project_strings( $slider['id'], $slider['data'] );
			}
		}

		$page++;

	} while( count( $sliders ) === $batchSize );
}

// Removes leftover registry entries created by older LayerSlider versions
// through Polylang's WPML compatibility layer. Lossless: translations are
// stored separately, keyed by the string value, and remain untouched.
function ls_polylang_cleanup_legacy_strings() {

	if( ! function_exists( 'icl_unregister_string' ) ) {
		return;
	}

	$strings = get_option( 'polylang_wpml_strings', [] );

	if( empty( $strings ) || ! is_array( $strings ) ) {
		return;
	}

	foreach( $strings as $string ) {

		if( ! empty( $string['context'] ) && in_array( $string['context'], [ 'LayerSlider Sliders', 'LayerSlider WP' ], true ) ) {
			icl_unregister_string( $string['context'], $string['name'] );
		}
	}
}

function ls_polylang_register_project_strings( $sliderID, $data, $isDraft = false ) {

	$group 		 = 'LayerSlider';
	$namePrefix  = "Project #{$sliderID} / ";
	$nameSuffix  = $isDraft ? ' (draft)' : '';

	foreach( $data['layers'] as $slideIndex => $slide ) {

		// Slide-level link
		$slideLink 	 = ! empty( $slide['properties']['layer_link'] ) ? $slide['properties']['layer_link'] : '';
		$slideLinkId = ! empty( $slide['properties']['linkId'] ) ? $slide['properties']['linkId'] : '';

		if( ls_is_translatable_link( $slideLink, $slideLinkId ) ) {
			$stringName = $namePrefix.ls_get_translation_string_title( $slideIndex, $slide, false, false, 'link' ).$nameSuffix;
			pll_register_string( $stringName, stripslashes( $slideLink ), $group );
		}

		if( empty( $slide['sublayers'] ) || ! is_array( $slide['sublayers'] ) ) {
			continue;
		}

		foreach( $slide['sublayers'] as $layerIndex => $layer ) {

			$layerLink 	 = ! empty( $layer['url'] ) ? $layer['url'] : '';
			$layerLinkId = ! empty( $layer['linkId'] ) ? $layer['linkId'] : '';

			if( ls_is_translatable_link( $layerLink, $layerLinkId ) ) {
				$stringName = $namePrefix.ls_get_translation_string_title( $slideIndex, $slide, $layerIndex, $layer, 'link' ).$nameSuffix;
				pll_register_string( $stringName, stripslashes( $layerLink ), $group );
			}

			if( ! ls_layer_has_translatable_text( $layer ) ) {
				continue;
			}

			// Registered values must exactly match the front-end pll__()
			// calls, as Polylang resolves translations by the string value.
			// The markup trims the layer html before translating (see
			// slider_markup_html.php), affixes and links are never trimmed.
			if( ! empty( $layer['html'] ) ) {
				$stringName = $namePrefix.ls_get_translation_string_title( $slideIndex, $slide, $layerIndex, $layer, 'html' ).$nameSuffix;
				pll_register_string( $stringName, trim( stripslashes( $layer['html'] ) ), $group, true );
			}

			if( ! empty( $layer['affixBefore'] ) ) {
				$stringName = $namePrefix.ls_get_translation_string_title( $slideIndex, $slide, $layerIndex, $layer, 'affix-before' ).$nameSuffix;
				pll_register_string( $stringName, stripslashes( $layer['affixBefore'] ), $group );
			}

			if( ! empty( $layer['affixAfter'] ) ) {
				$stringName = $namePrefix.ls_get_translation_string_title( $slideIndex, $slide, $layerIndex, $layer, 'affix-after' ).$nameSuffix;
				pll_register_string( $stringName, stripslashes( $layer['affixAfter'] ), $group );
			}
		}
	}
}
