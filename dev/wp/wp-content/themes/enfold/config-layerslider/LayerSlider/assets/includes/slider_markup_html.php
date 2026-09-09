<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;

// Popup
if( $projectLayout === 'popup' ) {
	$projectProps['width']  = ! empty( $projectProps['popupWidth'] ) ? $projectProps['popupWidth'] : 640;
	$projectProps['height'] = ! empty( $projectProps['popupHeight']) ? $projectProps['popupHeight'] : 360;
}

// Get slider style
$sliderStyleAttr[] = 'width:'.layerslider_check_unit($projectProps['width']).';';

if( $projectLayout === 'fullsize' && ( empty($projectAttrs['fullSizeMode']) || $projectAttrs['fullSizeMode'] !== 'fitheight' ) ) {
	$sliderStyleAttr[] = 'height:100vh;';
} else {
	$sliderStyleAttr[] = 'height:'.layerslider_check_unit($projectProps['height']).';';
}

if(!empty($projectProps['maxwidth'])) {
	$sliderStyleAttr[] = 'max-width:'.layerslider_check_unit($projectProps['maxwidth']).';';
}

$sliderStyleAttr[] = 'margin:0 auto;';
if(isset($projectProps['sliderStyle'])) {
	$sliderStyleAttr[] = str_replace('\n', ' ', $projectProps['sliderStyle'] );
}

// Border radius
$borderRadius = ! empty( $projectProps['borderRadius'] ) ? $projectProps['borderRadius'] : '';
if( ! empty( $borderRadius ) && $borderRadius !== '0px' && $borderRadius !== '0%' && $borderRadius !== '0' ) {
	$sliderStyleAttr[] = 'border-radius:'.layerslider_check_unit($borderRadius).';overflow: hidden;';
}

// Gutenberg Margin Options
if( ! empty( $embed['marginTop'] ) ) { $sliderStyleAttr[] = 'margin-top: '.layerslider_check_unit( $embed['marginTop'] ).';'; }
if( ! empty( $embed['marginRight'] ) ) { $sliderStyleAttr[] = 'margin-right: '.layerslider_check_unit( $embed['marginRight'] ).';'; }
if( ! empty( $embed['marginBottom'] ) ) { $sliderStyleAttr[] = 'margin-bottom: '.layerslider_check_unit( $embed['marginBottom'] ).';'; }
if( ! empty( $embed['marginLeft'] ) ) { $sliderStyleAttr[] = 'margin-left: '.layerslider_check_unit( $embed['marginLeft'] ).';'; }

// Before slider content hook
if(has_action('layerslider_before_slider_content')) {
	do_action('layerslider_before_slider_content');
}

// Wrap Popups
if( $projectLayout === 'popup' ) {
	$popupClasses = ! empty( $projectProps['popupScrollable'] ) ? 'ls-popup-scrollable' : '';
	$lsContainer[] = '<div class="ls-popup '.$popupClasses.'">';
}

$customClasses = '';
if( ! empty( $projectProps['sliderclass'] ) ) {
	$customClasses = ' '.$projectProps['sliderclass'];
}

if( ! empty( $embed['className'] ) ) {
	$customClasses .= ' '.$embed['className'];
}

// v7.10.0: Class to control new user-select option
if( empty( $projectProps['noUserSelect']) ) {
	$customClasses .= ' ls-selectable';
}

// Use srcset
$useSrcset = (bool) get_option('ls_use_srcset', true );
if( isset( $projectAttrs['useSrcset'] ) ) {

	if( is_bool( $projectAttrs['useSrcset'] ) ) {
		$useSrcset = $projectAttrs['useSrcset'];

	} elseif( $projectAttrs['useSrcset'] === 'enabled' ||
			  $projectAttrs['useSrcset'] === '1' ) {
		$useSrcset = true;

	} elseif( $projectAttrs['useSrcset'] === 'disabled') {
		$useSrcset = false;
	}
}

$projectAttrs['useSrcset'] = $useSrcset;


// Enhanced lazy load
$enhancedLazyLoad = (bool) get_option('ls_enhanced_lazy_load', false );
if( isset( $projectProps['enhancedLazyLoad'] ) ) {

	if( is_bool( $projectProps['enhancedLazyLoad'] ) ) {
		$enhancedLazyLoad = $projectProps['enhancedLazyLoad'];

	} elseif( $projectProps['enhancedLazyLoad'] === 'enabled' ||
			  $projectProps['enhancedLazyLoad'] === '1') {
		$enhancedLazyLoad = true;

	} elseif( $projectProps['enhancedLazyLoad'] === 'disabled') {
		$enhancedLazyLoad = false;
	}
}

$projectProps['enhancedLazyLoad'] = $enhancedLazyLoad;


// Performance mode
$performanceMode = (bool) get_option('ls_performance_mode', true );
if( isset( $projectAttrs['performanceMode'] ) ) {

	if( is_bool( $projectAttrs['performanceMode'] ) ) {
		$performanceMode = $projectAttrs['performanceMode'];

	} elseif( $projectAttrs['performanceMode'] === 'enabled' ||
			  $projectAttrs['performanceMode'] === '1') {
		$performanceMode = true;

	} elseif( $projectAttrs['performanceMode'] === 'disabled' ||
			  empty( $projectAttrs['performanceMode'] ) ) {
		$performanceMode = false;
	}
}

$projectAttrs['performanceMode'] = $performanceMode;



// Project-level Google Fonts
if( get_option('layerslider-google-fonts-enabled', true ) ) {

	$projectData = ls_merge_google_fonts( $projectData );

	if( ! empty( $projectData['googlefonts'] ) ) {

		$fontManager = new LS_GoogleFontsManager();
		$lsContainer[] = $fontManager->getInlineStyle( $projectData['googlefonts'] );
	}
}

$sliderCreatedWith = ! empty( $projectAttrs['createdWith'] ) ? $projectAttrs['createdWith'] : null;
$sliderImportedWith = ! empty( $projectProps['importVersion'] ) ? $projectProps['importVersion'] : null;
$lsTranslation = [

	// The active string translation engine: 'wpml', 'polylang' or false
	'engine' => ls_should_use_string_translation() ? ls_get_string_translation_engine() : false,

	// WPML-specific: string packages tier & package descriptor
	'useStringPackages' => ls_should_use_wpml_string_packages( $sliderCreatedWith, $sliderImportedWith ),
	'package' => [
		'kind'  	=> LS_WPML_SP_TITLE,
		'kind_slug' => LS_WPML_SP_SLUG,
		'name'  	=> "project-{$projectId}"
	]
];


// STICKY + SCROLL SCENE
$needsSceneWrapper = ( $projectLayout !== 'popup' && ! empty( $projectScene ) );

