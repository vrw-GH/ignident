<?php
namespace aviaBuilder\base;

/**
 * This class implements support for media query rules that can be used in post css files.
 * Used to group and combine all media queries
 *
 *
 * @author		Günter
 * @since 4.8.8
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


if( ! class_exists( __NAMESPACE__ . '\aviaCSSMediaQueries', false ) )
{
	class aviaCSSMediaQueries
	{
		/**
		 * 'media'   =>  'screen_sizes (min;max)'   =>   'rules'
		 *
		 * @since 4.8.8
		 * @var array
		 */
		protected $media_rules;

		/**
		 * Unique element id's used in the media query
		 *
		 * @since 5.0
		 * @var array
		 */
		protected $element_ids;

		/**
		 * @since 4.8.8
		 * @var string
		 */
		protected $new_ln;

		/**
		 * @since 4.8.8
		 */
		public function __construct()
		{
			$this->reset_rules();
			$this->new_ln = "\n";
		}

		/**
		 * @since 4.8.8
		 */
		public function __destruct()
		{
			unset( $this->media_rules );
			unset( $this->element_ids );
		}

		/**
		 * Clear the rules array
		 *
		 * @since 4.8.8
		 */
		public function reset_rules()
		{
			$this->media_rules = array();
			$this->element_ids = array();
		}

		/**
		 * Add new rules to global array
		 *
		 * @since 4.8.8
		 * @since 5.0					added $element_id
		 * @param array $rules
		 * @param string $element_id
		 * @return void
		 */
		public function add_rules( array $rules = array(), $element_id = '' )
		{
			if( empty( $rules ) )
			{
				return;
			}

			foreach( $rules as $media_type => $screen_sizes )
			{
				if( empty( $screen_sizes ) )
				{
					continue;
				}

				if( ! isset( $this->media_rules[ $media_type ] ) )
				{
					$this->media_rules[ $media_type ] = array();
				}

				foreach( $screen_sizes as $screen_size => $screen_styles )
				{
					if( empty( $screen_styles ) )
					{
						continue;
					}

					if( ! isset( $this->media_rules[ $media_type ][ $screen_size ] ) )
					{
						$this->media_rules[ $media_type ][ $screen_size ] = array();
					}

					if( empty( $screen_styles ) )
					{
						continue;
					}

					if( is_array( $screen_styles ) )
					{
						$this->media_rules[ $media_type ][ $screen_size ] = array_merge( $this->media_rules[ $media_type ][ $screen_size ], $screen_styles );
					}
					else
					{
						$this->media_rules[ $media_type ][ $screen_size ][] = $screen_styles;
					}

					if( ! in_array( $element_id, $this->element_ids ) )
					{
						$this->element_ids[] = $element_id;
					}
				}
			}
		}

		/**
		 * Returns the array of element id's that added media queries
		 *
		 * @since 5.0
		 * @return array
		 */
		public function get_processed_element_ids()
		{
			return $this->element_ids;
		}

		/**
		 * Sort screen sizes in reverse order and create output string
		 *
		 * @since 4.8.8
		 * @return string
		 */
		public function get_rules()
		{
			$out = '';

			if( empty( $this->media_rules ) )
			{
				return $out;
			}

			foreach( $this->media_rules as $media_type => $screen_sizes )
			{
				if( empty( $screen_sizes ) )
				{
					continue;
				}

				if( count( $screen_sizes ) > 1 )
				{
					//	sort descending screen sizes
					uksort( $screen_sizes, array( $this, 'compare_screen_sizes' ) );
				}

				foreach( $screen_sizes as $sorted_size => $screen_styles )
				{
					if( empty( $screen_styles ) )
					{
						continue;
					}

					$rules = implode( $this->new_ln, $screen_styles );
					$sizes = explode( ';', $sorted_size );

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
					$out .= "{$this->new_ln}@media only {$media_type}{$min}{$max}{ {$this->new_ln}{$rules}}{$this->new_ln}";

				}
			}

			return $out;
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

			//	set a high value if only min used
			if( 0 === $size1[1] )
			{
				$size1[1] = 999999;
			}
			if( 0 === $size2[1] )
			{
				$size2[1] = 999999;
			}

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
}
