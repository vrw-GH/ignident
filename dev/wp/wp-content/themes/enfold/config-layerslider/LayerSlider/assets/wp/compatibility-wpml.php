<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;

// ---------------------------------------------------------------------------
//  DEDICATED WPML INTEGRATION
//
//  Strings are registered at save time under persistent string names and
//  resolved by those names on the front-end. Depending on the project's
//  version, one of three implementations is used:
//
//  - String Packages (Translation Management) for v7.14.2+ projects
//  - Single strings (String Translation) for v6.5.5+ projects
//  - A legacy single string implementation, kept unchanged for
//    backward compatibility with existing translations
//
//  Shared helpers live in wp/compatibility-multilingual.php, the Polylang
//  integration in wp/compatibility-polylang.php.
// ---------------------------------------------------------------------------

add_filter( 'wpml_active_string_package_kinds', function( $kinds ) {
	$kinds[ LS_WPML_SP_SLUG ] = [
		'title'  => LS_WPML_SP_TITLE,
		'plural' => LS_WPML_SP_TITLE,
		'slug'   => LS_WPML_SP_SLUG
	];

	return $kinds;
});

function ls_should_use_wpml_string_packages( $createdWith = null, $importVersion = null ) {

	$compareVersion = ! empty( $importVersion ) ? $importVersion : $createdWith;

	return (
		ls_should_use_wpml_string_translation() &&
		has_action( 'wpml_register_string' ) &&
		! empty( $compareVersion ) &&
		version_compare( $compareVersion, '7.14.2', '>=' )
	);
}

function layerslider_register_wpml_strings( $sliderID, $data, $publishedData = null ) {

	$currentLang = apply_filters( 'wpml_current_language', NULL );
	$createdWith = ! empty( $data['properties']['createdWith'] ) ? $data['properties']['createdWith'] : null;
	$importVersion = ! empty( $data['properties']['importVersion'] ) ? $data['properties']['importVersion'] : null;
	$shouldUseStringPackages = ls_should_use_wpml_string_packages( $createdWith, $importVersion );
	$shouldCleanup = ls_should_auto_cleanup_translation_strings();
	$package = [
		'kind'  	=> LS_WPML_SP_TITLE,
		'kind_slug' => LS_WPML_SP_SLUG,
		'name'  	=> "project-{$sliderID}",
		'title' 	=> apply_filters('ls_slider_title', stripslashes( $data['properties']['title'] ), 40 ) . ' (#'.$sliderID.')',
		'view_link' => admin_url('admin.php?page=layerslider&action=edit&id='.$sliderID),
		'edit_link' => admin_url('admin.php?page=layerslider&action=edit&id='.$sliderID)
	];


	if( $shouldUseStringPackages && $shouldCleanup ) {
		do_action( 'wpml_start_string_package_registration', $package );
	}

	// First register strings from the published version in case of a draft
	if( ! empty( $publishedData['layers'] ) && is_array( $publishedData['layers'] ) ) {
		layerslider_do_register_wpml_strings( $publishedData, $sliderID, $currentLang, $createdWith, $shouldUseStringPackages, $package );
	}

	// Normal registration routine
	if( ! empty( $data['layers'] ) && is_array( $data['layers'] ) ) {
		layerslider_do_register_wpml_strings( $data, $sliderID, $currentLang, $createdWith, $shouldUseStringPackages, $package );
	}

	if( $shouldUseStringPackages && $shouldCleanup ) {
		do_action( 'wpml_delete_unused_package_strings', $package );
	}
}

