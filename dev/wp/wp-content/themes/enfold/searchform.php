<?php
if( ! defined( 'ABSPATH' ) )   { exit; }

global $avia_config;

/**
 * We check for possible passed $args to this template
 *
 * @since 5.7.1
 */
if( ! is_array( $args ) )
{
	$args = [];
}

$defaults = [
			'placeholder'	=> __( 'Search', 'avia_framework' ),
			'search_id'		=> 's',
			'form_action'	=> home_url( '/' ),
			'ajax_disable'	=> true,
			'icon_title'	=> '??'
	];

$search_params = wp_parse_args( $args, $defaults );


/**
 * Allows you to modify the search parameters, e.g. bbpress search_id needs to be 'bbp_search' instead of 's'.
 * You can also deactivate ajax search by setting ajax_disable to true.
 *
 * @since ????
 * @since 5.7.1						added 'icon_title' - add a custom title attribute for search icon - ?? forces a default text
 * @param array $search_params
 * @return array
 */
$search_params = apply_filters( 'avf_frontend_search_form_param', $search_params );

$disable_ajax = $search_params['ajax_disable'] == false ? '' : 'av_disable_ajax_search';

$icon_title = esc_attr( $search_params['icon_title'] );

if( '??' == $icon_title )
{
	if( $disable_ajax )
	{
		$icon_title = __( 'Click to start search', 'avia_framework' );
	}
	else
	{
		$icon_title = __( 'Enter at least 3 characters to show search results in a dropdown or click to route to search result page to show all results', 'avia_framework' );
	}
}

$icon = av_icon_char( 'search' );
$class = av_icon_class( 'search' );
$placeholder = esc_attr( $search_params['placeholder'] );

?>

<search>
	<form action="<?php echo $search_params['form_action']; ?>" id="searchform" method="get" class="<?php echo $disable_ajax; ?>">
		<div>
			<input type="submit" value="<?php echo $icon; ?>" id="searchsubmit" class="button <?php echo $class; ?>" title="<?php echo $icon_title; ?>" />
			<input type="search" id="s" name="<?php echo $search_params['search_id']; ?>" value="<?php if( ! empty( $_GET['s'] ) ) { echo get_search_query(); } ?>" aria-label='<?php echo $placeholder; ?>' placeholder='<?php echo $placeholder; ?>' required />
			<?php

			// allows to add aditional form fields to modify the query (eg add an input with name "post_type" and value "page" to search for pages only)
			do_action( 'ava_frontend_search_form' );

			?>
		</div>
	</form>
</search>
