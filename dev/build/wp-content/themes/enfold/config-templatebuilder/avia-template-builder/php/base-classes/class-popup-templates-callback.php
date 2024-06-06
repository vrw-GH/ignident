<?php
namespace aviaBuilder\base;

use \AviaHtmlHelper;

/**
 * Base class implements modal popup templates for extended styling options that need callback handlers
 *
 * @added_by Günter
 * @since 4.8.4
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly

if( ! class_exists( __NAMESPACE__ . '\aviaPopupTemplatesCallback', false ) )
{
	class aviaPopupTemplatesCallback extends \aviaBuilder\base\aviaPopupTemplatesBase
	{
		/**
		 * @since 4.8.4
		 * @var array
		 */
		protected $border_styles_options;

		/**
		 *
		 * @since 5.1
		 * @var array
		 */
		protected $multi_input4_options;

		/**
		 *
		 * @since 5.1
		 * @var array
		 */
		protected $multi_margins_options;

		/**
		 *
		 * @since 5.1
		 * @var array
		 */
		protected $multi_padding_options;

		/**
		 * @since 4.6.4
		 */
		protected function __construct()
		{
			parent::__construct();

			$this->border_styles_options = array();
			$this->multi_input4_options = array(
												'top'		=> __( 'Top', 'avia_framework' ),
												'right'		=> __( 'Right', 'avia_framework' ),
												'bottom'	=> __( 'Bottom', 'avia_framework' ),
												'left'		=> __( 'Left', 'avia_framework' )
											);

			$this->multi_margins_options = array(
												'top'		=> __( 'Top Margin', 'avia_framework' ),
												'right'		=> __( 'Right Margin', 'avia_framework' ),
												'bottom'	=> __( 'Bottom Margin', 'avia_framework' ),
												'left'		=> __( 'Left Margin', 'avia_framework' )
											);

			$this->multi_padding_options = array(
												'top'		=> __( 'Top Padding', 'avia_framework' ),
												'right'		=> __( 'Right Padding', 'avia_framework' ),
												'bottom'	=> __( 'Bottom Padding', 'avia_framework' ),
												'left'		=> __( 'Left Padding', 'avia_framework' )
											);
		}

		/**
		 * @since 4.6.4
		 */
		public function __destruct()
		{
			parent::__destruct();

			unset( $this->border_styles_options );
			unset( $this->multi_input4_options );
			unset( $this->multi_margins_options );
			unset( $this->multi_padding_options );
		}

		/**
		 * Border Options toggle
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function border_toggle( array $element )
		{
			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'border',
							'default_check'	=> true,
							'lockable'		=> true
						),

						array(
							'type'			=> 'template',
							'template_id'	=> 'border_radius',
							'lockable'		=> true
						)
				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Border', 'avia_framework' ),
								'content'		=> $c
							),
					);

			return $template;
		}

		/**
		 * Returns a filtered array of available border styles
		 *
		 * @since 4.8.4
		 * @return array
		 */
		public function get_border_styles_options()
		{
			if( empty( $this->border_styles_options ) )
			{
				$opt = array(
							__( 'None', 'avia_framework' )			=> 'none',
							__( 'Hidden', 'avia_framework' )		=> 'hidden',
							__( 'Solid', 'avia_framework' )			=> 'solid',
							__( 'Dashed', 'avia_framework' )		=> 'dashed',
							__( 'Dotted', 'avia_framework' )		=> 'dotted',
							__( 'Double', 'avia_framework' )		=> 'double',
							__( 'Groove', 'avia_framework' )		=> 'groove',
							__( 'Ridge', 'avia_framework' )			=> 'ridge',
							__( 'Inset', 'avia_framework' )			=> 'inset',
							__( 'Outset', 'avia_framework' )		=> 'outset'
						);

				/**
				 * @since 4.8.4
				 * @param array $opt
				 * @return array
				 */
				$this->border_styles_options = (array) apply_filters( 'avf_available_border_styles_options', $opt );
			}

			return $this->border_styles_options;
		}


		/**
		 * Border Options
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function border( array $element )
		{
			$def_names = array(
						'style'		=> __( 'Border Style', 'avia_framework' ),
						'width'		=> __( 'Border Width', 'avia_framework' ),
						'color'		=> __( 'Border Color', 'avia_framework' )
					);

			$default_check = isset( $element['default_check'] ) && true === $element['default_check'];
			$id = isset( $element['id'] ) ? $element['id'] : 'border';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$names = isset( $element['names'] ) && is_array( $element['names'] ) ? $element['names'] : array();


			$names = array_merge( $def_names, $names );


			$sub_border = $this->get_border_styles_options();
			$std_border = 'none';

			if( $default_check )
			{
				$default = array(
							__( 'Theme Default', 'avia_framework' )	=> '',
						);

				$sub_border = array_merge( $default, $sub_border );
				$std_border = '';
			}

			$template = array(

					array(
							'name'		=> $names['style'],
							'desc'		=> __( 'Choose the border style for your element here', 'avia_framework' ),
							'id'		=> $id,
							'type'		=> 'select',
							'std'		=> $std_border,
							'lockable'	=> $lockable,
							'styles_cb'	=> array(
											'method'	=> 'border',
											'id'		=> $id
										),
							'required'	=> $required,
							'subtype'	=> $sub_border
						),

					array(
							'name'		=> $names['width'],
							'desc'		=> __( 'Select your border width. Leave empty for theme default setting.', 'avia_framework' ),
							'id'		=> $id . '_width',
							'type'		=> 'multi_input',
							'sync'		=> true,
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'parent_not_in_array', ',none,hidden' ),
							'multi'		=> $this->multi_input4_options
						),

					array(
							'name'		=> $names['color'],
							'desc'		=> __( 'Select the border color for this element here. Leave empty for theme default setting.', 'avia_framework' ),
							'id'		=> $id . '_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'parent_not_in_array', ',none,hidden' )
						)

				);

			return $template;
		}

		/**
		 * Border Radius option
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function border_radius( array $element )
		{
			$def_name = __( 'Border Radius', 'avia_framework' );
			$def_desc = __( 'Select your border radius(e.g. 5px). Leave empty to use theme default setting.', 'avia_framework' );

			$id = isset( $element['id'] ) ? $element['id'] : 'border_radius';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$name = isset( $element['name'] ) ? $element['name'] : $def_name;
			$desc = isset( $element['desc'] ) ? $element['desc'] : $def_desc;

			$template = array(

					array(
							'name'		=> $name,
							'desc'		=> $desc,
							'id'		=> $id,
							'type'		=> 'multi_input',
							'sync'		=> true,
							'std'		=> '',
							'lockable'	=> $lockable,
							'styles_cb'	=> array(
											'method'	=> 'border_radius',
											'id'		=> $id
										),
							'required'	=> $required,
							'multi'		=> array(
											'top'		=> __( 'Top Left', 'avia_framework' ),
											'right'		=> __( 'Top Right', 'avia_framework' ),
											'bottom'	=> __( 'Bottom Right', 'avia_framework' ),
											'left'		=> __( 'Bottom Left', 'avia_framework' )
										)
						)

				);

			return $template;
		}

		/**
		 * Padding option
		 *
		 * @since 4.8.5
		 * @param array $element
		 * @return array
		 */
		protected function padding( array $element )
		{
			$def_name = __( 'Padding', 'avia_framework' );
			$def_desc = __( 'Set the padding here. Leave empty for default value. Both pixel and &percnt; based values are accepted. eg: 30px, 5&percnt;. px is used as default unit.', 'avia_framework' );

			$id = isset( $element['id'] ) ? $element['id'] : 'padding';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$name = isset( $element['name'] ) ? $element['name'] : $def_name;
			$desc = isset( $element['desc'] ) ? $element['desc'] : $def_desc;
			$std = isset( $element['std'] ) ? $element['std'] : '';
			$multi = isset( $element['multi'] ) ? $element['multi'] : $this->multi_padding_options;

			$def_styles_cb = array(
								'method'	=> 'padding',
								'id'		=> $id
							);

			$styles_cb = isset( $element['styles_cb'] ) ? $element['styles_cb'] : $def_styles_cb;

			$template = array(

					array(
							'name'		=> $name,
							'desc'		=> $desc,
							'id'		=> $id,
							'type'		=> 'multi_input',
							'sync'		=> true,
							'std'		=> $std,
							'lockable'	=> $lockable,
							'styles_cb'	=> $styles_cb,
							'required'	=> $required,
							'multi'		=> $multi
						)

				);

			return $template;
		}

		/**
		 * Margin option
		 *
		 * @since 4.8.5
		 * @param array $element
		 * @return array
		 */
		protected function margin( array $element )
		{
			$def_name = __( 'Margin', 'avia_framework' );
			$def_desc = __( 'Set the distance from the content to other elements here. Leave empty for default value. Both pixel and &percnt; based values are accepted. eg: 30px, 5&percnt;. px is used as default unit.', 'avia_framework' );

			$id = isset( $element['id'] ) ? $element['id'] : 'margin';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$name = isset( $element['name'] ) ? $element['name'] : $def_name;
			$desc = isset( $element['desc'] ) ? $element['desc'] : $def_desc;
			$std = isset( $element['std'] ) ? $element['std'] : '';
			$multi = isset( $element['multi'] ) ? $element['multi'] : $this->multi_margins_options;

			$def_styles_cb = array(
								'method'	=> 'margin',
								'id'		=> $id
							);

			$styles_cb = isset( $element['styles_cb'] ) ? $element['styles_cb'] : $def_styles_cb;

			$template = array(

					array(
							'name'		=> $name,
							'desc'		=> $desc,
							'id'		=> $id,
							'type'		=> 'multi_input',
							'sync'		=> true,
							'std'		=> $std,
							'lockable'	=> $lockable,
							'styles_cb'	=> $styles_cb,
							'required'	=> $required,
							'multi'		=> $multi
						)

				);

			return $template;
		}

		/**
		 * Box Shadow Options toggle
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function box_shadow_toggle( array $element )
		{
			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'box_shadow',
							'default_check'	=> true,
							'lockable'		=> true
						)

			);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Box Shadow', 'avia_framework' ),
								'content'		=> $c
							),
					);

			return $template;
		}

		/**
		 * Box Shadow Options
		 *
		 * Supports
		 *	- a full option set
		 *	- a minimized option set (similar to columns)
		 *
		 * animated:	false | 'auto' | 'manually'
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function box_shadow( array $element )
		{
			$default_names = array(
								__( 'Box Shadow', 'avia_framework' ),
								__( 'Box Shadow Styling', 'avia_framework' ),
								__( 'Box Shadow Color', 'avia_framework' ),
								__( 'Box Shadow Animation', 'avia_framework' ),
								__( 'Box Shadow Width', 'avia_framework' )
							);

			$default_desc = __( 'Select to add a customized box shadow', 'avia_framework' );

			$checkbox = isset( $element['checkbox'] ) ? $element['checkbox'] : false;
			$id = isset( $element['id'] ) ? $element['id'] : 'box_shadow';
			$id2 = isset( $element['id2'] ) ? $element['id2'] : '';
			$desc = isset( $element['desc'] ) ? $element['desc'] : $default_desc;
			$names = isset( $element['names'] ) && is_array( $element['names'] ) ? $element['names'] : array();
			$default_check = isset( $element['default_check'] ) && true === $element['default_check'];
			$std_shadow = isset( $element['std_shadow'] ) ? $element['std_shadow'] : 'none';
			$animated = isset( $element['animated'] ) ? $element['animated'] : false;
			$simplified = isset( $element['simplified'] ) ? $element['simplified'] : false;
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();

			if( isset( $element['sub_shadow'] ) && is_array( $element['sub_shadow'] ) )
			{
				$sub_shadow = $element['sub_shadow'];
			}
			else
			{
				$sub_shadow = array(
								__( 'No shadow', 'avia_framework' )		=> 'none',
								__( 'Outside', 'avia_framework' )		=> 'outside',
								__( 'Inset', 'avia_framework' )			=> 'inset',
							);

				if( $default_check )
				{
					$default = array(
									__( 'Theme Default', 'avia_framework' )	=> ''
								);
					$sub_shadow = array_merge( $default, $sub_shadow );
					$std_shadow = '';
				}
			}

			if( empty( $names ) )
			{
				$names = $default_names;
			}

			$template = array();

			if( true !== $checkbox )
			{
				$template[] = array(
								'name'		=> $names[0],
								'desc'		=> $desc,
								'id'		=> $id,
								'type'		=> 'select',
								'std'		=> $std_shadow,
								'lockable'	=> $lockable,
								'styles_cb'	=> array(
													'method'		=> 'box_shadow',
													'id'			=> $id,
													'id2'			=> $id2,
													'animated'		=> $animated,
													'simplified'	=> $simplified
												),
								'required'	=> $required,
								'subtype'	=> $sub_shadow
							);
			}
			else
			{
				$template[] = array(
								'name'		=> $names[0],
								'desc'		=> $desc,
								'id'		=> $id,
								'type'		=> 'checkbox',
								'std'		=> $std_shadow,
								'styles_cb'	=> array(
													'method'		=> 'box_shadow',
													'id'			=> $id,
													'id2'			=> $id2,
													'animated'		=> $animated,
													'simplified'	=> $simplified
												),
								'lockable'	=> $lockable,
								'required'	=> $required
							);
			}

			if( true !== $simplified )
			{
				$template[] = array(
								'name'		=> $names[1],
								'desc'		=> __( 'Set the shadow styling values, you can use em or px, negative values move in opposite direction. If left empty 0 px is assumed', 'avia_framework' ),
								'id'		=> $id . '_style',
								'type'		=> 'multi_input',
	//							'sync'		=> true,
								'std'		=> '0px',
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'parent_not_in_array', ',none' ),
								'multi'		=> array(
												'offset_x'	=> __( 'Offset X-axis', 'avia_framework' ),
												'offset_y'	=> __( 'Offset Y-axis', 'avia_framework' ),
												'blur'		=> __( 'Blur-Radius', 'avia_framework' ),
												'spread'	=> __( 'Spread-Radius', 'avia_framework' )
											)
							);
			}
			else
			{
				$template[] = array(
							'name'		=> $names[4],
							'desc'		=> __( 'Set the width of the box shadow', 'avia_framework' ),
							'id'		=> $id . '_width',
							'type'		=> 'select',
							'std'		=> '10',
							'lockable'	=> true,
							'required'	=> array( $id, 'parent_not_in_array', ',none' ),
							'subtype'	=> AviaHtmlHelper::number_array( 1, 40, 1, array(), 'px' )
						);
			}

			$template[] = array(
							'name'		=> $names[2],
							'desc'		=> __( 'Select a shadow color for this element here. Leave empty for default color', 'avia_framework' ),
							'id'		=> $id . '_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'parent_not_in_array', ',none' ),
						);

			if( 'manually' === $animated )
			{
				$template[] = array(
							'name'		=> $names[3],
							'desc'		=> __( 'Select to animate the box shadow when element comes into viewport', 'avia_framework' ),
							'id'		=> $id . '_duration',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'parent_not_in_array', ',none' ),
							'subtype'	=> AviaHtmlHelper::number_array( 0.5, 5, 0.5, array( __( 'No Animation', 'avia_framework' ) => '' ), ' sec.', '', '', array( 7.5, 10, 15, 20 ) )
						);
			}

			return $template;
		}

		/**
		 * Gradient Colors Options - Simple Styling
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function gradient_colors( array $element )
		{
			$def_name = __( 'Background Gradient Direction', 'avia_framework' );
			$def_desc = __( 'Define the gradient direction for background of the element', 'avia_framework' );

			$id = isset( $element['id'] ) ? $element['id'] : 'gradient_color';
			$name = isset( $element['name'] ) ? $element['name'] : $def_name;
			$desc = isset( $element['desc'] ) ? $element['desc'] : $def_desc;
			$rgba = isset( $element['rgba'] ) && is_bool( $element['rgba'] ) ? $element['rgba'] : true;

			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$container_class = isset( $element['container_class'] ) ? $element['container_class'] : array();

			if( ! is_array( $container_class ) || empty( $container_class ) )
			{
				$container_class = array( 'av_third', 'av_third av_third_first', 'av_third', 'av_third' );
			}

			if( ! is_array( $id ) )
			{
				$id_array = array(
							$id . '_direction',
							$id . '_1',
							$id . '_2',
							$id . '_3',
						);
			}
			else
			{
				$id_array = $id;
			}

			$template = array(

					array(
							'name'		=> $name,
							'desc'		=> $desc,
							'id'		=> $id_array[0],
							'type'		=> 'select',
							'container_class' => $container_class[0],
							'std'		=> 'vertical',
							'lockable'	=> $lockable,
							'required'	=> $required,
							'styles_cb'	=> array(
												'method'	=> 'gradient_colors',
												'id'		=> $id
											),
							'subtype'	=> array(
											__( 'Top To Bottom', 'avia_framework' )				=> 'vertical',
											__( 'Bottom To Top', 'avia_framework' )				=> 'vertical_rev',
											__( 'Left To Right', 'avia_framework' )				=> 'horizontal',
											__( 'Right To Left', 'avia_framework' )				=> 'horizontal_rev',
											__( 'Left Top To Right Bottom', 'avia_framework' )	=> 'diagonal_tb',
											__( 'Right Bottom To Left Top', 'avia_framework' )	=> 'diagonal_tb_rev',
											__( 'Left Bottom To Right Top', 'avia_framework' )	=> 'diagonal_bt',
											__( 'Right Top To Left Bottom', 'avia_framework' )	=> 'diagonal_bt_rev',
											__( 'Radial Inside Outside', 'avia_framework' )		=> 'radial',
											__( 'Radial Outside Inside', 'avia_framework' )		=> 'radial_rev'
										)
						),

					array(
							'name'		=> __( 'Gradient Color 1', 'avia_framework' ),
							'desc'		=> __( 'Select the first color for the gradient. Please select the first 2 colors, otherwise this option will be ignored.', 'avia_framework' ),
							'id'		=> $id_array[1],
							'type'		=> 'colorpicker',
							'rgba'		=> $rgba,
							'std'		=> '#000000',
							'container_class' => $container_class[1],
							'lockable'	=> $lockable,
							'required'	=> $required
						),

					array(
							'name'		=> __( 'Gradient Color 2', 'avia_framework' ),
							'desc'		=> __( 'Select the second color for the gradient. Please select the first 2 colors, otherwise this option will be ignored.', 'avia_framework' ),
							'id'		=> $id_array[2],
							'type'		=> 'colorpicker',
							'rgba'		=> $rgba,
							'std'		=> '#ffffff',
							'container_class' => $container_class[2],
							'lockable'	=> $lockable,
							'required'	=> $required
						),

					array(
							'name'		=> __( 'Gradient Color 3', 'avia_framework' ),
							'desc'		=> __( 'Select an optional third color for the gradient. Leave empty if not needed.', 'avia_framework' ),
							'id'		=> $id_array[3],
							'type'		=> 'colorpicker',
							'rgba'		=> $rgba,
							'std'		=> '',
							'container_class' => $container_class[3],
							'lockable'	=> $lockable,
							'required'	=> $required
						)
				);

			if( isset( $element['hover'] ) && true === $element['hover'] )
			{
				$template[] = array(
							'name'		=> __( 'Opacity For Background On Hover', 'avia_framework' ),
							'desc'		=> __( 'When using gradient colors it is only possible to select the opacity when you hover over the button. Background colors are not supported. This setting will override any other opacity settings.', 'avia_framework' ),
							'id'		=> $id . '_opacity',
							'type'		=> 'select',
							'std'		=> '0.7',
							'lockable'	=> $lockable,
							'required'	=> $required,
							'subtype'	=> \AviaHtmlHelper::number_array( 0.0, 1.0, 0.1 )
						);
			}

			return $template;
		}


		/**
		 * Button Effects Options toggle
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function effects_toggle( array $element )
		{
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$subtypes = isset( $element['subtypes'] ) ? $element['subtypes'] : array();
			$include = isset( $element['include'] ) ? $element['include'] : array();
			$ids = isset( $element['ids'] ) ? $element['ids'] : array();
			$names = isset( $element['names'] ) ? $element['names'] : array();
			$container_class = isset( $element['container_class'] ) ? $element['container_class'] : array();

			if( empty( $include ) )
			{
				$include = array( 'sonar_effect', 'hover_opacity' );
			}

			$c = array();

			if( in_array( 'hover_opacity', $include ) )
			{
				$hover = array(
							'type'			=> 'template',
							'template_id'	=> 'hover_opacity',
							'lockable'		=> $lockable,
							'required'		=> $required
						);

				if( isset( $ids['hover_opacity'] ) && ! empty( $ids['hover_opacity'] ) )
				{
					$hover['id'] = $ids['hover_opacity'];
				}

				if( isset( $names['hover_opacity'] ) && ! empty( $names['hover_opacity'] ) )
				{
					$hover['name'] = $names['hover_opacity'];
				}

				if( isset( $container_class['hover_opacity'] ) && ! empty( $container_class['hover_opacity'] ) )
				{
					$hover['container_class'] = $container_class['hover_opacity'];
				}

				$c[] = $hover;
			}

			if( in_array( 'sonar_effect', $include ) )
			{
				$sonar = array(
							'type'			=> 'template',
							'template_id'	=> 'sonar_effect',
							'lockable'		=> $lockable,
							'required'		=> $required,
							'subtypes'		=> $subtypes
						);

				if( isset( $ids['sonar_effect'] ) && ! empty( $ids['sonar_effect'] ) )
				{
					$sonar['id'] = $ids['sonar_effect'];
				}

				if( isset( $names['sonar_effect'] ) && ! empty( $names['sonar_effect'] ) )
				{
					$sonar['name'] = $names['sonar_effect'];
				}

				if( isset( $container_class['sonar_effect'] ) && ! empty( $container_class['sonar_effect'] ) )
				{
					$sonar['container_class'] = $container_class['sonar_effect'];
				}

				$c[] = $sonar;
			}

			if( empty( $c ) )
			{
				return array();
			}

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Effects', 'avia_framework' ),
								'content'		=> $c
							),
					);

			return $template;
		}

		/**
		 * Sonar Effect Options - Simple Styling
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function sonar_effect( array $element )
		{
			$id = isset( $element['id'] ) ? $element['id'] : 'sonar_effect';
			$name = isset( $element['name'] ) ? $element['name'] : __( 'Sonar/Pulsate Effect', 'avia_framework' );
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$subtypes = isset( $element['subtypes'] ) ? $element['subtypes'] : array();
			$container_class = isset( $element['container_class'] ) ? $element['container_class'] : array();

			if( ! is_array( $container_class ) || empty( $container_class ) )
			{
				$container_class = array( '', 'av_third av_third_first', 'av_third', 'av_third', 'av_third av_third_first' );
			}

			$subtype = array(
							__( 'No effect', 'avia_framework' )	=> '',
						);

			$shadow = array(
							__( 'Shadow only - permanent', 'avia_framework' )			=> 'shadow_permanent',
							__( 'Shadow only - on hover once', 'avia_framework' )		=> 'shadow_hover_once',
							__( 'Shadow only - on hover permanent', 'avia_framework' )	=> 'shadow_hover_perm',
						);

			$pulsate = array(
							__( 'Shadow and element  - permanent', 'avia_framework' )			=> 'pulsate_permanent',
							__( 'Shadow and element- on hover once', 'avia_framework' )			=> 'pulsate_hover_once',
							__( 'Shadow and element- on hover permanent', 'avia_framework' )	=> 'pulsate_hover_perm',
						);

			$element_only = array(
							__( 'Element only - permanent', 'avia_framework' )			=> 'element_permanent',
							__( 'Element only - on hover once', 'avia_framework' )		=> 'element_hover_once',
							__( 'Element only - on hover permanent', 'avia_framework' )	=> 'element_hover_perm',
						);

			if( empty( $subtypes ) )
			{
				$subtype = array_merge( $subtype, $shadow, $pulsate, $element_only );
			}
			else
			{
				if( in_array( 'shadow', $subtypes ) )
				{
					$subtype = array_merge( $subtype, $shadow );
				}

				if( in_array( 'pulsate', $subtypes ) )
				{
					$subtype = array_merge( $subtype, $pulsate );
				}

				if( in_array( 'element', $subtypes ) )
				{
					$subtype = array_merge( $subtype, $element_only );
				}
			}

			$template = array(

					array(
							'name'		=> $name,
							'desc'		=> __( 'Select a sonar/pulsate effect for the element. This effect might not always work as expected due to layout structure(e.g. for fullwidth elements). This is not a bug.', 'avia_framework' ),
							'id'		=> $id . '_effect',
							'type'		=> 'select',
							'std'		=> '',
							'container_class' => $container_class[0],
							'lockable'	=> $lockable,
							'required'	=> $required,
							'styles_cb'	=> array(
												'method'	=> 'sonar_effect',
												'id'		=> $id
											),
							'subtype'	=> $subtype
						),

						array(
							'name'		=> __( 'Sonar Shadow Color', 'avia_framework' ),
							'desc'		=> __( 'Select the color for the sonar shadow. Leave empty for theme default.', 'avia_framework' ),
							'id'		=> $id . '_color',
							'type'		=> 'colorpicker',
							'rgba'		=> true,
							'std'		=> '',
							'container_class' => $container_class[1],
							'lockable'	=> $lockable,
							'required'	=> array( $id . '_effect', 'not', '' )
						),

						array(
							'name'		=> __( 'Sonar effect duration', 'avia_framework' ),
							'desc'		=> __( 'Select approx. length of one effect, larger value slows down', 'avia_framework' ),
							'id'		=> $id . '_duration',
							'type'		=> 'select',
							'std'		=> '1',
							'container_class' => $container_class[2],
							'subtype'	=> \AviaHtmlHelper::number_array( 0.1, 10, 0.1, array( __( 'Theme default', 'avia_framework' ) => '' ) ),
							'required' 	=> array( $id . '_effect', 'not', '' ),
						),

						array(
							'name'		=> __( 'Expand Scale', 'avia_framework' ),
							'desc'		=> __( 'Select the expand value for the sonar effect', 'avia_framework' ),
							'id'		=> $id . '_scale',
							'type'		=> 'select',
							'std'		=> '',
							'container_class' => $container_class[3],
							'subtype'	=> \AviaHtmlHelper::number_array( 1.01, 2, 0.01, array( __( 'Theme default', 'avia_framework' ) => '' ) ),
							'required' 	=> array( $id . '_effect', 'not', '' ),
						),

						array(
							'name'		=> __( 'Element Opacity', 'avia_framework' ),
							'desc'		=> __( 'Select the opacity of the element when expanding', 'avia_framework' ),
							'id'		=> $id . '_opac',
							'type'		=> 'select',
							'std'		=> '0.5',
							'container_class' => $container_class[4],
							'subtype'	=> \AviaHtmlHelper::number_array( 0.1, 1, 0.1 ),
							'required' 	=> array( $id . '_effect', 'parent_in_array', 'pulsate_permanent,pulsate_hover_once,pulsate_hover_perm,element_permanent,element_hover_once,element_hover_perm' ),
						)
				);

			return $template;
		}

		/**
		 * Slideshow Image Scale Options
		 *
		 * @since 5.0
		 * @param array $element
		 * @return array
		 */
		protected function slideshow_image_scale( array $element )
		{
			$def_extra_int = array( '15' => '15', '20' => '20', '30' => '30', '40' => '40', '60' => '60', '100' => '100' );
			$def_intervals = AviaHtmlHelper::number_array( 1, 10, 1, array(), ' sec.', '', '', $def_extra_int );

			$add_scales = AviaHtmlHelper::number_array( 110, 300, 10, array(), ' %' );

			$id = isset( $element['id'] ) ? $element['id'] : 'img_scale';
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;

			$template = array(

							array(
								'name'		=> __( 'Image Scale Starting Size', 'avia_framework' ),
								'desc'		=> __( 'You can add a scale animation to the image (enlarge or reduce the image) once the slide is visible. This is the starting size. 0&percnt; is unscaled image.', 'avia_framework' ),
								'id'		=> $id,
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'required'	=> $required,
								'styles_cb'	=> array(
												'method'	=> 'slideshow_image_scale',
												'id'		=> $id
											),
								'subtype'	=> \AviaHtmlHelper::number_array( 0, 100, 1, array( __( 'No Scaling', 'avia_framework' ) => '' ), ' %', '', '', $add_scales ),
							),

							array(
								'name'		=> __( 'Image Scale End Size', 'avia_framework' ),
								'desc'		=> __( 'Set the end size for the image.', 'avia_framework' ),
								'id'		=> $id . '_end',
								'type'		=> 'select',
								'std'		=> 10,
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'not', '' ),
								'subtype'	=> \AviaHtmlHelper::number_array( 0, 100, 1, array(), ' %', '', '', $add_scales ),
							),

							array(
								'name'		=> __( 'Scale Directions', 'avia_framework' ),
								'desc'		=> __( 'You can change the scale direction to reverse when you move to the next image. This only works when moving with arrows or swipe, not with dots when switching randomly.', 'avia_framework' ),
								'id'		=> $id . '_direction',
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'not', '' ),
								'subtype'	=> array(
												__( 'Same for all', 'avia_framework' )		=> '',
												__( 'Reverse alternate', 'avia_framework' )	=> 'reverse',
											)
							),

							array(
								'name'		=> __( 'Scale Duration', 'avia_framework' ),
								'desc'		=> __( 'Select the time to enlarge the image. This should be less than &quot;Slider Autorotation Duration&quot;', 'avia_framework' ),
								'id'		=> $id . '_duration',
								'type'		=> 'select',
								'std'		=> 3,
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'not', '' ),
								'subtype'	=> $def_intervals
							),

							array(
								'name'		=> __( 'Start Opacity', 'avia_framework' ),
								'desc'		=> __( 'Set an image opacity for the start of the scale animation.', 'avia_framework' ),
								'id'		=> $id . '_opacity',
								'type'		=> 'select',
								'std'		=> 1,
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'not', '' ),
								'subtype'	=> \AviaHtmlHelper::number_array( 0.0, 1, 0.1 ),
							)
				);

			return $template;
		}

		/**
		 * SVG Divider Toggle
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function svg_divider_toggle( array $element )
		{
			$title = isset( $element['title'] ) ? $element['title'] : __( 'SVG Dividers', 'avia_framework' );
			$id = isset( $element['id'] ) ? $element['id'] : 'svg_div';

			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();


			$c = array(

						array(
								'type'			=> 'template',
								'template_id'	=> 'svg_divider',
								'id'			=> $id . '_top',
								'lockable'		=> $lockable,
								'location'		=> 'top'
							),

						array(
								'type'		=> 'hr',
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'svg_divider',
								'id'			=> $id . '_bottom',
								'lockable'		=> $lockable,
								'location'		=> 'bottom'
							),

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> $title,
								'content'		=> $c
							),
					);

			return $template;
		}

		/**
		 * SVG Divider Options - Defines a single SVG divider
		 *
		 * @since 4.8.4
		 * @param array $element
		 * @return array
		 */
		protected function svg_divider( array $element )
		{
			$id = isset( $element['id'] ) ? $element['id'] : 'svg_div';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$location = isset( $element['location'] ) && in_array( $element['location'] , array( 'top', 'bottom' ) ) ? $element['location'] : 'bottom';

			if( 'top' == $location )
			{
				$name = __( 'Top SVG Divider', 'avia_framework' );
			}
			else
			{
				$name = __( 'Bottom SVG Divider', 'avia_framework' );
			}

			$template = array(

						array(
								'name'		=> $name,
								'desc'		=> __( 'Choose to use a svg divider here', 'avia_framework' ),
								'id'		=> $id ,
								'type'		=> 'select',
								'std'		=> '',
								'lockable'	=> $lockable,
								'styles_cb'	=> array(
												'method'	=> 'svg_divider',
												'id'		=> $id,
												'location'	=> $location
											),
								'required'	=> $required,
								'subtype'	=> AviaSvgShapes()->modal_popup_select_dividers()
							),

						array(
								'name'		=> __( 'Divider Color', 'avia_framework' ),
								'desc'		=> __( 'Select the color for this divider here. Leave empty for theme default.', 'avia_framework' ),
								'id'		=> $id . '_color',
								'type'		=> 'colorpicker',
								'rgba'		=> true,
								'std'		=> '#333333',
								'container_class'	=> 'av_half av_half_first',
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'not', '' )
							),

						array(
								'name'		=> __( 'Divider Width', 'avia_framework' ),
								'desc'		=> __( 'Select the width of the divider.', 'avia_framework' ),
								'id'		=> $id . '_width',
								'type'		=> 'select',
								'std'		=> '100',
								'container_class'	=> 'av_half',
								'subtype'	=> \AviaHtmlHelper::number_array( 0, 500, 1, array(), '%' ),
								'required'	=> AviaSvgShapes()->modal_popup_required( 'width', $id )
							),

						array(
								'name'		=> __( 'Divider Height', 'avia_framework' ),
								'desc'		=> __( 'Select the height of the divider', 'avia_framework' ),
								'id'		=> $id . '_height',
								'type'		=> 'select',
								'std'		=> '50',
//								'container_class'	=> 'av_third',
								'subtype'	=> \AviaHtmlHelper::number_array( 0, 1000, 1, array( __( 'Auto - defined by svg viewport', 'avia_framework' ) => 'auto' ), 'px' ),
								'required'	=> array( $id, 'not', '' )
							),

						array(
								'name'		=> __( 'Maximum Height', 'avia_framework' ),
								'desc'		=> __( 'Select the maximum height of the divider', 'avia_framework' ),
								'id'		=> $id . '_max_height',
								'type'		=> 'select',
								'std'		=> 'none',
//								'container_class'	=> 'av_third',
								'subtype'	=> \AviaHtmlHelper::number_array( 0, 1000, 1, array( __( 'None', 'avia_framework' ) => 'none' ), 'px' ),
								'required'	=> array( $id . '_height', 'equals', 'auto' )
							),

						array(
								'name'		=> __( 'Flip', 'avia_framework' ),
								'desc'		=> __( 'Check if you want to horizontal flip the divider.', 'avia_framework' ) ,
								'id'		=> $id . '_flip',
								'type'		=> 'checkbox',
								'std'		=> '',
								'container_class'	=> 'av_third av_third_first',
								'lockable'	=> $lockable,
								'required'	=> AviaSvgShapes()->modal_popup_required( 'flip', $id )
							),

						array(
								'name'		=> __( 'Invert', 'avia_framework' ),
								'desc'		=> __( 'Check if you want an inverted divider image.', 'avia_framework' ) ,
								'id'		=> $id . '_invert',
								'type'		=> 'checkbox',
								'std'		=> '',
								'container_class'	=> 'av_third',
								'lockable'	=> $lockable,
								'required'	=> AviaSvgShapes()->modal_popup_required( 'invert', $id )
							),

						array(
								'name'		=> __( 'Bring To Front', 'avia_framework' ),
								'desc'		=> __( 'Check if you want to bring the divider to front.', 'avia_framework' ) ,
								'id'		=> $id . '_front',
								'type'		=> 'checkbox',
								'std'		=> '',
								'container_class'	=> 'av_third',
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'not', '' )
							),

						array(
								'type'			=> 'template',
								'template_id'	=> 'opacity',
								'id'			=> $id . '_opacity',
								'container_class'	=> 'av_third  av_third_first',
								'lockable'		=> $lockable,
								'required'		=> array( $id, 'not', '' )
							),

						array(
								'name'		=> __( 'Hide Preview', 'avia_framework' ),
								'desc'		=> __( 'Check to hide the rough preview of the svg divider.', 'avia_framework' ) ,
								'id'		=> $id . '_preview',
								'type'		=> 'checkbox',
								'std'		=> '',
								'container_class'	=> 'av_third',
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'not', '' )
							),

						array(
								'name'		=> __( 'Preview Window', 'avia_framework' ),
								'id'		=> $id . '_window',
								'type'		=> 'divider_preview',
								'required'	=> array( $id . '_preview', 'equals', '' ),
								'base_id'	=> $id,
								'location'	=> $location
							)

				);

			return $template;
		}

		/**
		 * Tetblock Colum Toggle
		 *
		 * @since 4.8.8
		 * @param array $element
		 * @return array
		 */
		protected function textblock_column_toggle( array $element )
		{
			$id = isset( $element['id'] ) ? $element['id'] : 'textblock_styling';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : true;
			$required = isset( $element['required'] ) ? $element['required'] : array();

			$c = array(

						array(
							'type'			=> 'template',
							'template_id'	=> 'text_alignment',
							'id'			=> $id . '_align',
							'lockable'		=> $lockable
						),

						array(
							'name'		=> __( 'Textblock Content Styling', 'avia_framework' ),
							'desc'		=> __( 'Select if the content of the textblock shall be displayed in 1 block or float in columns.', 'avia_framework' ),
							'id'		=> $id,
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> $lockable,
							'styles_cb'	=> array(
											'method'	=> 'textblock_column_toggle',
											'id'		=> $id
										),
							'required'	=> $required,
							'subtype'	=> array(
												__( 'Single Block', 'avia_framework' )	=> '',
												__( '2 colums', 'avia_framework' )		=> '2',
												__( '3 colums', 'avia_framework' )		=> '3',
												__( '4 colums', 'avia_framework' )		=> '4',
												__( '5 colums', 'avia_framework' )		=> '5',
												__( '6 colums', 'avia_framework' )		=> '6'
											),
						),

						array(
							'name'		=> __( 'Gap between columns', 'avia_framework' ),
							'desc'		=> __( 'Define distance between columns. Allowed are px, %, em - eg. 10px, 5%, 5em. Default is %.', 'avia_framework' ),
							'id'		=> $id . '_gap',
							'type'		=> 'input',
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'not', '' )
						),

						array(
							'name'		=> __( 'Mobile Breakpoint', 'avia_framework' ),
							'desc'		=> __( 'Select mobile size when to switch back to 1 column.', 'avia_framework' ),
							'id'		=> $id . '_mobile',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'not', '' ),
							'subtype'	=> array(
												__( 'No Breakpoint', 'avia_framework' )						=> '',
												__( 'Below 989px (Tablet Landscape)', 'avia_framework' )	=> '989',
												__( 'Below 767px (Tablet Portrait)', 'avia_framework' )		=> '767',
												__( 'Below 479px (Smartphone Portrait)', 'avia_framework' )	=> '479'
											)
						)

				);

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Text Block Styling', 'avia_framework' ),
								'content'		=> $c
							),
					);

			return $template;
		}

		/**
		 * Animation Options
		 *
		 * @since 5.0
		 * @param array $element
		 * @return array
		 */
		protected function animation( array $element )
		{
			$def_desc = __( 'Set an animation for this element. The animation will be shown once the element appears first on screen. Animations only work in modern browsers.', 'avia_framework' );

			$warning = '<br /><br />' .  __( 'Due to CSS limitations &quot;Curtain Reveal Animations&quot; might not always work as expected (e.g. custom stylings, with inline elements,... ). Be selective and check the frontend.', 'avia_framework');

			$id = isset( $element['id'] ) ? $element['id'] : 'animation';
			$name = isset( $element['name'] ) ? $element['name'] : __( 'Animation', 'avia_framework' );
			$desc = isset( $element['desc'] ) ? $element['desc'] : $def_desc;
			$groups = isset( $element['groups'] ) ? $element['groups'] : array();
			$multicolor = isset( $element['multicolor'] ) ? $element['multicolor'] : false;
			$std = isset( $element['std'] ) ? $element['std'] : '';
			$std_none = isset( $element['std_none'] ) ? $element['std_none'] : '';
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;

			$animation_groups = array(
				'fade'		=> __( 'Fade Animations', 'avia_framework'),
				'slide'		=> __( 'Slide Animations', 'avia_framework' ),
				'rotate'	=> __( 'Rotate Animations', 'avia_framework' ),
				'curtain'	=> __( 'Curtain Reveal Animations', 'avia_framework' ),
				'fade-adv'	=> __( 'Advanced Fade Animations', 'avia_framework' ),
				'special'	=> __( 'Special Advanced Animations', 'avia_framework' )
			);

			$animations = array(
				'fade'		=> array(
									__( 'Fade in', 'avia_framework' )	=> 'fade-in',
									__( 'Pop up', 'avia_framework' )	=> 'pop-up'

								),
				'slide'		=> array(
									__( 'Top to Bottom', 'avia_framework' ) => 'top-to-bottom',
									__( 'Bottom to Top', 'avia_framework' ) => 'bottom-to-top',
									__( 'Left to Right', 'avia_framework' ) => 'left-to-right',
									__( 'Right to Left', 'avia_framework' ) => 'right-to-left'
								),
				'rotate'	=> array(
									__( 'Full rotation', 'avia_framework' )			=> 'av-rotateIn',
									__( 'Bottom left rotation', 'avia_framework' )	=> 'av-rotateInUpLeft',
									__( 'Bottom right rotation', 'avia_framework' )	=> 'av-rotateInUpRight'
								),
				'curtain'	=> array(
									__( 'Reveal Top to Bottom', 'avia_framework' ) => 'curtain-reveal-ttb',
									__( 'Reveal Bottom to Top', 'avia_framework' ) => 'curtain-reveal-btt',
									__( 'Reveal Left to Right', 'avia_framework' ) => 'curtain-reveal-ltr',
									__( 'Reveal Right to Left', 'avia_framework' ) => 'curtain-reveal-rtl'
								),
				'fade-adv'	=> array(
									__( 'Fade in left', 'avia_framework' )	=> 'fade-in-left',
									__( 'Fade in right', 'avia_framework' )	=> 'fade-in-right',
									__( 'Fade in down', 'avia_framework' )	=> 'fade-in-down',
									__( 'Fade in up', 'avia_framework' )	=> 'fade-in-up'
								),
				'special'	=> array(
									__( 'Flip in X', 'avia_framework' )	=> 'flip-in-x',
									__( 'Flip in Y', 'avia_framework' )	=> 'flip-in-y',
									__( 'Roll in', 'avia_framework' )	=> 'roll-in',
									__( 'Zoom in', 'avia_framework' )	=> 'zoom-in'
								)
			);

			if( empty( $groups ) )
			{
				$groups = array_keys( $animation_groups );
			}

			if( in_array( 'curtain', $groups ) )
			{
				$desc .= $warning;
			}

			$subtype = array(
							__( 'None', 'avia_framework' )	=> $std_none
						);

			foreach( $groups as $group )
			{
				if( 'default' == $group )
				{
					$subtype[ __( 'Default Animation', 'avia_framework' ) ] = 'active';
					continue;
				}

				if( ! isset( $animation_groups[ $group ] ) )
				{
					continue;
				}

				if( isset( $animations[ $group ] ) )
				{
					$subtype[ $animation_groups[ $group ] ] = $animations[ $group ];
				}
			}

			$template = array(

					array(
							'name'		=> $name,
							'desc'		=> $desc,
							'id'		=> $id,
							'type'		=> 'select',
							'std'		=> $std,
							'styles_cb'	=> array(
											'method'	=> 'animation',
											'id'		=> $id
										),
							'lockable'	=> $lockable,
							'required'	=> $required,
							'subtype'	=> $subtype
						),

						array(
							'name'		=> __( 'Animation Duration', 'avia_framework' ),
							'desc'		=> __( 'Select how long the animation should last', 'avia_framework' ),
							'id'		=> $id . '_duration',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'parent_not_in_array', "active,$std_none" ),
							'subtype'	=> AviaHtmlHelper::number_array( 0.5, 5, 0.5, array( __( 'Default (approx. 1 sec.)', 'avia_framework' ) => '' ), ' sec.', '', '', array( 7.5, 10, 15, 20 ) )
						),

						array(
							'name'		=> __( 'Curtain Background Color', 'avia_framework' ),
							'desc'		=> __( 'Select a custom background color for your curtain reveal', 'avia_framework' ),
							'id'		=> $id . '_custom_bg_color',
							'type'		=> 'colorpicker',
							'std'		=> '',
							'rgba'		=> true,
							'lockable'	=> true,
							'required'	=> array( $id, 'contains', 'curtain-reveal' )
						)
				);

			if( true === $multicolor )
			{
				$template[] = array(
									'name'		=> __( 'Curtain Reveal Background Colors List', 'avia_framework' ),
									'desc'		=> __( 'Enter the custom background colors seperated by | and/or in a new line. If number of items exceeds number of colors it will start again at beginning of the list. If empty, &quot;Curtain Background Color&quot; setting will be used for all.', 'avia_framework' ),
									'id'		=> $id . '_custom_bg_color_multi_list',
									'type'		=> 'textarea',
									'std'		=> '',
									'lockable'	=> true,
									'required'	=> array( $id, 'contains', 'curtain-reveal' )
								);
			}

			$template[] = array(
								'name'		=> __( 'Z-Index', 'avia_framework' ),
								'desc'		=> __( 'Enter the z-index for the curtain reveal of this element. Defaults to 100 if empty or not a number. Necessary when element is placed inside a column/section with a curtain reveal or is covered by some other element.', 'avia_framework' ),
								'id'		=> $id . '_z_index_curtain',
								'type'		=> 'input_number',
								'std'		=> 100,
								'lockable'	=> $lockable,
								'required'	=> array( $id, 'contains', 'curtain-reveal' ),
						);


			return $template;
		}

		/**
		 * Filter function grayscale option
		 *
		 * @since 5.1.2
		 * @param array $element
		 * @return array
		 */
		protected function filter_grayscale( array $element )
		{
			$def_name = __( 'Grayscale image', 'avia_framework' );
			$def_desc = __( 'Select a value to grayscale the image (filter grayscale()). The bigger, the stronger.', 'avia_framework' );

			$id = isset( $element['id'] ) ? $element['id'] : 'filter_grayscale';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$name = isset( $element['name'] ) ? $element['name'] : $def_name;
			$desc = isset( $element['desc'] ) ? $element['desc'] : $def_desc;
			$std = isset( $element['std'] ) ? $element['std'] : '';

			$def_styles_cb = array(
								'method'	=> 'filter_grayscale',
								'id'		=> $id
							);

			$styles_cb = isset( $element['styles_cb'] ) ? $element['styles_cb'] : $def_styles_cb;

			$template = array(

					array(
							'name'		=> $name,
							'desc'		=> $desc,
							'id'		=> $id,
							'type'		=> 'select',
							'std'		=> $std,
							'lockable'	=> $lockable,
							'styles_cb'	=> $styles_cb,
							'required'	=> $required,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 100, 1, array( __( 'Keep original', 'avia_framework' ) => '' ), ' %' )
						)

				);

			return $template;
		}