if( $needsSceneWrapper ) {

	$slideDuration 	= ! empty( $slider['slides'][0]['props']['scrollSlideDuration'] ) ? (float) $slider['slides'][0]['props']['scrollSlideDuration'] : 0;
	$sceneDuration 	= ! empty( $projectAttrs['sceneDuration'] ) ? (float) $projectAttrs['sceneDuration'] : 2;
	$sceneDuration 	= ! empty( $slideDuration ) ? $slideDuration : $sceneDuration;

	$sceneSpeed 	= ! empty( $projectAttrs['sceneSpeed'] ) ? (float) $projectAttrs['sceneSpeed'] : 100;
	$sceneSpeed 	= max( 10, $sceneSpeed );
	$sceneSpeed 	= min( 999, $sceneSpeed );
	$playFrom 		= ! empty( $projectAttrs['playFrom'] ) ? $projectAttrs['playFrom'] : 'stick';
	$stickTo 		= ! empty( $projectAttrs['stickTo'] ) ? $projectAttrs['stickTo'] : 'center';
	$stickDuration 	= ( isset( $projectAttrs['stickDuration'] ) && $projectAttrs['stickDuration'] !== '' )
						? ((int) $projectAttrs['stickDuration']) / 1000
						: 'unset';
	$sceneHeight 	= ! empty( $projectAttrs['sceneHeight'] ) ? $projectAttrs['sceneHeight'] : '200%';
	$canvasHeight 	= (float) $projectProps['height'];

	$sceneWrapperHeight 	= '';
	$sceneSpacerHeight		= '';
	$sceneStyle 			= '';
	$sceneClassNames		= '';
	$sceneAttrs				= [];

	if( $projectScene === 'sticky' ) {

		if( in_array( $projectLayout, ['fixedsize', 'responsive', 'fullwidth'] ) ) {

			if( strpos( $sceneHeight, '%') !== false || strpos( $sceneHeight, 'sh') !== false  ) {
				$sceneWrapperHeight = round( $canvasHeight * ( (float) $sceneHeight / 100 ) ) . 'px';
			} elseif( strpos( $sceneHeight, 'px') !== false ) {
				$sceneWrapperHeight = round( max( (float) $sceneHeight, $canvasHeight ) ). 'px';
			} else {
				$sceneWrapperHeight = $sceneHeight;
			}

		} else {

			if( strpos( $sceneHeight, '%') !== false || strpos( $sceneHeight, 'sh') !== false  ) {
				$sceneWrapperHeight = max( 100, 100 * ( (float) $sceneHeight / 100 ) ) . 'vh';
			} elseif( strpos( $sceneHeight, 'px') !== false ) {
				$sceneWrapperHeight = round( max( (float) $sceneHeight, $canvasHeight ) ) . 'px';
			} else {
				$sceneWrapperHeight = max( 100, (float) $sceneHeight ) . 'vh';
			}
		}

	} elseif( $projectScene === 'scroll' ) {
		$sceneAttrs[] = 'data-scene-pre-duration="'.$sceneDuration.'"';
		$sceneSpacerHeight = 100 * $sceneDuration / ( $sceneSpeed / 100 ) . 'vh';

		// Per-slide embed group identity for the scrollToSceneSlide layer action
		if( ! empty( $sceneEmbedsGroup ) ) {
			$sceneAttrs[] = 'data-scene-group="'.$sceneEmbedsGroup.'"';
			$sceneAttrs[] = 'data-scene-slide="'.$sceneEmbedsSlide.'"';
		}
	}

	// Sticky + Scroll Scene wrapper START
	if( ! empty( $sceneWrapperHeight ) ){
		$sceneStyle = 'height: '.$sceneWrapperHeight.';';
	} elseif( !empty( $sceneSpacerHeight ) ){
		$sceneStyle = '--ls-duration: '.$sceneSpacerHeight.';';
	}

	if( $stickDuration !== 'unset' && $stickDuration < $sceneDuration ) {
		$sceneStyle .= '--ls-stickduration: '.($stickDuration*100).'vh;';
	}


	if( $projectLayout === 'responsive' || $projectLayout === 'fullwidth' ) {
		$sceneStyle .= '--ls-ratio: '.$projectProps['width']/$projectProps['height'].';';
	}

	if( $projectLayout === 'fixedsize' ) {
		$sceneStyle .= '--ls-height: '.$projectProps['height'].'px;';
	}

	$sceneClassNames = 'ls-playfrom-'.$playFrom;
	$sceneClassNames .= ' ls-stickto-'.$stickTo;
	$sceneClassNames .= ' ls-layout-'.$projectLayout;

	if( !empty($sceneClassNames) ){
		$sceneAttrs[] = 'class="' . $sceneClassNames . '"';
	}

	if( !empty($sceneStyle) ){
		$sceneAttrs[] = 'style="' . $sceneStyle . '"';
	}

	$lsContainer[] = '<ls-scene-wrapper ' . implode(' ', $sceneAttrs) . '>';
}


// Start of slider container
$lsContainer[] = '<div id="'.$projectInitId.'" '.( ! empty( $projectSlug ) ? 'data-ls-slug="'.$projectSlug.'"' : '' ).' class="ls-wp-container fitvidsignore'.$customClasses.'" style="'.implode('', $sliderStyleAttr).'">';

