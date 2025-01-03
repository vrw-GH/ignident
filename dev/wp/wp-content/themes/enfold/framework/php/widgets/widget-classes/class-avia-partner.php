<?php
namespace aviaFramework\widgets;

/**
 * AVIA PARTNER WIDGET
 *
 * An advertising widget that displays 2 images with 125 x 125 px in size
 *
 * @package AviaFramework
 * @since ???
 * @since 4.9			Code was moved from class-framework-widgets.php
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }


if( ! class_exists( __NAMESPACE__ . '\avia_partner_widget', false ) )
{
	class avia_partner_widget extends \aviaFramework\widgets\base\Avia_Widget
	{
		/**
		 * @since ????
		 * @var int
		 */
		protected $add_cont;

		/**
		 * @since 4.9						added parameters $id_base, ... $control_options
		 * @param string $id_base
		 * @param string $name
		 * @param array $widget_options
		 * @param array $control_options
		 */
		public function __construct( $id_base = '', $name = '', $widget_options = array(), $control_options = array() )
		{
			$this->add_cont = 2;

			if( empty( $id_base ) )
			{
				$id_base = 'avia_partner_widget';
			}

			if( empty( $name ) )
			{
				$name = THEMENAME . ' ' . __( 'Advertising Area', 'avia_framework' );
			}

			if( empty( $widget_options ) )
			{
				$widget_options = array(
							'classname'				=> 'avia_partner_widget',
							'description'			=> __( 'An advertising widget that displays 2 images with 125 x 125 px in size', 'avia_framework' ),
							'show_instance_in_rest' => true,
							'customize_selective_refresh' => false
						);
			}

			parent::__construct( $id_base, $name, $widget_options, $control_options );

			$this->defaults = array(
						'title'			=> '',
						'image_url'		=> '',
						'ref_url'		=> '',
						'image_url2'	=> '',
						'ref_url2'		=> ''
					);
		}

		/**
		 * Output the widget in frontend
		 *
		 * @param type $args
		 * @param type $instance
		 */
		public function widget( $args, $instance )
		{
			global $kriesiaddwidget, $firsttitle;

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



			extract( $args, EXTR_SKIP );

			echo $before_widget;

			$kriesiaddwidget ++;

			$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
			$image_url = empty( $instance['image_url'] ) ? '<span class="avia_parnter_empty"><span>' . __( 'Advertise here', 'avia_framework' ) . '</span></span>' : '<img class="rounded" src="' . $instance['image_url'] . '" title="' . $title . '" alt="' . $title . '"/>';
			$ref_url = empty( $instance['ref_url'] ) ? '#' : apply_filters( 'widget_comments_title', $instance['ref_url'] );
			$image_url2 = empty( $instance['image_url2'] ) ? '<span class="avia_parnter_empty"><span>' . __( 'Advertise here', 'avia_framework' ) . '</span></span>' : '<img class="rounded" src="' . $instance['image_url2'] . '" title="' . $title . '" alt="' . $title . '"/>';
			$ref_url2 = empty( $instance['ref_url2'] ) ? '#' : apply_filters( 'widget_comments_title', $instance['ref_url2'] );

			if ( ! empty( $title ) )
			{
				echo $before_title . $title . $after_title;
			}

			echo avia_targeted_link_rel( '<a target="_blank" href="' . $ref_url . '" class="preloading_background avia_partner1 link_list_item' . $kriesiaddwidget . ' ' . $firsttitle . '" >' . $image_url . '</a>' );

			if( $this->add_cont == 2 )
			{
				echo avia_targeted_link_rel( '<a target="_blank" href="' . $ref_url2 . '" class="preloading_background avia_partner2 link_list_item' . $kriesiaddwidget . ' ' . $firsttitle . '" >' . $image_url2 . '</a>' );
			}

			echo $after_widget;

			if( $title == '' )
			{
				$firsttitle = 'no_top_margin';
			}
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

			extract( $instance );
	?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'avia_framework' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'image_url' ); ?>"><?php _e( 'Image URL:', 'avia_framework' ); ?> <?php if($this->add_cont == 2) echo "(125px * 125px):"; ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'image_url' ); ?>" name="<?php echo $this->get_field_name( 'image_url' ); ?>" type="text" value="<?php echo esc_attr( $image_url ); ?>" /></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'ref_url' ); ?>"><?php _e( 'Referal URL:', 'avia_framework' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'ref_url' ); ?>" name="<?php echo $this->get_field_name( 'ref_url' ); ?>" type="text" value="<?php echo esc_attr( $ref_url ); ?>" /></label>
			</p>
			<?php

			if( $this->add_cont == 2 )
			{
				?>
				<p>
					<label for="<?php echo $this->get_field_id( 'image_url2' ); ?>"><?php _e( 'Image URL 2: (125px * 125px):', 'avia_framework' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'image_url2' ); ?>" name="<?php echo $this->get_field_name( 'image_url2' ); ?>" type="text" value="<?php echo esc_attr( $image_url2 ); ?>" /></label>
				</p>

				<p>
					<label for="<?php echo $this->get_field_id( 'ref_url2' ); ?>"><?php _e( 'Referal URL 2:', 'avia_framework' ); ?>
					<input class="widefat" id="<?php echo $this->get_field_id( 'ref_url2' ); ?>" name="<?php echo $this->get_field_name( 'ref_url2' ); ?>" type="text" value="<?php echo esc_attr( $ref_url2 ); ?>" /></label>
				</p>
				<?php
			}
		}
	}
}
