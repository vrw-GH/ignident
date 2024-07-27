/*
 * Support for a progress bar showing scroll position below header
 *
 *
 * @since 5.6
 * @by Günter
 */

"use strict";

//	global namespace
var avia_js_shortcodes = avia_js_shortcodes || {};
var aviaJS = aviaJS || {};

(function()
{
	if( ! avia_js_shortcodes.aviaHeaderReadingProgress )
	{
		class aviaHeaderReadingProgress
		{
			container = null;		//	container element
			id = '';
			settings = {
						color: ''
					};
			header = [];
			currentPos = 0;

			constructor( container )
			{
				this.container = container;
				this.container.aviaHeaderReadingProgress = this;
				this.id = container.getAttribute('id');
				this.header = container.closest( '#header' );

				if( ! this.header || this.header.classList.contains( 'av_header_sticky_disabled' ) )
				{
					this.container.style.display = 'none';
					return;
				}

				let passed = JSON.parse( this.container.dataset.settings );
				this.settings = Object.assign( this.settings, passed );

				if( this.settings.color )
				{
					this.container.style['background-color'] = this.settings.color;
				}

				this.bindEvents();
				this.scrollPosChanged();
			}

			bindEvents()
			{
				window.addEventListener( 'scroll', this.onScroll.bind( this ) );
				window.addEventListener( 'resize', aviaJS.aviaJSHelpers.debounce( this.onResize.bind( this ), 200 ) );

				window.addEventListener( 'avia_height_change', this.onAviaHeightChange.bind( this ) );
				document.body.addEventListener( 'avia_resize_finished', this.onAviaHeightChange.bind( this ) );
			}

			onScroll( e )
			{
				this.scrollPosChanged();
			}

			onResize( e )
			{
				this.scrollPosChanged();
			}

			onAviaHeightChange( e )
			{
				this.scrollPosChanged();
			}

			scrollPosChanged()
			{
				this.currentPos = this.currentScrollPercentage();

				this.container.style.width = this.currentPos + '%';
			}

			currentScrollPercentage()
			{
				let elScrollTop = document.documentElement.scrollTop,
					bodyScrollTop = document.body.scrollTop,
					elScrollHeight = document.documentElement.scrollHeight,
					elClientHeight = document.documentElement.clientHeight;

				let pos = ( elScrollTop + bodyScrollTop ) / ( elScrollHeight - elClientHeight ) * 100.0;

				return pos;
			}
		}

		//	class factory
		avia_js_shortcodes.aviaHeaderReadingProgress = function( container )
		{
			return new aviaHeaderReadingProgress( container );
		};

		aviaJS.aviaPlugins.register( avia_js_shortcodes.aviaHeaderReadingProgress, '.header-reading-progress' );
	}

})();

