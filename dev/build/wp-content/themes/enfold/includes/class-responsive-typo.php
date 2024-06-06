<?php
namespace enfold\styling;
/*
 * ATTENTION: Changes to this file will only be visible in your frontend after you have re-saved your Themes Styling Page
 * ==========
 *
 *
 * Add responsive typographie stylings
 *
 * @since 4.8.8
 * @added_by Günter
 */

if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( 'ResponsiveTypo', false ) )
{
	class ResponsiveTypo
	{
		/**
		 * Holds the instance of this class
		 *
		 * @since 4.8.8
		 * @var ResponsiveTypo
		 */
		static private $_instance = null;

		/**
		 *
		 * @since 4.8.8
		 * @var array
		 */
		protected $admin_values;

		/**
		 * Containes sizes for media queries
		 *
		 * @since 4.8.8
		 * @var array
		 */
		private $media_sizes;

		/**
		 * Containes rules for media queries
		 *
		 *		'min;max'  =>  array ( selectors  =>  array( values ) )
		 *
		 * @since 4.8.8
		 * @var array
		 */
		private $media_rules;

		/**
		 * @since 4.8.8
		 * @var string
		 */
		private $new_ln;

		/**
		 * Return the instance of this class
		 *
		 * @since 4.8.8
		 * @return aviaPostCssManagement
		 */
		static public function instance()
		{
			if( is_null( ResponsiveTypo::$_instance ) )
			{
				ResponsiveTypo::$_instance = new ResponsiveTypo();
			}

			return ResponsiveTypo::$_instance;
		}

		/**
		 * @since 4.8.8
		 */
		protected function __construct()
		{
			global $avia_admin_values;

			$this->admin_values = $avia_admin_values;

			/**
			 * @since 4.8.8
			 * @param array $limits
			 * @return array
			 */
			$this->media_sizes = apply_filters( 'avf_responsive_media_sizes', array(
									'av-medium'	=> array( 768, 989 ),
									'av-small'	=> array( 480, 767 ),
									'av-mini'	=> array( 0, 479 )
							) );

			$this->media_rules = array();
			$this->new_ln = "\n";
		}

		/**
		 * @since 4.8.8
		 */
		public function __destruct()
		{
			unset( $this->admin_values );
			unset( $this->media_sizes );
			unset( $this->media_rules );
		}

		/**
		 * Return CSS rules for given array
		 *
		 * @since 4.8.8
		 * @param array $typos
		 * @return string
		 */
		public function create_css_rules( array $typos )
		{
			$this->media_rules = array();
			$css = '';

			//	loop through all typo elements
			foreach( $this->admin_values['customize-typo-elements'] as $typo_element => $base_opt_key )
			{
				$typo_el_rules = $this->admin_values['customize-typo-elements-rules'][ $typo_element ];

				//	loop through all css rules
				foreach( $typo_el_rules as $rule )
				{
					//	loop through all media sizes
					foreach( $this->admin_values['responsive-sizes'] as $desc => $media )
					{
						$media_key = empty( $media ) ? '' : "-{$media}";

						if( 'content-font' == $typo_element )
						{
							$id = "{$base_opt_key}{$media_key}";
						}
						else
						{
							$id = "{$rule}-{$base_opt_key}{$media_key}";
						}

						if( ! isset( $typos[ $id ] ) || '' == trim( $typos[ $id ] ) )
						{
							continue;
						}

						if( empty( $media ) )
						{
							$query_key = '0;0';
						}
						else
						{
							$boundary = isset( $this->media_sizes[ "av-{$media}" ] ) ? $this->media_sizes[ "av-{$media}" ] : array( 0, 0 );
							$query_key = implode( ';', $boundary );
						}

						switch( $typo_element )
						{
							case 'content-font':
								$selector = 'body, body .avia-tooltip';
								break;
							case 'h1':
							case 'h2':
							case 'h3':
							case 'h4':
							case 'h5':
							case 'h6':
								$selector = $typo_element;
								break;
							default:
								$selector = '';
								break;
						}

						if( empty( $selector ) )
						{
							continue;
						}

						if( ! isset( $this->media_rules[ $query_key ] ) )
						{
							$this->media_rules[ $query_key ] = array();
						}

						if( ! isset( $this->media_rules[ $query_key ][ $selector ] ) )
						{
							$this->media_rules[ $query_key ][ $selector ] = array();
						}

						$this->media_rules[ $query_key ][ $selector ][] = "{$rule}: {$typos[ $id ]};";
					}
				}
			}

			if( isset( $this->media_rules[ '0;0' ] ) )
			{
				if( ! empty( $this->media_rules[ '0;0' ] ) )
				{
					foreach( $this->media_rules[ '0;0' ] as $selector => $values )
					{
						$css .= $this->new_ln . "{$selector}{" . $this->new_ln . implode( $this->new_ln,  $values ) . $this->new_ln . '}' . $this->new_ln;
					}
				}

				unset( $this->media_rules[ '0;0' ] );
			}

			if( count( $this->media_rules ) > 1 )
			{
				//	sort descending screen sizes
				uksort( $this->media_rules, array( $this, 'compare_screen_sizes' ) );
			}

			foreach( $this->media_rules as $sorted_size => $selectors )
			{
				if( empty( $selectors ) )
				{
					continue;
				}

				$sizes = explode( ';', $sorted_size );
				$rules = '';

				foreach( $selectors as $selector => $values )
				{
					$rules .= $this->new_ln . "{$selector}{" . $this->new_ln . implode( $this->new_ln,  $values ) . $this->new_ln . '}' . $this->new_ln;
				}

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

				//	@media only screen and (min-width: 768px) and (max-width: 989px) {  $rules  }
				$css .= "{$this->new_ln}@media only screen{$min}{$max}{ {$this->new_ln}{$rules}}{$this->new_ln}";
			}

			return $css;
		}

		/**
		 * Return CSS variables for given array
		 *
		 * @since 5.2
		 * @param array $typos
		 * @return string
		 */
		public function create_css_vars( array $typos )
		{
			$css = '';

			//	loop through all typo elements
			foreach( $this->admin_values['customize-typo-elements'] as $typo_element => $base_opt_key )
			{
				$typo_el_rules = $this->admin_values['customize-typo-elements-rules'][ $typo_element ];

				//	loop through all css rules
				foreach( $typo_el_rules as $rule )
				{
					//	loop through all media sizes
					foreach( $this->admin_values['responsive-sizes'] as $desc => $media )
					{
						$media_key = empty( $media ) ? '' : "-{$media}";
						$var_key = "{$rule}{$media_key}";

						if( 'content-font' == $typo_element )
						{
							$id = "{$base_opt_key}{$media_key}";
						}
						else
						{
							$id = "{$rule}-{$base_opt_key}{$media_key}";
						}

						if( ! isset( $typos[ $id ] ) || '' == trim( $typos[ $id ] ) )
						{
							continue;
						}

						switch( $typo_element )
						{
							case 'content-font':
							case 'h1':
							case 'h2':
							case 'h3':
							case 'h4':
							case 'h5':
							case 'h6':
								$var_key .= "-{$typo_element}";
								break;
							default:
								break;
						}

						$css .= "--enfold-{$var_key}: {$typos[ $id ]};\n";
					}
				}
			}

			return $css;
		}

		/**
		 * Sort in descending order
		 *
		 * @since 4.8.8
		 * @param string $index1
		 * @param string $index2
		 * @return int
		 */
		protected function compare_screen_sizes( $index1, $index2 )
		{
			$size1 = explode( ';', $index1 );
			$size2 = explode( ';', $index2 );

			/**
			 * Use 0 to skip rule
			 *
			 *		min;max   or
			 *		max
			 */
			if( count( $size1 ) == 1 )
			{
				$size1[1] = $size1[0];
				$size1[0] = 0;
			}

			$size1[0] = (int) $size1[0];
			$size1[1] = (int) $size1[1];

			if( count( $size2 ) == 1 )
			{
				$size2[1] = $size2[0];
				$size2[0] = 0;
			}

			$size2[0] = (int) $size2[0];
			$size2[1] = (int) $size2[1];


			//	descending for max width
			if( $size1[1] != $size2[1] )
			{
				return $size1[1] < $size2[1] ? 1 : -1;
			}

			//	ascending for min width as smaller limited sizes are stronger rules
			if( $size1[0] != $size2[0] )
			{
				return $size1[0] < $size2[0] ? -1 : 1;
			}

			return 0;
		}
	}

	/**
	 * Returns the main instance of aviaPostCssManagement to prevent the need to use globals
	 *
	 * @since 4.8.8
	 * @return ResponsiveTypo
	 */
	function Responsive_Typo()
	{
		return ResponsiveTypo::instance();
	}

}
