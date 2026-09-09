<?php

// Prevent direct file access
defined( 'LS_ROOT_FILE' ) || exit;

add_action('init', function() {

	add_action('save_post', 'layerslider_delete_caches');

	if(current_user_can(get_option('layerslider_custom_capability', 'manage_options'))) {

		// Handle large uploads where the post content length exceeds post_max_size
		if( $_SERVER['REQUEST_METHOD'] === 'POST' && strpos( $_SERVER['REQUEST_URI'], 'page=layerslider' ) !== false ) {

			$maxStr = @ini_get('post_max_size');
			$limitBytes = ! empty( $maxStr ) ? ls_string_to_bytes( $maxStr ) : 0;

			$bytes = isset( $_SERVER['CONTENT_LENGTH'] ) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
			$isChunked = ( isset( $_SERVER['HTTP_TRANSFER_ENCODING'] ) && stripos($_SERVER['HTTP_TRANSFER_ENCODING'], 'chunked') !== false );

			if( $limitBytes > 0 && ! $isChunked && $bytes > $limitBytes && empty( $_POST ) && empty( $_FILES ) ) {
				wp_redirect( admin_url('admin.php?page=layerslider&message=uploadErrorSize&error') );
				exit;
			}
		}

		// Preview iframe contents
		if( ! empty( $_GET['page'] ) && $_GET['page'] === 'layerslider' && ! empty( $_GET['action'] ) && $_GET['action'] === 'preview-iframe-html') {
			include LS_ROOT_PATH.'/templates/tmpl-project-preview-iframe.php';
			exit;
		}


		// Set Locale
		if( ! empty( $_GET['page'] ) && $_GET['page'] === 'layerslider' && ! empty( $_GET['action'] ) && $_GET['action'] === 'set-locale') {

			if( ! empty( $_GET['locale'] ) ) {
				update_option('ls_custom_locale', $_GET['locale']);
			}

			if( ! empty( $_GET['id'] ) ) {
				wp_redirect( admin_url('admin.php?page=layerslider&action=edit&id='.(int) $_GET['id'] ) );
				exit;
			}

			wp_redirect( admin_url('admin.php?page=layerslider') );
			exit;
		}


		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'create-slider') {
			if( check_admin_referer('create-slider') ) {
				ls_add_new_slider();
			}
		}

		// Hide slider
		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'hide') {
			if( check_admin_referer('hide_'.$_GET['id']) ) {
				add_action('admin_init', 'layerslider_hideslider');
			}
		}

		// Restore slider
		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'restore') {
			if( check_admin_referer('restore_'.$_GET['id']) ) {
				add_action('admin_init', 'layerslider_restoreslider');
			}
		}

		// Duplicate slider
		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'duplicate') {
			if( check_admin_referer('duplicate_'.$_GET['id']) ) {
				add_action('admin_init', 'layerslider_duplicateslider');
			}
		}

		// Export slider
		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'export') {
			if( check_admin_referer('export-sliders') ) {
				$_POST['sliders'] = [ (int) $_GET['id'] ];
				$_POST['ls-export'] = true;
			}
		}

		// Empty caches
		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'empty_caches') {
			if( check_admin_referer('empty_caches') ) {
				add_action('admin_init', 'layerslider_empty_caches');
			}
		}

		// Empty Google Fonts
		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'empty_google_fonts') {
			if( check_admin_referer('empty_google_fonts') ) {
				add_action('admin_init', 'layerslider_empty_google_fonts');
			}
		}

		// Update Library
		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'update_store') {
			if( check_admin_referer('update_store') ) {
				LS_RemoteData::update();
				wp_redirect( admin_url('admin.php?page=layerslider&message=updateStore') );
				exit;
			}
		}

		// Database Update
		if( isset( $_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'database_update') {
			if( check_admin_referer('database_update') ) {
				layerslider_create_db_table();
				wp_redirect( admin_url('admin.php?page=layerslider&section=system-status&message=dbUpdateSuccess') );
				exit;
			}
		}


		// Clear Groups
		if( isset( $_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'clear_groups') {
			if( check_admin_referer('clear_groups') ) {
				LS_Sliders::removeAllGroups();
				wp_redirect( admin_url('admin.php?page=layerslider&section=system-status&message=clearGroupsSuccess') );
				exit;
			}
		}

		// Use recommended settings
		if( isset( $_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'use_recommended_settings') {
			if( check_admin_referer('use_recommended_settings') ) {
				ls_use_recommended_settings();
				wp_redirect( admin_url('admin.php?page=layerslider&section=system-status&message=revertToRecommendedSettings') );
				exit;
			}
		}


		if( isset( $_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'check_updates') {
			if( check_admin_referer('check_updates') ) {
				ls_force_update_check();
			}

		}


		// Slider list bulk actions
		if(isset($_POST['ls-bulk-action'])) {
			if( check_admin_referer('bulk-action') ) {
				add_action('admin_init', 'ls_sliders_bulk_action');
			}
		}

		// Add new slider
		if(isset($_POST['ls-add-new-slider'])) {
			if( check_admin_referer('add-slider') ) {
				add_action('admin_init', 'ls_add_new_slider');
			}
		}

		// Import sliders
		if(isset($_POST['ls-import'])) {
			if( check_admin_referer('import-sliders') ) {
				add_action('admin_init', 'ls_import_sliders');
			}
		}

		// Export sliders
		if(isset($_POST['ls-export'])) {
			if( check_admin_referer('export-sliders') ) {
				add_action('admin_init', 'ls_export_sliders');
			}
		}

		// Custom CSS editor
		if(isset($_POST['ls-user-css'])) {
			if( check_admin_referer('save-user-css') ) {
				add_action('admin_init', 'ls_save_user_css');
			}
		}

		// Skin editor
		if(isset($_POST['ls-user-skins'])) {
			if( check_admin_referer('save-user-skin') ) {
				add_action('admin_init', 'ls_save_user_skin');
			}
		}

		// Transition builder
		if(isset($_POST['ls-user-transitions'])) {
			if( check_admin_referer('save-user-transitions') ) {
				add_action('admin_init', 'ls_save_user_transitions');
			}
		}


		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'hide-important-notice') {
			if( check_admin_referer('hide-important-notice') ) {

				$noticeData = LS_RemoteData::get('important-notice', false );
				if( ! empty( $noticeData['date'] ) ) {
					update_option('ls-last-important-notice', $noticeData['date']);
				}

				wp_redirect( admin_url( 'admin.php?page=layerslider') );
				exit;
			}
		}

		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'hide-support-notice') {
			if( check_admin_referer('hide-support-notice') ) {
				update_user_meta( get_current_user_id(), 'ls-show-support-notice-timestamp', time() );
				wp_redirect( admin_url( 'admin.php?page=layerslider') );
				exit;
			}
		}

		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'hide-canceled-activation-notice') {
			if( check_admin_referer('hide-canceled-activation-notice') ) {
				update_option('ls-show-canceled_activation_notice', 0);
				wp_redirect( admin_url( 'admin.php?page=layerslider') );
				exit;
			}
		}

		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'hide-update-notice') {
			if( check_admin_referer('hide-update-notice') ) {
				$latest = get_option('ls-latest-version', LS_PLUGIN_VERSION);
				update_option('ls-last-update-notification', $latest);
				wp_redirect( admin_url( 'admin.php?page=layerslider') );
				exit;
			}
		}


		if(isset($_GET['page']) && $_GET['page'] == 'layerslider' && isset($_GET['action']) && $_GET['action'] == 'disable_dt_silence') {

			if( class_exists( 'The7_Admin_Dashboard_Settings' ) ) {
				The7_Admin_Dashboard_Settings::set( 'silence-purchase-notification', false );
			}

			wp_redirect( admin_url( 'admin.php?page=layerslider') );
			exit;

		}


		// Erase Plugin Data
		if( isset( $_POST['ls-erase-plugin-data'] ) ) {
			if( check_admin_referer('erase_data') ) {
				add_action('admin_init', 'ls_erase_plugin_data');
			}
		}

		// AJAX functions
		add_action('wp_ajax_ls_save_google_fonts', 'ls_save_google_fonts');
		add_action('wp_ajax_ls_save_plugin_settings', 'ls_save_plugin_settings');
		add_action('wp_ajax_ls_save_slider', 'ls_save_slider');
		add_action('wp_ajax_ls_rename_project', 'ls_rename_project');
		add_action('wp_ajax_ls_publish_slider', 'ls_publish_slider');
		add_action('wp_ajax_ls_revert_slider', 'ls_revert_slider');
		add_action('wp_ajax_ls_import_bundled', 'ls_import_bundled');
		add_action('wp_ajax_ls_import_online', 'ls_import_online');
		add_action('wp_ajax_ls_save_pagination_limit', 'ls_save_pagination_limit');
		add_action('wp_ajax_ls_save_editor_settings', 'ls_save_editor_settings');
		add_action('wp_ajax_ls_get_slider_details', 'ls_get_slider_details');
		add_action('wp_ajax_ls_get_mce_sliders', 'ls_get_mce_sliders');
		add_action('wp_ajax_ls_get_mce_slides', 'ls_get_mce_slides');
		add_action('wp_ajax_ls_layer_action_popups', 'ls_layer_action_popups');
		add_action('wp_ajax_ls_layer_action_scene_projects', 'ls_layer_action_scene_projects');
		add_action('wp_ajax_ls_get_post_details', 'ls_get_post_details');
		add_action('wp_ajax_lse_get_search_posts', 'lse_get_search_posts');
		add_action('wp_ajax_ls_get_taxonomies', 'ls_get_taxonomies');
		add_action('wp_ajax_ls_upload_from_url', 'ls_upload_from_url');
		add_action('wp_ajax_ls_store_opened', 'ls_store_opened');
		add_action('wp_ajax_ls_addons_opened', 'ls_addons_opened');
		add_action('wp_ajax_ls_create_slider_group', 'ls_create_slider_group');
		add_action('wp_ajax_ls_add_slider_to_group', 'ls_add_slider_to_group');
		add_action('wp_ajax_ls_rename_slider_group', 'ls_rename_slider_group');
		add_action('wp_ajax_ls_remove_slider_from_group', 'ls_remove_slider_from_group');
		add_action('wp_ajax_ls_delete_slider_group', 'ls_delete_slider_group');
		add_action('wp_ajax_ls_download_module', 'ls_download_module');
		add_action('wp_ajax_ls_get_revisions', 'ls_get_revisions');
		add_action('wp_ajax_ls_save_revisions_options', 'ls_save_revisions_options');
		add_action('wp_ajax_ls_delete_revisions', 'ls_delete_revisions');
		add_action('wp_ajax_ls_save_transition_presets', 'ls_save_transition_presets');
		add_action('wp_ajax_ls_download_object', 'ls_download_object');
		add_action('wp_ajax_ls_assets_remote_download', 'ls_assets_remote_download');
		add_action('wp_ajax_ls_assets_remote_search', 'ls_assets_remote_search');
		add_action('wp_ajax_ls_upload_lottie_file', 'ls_upload_lottie_file');
		add_action('wp_ajax_ls_get_lottie_uploads', 'ls_get_lottie_uploads');
		add_action('wp_ajax_ls_fetch_external_lottie', 'ls_fetch_external_lottie');
	}

	// ADMIN PUBLIC AJAX FUNCTIONS
	add_action('wp_ajax_ls_slider_library_contents', 'ls_slider_library_contents');

	// POPUP FUNCTIONS
	add_action( 'wp_ajax_ls_get_popup_markup', 'ls_get_popup_markup' );
	add_action( 'wp_ajax_nopriv_ls_get_popup_markup', 'ls_get_popup_markup' );
});

