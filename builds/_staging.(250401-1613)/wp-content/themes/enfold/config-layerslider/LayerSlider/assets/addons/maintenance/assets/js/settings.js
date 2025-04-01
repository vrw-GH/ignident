jQuery( function( $ ) {

	const wrapperID = '#ls-maintenance-addon-settings';

	$( document ).on( 'change', wrapperID+' select[name="project"]', function() {

		let $editorButton = $('.ls--maintenance-open-project-editor-button'),
			projectID = $(this).val(),
			baseURL = $editorButton.attr('data-href-base');

		$editorButton.attr('href', baseURL+projectID );

	}).on( 'change', wrapperID+' select[name="page"]', function() {

		let $editorButton = $('.ls--maintenance-open-page-editor-button'),
			projectID = $(this).val(),
			baseURL = $editorButton.attr('data-href-base');

		$editorButton.attr('href', baseURL+projectID );

	}).on( 'change', wrapperID+' select[name="content"]', function() {
		$( wrapperID ).attr('data-content', $(this).val());

	});

	// Load settings
	const data = { action: 'ls_maintenance_load_addon_settings' };
	jQuery('#ls--addon-maintenance-ajax-container').load( ajaxurl, data, function( response ) {
		LS_Addons.setPrevAddonData( jQuery( wrapperID ) );
	});

});