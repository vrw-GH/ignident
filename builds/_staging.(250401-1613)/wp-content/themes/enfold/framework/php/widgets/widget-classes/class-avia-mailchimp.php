<?php
namespace aviaFramework\widgets;

/**
 * AVIA Mailchimp WIDGET
 *
 * Widget that displays a Mailchimp newsletter signup form
 *
 * @package AviaFramework
 * @since ???
 * @since 4.9			Code was moved from functions-enfold.php
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }


if( ! class_exists( __NAMESPACE__ . '\avia_mailchimp_widget', false ) )
{
	class avia_mailchimp_widget extends \aviaFramework\widgets\base\Avia_Widget
	{
		/**
		 *
		 */
		public function __construct()
		{
			$id_base = 'avia_mailchimp_widget';
			$name = THEMENAME . ' ' . __( 'Mailchimp Newsletter Signup', 'avia_framework' );

			$widget_options = array(
							'classname'				=> 'avia_mailchimp_widget avia_no_block_preview',
							'description'			=> __( 'A widget that displays a Mailchimp newsletter signup form', 'avia_framework' ),
							'show_instance_in_rest' => true,
							'customize_selective_refresh' => false
						);

			parent::__construct( $id_base, $name, $widget_options  );

			$this->defaults = array(
								'title' 			=> __( 'Newsletter', 'avia_framework' ),
								'mailchimp_list' 	=> '',
								'styling' 			=> '' ,
								'double_optin' 		=> 'true',
								'success' 			=> __( 'Thank you for subscribing to our newsletter!', 'avia_framework' ),
								'submit_label' 		=> __( 'Subscribe', 'avia_framework' ),
							);
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

			extract( $args, EXTR_SKIP );

			echo $before_widget;

			if( ! empty( $instance['title'] ) )
			{
				echo $before_title . $instance['title'] . $after_title;
			};

			$shortcode  = '[av_mailchimp';
			$shortcode .= " list='{$instance['mailchimp_list']}'";
			$shortcode .= " listonly='true'";
			$shortcode .= " hide_labels='true'";
			$shortcode .= " double_opt_in='{$instance['double_optin']}'";
			$shortcode .= " sent='{$instance['success']}'";
			$shortcode .= " button='{$instance['submit_label']}'";

			$shortcode .= ']';


			echo "<div class='av-mailchimp-widget av-mailchimp-widget-style-{$instance['styling']}'>";
			echo	do_shortcode( $shortcode );
			echo "</div>";

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
			echo '<div class="avia-preview-info">' . __( 'Mailchimp List:', 'avia_framework' ) . ' ' . $instance['mailchimp_list'] .  '</div>';
			echo '<div class="avia-preview-in-front">' . __( 'Content is only rendered in frontend.', 'avia_framework' ) . '</div>';

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

			foreach( $new_instance as $key => $value )
			{
				$instance[ $key ] = strip_tags( $value );
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

			foreach( $instance as $key => $value )
			{
				$instance[ $key ] = strip_tags( $value );
			}

			extract( $instance, EXTR_SKIP );

			$lists = get_option( 'av_chimplist' );
			$newlist = array( 'Select a Mailchimp list...' => '' );

			if( empty( $lists ) )
			{
				return;
			}

			foreach( $lists as $key => $list_item )
			{
				$newlist[ $list_item['name'] ] = $key;
			}
			$lists = $newlist;

	?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'avia_framework' );?>:
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'mailchimp_list' ); ?>"><?php _e( 'Mailchimp list to subscribe to', 'avia_framework' );?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'mailchimp_list' ); ?>" name="<?php echo $this->get_field_name( 'mailchimp_list' ); ?>">
					<?php
					$list = '';

					foreach ( $lists as $answer => $key )
					{
						$selected = '';
						if( $key == $mailchimp_list )
						{
							$selected = 'selected="selected"';
						}

						$list .= "<option {$selected} value='{$key}'>{$answer}</option>";
					}

					echo $list;
					?>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'styling' ); ?>"><?php _e( 'Signup Form Styling', 'avia_framework' );?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'styling' ); ?>" name="<?php echo $this->get_field_name( 'styling' ); ?>">
					<?php
					$answers = array(
									__( 'Default', 'avia_framework' )	=> '',
									__( 'Boxed', 'avia_framework' )		=> 'boxed_form',
								);

					$list = '';

					foreach( $answers as $answer => $key )
					{
						$selected = '';
						if( $key == $styling )
						{
							$selected = 'selected="selected"';
						}

						$list .= "<option {$selected} value='{$key}'>{$answer}</option>";
					}

					echo $list;
					?>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'double_optin' ); ?>"><?php _e( 'Activate double opt-in?', 'avia_framework' );?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'double_optin' ); ?>" name="<?php echo $this->get_field_name( 'double_optin' ); ?>">
					<?php
					$answers = array(
									__( 'Yes', 'avia_framework' )	=> 'true',
									__( 'No', 'avia_framework' )	=> '',
								);

					$list = '';

					foreach ($answers as $answer => $key)
					{
						$selected = '';
						if( $key == $double_optin )
						{
							$selected = 'selected="selected"';
						}

						$list .= "<option {$selected} value='{$key}'>{$answer}</option>";
					}

					echo $list;
					?>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'success' ); ?>"><?php _e( 'Message if user subscribes successfully', 'avia_framework' );?>:
				<input class="widefat" id="<?php echo $this->get_field_id( 'success' ); ?>" name="<?php echo $this->get_field_name( 'success' ); ?>" type="text" value="<?php echo esc_attr($success); ?>" /></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'submit_label' ); ?>"><?php _e( 'Submit Button Label', 'avia_framework' );?>:
				<input class="widefat" id="<?php echo $this->get_field_id( 'submit_label' ); ?>" name="<?php echo $this->get_field_name( 'submit_label' ); ?>" type="text" value="<?php echo esc_attr($submit_label); ?>" /></label>
			</p>
<?php
		}
	}
}