// Add slides
if(!empty($slider['slides']) && is_array($slider['slides'])) {
	foreach($slider['slides'] as $slidekey => $slide) {

		// Get slide attributes
		$slideId = !empty($slide['props']['id']) ? ' id="'.$slide['props']['id'].'"' : '';
		$slideAttrs = !empty($slide['attrs']) ? ls_array_to_attr($slide['attrs']) : '';

		if( ! empty( $slide['props']['customProperties'] ) && is_array( $slide['props']['customProperties'] ) ) {
			$slideAttrs .= ls_array_to_attr( $slide['props']['customProperties'] );
		}

		$postContent = false;

		// Post content
		//if( !isset($slide['props']['post_content']) || $slide['props']['post_content']) {
			$queryArgs = [
				'post_status' => 'publish',
				'limit' => 1,
				'posts_per_page' => 1,
				'suppress_filters' => false
			];


			if(isset($slide['props']['post_offset'])) {
				if($slide['props']['post_offset'] == -1) {
					$slide['props']['post_offset'] = $slidekey;
				}

				$queryArgs['offset'] = $slide['props']['post_offset'];
			}

			if(!empty($projectProps['post_type'])) {
				$queryArgs['post_type'] = $projectProps['post_type']; }

			if(!empty($projectProps['post_orderby'])) {
				$queryArgs['orderby'] = $projectProps['post_orderby']; }

			if(!empty($projectProps['post_order'])) {
				$queryArgs['order'] = $projectProps['post_order']; }

			if(!empty($projectProps['post_categories'][0])) {
				$queryArgs['category__in'] = $projectProps['post_categories']; }

			if(!empty($projectProps['post_tags'][0])) {
				$queryArgs['tag__in'] = $projectProps['post_tags']; }

			if(!empty($projectProps['post_taxonomy']) && !empty($projectProps['post_tax_terms'])) {
				$queryArgs['tax_query'][] = [
					'taxonomy' => $projectProps['post_taxonomy'],
					'field' => 'id',
					'terms' => $projectProps['post_tax_terms']
				];
			}

			$postContent = LS_Posts::find($queryArgs);
		//}

		// v8.3.0: Convert Origami to new sFX transitions
		if( ! empty( $slide['attrs']['transitionorigami'] ) ) {
			unset( $slide['attrs']['transitionorigami'] );
			$slide['props']['sfxTransitions'] = [
				'origami' => [ [ 'key' => 'darkFold' ] ]
			];
		}

		// v8.3.0: Load sFX transitions plugin and apply data attribute
		$slideTrAttr = '';
		if( $GLOBALS['lsIsActivatedSite'] && ! empty( $slide['props']['sfxTransitions'] ) && is_array( $slide['props']['sfxTransitions'] ) ) {

			$effects = [];

			foreach( $slide['props']['sfxTransitions'] as $effectKey => $presets ) {

				if( ! is_array( $presets ) ) {
					continue;
				}

				// Only keep enabled custom effects
				$kept = array_values( array_filter( $presets, function( $item ) {
					return ! ( isset( $item['enabled'] ) && $item['enabled'] === false );
				} ) );

				if( $kept ) {
					$effects[ $effectKey ] = $kept;
				}
			}

			if( $effects ) {
				$lsPlugins[] = 'slidefxtr'; // Load sFX transitions plugin
				$slideTrAttr = " data-ls-sfxtr='" . json_encode( $effects, JSON_NUMERIC_CHECK ) . "'";
			}
		}

		// Start of slide
		$slideAttrs = !empty($slideAttrs) ? ' data-ls="'.$slideAttrs.'"' : '';
		$lsMarkup[] = '<div class="ls-slide"'.$slideId.''.$slideAttrs.''.$slideTrAttr.'>';

		// Add slide background
		if( ! empty( $slide['props']['background'] ) && ls_assets_cond( $slide['props'], 'background') ) {
			$lsBG = '';
			$alt = '';

			if( ! empty($slide['props']['backgroundId'])) {

				$slide['props']['backgroundId'] = ls_translate_media_id( $slide['props']['backgroundId'] );

				$lsBG = ls_get_markup_image( $slide['props']['backgroundId'], ['class' => 'ls-bg'] );

			} elseif($slide['props']['background'] == '[image-url]') {
				$src = $postContent->getWithFormat($slide['props']['background']);

				if(is_object($postContent->post)) {
					$attachID = get_post_thumbnail_id($postContent->post->ID);
					$lsBG = ls_get_markup_image( $attachID, ['class' => 'ls-bg'] );
				}
			} else {
				$src = do_shortcode($slide['props']['background']);
				$alt = 'Slide background';
			}

			if( ! empty( $lsBG ) ) {


				if( ! $useSrcset ) {
					$lsBG = preg_replace('/srcset="[^\"]*"/', '', $lsBG);
					$lsBG = preg_replace('/sizes="[^\"]*"/', '', $lsBG);
				}

				if( $enhancedLazyLoad ) {
					$lsBG = str_replace(' src="', ' data-src="', $lsBG);
					$lsBG = str_replace(' srcset="', ' data-srcset="', $lsBG);
				}

				$lsMarkup[] = $lsBG;
			} elseif( ! empty( $src ) ) {
				$lsMarkup[] = '<img src="'.$src.'" class="ls-bg" alt="'.$alt.'" />';
			}
		}

		// Add slide thumbnail
		if(!isset($projectAttrs['thumbnailNavigation']) || $projectAttrs['thumbnailNavigation'] != 'disabled') {
			if( ! empty( $slide['props']['thumbnail'] ) && ls_assets_cond( $slide['props'], 'thumbnail') ) {

				$lsTN = '';
				$alt = '';

				if( ! empty($slide['props']['thumbnailId']) ) {

					$slide['props']['thumbnailId'] = ls_translate_media_id( $slide['props']['thumbnailId'] );

					$lsTN = ls_get_markup_image( $slide['props']['thumbnailId'], ['class' => 'ls-tn'] );

				} elseif($slide['props']['thumbnail'] == '[image-url]') {
					$src = $postContent->getWithFormat($slide['props']['thumbnail']);

					if(is_object($postContent->post)) {
						$attachID = get_post_thumbnail_id($postContent->post->ID);
						$lsTN = ls_get_markup_image( $attachID, ['class' => 'ls-tn'] );
					}
				} else {
					$src = do_shortcode($slide['props']['thumbnail']);
					$alt = 'Slide thumbnail';
				}

				if( ! empty( $lsTN ) ) {

					if( ! $useSrcset ) {
						$lsTN = preg_replace('/srcset="[^\"]*"/', '', $lsTN);
						$lsTN = preg_replace('/sizes="[^\"]*"/', '', $lsTN);
					}

					if( $enhancedLazyLoad ) {
						$lsTN = str_replace(' src="', ' data-src="', $lsTN);
						$lsTN = str_replace(' srcset="', ' data-srcset="', $lsTN);
					}

					$lsMarkup[] = $lsTN;
				} elseif( ! empty( $src ) ) {
					$lsMarkup[] = '<img src="'.$src.'" class="ls-tn" alt="'.$alt.'" />';
				}
			}
		}


		// Add layers
		if(!empty($slide['layers']) && is_array($slide['layers'])) {
			foreach($slide['layers'] as $layerkey => $layer) {

				$svgIB = false;

				// Skip this layer?
				if( ! empty( $layer['props']['skip'] ) ) {
					$skip = ls_normalize_hide_layer_value( $layer['props']['skip'] );
					if( $skip === 'all' ) {
						continue;
					}
				}

				unset($layerAttributes);
				unset($innerAttributes);
				$layerAttributes = ['style' => '', 'class' => 'ls-l'];
				$innerAttributes = ['style' => '', 'class' => ''];

				if( empty( $layer['props']['url'] ) ) {
					$innerAttributes =& $layerAttributes;
				}

				if( empty( $layer['props']['styles'] ) ) {
					$layer['props']['styles'] = [];
				}

				$layer['props']['html'] = ( ! empty( $layer['props']['html'] ) || ( isset( $layer['props']['html'] ) && $layer['props']['html'] === '0' ) ) ? trim( $layer['props']['html'] ) : '';
				$layer['props']['type'] = !empty($layer['props']['type']) ? $layer['props']['type'] : '';
				$layer['props']['media'] = !empty($layer['props']['media']) ? $layer['props']['media'] : '';

				// Premium layer content checks
				if( ! $GLOBALS['lsIsActivatedSite'] ) {

					// Protected layer types
					if( in_array( $layer['props']['media'], ['shape', 'countdown', 'counter'] ) ) {
						continue;
					}

					if( $layer['props']['media'] === 'icon' && ! empty( $layer['props']['html'] ) && strpos( $layer['props']['html'], '<svg' ) !== false ) {
						continue;
					}

					if( in_array( $layer['props']['media'], ['text', 'media', 'button', 'shape', 'icon', 'svg', 'html', 'post'] ) ) {
						if( ! ls_assets_cond( $layer['props'] ) ) {
							continue;
						}
					}
				}


				// String translation
				if( $lsTranslation['engine'] ) {

					// Layer content — WPML
					if( $lsTranslation['engine'] === 'wpml' ) {

						// v7.14.2: Use WPML's string packages translation method
						if( $lsTranslation['useStringPackages'] ) {
							$layer['props']['html'] = apply_filters( 'wpml_translate_string', $layer['props']['html'], $layer['props']['uuid'].'-html', $lsTranslation['package'] );

						// Check 'createdWith' property to decide which WPML implementation
						// should we use. This property was added in v6.5.5 along with the
						// new WPML implementation, so no version comparison required.
						} elseif( $sliderCreatedWith ) {
							$string_name = "slider-{$projectId}-layer-{$layer['props']['uuid']}-html";
							$layer['props']['html'] = apply_filters( 'wpml_translate_single_string', $layer['props']['html'], 'LayerSlider Sliders', $string_name );

						// Old implementation
						} else {
							$string_name = '<'.$layer['props']['type'].':'.substr(sha1($layer['props']['html']), 0, 10).'> layer on slide #'.($slidekey+1).' in slider #'.$projectId.'';
							$layer['props']['html'] = apply_filters( 'wpml_translate_single_string', $layer['props']['html'], 'LayerSlider WP', $string_name);
						}

					// Layer content — Polylang, resolved by the string value
					} elseif( $lsTranslation['engine'] === 'polylang' && $layer['props']['html'] !== '' ) {
						$layer['props']['html'] = pll__( $layer['props']['html'] );
					}

					// Translate manually entered static link URLs
					$layerUrlTranslated = false;
					if( ! empty( $layer['props']['url'] ) ) {

						$layerLinkId = ! empty( $layer['props']['linkId'] ) ? $layer['props']['linkId'] : '';

						if( ls_is_translatable_link( $layer['props']['url'], $layerLinkId ) ) {

							$urlBeforeTranslation = $layer['props']['url'];

							// Link URL — WPML
							if( $lsTranslation['engine'] === 'wpml' && ! empty( $layer['props']['uuid'] ) ) {

								// v7.14.2: Use WPML's string packages translation method
								if( $lsTranslation['useStringPackages'] ) {
									$layer['props']['url'] = apply_filters( 'wpml_translate_string', $layer['props']['url'], $layer['props']['uuid'].'-link', $lsTranslation['package'] );

								// Older implementation with single strings
								} elseif( $sliderCreatedWith ) {
									$string_name = "slider-{$projectId}-layer-{$layer['props']['uuid']}-link";
									$layer['props']['url'] = apply_filters( 'wpml_translate_single_string', $layer['props']['url'], 'LayerSlider Sliders', $string_name );
								}

							// Link URL — Polylang
							} elseif( $lsTranslation['engine'] === 'polylang' ) {
								$layer['props']['url'] = pll__( $layer['props']['url'] );
							}

							$layerUrlTranslated = ( $layer['props']['url'] !== $urlBeforeTranslation );
						}
					}

					// Untranslated, manually entered URLs: carry over the 'lang'
					// URI param on internal links. Translated URLs are left alone.
					if( ! $layerUrlTranslated && ! empty( $layer['props']['url'] ) && empty( $layer['props']['linkId'] ) ) {
						$layer['props']['url'] = ls_append_lang_uri_param( $layer['props']['url'] );
					}
				}

				// v7.0.0: Normalize HTML element tag for old versions
				if( empty( $layer['props']['htmlTag'] ) ) {

					$layer['props']['htmlTag'] = ! empty( $layer['props']['type'] ) ? $layer['props']['type'] : 'ls-layer';

					if( ! empty( $layer['props']['media'] ) ) {
						switch( $layer['props']['media'] ) {
							case 'img':
								$layer['props']['htmlTag'] = 'img';
								break;

							case 'button':
							case 'icon':
								$layer['props']['htmlTag'] = 'span';
								break;

							case 'html':
							case 'media':
								$layer['props']['htmlTag'] = 'div';
								break;

							case 'post':
								$layer['props']['htmlTag'] = 'div';
								break;
						}
					}
				}


				// Post layer
				if( $layer['props']['media'] === 'post' ) {
					$layer['props']['post_text_length'] = !empty($layer['props']['post_text_length']) ? $layer['props']['post_text_length'] : 0;
					$layer['props']['html'] = $postContent->getWithFormat($layer['props']['html'], $layer['props']['post_text_length']);
					$layer['props']['html'] = do_shortcode($layer['props']['html']);
				}


				// Handle media uploads
				if( $layer['props']['media'] === 'media' && isset( $layer['props']['mediaAttachments'] ) ) {

					// Make sure to empty the layer's HTML in case of using uploaded media
					$layer['props']['html'] = '';

					if( ! empty( $layer['props']['mediaAttachments'] ) ) {

						$mediaHTML = '';
						$mediaType = $layer['props']['mediaAttachments'][0]['type'];
						$width = ! empty( $layer['props']['mediaAttachments'][0]['width'] ) ? $layer['props']['mediaAttachments'][0]['width'] : 640;
						$height = ! empty( $layer['props']['mediaAttachments'][0]['height'] ) ? $layer['props']['mediaAttachments'][0]['height'] : 360;

						if( $mediaType === 'video' ) {
							$mediaHTML .= '<video width="'.$width.'" height="'.$height.'" preload="metadata" controls playsinline muted>';
						} else {
							$mediaHTML .= '<audio preload="metadata" controls>';
						}

						foreach( $layer['props']['mediaAttachments'] as $item ) {

							$item['id'] = ls_translate_media_id( $item['id'] );

							$mediaURL = wp_get_attachment_url( $item['id'] );
							$mediaURL = ! empty( $mediaURL ) ? $mediaURL : $item['url'];
							$mediaHTML .= '<source src="'.$mediaURL.'" type="'.$item['mime'].'">';
						}


						$mediaHTML .= '</'.$mediaType.'>';

						$layer['props']['html'] = $mediaHTML;
					}
				}

				// Should wrap layer? Test for a single HTML element
				$wrapLayer = true;
				if( $layer['props']['media'] === 'post' && ! empty( $layer['props']['html'] ) ) {

					$firstChar = substr( $layer['props']['html'], 0, 1 );
					$lastChar = substr( $layer['props']['html'], strlen( $layer['props']['html'] ) - 1, 1 );

					if( $firstChar === '<' && $lastChar === '>') {

						try {
							$layerHTML = LayerSlider\DOM::newDocumentHTML( $layer['props']['html'] );
							if( $layerHTML->length === 1 ) {
								$wrapLayer = false;
							}

						} catch( Exception $e ) {

						}
					}
				}

				// Skip image layer without src
				if( ( $layer['props']['type'] === 'img' || $layer['props']['media'] === 'img' ) && empty($layer['props']['image'])) { continue; }

				// Convert line breaks
				if( ! empty( $layer['props']['htmlLineBreak'] ) ) {

					if( $layer['props']['htmlLineBreak'] === 'enabled' ) {
						$layer['props']['html'] = nl2br( $layer['props']['html'] );
					}

					if( $layer['props']['htmlLineBreak'] === 'auto' ) {
						if( in_array( $layer['props']['media'], ['text', 'button', 'post'] ) ) {
							$layer['props']['html'] = nl2br( $layer['props']['html'] );
						}
					}
				}

				// Handle attached icon
				if( ! empty( $layer['props']['icon'] ) && in_array( $layer['props']['media'], ['text', 'button', 'post', 'html'] ) ) {

					// Premium content check
					if( $GLOBALS['lsIsActivatedSite'] || strpos( $layer['props']['icon'], '<svg' ) === false ) {

						$iconHTML = $layer['props']['icon'];
						$icon;

						if( ! empty( $layer['props']['html'] ) ) {
							$svgIB = true;
						}

						try {
							$icon = LayerSlider\DOM::newDocumentHTML( $layer['props']['icon'] );
						} catch( Exception $e ) {}

						$layer['props']['iconPlacement'] = ! empty( $layer['props']['iconPlacement'] ) ? $layer['props']['iconPlacement'] : '';

						// Icon Color & Icon Gap
						if( $icon ) {

							$iconCSS = [];

							if( ! empty( $layer['props']['iconColor'] ) ) {
								$iconCSS[ 'color' ] = $layer['props']['iconColor'];
							}

							if( ! empty( $layer['props']['iconGap'] ) ) {

								if( $layer['props']['iconPlacement'] === 'left' ) {
									$iconCSS[ 'margin-right' ] = $layer['props']['iconGap'].'em';
								} else {
									$iconCSS[ 'margin-left' ] = $layer['props']['iconGap'].'em';
								}
							}

							if( ! empty( $layer['props']['iconSize'] ) ) {
								$iconCSS[ 'font-size' ] = $layer['props']['iconSize'].'em';
							}

							if( ! empty( $layer['props']['iconVerticalAdjustment'] ) ) {
								$iconCSS[ 'transform' ] = 'translateY( '.$layer['props']['iconVerticalAdjustment'].'em )';
							}

							$icon->attr('style', ls_array_to_attr( $iconCSS ) );
							$iconHTML = $icon;
						}

						// Content & Icon Placement
						$layer['props']['html'] = ( $layer['props']['iconPlacement'] === 'left' ) ? $iconHTML.$layer['props']['html'] : $layer['props']['html'].$iconHTML;
					}
				}

				// Image layer
				$layerIMG = false;
				if( $layer['props']['type'] === 'img' || $layer['props']['media'] === 'img' ) {

					if( ! empty( $layer['props']['image'] ) && ls_assets_cond( $layer['props'], 'image') ) {

						if( ! empty($layer['props']['imageId'])) {

							$layer['props']['imageId'] = ls_translate_media_id( $layer['props']['imageId'] );

							$layerIMG = ls_get_markup_image( (int)$layer['props']['imageId'], ['class' => 'ls-l'] );

						} elseif($layer['props']['image'] == '[image-url]') {

							if(is_object($postContent->post)) {
								$attachID = get_post_thumbnail_id($postContent->post->ID);
								$layerIMG = ls_get_markup_image( $attachID, ['class' => 'ls-l'] );
							} else {
								$layerIMG = '<img src="'.$postContent->getWithFormat($layer['props']['image']).'">';
							}

						} else {

							$layerIMG = '<img src="'.do_shortcode( $layer['props']['image'] ).'">';

							if(!empty($layer['props']['alt'])) {
							$innerAttributes['alt'] = $layer['props']['alt']; }
								else { 	$innerAttributes['alt'] = ''; }
						}
					}
				}


				if( ! empty( $layerIMG ) && ! $useSrcset ) {
					$layerIMG = preg_replace('/srcset="[^\"]*"/', '', $layerIMG);
					$layerIMG = preg_replace('/sizes="[^\"]*"/', '', $layerIMG);
				}

				if( ! empty( $layerIMG ) && $enhancedLazyLoad ) {
					$layerIMG = str_replace(' src="', ' data-src="', $layerIMG);
					$layerIMG = str_replace(' srcset="', ' data-srcset="', $layerIMG);
				}

				// Layer element type & wrapping
				if( ! empty( $layerIMG ) ) {
					$type = $layerIMG;

				} elseif( ! $wrapLayer ) {
					$type = $layer['props']['html'];

				} else {
					$type = '<'.$layer['props']['htmlTag'].'>';
				}

				// Linked layer
				if( ! empty( $layer['props']['url'] ) ) {

					// Create <a> element
					$el = LayerSlider\DOM::newDocumentHTML('<a>')->children();

					// Auto-generated URL
					if( ! empty( $layer['props']['linkId'] ) ) {

						// Smart Links
						if( '#' === substr( $layer['props']['linkId'], 0, 1 ) ) {
							$layer['props']['url'] = $layer['props']['linkId'];

						// Dynamic Layer
						} elseif( '[post-url]' === $layer['props']['linkId'] ) {
							$layer['props']['url'] = $postContent->getWithFormat('[post-url]');

						// Attachment
						} elseif( ! empty( $layer['props']['linkType'] ) && $layer['props']['linkType'] === 'attachment' ) {
							$layer['props']['url'] = wp_get_attachment_url( $layer['props']['linkId'] );

						// Page / Post
						} else {
							$layer['props']['url'] = get_permalink( $layer['props']['linkId'] );
						}
					}


					if( $layer['props']['url'] === '[post-url]' ) {
						$layer['props']['url'] = $postContent->getWithFormat('[post-url]');
					}

					$layerAttributes['href'] = ! empty( $layer['props']['url'] ) ? do_shortcode( $layer['props']['url'] ) : '#';

					if(!empty($layer['props']['target'])) {
						$layerAttributes['target'] =  $layer['props']['target'];
					}

					$inner = $el->append($type)->children();

				} else {

					if( ! $wrapLayer ) {
						$el = $inner = LayerSlider\DOM::newDocumentHTML($type);
					} else {
						$el = $inner = LayerSlider\DOM::newDocumentHTML($type)->children();
					}

				}

				// HTML attributes
				$layerAttributes['class'] = 'ls-l';

				if(!empty($layer['props']['id'])) { $innerAttributes['id'] = $layer['props']['id']; }
				if(!empty($layer['props']['class'])) { $innerAttributes['class'] .= ' '.$layer['props']['class']; }

				if(!empty($layer['props']['url'])) {

					if(!empty($layer['props']['rel'])) {
						$layerAttributes['rel'] = $layer['props']['rel'];
					}

					if(!empty($layer['props']['title'])) {
						$layerAttributes['title'] = $layer['props']['title'];
					}

					if( isset( $layer['props']['tabindex']) && $layer['props']['tabindex'] !== '' ) {
						$layerAttributes['tabindex'] = $layer['props']['tabindex'];
					}

				} else {
					if(!empty($layer['props']['title'])) {
						$innerAttributes['title'] = $layer['props']['title'];
					}

					if( isset( $layer['props']['tabindex']) && $layer['props']['tabindex'] !== '' ) {
						$innerAttributes['tabindex'] = $layer['props']['tabindex'];
					}
				}


				if( ! empty( $layer['props']['posterId'] ) ) {

					$layer['props']['posterId'] = ls_translate_media_id( $layer['props']['posterId'] );

					$poster = wp_get_attachment_image_src( $layer['props']['posterId'], 'full', false );
					$poster = ! empty( $poster[0] ) ? $poster[0]: '';

					$layer['attrs']['poster'] = $poster;

					if( ! ls_assets_cond( $layer['attrs'], 'poster') ) {
						unset( $layer['attrs']['poster'] );
					}
				} elseif( ! empty( $layer['attrs']['poster'] ) ) {
					$layer['attrs']['poster'] = do_shortcode( $layer['attrs']['poster'] );

					if( ! ls_assets_cond( $layer['attrs'], 'poster') ) {
						unset( $layer['attrs']['poster'] );
					}
				}


				if(isset($layer['attrs']) && isset($layer['props']['transition'])) { $layerAttributes['data-ls'] = ls_array_to_attr($layer['attrs']); }
					elseif(isset($layer['attrs'])) { $layerAttributes['style'] .= ls_array_to_attr($layer['attrs']); }

				if(!empty($layer['props']['style'])) {
					if(substr($layer['props']['style'], -1) != ';') { $layer['props']['style'] .= ';'; }
					$innerAttributes['style'] .= preg_replace('/\s\s+/', ' ', $layer['props']['style']);
				}

				if( ! empty( $layer['props']['layerBackground'] ) && ls_assets_cond( $layer['props'], 'layerBackground') ) {

					if( ! empty( $layer['props']['layerBackgroundId'] ) ) {

						$layer['props']['layerBackgroundId'] = ls_translate_media_id( $layer['props']['layerBackgroundId'] );

						$layerBG = wp_get_attachment_image_src( $layer['props']['layerBackgroundId'], 'full', false );
						$layerBG = ! empty( $layerBG[0] ) ? $layerBG[0]: '';

					} elseif( $layer['props']['layerBackground'] === '[image-url]' ) {
						$layerBG = $postContent->getWithFormat( $layer['props']['layerBackground'] );

					} else {
						$layerBG = do_shortcode( $layer['props']['layerBackground'] );
					}

					$layer['props']['styles']['background-image'] = 'url("'.$layerBG.'")';
				}

				if( ! empty( $layer['props']['styles']['background-color'] ) && strstr( $layer['props']['styles']['background-color'], 'gradient' ) ) {

					if( empty( $layer['props']['styles']['background-image'] ) ) {
						$layer['props']['styles']['background-image'] = $layer['props']['styles']['background-color'];
					} else {
						$layer['props']['styles']['background-image'] .= ', ' . $layer['props']['styles']['background-color'];
					}

					unset( $layer['props']['styles']['background-color'] );
				}

				// v7.2.5: Add prefixed version of backdrop filter for Safari
				if( ! empty( $layer['props']['styles']['backdrop-filter'] ) ) {
					$layer['props']['styles']['-webkit-backdrop-filter'] = $layer['props']['styles']['backdrop-filter'];
				}

				// v7.5.0: Browser support for background-clip
				if( ! empty( $layer['props']['styles']['background-clip'] ) ) {

					if( ! $GLOBALS['lsIsActivatedSite'] ) {
						unset( $layer['props']['styles']['background-clip'] );

					} else {

						$layer['props']['styles']['-webkit-background-clip'] = $layer['props']['styles']['background-clip'];

						if( $layer['props']['styles']['background-clip'] === 'text' ) {
							$layer['props']['styles']['text-fill-color'] = 'transparent';
							$layer['props']['styles']['-webkit-text-fill-color'] = 'transparent';
						}
					}
				}

				// v7.9.9: Added clip-path support
				if( ! empty( $layer['props']['styles']['clip-path'] ) ) {
					$layer['props']['styles']['clip-path'] = 'polygon('.$layer['props']['styles']['clip-path'].')';
				}

				// v7.12.0: Countdowns
				if( $layer['props']['media'] === 'countdown' ) {

					$countdownID = ! empty( $layer['props']['countdownID'] ) ? $layer['props']['countdownID'] : '';
					$countdownData = ! empty( $slide['countdowns'][ $countdownID ] ) ? $slide['countdowns'][ $countdownID ] : (object)[];
					$countdownComponent = ! empty( $layer['props']['countdownComponent'] ) ? $layer['props']['countdownComponent'] : 'days';
					$useLeadingZeroes = isset( $layer['props']['countdownLeadingZeros'] ) ? $layer['props']['countdownLeadingZeros'] : false;

					$layer['props']['html'] = $useLeadingZeroes ? '00' : '0';
					$countdownStyles = [];

					$innerAttributes['data-countdown'] = json_encode( array_merge( $countdownData, [
						'component' => $countdownComponent,
						'leadingZeros' => $useLeadingZeroes
					]), JSON_NUMERIC_CHECK );

					ls_apply_affix_properties( $layer['props'], $innerAttributes, [
						'sliderID' => $projectId,
						'translation' => $lsTranslation
					]);
				}


				// v7.14.0: Counter
				if( $layer['props']['media'] === 'counter') {
					$counterStart = ! empty( $layer['props']['counterStart'] ) ? $layer['props']['counterStart'] : 0;
					$counterEnd = ! empty( $layer['props']['counterEnd'] ) ? $layer['props']['counterEnd'] : 100;
					$counterDecimals = ! empty( $layer['props']['counterDecimals'] ) ? $layer['props']['counterDecimals'] : '';
					$counterDecimalSeparator = ! empty( $layer['props']['counterDecimalSeparator'] ) ? $layer['props']['counterDecimalSeparator'] : '.';
					$counterThousandsSeparator = ! empty( $layer['props']['counterThousandsSeparator'] ) ? $layer['props']['counterThousandsSeparator'] : '';
					$counterLeadingZeros = isset( $layer['props']['counterLeadingZeros'] ) ? $layer['props']['counterLeadingZeros'] : false;
					$counterAnimationType = ! empty( $layer['props']['counterAnimationType'] ) ? $layer['props']['counterAnimationType'] : 'time';
					$counterDuration = ! empty( $layer['props']['counterDuration'] ) ? $layer['props']['counterDuration'] : 2000;
					$counterEasing = ! empty( $layer['props']['counterEasing'] ) ? $layer['props']['counterEasing'] : 'easeOutSine';
					$counterStep = ! empty( $layer['props']['counterStep'] ) ? $layer['props']['counterStep'] : 1;
					$counterStepDelay = ! empty( $layer['props']['counterStepDelay'] ) ? $layer['props']['counterStepDelay'] : 50;
					$counterStartAt = ! empty( $layer['props']['counterStartAt'] ) ? $layer['props']['counterStartAt'] : 'transitioninstart';

					// Auto-decide decimal places
					if( empty( $counterDecimals ) && ( $counterDecimals !== 0 || $counterDecimals !== '0' ) ) {

						$startDecimals = ls_get_decimal_places( $counterStart );
						$endDecimals = ls_get_decimal_places( $counterEnd );
						$stepDecimals = ls_get_decimal_places( $counterStep );

						if( $counterAnimationType === 'step' ) {
							$counterDecimals = max( $startDecimals, $endDecimals, $stepDecimals );
						} else {
							$counterDecimals = max( $startDecimals, $endDecimals );
						}
					}

					$counterData = [
						'type' => $counterAnimationType,
						'start' => $counterStart,
						'end' => $counterEnd,
						'dp' => (int) $counterDecimals,
						'ds' => $counterDecimalSeparator,
						'ts' => $counterThousandsSeparator,
						'lz' => $counterLeadingZeros,
						'startAt' => $counterStartAt
					];

					if( $counterAnimationType === 'step' ) {
						$counterData['step'] = $counterStep;
						$counterData['stepDelay'] = $counterStepDelay;
					} else {
						$counterData['duration'] = $counterDuration;
						$counterData['ease'] = $counterEasing;
					}

					$innerAttributes['data-counter'] = json_encode( $counterData, JSON_NUMERIC_CHECK );
					ls_apply_affix_properties( $layer['props'], $innerAttributes, [
						'sliderID' => $projectId,
						'translation' => $lsTranslation
					]);

					$formattedNumber = number_format( $counterEnd, (int) $counterDecimals, $counterDecimalSeparator, $counterThousandsSeparator );

					$layer['props']['html'] = $formattedNumber;
				}

				$innerAttributes['style'] .= ls_array_to_attr($layer['props']['styles'], 'css');

				// Text / HTML layer
				if( $wrapLayer ) {
					$inner->html(do_shortcode(__(stripslashes($layer['props']['html']))));
				}

				// Rewrite Youtube/Vimeo iframe src to data-src
				$video = $inner->find('iframe[src*="youtube-nocookie.com"], iframe[src*="youtube.com"], iframe[src*="youtu.be"], iframe[src*="player.vimeo"]');
				if( $video->length ) {
					$video->attr('data-src', $video->attr('src') );
					$video->removeAttr('src');
				}

				// Device dependent responsive classes
				if( ! empty($layer['props']['hide_on_desktop']) ) {
					$layerAttributes['class'] .=  ' ls-hide-desktop';
				}

				if( ! empty($layer['props']['hide_on_tablet']) ) {
					$layerAttributes['class'] .= ' ls-hide-tablet';
				}

				if( ! empty($layer['props']['hide_on_phone']) ) {
					$layerAttributes['class'] .= ' ls-hide-phone';
				}

				$el->attr( $layerAttributes );
				$inner->attr( $innerAttributes );

				if( ! empty( $layer['props']['outerAttributes'] ) ) {
					foreach( $layer['props']['outerAttributes'] as $key => $val ) {
						if( $key === 'class' ) {
							$el->addClass( $val );
						} else {
							$el->attr( $key, $val );
						}
					}
				}

				if( ! empty( $layer['props']['innerAttributes'] ) ) {
					foreach( $layer['props']['innerAttributes'] as $key => $val ) {
						if( $key === 'class' ) {
							$inner->addClass( $val );
						} else {
							$inner->attr( $key, $val );
						}
					}
				}

				// Layer Actions
				if( ! empty( $layer['props']['actions'] ) ) {

					$actionsString = json_encode( $layer['props']['actions'], JSON_NUMERIC_CHECK );

					$el->attr('data-ls-actions', $actionsString );

					if( strpos( $actionsString, 'openPopup' ) !== false ) {
						$GLOBALS['lsInitAjaxURL'] = true;
						$GLOBALS['lsLoadPlugins'][] = 'popup';
					}
				}

				// Layer Effects
				if( $GLOBALS['lsIsActivatedSite'] && ! empty( $layer['props']['effects'] ) ) {
					$inner->attr('data-ls-effects', json_encode( $layer['props']['effects'], JSON_NUMERIC_CHECK ) );
					foreach( $layer['props']['effects'] as $effect ) {
						if( ! in_array( $effect['effect'], $lsPlugins ) ) {
							$lsPlugins[] = $effect['effect'];
						}
					}
				}

				// Lottie
				if( ! empty( $layer['props']['media'] ) && $layer['props']['media'] === 'lottie' ) {

					$lottie = apply_filters('ls_parse_defaults', $lsDefaults['lottie'], $layer['props']['lottie'], [ 'esc_js' => true ] );

					if( ! $GLOBALS['lsIsActivatedSite'] || empty( $lottie['attrs']['src'] ) ) {
						continue;
					}

					$lottie = $lottie['attrs'];
					$uploads = wp_upload_dir();

					$lottie['src'] = ( substr( $lottie['src'], 0, 4 ) === 'http' )
						? do_shortcode( $lottie['src'] )
						: $uploads['baseurl'].'/layerslider/lottiefiles/' . $lottie['src'];

					$lottie['loop'] = ! empty( $lottie['loop'] ) ? $lottie['loop'] : 'enabled';
					$lottie['loop'] = ( $lottie['loop'] === 'enabled' ) ? true : false;

					if( ! empty( $lottie['fit'] ) || ! empty( $lottie['alignX'] ) || ! empty( $lottie['alignY'] ) ) {
						$lottie['layout'] = [];

						if( ! empty( $lottie['fit'] ) ) {
							$lottie['layout']['fit'] = $lottie['fit'];
							unset( $lottie['fit'] );
						}

						if( ! empty( $lottie['alignX'] ) || ! empty( $lottie['alignY'] ) ) {
							$lottie['alignX'] = ! empty( $lottie['alignX'] ) ? (float)$lottie['alignX'] : 0.5;
							$lottie['alignY'] = ! empty( $lottie['alignY'] ) ? (float)$lottie['alignY'] : 0.5;
							$lottie['layout']['align'] = [ $lottie['alignX'], $lottie['alignY'] ];
							unset( $lottie['alignX'] );
							unset( $lottie['alignY'] );
						}
					}

					$inner->attr('data-ls-lottie', json_encode( $lottie, JSON_NUMERIC_CHECK ) );
				}

				if( $svgIB ) {
					$inner->addClass('ls-ib-icon');
				}

				if( ! empty( $layer['props']['media'] ) ) {
					$inner->addClass('ls-'.$layer['props']['media'].'-layer');
				}

				if( ! empty( $layer['props']['userSelect'] ) ) {
					if( $layer['props']['userSelect'] === 'none' ) {
						$inner->addClass('ls-unselectable');
					} else {
						$inner->addClass('ls-selectable');
					}
				}

				$lsMarkup[] = $el;
				LayerSlider\DOM::unloadDocuments();
			}
		}

		// Link this slide
		if( ! empty( $slide['props']['linkUrl'] ) ) {

			if( ! empty( $slide['props']['linkTarget'] ) ) {
				$target = ' target="'.$slide['props']['linkTarget'].'"';
			} else {
				$target = '';
			}

			if( ! empty( $slide['props']['linkId'] ) ) {

				// Smart Links
				if( '#' === substr( $slide['props']['linkId'], 0, 1 ) ) {
					$slide['props']['linkUrl'] = $slide['props']['linkId'];

				// Dynamic Layer
				} elseif( '[post-url]' === $slide['props']['linkId'] ) {
					$slide['props']['linkUrl'] = $postContent->getWithFormat('[post-url]');

				// Attachment
				} elseif( ! empty( $slide['props']['linkType'] ) && $slide['props']['linkType'] === 'attachment' ) {
					$slide['props']['linkUrl'] = wp_get_attachment_url( $slide['props']['linkId'] );

				// Page / Post
				} else {
					$slide['props']['linkUrl'] = get_permalink( $slide['props']['linkId'] );
				}
			}


			// Translate manually entered static link URLs
			$slideUrlTranslated = false;
			if( $lsTranslation['engine'] ) {

				$slideLinkId = ! empty( $slide['props']['linkId'] ) ? $slide['props']['linkId'] : '';

				if( ls_is_translatable_link( $slide['props']['linkUrl'], $slideLinkId ) ) {

					$urlBeforeTranslation = $slide['props']['linkUrl'];

					// Slide link — WPML
					if( $lsTranslation['engine'] === 'wpml' && ! empty( $slide['props']['uuid'] ) ) {

						// v7.14.2: Use WPML's string packages translation method
						if( $lsTranslation['useStringPackages'] ) {
							$slide['props']['linkUrl'] = apply_filters( 'wpml_translate_string', $slide['props']['linkUrl'], $slide['props']['uuid'].'-link', $lsTranslation['package'] );

						// Older implementation with single strings
						} elseif( $sliderCreatedWith ) {
							$string_name = "slider-{$projectId}-slide-{$slide['props']['uuid']}-link";
							$slide['props']['linkUrl'] = apply_filters( 'wpml_translate_single_string', $slide['props']['linkUrl'], 'LayerSlider Sliders', $string_name );
						}

					// Slide link — Polylang, resolved by the string value
					} elseif( $lsTranslation['engine'] === 'polylang' ) {
						$slide['props']['linkUrl'] = pll__( $slide['props']['linkUrl'] );
					}

					$slideUrlTranslated = ( $slide['props']['linkUrl'] !== $urlBeforeTranslation );
				}
			}

			if( $slide['props']['linkUrl'] === '[post-url]' ) {
				$slide['props']['linkUrl'] = $postContent->getWithFormat('[post-url]');
			}

			// Apply shortcodes
			$slide['props']['linkUrl'] = do_shortcode( $slide['props']['linkUrl'] );

			// Untranslated, manually entered URLs: carry over the 'lang'
			// URI param on internal links. Translated URLs are left alone.
			if( $lsTranslation['engine'] && ! $slideUrlTranslated && empty( $slide['props']['linkId'] ) ) {
				$slide['props']['linkUrl'] = ls_append_lang_uri_param( $slide['props']['linkUrl'] );
			}


			$linkClass = 'ls-link';
			if( empty( $slide['props']['linkPosition'] ) || $slide['props']['linkPosition'] === 'over' ) {
				$linkClass .= ' ls-link-on-top';
			}

			$slide['props']['linkUrl'] = ! empty( $slide['props']['linkUrl'] ) ? $slide['props']['linkUrl'] : '#';

			$lsMarkup[] = '<a href="'.$slide['props']['linkUrl'].'"'.$target.' class="'.$linkClass.'"></a>';
		}

		// End of slide
		$lsMarkup[] = '</div>';
	}
}

// End of slider container
$lsMarkup[] = '</div>';

// End of scene wrapper
if( $needsSceneWrapper ) {

	$lsMarkup[] = '</ls-scene-wrapper>';
}

// End of Popup wrapper
if( $projectLayout === 'popup' ) {
	$lsMarkup[] = '</div>';
}

// After slider content hook
if(has_action('layerslider_after_slider_content')) {
	do_action('layerslider_after_slider_content');
}