function layerslider_do_register_wpml_strings( $data, $sliderID, $currentLang, $createdWith, $shouldUseStringPackages, $package ) {


	foreach( $data['layers'] as $slideIndex => $slide ) {


		$slideLink 	 = ! empty( $slide['properties']['layer_link'] ) ? $slide['properties']['layer_link'] : '';
		$slideLinkId = ! empty( $slide['properties']['linkId'] ) ? $slide['properties']['linkId'] : '';

		// v7.14.2: WPML String Packages support for new projects
		if( ! empty( $slide['properties']['uuid'] ) && $shouldUseStringPackages ) {

			if( ls_is_translatable_link( $slideLink, $slideLinkId ) ) {
				$stringName = ls_get_translation_string_title( $slideIndex, $slide, false, false, 'link' );
				do_action( 'wpml_register_string', $slideLink, $slide['properties']['uuid'].'-link', $package, $stringName, 'LINE' );
			}

		// Check 'createdWith' property to decide which WPML implementation
		// should we use. This property was added in v6.5.5 along with the
		// new WPML implementation, so no version comparison required.
		} elseif( ! empty( $slide['properties']['uuid'] ) && ! empty( $data['properties']['createdWith'] ) ) {

			$string_name = "slider-{$sliderID}-slide-{$slide['properties']['uuid']}";

			if( ls_is_translatable_link( $slideLink, $slideLinkId ) ) {
				do_action( 'wpml_register_single_string', 'LayerSlider Sliders', $string_name.'-link', $slideLink, false, $currentLang );
			}
		}



		if( ! empty( $slide['sublayers'] ) && is_array( $slide['sublayers'] ) ) {
			foreach( $slide['sublayers'] as $layerIndex => $layer ) {

				$layerLink 	 = ! empty( $layer['url'] ) ? $layer['url'] : '';
				$layerLinkId = ! empty( $layer['linkId'] ) ? $layer['linkId'] : '';

				// Layer link URL
				if( ls_is_translatable_link( $layerLink, $layerLinkId ) ) {

					// v7.14.2: WPML String Packages support for new projects
					if( ! empty( $layer['uuid'] ) && $shouldUseStringPackages ) {
						$stringName = ls_get_translation_string_title( $slideIndex, $slide, $layerIndex, $layer, 'link' );
						do_action( 'wpml_register_string', $layerLink, $layer['uuid'].'-link', $package, $stringName, 'LINE' );

					} elseif( ! empty( $layer['uuid'] ) && ! empty( $data['properties']['createdWith'] ) ) {
						do_action( 'wpml_register_single_string', 'LayerSlider Sliders', "slider-{$sliderID}-layer-{$layer['uuid']}-link", $layerLink, false, $currentLang );
					}
				}

				if( ! ls_layer_has_translatable_text( $layer ) ) {
					continue;
				}

				// v7.14.2: WPML String Packages support for new projects
				if( ! empty( $layer['uuid'] ) && $shouldUseStringPackages ) {

					if( ! empty( $layer['html'] ) ) {
						$stringName = ls_get_translation_string_title( $slideIndex, $slide, $layerIndex, $layer, 'html' );
						do_action( 'wpml_register_string', $layer['html'], $layer['uuid'].'-html', $package, $stringName, 'LINE' );
					}

					if( ! empty( $layer['affixBefore'] ) ) {
						$stringName = ls_get_translation_string_title( $slideIndex, $slide, $layerIndex, $layer, 'affix-before' );
						do_action( 'wpml_register_string', $layer['affixBefore'], $layer['uuid'].'-affix-before', $package, $stringName, 'LINE' );
					}

					if( ! empty( $layer['affixAfter'] ) ) {
						$stringName = ls_get_translation_string_title( $slideIndex, $slide, $layerIndex, $layer, 'affix-after' );
						do_action( 'wpml_register_string', $layer['affixAfter'], $layer['uuid'].'-affix-after', $package, $stringName, 'LINE' );
					}


				// Check 'createdWith' property to decide which WPML implementation
				// should we use. This property was added in v6.5.5 along with the
				// new WPML implementation, so no version comparison required.
				} elseif( ! empty( $layer['uuid'] ) && ! empty( $data['properties']['createdWith'] ) ) {

					$string_name = "slider-{$sliderID}-layer-{$layer['uuid']}";

					if( ! empty( $layer['html'] ) ) {
						do_action( 'wpml_register_single_string', 'LayerSlider Sliders', $string_name.'-html', $layer['html'], false, $currentLang );
					}

					if( ! empty( $layer['affixBefore'] ) ) {
						do_action( 'wpml_register_single_string', 'LayerSlider Sliders', $string_name.'-affix-before', $layer['affixBefore'], false, $currentLang );
					}

					if( ! empty( $layer['affixAfter'] ) ) {
						do_action( 'wpml_register_single_string', 'LayerSlider Sliders', $string_name.'-affix-after', $layer['affixAfter'], false, $currentLang );
					}

				// Old implementation
				} elseif( ! empty( $layer['html'] ) ) {

					$string_name = '<'.$layer['type'].':'.substr(sha1($layer['html']), 0, 10).'> layer on slide #'.($slideIndex+1).' in slider #'.$sliderID.'';
					do_action( 'wpml_register_single_string', 'LayerSlider WP', $string_name, $layer['html'], false, $currentLang );
				}
			}
		}
	}
}
