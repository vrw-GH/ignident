/**
 * Lets the user set how wide the element sidebar is by dragging its edge.
 *
 * The panel snaps to four widths, one per column of element buttons, rather than following the
 * pointer freely: a column that is half drawn is wasted space, and every width in between is one of
 * those. The choice is the user's and it sticks - the panel never changes width on its own.
 *
 * Kept out of avia-element-behavior.js on purpose. That file is about where the panel sits, which it
 * has to work out from the editor around it; this is about how wide the user wants it, which is not
 * measured at all. They share only the stylesheet.
 *
 * @since 8.0
 */

(function( $ )
{
	'use strict';

	var panel_selector = '.avia-fixed-controls.avia-alb-panel-side',
		handle_class = 'avia-alb-panel-resize',
		resizing_class = 'avia-alb-resizing',
		columns_attribute = 'data-av-alb-cols',
		//	the browser remembers this, so the choice follows the user around this browser only
		storage_key = 'avia-alb-panel-columns',
		min_columns = 1,
		max_columns = 4,
		default_columns = 3;

	$( function()
	{
		var panel = document.querySelector( panel_selector );

		if( ! panel )
		{
			return;
		}

		apply_columns( stored_columns() );

		build_handle( panel );
	});

	/**
	 * The width of the narrowest panel and what each further column adds, both from the stylesheet.
	 *
	 * Read rather than written down here so there is one set of numbers to change, and so a child
	 * theme that redraws the element buttons can move the steps with them.
	 */
	function read_steps()
	{
		var style = window.getComputedStyle( document.body ),
			min = parseInt( style.getPropertyValue( '--av-alb-panel-width-min' ), 10 ),
			step = parseInt( style.getPropertyValue( '--av-alb-panel-width-step' ), 10 );

		if( ! min || ! step )
		{
			return null;
		}

		return { min: min, step: step };
	}

	/**
	 * How many columns a panel of this width comes closest to holding.
	 */
	function columns_for_width( width, steps )
	{
		var columns = Math.round( ( width - steps.min ) / steps.step ) + min_columns;

		return Math.max( min_columns, Math.min( max_columns, columns ) );
	}

	function current_columns()
	{
		var stored = parseInt( document.documentElement.getAttribute( columns_attribute ), 10 );

		return stored || default_columns;
	}

	function apply_columns( columns )
	{
		document.documentElement.setAttribute( columns_attribute, columns );
	}

	/**
	 * Storage is allowed to fail without taking the panel with it - a browser in private mode throws
	 * on write, and some throw on read as well. A panel that forgets its width is a far smaller
	 * problem than an editor whose sidebar never appears.
	 */
	function stored_columns()
	{
		var stored = null;

		try
		{
			stored = parseInt( window.localStorage.getItem( storage_key ), 10 );
		}
		catch( e )
		{
			stored = null;
		}

		if( ! stored || stored < min_columns || stored > max_columns )
		{
			return default_columns;
		}

		return stored;
	}

	function store_columns( columns )
	{
		try
		{
			window.localStorage.setItem( storage_key, columns );
		}
		catch( e )
		{
			//	nothing to do - the width still applies for this page
		}
	}

	/**
	 * Builds the grip and gives it to both the pointer and the keyboard.
	 *
	 * A separator rather than a button, because that is what it is: it reports which of the four
	 * widths is in use, so a screen reader announces "3 of 4" rather than an unlabelled control, and
	 * the arrow keys move it the same way dragging does.
	 */
	function build_handle( panel )
	{
		var handle = document.createElement( 'div' );

		handle.className = handle_class;
		handle.setAttribute( 'role', 'separator' );
		handle.setAttribute( 'aria-orientation', 'vertical' );
		handle.setAttribute( 'aria-label', window.AviaBuilderPanelL10n && AviaBuilderPanelL10n.resize_panel ? AviaBuilderPanelL10n.resize_panel : 'Resize element panel' );
		handle.setAttribute( 'aria-valuemin', min_columns );
		handle.setAttribute( 'aria-valuemax', max_columns );
		handle.setAttribute( 'tabindex', '0' );

		announce( handle, current_columns() );

		document.body.appendChild( handle );

		follow_panel( handle, panel );
		bind_drag( handle, panel );
		bind_keys( handle );

		return handle;
	}

	/**
	 * Copies the editor chrome offsets from the panel onto the handle.
	 *
	 * The panel carries them as custom properties of its own rather than putting them on <body>, so
	 * that moving it does not mark the whole document dirty - which means nothing outside the panel
	 * inherits them, the handle included. Rather than change where they are written and pay that cost
	 * on every editor resize, the handle takes a copy whenever the panel's own style changes, which is
	 * exactly when it moved.
	 */
	function follow_panel( handle, panel )
	{
		function place()
		{
			var style = panel.style;

			handle.style.setProperty( '--av-alb-chrome-top', style.getPropertyValue( '--av-alb-chrome-top' ) || '0px' );
			handle.style.setProperty( '--av-alb-chrome-start', style.getPropertyValue( '--av-alb-chrome-start' ) || '0px' );
		}

		place();

		if( typeof MutationObserver == 'undefined' )
		{
			return;
		}

		new MutationObserver( place ).observe( panel, { attributes: true, attributeFilter: [ 'style' ] } );
	}

	function announce( handle, columns )
	{
		handle.setAttribute( 'aria-valuenow', columns );
	}

	/**
	 * Drags the edge to one of the four widths.
	 *
	 * The edge the panel starts at is taken once, when the drag starts, and the width is measured
	 * against that - not against the panel, which is moving under the pointer as this runs and would
	 * feed its own change back into the next reading.
	 *
	 * Pointer events rather than mouse events so a pen or a touch screen drags it too, and the capture
	 * keeps the drag alive when the pointer runs ahead of the handle or leaves the window entirely.
	 */
	function bind_drag( handle, panel )
	{
		var origin = 0,
			steps = null,
			rtl = false;

		handle.addEventListener( 'pointerdown', function( event )
		{
			steps = read_steps();

			if( ! steps )
			{
				return;
			}

			var rect = panel.getBoundingClientRect();

			rtl = is_rtl();
			origin = rtl ? rect.right : rect.left;

			handle.setPointerCapture( event.pointerId );
			document.documentElement.classList.add( resizing_class );

			event.preventDefault();
		});

		handle.addEventListener( 'pointermove', function( event )
		{
			if( ! handle.hasPointerCapture( event.pointerId ) || ! steps )
			{
				return;
			}

			var width = rtl ? origin - event.clientX : event.clientX - origin,
				columns = columns_for_width( width, steps );

			if( columns === current_columns() )
			{
				return;
			}

			apply_columns( columns );
			announce( handle, columns );
		});

		function end( event )
		{
			if( ! handle.hasPointerCapture( event.pointerId ) )
			{
				return;
			}

			handle.releasePointerCapture( event.pointerId );
			document.documentElement.classList.remove( resizing_class );

			store_columns( current_columns() );
		}

		handle.addEventListener( 'pointerup', end );
		handle.addEventListener( 'pointercancel', end );
	}

	/**
	 * The same four steps from the keyboard, so the panel can be resized without a pointer.
	 *
	 * Which arrow widens depends on the writing direction: the panel grows towards the middle of the
	 * screen, which is to the right in a left to right admin and to the left in a right to left one.
	 */
	function bind_keys( handle )
	{
		handle.addEventListener( 'keydown', function( event )
		{
			var step = 0;

			if( 'ArrowLeft' === event.key )
			{
				step = is_rtl() ? 1 : -1;
			}
			else if( 'ArrowRight' === event.key )
			{
				step = is_rtl() ? -1 : 1;
			}
			else if( 'Home' === event.key )
			{
				step = min_columns - current_columns();
			}
			else if( 'End' === event.key )
			{
				step = max_columns - current_columns();
			}
			else
			{
				return;
			}

			var columns = Math.max( min_columns, Math.min( max_columns, current_columns() + step ) );

			if( columns === current_columns() )
			{
				return;
			}

			apply_columns( columns );
			announce( handle, columns );
			store_columns( columns );

			event.preventDefault();
		});
	}

	function is_rtl()
	{
		return 'rtl' == ( document.documentElement.getAttribute( 'dir' ) || '' ).toLowerCase();
	}

})( jQuery );
