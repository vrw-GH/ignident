/*
 * Holds scripts for handling admin notices
 *
 * @since 6.0
 */
(function($)
{
	"use strict";

	$(window).on('load', function (e)
	{
        $('.avia-admin-notices').aviaAdminNotices();
    });

	$.fn.aviaAdminNotices = function()
	{
		if( ! this.length )
		{
			return;
		}

		return this.each( function()
		{
			let container = $(this),
				settings = container.data( 'avia-notice-settings' ),
				closeIcon = container.find( 'button.notice-dismiss' ),
				dismissText = container.find( 'button.notice-dismiss-text' ),
				allNotices = $('.avia-admin-notices'),

				sendNoticeDismissed = function()
				{
					let senddata = {
								action: 'avia_admin_notice_dismissed',
								settings: settings,
								avia_admin_notices_nonce: container.data( 'avia_admin_notices_nonce' )
							};

					$.ajax({
							type: "POST",
							url: avia_framework_globals.ajaxurl,
							dataType: 'json',
							cache: false,
							data: senddata,
							post_type: $('.avia-builder-main-wrap').data('post_type'),
							beforeSend: function()
							{
								allNotices.addClass('avia-ajax-send');
							},
							success: function(response, textStatus, jqXHR)
							{
								if( response.success == true )
								{
									console.log( response.message );
								}
								else
								{
									console.log( '******* Error: ' + response.message );
								}
							},
							error: function(errorObj)
							{
								console.log( '******* Ajax Error: ', errorObj );
							},
							complete: function( jqXHR, status )
							{
								allNotices.removeClass('avia-ajax-send');
							}
						});

				},

				closeNoticeBox = function()
				{
					container.fadeTo( 100, 0, function()
					{
						container.slideUp( 100, function()
						{
							container.remove();
						});
					});
				};

			//	see WP core ..\wp-admin\js\common.js makeNoticesDismissible()
			closeIcon.on( 'click', function( event )
			{
				event.preventDefault();

				if( ! dismissText.length )
				{
					sendNoticeDismissed();
				}

				closeNoticeBox();
			});

			dismissText.on( 'click', function( event )
			{
				event.preventDefault();

				sendNoticeDismissed();
				closeNoticeBox();
			});

		});
	};

}(jQuery));
