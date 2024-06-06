<?php
namespace aviaBuilder\base;
use \AviaHelper;

/**
 * This base class implements extended styling support. It contains the methods necessary to store and handle access.
 *
 * Adds support to generate <style> tags, style rules and class strings for a single shortcode element.
 *
 * @author		Günter
 * @since 4.8.4
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( __NAMESPACE__ . '\aviaElementStylingBase', false ) )
{
	abstract class aviaElementStylingBase
	{
		/**
		 * Shortcode we need to create inline styles
		 *
		 * @since 4.8.4
		 * @var aviaShortcodeTemplate
		 */
		protected $shortcode;

		/**
		 *
		 * @since 4.8.4
		 * @var boolean
		 */
		protected $is_modal_item;

		/**
		 * Elements that are needed to create styles (depending on $is_modal_item)
		 *
		 * @since 4.8.4
		 * @var array
		 */
		protected $elements;

		/**
		 * Unique element id normally built from args of shortcode before creating this object
		 *
		 * @var string
		 */
		protected $element_id;

		/**
		 * Stores already combined keyframes, styles and classes for callback option elements, id of element is main key
		 * (e.g. 'box-shadow' => '5px 4px 3px red;' ):
		 *
		 *		'$id' => array(
		 *					'keyframes'	=> array ( 'value', ... )
		 *					'styles'	=> array ( 'attr' => 'value' )
		 *					'classes'	=> array ( 'value', ... )
		 *					'data'		=> array ( 'attr' => 'value' )
		 *					'media'		=> array ( 'screen|print|,,,'  => array (  'min; max'  =>  array ( 'attr' => 'value' ) ) )
		 *				)
		 *
		 * @since 4.8.4
		 * @var array
		 */
		protected $callback_settings;

		/**
		 * Stores styles for seperate containers:
		 *
		 *		'container' => array( 'attr' => 'value' )
		 *
		 *
		 * @since 4.8.4
		 * @var array
		 */
		protected $container_styles;

		/**
		 * Stores classes for seperate containers:
		 *
		 *		'container' => array( 'value', ... )
		 *
		 *
		 * @since 4.8.4
		 * @var array
		 */
		protected $container_classes;

		/**
		 * Stores data attributes for seperate containers:
		 *
		 *		'container' => array( 'attr' => 'value', ... )
		 *
		 * @since 5.0
		 * @var array
		 */
		protected $data_atts;

		/**
		 * Stores the media queries and containers
		 *
		 *		'media type' =>  array ( 'min; max' =>  array (  'container' => array( 'value', ... )  )  )
		 *
		 * @since 4.8.8
		 * @var array
		 */
		protected $media_queries;

		/**
		 * Array of selectors for styles
		 *
		 *		'selector' => array( 'container_id' )
		 *
		 * @since 4.8.4
		 * @var array
		 */
		protected $style_selectors;

		/**
		 * Array of subitem stylings (mainly used to add stylings from a secondary query like in sliders)
		 *
		 *		'element_id'	=> array(
		 *								'element_id'		=> string
		 *								'elementStyling'	=> (\aviaElementStyling) elementStyling
		 *							)
		 *
		 * @since 4.8.8.1
		 * @var array
		 */
		protected $subitem_stylings;

		/**
		 * @since 4.8.4
		 * @var string
		 */
		protected $new_ln;

		/**
		 *
		 * @since 4.8.4
		 * @param \aviaShortcodeTemplate|null $shortcode
		 * @param string $element_id
		 */
		protected function __construct( \aviaShortcodeTemplate $shortcode = null, $element_id = '' )
		{
			$this->shortcode = $shortcode;
			$this->element_id = $element_id;

			$this->is_modal_item = false;
			$this->elements = array();

			$this->callback_settings = array();
			$this->container_styles = array();
			$this->container_classes = array();
			$this->data_atts = array();
			$this->media_queries = array();
			$this->style_selectors = array();
			$this->subitem_stylings = array();

			$this->new_ln = "\n";
		}

		/**
		 * @since 4.8.4
		 */
		public function __destruct()
		{
			unset( $this->shortcode );
			unset( $this->elements );
			unset( $this->callback_settings );
			unset( $this->container_styles );
			unset( $this->container_classes );
			unset( $this->data_atts );
			unset( $this->media_queries );
			unset( $this->style_selectors );
			unset( $this->subitem_stylings );
		}

		/**
		 * Scans the element for attributes that have a callback and executes to create them for the shortcode
		 *
		 * @since 4.8.4
		 * @param array $atts
		 * @param boolean $is_modal_item
		 */
		public function create_callback_styles( array &$atts, $is_modal_item = false )
		{
			$this->set_elements( $is_modal_item );

			foreach( $this->elements as &$element )
			{
				if( ! isset( $element['styles_cb'] ) || ! is_array( $element['styles_cb'] ) || ! isset( $element['styles_cb']['method'] ) )
				{
					continue;
				}

				if( ! method_exists( $this, $element['styles_cb']['method'] ) )
				{
					continue;
				}

				call_user_func( array( $this, $element['styles_cb']['method'] ), $element, $atts );
			}
		}

		/**
		 * Adds a styles array to the corresponding container styles.
		 * If $skip_empty != 'skip_empty' empty strings are also added.
		 *
		 * @since 4.8.4
		 * @param string $container
		 * @param array $styles
		 * @param false|string $skip_empty				'skip_empty' | 'no_skip_empty' | false
		 */
		public function add_styles( $container, array $styles, $skip_empty = 'skip_empty' )
		{
			if( ! isset( $this->container_styles[ $container ] ) )
			{
				$this->container_styles[ $container ] = array();
			}

			if( 'skip_empty' == $skip_empty )
			{
				$styles = array_filter( $styles, function( $value ) { return ( ! is_null( $value ) && $value !== '' ); } );
			}

			if( empty( $styles ) )
			{
				return;
			}

			$this->container_styles[ $container ] = array_merge( $this->container_styles[ $container ], $styles );
		}

		/**
		 * Add rules to callback settings styles
		 *
		 * @since 5.1
		 * @param string $id
		 * @param array $rules
		 */
		public function add_callback_settings_style( $id, array $rules = array() )
		{
			if( empty( $rules ) )
			{
				return;
			}

			//	ensure to have an empty structure we can rely on later
			if( ! isset( $this->callback_settings[ $id ]['styles'] ) )
			{
				$this->callback_settings[ $id ]['styles'] = array();
			}

			$this->callback_settings[ $id ]['styles'] = array_merge( $this->callback_settings[ $id ]['styles'], $rules );
		}

		/**
		 * Adds styles if a container has styles already
		 *
		 * @since 4.8.4
		 * @param type $container_id
		 * @param array $styles
		 */
		public function add_styles_conditionally( $container_id, array $styles )
		{
			if( $this->has_styles( $container_id ) )
			{
				$this->add_styles( $container_id, $styles );
			}
		}

		/**
		 * Adds a single class or a class array to the corresponding container array.
		 *
		 * @since 4.8.4
		 * @param string $container
		 * @param string|array $classes
		 */
		public function add_classes( $container, $classes )
		{
			if( ! isset( $this->container_classes[ $container ] ) )
			{
				$this->container_classes[ $container ] = array();
			}

			if( ! is_array( $classes ) )
			{
				$classes = array( $classes );
			}

			$filtered = array_filter( $classes );

			if( empty( $filtered ) )
			{
				return;
			}

			$this->container_classes[ $container ] = array_merge( $this->container_classes[ $container ], $filtered );
		}

		/**
		 * Adds data attributes array to the corresponding container array.
		 *
		 * @since 5.0
		 * @param string $container
		 * @param array $attributes
		 */
		public function add_data_attributes( $container, array $attributes = array() )
		{
			if( ! isset( $this->data_atts[ $container ] ) )
			{
				$this->data_atts[ $container ] = array();
			}

			$this->data_atts[ $container ] = array_merge( $this->data_atts[ $container ], $attributes );
		}

		/**
		 * Checks if key exists in $source and adds value to $container
		 *
		 * @since 4.8.4
		 * @param string $container
		 * @param array $source
		 * @param string|array $key
		 */
		public function add_classes_from_array( $container, array $source, $key )
		{
			if( empty( $key ) )
			{
				return;
			}

			if( ! is_array( $key ) )
			{
				$key = array( $key );
			}

			$add = array();

			foreach( $key as $index )
			{
				if( isset( $source[ $index ] ) && ! empty( $source[ $index ] ) )
				{
					$add[] = $source[ $index ];

				}
			}

			if( ! empty( $add ) )
			{
				$this->add_classes( $container, $add );
			}
		}

		/**
		 * Returns the class string for a given container
		 *
		 * @since 4.8.4
		 * @param string $container
		 * @return string
		 */
		public function get_class_string( $container )
		{
			if( ! isset( $this->container_classes[ $container ] ) || ! is_array( $this->container_classes[ $container ] ) )
			{
				return '';
			}

			return implode( ' ', $this->container_classes[ $container ] );
		}

		/**
		 * Return a HTML data attribute string
		 *
		 * @since 5.0
		 * @param string $container
		 * @return string
		 */
		public function get_data_attributes_string( $container )
		{
			if( ! isset( $this->data_atts[ $container ] ) || ! is_array( $this->data_atts[ $container ] ) )
			{
				return '';
			}

			$out = array();

			foreach( $this->data_atts[ $container ] as $key => $value )
			{
				$out[] = "data-{$key}=\"" . esc_attr( $value ) . '"';
			}

			return implode( ' ', $out );
		}

		/**
		 * Return a HTML data attribute string with attributes as json object packed in $data_key
		 *
		 * @since 5.0
		 * @param string $container
		 * @param string $data_key
		 * @return string
		 */
		public function get_data_attributes_json_string( $container, $data_key )
		{
			if( ! isset( $this->data_atts[ $container ] ) || ! is_array( $this->data_atts[ $container ] ) || empty( $this->data_atts[ $container ] ) )
			{
				return '';
			}

			$out = "data-{$data_key}=\"" . esc_attr( json_encode( $this->data_atts[ $container ] ) ) . '"';

			return $out;
		}



		/**
		 * Add containers and the selectors to style_selectors array.
		 * Selectors and containers must be unique and valid array keys.
		 * Containers are added to already existing selectors.
		 *
		 * @since 4.8.4
		 * @param array $selectors
		 */
		public function add_selectors( array $selectors )
		{
			$sel = array_flip( $selectors );

			foreach( $sel as $selector => $container )
			{
				if( ! isset( $this->style_selectors[ $selector ] ) )
				{
					$this->style_selectors[ $selector ] = array();
				}

				if( ! is_array( $container ) )
				{
					$container = array( $container );
				}

				$this->style_selectors[ $selector ] = array_merge( $this->style_selectors[ $selector ], $container );
			}
		}

		/**
		 * Adds callback styles to a style container
		 *
		 * @since 4.8.4
		 * @param string $container
		 * @param array $callback_id
		 * @return int
		 */
		public function add_callback_styles( $container, array $callback_id )
		{
			return $this->add_callback_data( $container, 'styles', $callback_id );
		}

		/**
		 * Adds callback classes to a class container
		 *
		 * @since 4.8.4
		 * @param string $container
		 * @param array $callback_id
		 * @return int
		 */
		public function add_callback_classes( $container, array $callback_id )
		{
			return $this->add_callback_data( $container, 'classes', $callback_id );
		}

		/**
		 * Adds callback data to a data container
		 *
		 * @since 5.0
		 * @param string $container
		 * @param array $callback_id
		 * @return int
		 */
		public function add_callback_data_attributes( $container, array $callback_id )
		{
			return $this->add_callback_data( $container, 'data', $callback_id );
		}

		/**
		 * Add the callback media queries to container media queries
		 *
		 * @since 4.8.8
		 * @param string $container
		 * @param array $callback_ids
		 */
		public function add_callback_media_queries( $container, array $callback_ids )
		{
			if( empty( $callback_ids ) )
			{
				return;
			}

			foreach( $callback_ids as $id )
			{
				if( ! isset( $this->callback_settings[ $id ] ) || empty( $this->callback_settings[ $id ]['media'] ) )
				{
					continue;
				}

				$media_array = $this->callback_settings[ $id ]['media'];
				$this->add_media_queries( $container, $media_array );
			}

		}

		/**
		 * Adds new media queries to existing
		 *
		 * @since 4.8.8
		 * @param string $container
		 * @param array $media_array
		 */
		public function add_media_queries( $container, array $media_array = array() )
		{
			if( empty( $media_array ) )
			{
				return;
			}

			foreach( $media_array as $media_type => $screen_widths )
			{
				if( empty( $screen_widths ) )
				{
					continue;
				}

				if( ! isset( $this->media_queries[ $media_type ] ) )
				{
					$this->media_queries[ $media_type ] = array();
				}

				foreach( $screen_widths as $widths => $rules )
				{
					if( empty( $rules ) )
					{
						continue;
					}

					if( ! isset( $this->media_queries[ $media_type ][ $widths ] ) )
					{
						$this->media_queries[ $media_type ][ $widths ] = array();
					}

					if( ! isset( $this->media_queries[ $media_type ][ $widths ][ $container ] ) )
					{
						$this->media_queries[ $media_type ][ $widths ][ $container ] = array();
					}

					$this->media_queries[ $media_type ][ $widths ][ $container ] = array_merge( $this->media_queries[ $media_type ][ $widths ][ $container ], $rules );

				}
			}
		}

		/**
		 * Adds callback data to a container.
		 * Returns number of entries added.
		 *
		 * @since 4.8.4
		 * @param string $container
		 * @param string $source				'styles' | 'classes'
		 * @param array $callback_id
		 * @return int
		 */
		protected function add_callback_data( $container, $source, array $callback_id )
		{
			if( empty( $callback_id ) )
			{
				return 0;
			}

			if( ! in_array( $source, array( 'styles' , 'classes', 'data' ) ) )
			{
				return 0;
			}

			$data = $this->get_callback_data_array( $source, $callback_id );

			if( 'styles' == $source )
			{
				$count = count( $data );
				$this->add_styles( $container, $data );
			}
			else if( 'classes' == $source )
			{
				$count = 1;
				$this->add_classes( $container, $data );
			}
			else
			{
				$count = count( $data );
				$this->add_data_attributes( $container, $data );
			}

			return $count;
		}


		/**
		 * Returns the stored styles or classes array for the id.
		 * Structure and content depends on the element type
		 *
		 * @since 4.8.4
		 * @param string $id
		 * @param string $what				'styles' | 'classes'
		 * @return array
		 */
		public function get_callback_settings( $id, $what = 'styles' )
		{
			if( ! isset( $this->callback_settings[ $id ] ) )
			{
				return array();
			}

			return isset( $this->callback_settings[ $id ][ $what ] ) ? $this->callback_settings[ $id ][ $what ] : array();
		}

		/**
		 * Extracts the styles or classes for the given id's and returns them in an array
		 *
		 * @since 4.8.4
		 * @param string $source				'styles' | 'classes'
		 * @param string|array $ids
		 * @return array
		 */
		public function get_callback_data_array( $source, ...$ids )
		{
			$attr = array();

			foreach ( $ids as $id )
			{
				if( is_array( $id ) )
				{
					$a = array();

					foreach( $id as $id_sub )
					{
						$sub = $this->get_callback_data_array( $source, $id_sub );
						$a = array_merge( $a, $sub );
					}

					$attr = array_merge( $attr, $a );
					continue;
				}

				$data = $this->get_callback_settings( $id, $source );

				foreach( $data as $key => $value )
				{
					if( in_array( $source, array( 'styles', 'data' ) ) )
					{
						$attr[ $key ] = $value;
					}
					else
					{
						$attr[] = $value;
					}
				}
			}

			return $attr;
		}

		/**
		 * Adds an subitem element styling to the queue
		 *
		 * @since 4.8.8.1
		 * @param string $element_id
		 * @param \aviaElementStyling $element_styling
		 */
		public function add_subitem_styling( $element_id, \aviaElementStyling $element_styling )
		{
			$this->subitem_stylings[] = compact( 'element_id', 'element_styling' );
		}

		/**
		 * Returns the styling object for an element id
		 *
		 * @since 4.8.8.1
		 * @param string $element_id
		 * @return boolean|\aviaElementStyling
		 */
		public function get_subitem_styling( $element_id )
		{
			foreach( $this->subitem_stylings as $stylings )
			{
				if( $stylings['element_id'] == $element_id )
				{
					return $stylings['element_styling'];
				}
			}

			return false;
		}

		/**
		 * Returns info about the subitem accessed by index
		 *
		 * @since 4.8.8.1
		 * @param int $index
		 * @return boolean
		 */
		public function get_subitem_styling_info( $index )
		{
			if( ! isset( $this->subitem_stylings[ $index ] ) )
			{
				return false;
			}

			return $this->subitem_stylings[ $index ];
		}

		/**
		 * Returns a final style string to add to HTML. Accepts several style arrays and merges them to a single string
		 *
		 * @since 4.8.4
		 * @param array ...$styles_arrays
		 * @return string
		 */
		public function inline_style_string( array ...$styles_arrays )
		{
			$styles = '';

			foreach( $styles_arrays as $styles_array )
			{
				foreach( $styles_array as $key => $value )
				{
					$styles .= AviaHelper::style_string( $styles_array, $key );
				}
			}

			if( ! empty( $styles ) )
			{
				//	wrap in style=""
				$styles = AviaHelper::style_string( $styles );
			}

			return $styles;
		}

		/**
		 * Returns the complete <style> tag including all selectors added with add_selectors
		 *
		 * @since 4.8.4
		 * @param string $element_id
		 *  @param string $return				'tag' | 'rules_only'
		 * @return string
		 */
		public function get_style_tag( $element_id, $return = 'tag' )
		{
			if( AviaPostCss()->shortcode_styles_processed( $element_id ) )
			{
				return '';
			}

			return $this->create_style_tag( $this->style_selectors, $element_id, $return );
		}

		/**
		 * Returns the complete styling rules including all selectors added with add_selectors
		 *
		 * Attention: see also create_style_tag() to adjust output for inline styles
		 * ==========
		 *
		 * @since 4.8.4
		 * @param string $destination									'file' | 'modal_preview'
		 * @param \aviaBuilder\base\aviaCSSMediaQueries $media_rules
		 * @return string
		 */
		public function get_style_rules( $destination, \aviaBuilder\base\aviaCSSMediaQueries $media_rules )
		{
			$rules = $this->create_keyframe_rules();
			$rules .= $this->create_style_rules( $this->style_selectors );

			if( ! Avia_Builder()->in_text_to_preview_mode() )
			{
				if( 'file' == $destination )
				{
					$global_rules = $this->create_media_queries_rules( $this->style_selectors, 'global_array' );
					$media_rules->add_rules( $global_rules, $this->element_id );
				}
				else
				{
					$rules .= $this->create_media_queries_rules( $this->style_selectors );
				}
			}

			if( ! empty( $this->subitem_stylings ) )
			{
				foreach( $this->subitem_stylings as $styling )
				{
					$rules .= $styling['element_styling']->get_style_tag( $styling['element_id'], 'rules_only' );
				}
			}

			return $rules;
		}


		/**
		 * Returns the rules for a given selectors array.
		 * Rules containing '' are removed.
		 *
		 * @since 4.8.4
		 * @since 4.8.8					added $container_styles
		 * @param array $selectors
		 * @param array|null $container_styles
		 * @return string
		 */
		public function create_style_rules( $selectors = array(), $container_styles = null )
		{
			if( is_null( $container_styles ) || ! is_array( $container_styles ) )
			{
				$container_styles = $this->container_styles;
			}

			$out = '';

			if( empty( $selectors ) )
			{
				return $out;
			}

			foreach( $selectors as $selector => $container_ids )
			{
				$styles = array();
				$rules = array();

				if( ! is_array( $container_ids ) )
				{
					$container_ids = array( $container_ids );
				}

				foreach( $container_ids as $container_id )
				{
					if( ! isset( $container_styles[ $container_id ] ) )
					{
						continue;
					}

					$rules = array_merge( $rules, $container_styles[ $container_id ] );
				}

				if( empty( $rules ) )
				{
					continue;
				}

				foreach( $rules as $key => $value )
				{
					$r = trim( AviaHelper::style_string( $rules, $key ) );
					if( '' != $r )
					{
						$styles[] = $r;
					}
				}

				if( empty( $styles ) )
				{
					continue;
				}

				$out .= $selector . '{' . $this->new_ln . implode( $this->new_ln, $styles ) . $this->new_ln . '}' . $this->new_ln;
			}

			return $out;
		}

		/**
		 * Returns the rules for a given selectors array.
		 * Rules containing '' are removed.
		 *
		 * @since 4.8.8
		 * @param array $selectors
		 * @param string $result				'string' | 'global_array'
		 * @param string $element_id
		 * @return string
		 */
		public function create_media_queries_rules( $selectors = array(), $result = 'string' )
		{
			$out = $result != 'global_array' ? '' : array();

			if( empty( $selectors ) || empty( $this->media_queries ) )
			{
				return $out;
			}

			foreach( $this->media_queries as $media_type => $screen_sizes )
			{
				foreach( $screen_sizes as $screen_size => $container_styles )
				{
					if( empty( $container_styles ) )
					{
						continue;
					}

					$rules = $this->create_style_rules( $selectors, $container_styles );

					if( empty( $rules ) )
					{
						continue;
					}

					$sizes = explode( ';', $screen_size );

					/**
					 * Use 0 to skip rule
					 *
					 *		min;max   or
					 *		max
					 */
					if( count( $sizes ) == 1 )
					{
						$sizes[1] = $sizes[0];
						$sizes[0] = '0';
					}

					$sizes[0] = (int) $sizes[0];
					$sizes[1] = (int) $sizes[1];

					$min = ! empty( $sizes[0] ) ? " and (min-width: {$sizes[0]}px)" : '';
					$max = ! empty( $sizes[1] ) ? " and (max-width: {$sizes[1]}px)" : '';

					if( empty( $min ) && empty( $max ) )
					{
						continue;
					}

					if( 'global_array' == $result )
					{
						$new_size = "{$sizes[0]};{$sizes[1]}";

						if( ! isset( $out[ $media_type ] ) )
						{
							$out[ $media_type ] = array();
						}

						if( ! isset( $out[ $media_type ][ $new_size ] ) )
						{
							$out[ $media_type ][ $new_size ] = array();
						}

						$out[ $media_type ][ $new_size ][] = $rules;
					}
					else
					{
						//	@media only screen and (min-width: 768px) and (max-width: 989px) {  $rules  }
						$out .= "{$this->new_ln}@media only {$media_type}{$min}{$max}{ {$this->new_ln}{$rules}}{$this->new_ln}";
					}
				}
			}

			return $out;
		}

		/**
		 * Returns all the keyframes
		 *
		 * @since 4.8.4
		 * @return string
		 */
		public function create_keyframe_rules()
		{
			$rules = '';

			foreach( $this->callback_settings as $setting )
			{
				$keyframes = \AviaHelper::array_value( $setting, 'keyframes', array() );
				if( empty( $keyframes ) )
				{
					continue;
				}

				$rules .= implode( $this->new_ln, $keyframes ) . $this->new_ln;
			}

			return $rules;
		}

		/**
		 * Returns the complete <style> tag a given selector array
		 *
		 * Attention: see also get_style_rules() to adjust output for css file
		 * ==========
		 *
		 * @since 4.8.4
		 * @param array $selectors
		 * @param string $tag_id
		 * @param string $return				'tag' | 'rules_only'
		 * @return string
		 */
		public function create_style_tag( $selectors = array(), $tag_id = '', $return = 'tag' )
		{
			$rules = $this->create_keyframe_rules();
			$rules .= $this->create_style_rules( $selectors );

			if( ! Avia_Builder()->in_text_to_preview_mode() )
			{
				$rules .= $this->create_media_queries_rules( $selectors );
			}

			if( ! empty( $this->subitem_stylings ) )
			{
				foreach( $this->subitem_stylings as $styling )
				{
					$rules .= $styling['element_styling']->get_style_tag( $styling['element_id'], 'rules_only' );
				}
			}

			if( empty( $rules ) )
			{
				return '';
			}

			if( 'rules_only' == $return )
			{
				return $rules;
			}

			return $this->style_tag_html( $rules, $tag_id );
		}

		/**
		 * Creates the <style> html for given rule string
		 *
		 * @since 4.8.4
		 * @param string $rules
		 * @param string $tag_id
		 * @return string
		 */
		public function style_tag_html( $rules, $tag_id = '' )
		{
			if( empty( $rules ) )
			{
				return '';
			}

			$id = ! empty( $tag_id ) ? 'id="style-css-' . $tag_id . '"' : '';
			$out = $this->new_ln . '<style type="text/css" data-created_by="avia_inline_auto" ' . $id . '>'. $this->new_ln . $rules . '</style>' . $this->new_ln;

			return $out;
		}

		/**
		 * Filter the elements to scan
		 *
		 * @since 4.8.4
		 * @param boolean $is_modal_item
		 */
		protected function set_elements( $is_modal_item = false )
		{
			$this->is_modal_item = $is_modal_item;

			if( ! $this->shortcode instanceof \aviaShortcodeTemplate )
			{
				$this->elements = array();
			}
			else
			{
				$this->elements = ! $is_modal_item ? $this->shortcode->elements : $this->shortcode->get_modal_group_subelements();
			}
		}

		/**
		 * Checks if style exist for a given container
		 *
		 *
		 * @param string $container_id
		 * @return boolean
		 */
		public function has_styles( $container_id )
		{
			return isset( $this->container_styles[ $container_id ] ) && ! empty( $this->container_styles[ $container_id ] );
		}

	}
}
