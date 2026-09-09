/*
 * "What's new" panel.
 *
 * The panel markup is printed once into an inert <template>. It is cloned into a
 * wrapper anchored to whichever trigger was clicked, with the matching skin class.
 * Because <template> content is inert, no image is requested until the panel opens.
 *
 * @since 8.0
 */
( function()
{
	'use strict';

	var template = null,
		wrap = null,
		trigger = null,
		byHover = false,
		hoverTimer = null;

	//	long enough to cross the seam between the admin bar and the panel below it
	var HOVER_CLOSE_DELAY = 260;

	/*
	 *  A mouse on its way to the account menu crosses this entry in well under this,
	 *  so a sweep past never opens the panel. Only a pointer that settles does.
	 */
	var HOVER_OPEN_DELAY = 200;
	var openTimer = null;

	function cancelHoverClose()
	{
		if( hoverTimer )
		{
			window.clearTimeout( hoverTimer );
			hoverTimer = null;
		}
	}

	/**
	 * Only ever closes a panel that hover opened. Once it has been clicked it is
	 * pinned, and moving the mouse away must not take it down.
	 */
	function scheduleHoverClose()
	{
		cancelHoverClose();

		hoverTimer = window.setTimeout( function()
		{
			if( byHover )
			{
				close();
			}
		}, HOVER_CLOSE_DELAY );
	}

	function build( skin )
	{
		wrap = document.createElement( 'div' );
		wrap.className = 'av-news-panel-wrap';
		wrap.appendChild( template.content.cloneNode( true ) );

		wrap.addEventListener( 'mouseenter', cancelHoverClose );
		wrap.addEventListener( 'mouseleave', scheduleHoverClose );

		var panel = wrap.querySelector( '.av-news-panel' );

		if( panel )
		{
			panel.classList.add( skin );
		}

		document.body.appendChild( wrap );
	}

	function position( el )
	{
		var box = el.getBoundingClientRect(),
			top = box.bottom + window.pageYOffset,
			left = box.left + window.pageXOffset,
			max = window.pageXOffset + document.documentElement.clientWidth - wrap.offsetWidth - 20;

		wrap.style.top = top + 'px';
		wrap.style.left = Math.max( 10, Math.min( left, max ) ) + 'px';
	}

	function close()
	{
		cancelHoverClose();
		byHover = false;

		if( wrap )
		{
			wrap.parentNode.removeChild( wrap );
			wrap = null;
		}

		if( trigger )
		{
			var node = trigger.closest( '#wp-admin-bar-av-news' ) || trigger;
			node.classList.remove( 'av-news-open' );
			trigger.setAttribute( 'aria-expanded', 'false' );
			trigger = null;
		}
	}

	function markRead()
	{
		if( ! window.aviaNewsL10n || ! window.aviaNewsL10n.newest )
		{
			return;
		}

		var body = new FormData();
		body.append( 'action', 'avia_news_mark_read' );
		body.append( '_wpnonce', window.aviaNewsL10n.nonce );

		window.fetch( window.aviaNewsL10n.ajaxurl, {
					method: 'POST',
					credentials: 'same-origin',
					body: body
				} ).then( function()
		{
			var badges = document.querySelectorAll( '.av-news-badge' );

			for( var i = 0; i < badges.length; i ++ )
			{
				badges[ i ].parentNode.removeChild( badges[ i ] );
			}
		} ).catch( function(){} );
	}

	function open( el )
	{
		var skin = el.classList.contains( 'av-news-trigger-adminbar' ) ? 'av-news-panel--dark' : 'av-news-panel--light';

		build( skin );

		trigger = el;
		( el.closest( '#wp-admin-bar-av-news' ) || el ).classList.add( 'av-news-open' );
		el.setAttribute( 'aria-expanded', 'true' );

		position( el );
		markRead();
	}

	function onClick( event )
	{
		var el = event.target.closest( '.av-news-trigger' );

		if( el )
		{
			event.preventDefault();

			if( wrap && trigger === el )
			{
				//	a click on a panel that hover opened pins it rather than closing it
				if( byHover )
				{
					byHover = false;
					cancelHoverClose();
				}
				else
				{
					close();
				}
			}
			else
			{
				close();
				open( el );
			}

			return;
		}

		//	a click inside the panel follows the link, anything else closes it
		if( wrap && ! event.target.closest( '.av-news-panel-wrap' ) )
		{
			close();
		}
	}

	/**
	 * The admin bar entry behaves like every other admin bar menu: it drops open on
	 * hover. The options header trigger stays click only - it is a two line block in
	 * the middle of a form, and opening it in passing would be a surprise.
	 */
	function initHover()
	{
		var node = document.getElementById( 'wp-admin-bar-av-news' );

		if( ! node )
		{
			return;
		}

		/*
		 *  The admin bar puts meta['class'] on the <li>, not on the anchor inside it, so
		 *  the trigger IS this node. It also has to be the same element that a click
		 *  resolves to through closest(), or the two would fight over `trigger`.
		 */
		var el = node.classList.contains( 'av-news-trigger' ) ? node : node.querySelector( '.av-news-trigger' );

		if( ! el )
		{
			return;
		}

		node.addEventListener( 'mouseenter', function()
		{
			cancelHoverClose();

			if( wrap && trigger === el )
			{
				return;
			}

			window.clearTimeout( openTimer );

			openTimer = window.setTimeout( function()
			{
				close();
				open( el );
				byHover = true;

			}, HOVER_OPEN_DELAY );
		} );

		node.addEventListener( 'mouseleave', function()
		{
			//	left before it opened - nothing happened at all
			window.clearTimeout( openTimer );
			scheduleHoverClose();
		} );
	}

	function init()
	{
		template = document.getElementById( 'av-news-panel-template' );

		if( ! template || ! template.content )
		{
			return;
		}

		var triggers = document.querySelectorAll( '.av-news-trigger' );

		for( var i = 0; i < triggers.length; i ++ )
		{
			triggers[ i ].setAttribute( 'aria-expanded', 'false' );
			triggers[ i ].setAttribute( 'aria-haspopup', 'true' );
		}

		document.addEventListener( 'click', onClick );

		initHover();

		document.addEventListener( 'keydown', function( event )
		{
			if( 'Escape' === event.key && wrap )
			{
				close();
			}
		} );

		window.addEventListener( 'resize', function()
		{
			if( wrap && trigger )
			{
				position( trigger );
			}
		} );
	}

	if( 'loading' === document.readyState )
	{
		document.addEventListener( 'DOMContentLoaded', init );
	}
	else
	{
		init();
	}

} )();
