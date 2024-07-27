<?php
/**
 * E-Mail Spam Protection
 *
 * Shortcode which obfuscates E-Mail URL - uses WP antispambot()
 * @since 5.6.7
 */

 // Don't load directly
if( ! defined( 'ABSPATH' ) ) { exit; }


if( ! class_exists( 'av_email_spam', false ) )
{
	class av_email_spam extends aviaShortcodeTemplate
	{
		/**
		 * Create the config array for the shortcode button
		 */
		protected function shortcode_insert_button()
		{
			$this->config['self_closing']	= 'no';

			$this->config['name']			= __( 'E-Mail Spam Protect', 'avia_framework' );
			$this->config['order']			= 100;
			$this->config['shortcode']		= 'av_email_spam';
			$this->config['inline']			= true;
			$this->config['html_renderer']	= false;
			$this->config['tinyMCE']		= array(
													'tiny_only'		=> true,
													'instantInsert'	=> '[av_email_spam url="your_email@domain.com" hex_encoding="1"]readable info[/av_email_spam]'
												);

		}

		/**
		 * Frontend Shortcode Handler
		 *
		 * @since 5.6.7
		 * @param array $atts				array of attributes
		 * @param string $content			text within enclosing form of shortcode element
		 * @param string $shortcodename		the shortcode found, when == callback name
		 * @return string $output			the modified html string
		 */
		public function shortcode_handler( $atts, $content = '', $shortcodename = '', $meta = '' )
		{
			global $post, $avia_add_p;

			/**
			 * this is a fix that solves the false paragraph removal by wordpress
			 * if the av_email_spam shortcode is used at the beginning of the content of single posts/pages
			 * (see also shortcode dropcap)
			 */
			$add_p = '';
			if( isset( $post->post_content ) && strpos( $post->post_content, '[av_email_spam' ) === 0 && $avia_add_p == false && is_singular() )
			{
				$add_p = '<p>';
				$avia_add_p = true;
			}


			$default = array(
						'url'			=> '',
						'hex_encoding'	=> '1'
					);

			$default = $this->sync_sc_defaults_array( $default, 'no_modal_item', 'no_content' );

			$atts = shortcode_atts( $default, $atts, $this->config['shortcode'] );


			$e_mail = AviaHelper::split_e_mail( $atts['url'] );


			if( ! is_array( $e_mail ) )
			{
				return $content;
			}

			$atts['hex_encoding'] = ( is_numeric( $atts['hex_encoding'] ) && (int)$atts['hex_encoding'] === 1 ) ? 1 : 0;

			$e_mail['url'] = antispambot( $e_mail['url'], $atts['hex_encoding'] );

			$output  = '';
			$output .= $add_p;
			$output .=	'<a href="' . esc_url( $e_mail['mailto'] . $e_mail['url'] . $e_mail['add_info'] ) . '">' . esc_html( $content ) . '</a>';

			return $output;
		}
	}
}


