/**
 * Gives the layout builder the whole screen.
 *
 * There is no fullscreen mode of the builder's own here - no overlay, no second set of publish
 * buttons, no state to keep in step with the editor. The button drives the chrome the editor
 * already has, and the element panel follows on its own: it re-measures whenever the class on
 * <body> changes, which is what both editors do when their chrome moves.
 *
 * The two editors are told apart by body.block-editor-page, as elsewhere in the builder.
 *
 * @since 8.0
 */
( function( $ )
{
	'use strict';

	var button_selector = '.avia-alb-fullscreen',
		active_class = 'avia-alb-fullscreen-active',
		//	classic only: what the body is wearing while the builder has the screen
		body_class = 'avia-alb-is-fullscreen',
		/*
		 * The block editor keeps its own fullscreen setting and remembers it between visits. The classic
		 * editor is not given one: a page laid out in a single column is a good place to build and a poor
		 * place to publish from, and coming back to it days later without having asked for it is worse
		 * than turning it on again when it is wanted.
		 */
		classic_state = false;

	$( function()
	{
		var button = document.querySelector( button_selector );

		if( ! button )
		{
			return;
		}

		button.addEventListener( 'click', function( event )
		{
			event.preventDefault();
			toggle( button );
		});

		if( is_block_editor() )
		{
			follow_block_editor( button );
		}

		reflect( button, is_active() );
	});

	function is_block_editor()
	{
		return document.body.classList.contains( 'block-editor-page' );
	}

	function is_active()
	{
		return is_block_editor() ? block_editor_fullscreen() : classic_state;
	}

	function toggle( button )
	{
		var next = ! is_active();

		if( is_block_editor() )
		{
			set_block_editor( next );
		}
		else
		{
			set_classic( next );
		}

		reflect( button, next );
	}

	/* --- block editor ------------------------------------------------------ */

	/**
	 * The preferences store is where the block editor has kept this since WordPress 6.1. Older
	 * installs are still supported by the theme and answer to the edit-post store instead, which
	 * newer ones keep as a deprecated shim - so the modern one is asked for first and the old one is
	 * only reached for when it is the only one there.
	 */
	function block_editor_fullscreen()
	{
		try
		{
			if( has_preferences() )
			{
				return !! wp.data.select( 'core/preferences' ).get( 'core/edit-post', 'fullscreenMode' );
			}

			return !! wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' );
		}
		catch( e )
		{
			return false;
		}
	}

	function set_block_editor( on )
	{
		try
		{
			if( has_preferences() )
			{
				wp.data.dispatch( 'core/preferences' ).set( 'core/edit-post', 'fullscreenMode', on );
			}
			else if( block_editor_fullscreen() !== on )
			{
				wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' );
			}

			/*
			 * The settings sidebar is already out of the way while the builder is open - the builder
			 * covers the width the sidebar would otherwise take. It is closed anyway, so that switching
			 * back to the default editor from here does not open onto a sidebar nobody asked for.
			 */
			if( on )
			{
				wp.data.dispatch( 'core/interface' ).disableComplementaryArea( 'core/edit-post' );
			}
		}
		catch( e )
		{
		}
	}

	function has_preferences()
	{
		return !! ( window.wp && wp.data && wp.data.select( 'core/preferences' ) &&
					typeof wp.data.select( 'core/preferences' ).get === 'function' );
	}

	/**
	 * Fullscreen is the block editor's own setting and can be changed from its menu as well, so the
	 * button reads the store rather than remembering what it did last.
	 */
	function follow_block_editor( button )
	{
		if( ! window.wp || ! wp.data || typeof wp.data.subscribe !== 'function' )
		{
			return;
		}

		var last = block_editor_fullscreen();

		wp.data.subscribe( function()
		{
			var now = block_editor_fullscreen();

			if( now !== last )
			{
				last = now;
				reflect( button, now );
			}
		});
	}

	/* --- classic editor ---------------------------------------------------- */

	/**
	 * Two things are in the way in the classic editor: the admin menu, and the column of boxes beside
	 * the builder. Both are given up, and the layout is turned into a single column.
	 *
	 * A single column on its own would be a step back - WordPress writes the side boxes before the
	 * main ones, so Publish and Featured Image would land between the title and the builder and push
	 * it below the fold. The stylesheet puts the builder first instead and sends those boxes to the
	 * end, where they are still reachable rather than hidden.
	 *
	 * The admin menu is folded by class rather than through WordPress' own collapse button, which
	 * would remember the choice for every screen in the admin long after the builder was closed.
	 */
	function set_classic( on )
	{
		var post_body = document.getElementById( 'post-body' );

		classic_state = on;

		document.body.classList.toggle( body_class, on );
		document.body.classList.toggle( 'folded', on );

		if( ! post_body )
		{
			return;
		}

		post_body.classList.toggle( 'columns-1', on );
		post_body.classList.toggle( 'columns-2', ! on );
	}

	/* --- the button -------------------------------------------------------- */

	function reflect( button, on )
	{
		button.classList.toggle( active_class, on );
		button.setAttribute( 'aria-pressed', on ? 'true' : 'false' );

		var label = translate( on ? 'exit_fullscreen' : 'enter_fullscreen', on ? 'Exit fullscreen' : 'Fullscreen' );

		button.setAttribute( 'data-avia-panel-tooltip', label );
		button.textContent = label;
	}

	function translate( key, fallback )
	{
		if( typeof AviaBuilderPanelL10n !== 'undefined' && AviaBuilderPanelL10n[ key ] )
		{
			return AviaBuilderPanelL10n[ key ];
		}

		return fallback;
	}

})( jQuery );
