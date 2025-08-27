<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;

function layerslider_builder_convert_numbers(&$item, $key) {
	if(is_numeric($item)) {
		$item = (float) $item;
	}
}

function ls_ordinal_number($number) {
	$ends = ['th','st','nd','rd','th','th','th','th','th','th'];
	$mod100 = $number % 100;
	return $number . ($mod100 >= 11 && $mod100 <= 13 ? 'th' :  $ends[$number % 10]);
}


function layerslider_check_unit($str, $key = '') {

	if( strstr($str, 'px') == false && strstr($str, '%') == false && strstr($str, 'em') == false && strstr($str, 'vw') == false ) {
		if( $key !== 'z-index' && $key !== 'font-weight' && $key !== 'opacity') {
			return $str.'px';
		}
	}

	return $str;
}

function ls_get_markup_image( $id, $attrs = [] ) {
	return wp_get_attachment_image( $id, 'full', false, $attrs );
}

function ls_lazy_loading_cb() {
	return false;
}

function ls_assets_cond( $data = [], $key = 0 ) {

	if( ! $GLOBALS['lsIsActivatedSite'] ) {

		if( ! empty( $data['isAsset'] ) ) {
			return false;
		}

		if( ! empty( $data[ $key ] ) && strpos( $data[ $key ], '/layerslider/assets/' ) !== false ) {
			return false;
		}
	}

	return true;
}

function ls_normalize_hide_layer_value( $value = false ) {
	if( $value === 'editor' || $value === 'all' ) {
		return $value;
	}

	$value = !! $value;

	return $value ? 'all' : false;
}

function ls_apply_affix_properties( $layerProps, &$innerAttributes, $properties ) {

	$styles = [];
	$wpml_string_base = "slider-{$properties['sliderID']}-layer-{$layerProps['uuid']}";

	if( ! empty( $layerProps['affixBefore'] ) ) {

		if( $properties['wpml']['useStringTranslation'] ) {
			if( $properties['wpml']['useStringPackages'] ) {
				$layerProps['affixBefore'] = apply_filters( 'wpml_translate_string', $layerProps['affixBefore'], $layerProps['uuid'].'-affix-before', $properties['wpml']['package'] );
			} else {
				$layerProps['affixBefore'] = apply_filters( 'wpml_translate_single_string', $layerProps['affixBefore'], 'LayerSlider Sliders', $wpml_string_base.'-affix-before' );
			}
		}

		$innerAttributes['data-prefix'] = do_shortcode( __( stripslashes( $layerProps['affixBefore'] ) ) );
	}

	if( ! empty( $layerProps['affixAfter'] ) ) {

		if( $properties['wpml']['useStringTranslation'] ) {
			if( $properties['wpml']['useStringPackages'] ) {
				$layerProps['affixAfter'] = apply_filters( 'wpml_translate_string', $layerProps['affixAfter'], $layerProps['uuid'].'-affix-after', $properties['wpml']['package'] );
			} else {
				$layerProps['affixAfter'] = apply_filters( 'wpml_translate_single_string', $layerProps['affixAfter'], 'LayerSlider Sliders', $wpml_string_base.'-affix-after' );
			}
		}

		$innerAttributes['data-suffix'] = do_shortcode( __( stripslashes( $layerProps['affixAfter'] ) ) );
	}

	if( ! empty( $layerProps['affixFloat'] ) ) {
		$innerAttributes['class'] .=  ' ls-affix-float';
	}

	if( ! empty( $layerProps['affixNewLine'] ) ) {
		$styles['--ls-affix-nl'] = 'block';
	}

	if( ! empty( $layerProps['affixColor'] ) ) {
		$styles['--ls-affix-color'] = $layerProps['affixColor'];
	}

	if( ! empty( $layerProps['affixFontSize'] ) ) {
		$styles['--ls-affix-fs'] = $layerProps['affixFontSize'].'em';
	}

	if( ! empty( $layerProps['affixFontFamily'] ) ) {
		$styles['--ls-affix-ff'] = $layerProps['affixFontFamily'];
	}

	if( ! empty( $layerProps['affixFontWeight'] ) ) {
		$styles['--ls-affix-fw'] = $layerProps['affixFontWeight'];
	}

	if( ! empty( $layerProps['affixHA'] ) ) {
		$styles['--ls-affix-ha'] = $layerProps['affixHA'].'em';
	}

	if( ! empty( $layerProps['affixVA'] ) ) {
		$styles['--ls-affix-va'] = $layerProps['affixVA'].'em';
	}

	$innerAttributes['style'] .= ls_array_to_attr( $styles, 'css' );
}

function ls_get_decimal_places( $number ) {

	if( ! is_numeric( $number ) ) {
        return 0;
    }

    $parts = explode( '.', (string) $number );
    return isset( $parts[1] ) ? strlen( $parts[1] ) : 0;
}