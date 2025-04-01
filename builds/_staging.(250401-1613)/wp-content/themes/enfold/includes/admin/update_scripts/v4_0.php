<?php
/**
 * update for version 4.0
 *
 * updates the old layerslider datastructure to the new one so we can use the latest version of the slider
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


// Get WPDB Object
global $wpdb;

// Table name
$table_name = $wpdb->prefix . "layerslider";

// Get sliders
$sliders = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY date_c ASC LIMIT 300" );

if( empty( $sliders ) )
{
	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
	{
		error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
	}

	return;
}

$easy_mapping = array(
	"easingin",
	"easingout",
	"delayin",
	"delayout",
	"durationin",
	"durationout",
	"showuntil",
	"rotateout",
	"rotatein",
);

foreach($sliders as $key => $item)
{
	// we presume that no update is necessary. only after we check each slide data and the subslides we know if the transition property is available.
	// if it is not we need to update
	$update_necessary 	= false;
	$id 				= $item->id;
	$layers 			= false;
	$test_update_all 	= false; // testing - update everything
	$uploaded_imgs		= array();

	if(isset($item->data))
	{
		$data = json_decode($item->data);
		if(is_object($data) && !empty($data->layers)) $layers = $data->layers;
	}

	if($layers)
	{
		foreach($layers as $layer)
		{
			//update the subslides
			if(!empty($layer->sublayers))
			{
				foreach ($layer->sublayers as $sublayer )
				{
					if(empty($sublayer->transition) || $test_update_all )
					{
						$update_necessary = true;
						$sublayer->transition = array();

						//first map the easy conditions to the new slider. those are 1:1 translateable because the got the same key
						foreach($easy_mapping as $key)
						{
							if(!empty($sublayer->$key)){
								$sublayer->transition[$key] = $sublayer->$key;
							}
						}

						//now map the complicated stuff

						//slideIndirection
						if(!empty($sublayer->slidedirection))
						{
							if(in_array($sublayer->slidedirection, array('left','right','auto')))
							{
								$sublayer->transition['offsetxin'] = $sublayer->slidedirection;
								$sublayer->transition['offsetyin'] = "0";
							}
							else
							{
								$sublayer->transition['offsetyin'] = $sublayer->slidedirection;
								$sublayer->transition['offsetxin'] = "0";
							}
						}

						//slideOutdirection
						if(!empty($sublayer->slideoutdirection))
						{
							if(in_array($sublayer->slideoutdirection, array('left','right','auto')))
							{
								$sublayer->transition['offsetxout'] = $sublayer->slideoutdirection;
								$sublayer->transition['offsetyout'] = "0";
							}
							else
							{
								$sublayer->transition['offsetyout'] = $sublayer->slideoutdirection;
								$sublayer->transition['offsetxout'] = "0";
							}
						}

						$sublayer->transition = json_encode($sublayer->transition);
					}

					//update image links for old demo sliders in case they are still used by some users
					//eg old: http://wpoffice/layerslider-test-2/wp-content/themes/enfold/config-layerslider/LayerSlider/avia-samples/slide1_Layer_2.png
					//to new: http://www.kriesi.at/themes/wp-content/uploads/avia-sample-layerslides/slide1_Layer_2.png

					if(!empty($sublayer->image) && strpos($sublayer->image, "/config-layerslider/LayerSlider/") !== false)
					{
						update_option('enfold_layerslider_compat_update', 1);

						/*
						set_time_limit ( 0 );

						$image_name = basename($sublayer->image);

						if(isset($uploaded_imgs[$image_name]))
						{
							$sublayer->image = $new_url;
							$update_necessary = true;
						}
						else
						{
							$full_url 	= "http://www.kriesi.at/themes/wp-content/uploads/avia-sample-layerslides/" . $image_name;

							$new_url = media_sideload_image( $full_url , false, null, 'src');

							if(!is_object($new_url) && !empty($new_url))
							{
								$uploaded_imgs[$image_name] = $new_url;
								$sublayer->image = $new_url;
								$update_necessary = true;
							}
						}
						*/
					}

				}
			}


			//update images for the main slides

			if(isset($layer->properties))
			{
				if(isset($layer->properties->background) && strpos($layer->properties->background, "/config-layerslider/LayerSlider/") !== false)
				{
					update_option('enfold_layerslider_compat_update', 1);

					/*
					set_time_limit ( 0 );
					$image_name = basename($layer->properties->background);

					if(isset($uploaded_imgs[$image_name]))
					{
						$sublayer->image = $new_url;
						$update_necessary = true;
					}
					else
					{
						$full_url 	= "http://www.kriesi.at/themes/wp-content/uploads/avia-sample-layerslides/" . $image_name;
						$new_url = media_sideload_image( $full_url , false, null, 'src');

						if(!is_object($new_url) && !empty($new_url))
						{
							$uploaded_imgs[$image_name] = $new_url;
							$layer->properties->background = $new_url;
							$update_necessary = true;
						}
					}
					*/


				}
			}
		}
	}

	if($update_necessary)
	{
		$wpdb->update($table_name, array(
			'data' => json_encode($data),
		),
		array('id' => $id),
		array('%s')
		);
	}
}

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( esc_html( __( 'Executed theme update script:', 'avia_framework' ) ) . ' ' . basename( __FILE__ ) );
}
