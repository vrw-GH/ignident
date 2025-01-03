<?php
namespace aviaFramework\widgets;

/**
 * AVIA TABLE OF CONTENTS WIDGET
 *
 * Widget that displays a 'table of contents' genereated from the headlines of the page it is viewed on
 *
 * @package AviaFramework
 * @author tinabillinger
 * @since ???
 * @since 4.9			Code was moved from class-framework-widgets.php
 */
if( ! defined( 'AVIA_FW' ) ) {  exit( 'No direct script access allowed' );  }


if( ! class_exists( __NAMESPACE__ . '\avia_auto_toc', false ) )
{
	class avia_auto_toc extends \aviaFramework\widgets\base\Avia_Widget
	{
		/**
		 *
		 */
		public function __construct()
		{
			$id_base = 'avia_auto_toc';
			$name = THEMENAME . ' ' . __( 'Table of Contents', 'avia_framework' );

			$widget_options = array(
						'classname'				=> 'avia_auto_toc avia_no_block_preview',
						'description'			=> __( 'Widget that displays a table of contents genereated from the headlines of the page it is viewed on', 'avia_framework' ),
						'show_instance_in_rest' => true,
						'customize_selective_refresh' => false
					);

			parent::__construct( $id_base, $name, $widget_options );

			$this->defaults = array(
						'title'			=> '',
						'exclude'		=> '',
						'style'			=> 'elegant',
						'level'			=> 'h1',
						'single_only'	=> 1,
						'indent'		=> 1,
						'smoothscroll'	=> 1
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

			if( $instance['single_only'] && ! is_single() )
			{
				return;
			}

			$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
			$indent_class = $instance['indent'] ? ' avia-toc-indent' : '';
			$smoothscroll_class = $instance['smoothscroll'] ? ' avia-toc-smoothscroll' : '';

			echo $before_widget;

			if( ! empty( $title ) )
			{
				echo $before_title . $title . $after_title;
			};

			$exclude = '';
			if( $instance['exclude'] !== '' )
			{
				$exclude = 'data-exclude="' . $instance['exclude'] . '"';
			}

			$instance['style'] = 'elegant';

			echo '<div class="avia-toc-container avia-toc-style-' . $instance['style'] . $indent_class . $smoothscroll_class . '" data-level="' . $instance['level'] . '" ' . $exclude . '></div>';

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
			echo '<div class="avia-preview-in-front">' . __( 'Table can only be rendered in frontend.', 'avia_framework' ) . '</div>';

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

			if( ! is_array( $new_instance['level'] ) )
			{
				$new_instance['level'] = ! empty( $new_instance['level'] ) ? explode( ',', $instance['level'] ) : array( 'h1' );
			}

			$instance['title'] = trim( strip_tags( $new_instance['title'] ) );
			$instance['exclude'] = strip_tags( $new_instance['exclude'] );
//			$instance['style'] = strip_tags( $new_instance['style'] );				currently not supported
			$instance['level'] = implode( ',', $new_instance['level'] );
			$instance['single_only'] = isset( $new_instance['single_only'] ) ? 1 : 0;
			$instance['indent'] = isset( $new_instance['indent'] ) ? 1 : 0;
			$instance['smoothscroll'] = isset( $new_instance['smoothscroll'] ) ? 1 : 0;

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

			$title = sanitize_text_field( $instance['title'] );
			$single_only = isset( $instance['single_only'] ) ? (bool) $instance['single_only'] : true;
			$indent = isset( $instance['indent'] ) ? (bool) $instance['indent'] : true;
			$smoothscroll = isset( $instance['smoothscroll'] ) ? (bool) $instance['smoothscroll'] : true;

			$levels = array(
					'h1'	=> __( 'H1 Headlines', 'avia_framework' ),
					'h2'	=> __( 'H2 Headlines', 'avia_framework' ),
					'h3'	=> __( 'H3 Headlines', 'avia_framework' ),
					'h4'	=> __( 'H4 Headlines', 'avia_framework' ),
					'h5'	=> __( 'H5 Headlines', 'avia_framework' ),
					'h6'	=> __( 'H6 Headlines', 'avia_framework' )
			);

			$styles = array(
					'simple'	=> __( 'Simple', 'avia_framework' ),
					'elegant'	=> __( 'Elegant', 'avia_framework' )
			);

	        ?>
			<p>
			<label for="<?php echo $this->get_field_id( 'Title' ); ?>"><?php _e( 'Title:', 'avia_framework' ); ?>
			    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
			</p>

			<p>
			<label for="<?php echo $this->get_field_id( 'exclude' ); ?>"><?php _e( 'Exclude headlines by class:', 'avia_framework' ); ?>
			    <input class="widefat" id="<?php echo $this->get_field_id( 'exclude' ); ?>" name="<?php echo $this->get_field_name( 'exclude' ); ?>" type="text" value="<?php echo esc_attr($instance['exclude']); ?>" /></label>
			    <small>Provice a classname without a dot</small>
			</p>

			<p>
			<label for="<?php echo $this->get_field_id( 'level' ); ?>"><?php _e( 'Select headlines to include:', 'avia_framework' ); ?><br/>
			    <select class="widefat" id="<?php echo $this->get_field_id( 'level' ); ?>" name="<?php echo $this->get_field_name( 'level' ); ?>[]" multiple="multiple">
                    <?php
					$selected_levels = explode( ',', $instance['level'] );

					foreach( $levels as $k => $l )
					{
						$selected = '';
						if( in_array( $k, $selected_levels ) )
						{
							$selected = ' selected="selected"';
						}
						?>
						<option<?php echo $selected;?> value="<?php echo $k; ?>"><?php echo $l; ?></option>
						<?php
					}
                    ?>
                </select>
            </label>
			</p>

			<!--
			<p>
			<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e( 'Select a style', 'avia_framework' ); ?><br/>
			    <select class="widefat" id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
                    <?php

   			         foreach ( $styles as $sk => $sv) {

                            $selected = '';
                            if( $sk == $instance['style'] )
							{
                                $selected = ' selected="selected"';
                            }
                            ?>
                            <option<?php echo $selected;?> value="<?php echo $sk; ?>"><?php echo $sv; ?></option>
                            <?php
                        }
                    ?>
                </select>
            </label>
			</p>
			-->


            <p>
                <input class="checkbox" id="<?php echo $this->get_field_id( 'single_only' ); ?>" name="<?php echo $this->get_field_name( 'single_only' ); ?>" type="checkbox" <?php checked( $single_only ); ?> />
                <label for="<?php echo $this->get_field_id( 'single_only' ); ?>"><?php _e( 'Display on Single Blog Posts only', 'avia_framework' ); ?></label>
            </br>
                <input class="checkbox" id="<?php echo $this->get_field_id( 'indent' ); ?>" name="<?php echo $this->get_field_name( 'indent' ); ?>" type="checkbox" <?php checked( $indent ); ?> />
                <label for="<?php echo $this->get_field_id( 'indent' ); ?>"><?php _e( 'Hierarchy Indentation', 'avia_framework' ); ?></label>
            </br>
                <input class="checkbox" id="<?php echo $this->get_field_id( 'smoothscroll' ); ?>" name="<?php echo $this->get_field_name( 'smoothscroll' ); ?>" type="checkbox" <?php checked( $smoothscroll ); ?> />
                <label for="<?php echo $this->get_field_id( 'smoothscroll' ); ?>"><?php _e( 'Enable Smooth Scrolling', 'avia_framework' ); ?></label>
            </p>
		    <?php
        }
    }
}
