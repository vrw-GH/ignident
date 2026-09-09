/**
 * Filters the element sidebar as the user types.
 *
 * The panel holds over a hundred elements across five sections, most of them closed at any time, so
 * finding one means either knowing which section it lives in or opening each in turn. Typing skips
 * that: every section is opened while a search is running, whatever does not match is taken out of
 * the way, and sections left with nothing go too - so what remains reads as a list of results that
 * happens to still be grouped.
 *
 * Closing the search puts the sections back exactly as they were, open or closed.
 *
 * @since 8.0
 */

(function( $ )
{
	'use strict';

	var panel_selector = '.avia-fixed-controls.avia-alb-panel-side',
		row_selector = '.avia-alb-panel-row',
		/*
		 * Accordion headings - each is followed by the section it opens.
		 *
		 * Both kinds are named. The panel holds the theme's own elements and the user's custom ones,
		 * and the switcher above them hides one set by putting display:none straight on its headings;
		 * searching only the first kind would quietly leave every custom element out of the results.
		 */
		heading_selector = 'a.avia-alb-tab, a.avia-custom-element-tab',
		tab_class = 'avia-tab',
		button_selector = 'a.shortcode_insert_button',
		input_class = 'avia-alb-panel-filter',
		empty_class = 'avia-alb-panel-no-results',
		filtering_class = 'avia-alb-filtering',
		//	the display each section had before a search started, so closing the search restores it
		remembered = null;

	$( function()
	{
		var panel = document.querySelector( panel_selector ),
			row = panel ? panel.querySelector( row_selector ) : null;

		if( ! panel || ! row )
		{
			return;
		}

		var input = build_input();

		row.insertBefore( input, row.firstChild );

		bind( input, panel );
		freeze_accordion( panel );
	});

	/**
	 * Stops the sections being opened and closed while a search is running.
	 *
	 * With a search in the field the sections are not drawers any more, they are the results - all of
	 * them open, the empty ones gone. Collapsing one from there only hides matches, and the heading
	 * would be offering exactly that. The chevron is taken away in the stylesheet; this takes away the
	 * click behind it, on the way down so the accordion's own handler never sees it, and for the
	 * keyboard as well as the mouse.
	 */
	function freeze_accordion( panel )
	{
		panel.addEventListener( 'click', function( event )
		{
			if( ! panel.classList.contains( filtering_class ) )
			{
				return;
			}

			if( ! event.target.closest || ! event.target.closest( heading_selector ) )
			{
				return;
			}

			event.preventDefault();
			event.stopPropagation();

		}, true );
	}

	function translate( key, fallback )
	{
		if( window.AviaBuilderPanelL10n && AviaBuilderPanelL10n[ key ] )
		{
			return AviaBuilderPanelL10n[ key ];
		}

		return fallback;
	}

	/**
	 * A search input rather than a text one: browsers give it a clear button of their own, and it is
	 * what a screen reader should be announcing.
	 */
	function build_input()
	{
		var input = document.createElement( 'input' );

		input.type = 'search';
		input.className = input_class;
		input.placeholder = translate( 'search_placeholder', 'Search elements' );
		input.setAttribute( 'aria-label', translate( 'search_label', 'Search elements' ) );
		input.setAttribute( 'autocomplete', 'off' );

		return input;
	}

	function bind( input, panel )
	{
		input.addEventListener( 'input', function()
		{
			filter( panel, input.value );
		});

		/*
		 * Escape empties the field rather than only clearing the filter, so one key gets all the way
		 * back rather than leaving the box looking as though it is still searching.
		 */
		input.addEventListener( 'keydown', function( event )
		{
			if( 'Escape' !== event.key )
			{
				return;
			}

			input.value = '';
			filter( panel, '' );

			event.stopPropagation();
		});
	}

	function sections( panel )
	{
		var headings = panel.querySelectorAll( heading_selector ),
			found = [];

		for( var i = 0; i < headings.length; i++ )
		{
			var tab = headings[i].nextElementSibling;

			if( tab && tab.classList.contains( tab_class ) )
			{
				found.push( { heading: headings[i], tab: tab } );
			}
		}

		return found;
	}

	/**
	 * What a button can be found by - its name and its description, not the label on its face.
	 *
	 * The label is rendered twice inside the button, so searching the text would match "1/11/1" for an
	 * element called "1/1". The name and the tooltip are single, and the tooltip is worth searching in
	 * its own right: someone looking for a menu should find List Menu by typing "links".
	 */
	function haystack( button )
	{
		var name = button.getAttribute( 'data-sort_name' ) || '',
			tip = button.getAttribute( 'data-avia-tooltip' ) || '';

		if( ! name )
		{
			name = button.textContent || '';
		}

		return ( name + ' ' + tip ).toLowerCase();
	}

	/**
	 * Notes how the panel looked before the search, so closing it can put things back exactly.
	 *
	 * The headings are recorded as well as the sections, and not because the accordion moves them: the
	 * switcher between the theme's elements and the user's own hides a whole set by writing display
	 * onto their headings. Clearing that rather than restoring it would leave both sets on screen at
	 * once, with the switcher still claiming to show one.
	 */
	function remember( list )
	{
		if( remembered )
		{
			return;
		}

		remembered = list.map( function( section )
		{
			return {
					tab: section.tab,
					tab_display: section.tab.style.display,
					heading: section.heading,
					heading_display: section.heading.style.display
				};
		});
	}

	function restore( panel, list )
	{
		for( var i = 0; i < list.length; i++ )
		{
			var buttons = list[i].tab.querySelectorAll( button_selector );

			for( var b = 0; b < buttons.length; b++ )
			{
				buttons[b].style.display = '';
			}
		}

		if( remembered )
		{
			for( var r = 0; r < remembered.length; r++ )
			{
				remembered[r].tab.style.display = remembered[r].tab_display;
				remembered[r].heading.style.display = remembered[r].heading_display;
			}

			remembered = null;
		}

		panel.classList.remove( filtering_class );

		message( panel, false );
	}

	function filter( panel, value )
	{
		var query = ( value || '' ).trim().toLowerCase(),
			list = sections( panel );

		if( ! query )
		{
			restore( panel, list );
			return;
		}

		remember( list );

		panel.classList.add( filtering_class );

		var total = 0;

		for( var i = 0; i < list.length; i++ )
		{
			var buttons = list[i].tab.querySelectorAll( button_selector ),
				matches = 0;

			for( var b = 0; b < buttons.length; b++ )
			{
				var hit = haystack( buttons[b] ).indexOf( query ) !== -1;

				buttons[b].style.display = hit ? '' : 'none';

				if( hit )
				{
					matches ++;
				}
			}

			//	a section with nothing left in it is noise, so it and its heading step aside
			list[i].tab.style.display = matches ? 'block' : 'none';
			list[i].heading.style.display = matches ? '' : 'none';

			total += matches;
		}

		message( panel, 0 === total );
	}

	function message( panel, show )
	{
		var note = panel.querySelector( '.' + empty_class );

		if( ! show )
		{
			if( note )
			{
				note.parentNode.removeChild( note );
			}

			return;
		}

		if( note )
		{
			return;
		}

		note = document.createElement( 'p' );
		note.className = empty_class;
		note.textContent = translate( 'no_results', 'No elements match your search' );

		panel.appendChild( note );
	}

})( jQuery );
