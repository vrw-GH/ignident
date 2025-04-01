<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;


class LS_Addon_Maintenance {

	private $type;
	private $content;
	private $project;
	private $page;
	private $mode;
	private $title;
	private $background;

	public function __construct() {
		add_action( 'init', [ $this, 'init' ], 1, 0 );
	}


	public function init() {
		$role = get_option( 'ls-maintenance-addon-capability', 'manage_options' );
		$canManage = current_user_can( get_option( 'ls-maintenance-addon-capability', 'manage_options' ) );
		$isPreview = strpos( $_SERVER['REQUEST_URI'], 'layerslider-maintenance-preview') !== false;

		// Preview
		if( $canManage && ! $isPreview ) {
			return;
		}

		$this->type 		= get_option( 'ls-maintenance-addon-type', 'maintenance' );
		$this->content 		= get_option( 'ls-maintenance-addon-content', 'project' );
		$this->project 		= get_option( 'ls-maintenance-addon-project', 0 );
		$this->page 		= get_option( 'ls-maintenance-addon-page', 0 );
		$this->mode 		= get_option( 'ls-maintenance-addon-mode', 'normal' );
		$this->title 		= get_option( 'ls-maintenance-addon-title', '' );
		$this->background 	= get_option( 'ls-maintenance-addon-background', '#ffffff' );

		add_action( 'template_redirect', [ $this, 'ls_template_redirect' ], 1, 0 );

		if( $this->content === 'page' && $this->mode === 'normal' ) {
			status_header( $this->type === 'maintenance' ? 503 : 200 );
			add_filter( 'template_include', [ $this, 'ls_maintenance_template' ], 999 );
		}
	}


	public function ls_template_redirect() {

		// Set header after redirection
		if( $this->content === 'page' && $this->mode === 'redirect' && ! empty( $this->page ) && is_page( $this->page ) ) {
			status_header( $this->type === 'maintenance' ? 503 : 200 );

		// Render dynamic maintenance page with LayerSlider embed
		} elseif( $this->content === 'project' ) {
			status_header( $this->type === 'maintenance' ? 503 : 200 );
			require_once LS_ROOT_PATH.'/addons/maintenance/assets/page-template.php';
			exit;

		// Redirect to a specific page
		} elseif( $this->content === 'page' && $this->mode === 'redirect' && ! empty( $this->page ) ) {
			if( $url = get_permalink( $this->page ) ) {
				wp_redirect( $url );
			}
			exit;
		}
	}


	public function ls_maintenance_template( $template ) {

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

		status_header( $this->type === 'maintenance' ? 503 : 200 );

		return $template;
	}
}


new LS_Addon_Maintenance();