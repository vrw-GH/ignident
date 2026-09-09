(function($)
{
	"use strict";

	/**
	 * Hover and click hints for the layout builder.
	 *
	 * Shown in the top layer through the popover API, which is what keeps them out of trouble: the
	 * element panel scrolls and carries a backdrop filter, and either of those on its own is enough
	 * to clip a hint drawn inside the panel or to trap it in the panel's coordinate space. The top
	 * layer sits outside every ancestor, so neither can reach it and no z-index is involved.
	 *
	 * @since 8.0
	 * @param {object} options
	 */
	$.AviaTooltip = function( options )
	{
		var defaults = {
					delay:		1500,				//delay in ms until the tooltip appears
					delayOut:	300,				//time in ms in which the next tooltip opens without the delay
					"class":	'avia-tooltip',		//tooltip classname for css styling and alignment
					scope:		'#avia_builder',	//area the tooltip should be applied to
					data:		'avia-tooltip',		//data attribute that contains the tooltip text
					event:		'mouseenter',		//mouseenter and leave or click and leave
					position:	'top',				//top or bottom
					gap:		2					//px between the tip of the arrow and the element
				};

		this.options		= $.extend( {}, defaults, options );
		this.body			= $('body');
		this.scope			= $(this.options.scope);
		this.tooltip		= null;
		this.anchor			= null;
		this.timer			= false;
		this.instant_timer	= false;
		this.instant		= false;

		this.bind_events();
	};

	$.AviaTooltip.prototype =
	{
		bind_events: function()
		{
			var obj = this,
				selector = '[data-' + this.options.data + ']';

			if( 'click' == this.options.event )
			{
				this.scope.on( 'click', selector, function( e )
				{
					e.preventDefault();
					obj.toggle( this );
				});

				//	a press anywhere else closes it - but not on the element it belongs to, which toggles
				this.body.on( 'mousedown', function( e )
				{
					if( obj.tooltip && ! obj.tooltip.contains( e.target ) && ! $( e.target ).closest( selector ).length )
					{
						obj.close();
					}
				});
			}
			else
			{
				this.scope.on( 'mouseenter', selector, function( e )
				{
					obj.schedule( e, this );
				});

				this.scope.on( 'mouseleave', selector, function()
				{
					obj.leave();
				});

				/**
				 * A hint explains what you are about to do, so it has no business being on screen once
				 * you are doing it. Dragging never sends a mouseleave either - the element is cloned to
				 * follow the cursor and the clone carries the same attribute - so without this the hint
				 * opens again over the canvas and is left behind when the clone is thrown away.
				 */
				this.body.on( 'mousedown', function(){ obj.close(); } );
			}

			/*
			 * Anything that moves the element leaves the hint pointing at nothing. The panel is a
			 * scroll container of its own, so this listens in the capture phase to hear scrolling that
			 * never reaches the window.
			 */
			document.addEventListener( 'scroll', function( e )
			{
				if( ! obj.tooltip || ! obj.tooltip.contains( e.target ) )
				{
					obj.close();
				}
			}, true );

			$(window).on( 'resize', function(){ obj.close(); } );

			$(document).on( 'keydown', function( e )
			{
				if( 27 == e.keyCode )
				{
					obj.close();
				}
			});
		},

		/**
		 * The single element every hint of this instance is shown in.
		 *
		 * Reused rather than cloned per element: a clone left behind by markup that is gone - a drag
		 * helper, an element deleted from the canvas - can never be closed again, because the event
		 * that would close it belongs to an element that no longer exists.
		 *
		 * @returns {HTMLElement}
		 */
		element: function()
		{
			if( ! this.tooltip )
			{
				var tip = document.createElement( 'div' );

				tip.className = this.options['class'];
				tip.setAttribute( 'popover', 'manual' );
				tip.innerHTML = '<div class="inner_tooltip"></div><span class="avia-arrow-wrap"><span class="avia-arrow"></span></span>';

				document.body.appendChild( tip );

				this.tooltip = tip;
			}

			return this.tooltip;
		},

		schedule: function( e, anchor )
		{
			clearTimeout( this.timer );

			//	a held button means a drag, not a look - and the drag helper carries the same attribute
			if( e.buttons || ( e.originalEvent && e.originalEvent.buttons ) )
			{
				return;
			}

			this.timer = setTimeout( this.open.bind( this, anchor ), this.instant ? 0 : this.options.delay );
		},

		leave: function()
		{
			this.close();

			//	moving on to a neighbour right away shows the next hint without the wait
			this.instant = true;

			clearTimeout( this.instant_timer );
			this.instant_timer = setTimeout( this.stop_instant.bind( this ), this.options.delayOut );
		},

		stop_instant: function()
		{
			this.instant = false;
		},

		toggle: function( anchor )
		{
			if( this.anchor === anchor && this.is_open() )
			{
				this.close();
				return;
			}

			this.open( anchor );
		},

		is_open: function()
		{
			return !! this.tooltip && this.tooltip.matches( ':popover-open' );
		},

		open: function( anchor )
		{
			/*
			 * Read from the attribute rather than through jQuery, which caches what it found the first
			 * time and hands that back for the rest of the page. A hint that never changes cannot tell
			 * the difference - but one on a button that switches, "Fullscreen" to "Exit fullscreen",
			 * would go on describing what the button did before it was pressed.
			 */
			var text = anchor.getAttribute( 'data-' + this.options.data );

			/*
			 * The wait between hovering and opening is long enough for the element to be gone by the
			 * time this runs - deleted from the canvas, or a drag helper that has been dropped - and
			 * an element that is no longer in the page has no position to point at.
			 */
			if( ! text || ! anchor.isConnected )
			{
				return;
			}

			var tip = this.element();

			this.anchor = anchor;
			tip.querySelector( '.inner_tooltip' ).innerHTML = text;

			if( ! this.is_open() )
			{
				tip.showPopover();
			}

			/*
			 * Placing reads the layout, which settles the styles the tooltip is currently drawn with -
			 * so the class that follows is a change the browser can transition from rather than the
			 * first style it ever sees, and the hint fades in instead of appearing.
			 */
			this.place();

			tip.classList.add( 'av-tooltip-visible' );
		},

		/**
		 * How far the arrow sticks out of the box, which is what has to clear the element.
		 *
		 * Measured rather than assumed: the arrow is a rotated square inside a wrapper that crops it,
		 * so how much of it shows is the overlap of the two and not the size of either. Reading it
		 * keeps the stylesheet free to change the shape without the spacing quietly going wrong.
		 *
		 * @returns {number}
		 */
		arrow_reach: function( box )
		{
			var wrap = this.tooltip.querySelector( '.avia-arrow-wrap' ),
				arrow = wrap ? wrap.querySelector( '.avia-arrow' ) : null;

			if( ! arrow )
			{
				return 0;
			}

			var wrap_rect = wrap.getBoundingClientRect(),
				arrow_rect = arrow.getBoundingClientRect();

			if( 'bottom' == this.options.position )
			{
				return Math.max( 0, box.top - Math.max( wrap_rect.top, arrow_rect.top ) );
			}

			return Math.max( 0, Math.min( wrap_rect.bottom, arrow_rect.bottom ) - box.bottom );
		},

		place: function()
		{
			var tip = this.tooltip,
				rect = this.anchor.getBoundingClientRect(),
				box = tip.getBoundingClientRect(),
				arrow = tip.querySelector( '.avia-arrow-wrap' ),
				reach = this.arrow_reach( box ),
				edge = 5,
				center = rect.left + ( rect.width / 2 ),
				left = center - ( box.width / 2 ),
				top;

			if( 'bottom' == this.options.position )
			{
				top = rect.bottom + reach + this.options.gap;
			}
			else
			{
				top = rect.top - box.height - reach - this.options.gap;
			}

			//	keep it on screen - an element against the left edge of the panel would push it off
			left = Math.max( edge, Math.min( left, window.innerWidth - box.width - edge ) );
			top = Math.max( edge, Math.min( top, window.innerHeight - box.height - edge ) );

			tip.style.left = left + 'px';
			tip.style.top = top + 'px';

			//	the arrow stays over the element the hint belongs to, wherever the box ended up
			if( arrow )
			{
				arrow.style.left = ( center - left - ( arrow.offsetWidth / 2 ) ) + 'px';
			}
		},

		close: function()
		{
			clearTimeout( this.timer );

			if( ! this.is_open() )
			{
				return;
			}

			var tip = this.tooltip;

			tip.classList.remove( 'av-tooltip-visible' );
			this.anchor = null;

			/*
			 * Left in the top layer until the fade has finished so it does not blink away. Opening it
			 * again in the meantime puts the class back, which is what tells this to leave it alone.
			 */
			setTimeout( function()
			{
				if( ! tip.classList.contains( 'av-tooltip-visible' ) )
				{
					tip.hidePopover();
				}
			}, 200 );
		}
	};

})(jQuery);
