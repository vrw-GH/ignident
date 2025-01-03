(function($)
{
    'use strict';

    $( function()
    {
    	$('body').on('click', 'a, input.button, button, submit', function(e)
    	{
    		e.preventDefault();
    	});

		//	activate monitor of backend preview elements
		new AviaMonitorPreViewElements();
    });


	/**
	 * As preview iframe does not accept jQuery trigger events when backend preview has been updated we have to
	 * monitor a class to bind js shortcode to new element
	 *
	 * @since 4.8.6.3
	 */
	var AviaMonitorPreViewElements = function()
	{
		this.active = false;
		this.elList = [];
		this.previewContainer = $( '#av-admin-preview' );

		this.registerElements();
		this.start();
	};

	AviaMonitorPreViewElements.prototype =
	{
		registerElements: function()
		{
			if( 'undefined' == typeof avia_monitor_preview_elements )
			{
				return;
			}

			this.elList = avia_monitor_preview_elements;
		},

		start: function()
		{
			if( this.elList.length == 0 )
			{
				return;
			}

			if( ! this.active )
			{
				this.active = true;
				this.monitorPreView();
			}
		},

		monitorPreView: function()
		{
			if( this.previewContainer.length == 0 )
			{
				this.previewContainer = $( '#av-admin-preview' );
			}

			if( this.previewContainer.length > 0 )
			{
				if( this.previewContainer.hasClass( 'avia-preview-updated' ) )
				{
					this.checkToBind();
				}
			}

			//	keep alive as trigger does not work due to security reasons
			var obj = this;
			setTimeout( function()
			{
				obj.monitorPreView();
			}, 300 );
		},

		checkToBind: function()
		{
			var obj = this;

			$.each( obj.elList, function( index, element )
			{
				var el_container = obj.previewContainer.find( element.selector );

				if( el_container.length > 0 )
				{
					obj.previewContainer.removeClass( 'avia-preview-updated' ).addClass( 'avia-preview-bind-js' );

					if( 'undefined' != typeof element.object )
					{
						//	plain js plugin
						if( 'undefined' != typeof window[ element.object ] && 'undefined' != typeof window[ element.object ][element.plugin] )
						{
							//	el_container is jQuery object !!
							window[ element.object ][element.plugin]( el_container[0] );
						}
					}
					else if( 'undefined' != typeof el_container[element.plugin] )
					{
						//	jQuery plugin
						el_container[element.plugin]();
					}

					obj.previewContainer.removeClass( 'avia-preview-bind-js' );

					el_container.trigger( 'avia_start_animation' );
				}
			});
		}
	};

})( jQuery );
