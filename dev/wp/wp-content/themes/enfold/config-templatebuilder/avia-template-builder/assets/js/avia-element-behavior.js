(function($)
{
	"use strict";

	$.AviaElementBehavior = $.AviaElementBehavior || {};

	$( function()
	{
		// can be removed once ie7 and 8 are dead and mobile browsers understand the css pseudo selector :checked
    	$.AviaElementBehavior.image_radio();

		//	allows to change labels for a checkbox depending on checked / unchecked
		$.AviaElementBehavior.checkbox_label_change();

    	// can be removed once all browser support css only tabs (:target support needed)
    	$.AviaElementBehavior.tabs('.avia-tab-container');

    	//sets the input hidden field that contains the final icon value
    	$.AviaElementBehavior.icon_select();

    	//builds the side panel that holds the layout builder elements
    	$.AviaElementBehavior.expand_metabox();

    	//show/hide dependent elements
    	$.AviaElementBehavior.check_dependencies();

    	//set another elements property
    	$.AviaElementBehavior.set_target_property();

    	//fetch a php template and append it to an element
    	$.AviaElementBehavior.tmpl_fetcher();

    	//functionallity that controlls the redo and undo buttons
    	$.AviaElementBehavior.redo_undo();

    	//image insert functionality located in avia-media.js
    	$.AviaElementBehavior.wp_media_advanced();

    	//functionallity that fetches the google maps coordinates
    	$.AviaElementBehavior.gmaps_fetcher();

    	if(typeof $.AviaElementBehavior.wp_save_template == 'function')
    	{
			//save template functionality avia-template-saving.js
			new $.AviaElementBehavior.wp_save_template();
        }

		/**
		 * Delegated from the document rather than from #avia_builder, because the element panel is not
		 * always inside it: in the sidebar layout it is attached to <body> so it can stay pinned while
		 * the editor scrolls. The data attributes these look for are the theme's own, so widening the
		 * root does not pick up anything that was not already a builder tooltip.
		 */
        //default tooltips for various elements like shortcodes
    	new $.AviaTooltip({attach:'body', scope:'body'});

    	 //tooltips for the help icon
    	new $.AviaTooltip({'class': 'avia-help-tooltip', data: 'avia-help-tooltip', event:'click', position:'bottom', attach:'body', scope:'body'});

		/*
		 * The row of icons at the top of the element panel.
		 *
		 * Their own kind of hint, for two reasons. They are named rather than explained - three words
		 * at most - so the hint is drawn smaller than the ones describing an element. And they are
		 * three buttons side by side that a user reads along in one movement, where a wait before each
		 * one is what makes a row of icons tiring rather than helpful: these appear at once.
		 */
		new $.AviaTooltip({'class': 'avia-panel-tooltip', data: 'avia-panel-tooltip', delay: 0, attach:'body', scope:'body'});

	});


	$.AviaElementBehavior.gmaps_fetcher =  function()
	{
		var map_api 		= '',
			loading 		= false,
			clicked			= {},
			timeout_check 	= false,
			timout_timer	= 1500;

			if( 'undefined' == typeof avia_framework_globals.gmap_builder_maps_loaded || avia_framework_globals.gmap_builder_maps_loaded == '' )
			{
						//	this is only for fallback
				map_api = 'https://maps.googleapis.com/maps/api/js?v=3.59&loading=async&libraries=marker&callback=av_builder_maps_loaded';
				if( avia_framework_globals.gmap_api != 'undefined' && avia_framework_globals.gmap_api != "" )
				{
					map_api += "&key=" + avia_framework_globals.gmap_api;
				}
			}
			else
			{
				map_api = avia_framework_globals.gmap_builder_maps_loaded;
			}

		$("body").on('click', '.avia-js-google-coordinates', function()
		{
			clicked = this;

			//load the maps script if google maps is not loaded
			if((typeof window.google == 'undefined' || typeof window.google.maps == 'undefined') && loading == false)
			{
				loading = true;
				var script 	= document.createElement('script');
				script.type = 'text/javascript';
				script.src 	= map_api;

      			document.body.appendChild(script);
			}
			else if(typeof window.google != 'undefined' && typeof window.google.maps != 'undefined')
			{
				window.av_builder_maps_loaded();
			}

			return false;
		});



		window.av_builder_maps_loaded = function(data)
		{
			//data array can also be passed
			if(typeof data == 'undefined')
			{
				data = {};
				data.clicked 			= $(clicked);
				data.parent  			= data.clicked.parents('div').eq( 0 ),
				data.long  				= data.parent.find('#long');
				data.lat  				= data.parent.find('#lat');
				data.coordinatcontainer = data.parent.find('.av-gmap-coordinates');
				data.inputs  			= data.parent.find('.av-gmap-addres input'),
				data.address 			= data.inputs.map(function(){ return this.value; }).get().join( " " );
			}

			//reset click var
			clicked	= false;

			var geocoder 	= new google.maps.Geocoder(),
				addressGeo	= data.address,
				coordinates = {},
				executed	= false;


			geocoder.geocode( { 'address': addressGeo}, function(results, status)
            {
	            executed = true;

                if(status == google.maps.GeocoderStatus.OK)
                {
                    coordinates.latitude = results[0].geometry.location.lat();
                    coordinates.longitude = results[0].geometry.location.lng();

                    data.long.val(coordinates.longitude);
                    data.lat.val( coordinates.latitude );
                }
                else if(status == google.maps.GeocoderStatus.ZERO_RESULTS)
                {
                    if( !addressGeo.replace(/\s/g, '').length)
                    {
	                    new $.AviaModalNotification({mode:'error', msg:avia_modal_L10n.insertaddress});
                    }
                    else
                    {
                         new $.AviaModalNotification({mode:'error', msg:avia_modal_L10n.notfound});
                    }
                }
                else if(status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT)
                {
	                new $.AviaModalNotification({mode:'error', msg:avia_modal_L10n.toomanyrequests});
                }
                else if(status == google.maps.GeocoderStatus.REQUEST_DENIED)
                {
	                new $.AviaModalNotification({mode:'error', msg:avia_modal_L10n.gmap_api_text});
                }

                data.coordinatcontainer.addClass('av-visible');

            });

            //check if the google geocoder has requested the data
            if(timeout_check === false)
            {
	            timeout_check = setTimeout(function(){

		            if(executed === false)
		            {
			           new $.AviaModalNotification({mode:'error', msg:avia_modal_L10n.gmap_api_wrong});
			           timeout_check = false;
			           timout_timer = 0; //consecutive requests should be show instantly
		            }
	            }, timout_timer);
            }
		};
	};

	// since css only tabs are not fully working by now this script adds tab behavior to a tab container of choice
	$.AviaElementBehavior.tabs = function(tab_container)
	{
		$( tab_container ).each(function(i)
		{
			var active_tab = 0,
				id = "avia_post_"+ i + "_" + avia_globals.post_id,
				storage = typeof sessionStorage !== 'undefined';

			if( storage )
			{
				try
				{
					active_tab = sessionStorage.getItem( id ) || 0;
				}
				catch( err )
				{
					avia_log( 'Info - Session Storage: Browser memory limit reached, blocked or not supported. We are not able to save the state of the last open options tabs in modal popup windows of ALB elements.' );
					avia_log( err );
				}
			}

			var current = $(this),
				links = current.find('.avia-tab-title-container a').not( '.avia-fake-tab'),
				tabs = current.find('.avia-tab'),
				currentLink;

			links.off( 'click' ).on( 'click', function()
			{
				links.removeClass('active-tab');
				currentLink = $(this).addClass('active-tab');

				var index = links.index( currentLink );

				tabs.css( {display:'none'} ).eq( index ).css( {display:'block'} );

				if( storage )
				{
					try
					{
						sessionStorage.setItem( id, index );
					}
					catch( err )
					{
						avia_log( 'Info - Session Storage: Browser memory limit reached, blocked or not supported. We are not able to save the last open tab.' );
						avia_log( err );
					}
				}

				//	trigger custom event as we removed all standard click events
				$( this ).trigger( 'avia-tab-title-container-clicked', [ this ] );

				return false;
			});

			if( ! links.filter('.active-tab').length )
			{
				links.eq( active_tab ).addClass('active-tab').trigger('click');
			}
		});
	};


	// necessary for image radiobutton
	$.AviaElementBehavior.image_radio  = function()
	{
		$('.avia_scope input[type="radio"]:checked').parents('.avia_radio_wrap').eq( 0 ).addClass('avia_checked');

		$("body").on( "click", ".avia_scope input[type='radio']", function(event)
		{
			var $parent = $(this).parents('.avia_radio_wrap').eq( 0 );
			$parent.siblings('.avia_radio_wrap').removeClass('avia_checked').end().addClass('avia_checked');
		});
	};

	/**
	 * adds functionallity to the font based icon selector. Supports both icon font and svg icon sets added with 7.0
	 * When an item is clicked it stores the item nr and if possible the item html code.
	 * In case of svg this is the <img> tag to the svg file
	 */
	$.AviaElementBehavior.icon_select =  function()
	{
		$("body").on('click', '.avia-attach-element-select', function()
		{
			var clicked = $(this),
				parent  = clicked.parents('.avia-attach-element-container').eq( 0 ),
				old 	= parent.find('.avia-active-element').removeClass('avia-active-element'),
				input	= parent.find('input[type=hidden]').eq( 0 ),
				icon 	= parent.find('input[type=hidden]').eq( 1 ),
				font 	= parent.find('input[type=hidden]').eq( 2 );

				clicked.addClass('avia-active-element');
				input.val(clicked.data('element-nr'));

				if( icon.length )
				{
					let content = clicked.html();

					if( content.indexOf( '<svg') !== -1 )
					{
						//	decode to base64 - this allows to add it to attribute, encode and add to DOM element innerHTML to show svg
						content = '###avia64###:' + btoa( content );
					}

					icon.val( content );
				}

				if( font.length )
				{
					font.val( clicked.data('element-font') );
				}

				//window.prompt ("Copy to clipboard: Ctrl+C, Enter", clicked.data('element-nr'));
				//clicked.css({display:'none'});

				input.trigger('change');
				return false;
		});
	};

	//	builds the side panel that holds the layout builder elements
	$.AviaElementBehavior.expand_metabox =  function()
	{
		var the_body			= $("body"),
			parent, container, tab_container, button_container,
			//	controls rendered next to the element panel that belong inside it
			panel_control_selector = '#avia-sort-list-dropdown, .avia-hotkey-info, .avia-alb-fullscreen',
			panel_controls = [],
			//	element sections moved under their own heading to form the sidebar accordion
			accordion_sections = [],
			//	theme option: show the elements in a side panel instead of the toolbar across the top
			sidebar_layout = $('body').is('.avia-alb-panel-sidebar'),
			//	editor chrome the panel has to clear, measured rather than assumed - see sync_chrome_offset()
			chrome_top_selectors = [ '.interface-interface-skeleton__header', '#wpadminbar' ],
			chrome_left_selectors = [ '#adminmenuwrap', '#adminmenuback' ],
			//	last written offsets, so an unchanged measurement costs nothing
			measured_top = null,
			measured_start = null,
			sync_frame = null,
			//	true while a settle loop is watching, so a second one is not started on top of it
			settling = false;

		if( sidebar_layout )
		{
			build_sidebar_panel();
		}

		/**
		 * Builds the side panel once, when the page loads.
		 *
		 * The sidebar is the layout of the builder: the elements are meant to be within reach whenever
		 * the builder is on screen, so it is built once and stays. Whatever fullscreen the editor
		 * itself offers only changes how much room the canvas gets - the panel measures the chrome
		 * that is actually there and follows it.
		 *
		 * Nothing here depends on the builder being switched on. The metabox is rendered either way, and
		 * the stylesheet hides the panel while the default WordPress editor is in use, so switching
		 * between the two editors needs no rebuild.
		 */
		function build_sidebar_panel()
		{
			parent = $('#avia_builder');

			if( ! parent.length )
			{
				return;
			}

			tab_container = parent.find('.avia-tab-container').first();

			if( ! tab_container.length )
			{
				tab_container = null;
				return;
			}

			container = $('<div class="avia-fixed-controls avia-alb-panel-side"></div>').appendTo( the_body );
			the_body.addClass( 'avia-alb-sidebar-open' );

			tab_container.appendTo( container );

			create_button_row();
			move_panel_controls();
			move_template_button();
			build_accordion();
			watch_layout();
		}

		/**
		 * Moves the template button into the canvas toolbar next to undo and redo.
		 *
		 * It is rendered as a sibling of the element panel and pinned to the corner of it. With the
		 * panel gone to the side there is nothing left for it to hang off, so it ends up floating over
		 * the canvas. The undo/redo bar is where it belongs anyway - both act on the layout, not on the
		 * element list.
		 *
		 * Safe to move: the template script keeps a reference to the node itself and decides what counts
		 * as an outside click by walking up from the event, so neither depends on where it sits.
		 */
		function move_template_button()
		{
			var control_bar = parent.find( '.layout-builder-wrap .avia-controll-bar' ).first(),
				save_button = parent.find( '.avia-template-save-button-container' ).first();

			if( ! control_bar.length || ! save_button.length )
			{
				return;
			}

			//	the class is what the stylesheet hooks, so its offsets can outrank the corner pinning
			save_button.addClass( 'avia-in-control-bar' ).appendTo( control_bar );
		}

		/**
		 * The sorting dropdown and the hotkey info belong to the element panel but are rendered as its
		 * siblings, so while expanded they used to be pinned to the viewport with their own coordinates
		 * and had to be re-tuned for every editor chrome and scroll state.
		 *
		 * They move into the same row as the publish/preview/close buttons, which is laid out as a flex
		 * row. Everything in the bar's top right corner is then placed by that one row: no element has
		 * to know how tall the editor chrome is, how much padding the bar has, or how many buttons the
		 * editor next to it happens to render.
		 */
		function move_panel_controls()
		{
			var target = button_container && button_container.length ? button_container : tab_container;

			parent.find( panel_control_selector ).each( function()
			{
				var control = $(this);

				panel_controls.push({
							element: control,
							placeholder: $('<span class="avia-panel-control-placeholder" style="display:none"></span>').insertBefore( control )
						});

				//	the class is what the stylesheet hooks, so the row can outrank the corner pinning
				control.addClass( 'avia-in-panel-row' );

				//	before the buttons - sorting and help sit left of publish/preview/close
				control.prependTo( target );
			});
		}

		/**
		 * Turns the tab strip into an accordion for the sidebar.
		 *
		 * A row of tabs does not survive being narrowed to the width of a side panel, so each section
		 * of elements is moved to sit directly underneath its own heading. The headings stay where they
		 * are inside .avia-tab-title-container, because the sorting, the keyboard shortcuts and the
		 * custom element scripts all look them up through it.
		 *
		 * No click handling is added: showing one section and hiding the rest is what the tab script
		 * already does, and that reads as an accordion once each heading sits above its own section.
		 */
		function build_accordion()
		{
			if( ! sidebar_layout )
			{
				return;
			}

			var titles = tab_container.find( '.avia-tab-title-container' ).first();

			if( ! titles.length )
			{
				return;
			}

			//	same pairing the tab script uses - heading n belongs to section n
			var headings = titles.find( 'a' ).not( '.avia-fake-tab' );
			var sections = tab_container.find( '.avia-tab' );

			headings.each( function( index )
			{
				var section = sections.eq( index );

				if( ! section.length )
				{
					return;
				}

				accordion_sections.push({
							element: section,
							placeholder: $('<span class="avia-alb-section-placeholder" style="display:none"></span>').insertBefore( section )
						});

				section.insertAfter( $(this) );
			});

			attach_accordion_collapse( headings );

			tab_container.addClass( 'avia-alb-accordion' );
		}

		/**
		 * Lets a click on the open section close it again.
		 *
		 * The tab script only ever switches sections, and it stops the event, so this cannot be done
		 * from a delegated handler further up. Instead the state is read in the capture phase - before
		 * any jQuery handler has run - and a handler bound directly on the heading afterwards closes
		 * the section again when it was already open. Binding order matters: this runs after the tab
		 * script has opened it, and stopping propagation does not stop handlers on the same element.
		 */
		function attach_accordion_collapse( headings )
		{
			headings.each( function()
			{
				var heading = $(this);

				this.addEventListener( 'click', function()
				{
					heading.data( 'avia-was-open', heading.hasClass( 'active-tab' ) );
				}, true );

				heading.on( 'click.aviaAccordion', function()
				{
					if( ! heading.data( 'avia-was-open' ) )
					{
						return false;
					}

					heading.removeClass( 'active-tab' );
					heading.next( '.avia-tab' ).css( 'display', 'none' );

					sync_custom_element_footer();

					return false;
				});
			});
		}

		/**
		 * Takes the custom element buttons away while no custom element section is open.
		 *
		 * They are a footer to the whole strip rather than part of a section, so the custom element
		 * script only hides them when another heading is opened. Closing a section is not opening
		 * another one, and buttons for editing a list that is no longer on screen only mislead.
		 */
		function sync_custom_element_footer()
		{
			var footer = tab_container.find( '.av-custom-element-footer' );

			if( footer.length && ! tab_container.find( 'a.avia-custom-element-tab.active-tab' ).length )
			{
				footer.hide();
			}
		}

		/**
		 * True while a dialog is holding the page still.
		 *
		 * A modal locks the page behind it, and in a browser the only way to do that is to take the
		 * scrollbar off <body> - so a locked body means something is open over the editor. The builder's
		 * own modals do it through the avia-noscroll class, but nothing here depends on that name: any
		 * dialog that locks the page the usual way reads the same.
		 *
		 * Both editors were measured for this. Neither locks the body on its own, and Gutenberg's
		 * fullscreen mode - the one transition the panel really has to follow - leaves it scrollable,
		 * so nothing the editor does by itself is mistaken for a dialog.
		 */
		function dialog_is_open()
		{
			var overflow = window.getComputedStyle( document.body ).overflowY;

			return 'hidden' == overflow || 'clip' == overflow;
		}

		/**
		 * Publishes the height of the editor chrome as a custom property so the sidebar can start below
		 * it and the canvas can be inset by it.
		 *
		 * Measured, not hardcoded: the admin bar, the block editor header and the editor's own
		 * fullscreen mode each change it, and every WordPress release is free to change it again.
		 */
		function sync_chrome_offset()
		{
			if( ! sidebar_layout )
			{
				return;
			}

			/*
			 * Whatever a dialog rearranges is not the editor chrome, so there is nothing here worth
			 * re-reading while one is open - and plenty to get wrong, since a dialog is pinned over the
			 * page much as a plugin's admin bar is. Holding the last known offsets is also what the panel
			 * wants visually: it should stay exactly where the user left it while they work in the modal.
			 *
			 * Closing the dialog unlocks the body, which is itself a class change on <body>, so the
			 * observer below measures again the moment it is gone.
			 */
			if( dialog_is_open() )
			{
				return false;
			}

			/*
			 * Each of these returns 0 when the thing it looks for is not on screen, so the editor's own
			 * fullscreen mode - which takes the admin bar and the admin menu away - needs no special
			 * case here: the panel simply measures its way into the corner.
			 */
			var top = measure_edge( chrome_top_selectors, 'bottom' ),
				start = measure_inline_start_edge();

			top = measure_pinned_top_bars( top );

			top = Math.round( top );
			start = Math.round( start );

			//	nothing moved - skip the write, which is what makes the whole thing expensive
			if( top === measured_top && start === measured_start )
			{
				return false;
			}

			measured_top = top;
			measured_start = start;

			/**
			 * Written on the panel, not on <body>.
			 *
			 * Only the panel reads these, and a style change on <body> marks the whole document dirty:
			 * the next thing that reads a layout value then has to re-measure every element on the page,
			 * the block editor's own tree included. Measured on a normal page that is ~144ms per call
			 * against ~4ms when the same value is written here, which is the difference between the
			 * panel lagging behind the editor and keeping up with it.
			 */
			var style = container.get(0).style;

			style.setProperty( '--av-alb-chrome-top', top + 'px' );
			style.setProperty( '--av-alb-chrome-start', start + 'px' );

			return true;
		}

		/**
		 * Keeps measuring until the numbers hold still, then hands over to the callback.
		 *
		 * The editor chrome is not in place when this starts - the block editor renders its header after
		 * the metaboxes, and entering or leaving fullscreen takes the admin bar and menu away and puts
		 * them back over several frames. Rather than guessing a delay, this watches until two readings
		 * in a row agree, with a cap so it can never spin.
		 */
		function settle_offsets( on_settled )
		{
			var unchanged = 0,
				frames = 0,
				finished = false;

			settling = true;

			function finish()
			{
				if( finished )
				{
					return;
				}

				finished = true;
				settling = false;

				if( on_settled )
				{
					on_settled();
				}
			}

			/**
			 * Browsers stop running animation frames in a background tab, so a page opened in one would
			 * never finish settling and the panel would stay hidden. Timers keep running there, so this
			 * gives up waiting and shows it anyway - the next resize or fullscreen change corrects the
			 * offsets if they were read too early.
			 */
			setTimeout( function()
			{
				sync_chrome_offset();
				finish();
			}, 3000 );

			function step()
			{
				if( finished )
				{
					return;
				}

				if( sync_chrome_offset() )
				{
					unchanged = 0;
				}
				else
				{
					unchanged ++;
				}

				frames ++;

				/**
				 * The reading has to hold still for a while, not just for a frame or two. Entering
				 * fullscreen animates the editor chrome over several hundred milliseconds, and a couple
				 * of matching frames part way through that is a plateau, not the final position -
				 * stopping there leaves the panel measured against a layout that no longer exists.
				 * Watching costs nothing while nothing changes, so it is worth being patient.
				 */
				if( unchanged >= 20 || frames >= 120 )
				{
					finish();
					return;
				}

				window.requestAnimationFrame( step );
			}

			step();
		}

		/**
		 * Reveals the panel once it is in the right place, then allows it to animate.
		 *
		 * Two steps on purpose: showing it and enabling the transition in the same frame would make the
		 * first appearance slide in from wherever it happened to start.
		 */
		function mark_panel_ready()
		{
			if( ! container || ! container.length )
			{
				return;
			}

			container.addClass( 'avia-alb-panel-ready' );

			window.requestAnimationFrame( function()
			{
				container.addClass( 'avia-alb-panel-animated' );
			});
		}

		/**
		 * Coalesces bursts of events into one measurement per frame. Resize fires far faster than the
		 * layout can usefully be recalculated.
		 */
		function request_sync()
		{
			if( sync_frame )
			{
				return;
			}

			sync_frame = window.requestAnimationFrame( function()
			{
				sync_frame = null;
				sync_layout();
			});
		}

		/**
		 * Largest edge of whichever of these is actually on screen - the admin bar, the block editor
		 * header, the admin menu. Which of them exist depends on the editor, on fullscreen mode and on
		 * whether the menu is folded, so they are measured instead of being written down as numbers.
		 */
		function measure_edge( selectors, edge )
		{
			var found = 0;

			for( var i = 0; i < selectors.length; i++ )
			{
				var el = document.querySelector( selectors[i] );

				if( ! el )
				{
					continue;
				}

				var rect = el.getBoundingClientRect();

				if( rect.width > 0 && rect.height > 0 && rect[ edge ] > found )
				{
					found = rect[ edge ];
				}
			}

			return found;
		}

		/**
		 * How far in from the starting edge of the screen the admin menu reaches.
		 *
		 * The panel is placed with inset-inline-start, so what it needs is the distance from the edge
		 * the text starts at - the left in a left to right admin, the right in a right to left one,
		 * where the admin menu sits on the other side of the window entirely. Reading the direction
		 * from the document rather than from a class: WordPress always sets the attribute, while the
		 * rtl class the theme adds depends on a filter that a site can switch off.
		 *
		 * @returns {number}
		 */
		function measure_inline_start_edge()
		{
			var rtl = 'rtl' == ( document.documentElement.getAttribute( 'dir' ) || '' ).toLowerCase();

			if( ! rtl )
			{
				return measure_edge( chrome_left_selectors, 'right' );
			}

			var found = 0;

			for( var i = 0; i < chrome_left_selectors.length; i++ )
			{
				var el = document.querySelector( chrome_left_selectors[i] );

				if( ! el )
				{
					continue;
				}

				var rect = el.getBoundingClientRect();

				if( rect.width > 0 && rect.height > 0 )
				{
					found = Math.max( found, window.innerWidth - rect.left );
				}
			}

			return found;
		}

		/**
		 * Bottom edge of any bar pinned across the top of the screen, whoever put it there.
		 *
		 * Plugins add their own admin chrome, and a pinned bar cannot be pushed aside by insetting a
		 * container - it is out of the flow, so it stays full width and the panel would sit on top of it.
		 * WooCommerce is the case in hand: its header is fixed and lives beside #wpbody rather than
		 * inside it. Nothing here is named, so a plugin doing the same is handled the same way.
		 *
		 * Only the first few levels of the admin containers are examined, and only bars that are
		 * actually pinned to the top and span most of the width - a narrow floating widget is not chrome.
		 */
		function measure_pinned_top_bars( bottom )
		{
			var roots = [ document.body, document.getElementById( 'wpcontent' ) ];

			/**
			 * A few levels is enough to reach a plugin's own chrome without walking the whole document.
			 *
			 * Size decides whether an element is itself a bar, never whether it is worth looking inside:
			 * a pinned bar is out of the flow, so a wrapper holding nothing else collapses to zero height
			 * and reports no useful width either. WooCommerce nests its header two such wrappers deep.
			 */
			function scan( el, depth )
			{
				var children = el.children;

				for( var i = 0; i < children.length; i++ )
				{
					var child = children[i];

					/*
					 * The panel is pinned to the top itself and sits on <body> beside everything else.
					 * Measuring it would push the edge down to its own bottom and take the panel with
					 * it, over and over - so it is left out, and nothing inside it is admin chrome.
					 */
					if( child.classList && child.classList.contains( 'avia-fixed-controls' ) )
					{
						continue;
					}

					var rect = child.getBoundingClientRect();

					/*
					 * Stacks on what is already known: a plugin bar usually starts where the admin bar
					 * ends rather than at the very top, so "pinned" means it spans most of the width,
					 * begins at or above the bottom edge found so far, and pushes that edge further down.
					 *
					 * It also has to be a strip rather than a panel. Chrome is thin - the admin bar is
					 * 32px, the block editor header 64, the WooCommerce one 60 - while a dialog pinned
					 * over the page runs to hundreds and would push the panel off the bottom of the
					 * screen. A quarter of the height sits far above anything that is really chrome and
					 * far below anything that is really a dialog, and covers the case of a sheet laid
					 * over the whole page as well.
					 *
					 * This is the second of the two guards against overlays. A dialog that locks the
					 * page never reaches here at all, because nothing is measured while one is open -
					 * but an overlay that leaves the page scrollable still has to be turned away, and
					 * its size is what gives it away.
					 */
					if( rect.height > 0 && rect.height <= window.innerHeight / 4 && rect.width >= window.innerWidth * 0.5 && rect.top <= bottom + 2 && rect.bottom > bottom )
					{
						var style = window.getComputedStyle( child );

						if( 'fixed' == style.position || 'sticky' == style.position )
						{
							bottom = rect.bottom;
							continue;
						}
					}

					if( depth > 0 )
					{
						scan( child, depth - 1 );
					}
				}
			}

			for( var r = 0; r < roots.length; r++ )
			{
				if( roots[r] )
				{
					scan( roots[r], 3 );
				}
			}

			return bottom;
		}

		/**
		 * Starts following the editor chrome.
		 *
		 * The panel is pinned to the viewport, so all it needs is where the chrome ends - and that
		 * changes when the window is resized, when the admin menu is folded, and when the editor
		 * enters or leaves its own fullscreen mode.
		 */
		function watch_layout()
		{
			//	measure until the editor stops moving, then let the panel be seen and let it animate
			settle_offsets( mark_panel_ready );

			$(window).on( 'resize.aviaAlbPanel', request_sync );

			watch_editor_chrome();
		}

		/**
		 * Re-measures when the editor rearranges itself without the window changing size.
		 *
		 * Entering the block editor's fullscreen mode takes the admin bar and the admin menu away, and
		 * folding the menu narrows it. Neither fires a resize, because the window itself never changes -
		 * but both rewrite the class on <body>. Watching that rather than one editor's own state keeps
		 * this working in the classic editor too, and for anything else that moves the chrome about.
		 */
		function watch_editor_chrome()
		{
			if( typeof MutationObserver == 'undefined' )
			{
				return;
			}

			var last_class = document.body.className;

			new MutationObserver( function()
			{
				//	the editor rewrites the attribute far more often than it changes it
				if( document.body.className === last_class )
				{
					return;
				}

				last_class = document.body.className;

				/*
				 * A settle loop that is already running sees the change for itself - it keeps measuring
				 * until the readings hold still, which is what a fullscreen transition needs anyway.
				 */
				if( ! settling )
				{
					settle_offsets();
				}

			}).observe( document.body, { attributes: true, attributeFilter: [ 'class' ] } );
		}

		function sync_layout()
		{
			sync_chrome_offset();
		}

		/**
		 * The row along the top of the panel. The sorting dropdown and the keyboard shortcut help are
		 * moved into it, which is what keeps them from needing coordinates of their own.
		 */
		function create_button_row()
		{
			if( ! button_container || ! button_container.length )
			{
				button_container = $('<div class="avia-alb-panel-row"></div>').appendTo(tab_container);
			}

			return button_container;
		}
	};

	//	allows to change labels for a checkbox depending on checked / unchecked
	$.AviaElementBehavior.checkbox_label_change = function()
	{
		$('body').on( 'change', '.avia-style input[type=checkbox]', function( e )
		{
			var current = $(this),
				container = current.closest( '.avia-form-element-container');

			if( current.hasClass( 'avia-locked-data-value' ) || current.hasClass( 'avia-locked-data-hide' ) )
			{
				return;
			}

			if( container.length == 0 || ! container.hasClass( 'avia-checkbox-label-change' ) )
			{
				return;
			}

			var label = container.find( 'label' ),
				checked = container.data('checkbox-checked'),
				unchecked = container.data('checkbox-unchecked');

			if( label.length == 0 )
			{
				return;
			}

			if( current.prop( 'checked' ) )
			{
				if( 'undefined' != typeof checked )
				{
					label.html( checked );
				}
			}
			else
			{
				if( 'undefined' != typeof unchecked )
				{
					label.html( unchecked );
				}
			}
		});
	};

	//dependency checker for select elements
	$.AviaElementBehavior.check_dependencies = function()
	{
		var the_body = $("body");

		the_body.on('change', '.avia-style select, .avia-style textarea, .avia-style radio, .avia-style input[type=checkbox], .avia-style input[type=hidden], .avia-style input[type=text], .avia-style input[type=radio]', function()
		{
			var current = $(this),
				scope = current.parents('.avia-modal').eq( 0 );

			if( ! scope.length )
			{
				scope = the_body;
			}

			var id			= this.id.replace(/aviaTB/g,""),
				dependent	= scope.find('.avia-form-element-container[data-check-element="'+id+'"]'),
				value1		= this.value,
				is_hidden	= current.parents('.avia-form-element-container').eq( 0 ).is('.avia-hidden'),
				parent_val  = '',
				locked_value = current.data( 'locked_value' );

			//	check for a locked value - replaces entered value
			if( 'undefined' != typeof locked_value && locked_value != '$$undefined$$' )
			{
				value1 = locked_value;
			}

			if( '' == id )
			{
				return;
			}

			if( current.is('input[type=checkbox]') && ! current.prop('checked') )
			{
				value1 = "";
			}

			if( current.is('input[type=radio]') )
			{
				var name = this.name.replace(/aviaTB/g,"");
				dependent = scope.find('.avia-form-element-container[data-check-element="'+name+'"]');
			}

			//	Get value of parent element when depending subelements are changed
			var parent_element = current.closest('.avia-form-element-container').find( '#' + this.id ).eq( 0 );

			if( parent_element.is('input[type=checkbox]') )
			{
				parent_val = parent_element.prop('checked') ? parent_element.val() : '';
			}
			else if( parent_element.is('input[type=radio]' ) )
			{
				if( '' === parent_val )
				{
					parent_val = parent_element.prop('checked');
				}
			}
			else
			{
				parent_val = parent_element.val();
			}

			if( ! dependent.length )
			{
				return;
			}

			dependent.each( function()
			{
				var current		= $(this),
					check_data	= current.data(),
					value2		= check_data.checkValue.toString(),
					show		= false;

				if(! is_hidden )
				{
					switch( check_data.checkComparison )
					{
						case 'equals': 			if(value1 == value2) show = true; break;
						case 'not': 			if(value1 != value2) show = true; break;
						case 'is_larger': 		if(value1 >  value2) show = true; break;
						case 'is_smaller': 		if(value1 <  value2) show = true; break;
						case 'contains': 		if(value1.indexOf(value2) !== -1) show = true; break;
						case 'doesnt_contain':  if(value1.indexOf(value2) === -1) show = true; break;
						case 'starts_with':		if(value1.indexOf(value2) === 0) show = true; break;
						case 'is_empty_or':  	if(value1 === "" || value1 === value2) show = true; break;
						case 'not_empty_and':  	if(value1 !== "" && value1 !== value2) show = true; break;
						case 'parent_in_array':
							show = ( -1 !== $.inArray( parent_val, value2.split( ',' ) ) );
							break;
						case 'parent_not_in_array':
							show = ( -1 === $.inArray( parent_val, value2.split( ',' ) ) );
							break;
					}
				}

				if( show === true && current.is('.avia-hidden') )
				{
					current.css( {display:'none'} ).removeClass('avia-hidden').find('select, radio, input[type=checkbox]').trigger('change');
					current.slideDown(300);
				}
				else if( show === false  && ! current.is('.avia-hidden') )
				{
					current.css( {display:'block'} ).addClass('avia-hidden').find('select, radio, input[type=checkbox]').trigger('change');
					current.slideUp(300);
				}
			});
		});
	};

	//target setter for elements
	$.AviaElementBehavior.set_target_property = function()
	{
		var the_body = $("body"),
			container = "";

		the_body.on('change', '.avia-style select, .avia-style radio, .avia-style input[type=checkbox]', function()
		{
			var current = $(this),
				wrapper = current.parents('.avia-form-element-container').eq( 0 ),
				scope	= current.parents('.avia-modal').eq( 0 ),
				data 	= wrapper.data(),
				options = "";

			//	added to support styling outside modal window context
			if( ! wrapper.length )
			{
				return;
			}

			if( ! data.targetElement )
			{
				return;
			}

			if( ! scope.length )
			{
				scope = the_body;
			}

			if( current.is('select') )
			{
				options = current.find('option').map( function(){ return this.value; } ).get().join(" ");
			}

			var target = the_body.find( data.targetElement ),
				new_value = this.value;

			if( ! target.length )
			{
				return;
			}

			target.each( function()
			{
				var current_target = $(this);

				switch(data.targetProperty)
				{
					case 'class':
						current_target.removeClass(options).addClass(new_value);
						break;
					case 'id':
						current_target.attr({'id': new_value});
						break;
				}
			});
		});


		the_body.on('avia_modal_finished', function(event, window)
		{
			window.modal.find(".avia-attach-targeting select,.avia-attach-targeting radio,.avia-attach-targeting input[type=checkbox]").trigger('change');
		});

	};

	//template fetchter for elements
	$.AviaElementBehavior.tmpl_fetcher = function()
	{
		var the_body = $("body"), container = "";

		the_body.on('change', '.avia-attach-templating select, .avia-attach-templating radio, .avia-attach-templating input[type=checkbox]', function()
		{
			var current = $(this),
				css_id	= current.attr('id'),
				wrapper = current.parents('.avia-form-element-container').eq( 0 ),
				scope	= current.parents('.avia-modal').eq( 0 ),
				target  = current.next('.template-container');

			if( ! scope.length )
			{
				scope = the_body;
			}

			if( ! target.length )
			{
				return;
			}

			var new_value 	= this.value,
				temp_string = "#avia-tmpl-"+css_id+'-'+new_value,
				template	= $(temp_string);

				if( ! template.length )
				{
					if(avia_globals.builderMode && avia_globals.builderMode == "debug")
					{
						avia_log('template snippet "'+temp_string+'" not defined','error');
   						avia_log('Make sure that the you have created the template and check the source code if its really available','help');
					}

					template = $('<div />');
				}

				target.html( template.html() ); //.find('select, input, radio').trigger('change');
		});


		the_body.on('avia_modal_finished', function(event, window)
		{
			window.modal.find(".avia-attach-templating select, .avia-attach-templating radio, .avia-attach-templating input[type=checkbox]").trigger('change');
		});
	};

	//redo and undo buttons
	$.AviaElementBehavior.redo_undo =  function()
	{
		var el_storage = new $.AviaElementBehavior.history( {
						monitor: "#aviaLayoutBuilder",
						editor:	 "#_aviaLayoutBuilderCleanData",
						buttons: ".layout-builder-wrap .avia-controll-bar"
					});
	};

})(jQuery);
