/**
 * ALB Shortcode Lottie Animations backend ALB canvas
 *
 * @added by guenter
 * @since 5.5
 */
"use strict";

//	global namespace
var avia_js_shortcodes = avia_js_shortcodes || {};

(function()
{
	if( ! avia_js_shortcodes.aviaLottieAnimationALBCanvas )
	{
		class aviaLottieAnimationALBCanvas
		{
			body = null;
			builder = null;

			constructor( builder )
			{
				let obj = this;

				this.builder = builder;

				let els = document.getElementsByTagName( 'body' );

				if( ! els.length )
				{
					//	security fallback only
					return;
				}

				this.body = els[0];
				this.body.addEventListener( 'av-alb-element-dropped-in-editor', this.monitorAnimations.bind( this ) );
				this.body.addEventListener( 'av-alb-canvas-updated', this.monitorCanvasUpdated.bind( this ) );

				//	jQuery trigger hack
				this.body.onAviaBuilder_interface_loaded = ( e ) =>
				{
					obj.monitorCanvasUpdated();
				};
			}

			//	we need to monitor for animations (after drag drop of containers playing is stopped and invisible)
			monitorAnimations( e )
			{
				let lotties = document.getElementsByTagName( 'dotlottie-player' );
				if( lotties.length )
				{
					for( let player of lotties )
					{
						player.dataset.av_src = player.__src;
						player.stop();
					}
				}

				setTimeout( function()
				{
					let lotties = document.getElementsByTagName( 'dotlottie-player' );
					if( lotties.length )
					{
						for( let player of lotties )
						{
							if( typeof player.load === 'function' && typeof player.dataset.av_src == 'string' )
							{
								player.load( player.dataset.av_src );
							}
						}
					}
				}, 200 );
			}

			//	we need to monitor for direct linked animation in second container
			monitorCanvasUpdated( e )
			{
				let obj = this.builder;

				setTimeout( function()
				{
					let containers2 = obj.querySelectorAll( '.avia_lottie_player_container.container2' );
					if( ! containers2.length )
					{
						return;
					}

					for( let container2 of containers2 )
					{
						let player2 = container2.getElementsByTagName( 'dotlottie-player' );
						if( ! player2.length )
						{
							continue;
						}

						let wrapper = player2[0].closest( '.avia_lottie_player_wrap' );
						if( wrapper == null )
						{
							continue;
						}

						let container1 = wrapper.querySelector( '.avia_lottie_player_container.container1' );
						let src = player2[0].getAttribute('src') || '';

						if( src.trim() == '' )
						{
							container1.style.display = 'block';
							container2.style.display = 'none';
						}
						else
						{
							container1.style.display = 'none';
							container2.style.display = 'block';
						}
					}
				}, 200 );
			}
		}

		//	class factory
		avia_js_shortcodes.aviaLottieAnimationALBCanvas = function( builder )
		{
			return new aviaLottieAnimationALBCanvas( builder );
		};
	}

	//	bind js to elements
	const bind_aviaLottieAnimationAdmin = function()
	{
		let builder = document.getElementById( 'aviaLayoutBuilder' );
		if( builder != null )
		{
			avia_js_shortcodes.aviaLottieAnimationALBCanvas( builder );
		}
	};

	if( document.readyState === 'complete' )
	{
		// The page is already fully loaded
		bind_aviaLottieAnimationAdmin();
	}
	else
	{
		document.onreadystatechange = ( e ) =>
		{
			if( document.readyState === 'complete' )
			{
				// document ready
				bind_aviaLottieAnimationAdmin();
			}
		};
	}

})();
