	jQuery(document).ready(function() {
		jQuery('#xyz_ips_system_notice_area').animate({
			opacity : 'show',
			height : 'show'
		}, 500);

		jQuery('#xyz_ips_system_notice_area_dismiss').click(function() {
			jQuery('#xyz_ips_system_notice_area').animate({
				opacity : 'hide',
				height : 'hide'
			}, 500);

		});

		let ips_deactivateURL = '';
		jQuery(document).on('click', '.xyz-ips-deactivate-link', function(e) {
			e.preventDefault();
			ips_deactivateURL = jQuery(this).attr('href');
			jQuery('#xyz-ips-modal').fadeIn();
		});
		jQuery('#xyz-ips-proceed-deactivate').on('click', function() {
			window.location.href = ips_deactivateURL;
		});
		jQuery('#xyz-ips-cancel-deactivate').on('click', function() {
			jQuery('#xyz-ips-modal').fadeOut();
		});
	});
