/*
 * Adds support for dynamic data in modal popup in backend
 *
 * @since 6.0
 */

"use strict";

//	global namespace
var aviaJS = aviaJS || {};


(function()
{
	if( ! aviaJS.aviaModalDynamic )
	{
		class aviaModalDynamic
		{
			obj_modal = null;
			modal = null;
			dynChars = null;
			dynLists = null;
			dynSelects = null;
			attached = false;


			constructor( obj_modal )
			{
				this.obj_modal = obj_modal;

				if( ! this.obj_modal.modal.length )
				{
					return;
				}

				this.modal = this.obj_modal.modal[0];

				this.modal.addEventListener( 'aviaModalWindowOpen', this.onAviaModalWindowOpen.bind( this ) );
			}

			bindEvents()
			{
				this.dynLists = this.modal.getElementsByClassName( 'av-dynamic-select' );

				if( ! this.dynLists.length )
				{
					return;
				}

				for( let i = 0; i < this.dynLists.length; i++ )
				{
					this.dynLists[i].addEventListener( 'mousedown', this.onDynListsMousedown.bind( this ) );
					this.dynLists[i].addEventListener( 'mouseleave', this.onDynListsMouseleave.bind( this ) );
				}

				this.dynChars = this.modal.getElementsByClassName( 'avia-dynamic-char' );

				for( let i = 0; i < this.dynChars.length; i++ )
				{
					this.dynChars[i].addEventListener( 'mousedown', this.onDynCharsMousedown.bind( this ) );
				}

				this.dynSelects = this.modal.getElementsByClassName( 'av-dynamic-select-element' );

				for( let i = 0; i < this.dynSelects.length; i++ )
				{
					this.dynSelects[i].addEventListener( 'mousedown', this.onDynSelectElementMousedown.bind( this ) );
				}
			}

			onAviaModalWindowOpen( event )
			{
				if( this.attached )
				{
					return;
				}

				this.bindEvents();

				this.attached = true;
			}

			onDynCharsMousedown( event )
			{
				//	avoid input/textarea to loose focus
				event.preventDefault();
				event.stopPropagation();

				//	open list div on this event - active user interaction (not as previous by hover event)
				let current = event.target,
					container = current.closest( '.avia-dynamic-select-container' ),
					list = container.getElementsByClassName( 'av-dynamic-select' );

				if( list.length )
				{
					list[0].classList.add( 'av-dyn-select-visible' );
				}
			}

			onDynListsMousedown( event )
			{
				//	avoid input/textarea to loose focus
				event.preventDefault();
				event.stopPropagation();
			}

			onDynListsMouseleave( event )
			{
				event.preventDefault();
				event.stopPropagation();

				let current = event.target;
				current.classList.remove( 'av-dyn-select-visible' );
			}

			onDynSelectElementMousedown( event )
			{
				event.preventDefault();
				event.stopPropagation();

				let current = event.target,
					data = JSON.parse( current.dataset.dynamic ),
					container = current.closest( '.avia-form-element' ),
					select = current.closest( '.av-dynamic-select' ),
					input = null,
					clear = select.classList.contains( 'av-dynamic-clear' );

				input = container.getElementsByTagName( 'input' );

				if( ! input.length )
				{
					input = container.getElementsByTagName( 'textarea' );
				}

				if( ! input.length )
				{
					return;
				}

				if( clear )
				{
					input[0].value = data;
				}
				else if( input[0] === document.activeElement )
				{
					let caret = input[0].selectionStart > input[0].selectionEnd ? input[0].selectionStart: input[0].selectionEnd ;

					if( caret == 0 )
					{
						input[0].value = data + input[0].value;
					}
					else if( caret >= input[0].value.length )
					{
						input[0].value += data;
					}
					else
					{
						input[0].value = input[0].value.substring( 0, caret ) + data + input[0].value.substring( caret );
					}
				}
				else
				{
					input[0].value += data;
				}

				select.style['pointer-events'] = 'none';

				setTimeout( function()
				{
					select.style['pointer-events'] = '';
				}, 500);

				input[0].dispatchEvent( new Event('change', { bubbles: true, cancelable: true } ) );
			}
		}

		//	class factory
		aviaJS.aviaModalDynamic = function( obj_modal )
		{
			return new aviaModalDynamic( obj_modal );
		};

	}

})();
