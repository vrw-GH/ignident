<?php
namespace aviaFramework\widgets;

use \avia_htmlhelper;

/**
 * AVIA Google Maps Widget
 *
 * Widget that displays a Google Map
 *
 * @package AviaFramework
 * @since ???
 * @since 4.9			Code was moved from class-framework-widgets.php
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }


/*
	Google Maps Widget

	Copyright 2009  Clark Nikdel Powell  (email : taylor@cnpstudio.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if( ! class_exists( __NAMESPACE__ . '\avia_google_maps', false ) )
{
	class avia_google_maps extends \aviaFramework\widgets\base\Avia_Widget
	{
		/**
		 *
		 */
		public function __construct()
		{
			$id_base = 'avia_google_maps';
			$name = THEMENAME . ' ' . __( 'Google Maps Widget', 'avia_framework' );

			$widget_options = array(
								'classname'				=> 'avia_google_maps avia_no_block_preview',
								'description'			=> __( 'Add a google map to your blog or site', 'avia_framework' ),
								'show_instance_in_rest' => true,
								'customize_selective_refresh' => false
							);

			parent::__construct( $id_base, $name, $widget_options );

//            add_action( 'admin_enqueue_scripts', array( $this,'handler_print_google_maps_scripts' ) );
		}

		/**
		 * @since 4.3.2
		 */
		public function __destruct()
		{
			parent::__destruct();
		}

		/**
		 *
		 * @since 4.3.2
		 * @param array $instance
		 * @return array
		 */
		protected function parse_args_instance( array $instance )
		{
			$SGMoptions = get_option( 'SGMoptions', array() ); // get options defined in admin page ????
			$SGMoptions = wp_parse_args( $SGMoptions, array(
											'zoom'				=>	'15',			// 1 - 19
											'type'				=>	'ROADMAP',		// ROADMAP, SATELLITE, HYBRID, TERRAIN
											'content'			=>	'',
										) );

			$new_instance = wp_parse_args( $instance, array(
											'title'				=>	'',
											'lat'				=>	'0',
											'lng'				=>	'0',
											'zoom'				=>	$SGMoptions['zoom'],
											'type'				=>	$SGMoptions['type'],
											'directionsto'		=>	'',
											'content'			=>	$SGMoptions['content'],
											'width'				=>	'',
											'height'			=>	'',
											'street-address'	=>	'',
											'city'				=>	'',
											'state'				=>	'',
											'postcode'			=>	'',
											'country'			=>	'',
											'icon'				=>	'',
											'google_link'		=>	'',
											'confirm_button'	=>	__( 'Click to load Google Maps', 'avia_framework' ),
											'page_link_text'	=>	__( 'Open Google Maps in a new window', 'avia_framework' ),
											'google_fallback'	=>	''
										) );

			return $new_instance;
		}

		/**
		 * Output the widget in frontend
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance )
		{
			$instance = $this->parse_args_instance( $instance );
			$fields = $this->get_field_names();

			foreach( $instance as $key => $value )
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = esc_attr( $value );
				}
			}

			/**
			 * Filter $instance values on page basis
			 *
			 * @since 5.6
			 * @param array $instance
			 * @param array $args
			 * @param string $context
			 * @return array
			 */
			$instance = apply_filters( 'avf_widget_front_instance', $instance, $args, get_called_class() );


			if( $this->in_block_editor_preview( $args, $instance ) )
			{
				return;
			}

			if( true === Av_Google_Maps()->is_loading_prohibited() )
			{
				if( current_user_can( 'edit_posts' ) )
				{
					echo '<p style="font-weight: 700;color: red;">' . __( 'Widget Google Maps is disabled in theme options &quot;Google Services&quot;.', 'avia_framework' ) . '</p>';
					echo '<p style="font-weight: 400;color: red;">' . __( '(Visible to admins only.)', 'avia_framework' ) . '</p>';
				}

				return;
			}

			extract( $args );
			extract( $instance );

			$street_address = $instance['street-address'];

			if( empty( $lat ) || empty( $lng ) )
			{
				return;
			}

			/**
			 * Allow to change the conditional display setting - e.g. if user is opt in and allows to connect directly
			 *
			 * @since 4.4
			 * @param string $google_link			'' | 'confirm_link' | 'page_only'
			 * @param string $context
			 * @param mixed $object
			 * @param array $args
			 * @param array $instance
			 * @return string
			 */
			$original_google_link = $google_link;
			$google_link = apply_filters( 'avf_conditional_setting_external_links', $google_link, __CLASS__, $this, $args, $instance );
			if( ! in_array( $google_link, array( '', 'confirm_link', 'page_only' ) ) )
			{
				$google_link = $original_google_link;
			}

			$title = apply_filters( 'widget_title', $title );

			echo $before_widget;

			if( ! empty( $title ) )
			{
				echo $before_title . $title . $after_title;
			}


			$html_fallback_url = '';
			if( ! empty( $google_fallback ) )
			{
				$html_fallback_url .= 'background-image:url(' . $google_fallback . ');';
			}

			$html_overlay = '';
			if( ( 'confirm_link' == $google_link ) || ( 'page_only' == $google_link ) )
			{
				$button_class = empty( $html_fallback_url ) ? ' av_text_confirm_link_visible' : '';

				$text_overlay = '';
				if( 'confirm_link' == $google_link )
				{
					$html_overlay = '<a href="#" class="av_gmaps_confirm_link av_text_confirm_link' . $button_class . '">';
					$text_overlay =	esc_html( $confirm_button );
				}
				else
				{
					if( empty( $street_address ) )
					{
						$adress1 = $lat;
						$adress2 = $lng;
					}
					else
					{
						$adress1 = $street_address . ' ' . $postcode . ' ' . $city . ' ' . $state . ' ' . $country;
						$adress2 = '';
					}

					$url = \av_google_maps::api_destination_url( $adress1, $adress2 );
					$html_overlay = avia_targeted_link_rel( '<a class="av_gmaps_page_only av_text_confirm_link' . $button_class . '" href="' . $url . '" target="_blank">' );
					$text_overlay = esc_html( $page_link_text );
				}

				$html_overlay .= '<span>' . $text_overlay . '</span></a>';

				/**
				 * @since 4.4.2
				 * @param string		output string
				 * @param object		context
				 * @param array
				 * @param array
				*/
				$filter_args = array(
						   $html_overlay,
						   $this,
						   $args,
						   $instance
				   );
				$html_overlay = apply_filters_ref_array( 'avf_google_maps_confirm_overlay', $filter_args );
			}

			$map_id = '';
			if( 'page_only' != $google_link )
			{
				/**
				 * Add map data to js
				 */
				$content = htmlspecialchars( $content, ENT_QUOTES );
				$content = str_replace( '&lt;', '<', $content );
				$content = str_replace( '&gt;', '>', $content );
				$content = str_replace( '&quot;', '"', $content );
				$content = str_replace( '&#039;', '"', $content );
//				$content = json_encode( $content );
				$content = wpautop( $content );

				$data = array(
							'hue'					=> '',
							'zoom'					=> $zoom,
							'saturation'			=> '',
							'zoom_control'			=> true,
//							'pan_control'			=> true,				not needed in > 4.3.2
							'streetview_control'	=> false,
							'mobile_drag_control'	=> true,
							'maptype_control'		=> 'dropdown',
							'maptype_id'			=> $type
				);

				$data['marker'] = array();

				$data['marker'][0] = array(
							'address'			=> $postcode . '  ' . $street_address,
							'city'				=> $city,
							'country'			=> $country,
							'state'				=> $state,
							'long'				=> $lng,
							'lat'				=> $lat,
							'icon'				=> $icon,
							'imagesize'			=> 40,
							'content'			=> $content,
					);

				/**
				 * Does not work since 4.4
				 */
				if( ! empty( $directionsto ) )
				{
					$data['marker'][0]['directionsto'] = $directionsto;
				}

				$add = empty( $google_link ) ? 'unconditionally' : 'delayed';

				/**
				 * Allow to filter Google Maps data array
				 *
				 * @since 4.4
				 * @param array $data
				 * @param string context
				 * @param object
				 * @param array additional args
				 * @return array
				 */
				$data = apply_filters( 'avf_google_maps_data', $data, __CLASS__, $this, array( $args, $instance ) );

				$map_id = Av_Google_Maps()->add_map( $data, $add );
			}

			switch( $google_link )
			{
				case 'confirm_link':
					$show_class = 'av_gmaps_show_delayed';
					break;
				case 'page_only':
					$show_class = 'av_gmaps_show_page_only';
					break;
				case '':
				default:
					$show_class = 'av_gmaps_show_unconditionally';
					break;
			}

			if( empty( $html_fallback_url ) )
			{
				$show_class .= ' av-no-fallback-img';
			}

			$style = '';		// $this->define_height($height)
			if( ! empty( $height ) )
			{
				$height = str_replace( ';', '', $height );
				$style .= " height: {$height};";
			}
			if( ! empty( $width ) )
			{
				$width = str_replace( ';', '', $width );
				$style .= " width: {$width};";
			}
			if( ! empty( $html_fallback_url ) )
			{
				$html_fallback_url = str_replace( ';', '', $html_fallback_url );
				$style .= " {$html_fallback_url};";
			}

			if( ! empty( $style ) )
			{
				$style = "style='{$style}'";
			}

			echo '<div class="av_gmaps_widget_main_wrap av_gmaps_main_wrap">';

			if( empty( $map_id ) )
			{
				echo	"<div class='avia-google-map-container avia-google-map-widget {$show_class}' {$style}>";
			}
			else
			{
				echo	"<div id='{$map_id}' class='avia-google-map-container avia-google-map-widget {$show_class}' data-mapid='{$map_id}' {$style}>";
			}

			echo			$html_overlay;
			echo		'</div>';

			echo '</div>';

			echo $after_widget;
		}

		/**
		 * Callback to output a custom block preview
		 *
		 * @since 4.9
		 * @param array $args
		 * @param array $instance
		 * @return boolean					true if output processed
		 */
		protected function widget_block_preview( array $args, array $instance = array() )
		{
			echo isset( $args['before_widget'] ) ? $args['before_widget'] : '';

			echo '<div class="avia-preview-headline">' . $this->name . '</div>';
			echo '<div class="avia-preview-info">' . __( 'Title:', 'avia_framework' ) . ' ' . $instance['title'] .  '</div>';

			$address = array_filter( array(
							$instance['street-address'],
							$instance['postcode'],
							$instance['city'],
							$instance['state'],
							$instance['country']
						) );

			if( ! empty( $address ) )
			{
				$coord = array( __( 'Address:', 'avia_framework' ) . ' ' . implode( ', ', $address ) );
			}
			else
			{
				$coord = array(
						__( 'Latitude:', 'avia_framework' ) . ' ' . $instance['lat'],
						__( 'Longitude:', 'avia_framework' ) . ' ' . $instance['lng']
					);
			}

			foreach( $coord as $value )
			{
				echo '<div class="avia-preview-info">' . $value .  '</div>';
			}

			if( true === Av_Google_Maps()->is_loading_prohibited() )
			{
				echo '<div class="avia-preview-in-front" style="color: red;">' . __( 'Google Maps is disabled in theme options &quot;Google Services&quot;.', 'avia_framework' ) . '</div>';
			}
			else
			{
				echo '<div class="avia-preview-in-front">' . __( 'Map is only rendered in frontend.', 'avia_framework' ) . '</div>';
			}

			echo isset( $args['after_widget'] ) ? $args['after_widget'] : '';

			return true;
		}

		/**
		 * Update widget options
		 *
		 * @param array $new_instance
		 * @param array $old_instance
		 * @return array
		 */
		public function update( $new_instance, $old_instance )
		{
			$instance = $this->parse_args_instance( $old_instance );

			$fields = $this->get_field_names();

			foreach( $new_instance as $key => $value )
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = strip_tags( $value );
				}
			}

			return $instance;
		}

		/**
		 * Output the form in backend
		 *
		 * @param array $instance
		 */
		public function form( $instance )
		{
			$instance = $this->parse_args_instance( $instance );
			$fields = $this->get_field_names();

			foreach( $instance as $key => $value )
			{
				if( in_array( $key, $fields ) )
				{
					$instance[ $key ] = esc_attr( $value );
				}
			}

			extract( $instance );

			$street_address = $instance['street-address'];

			$html = new avia_htmlhelper();

			$marker_icon_element = array(
								'name'		=> __( 'Custom Marker Icon', 'avia_framework' ),
								'desc'		=> __( 'Upload a custom marker icon or enter the URL', 'avia_framework' ),
								'id'		=> $this->get_field_id( 'icon' ),
								'id_name'	=> $this->get_field_name( 'icon' ),
								'std'		=> $icon,
								'type'		=> 'upload',
								'label'		=> __( 'Use image as custom marker icon', 'avia_framework' )
							);

			$fallback_element = array(
								'name'		=> __( 'Fallback image to replace Google Maps', 'avia_framework' ),
								'desc'		=> __( 'Upload a fallback image or enter the URL to an image to replace Google Maps or until Google Maps is loaded', 'avia_framework' ),
								'id'		=> $this->get_field_id( 'google_fallback' ),
								'id_name'	=> $this->get_field_name( 'google_fallback' ),
								'std'		=> $google_fallback,
								'type'		=> 'upload',
								'label'		=> __( 'Use image as Google Maps fallback image', 'avia_framework' )
							);

			?>
			<div class="avia_widget_form avia_widget_conditional_form avia_google_maps_form <?php echo $google_link;?>">
				<p>
					<label for="<?php print $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'avia_framework' ); ?></label>
					<input class="widefat" id="<?php print $this->get_field_id( 'title' ); ?>" name="<?php print $this->get_field_name( 'title' ); ?>" type="text" value="<?php print $title; ?>" />
				</p>
				<p>
				<?php _e( 'Enter the latitude and longitude of the location you want to display. Need help finding the latitude and longitude?', 'avia_framework' ); ?> <a href="#" class="avia-coordinates-help-link button"><?php _e( 'Click here to enter an address.', 'avia_framework' ); ?></a>
                </p>
				<div class="avia-find-coordinates-wrapper">
                    <p>
                        <label for="<?php print $this->get_field_id( 'street-address' ); ?>"><?php _e( 'Street Address:', 'avia_framework' ); ?></label>
                        <input class='widefat avia-map-street-address' id="<?php print $this->get_field_id( 'street-address' ); ?>" name="<?php print $this->get_field_name( 'street-address' ); ?>" type="text" value="<?php print $street_address; ?>" />
                    </p>
                    <p>
                        <label for="<?php print $this->get_field_id( 'city' ); ?>"><?php _e( 'City:', 'avia_framework' ); ?></label>
                        <input class='widefat avia-map-city' id="<?php print $this->get_field_id( 'city' ); ?>" name="<?php print $this->get_field_name( 'city' ); ?>" type="text" value="<?php print $city; ?>" />
                    </p>
                    <p>
                        <label for="<?php print $this->get_field_id( 'state' ); ?>"><?php _e( 'State:', 'avia_framework' ); ?></label>
                        <input class='widefat avia-map-state' id="<?php print $this->get_field_id( 'state' ); ?>" name="<?php print $this->get_field_name( 'state' ); ?>" type="text" value="<?php print $state; ?>" />
                    </p>
                    <p>
                        <label for="<?php print $this->get_field_id( 'postcode' ); ?>"><?php _e( 'Postcode:', 'avia_framework' ); ?></label>
                        <input class='widefat avia-map-postcode' id="<?php print $this->get_field_id( 'postcode' ); ?>" name="<?php print $this->get_field_name( 'postcode' ); ?>" type="text" value="<?php print $postcode; ?>" />
                    </p>
                    <p>
                        <label for="<?php print $this->get_field_id( 'country' ); ?>"><?php _e( 'Country:', 'avia_framework' ); ?></label>
                        <input class='widefat avia-map-country' id="<?php print $this->get_field_id( 'country' ); ?>" name="<?php print $this->get_field_name( 'country' ); ?>" type="text" value="<?php print $country; ?>" />
                    </p>
                    <p>
                        <a class="button avia-populate-coordinates"><?php _e( 'Fetch coordinates!', 'avia_framework' ); ?></a>
                        <div class='avia-loading-coordinates'><?php _e( 'Fetching the coordinates. Please wait...', 'avia_framework' ); ?></div>
                    </p>
                </div>
                <div class="avia-coordinates-wrapper">
					<p>
						<label for="<?php print $this->get_field_id( 'lat' ); ?>"><?php _e( 'Latitude:', 'avia_framework' ); ?></label>
						<input class='widefat avia-map-lat' id="<?php print $this->get_field_id( 'lat' ); ?>" name="<?php print $this->get_field_name( 'lat' ); ?>" type="text" value="<?php print $lat; ?>" />
					</p>
					<p>
						<label for="<?php print $this->get_field_id( 'lng' ); ?>"><?php _e( 'Longitude:', 'avia_framework' ); ?></label>
						<input class='widefat avia-map-lng' id="<?php print $this->get_field_id( 'lng' ); ?>" name="<?php print $this->get_field_name( 'lng' ); ?>" type="text" value="<?php print $lng; ?>" />
					</p>
                </div>
        		<p>
				<label for="<?php print $this->get_field_id( 'zoom' ); ?>"><?php echo __( 'Zoom Level:', 'avia_framework' ).' <small>'.__( '(1-19)', 'avia_framework' ).'</small>'; ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'zoom' ); ?>" name="<?php echo $this->get_field_name( 'zoom' ); ?>">
					<?php
					$answers = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19);
					foreach( $answers as $answer )
					{
						?><option value="<?php echo $answer;?>" <?php selected( $answer, $zoom ); ?>><?php echo $answer;?></option><?php
					}?>
				</select>
				</p>
				<p>
				<label for="<?php print $this->get_field_id( 'type' ); ?>"><?php _e( 'Map Type:', 'avia_framework' ); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>">
					<?php
					$answers = array( 'ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN' );
					foreach( $answers as $answer )
					{
						?><option value="<?php echo $answer;?>" <?php selected( $answer, $type ); ?>><?php echo $answer;?></option><?php
					}?>
				</select>
				</p>
				<p style="display:none;">
					<label for="<?php print $this->get_field_id( 'directionsto' ); ?>"><?php _e( 'Display a Route by entering a address here. (If address is added Zoom will be ignored)', 'avia_framework' ); ?>:</label>
					<input class="widefat" id="<?php print $this->get_field_id( 'directionsto' ); ?>" name="<?php print $this->get_field_name( 'directionsto' ); ?>" type="text" value="<?php print $directionsto; ?>" />
				</p>
				<p>
					<label for="<?php print $this->get_field_id( 'content' ); ?>"><?php _e( 'Info Bubble Content:', 'avia_framework' ); ?></label>
					<textarea rows="7" class="widefat" id="<?php print $this->get_field_id( 'content' ); ?>" name="<?php print $this->get_field_name( 'content' ); ?>"><?php print $content; ?></textarea>
				</p>

				<div class="avia_gm_marker_icon_upload avia_google_marker_icon av-widgets-upload">
					<?php echo $html->render_single_element( $marker_icon_element );?>
				</div>
                <p>
					<label for="<?php print $this->get_field_id( 'width' ); ?>"><?php _e( 'Enter the width in px or &percnt; (100&percnt; width will be used if you leave this field empty)', 'avia_framework' ); ?>:</label>
					<input class="widefat" id="<?php print $this->get_field_id( 'width' ); ?>" name="<?php print $this->get_field_name( 'width' ); ?>" type="text" value="<?php print $width; ?>" />
                </p>
                <p>
					<label for="<?php print $this->get_field_id( 'height' ); ?>"><?php _e( 'Enter the height in px or &percnt;', 'avia_framework' ); ?>:</label>
					<input class="widefat" id="<?php print $this->get_field_id( 'height' ); ?>" name="<?php print $this->get_field_name( 'height' ); ?>" type="text" value="<?php print $height; ?>" />
                </p>
				<p>
					<label for="<?php echo $this->get_field_id( 'google_link' ); ?>"><?php _e( 'Link to Google Maps', 'avia_framework' ); ?>:</label>
					<select id="<?php echo $this->get_field_id( 'google_link' ); ?>" name="<?php echo $this->get_field_name( 'google_link' ); ?>" class="widefat avia-coditional-widget-select">
						<option value="" <?php selected( '', $google_link ) ?>><?php _e( 'Show Google Maps immediately', 'avia_framework' ); ?></option>
						<option value="confirm_link" <?php selected( 'confirm_link', $google_link ) ?>><?php _e( 'User must accept to show Google Maps', 'avia_framework' ); ?></option>
						<option value="page_only" <?php selected( 'page_only', $google_link ) ?>><?php _e( 'Only open Google Maps in new window', 'avia_framework' ); ?></option>
					</select>
				</p>

				<p class="av-confirm_link">
					<label for="<?php echo $this->get_field_id( 'confirm_button' ); ?>"><?php _e( 'Button text confirm to load Google Maps:', 'avia_framework' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'confirm_button' ); ?>" name="<?php echo $this->get_field_name( 'confirm_button' ); ?>" type="text" value="<?php echo $confirm_button; ?>" /></label>
				</p>

				<p class="av-page_only">
					<label for="<?php echo $this->get_field_id( 'page_link_text' ); ?>"><?php _e( 'Direct link to Google Maps page:', 'avia_framework' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'page_link_text' ); ?>" name="<?php echo $this->get_field_name( 'page_link_text' ); ?>" type="text" value="<?php echo $page_link_text; ?>" /></label>
				</p>

				<div class="avia_gm_fallback_upload avia_google_fallback av-widgets-upload">
					<?php echo $html->render_single_element( $fallback_element );?>
				</div>
<?php
				if( true === Av_Google_Maps()->is_loading_prohibited() )
				{
					echo '<p style="font-weight: 700;color: red;">' . __( 'This element is disabled in frontend with theme option', 'avia_framework' ) . '</p>';
				}
?>
			</div>
			<?php
		}
	}
}
