<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;

$slider = [];

$sliderVersion 	= ! empty( $projectData['properties']['sliderVersion'] ) ? $projectData['properties']['sliderVersion'] : '1.0.0';
$preVersion725 	= version_compare( $sliderVersion, '7.2.5', '<' );


// Filter to override the defaults
if(has_filter('layerslider_override_defaults')) {
	$newDefaults = apply_filters('layerslider_override_defaults', $lsDefaults);
	if(!empty($newDefaults) && is_array($newDefaults)) {
		$lsDefaults = $newDefaults;
		unset($newDefaults);
	}
}

// Allow overriding slider settings from the embed code like skins.
//
// This is a generic solution. To keep things simple and flexible,
// this takes place before filtering with defaults.
//
// As such, some keys might still use their legacy form.
foreach( $embed as $key => $val ) {

	if( $key !== 'id' ) {
		$projectData['properties'][ $key ] = esc_js( $val );
	}
}


// Allow accepting a "hero" type slider
if( ! empty( $projectData['properties']['type'] ) ) {
	if( $projectData['properties']['type'] === 'hero' ) {
		$projectData['properties']['type'] = 'fullsize';
		$projectData['properties']['fullSizeMode'] = 'hero';
	}
}

// Hook to alter slider data *before* filtering with defaults
if(has_filter('layerslider_pre_parse_defaults')) {
	$result = apply_filters('layerslider_pre_parse_defaults', $projectData);
	if(!empty($result) && is_array($result)) {
		$projectData = $result;
	}
}

// Filter slider data with defaults
$filteredProperties = apply_filters('ls_parse_defaults', $lsDefaults['slider'], $projectData['properties'], [ 'esc_js' => true ] );
$projectProps = &$filteredProperties['props'];
$projectAttrs = &$filteredProperties['attrs'];

$skin = !empty($projectAttrs['skin']) ? $projectAttrs['skin'] : $lsDefaults['slider']['skin']['value'];
$projectAttrs['skinsPath'] = dirname(LS_Sources::urlForSkin($skin)) . '/';
if(isset($projectAttrs['autoPauseSlideshow'])) {
	switch($projectAttrs['autoPauseSlideshow']) {
		case 'auto': $projectAttrs['autoPauseSlideshow'] = 'auto'; break;
		case 'enabled': $projectAttrs['autoPauseSlideshow'] = true; break;
		case 'disabled': $projectAttrs['autoPauseSlideshow'] = false; break;
	}
}


// Get global background image by attachment ID (if any)
if( ! empty( $projectProps['globalBGImageId'] ) ) {

	$projectProps['globalBGImageId'] = ls_translate_media_id( $projectProps['globalBGImageId'] );

	$tempSrc = wp_get_attachment_image_src( $projectProps['globalBGImageId'], 'full' );
	$tempSrc = apply_filters('layerslider_init_props_image', $tempSrc[0]);

	$projectAttrs['globalBGImage'] = $tempSrc;
}

// GLobal background image asset
if( ! empty( $projectAttrs['globalBGImage'] ) && ! ls_assets_cond( $projectAttrs, 'globalBGImage' ) ) {
	unset( $projectAttrs['globalBGImage'] );
}


// Old and without type
if( empty($projectAttrs['sliderVersion']) && empty($projectAttrs['type']) ) {

	if( !empty($projectProps['forceresponsive']) ) {
		$projectAttrs['type'] = 'fullwidth';
	} elseif( empty($projectProps['responsive']) ) {
		$projectAttrs['type'] = 'fixedsize';
	} else {
		$projectAttrs['type'] = 'responsive';
	}
}

$projectLayout = ! empty( $projectAttrs['type'] ) ? $projectAttrs['type'] : 'responsive';
$projectScene = ! empty( $projectAttrs['scene'] ) ? $projectAttrs['scene'] : '';

$firstSlide = 1;
if ( ! empty( $projectAttrs['firstSlide'] ) ) {
	$firstSlide = $projectAttrs['firstSlide'];
}

