<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;


class LS_Addon_404 {

	private $type;
	private $project;
	private $page;
	private $mode;
	private $title;
	private $background;

	public function __construct() {

		$this->type 		= get_option( 'ls-404-addon-type', 'project' );
		$this->project 		= get_option( 'ls-404-addon-project', 0 );
		$this->page 		= get_option( 'ls-404-addon-page', 0 );
		$this->mode 		= get_option( 'ls-404-addon-mode', 'normal' );
		$this->title 		= get_option( 'ls-404-addon-title', '' );
		$this->background 	= get_option( 'ls-404-addon-background', '#ffffff' );

		add_action( 'template_redirect', [ $this, 'ls_template_redirect' ], 1, 0 );

		if( $this->type === 'page' && $this->mode === 'normal' ) {
			add_filter( '404_template', [ $this, 'ls_404_template' ], 999 );
		}
	}


	public function ls_template_redirect() {

		// Set 404 Not Found header after redirection
		if( $this->type === 'page' && $this->mode === 'redirect' && ! empty( $this->page ) && is_page( $this->page ) ) {
			header('HTTP/1.0 404 Not Found');
		}

		if( is_404() ) {

			// Render dynamic 404 page with LayerSlider embed
			if( $this->type === 'project' ) {
				header('HTTP/1.0 404 Not Found');
				require_once LS_ROOT_PATH.'/addons/404/assets/page-template.php';
				exit;

			// Redirect to a specific page
			} elseif( $this->type === 'page' && $this->mode === 'redirect' && ! empty( $this->page ) ) {
				if( $url = get_permalink( $this->page ) ) {
					wp_redirect( $url );
				}
				exit;
			}
		}
	}


	public function ls_404_template( $template ) {

		global $wp_query;

		if( has_filter('wpml_object_id') ) {
			$this->page = apply_filters('wpml_object_id', $this->page, 'page', true );
		}

		$wp_query = null;
		$wp_query = new WP_Query();
		$wp_query->query( 'page_id=' . $this->page );
		$wp_query->the_post();

		$template = get_page_template();

		rewind_posts();

		return $template;
	}
}


new LS_Addon_404();