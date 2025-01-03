jQuery( function( $ ) {

	const wrapperID = '#ls-404-addon-settings';

	$( document ).on( 'change', wrapperID+' select[name="project"]', function() {

		let $editorButton = $('.ls--404-open-project-editor-button'),
			projectID = $(this).val(),
			baseURL = $editorButton.attr('data-href-base');

		$editorButton.attr('href', baseURL+projectID );

	}).on( 'change', wrapperID+' select[name="page"]', function() {

		let $editorButton = $('.ls--404-open-page-editor-button'),
			projectID = $(this).val(),
			baseURL = $editorButton.attr('data-href-base');

		$editorButton.attr('href', baseURL+projectID );

	}).on( 'change', wrapperID+' select[name="type"]', function() {
		$( wrapperID ).attr('data-type', $(this).val());

	});

	// Load settings
	const data = { action: 'ls_404_load_addon_settings' };
	jQuery('#ls--addon-404-ajax-container').load( ajaxurl, data, function( response ) {
		LS_Addons.setPrevAddonData( jQuery( wrapperID ) );
	});

});