// Override firstSlide if it is specified in embed params
if( ! empty( $embed['firstslide'] ) ) {
	$projectAttrs['firstSlide'] = '[firstSlide]';
	$firstSlide = $embed['firstslide'];
}

// Override popup triggers for layer action
if( ! empty( $GLOBALS['lsAjaxOverridePopupSettings'] ) ) {
	$projectAttrs['popupShowOnTimeout'] = 0;
	$projectAttrs['popupShowOnce'] = false;

	if( ! empty( $_GET['slide'] ) ) {
		$projectAttrs['firstSlide'] = (int) $_GET['slide'];
		$firstSlide = (int) $_GET['slide'];
	}
}

$slides = [];
if(isset($projectData['layers']) && is_array($projectData['layers'])) {
	$slides = $projectData['layers'];
}


// Make sure that width & height are set correctly
if( empty( $projectProps['width'] ) ) { $projectProps['width'] = 1280; }
if( empty( $projectProps['height'] ) ) { $projectProps['height'] = 720; }


// Slides and layers
foreach( $slides as $slideKey => $slideData ) {

	// Skip this slide?
	if( ! empty( $slideData['properties']['skip'] ) ) {
		continue;
	}

	// Schedule start
	if( ! empty( $slideData['properties']['schedule_start'] ) && (int) $slideData['properties']['schedule_start'] > time() ) {
		continue;
	}

	// Schedule end
	if( ! empty( $slideData['properties']['schedule_end'] ) && (int) $slideData['properties']['schedule_end'] < time() ) {
		continue;
	}

	// v6.6.1: Fix PHP undef notice
	$slideData['properties'] = ! empty( $slideData['properties'] ) ? $slideData['properties'] : [];

	// v7.2.5: Backward compatibility for parallax transformOrigin changes
	if( $preVersion725 && ! empty( $slideData['properties']['parallaxtransformorigin'] ) ) {

		$toParams = explode(' ', trim( $slideData['properties']['parallaxtransformorigin'] ) );
		if( $toParams[0] === '50%' ) { $toParams[0] = 'slidercenter'; }
		if( isset( $toParams[1] ) && $toParams[1] === '50%' ) { $toParams[1] = 'slidermiddle'; }

		$slideData['properties']['parallaxtransformorigin'] = implode(' ', $toParams);
	}

	$slider['slides'][$slideKey] = apply_filters('ls_parse_defaults', $lsDefaults['slides'], $slideData['properties']);
	$slider['slides'][$slideKey]['countdowns'] = ! empty( $slideData['countdowns'] ) ? $slideData['countdowns'] : [];

	if(isset($slideData['sublayers']) && is_array($slideData['sublayers'])) {

		foreach($slideData['sublayers'] as $layerkey => $layer) {

			if( ! empty( $layer['transition'] ) ) {
				$layer = array_merge($layer, json_decode(stripslashes($layer['transition']), true));
			}

			if( ! empty( $layer['styles'] ) ) {
				$layerStyles = json_decode($layer['styles'], true);

				if( empty( $layerStyles ) ) {
					$layerStyles = json_decode(stripslashes($layer['styles']), true);
				}

				$layer['styles'] = ! empty( $layerStyles ) ? $layerStyles : [];
			}

			$layer['transition'] = ! empty( $layer['transition'] ) ? $layer['transition'] : [];
			$layer['styles'] = ! empty( $layer['styles'] ) ? $layer['styles'] : [];

			if( ! empty( $layer['top'] ) ) {
				$layer['styles']['top']  = $layer['top'];
			}

			if( ! empty( $layer['left'] ) ) {
				$layer['styles']['left']  = $layer['left'];
			}

			if( ! empty($layer['wordwrap']) || ! empty($layer['styles']['wordwrap']) ) {
				$layer['styles']['white-space'] = 'normal';
			}

			if( ! empty( $layer['layerBackground'] ) && empty( $layer['styles']['background-repeat'] ) ) {
				if(
					empty( $projectAttrs['sliderVersion'] ) ||
					version_compare( $projectAttrs['sliderVersion'], '7.0.0', '<' )
				) {
					$layer['styles']['background-repeat'] = 'repeat';
				}
			}

			// v7.2.5: Backward compatibility for parallax transformOrigin changes
			if( $preVersion725 && ! empty( $layer['parallaxtransformorigin'] ) ) {

				$toParams = explode(' ', trim( $layer['parallaxtransformorigin'] ) );
				if( $toParams[0] === '50%' ) { $toParams[0] = 'slidercenter'; }
				if( isset( $toParams[1] ) && $toParams[1] === '50%' ) { $toParams[1] = 'slidermiddle'; }

				$layer['parallaxtransformorigin'] = implode(' ', $toParams);
			}


			// Marker for Font Awesome 4
			if( empty( $lsFonts['font-awesome-4'] ) && ( ! empty( $layer['html'] ) || ! empty( $layer['icon'] ) ) ) {

				if( ! empty( $layer['html'] ) && strpos( $layer['html'], 'fa fa-') !== false ) {
					$lsFonts['font-awesome-4'] = 'font-awesome-4';
				}

				if( ! empty( $layer['icon'] ) && strpos( $layer['icon'], 'fa fa-') !== false ) {
					$lsFonts['font-awesome-4'] = 'font-awesome-4';
				}
			}

			// Marker for Font Awesome 5
			if( empty( $lsFonts['font-awesome-5'] ) && ! empty( $layer['html'] ) ) {
				if( strpos( $layer['html'], 'fas fa-') !== false ||
					strpos( $layer['html'], 'far fa-') !== false ||
					strpos( $layer['html'], 'fal fa-') !== false ||
					strpos( $layer['html'], 'fad fa-') !== false ||
					strpos( $layer['html'], 'fab fa-') !== false

					) {
					$lsFonts['font-awesome-5'] = 'font-awesome-5';
				}
			}

			// Marker for Lottie
			if( ! empty( $layer['media'] ) && $layer['media'] === 'lottie' ) {
				if( empty( $layer['skip'] ) ) {
					$GLOBALS['lsLoadLottie'] = true;
				}
			}

			// v6.5.6: Compatibility mode for media layers that used the
			// old checkbox based media settings.
			if( isset( $layer['controls'] ) ) {
				if( true === $layer['controls'] ) {
					$layer['controls'] = 'auto';
				} elseif( false === $layer['controls'] ) {
					$layer['controls'] = 'disabled';
				}
			}

			// Remove unwanted style options
			$keys = array_keys( $layer['styles'], 'unset', true );
			foreach( $keys as $key) {
				unset( $layer['styles'][$key] );
			}

			if( isset($layer['styles']['opacity']) && $layer['styles']['opacity'] == '1') {
				unset($layer['styles']['opacity']);
			}

			unset($layer['styles']['wordwrap']);

			if( ! empty( $layer['effects'] ) && is_array( $layer['effects'] ) ) {
				foreach( $layer['effects'] as $effectKey => $effect ) {
					$effectName = $effect['effect'];
					if( ! empty( $lsDefaults['layerEffects'][ $effectName ] ) ) {

						// Add an effect key at the beginning of every effect's defaults
						$lsDefaults['layerEffects'][ $effectName ] = [
							'effect' => [
								'value' => '',
								'keys'  => 'effect',
							]
						] + $lsDefaults['layerEffects'][ $effectName ];

						$parsed = apply_filters('ls_parse_defaults', $lsDefaults['layerEffects'][ $effectName ], $effect, [ 'esc_js' => true ] );
						$layer['effects'][ $effectKey ] = $parsed['attrs'];
					}
				}
			}

			$slider['slides'][$slideKey]['layers'][$layerkey] = apply_filters('ls_parse_defaults', $lsDefaults['layers'], $layer);
		}
	}
}

