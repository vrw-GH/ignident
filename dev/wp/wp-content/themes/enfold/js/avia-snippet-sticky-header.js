(function($)
{
	"use strict";

	$( function()
	{
		// decreases header size when user scrolls down
		avia_header_size();
	});


	function av_change_class($element, change_method, class_name)
	{
		if($element[0].classList)
		{
			if(change_method == "add")
			{
				$element[0].classList.add(class_name);
			}
			else
			{
				$element[0].classList.remove(class_name);
			}
		}
		else
		{
			if(change_method == "add")
			{
				$element.addClass(class_name);
			}
			else
			{
				$element.removeClass(class_name);
			}
		}
	}


	function avia_header_size()
	{
		var win				= $(window),
			header          = $('.html_header_top.html_header_sticky #header'),
			unsticktop		= $('.av_header_unstick_top');

		if( ! header.length && ! unsticktop.length )
		{
			return;
		}

		var logo            = $('#header_main .container .logo img, #header_main .container .logo svg, #header_main .container .logo a'),
			elements        = $('#header_main .container:not(#header_main_alternate>.container), #header_main .main_menu ul:first-child > li > a:not(.avia_mega_div a, #header_main_alternate a), #header_main #menu-item-shop .cart_dropdown_link'),
			el_height       = $(elements).first().height(),
			isMobile        = $.avia_utilities.isMobile,
			scroll_top		= $('#scroll-top-link'),
			transparent 	= header.is('.av_header_transparency'),
			shrinking		= header.is('.av_header_shrinking'),
			shrink_factor	= header.data( 'av_shrink_factor' ),
			shrink_val		= el_height/2.0,
			el_shrinked		= el_height/2.0,
			header_meta		= header.find('#header_meta'),
			topbar_height	= header_meta.length ? header_meta.outerHeight() : 0,
			header_alternate = header.find('#header_main_alternate'),
			alternate_header_height = header_alternate.length ? header_alternate.outerHeight() : 0,

			set_height = function()
			{
				var st = win.scrollTop(),
					newH = 0,
					st_real = st;

				if( unsticktop )
				{
					st -= topbar_height;
				}

				if( st < 0 )
				{
					st = 0;
				}

				if( shrinking && ! isMobile )
				{
					if( st < shrink_val )
					{
						newH = el_height - st;
						if( st <= 0 )
						{
							newH = el_height;
						}

						av_change_class( header, 'remove', 'header-scrolled' );
						//header.removeClass('header-scrolled');
					}
					else
					{
						newH = el_shrinked;
						//header.addClass('header-scrolled');
						av_change_class( header, 'add', 'header-scrolled' );
					}

					if( st - 30 < el_height )
					{
						av_change_class( header, 'remove', 'header-scrolled-full' );
					}
					else
					{
						av_change_class( header, 'add', 'header-scrolled-full' );
					}

					elements.css({'height': newH + 'px', 'lineHeight': newH + 'px'});
					logo.css({'maxHeight': newH + 'px'});

					// (solution contributed by Guenni007)
					let propHeight = newH + alternate_header_height;

					if( header.length )
					{
						if( ! unsticktop.length )
						{
							propHeight += topbar_height;
						}
						else
						{
							if( st <= topbar_height )
							{
								propHeight = propHeight + topbar_height - st_real;
							}
						}
					}

					$(':root')[0].style.setProperty( '--enfold-header-height', Math.round( propHeight ) + 'px' );
				}

				if( unsticktop.length )
				{
					if( st <= 0 )
					{
						if( st_real <= 0 )
						{
							st_real = 0;
						}

						unsticktop.css({"margin-top":"-"+st_real+"px"});
					}
					else
					{
						unsticktop.css({"margin-top":"-"+topbar_height+"px"});
					}
				}

				if( transparent )
				{
					if( st > 50 )
					{
						//header.removeClass('av_header_transparency');
						av_change_class( header, 'remove', 'av_header_transparency' );
					}
					else
					{
						//header.addClass('av_header_transparency');
						av_change_class( header, 'add', 'av_header_transparency' );
					}
				}
			};

		if( typeof shrink_factor != 'undefined' )
		{
			const value = parseInt( shrink_factor );
			if( ! isNaN( value ) )
			{
				shrink_val = el_height * ( value / 100.0 );
				el_shrinked = el_height - shrink_val;
			}
		}

		if( $('body').is( '.avia_deactivate_menu_resize' ) )
		{
			shrinking = false;
		}

		if( ! transparent && ! shrinking && ! unsticktop.length )
		{
			return;
		}

		win.on( 'debouncedresize',  function()
		{
			el_height = $( elements ).attr( 'style',"" ).first().height();
			set_height();
		});

		win.on( 'scroll',  function()
		{
			window.requestAnimationFrame( set_height );
		});

		set_height();
    }

})(jQuery);