//
//-webkit-mask-image: url( http://localhost/wp56/wp-content/plugins/elementor/assets//mask-shapes/sketch.svg );
//-webkit-mask-size: contain|50%;
//-webkit-mask-position: top left;
//-webkit-mask-repeat: repeat;

		/**
		 * Mask Overlay Options
		 *
		 * @since 5.1.2
		 * @param array $element
		 * @return array
		 */
		protected function mask_overlay( array $element )
		{
			$def_images = array(
						__( 'Blob', 'avia_framework' )		=> 'blob',
						__( 'Circle', 'avia_framework' )	=> 'circle',
						__( 'Flower', 'avia_framework' )	=> 'flower',
						__( 'Hexagon', 'avia_framework' )	=> 'hexagon',
						__( 'Sketch', 'avia_framework' )	=> 'sketch',
						__( 'Triangle', 'avia_framework' )	=> 'triangle'
					);

			/**
			 * Add custom mask images to selectbox.
			 * Value must be a valid URL to the image that user may select, e.g.:
			 *
			 *		'Your Image'	=> 'https//example.com/images/your_image.svg'
			 *
			 * In case you add svg images to default folder ../avia-template-builder/assets/masks you only need to add the filename without extension.
			 *
			 * @since 5.1.2
			 * @param array $def_images
			 * @return array
			 */
			$mask_images = apply_filters( 'avf_mask_overlay_images', $def_images );

			$id = isset( $element['id'] ) ? $element['id'] : 'mask_overlay';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$toggle = isset( $element['toggle'] ) && true === $element['toggle'];

			$c = array(

					array(
							'name'		=> __( 'Mask Overlay', 'avia_framework' ),
							'desc'		=> __( 'Select to use a mask overlay effect', 'avia_framework' ),
							'id'		=> $id,
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> $lockable,
							'styles_cb'	=> array(
											'method'	=> 'mask_overlay',
											'id'		=> $id
										),
							'required'	=> $required,
							'subtype'	=> array(
											__( 'No overlay', 'avia_framework' )				=> '',
											__( 'Image overlay', 'avia_framework' )				=> 'image',
											__( 'Linear gradient overlay', 'avia_framework' )	=> 'linear_gradient',
											__( 'Radial gradient overlay', 'avia_framework' )	=> 'radial_gradient'
										)
						),

					array(
							'name'		=> __( 'Mask Shape', 'avia_framework' ),
							'desc'		=> __( 'Select the image to use as mask shape', 'avia_framework' ),
							'id'		=> $id . '_shape',
							'type'		=> 'select',
							'std'		=> 'blob',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'equals', 'image' ),
							'subtype'	=> $mask_images
						),

					array(
							'name'		=> __( 'Mask Size', 'avia_framework' ),
							'desc'		=> __( 'Select the image mask size', 'avia_framework' ),
							'id'		=> $id . '_size',
							'type'		=> 'select',
							'std'		=> 'contain',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'equals', 'image' ),
							'subtype'	=> array(
											__( 'Fit', 'avia_framework' )		=> 'contain',
											__( 'Fill', 'avia_framework' )		=> 'cover',
											__( 'Custom', 'avia_framework' )	=> 'custom'
										)
						),

					array(
							'name'		=> __( 'Mask Scale', 'avia_framework' ),
							'desc'		=> __( 'Scale the image mask. Allowed unit values are px, &percnt;, em, vw. Defaults to px.', 'avia_framework' ),
							'id'		=> $id . '_scale',
							'type'		=> 'input',
							'std'		=> '100%',
							'lockable'	=> $lockable,
							'required'	=> array( $id . '_size', 'equals', 'custom' )
						),

					array(
							'name'		=> __( 'Mask Position', 'avia_framework' ),
							'desc'		=> __( 'Select the image mask position', 'avia_framework' ),
							'id'		=> $id . '_position',
							'type'		=> 'select',
							'std'		=> 'center center',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'equals', 'image' ),
							'subtype'	=> array(
												__( 'Top Left', 'avia_framework' )		=> 'top left',
												__( 'Top Center', 'avia_framework' )	=> 'top center',
												__( 'Top Right', 'avia_framework' )		=> 'top right',
												__( 'Center Left', 'avia_framework' )	=> 'center left',
												__( 'Center Center', 'avia_framework' )	=> 'center center',
												__( 'Center Right', 'avia_framework' )	=> 'center right',
												__( 'Bottom Left', 'avia_framework' )	=> 'bottom left',
												__( 'Bottom Center', 'avia_framework' )	=> 'bottom center',
												__( 'Bottom Right', 'avia_framework' )	=> 'bottom right'
										)
						),

					array(
							'name'		=> __( 'Mask Repeat', 'avia_framework' ),
							'desc'		=> __( 'Select the image mask repeat behaviour', 'avia_framework' ),
							'id'		=> $id . '_repeat',
							'type'		=> 'select',
							'std'		=> 'no-repeat',
							'lockable'	=> $lockable,
							'required'	=> array( $id . '_size', 'not', 'cover' ),
							'subtype'	=> array(
												__( 'No Repeat', 'avia_framework' )          => 'no-repeat',
												__( 'Repeat', 'avia_framework' )             => 'repeat',
												__( 'Tile Horizontally', 'avia_framework' )  => 'repeat-x',
												__( 'Tile Vertically', 'avia_framework' )    => 'repeat-y',
												__( 'Fill with space', 'avia_framework' )	=> 'space',
												__( 'Round to fit', 'avia_framework' )		=> 'round',
										)
						),

					array(
							'name'		=> __( 'Rotation Angle Of Linear Gradient', 'avia_framework' ),
							'desc'		=> __( 'By default the linear gradient is bottom to top (0 deg). Here you can rotate the gradient.', 'avia_framework' ),
							'id'		=> $id . '_rotate',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'equals', 'linear_gradient' ),
							'subtype'	=> AviaHtmlHelper::number_array( -180, 180, 1, array( __( 'No rotation', 'avia_framework' ) => '' ), ' deg' )
						),

					array(
							'name'		=> __( 'Radial Gradient Shape', 'avia_framework' ),
							'desc'		=> __( 'Select the shape of the radial gradient.', 'avia_framework' ),
							'id'		=> $id . '_rad_shape',
							'type'		=> 'select',
							'std'		=> 'circle',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'equals', 'radial_gradient' ),
							'subtype'	=> array(
												__( 'Circle', 'avia_framework' )					=> 'circle',
												__( 'Ellipse', 'avia_framework' )					=> 'ellipse',
												__( 'Align to closest side', 'avia_framework' )		=> 'closest-side',
												__( 'Align to farthest side', 'avia_framework' )	=> 'farthest-side',
												__( 'Align to closest corner', 'avia_framework' )	=> 'closest-corner',
												__( 'Align to farthest corner', 'avia_framework' )	=> 'farthest-corner'
										)
						),

					array(
							'name'		=> __( 'Radial Gradient Position', 'avia_framework' ),
							'desc'		=> __( 'Select the center position of the radial gradient.', 'avia_framework' ),
							'id'		=> $id . '_rad_position',
							'type'		=> 'select',
							'std'		=> 'center center',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'equals', 'radial_gradient' ),
							'subtype'	=> array(
												__( 'Top Left', 'avia_framework' )		=> 'top left',
												__( 'Top Center', 'avia_framework' )	=> 'top center',
												__( 'Top Right', 'avia_framework' )		=> 'top right',
												__( 'Center Left', 'avia_framework' )	=> 'center left',
												__( 'Center Center', 'avia_framework' )	=> 'center center',
												__( 'Center Right', 'avia_framework' )	=> 'center right',
												__( 'Bottom Left', 'avia_framework' )	=> 'bottom left',
												__( 'Bottom Center', 'avia_framework' )	=> 'bottom center',
												__( 'Bottom Right', 'avia_framework' )	=> 'bottom right'
										)
						),

					array(
							'name'		=> __( 'Start Opacity', 'avia_framework' ),
							'desc'		=> __( 'Select the start opacity. 1 shows background, 0 hides it.', 'avia_framework' ),
							'id'		=> $id . '_opacity1',
							'type'		=> 'select',
							'std'		=> 0,
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'contains', 'gradient' ),
							'subtype'	=> AviaHtmlHelper::number_array( 0, 1, 0.1 )
						),

					array(
							'name'		=> __( '2nd Opacity', 'avia_framework' ),
							'desc'		=> __( 'Select the second opacity. 1 shows background, 0 hides it.', 'avia_framework' ),
							'id'		=> $id . '_opacity2',
							'type'		=> 'select',
							'std'		=> 1,
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'contains', 'gradient' ),
							'subtype'	=> AviaHtmlHelper::number_array( 0, 1, 0.1 )
						),

					array(
							'name'		=> __( '3rd Opacity', 'avia_framework' ),
							'desc'		=> __( 'Select the 3rd opacity. 1 shows background, 0 hides it.', 'avia_framework' ),
							'id'		=> $id . '_opacity3',
							'type'		=> 'select',
							'std'		=> '',
							'lockable'	=> $lockable,
							'required'	=> array( $id, 'contains', 'gradient' ),
							'subtype'	=> AviaHtmlHelper::number_array( 0, 1, 0.1, array( __( 'Not used', 'avia_framework' ) => '' ) )
						)

				);

			if( ! $toggle )
			{
				return $c;
			}

			$template = array(
							array(
								'type'			=> 'template',
								'template_id'	=> 'toggle',
								'title'			=> __( 'Mask Overlay', 'avia_framework' ),
								'content'		=> $c
							),
					);

			return $template;
		}

		/**
		 * Filter function blur option
		 *
		 * @since 5.1.2
		 * @param array $element
		 * @return array
		 */
		protected function filter_blur( array $element )
		{
			$def_name = __( 'Smoothen Image', 'avia_framework' );
			$def_desc = __( 'Select a value to smoothen the image (filter blur()). The bigger, the stronger.', 'avia_framework' );

			$id = isset( $element['id'] ) ? $element['id'] : 'filter_blur';
			$lockable = isset( $element['lockable'] ) ? $element['lockable'] : false;
			$required = isset( $element['required'] ) ? $element['required'] : array();
			$name = isset( $element['name'] ) ? $element['name'] : $def_name;
			$desc = isset( $element['desc'] ) ? $element['desc'] : $def_desc;
			$std = isset( $element['std'] ) ? $element['std'] : '';

			$def_styles_cb = array(
								'method'	=> 'filter_blur',
								'id'		=> $id
							);

			$styles_cb = isset( $element['styles_cb'] ) ? $element['styles_cb'] : $def_styles_cb;

			$template = array(

					array(
							'name'		=> $name,
							'desc'		=> $desc,
							'id'		=> $id,
							'type'		=> 'select',
							'std'		=> $std,
							'lockable'	=> $lockable,
							'styles_cb'	=> $styles_cb,
							'required'	=> $required,
							'subtype'	=> AviaHtmlHelper::number_array( 1, 10, 1, array( __( 'Keep original', 'avia_framework' ) => '' ), 'px' )
						)

				);

			return $template;
		}

	}
}