// ------------------------------------------------------------------------------------
//                     START OF FIRST SLIDE + SCROLL SCENE HANDLING
// ------------------------------------------------------------------------------------

// Group identity for per-slide embeds. Scene instances emitted from the same embed
// call share a group ID, so the scrollToSceneSlide layer action can tell which
// ls-scene-wrapper elements in the DOM belong together even when multiple
// multi-slide Scroll Scenes are present on the same page.
$sceneEmbedsGroup = '';
$sceneEmbedsSlide = 0;

if( $projectScene === 'scroll' && $projectLayout !== 'popup' && ! empty( $slider['slides'] ) ) {

	// First pass: No remaining per-slide embeds yet
	if( empty( $GLOBALS['lsRemainingPerSlideEmbeds'] ) ) {

		$allKeys = array_keys( $slider['slides'] );

		if( $firstSlide === 'random' ) {

			$selectedPos = array_rand( $allKeys );
			$selectedKey = $allKeys[ $selectedPos ];

		} else {

			$userKey = (int)$firstSlide - 1;

			if( isset( $slider['slides'][ $userKey ] ) ) {
				$selectedPos = array_search( $userKey, $allKeys, true );
			} else {
				$selectedPos = 0;
			}

			$selectedKey = $allKeys[ $selectedPos ];
		}

		$slider['slides'] = [ $slider['slides'][ $selectedKey ] ];

		if( ! empty( $projectProps['scrollPerSlideEmbeds'] ) ) {

			$GLOBALS['lsSceneEmbedsGroup'] = 'lsg_' . $projectId . '_' . self::getUniqueID();
			$sceneEmbedsGroup = $GLOBALS['lsSceneEmbedsGroup'];
			$sceneEmbedsSlide = $selectedKey + 1;

			// First scene Play From override. This is the first pass, i.e. the first
			// scene instance in the DOM; the subsequent per-slide embeds re-run this
			// setup and keep the project's own Play From setting. The default
			// 'inherit' (or any unknown value) leaves everything untouched.
			if(
				! empty( $projectProps['scrollFirstScenePlayFrom'] ) &&
				in_array( $projectProps['scrollFirstScenePlayFrom'], [ 'stick', 'top', 'center', 'bottom' ], true )
			) {
				$projectAttrs['playFrom'] = $projectProps['scrollFirstScenePlayFrom'];
			}

			$remainingIndexes = [];
			$count = count( $allKeys );

			if( $firstSlide === 'random' ) {

				for( $i = 0; $i < $count; $i++ ) {
					if( $i === $selectedPos ) {
						continue;
					}
					$remainingIndexes[] = $allKeys[ $i ] + 1;
				}

				shuffle( $remainingIndexes );

			} else {

				for( $i = $selectedPos + 1; $i < $count; $i++ ) {
					$remainingIndexes[] = $allKeys[ $i ] + 1;
				}
			}

			$GLOBALS['lsRemainingPerSlideEmbeds'] = $remainingIndexes;
		}

	// Second pass: There are remaining per-slide embeds where we already know the slide keys.
	//              We also know that firstSlide cannot be "random", but still check just in case.
	} else {

		if( $firstSlide !== 'random' ) {
			$slider['slides'] = [ $slider['slides'][ (int)$firstSlide - 1 ] ];

			if( ! empty( $GLOBALS['lsSceneEmbedsGroup'] ) ) {
				$sceneEmbedsGroup = $GLOBALS['lsSceneEmbedsGroup'];
				$sceneEmbedsSlide = (int)$firstSlide;
			}
		}
	}
}
// ------------------------------------------------------------------------------------
//                        END OF FIRST SLIDE + SCROLL SCENE HANDLING
// ------------------------------------------------------------------------------------

// Hook to alter slider data *after* filtering with defaults
if(has_filter('layerslider_post_parse_defaults')) {
	$result = apply_filters('layerslider_post_parse_defaults', $projectData);
	if(!empty($result) && is_array($result)) {
		$projectData = $result;
	}
}

// Fix circle timer
if( empty($projectAttrs['sliderVersion']) && empty($projectAttrs['showCircleTimer']) ) {
	$projectAttrs['showCircleTimer'] = false;
}

// Important: Reindex slides
if( ! empty( $slider['slides'] ) ) {
	$slider['slides'] = array_values( $slider['slides'] );
}