function ls_save_transition_presets() {

	if( ! wp_verify_nonce( $_POST['nonce'], 'ls-editor-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	include LS_ROOT_PATH . '/classes/class.ls.transitionpresets.php';

	$data 	= stripslashes( $_POST['data'] );
	$result = LS_TransitionPresets::save( $data );

	die( json_encode( ['success' => $result ] ) );
}

function ls_get_popup_markup() {

	$id 	= is_numeric( $_GET['id'] ) ? (int) $_GET['id'] : (string) $_GET['id'];
	$popup 	= LS_Sliders::find( $id );

	if( $popup ) {

		// The "wp_enqueue_scripts" hook does not run in AJAX calls, so
		// we need to execute this function manually in order to get
		// scripts and style info we might depend on.
		layerslider_enqueue_content_res();

		// Marker to override opening trigger and timing, plus we use it
		// to supply and load plugin dependecies in init code.
		$GLOBALS['lsAjaxOverridePopupSettings'] = true;

		// Get generated HTML markup and necessarry parts
		$parts 	= LS_Shortcode::generateSliderMarkup( $popup );

		// Extra fonts we need to load (ie Font Awesome 4 icon font)
		if( ! empty( $parts['fonts'] ) ) {
			foreach( $parts['fonts'] as $item ) {
				wp_print_styles( ['ls-'.$item] );
			}
		}

		echo $parts['container'];
		echo $parts['markup'];
		echo '<script>'.$parts['init'].'</script>';
	}

	die();
}

function ls_use_recommended_settings() {

	$options = [
		'use_cache',
		'clear_3rd_party_caches',
		'admin_no_conflict_mode',
		'gsap_sandboxing'
	];

	foreach( $options as $option ) {
		delete_option( 'ls_' . $option );
	}
}


function ls_force_update_check() {
	LS_RemoteData::update('general');
	delete_site_transient('update_plugins');
	wp_redirect( admin_url( 'update-core.php' ) );
	exit;
}


function ls_download_module() {

	if( ! wp_verify_nonce( $_REQUEST['nonce'], 'ls-editor-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	$module = new LS_ModuleManager( $_GET['module'] );

	die(json_encode([
		'success' => empty( $module->errMessage ),
		'message' => $module->errMessage
	]));
}

function ls_get_revisions() {

	// Attempt to workaround memory limit & execution time issues
	@ini_set( 'max_execution_time', 0 );
	@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );

	$revisions = LS_Revisions::snapshots( (int) $_GET['sliderID'] );

	foreach( $revisions as $key => $revision ) {

		$revisions[$key]->avatar 	= function_exists( 'get_avatar_url' ) ? get_avatar_url( $revisions[$key]->author ) : '';
		$revisions[$key]->nickname 	= get_userdata( $revisions[$key]->author )->user_nicename;
		$revisions[$key]->time_diff =  sprintf(__(' %s ago', 'LayerSlider'), human_time_diff( $revision->date_c ) );
		$revisions[$key]->created 	= ls_date('M j @ H:i', $revision->date_c);
		$revisions[$key]->data 		= ls_normalize_slider_data( json_decode( $revision->data , true) );
	}

	die( json_encode( $revisions ) );
}


function ls_create_slider_group() {

	if( ! wp_verify_nonce( $_GET['nonce'], 'ls-dashboard-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	$groupId = LS_Sliders::addGroup( __('Unnamed Group', 'LayerSlider') );

	foreach( $_GET['items'] as $sliderId ) {

		LS_Sliders::addSliderToGroup(
			(int) $sliderId,
			(int) $groupId
		);
	}

	die( json_encode( [ 'success' => true, 'groupId' => $groupId ] ) );
}


function ls_add_slider_to_group() {

	if( ! wp_verify_nonce( $_GET['nonce'], 'ls-dashboard-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	LS_Sliders::addSliderToGroup(
		(int) $_GET['sliderId'],
		(int) $_GET['groupId']
	);
}


function ls_rename_slider_group() {

	if( ! wp_verify_nonce( $_GET['nonce'], 'ls-dashboard-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	LS_Sliders::renameGroup(
		(int) $_GET['groupId'],
		$_GET['name']
	);
}


function ls_remove_slider_from_group() {

	if( ! wp_verify_nonce( $_GET['nonce'], 'ls-dashboard-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	LS_Sliders::removeSliderFromGroup(
		(int) $_GET['sliderId'],
		(int) $_GET['groupId']
	);
}


function ls_delete_slider_group() {

	if( ! wp_verify_nonce( $_GET['nonce'], 'ls-dashboard-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	LS_Sliders::removeGroup( (int) $_GET['groupId'] );
}



// Template store last viewed
function ls_store_opened() {
	update_user_meta( get_current_user_id(), 'ls-store-last-viewed', date('Y-m-d'));
	exit;
}

function ls_addons_opened() {

	if( ! wp_verify_nonce( $_GET['nonce'], 'ls-dashboard-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	update_user_meta( get_current_user_id(), 'ls-addons-last-version', LS_ADDONS_VERSION );
	exit;
}

function layerslider_delete_caches() {

	global $wpdb;
	$sql = "SELECT * FROM $wpdb->options
			WHERE option_name LIKE '_transient_ls-slider-data-%'
			ORDER BY option_id DESC LIMIT 100";

	if( $transients = $wpdb->get_results($sql) ) {
		foreach( $transients as $key => $value ) {
			$key = str_replace('_transient_', '', $value->option_name);
			delete_transient($key);
		}
	}
}

function layerslider_empty_caches() {
	layerslider_delete_caches();
	wp_redirect( admin_url('admin.php?page=layerslider&message=cacheEmpty') );
	exit;
}


function layerslider_empty_google_fonts() {
	delete_option( 'ls-google-fonts' );
	wp_redirect( admin_url('admin.php?page=layerslider&message=googleFontsEmpty') );
	exit;
}


function ls_add_new_slider() {

	$title 	= ! empty( $_POST['title'] ) ? $_POST['title'] : __('Unnamed Project', 'LayerSlider');
	$id 	= LS_Sliders::add( $title );

	wp_redirect( admin_url('admin.php?page=layerslider&action=edit&id='.$id.'&showsettings=1') );
	exit;
}

function ls_sliders_bulk_action() {

	// Export
	if( $_POST['action'] === 'export' ) {
		ls_export_sliders();

	} elseif( $_POST['action'] === 'duplicate') {
		layerslider_duplicateslider( $_POST['sliders'][0] );

	// Hide
	} elseif($_POST['action'] === 'hide') {
		if(!empty($_POST['sliders']) && is_array($_POST['sliders'])) {
			foreach($_POST['sliders'] as $item) {
				LS_Sliders::remove( intval($item) );
				delete_transient('ls-slider-data-'.intval($item));
			}
			wp_redirect( admin_url( 'admin.php?page=layerslider&message=hideSuccess&count='.count($_POST['sliders'])) );
			exit;
		} else {
			wp_redirect( admin_url( 'admin.php?page=layerslider&message=hideSelectError&error=1') );
			exit;
		}


	// Delete
	} elseif($_POST['action'] === 'delete') {
		if(!empty($_POST['sliders']) && is_array($_POST['sliders'])) {
			foreach($_POST['sliders'] as $item) {
				LS_Sliders::delete( intval($item) );
				LS_Revisions::clear( intval($item) );
				delete_transient('ls-slider-data-'.intval($item));
			}
			wp_redirect( admin_url( 'admin.php?page=layerslider&message=deleteSuccess&count='.count($_POST['sliders'])) );
			exit;
		} else {
			wp_redirect( admin_url( 'admin.php?page=layerslider&message=deleteSelectError&error=1') );
			exit;
		}


	// Restore
	} elseif($_POST['action'] === 'restore') {
		if(!empty($_POST['sliders']) && is_array($_POST['sliders'])) {
			foreach($_POST['sliders'] as $item) { LS_Sliders::restore( intval($item)); }
			wp_redirect( admin_url( 'admin.php?page=layerslider&message=restoreSuccess&count='.count($_POST['sliders'])) );
			exit;
		} else {
			wp_redirect( admin_url( 'admin.php?page=layerslider&message=restoreSelectError&error=1') );
			exit;
		}


	// Group
	} elseif($_POST['action'] === 'group') {

		// Error check
		if(!isset($_POST['sliders'][1]) || !is_array($_POST['sliders'])) {
			wp_redirect( admin_url( 'admin.php?page=layerslider&error=1&message=groupSelectError') );
			exit;
		}

		if( $sliders = LS_Sliders::find($_POST['sliders']) ) {
			$groupId = LS_Sliders::addGroup(
				__('Unnamed Group', 'LayerSlider')
			);

			foreach( $sliders as $slider ) {
				LS_Sliders::addSliderToGroup( $slider['id'], $groupId );
			}
		}

		wp_redirect( admin_url( 'admin.php?page=layerslider&message=groupSuccess&count='.count($_POST['sliders']) ) );
		exit;

	// Merge
	} elseif($_POST['action'] === 'merge') {

		// Error check
		if(!isset($_POST['sliders'][1]) || !is_array($_POST['sliders'])) {
			wp_redirect( admin_url( 'admin.php?page=layerslider&error=1&message=mergeSelectError') );
			exit;
		}

		if( $sliders = LS_Sliders::find( $_POST['sliders'] ) ) {

			$data = [
				'layers' => [],
				'googlefonts' => []
			];

			foreach($sliders as $key => $item) {

				// Get IDs
				$ids[] = '#' . $item['id'];

				// Merge slides
				if($key === 0) {
					$data = $item['data'];
				} else {

					if( ! empty( $item['data']['layers'] ) ) {
						$data['layers'] = array_merge( $data['layers'], $item['data']['layers'] );
					}

					if( ! empty( $item['data']['googlefonts'] ) ) {
						$data['googlefonts'] = array_merge( $data['googlefonts'], $item['data']['googlefonts'] );
					}
				}
			}

			// Filter out duplicate Google Fonts
			if( ! empty( $data['googlefonts'] ) ) {
				$usedFonts = [];
				$data['googlefonts'] = array_filter( $data['googlefonts'], function( $font ) use ( &$usedFonts ) {

					if( ! in_array( $font['param'], $usedFonts ) ) {
						$usedFonts[] = $font['param'];
						return true;
					}

					return false;
				});
			}

			// Save as new
			$name = 'Merged sliders of ' . implode(', ', $ids);
			$data['properties']['title'] = $name;
			LS_Sliders::add($name, $data);
		}

		wp_redirect( admin_url( 'admin.php?page=layerslider&message=mergeSuccess&count='.count( $_POST['sliders'] ) ) );
		exit;
	}
}

function ls_save_plugin_settings() {

	check_admin_referer('ls-save-plugin-settings');

	// Get capability
	$capability = ( $_POST['custom_role'] === 'custom' ) ?
					$_POST['custom_capability'] :
					$_POST['custom_role'];

	// Test capability
	if( ! empty( $capability ) && current_user_can( $capability ) ) {
		update_option('layerslider_custom_capability', $capability);
	}

	// Language
	update_option('ls_custom_locale', $_POST['ls_custom_locale'] );

	$options = [

		//Performance
		'performance_mode',
		'use_cache',
		'include_at_footer',
		'conditional_script_loading',
		'concatenate_output',
		'defer_scripts',
		'use_loading_attribute',

		// Troubleshooting
		'clear_3rd_party_caches',
		'admin_no_conflict_mode',
		'fix_optimizer_issues',
		'rocketscript_ignore',
		'load_all_js_files',
		'gsap_sandboxing',

		// Miscellaneous
		'tinymce_helper',
		'gutenberg_block',
		'elementor_widget',
		'suppress_debug_info',
		'enable_play_by_scroll',
		'wpml_string_translation',
		'wpml_link_translation',
		'wpml_media_translation',
		'wpml_auto_cleanup',

		// Project Defaults
		'use_srcset',
		'enhanced_lazy_load'
	];

	foreach( $options as $item ) {
		update_option('ls_'.$item, (int) array_key_exists($item, $_POST));
	}

	// Google Fonts
	update_option('layerslider-google-fonts-enabled', (int) array_key_exists('ls_google_fonts_status', $_POST) );
	update_option('layerslider-google-fonts-host-locally', (int) array_key_exists('ls_google_fonts_local_cache', $_POST) );

	// Scripts priority
	update_option('ls_scripts_priority', (int)$_POST['scripts_priority']);



	layerslider_delete_caches();

	die( json_encode( [ 'success' => true ] ) );
}

function ls_save_google_fonts() {

	check_admin_referer('save-google-fonts');

	$fonts = [];
	if( ! empty( $_POST['fonts'] ) && is_array( $_POST['fonts'] ) ) {
		foreach( $_POST['fonts'] as $key => $val ) {

			if( ! empty( $val ) ) {
				$fonts[] = [
					'param' => $val
				];
			}
		}
	}

	update_option('ls-google-fonts', $fonts );
	exit;
}


function ls_save_pagination_limit() {

	$userID = get_current_user_id();
	$limit 	= (int) $_POST['limit'];

	update_user_meta( $userID, 'ls-pagination-limit', $limit );
	exit;
}


function ls_save_editor_settings() {

	if( ! wp_verify_nonce( $_POST['nonce'], 'ls-editor-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	update_user_meta( get_current_user_id(), 'ls-editor-settings', $_POST['data'] );
	die( json_encode( [ 'success' => true ] ) );
}

function ls_slider_library_contents() {

	$sliders = LS_Sliders::find([
		'orderby' => 'date_c',
		'order' => 'DESC',
		'limit' => 200,
		'groups' => true
	]);

	$excludeActionSheet = true;

	include LS_ROOT_PATH.'/templates/tmpl-project-library.php';

	exit;
}


function ls_get_slider_details( ) {

	$sliderID = (int) $_GET['sliderID'];

	$slider = LS_Sliders::find( $sliderID );
	$preview = apply_filters('ls_preview_for_slider', $slider );

	die( json_encode([
		'id' => $slider['id'],
		'slug' => htmlentities( $slider['slug'] ),
		'name' => apply_filters('ls_slider_title', stripslashes( $slider['name'] ), 40 ),
		'previewurl' => ! empty( $preview ) ? $preview : LS_ROOT_URL . '/static/admin/img/blank.gif',
		'slidecount' => ! empty( $slider['data']['layers'] ) ? count( $slider['data']['layers'] ) : 0,
		'author' => $slider['author'],
		'date_c' => $slider['date_c'],
		'date_m' => $slider['date_m']
	]) );
}


function ls_get_mce_sliders() {

	$sliders = LS_Sliders::find( [ 'limit' => 200 ] );

	foreach($sliders as $key => $item) {
		$sliders[ $key ]['preview'] = apply_filters('ls_preview_for_slider', $item );
		$sliders[ $key ]['name'] 	= apply_filters('ls_slider_title', stripslashes( $item['name'] ), 40);
		$sliders[ $key ]['slug'] 	= ! empty( $item['slug'] ) ? htmlentities( $item['slug'] ) : '';
		$sliders[ $key ]['pt'] 		= ! empty( $item['data']['properties']['pt'] );


		// Prevent outputting the unnecessarily large slider data object that
		// in some cases also causes server issues with the large request data.
		$sliders[ $key ]['data'] 	= null;
	}

	die( json_encode( $sliders ) );
}


function ls_get_mce_slides() {

	$sliderID = (int) $_GET['sliderID'];

	$slider = LS_Sliders::find( $sliderID );
	$slider = $slider['data'];
	$slider = ls_normalize_slider_data( $slider );

	die( json_encode( $slider['layers'] ) );
}


function ls_layer_action_popups() {

	$popups = LS_Sliders::find( [
		'limit' => 200,
		'where' => "flag_popup = '1'",
		'data' => false
	]);

	foreach( $popups as $key => $item) {
		$popups[ $key ]['name'] 	= apply_filters('ls_slider_title', stripslashes( $item['name'] ), 40);
		$popups[ $key ]['slug'] 	= ! empty( $item['slug'] ) ? htmlspecialchars( stripslashes( $item['slug'] ) ) : '';
	}

	return die( json_encode( $popups, JSON_UNESCAPED_UNICODE ) );
}


// Multi-slide Scroll Scene projects for the scrollToSceneSlide layer action
function ls_layer_action_scene_projects() {

	$projects = LS_Sliders::find( [
		'limit' => 200,
		'where' => 'flag_popup = \'0\' AND data LIKE \'%"scene":"scroll"%\''
	]);

	$result = [];

	foreach( $projects as $item ) {

		$data = $item['data'];

		if(
			empty( $data['properties']['scene'] ) ||
			$data['properties']['scene'] !== 'scroll' ||
			( ! empty( $data['properties']['type'] ) && $data['properties']['type'] === 'popup' ) ||
			empty( $data['layers'] ) ||
			count( $data['layers'] ) < 2
		) {
			continue;
		}

		$slides = [];
		foreach( $data['layers'] as $slide ) {
			$slides[] = ! empty( $slide['properties']['title'] ) ? stripslashes( $slide['properties']['title'] ) : '';
		}

		$result[] = [
			'id' 		=> (int) $item['id'],
			'slug' 		=> ! empty( $item['slug'] ) ? htmlspecialchars( stripslashes( $item['slug'] ) ) : '',
			'name' 		=> apply_filters('ls_slider_title', stripslashes( $item['name'] ), 40),
			'slides' 	=> $slides
		];
	}

	die( json_encode( $result, JSON_UNESCAPED_UNICODE ) );
}


function ls_rename_project() {

	check_admin_referer( 'bulk-action' );

	$id = (int) $_POST['id'];
	$name = $_POST['name'];

	LS_Sliders::rename( $id, $name );
}

function ls_extract_slider_data_from_request() {

	if( empty( $_FILES['sliderData']['tmp_name'] ) || $_FILES['sliderData']['error'] !== UPLOAD_ERR_OK ) {
		wp_send_json_error([
			'errCode' => 'ERR_UPLOAD_ERROR',
			'title' => __('Save Error', 'LayerSlider'),
			'message' => __('There was an error uploading the project data file.', 'LayerSlider'),
		]);
	}

	$data = file_get_contents( $_FILES['sliderData']['tmp_name'] );
	$json = json_decode( $data, true );

	if( json_last_error() !== JSON_ERROR_NONE ) {

		// json_last_error_msg() requires PHP 5.5+
		$jsonError = function_exists('json_last_error_msg') ? json_last_error_msg() : 'error code #'.json_last_error();

		wp_send_json_error( [
			'errCode' => 'ERR_INVALID_JSON',
			'title' => __('Save Error', 'LayerSlider'),
			'message' => sprintf( __('Couldn’t parse the project data file as JSON. It threw the following error: %s', 'LayerSlider'), $jsonError ),
		]);
	}

	return $json;
}


function ls_prepare_save_data( $data ) {

	// Parse slider settings
	$data['properties'] = json_decode( html_entity_decode( $data['properties'] ), true );

	$schedule_start = $data['properties']['schedule_start'];
	$schedule_end = $data['properties']['schedule_end'];

	if( ! empty( $schedule_start ) && is_string( $schedule_start ) ) {
		$data['properties']['schedule_start'] = ls_date_create_for_timezone( $schedule_start );
	}

	if( ! empty( $schedule_end ) && is_string( $schedule_end  ) ) {
		$data['properties']['schedule_end'] = ls_date_create_for_timezone( $schedule_end  );
	}
	// Parse slide data
	if(!empty($data['layers']) && is_array($data['layers'])) {
		foreach($data['layers'] as $slideKey => $slideData) {

			$slideData = json_decode( $slideData, true );

			$schedule_start = ! empty( $slideData['properties']['schedule_start'] ) ? $slideData['properties']['schedule_start'] : '';
			$schedule_end = ! empty( $slideData['properties']['schedule_end'] ) ? $slideData['properties']['schedule_end'] : '';

			if( ! empty( $schedule_start ) && is_string( $schedule_start ) ) {
				$slideData['properties']['schedule_start'] = ls_date_create_for_timezone( $schedule_start );
			}

			if( ! empty( $schedule_end ) && is_string( $schedule_end  ) ) {
				$slideData['properties']['schedule_end'] = ls_date_create_for_timezone( $schedule_end  );
			}

			if( ! empty( $slideData['sublayers'] ) ) {
				foreach( $slideData['sublayers'] as $layerKey => $layerData ) {

					if( ! empty( $layerData['transition'] ) ) {
						$slideData['sublayers'][$layerKey]['transition'] = addslashes($layerData['transition']);
					}

					if( ! empty( $layerData['styles'] ) ) {
						$slideData['sublayers'][$layerKey]['styles'] = addslashes($layerData['styles']);
					}
				}
			}

			$data['layers'][$slideKey] = $slideData;
		}
	}

	$title = esc_sql($data['properties']['title']);
	$slug = !empty($data['properties']['slug']) ? esc_sql($data['properties']['slug']) : '';


	// Relative URL
	if( isset( $data['properties']['relativeurls'] ) ) {
		$data = layerslider_convert_urls($data);
	}

	return [
		'title' => $title,
		'slug' 	=> $slug,
		'data' 	=> $data
	];
}


function ls_save_slider() {

	$id = (int) $_POST['id'];

	// Security check
	check_admin_referer( 'ls-save-slider-' . $id );

	// Set $title, $slug, $data
	$data = ls_extract_slider_data_from_request();
	$isDirty = $_POST['dirty'];
	extract( ls_prepare_save_data( $data ) );

	// WPML
	if( ls_should_use_wpml_string_translation() ) {

		// Get published version
		$published = LS_Sliders::find( $id );
		layerslider_register_wpml_strings( $id, $data, $published['data'] );
	}

	// Save draft
	LS_Sliders::saveDraft( $id, $data, $isDirty );

	// Revisions handling
	if( LS_Revisions::$active ) {

		$lastRevision = LS_Revisions::last( $id );

		if( ! $lastRevision || $lastRevision->date_c < time() - 60*LS_Revisions::$interval ) {
			LS_Revisions::add( $id, json_encode($data) );

			if( LS_Revisions::count( $id ) > LS_Revisions::$limit ) {
				LS_Revisions::shift( $id );
			}
		}
	}

	wp_send_json_success();
}


function ls_publish_slider() {

	$id = (int) $_POST['id'];

	// Security check
	check_admin_referer( 'ls-save-slider-' . $id );

	// Set $title, $slug, $data
	$data = ls_extract_slider_data_from_request();
	extract( ls_prepare_save_data( $data ) );

	// WPML
	if( ls_should_use_wpml_string_translation() ) {
		layerslider_register_wpml_strings( $id, $data );
	}

	// Delete transient (if any) to
	// invalidate outdated data
	delete_transient('ls-slider-data-'.$id);

	// Save draft
	LS_Sliders::saveDraft( $id, $data );

	// Revisions handling
	if( LS_Revisions::$active ) {

		$lastRevision = LS_Revisions::last( $id );

		if( ! $lastRevision || $lastRevision->date_c < time() - 60*LS_Revisions::$interval ) {
			LS_Revisions::add( $id, json_encode($data) );

			if( LS_Revisions::count( $id ) > LS_Revisions::$limit ) {
				LS_Revisions::shift( $id );
			}
		}
	}

	// Update the slider
	if( empty( $id ) ) {
		$id = LS_Sliders::add($title, $data, $slug);
	} else {
		LS_Sliders::update($id, $title, $data, $slug);
	}


	// Popup Index
	if( $data['properties']['type'] === 'popup' ) {
		$props = $data['properties'];
		LS_Popups::addIndex([
			'id' => $id,
			'first_time_visitor' => ! empty($props['popup_first_time_visitor']),
			'repeat' => ! empty( $props['popup_repeat'] ),
			'repeat_days' => $props['popup_repeat_days'],
			'roles' => [
				'administrator' => ! empty($props['popup_roles_administrator']),
				'editor' 		=> ! empty($props['popup_roles_editor']),
				'author' 		=> ! empty($props['popup_roles_author']),
				'contributor' 	=> ! empty($props['popup_roles_contributor']),
				'subscriber' 	=> ! empty($props['popup_roles_subscriber']),
				'customer' 		=> ! empty($props['popup_roles_customer']),
				'visitor' 		=> ! empty($props['popup_roles_visitor'])
			],

			'pages' => [
				'all'  		=> ! empty($props['popup_pages_all']),
				'home' 		=> ! empty($props['popup_pages_home']),
				'post' 		=> ! empty($props['popup_pages_post']),
				'page' 		=> ! empty($props['popup_pages_page']),
				'custom' 	=> $props['popup_pages_custom'],
				'exclude' 	=> $props['popup_pages_exclude']
			]
		]);

	} else {
		LS_Popups::removeIndex( $id );
	}


	// Clear 3rd party caches
	if( get_option('ls_clear_3rd_party_caches', true ) ) {
		ls_empty_3rd_party_caches();
	}


	wp_send_json_success();
}


function ls_save_revisions_options() {

	// Security check
	check_admin_referer('ls-save-revisions-options');

	$enabled = ! empty( $_POST['enabled'] );

	update_option('ls-revisions-enabled', $enabled );
	update_option('ls-revisions-limit', (int) $_POST['limit']);
	update_option('ls-revisions-interval', (int) $_POST['interval']);

	die( json_encode( [ 'status' => true, 'success' => true ] ) );
}


function ls_delete_revisions() {

	// Security check
	check_admin_referer('ls-save-revisions-options');

	// Delete revisions
	if( ! empty( $_POST['delete-all'] ) ) {
		LS_Revisions::truncate();
	}


	die( json_encode( [ 'status' => true ] ) );
}




function ls_revert_slider( ) {

	$sliderId 	= (int)$_POST['slider-id'];
	$revisionId = (int)$_POST['revision-id'];

	// Security check
	check_admin_referer('ls-revert-slider-'.$sliderId);

	if( empty( $sliderId ) || empty( $revisionId ) ) {
		die( json_encode( [ 'status' => false ] ) );
	}

	// Revert back to revision
	LS_Revisions::revert( $sliderId, $revisionId );

	// Delete transient cache
	delete_transient( 'ls-slider-data-'.$sliderId );

	die( json_encode( [ 'status' => true ] ) );
}


/********************************************************/
/*               Action to duplicate slider             */
/********************************************************/
function layerslider_duplicateslider( $sliderId = 0 ) {

	if( empty( $sliderId ) ) {
		$sliderId = $_GET['id'];
	}

	$sliderId = (int) $sliderId;
	if( empty( $sliderId ) ) {
		return;
	}

	// Get the original slider
	$slider = LS_Sliders::find( $sliderId );
	$data = $slider['data'];

	// Name check
	if( empty( $data['properties']['title'] ) ) {
		$data['properties']['title'] = 'Unnamed';
	}

	// Remove existing layer UUIDs for better WPML integration.
	// The editor will generate new UUIDs and register these layers
	// as new, so users can freely change the contents of the duplicated
	// slider without having conflicts with or references to the old one.
	if( ! empty( $data['layers'] ) && is_array( $data['layers'] ) ) {
		foreach( $data['layers'] as $slideKey => $slideData ) {

			if( ! empty( $slideData['sublayers'] ) && is_array( $slideData['sublayers'] ) ) {
				foreach( $slideData['sublayers'] as $layerKey => $layerData ) {

					unset( $data['layers'][ $slideKey ]['sublayers'][ $layerKey ]['uuid'] );
				}
			}
		}
	}

	// Insert the duplicate
	$data['properties']['title'] .= ' copy';
	LS_Sliders::add($data['properties']['title'], $data);

	// Success
	wp_redirect( admin_url( 'admin.php?page=layerslider&message=duplicateSuccess&count=1') );
	exit;
}


/********************************************************/
/*                Action to remove slider               */
/********************************************************/
function layerslider_hideslider() {

	// Check received data
	if(empty($_GET['id'])) { return false; }

	// Remove the slider
	LS_Sliders::remove( intval($_GET['id']) );

	// Delete transient cache
	delete_transient('ls-slider-data-'.intval($_GET['id']));

	// Reload page
	wp_redirect( admin_url( 'admin.php?page=layerslider&message=hideSuccess&count=1') );
	exit;
}


/********************************************************/
/*                Action to restore slider              */
/********************************************************/
function layerslider_restoreslider() {

	// Check received data
	if(empty($_GET['id'])) { return false; }

	// Remove the slider
	LS_Sliders::restore( (int) $_GET['id'] );

	// Delete transient cache
	delete_transient('ls-slider-data-'.intval($_GET['id']));

	// Reload page
	if( ! empty($_GET['ref']) ) {
		wp_safe_redirect( urldecode($_GET['ref']) );
	} else {
		wp_redirect( admin_url('admin.php?page=layerslider&message=restoreSuccess&count=1') );
	}

	exit;
}

/********************************************************/
/*            Actions to import sample slider            */
/********************************************************/
function ls_import_bundled() {

	// Check nonce
	check_ajax_referer('ls-import-demos', 'security');


	// Get samples and importUtil
	$sliders = LS_Sources::getDemoSliders();
	require_once LS_ROOT_PATH.'/classes/class.ls.importutil.php';

	if( ! empty($_GET['slider']) && is_string($_GET['slider'] )) {
		if( $item = LS_Sources::getDemoSlider($_GET['slider']) ) {
			if( file_exists( $item['file'] ) ) {
				$import = new LS_ImportUtil($item['file']);
				$id = $import->lastImportId;
			}
		}
	}

	die( json_encode( [
		'success' => !! $id,
		'slider_id' => $id,
		'url' => admin_url('admin.php?page=layerslider&action=edit&id='.$id)
	] ));
}


function ls_import_online() {

	// Check nonce
	check_ajax_referer('ls-import-demos', 'security');

	$name 			= $_GET['name'];
	$slider 		= urlencode( $_GET['slider'] );
	$category 		= ! empty( $_GET['category'] ) ? urlencode( $_GET['category'] ) : '';
	$remoteURL 		= LS_REPO_BASE_URL.'sliders/download.php?slider='.$slider.'&collection='.$category;
	$fileName 		= sanitize_file_name( $slider );
	$tmpFolder 		= LS_FileSystem::createUniqueTmpFolder( $fileName.'_zip' );
	$downloadPath 	= $tmpFolder . '/'.$fileName.'.zip';

	// Download package
	$zip 			= $GLOBALS['LS_AutoUpdate']->sendApiRequest( $remoteURL );
	$defErrorCode 	= 'ERR_UNAUTHORIZED_ACCESS';
	$defErrorTitle 	= __('Import Error', 'LayerSlider');
	$defErrorMsg 	= sprintf(__('It seems there is a server issue that prevented LayerSlider from importing the selected template. Please check %sSystem Status%s for potential errors, try to temporarily disable themes/plugins to rule out incompatibility issues or contact your hosting provider to resolve server configuration problems. Retrying the import might also help.', 'LayerSlider'), '<a href="'.admin_url( 'admin.php?page=layerslider&section=system-status' ).'" target="_blank">', '</a>');


	// Invalid response
	if( ! $zip ) {
		die( json_encode( [
			'success' => false,
			'message' => $defErrorMsg
		] ) );
	}


	// Try parsing response as JSON.
	//
	// Warn about potential errors and check
	// activation state on client side in case
	// of receiving a "Not Activated" flag
	if( $zip && $zip[0] === '{' && $zip[1] === '"' )  {
		if( $json = json_decode( $zip, true ) ) {

			// Check activation state
			if( ! empty( $json['_not_activated'] ) ) {

				$subDeactivated = ! empty( $json['_sub_deactivated'] );
				$GLOBALS['LS_AutoUpdate']->check_activation_state( $subDeactivated );

				die( json_encode( [
					'success' => false,
					'reload' => true
				] ) );
			}

			die( json_encode( [
				'success' => false,
				'errCode' => ! empty( $json['errCode'] ) ? $json['errCode'] : $defErrorCode,
				'title' 	=> ! empty( $json['title'] ) ? $json['title'] : $defErrorTitle,
				'message' => ! empty( $json['message'] ) ? $json['message'] : $defErrorMsg
			] ) );
		}
	}


	// Save package
	if( ! file_put_contents( $downloadPath, $zip ) ) {
		die( json_encode( [
			'success' => false,
			'message' => sprintf(__('LayerSlider couldn’t save the downloaded template on your server. Please check %sSystem Status%s for potential issues. The most common reason for this issue is the lack of write permission on the /wp-content/uploads/ directory.', 'LayerSlider'), '<a href="'.admin_url( 'admin.php?page=layerslider&section=system-status' ).'" target="_blank">', '</a>')
		] ) );
	}

	// Load importUtil & import the slider
	require_once LS_ROOT_PATH.'/classes/class.ls.importutil.php';
	$import = new LS_ImportUtil( $downloadPath, null, $name, true );
	$id = $import->lastImportId;
	$sliderCount = (int)$import->sliderCount;

	// Remove package
	LS_FileSystem::deleteDir( $tmpFolder );
	LS_FileSystem::cleanupTmpFiles();

	$url = admin_url('admin.php?page=layerslider&action=edit&id='.$id);

	if( $sliderCount > 1 ) {
		$url = admin_url('admin.php?page=layerslider&message=importSuccess&count='.$sliderCount);
	}

	// Success
	die( json_encode( [
		'success' => !! $id,
		'slider_id' => $id,
		'url' => $url
	] ) );
}





// IMPORT SLIDERS
//-------------------------------------------------------
function ls_import_sliders() {

	if( ! isset( $_FILES['import_file'] ) ) {
		wp_redirect( admin_url('admin.php?page=layerslider&error=1&message=importSelectError' ) );
		exit;
	}

	$file = $_FILES['import_file'];

	// Didn't choose a file
	if( $file['error'] === UPLOAD_ERR_NO_FILE ) {
		wp_redirect( admin_url('admin.php?page=layerslider&error=1&message=importSelectError') );
		exit;
	}

	// Too large file
	if( $file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE ) {
		wp_redirect( admin_url('admin.php?page=layerslider&error=1&message=uploadErrorSize') );
		exit;
	}

	// Other error
	if( $file['error'] !== UPLOAD_ERR_OK ) {
		wp_redirect( admin_url('admin.php?page=layerslider&error=1&message=uploadError') );
		exit;
	}

	// Sec-check
	if( ! is_uploaded_file( $file['tmp_name'] ) ) {
		wp_redirect( admin_url('admin.php?page=layerslider&error=1&message=uploadError' ) );
		exit;
	}

	require_once LS_ROOT_PATH.'/classes/class.ls.importutil.php';

	$import = new LS_ImportUtil( $file['tmp_name'], $file['name'], __('Imported Group', 'LayerSlider') );

	if( ! empty( $import->lastErrorCode ) ) {
		if( $import->lastErrorCode === 'LR_PARTIAL_IMPORT' ) {
			wp_redirect( admin_url('admin.php?page=layerslider&message=importLRPartial&error') );
			exit;

		} elseif( $import->lastErrorCode === 'LR_EMPTY_IMPORT' ) {
			wp_redirect( admin_url('admin.php?page=layerslider&message=importLSEmpty&error') );
			exit;
		}
	}

	// One slider, redirect to editor
	if( ! empty( $import->lastImportId ) ) {

		if(	(int)$import->sliderCount === 1 ) {
			wp_redirect( admin_url('admin.php?page=layerslider&action=edit&id='.$import->lastImportId) );

		// Multiple sliders, redirect to slider list
		} else {
			wp_redirect( admin_url('admin.php?page=layerslider&message=importSuccess&count='.$import->sliderCount) );
		}

	} else {
		wp_redirect( admin_url('admin.php?page=layerslider&message=importFailed&error') );
	}

	exit;
}




// EXPORT SLIDERS
//-------------------------------------------------------
function ls_export_sliders( $sliderId = 0 ) {

	// Get sliders
	if( ! empty( $sliderId ) ) {
		$sliders = LS_Sliders::find( $sliderId );

	} elseif(isset($_POST['sliders'][0]) && $_POST['sliders'][0] == -1) {
		$sliders = LS_Sliders::find( ['limit' => 500 ] );

	} elseif(!empty($_POST['sliders'])) {
		$sliders = LS_Sliders::find($_POST['sliders']);

	} else {
		wp_redirect( admin_url( 'admin.php?page=layerslider&error=1&message=exportSelectError') );
		die('Invalid data received.');
	}

	// Check results
	if(empty($sliders)) {
		wp_redirect( admin_url( 'admin.php?page=layerslider&error=1&message=exportNotFound') );
		die('Invalid data received.');
	}

	if(class_exists('ZipArchive')) {
		include LS_ROOT_PATH.'/classes/class.ls.exportutil.php';
		$zip = new LS_ExportUtil;
	}

	// Gather slider data
	foreach($sliders as $item) {

		// Get saved project draft if any
		if( $draft = LS_Sliders::getDraft( (int) $item['id'] ) ) {
			$item['data'] = $draft['data'];
		}

		// Slider settings array for fallback mode
		$data[] = $item['data'];

		// If ZipArchive is available
		if( class_exists('ZipArchive') ) {

			// Add slider folder and settings.json
			$name = empty($item['name']) ? 'slider_' . $item['id'] : $item['name'];
			$name = sanitize_file_name($name);
			$zip->addSettings(json_encode($item['data']), $name);

			// Add images?
			if(!isset($_POST['skip_images'])) {

				remove_all_filters('wp_get_attachment_image_src');

				$images = $zip->getImagesForSlider($item['data']);
				$images = $zip->getFSPaths($images);
				$zip->addImage($images, $name);
			}
		}
	}

	if( class_exists('ZipArchive') ) {

		$date = ls_date('Y-m-d').' at '.ls_date('H.i.s');

		if( count( $sliders ) > 1 ) {
			$fileName = 'LayerSlider - '.count( $sliders ).' sliders - '.$date.'.zip';
		} else {
			$fileName = 'LayerSlider - '.$name.' - '.$date.'.zip';
		}

		$zip->download( $fileName );

	} else {
		$name = 'LayerSlider Export '.ls_date('Y-m-d').' at '.ls_date('H.i.s').'.json';
		header('Content-type: application/force-download');
		header('Content-Disposition: attachment; filename="'.str_replace(' ', '_', $name).'"');
		die(base64_encode(json_encode($data)));
	}
}


function ls_upload_lottie_file() {

	// Check the nonce for security
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'ls-editor-nonce' ) ) {
		wp_send_json_error( [ 'message' => 'Invalid nonce. Please reload the page and try again.' ] );
	}

	// Ensure a file was uploaded
	if ( empty( $_FILES['file'] ) ) {
		wp_send_json_error( [ 'message' => 'No file uploaded.' ] );
	}

	$file = $_FILES['file'];
	$allowed_extensions = [ 'lottie', 'json' ];

	// Validate file type
	$file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
	if ( ! in_array( $file_extension, $allowed_extensions ) ) {
		wp_send_json_error( [ 'message' => 'Invalid file format. Only .lottie and .json files are allowed.' ] );
	}

	// Sanitize the file name
	$original_file_name = sanitize_file_name( pathinfo( $file['name'], PATHINFO_FILENAME ) );
	$file_name = $original_file_name . '.' . $file_extension;

	// Ensure the upload directory exists
	$uploads = wp_upload_dir();
	$upload_base_dir = $uploads['basedir'];
	$upload_dir = $upload_base_dir . '/layerslider/lottiefiles/imported';
	LS_FileSystem::createUploadDirs();

	$target_file = $upload_dir . '/' . $file_name;
	$isDuplicate = false;
	$i = 0;


	// Check if the file already exists and compare contents
	do {
		if( file_exists( $target_file ) ) {

			// Files are identical, return the existing file name
			if( hash_file( 'md5', $target_file ) === md5_file( $file['tmp_name'] ) ) {
				wp_send_json_success( [
					'src' => 'imported/'.$file_name,
					'url' => $uploads['baseurl'] . '/layerslider/lottiefiles/imported/' . $file_name
				] );
				$isDuplicate = true;
				break;
			}

			// Increment the file name for the next iteration
			$i++;
			$file_name = $original_file_name . '-' . $i . '.' . $file_extension;
			$target_file = $upload_dir . '/' . $file_name;
		} else {
			break; // No more files to check
		}
	} while( true );

	// If a duplicate was found, skip the rest of the processing
	if( $isDuplicate ) {
		return;
	}

	// Move the uploaded file to the unique path
	if ( ! move_uploaded_file( $file['tmp_name'], $target_file ) ) {
		wp_send_json_error( [ 'message' => 'Failed to move the uploaded file.' ] );
	}

	// Return the new file name
	wp_send_json_success( [
		'src' => 'imported/'.$file_name,
		'url' => $uploads['baseurl'] . '/layerslider/lottiefiles/imported/' . $file_name
	] );
}


function ls_get_lottie_uploads() {

	// Check the nonce for security
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ls-editor-nonce' ) ) {
		wp_send_json_error( [ 'message' => 'Invalid nonce. Please reload the page and try again.' ] );
	}

	// Ensure the upload directory exists
	$uploads = wp_upload_dir();
	$upload_base_dir = $uploads['basedir'];
	$upload_dir = $upload_base_dir . '/layerslider/lottiefiles/imported';

	if( ! is_dir( $upload_dir ) ) {
		wp_send_json_success([
			'files' => []
		]);
	}

	// Get all files in the directory
	$files = @scandir( $upload_dir );
	$files = array_diff( $files, [ '.', '..', 'index.php' ] );
	$files_data = [];

	$files_with_time = [];
	foreach( $files as $file ) {
		$files_with_time[$file] = filemtime( $upload_dir.'/'.$file );
	}

	arsort( $files_with_time );
	$files = array_keys( $files_with_time );

	foreach( $files as $file ) {
		$file_path = $upload_dir . '/' . $file;
		$file_url = $uploads['baseurl'] . '/layerslider/lottiefiles/imported/' . $file;
		$file_extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );

		// Only include .json and .lottie files
		if( in_array( $file_extension, [ 'json', 'lottie' ] ) ) {
			$files_data[] = [
				'src' => 'imported/'.$file,
				'url' => $file_url,
				'size' => filesize( $file_path ),
				'created' => filectime( $file_path ),
				'modified' => filemtime( $file_path )
			];
		}
	}

	wp_send_json_success([
		'files' => $files_data
	]);
}


function ls_fetch_external_lottie() {

	// Check the nonce for security
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ls-editor-nonce' ) ) {
		wp_send_json_error( [ 'message' => 'Invalid nonce. Please reload the page and try again.' ] );
	}

	// Ensure a URL was provided
	if( empty( $_GET['url'] ) ) {
		wp_send_json_error( [ 'message' => 'No URL provided.' ] );
	}

	$external_url = sanitize_url( $_GET['url'] );
	$path = parse_url( $external_url, PHP_URL_PATH );
	$file_name_from_url = basename( $path );
	$file_extension = strtolower( pathinfo( $file_name_from_url, PATHINFO_EXTENSION ) );

	// Fetch the external file
	$response = wp_remote_get( $external_url, [ 'timeout' => 30 ] );

	if( is_wp_error( $response ) ) {
		wp_send_json_error( [ 'message' => 'Failed to fetch the external file: ' . $response->get_error_message() ] );
	}

	$file_content = wp_remote_retrieve_body( $response );
	if( empty( $file_content ) ) {
		wp_send_json_error( [ 'message' => 'The external file is empty.' ] );
	}

	// Determine or validate file extension based on content
	json_decode( $file_content );
	$is_valid_json = ( json_last_error() === JSON_ERROR_NONE );
	$is_zip_header  = ( substr( $file_content, 0, 2 ) === 'PK' );
	$detected_type = $is_valid_json ? 'json' : ( $is_zip_header ? 'lottie' : false );

	// Error if the content is not a recognized format
	if( ! $detected_type ) {
		wp_send_json_error( [ 'message' => 'Invalid or unsupported file format.' ] );
	}

	// Error if the URL extension was valid but doesn't match the content
	if( in_array( $file_extension, [ 'json', 'lottie' ] ) && $file_extension !== $detected_type ) {
		wp_send_json_error( [ 'message' => 'Content mismatch: The file content does not match the URL extension.' ] );
	}

	// Everything is fine, use the detected extension
	$file_extension = $detected_type;

	// Sanitize the file name
	$original_file_name = sanitize_file_name( pathinfo( $file_name_from_url, PATHINFO_FILENAME ) );
	if( empty( $original_file_name ) ) {
		$original_file_name = 'external-lottie-' . time();
	}
	$file_name = $original_file_name . '.' . $file_extension;

	// Ensure the upload directory exists
	$uploads = wp_upload_dir();
	$upload_base_dir = $uploads['basedir'];
	$upload_dir = $upload_base_dir . '/layerslider/lottiefiles/remote';
	LS_FileSystem::createUploadDirs();

	$target_file = $upload_dir . '/' . $file_name;
	$isDuplicate = false;
	$i = 0;
	$content_hash = md5( $file_content );

	// Check if the file already exists and compare contents
	do {
		if( file_exists( $target_file ) ) {

			// Files are identical, return the existing file name
			if( hash_file( 'md5', $target_file ) === $content_hash ) {
				wp_send_json_success([
					'src' => 'remote/'.$file_name,
					'url' => $uploads['baseurl'] . '/layerslider/lottiefiles/remote/' . $file_name
				]);
				$isDuplicate = true;
				break;
			}

			// Increment the file name for the next iteration
			$i++;
			$file_name = $original_file_name . '-' . $i . '.' . $file_extension;
			$target_file = $upload_dir . '/' . $file_name;
		} else {
			break; // No more files to check
		}
	} while( true );

	// If a duplicate was found, skip the rest of the processing
	if( $isDuplicate ) {
		return;
	}

	// Save the fetched content to the unique path
	if( file_put_contents( $target_file, $file_content ) === false ) {
		wp_send_json_error( [ 'message' => 'Failed to save the external file.' ] );
	}

	// Return the new file name
	wp_send_json_success([
		'src' => 'remote/'.$file_name,
		'url' => $uploads['baseurl'] . '/layerslider/lottiefiles/remote/' . $file_name
	]);
}



// CSS EDITOR
//-------------------------------------------------------
function ls_save_user_css() {

	// Get target file and content
	$upload_dir = wp_upload_dir();
	$file = $upload_dir['basedir'].'/layerslider.custom.css';
	$content = sanitize_textarea_field( stripslashes( $_POST['contents'] ) );

	// Attempt to save changes
	if( is_writable( $upload_dir['basedir'] ) ) {

		if( empty( $content ) ) {
			unlink( $file );
		} else {
			file_put_contents( $file, $content );
		}

		wp_redirect( admin_url( 'admin.php?page=layerslider&section=css-editor&message=editSuccess' ) );
		exit;

	// File isn't writable
	} else {
		wp_die(__('It looks like your files isn’t writable, so PHP couldn’t make any changes (CHMOD).', 'LayerSlider'), __('Cannot write to file', 'LayerSlider'), [ 'back_link' => true ] );
	}
}





// SKIN EDITOR
//-------------------------------------------------------
function ls_save_user_skin() {

	// Error checking
	if( empty( $_POST['skin'] ) || strpos( $_POST['skin'], '..' ) !== false ) {
		wp_die( __('It looks like you haven’t selected any skin to edit.', 'LayerSlider'), __('No skin selected.', 'LayerSlider'), ['back_link' => true ] );
	}

	// Get skin file and contents
	$skin = LS_Sources::getSkin( $_POST['skin'] );
	$file = $skin['file'];
	$content = sanitize_textarea_field( stripslashes( $_POST['contents'] ) );

	// Attempt to write the file
	if( is_writable( $file ) ) {
		file_put_contents( $file, $content );
		wp_redirect( admin_url( 'admin.php?page=layerslider&section=skin-editor&message=editSuccess&skin='.$skin['handle'] ) );
		exit;
	} else {
		wp_die( __('It looks like your files isn’t writable, so PHP couldn’t make any changes (CHMOD).', 'LayerSlider'), __('Cannot write to file', 'LayerSlider'), [ 'back_link' => true ] );
	}
}




// TRANSITION BUILDER
//-------------------------------------------------------
function ls_save_user_transitions() {

	$upload_dir = wp_upload_dir();
	$custom_trs = $upload_dir['basedir'] . '/layerslider.custom.transitions.js';
	$content = sanitize_textarea_field( stripslashes($_POST['ls-transitions']) );
	$data = 'var layerSliderCustomTransitions = '.$content.';';
	file_put_contents($custom_trs, $data);
	die('SUCCESS');
}


// --
function ls_get_post_details() {

	$params = $_POST['params'];

	if( empty( $params['post_type'] ) ) {
		$params['post_type'] = 'post';
	}

	$queryArgs = [
		'post_status' => 'publish',
		'limit' => 100,
		'posts_per_page' => 100,
		'post_type' => $params['post_type'],
		'suppress_filters' => false
	];

	if(!empty($params['post_orderby'])) {
		$queryArgs['orderby'] = $params['post_orderby']; }

	if(!empty($params['post_order'])) {
		$queryArgs['order'] = $params['post_order']; }

	if(!empty($params['post_categories'][0])) {
		$queryArgs['category__in'] = $params['post_categories']; }

	if(!empty($params['post_tags'][0])) {
		$queryArgs['tag__in'] = $params['post_tags']; }

	if(!empty($params['post_taxonomy']) && !empty($params['post_tax_terms'])) {
		$queryArgs['tax_query'][] = [
			'taxonomy' => $params['post_taxonomy'],
			'field' => 'id',
			'terms' => $params['post_tax_terms']
		];
	}

	$posts = LS_Posts::find($queryArgs)->getParsedObject();

	die(json_encode($posts));
}


function lse_get_search_posts() {

	$filters = [
		'posts_per_page' 	=> 50,
		'post_status' 		=> 'any',
		'post_type' 		=> 'post'
	];

	if( ! empty( $_GET['s'] ) ) {
		$filters['s'] = $_GET['s'];
	}

	if( ! empty( $_GET['post_type'] ) ) {
		$types = [ 'post', 'page', 'attachment' ];
		if( in_array( $_GET['post_type'], $types ) ) {
			$filters['post_type'] = $_GET['post_type'];
		}
	}

	$query = new WP_Query( $filters );

	if( ! empty( $query->posts ) ) {
		$ret = [];
		foreach ( $query->posts as $key => $val ) {

			if( $val->post_type === 'attachment' ) {
				$imageURL = wp_get_attachment_url( $val->ID );
			} elseif( function_exists('get_post_thumbnail_id') && function_exists('wp_get_attachment_url') ) {
				$imageURL = wp_get_attachment_url(get_post_thumbnail_id( $val->ID ));
			}

			if( ! $imageURL ) {
				$imageURL = LS_ROOT_URL . '/static/admin/img/blank.gif';
			}

			$ret[] = [
				'author' 	=> get_userdata($val->post_author)->user_nicename,
				'content' 	=> htmlentities( $val->post_content ),
				'image-url' => $imageURL,
				'post-id' 	=> $val->ID,
				'post-slug' => $val->post_name,
				'post-url' 	=> get_permalink( $val->ID ),
				'post-type' => $val->post_type,
				'title' 	=> htmlentities( $val->post_title ),
				'date-published' => get_the_date('', $val->ID ),
				'date-modified' => get_the_modified_date('', $val->ID )
			];
		}

		die( json_encode( $ret ) );
	}

	die('[]');

}


function ls_get_taxonomies() {
	die(json_encode(array_values(get_terms($_POST['taxonomy']))));
}



function ls_erase_plugin_data() {

	// Only administrators can use this function.
	if( ! current_user_can('manage_options') ) {
		die('You are not an administrator.');
	}

	// Check for network-wide
	if( isset( $_POST['networkwide'] ) && ! current_user_can('manage_network') ) {
		die('You are not a network admin.');
	}

	if( is_multisite() && isset( $_POST['networkwide'] ) ) {

		// Get current & other sites
		global $wpdb;
		$current = $wpdb->blogid;
		$sites 	 = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

		// Iterate over the sites
		foreach($sites as $site) {
			switch_to_blog($site);
			ls_do_erase_plugin_data();
		}

		// Switch back the old site
		switch_to_blog($current);

		// Deactivate LayerSlider network-wide
		deactivate_plugins( LS_PLUGIN_BASE, false, true );

	} else {
		ls_do_erase_plugin_data();
	}

	// Finished
	wp_redirect( admin_url('plugins.php') );
	exit;
}



function ls_do_erase_plugin_data() {

	require_once LS_ROOT_PATH.'/classes/class.ls.uninstaller.php';

	$uninstaller = new LS_Uninstaller;
	$uninstaller->erasePluginData();
	$uninstaller->deactivatePlugin();

}


// function ls_upload_from_url() {

// 	// Check user permission
// 	if(!current_user_can(get_option('layerslider_custom_capability', 'manage_options'))) {
// 		die( json_encode( ['success' => false ] ) );
// 	}

// 	// Get URL & uploads folder
// 	$url = $_GET['url'];
// 	$uploads = wp_upload_dir();

// 	// Check if /uploads dir is writable
// 	if(is_writable($uploads['basedir'])) {

// 		// Set upload target
// 		$targetDir	= $uploads['basedir'].'/layerslider/cc_sdk/';
// 		$targetURL	= $uploads['baseurl'].'/layerslider/cc_sdk/';
// 		$targetExt	= pathinfo($url, PATHINFO_EXTENSION);
// 		$uploadFile	= $targetDir.time().'.'.$targetExt;

// 		// Create folder if not exists
// 		if(!file_exists(dirname($targetDir))) { mkdir(dirname($targetDir), 0755); }
// 		if(!file_exists($targetDir)) { mkdir($targetDir, 0755); }

// 		// Save image from URL
// 		$fp = fopen($uploadFile, 'w');
// 		fwrite($fp, file_get_contents($url));
// 		fclose($fp);

// 		// Include image.php for media library upload
// 		require_once(ABSPATH.'wp-admin/includes/image.php');

// 		// Get file type
// 		$fileName = sanitize_file_name(basename($uploadFile));
// 		$fileType = wp_check_filetype($fileName, null);

// 		// Validate media
// 		if(!empty($fileType['ext']) && $fileType['ext'] != 'php') {

// 			// Attachment meta
// 			$attachment = [
// 				'guid' => $uploadFile,
// 				'post_mime_type' => $fileType['type'],
// 				'post_title' => preg_replace( '/\.[^.]+$/', '', $fileName),
// 				'post_content' => '',
// 				'post_status' => 'inherit'
// 			];

// 			// Insert and update attachment
// 			$attach_id = wp_insert_attachment($attachment, $uploadFile, 37);
// 			if($attach_data = wp_generate_attachment_metadata($attach_id, $uploadFile)) {
// 				wp_update_attachment_metadata($attach_id, $attach_data);
// 			}

// 			// Success
// 			die( json_encode( [
// 				'success' => true,
// 				'id' => $attach_id,
// 				'url' => $targetURL.$fileName
// 			] ) );
// 		}
// 	}
// }


function layerslider_convert_urls($arr) {

	// Global BG
	if(!empty($arr['properties']['backgroundimage']) && strpos($arr['properties']['backgroundimage'], 'http://') !== false) {
		$arr['properties']['backgroundimage'] = parse_url($arr['properties']['backgroundimage'], PHP_URL_PATH);
	}

	if(!empty($arr['layers'])) {
		foreach($arr['layers'] as $key => $slide) {

			// Layer BG
			if(strpos($slide['properties']['background'], 'http://') !== false) {
				$arr['layers'][$key]['properties']['background'] = parse_url($slide['properties']['background'], PHP_URL_PATH);
			}

			// Layer Thumb
			if(strpos($slide['properties']['thumbnail'], 'http://') !== false) {
				$arr['layers'][$key]['properties']['thumbnail'] = parse_url($slide['properties']['thumbnail'], PHP_URL_PATH);
			}

			// Image sublayers
			if(!empty($slide['sublayers'])) {
				foreach($slide['sublayers'] as $subkey => $layer) {
					if($layer['media'] == 'img' && strpos($layer['image'], 'http://') !== false) {
						$arr['layers'][$key]['sublayers'][$subkey]['image'] = parse_url($layer['image'], PHP_URL_PATH);
					}
				}
			}
		}
	}

	return $arr;
}




function ls_empty_3rd_party_caches() {

	// W3 Total Cache
	if( function_exists( 'w3tc_flush_all' ) ) {
		w3tc_flush_all();
	}

	// WP Fastest Cache
	if( ! empty( $GLOBALS['wp_fastest_cache'] ) && method_exists( $GLOBALS['wp_fastest_cache'], 'deleteCache' ) ) {
		$GLOBALS['wp_fastest_cache']->deleteCache();
	}

	// WP Super Cache
	if( function_exists( 'wp_cache_clean_cache' ) ) {
		global $file_prefix;
		wp_cache_clean_cache( $file_prefix, true );
	}

	// WP Rocket
	if( function_exists('rocket_clean_domain') ) {
		rocket_clean_domain();
	}

	// WP-Optimize
	if( function_exists('wpo_cache_flush') ) {
		wpo_cache_flush();
	}

	// SG Optimizer
	if( function_exists('sg_cachepress_purge_cache') ) {
		sg_cachepress_purge_cache();
	}

	// LiteSpeed Cache For WordPress
	if( has_action('litespeed_purge_all') ) {
		do_action( 'litespeed_purge_all' );
	}

	// Autoptimize
	if( method_exists('autoptimizeCache', 'clearall' ) ) {
		autoptimizeCache::clearall();
	}
}

function ls_download_object() {

	// Check nonce
	if( ! wp_verify_nonce( $_GET['nonce'], 'ls-editor-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	$licenseErrorTitle = __('Assets library requires license registration', 'LayerSlider');
	$licenseErrorMessage = __('An error occurred while we tried to validate your LayerSlider license. Please verify that you’re using a genuine copy of LayerSlider and that you’ve registered a valid license key.');

	if( ! LS_Config::isActivatedSite() ) {

		die( json_encode( [
			'success' => false,
			'title' => $licenseErrorTitle,
			'message' => $licenseErrorMessage
		] ) );
	}

	$uploads 		= wp_upload_dir();

	$version 		= sanitize_file_name( basename( $_GET['version'] ) );
	$category 		= sanitize_file_name( basename( $_GET['category'] ) );
	$tag 			= sanitize_file_name( basename( $_GET['tag'] ) );
	$handle 		= sanitize_file_name( basename( $_GET['handle'] ) );
	$size 			= sanitize_file_name( basename( $_GET['size'] ) );
	$type 			= sanitize_file_name( basename( $_GET['type'] ) );
	$type 			= ! empty( $type ) ? $type : 'png';

	$urlParams 		= sprintf( 'version=%s&category=%s&tag=%s&handle=%s&size=%s&type=%s',
		urlencode( $version ),
		urlencode( $category ),
		urlencode( $tag ),
		urlencode( $handle ),
		urlencode( $size ),
		urlencode( $type )
	);

	$verifyURL 		= LS_REPO_BASE_URL.'assets/verify-object.php?'.$urlParams;
	$dlURL 			= LS_REPO_BASE_URL.'assets/download-object.php?'.$urlParams;

	$downloadPath 	= $uploads['basedir'].'/layerslider/assets/objects/'.$version.'/'.$category.'/'.$tag.'/'.$handle.'--'.$size.'.'.$type;
	$attachURL 		= $uploads['baseurl'].'/layerslider/assets/objects/'.$version.'/'.$category.'/'.$tag.'/'.$handle.'--'.$size.'.'.$type;

	$needsDownLoad 	= ! file_exists( $downloadPath );
	$needsNewThumbs = false;


	//
	// Already downloaded, verify by hash
	if( ! $needsDownLoad ) {

		$verifyURL .= '&hash='.sha1_file( $downloadPath );
		$response   = $GLOBALS['LS_AutoUpdate']->sendApiRequest( $verifyURL );

		if( $json = json_decode( $response, true ) ) {

			if( ! empty( $json['_hash_mismatch'] ) ) {
				$needsDownLoad = true;
				$needsNewThumbs = true;
			}

			if( ! empty( $json['_not_activated'] ) ) {

				$subDeactivated = ! empty( $json['_sub_deactivated'] );
				$GLOBALS['LS_AutoUpdate']->check_activation_state( $subDeactivated );

				die( json_encode( [
					'success' => false,
					'title' => $licenseErrorTitle,
					'message' => $licenseErrorMessage
				] ) );
			}
		}
	}


	//
	// Download object
	if( $needsDownLoad ) {

		// Download package
		$image 			= $GLOBALS['LS_AutoUpdate']->sendApiRequest( $dlURL );
		$defErrorCode 	= 'ERR_UNAUTHORIZED_ACCESS';
		$defErrorTitle 	= __('Download Error', 'LayerSlider');
		$defErrorMsg 	= sprintf(__('It seems there is a server issue that prevented LayerSlider from downloading the selected asset. Please check %sSystem Status%s for potential errors, try to temporarily disable themes/plugins to rule out incompatibility issues, or contact your hosting provider to resolve server configuration problems.', 'LayerSlider'), '<a href="'.admin_url( 'admin.php?page=layerslider&section=system-status' ).'" target="_blank">', '</a>');

		// Invalid response
		if( ! $image ) {
			die( json_encode( [
				'success' => false,
				'message' => $defErrorMsg
			] ) );
		}



		// Try parsing response as JSON.
		//
		// Warn about potential errors and check
		// activation state on client side in case
		// of receiving a "Not Activated" flag
		if( $image && $image[0] === '{' && $image[1] === '"' )  {
			if( $json = json_decode( $image, true ) ) {

				// Check activation state
				if( ! empty( $json['_not_activated'] ) ) {

					$subDeactivated = ! empty( $json['_sub_deactivated'] );
					$GLOBALS['LS_AutoUpdate']->check_activation_state( $subDeactivated );

					die( json_encode( [
						'success' => false,
						'title' => $licenseErrorTitle,
						'message' => $licenseErrorMessage
					] ) );
				}

				die( json_encode( [
					'success' => false,
					'errCode' => ! empty( $json['errCode'] ) ? $json['errCode'] : $defErrorCode,
					'title' 	=> ! empty( $json['title'] ) ? $json['title'] : $defErrorTitle,
					'message' => ! empty( $json['message'] ) ? $json['message'] : $defErrorMsg
				] ) );
			}
		}


		LS_FileSystem::createUploadDirs();
		wp_mkdir_p( $uploads['basedir'].'/layerslider/assets/objects/'.$version.'/'.$category.'/'.$tag );

		LS_FileSystem::addIndexPHP( $uploads['basedir'].'/layerslider/assets/objects/'.$version );
		LS_FileSystem::addIndexPHP( $uploads['basedir'].'/layerslider/assets/objects/'.$version.'/'.$category );
		LS_FileSystem::addIndexPHP( $uploads['basedir'].'/layerslider/assets/objects/'.$version.'/'.$category.'/'.$tag );

		if( ! file_put_contents( $downloadPath, $image ) ) {
			die( json_encode( [
				'success' => false,
				'message' => sprintf(__('LayerSlider couldn’t save the downloaded asset on your server. Please check %sSystem Status%s for potential issues. The most common reason for this issue is the lack of write permission on the /wp-content/uploads/ directory.', 'LayerSlider'), '<a href="'.admin_url( 'admin.php?page=layerslider&section=system-status' ).'" target="_blank">', '</a>')
			] ) );
		}
	}



	// SVG
	if( $type === 'svg' ) {

		if( $source = file_get_contents( $downloadPath ) ) {

			die( json_encode( [
				'success' => true,
				'type' => $type,
				'source' => $source
			] ) );
		}

	// IMAGE, VIDEO, AUDIO
	} else {

		$data = ls_maybe_upload_from_path( $downloadPath, $attachURL, $needsNewThumbs );

		if( ! empty( $data ) ) {
			die( json_encode( [
				'success' => true,
				'type' => $type,
				'id' => $data['id'],
				'url' => $data['url']
			] ) );
		}
	}


	die( json_encode( [ 'success' => false ] ) );
}



function ls_assets_remote_download() {

	// Check nonce
	if( ! wp_verify_nonce( $_GET['nonce'], 'ls-editor-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	$licenseErrorTitle = __('Assets library requires license registration', 'LayerSlider');
	$licenseErrorMessage = __('An error occurred while we tried to validate your LayerSlider license. Please verify that you’re using a genuine copy of LayerSlider and that you’ve registered a valid license key.');

	if( ! LS_Config::isActivatedSite() ) {

		die( json_encode( [
			'success' => false,
			'title' => $licenseErrorTitle,
			'message' => $licenseErrorMessage
		] ) );
	}

	$uploads 	= wp_upload_dir();

	$id 		= (int) $_GET['id'];
	$size 		= basename( $_GET['size'] );
	$type 		= basename( $_GET['type'] );

	$urlParams 	= sprintf( 'id=%s&size=%s&type=%s',
		urlencode( $id ),
		urlencode( $size ),
		urlencode( $type )
	);

	$infoURL 	= LS_REPO_BASE_URL.'assets/remote-download.php?'.$urlParams;
	$infoRes 	= $GLOBALS['LS_AutoUpdate']->sendApiRequest( $infoURL );
	$json 		= json_decode( $infoRes, true );
	$fileName 	= '';
	$dlURL 		= '';

	if( empty( $infoRes ) || empty( $json ) ) {
		die( json_encode( [
			'success' => false,
			'title' => __('Server Error', 'LayerSlider'),
			'message' => sprintf(__('It seems there is a server issue that prevented LayerSlider from accessing the assets you’re looking for. Please check %sSystem Status%s for potential errors, try to temporarily disable themes/plugins to rule out incompatibility issues, or contact your hosting provider to resolve server configuration problems.', 'LayerSlider'), '<a href="'.admin_url( 'admin.php?page=layerslider&section=system-status' ).'" target="_blank">', '</a>')
		] ) );
	}

	if( ! empty( $json ) ) {

		if( ! empty( $json['_not_activated'] ) ) {

			$subDeactivated = ! empty( $json['_sub_deactivated'] );
			$GLOBALS['LS_AutoUpdate']->check_activation_state( $subDeactivated );

			die( json_encode( [
				'success' => false,
				'title' => $licenseErrorTitle,
				'message' => $licenseErrorMessage
			] ) );
		}

		if( empty( $json['fileName'] ) || empty( $json['dlURL'] ) ) {
			die( json_encode( [
				'success' => false,
				'message' => __('It seems the remote API is experiencing a temporary outage, and we couldn’t load the assets you’re looking for. Please try again in a few minutes.')
			] ) );
		}

		$fileName 	= $json['fileName'];
		$dlURL 		= $json['dlURL'];
	}

	$downloadPath 	= $uploads['basedir'].'/layerslider/assets/remote/'.$fileName;
	$attachURL 		= $uploads['baseurl'].'/layerslider/assets/remote/'.$fileName;

	$needsDownLoad 	= ! file_exists( $downloadPath );
	$needsNewThumbs = false;


	//
	// Download asset
	if( $needsDownLoad ) {

		$image = wp_remote_retrieve_body( wp_remote_get( $dlURL ) ) ;

		// Invalid response
		if( ! $image ) {
			die( json_encode( [
				'success' => false,
				'message' => sprintf(__('It seems there is a server issue that prevented LayerSlider from downloading the selected asset. Please check %sSystem Status%s for potential errors, try to temporarily disable themes/plugins to rule out incompatibility issues, or contact your hosting provider to resolve server configuration problems.', 'LayerSlider'), '<a href="'.admin_url( 'admin.php?page=layerslider&section=system-status' ).'" target="_blank">', '</a>')
			] ) );
		}

		LS_FileSystem::createUploadDirs();

		if( ! file_put_contents( $downloadPath, $image ) ) {
			die( json_encode( [
				'success' => false,
				'message' => sprintf(__('LayerSlider couldn’t save the downloaded asset on your server. Please check %sSystem Status%s for potential issues. The most common reason for this issue is the lack of write permission on the /wp-content/uploads/ directory.', 'LayerSlider'), '<a href="'.admin_url( 'admin.php?page=layerslider&section=system-status' ).'" target="_blank">', '</a>')
			] ) );
		}
	}

	$data = ls_maybe_upload_from_path( $downloadPath, $attachURL, $needsNewThumbs );

	if( ! empty( $data ) ) {
		die( json_encode( [
			'success' => true,
			'type' => $type,
			'id' => $data['id'],
			'url' => $data['url'],
			'data' => $data['data']
		] ) );
	}



	die( json_encode( [ 'success' => false ] ) );
}


function ls_assets_remote_search() {

	// Check nonce
	if( ! wp_verify_nonce( $_GET['nonce'], 'ls-editor-nonce') ) {
		die( json_encode( [ 'success' => false ] ) );
	}

	$page 		= (int) $_GET['page'];
	$type 		= basename( $_GET['type'] );
	$query 		= basename( $_GET['query'] );

	$searchURL 	= LS_REPO_BASE_URL.'assets/remote-search.php?';
	$searchURL .= sprintf( 'type=%s&page=%s&query=%s',
		urlencode( $type ),
		urlencode( $page ),
		urlencode( $query )
	);

	$json = $GLOBALS['LS_AutoUpdate']->sendApiRequest( $searchURL );
	$jsonParse = json_decode( $json );

	if( empty( $json ) || empty( $jsonParse ) ) {
		die( json_encode( [
			'success' => false,
			'title' => __('Server Error', 'LayerSlider'),
			'message' => sprintf(__('It seems there is a server issue that prevented LayerSlider from accessing the assets you’re looking for. Please check %sSystem Status%s for potential errors, try to temporarily disable themes/plugins to rule out incompatibility issues, or contact your hosting provider to resolve server configuration problems.', 'LayerSlider'), '<a href="'.admin_url( 'admin.php?page=layerslider&section=system-status' ).'" target="_blank">', '</a>')
		] ) );
	}

	die( $json );
}


function ls_maybe_upload_from_path( $path, $url = '', $updateThumbnails = false ) {

	// New image, needs uploading
	if( ! $attach_id = ls_attach_id_for_path( $url ) ) {

		// Include image.php for media library upload
		require_once(ABSPATH.'wp-admin/includes/image.php');

		$filetype = wp_check_filetype( $path, null );

		// Upload to media library
		$attachment = [
			'guid' => $url,
			'post_mime_type' => $filetype['type'],
			'post_title' => sanitize_file_name( basename( $path ) ),
			'post_content' => '',
			'post_status' => 'inherit'
		];

		$attach_id = wp_insert_attachment( $attachment, $path, 37);
		if( $attach_data = wp_generate_attachment_metadata( $attach_id, $path ) ) {
			wp_update_attachment_metadata( $attach_id, $attach_data );
		}
	}


	// Modified image, needs updating
	if( $updateThumbnails ) {
		if( $attach_data = wp_generate_attachment_metadata( $attach_id, $path ) ) {
			wp_update_attachment_metadata( $attach_id, $attach_data );
		}
	}


	$attach_url = wp_get_attachment_url( $attach_id );
	$attach_data = wp_prepare_attachment_for_js( (int) $attach_id );

	// SUCCESS
	if( ! empty( $attach_id ) && ! empty( $attach_url ) ) {
		return [
			'success' => true,
			'id' => $attach_id,
			'url' => $attach_url,
			'data' => $attach_data
		];
	}

	return false;
}


function ls_attach_id_for_path( $url ) {

	if( $attachID = attachment_url_to_postid( $url ) ) {
		return $attachID;
	}

	return false;
}