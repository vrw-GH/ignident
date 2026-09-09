/**
 * Internal dependencies
 */
import Inspector from './inspector';

/**
 * WordPress dependencies
 */
import classnames from 'classnames';
import { __ } from '@wordpress/i18n';
import { Fragment, Component } from '@wordpress/element';
const { serverSideRender: ServerSideRender } = wp;

class edit extends Component {

	componentDidMount() {
		this.initializeFormComponents();
	}

	componentDidUpdate() {
		this.initializeFormComponents();
	}

	initializeFormComponents() {
	    // Handle Range Slider Value Update
		if(document.querySelectorAll('.ht-form-elem-range')) {
			document.querySelectorAll('.ht-form-elem-range').forEach((range) => {
				range.addEventListener('input', () => {
					range.nextElementSibling.querySelector('.ht-form-elem-range-amount').textContent = range.value;
				});
			});
		}
		
		// Custom Select using Choices JS
		if(document.querySelectorAll('[data-ht-select]')) {
			document.querySelectorAll('[data-ht-select]').forEach((select) => {
				const searchable = select.getAttribute('data-searchable') === '1';
				const maxselect = select.getAttribute('data-maxselect') ? parseInt(select.getAttribute('data-maxselect')) : -1;
				new Choices(select, {
					searchEnabled: searchable,
					itemSelectText: '',
					maxItemCount: maxselect,
					removeItemButton: true,
					placeholder: true,
					placeholderValue: '',
					shouldSort: false,
				});
			});
		}
	}

	render (){
		const{
			clientId,
			attributes,
			className,
			setAttributes,
		} = this.props;

		const { formId, blockUniqId } = attributes;

		{ ( blockUniqId == '' ) && setAttributes( { blockUniqId: clientId } ) }

		const areaClasses = classnames( 
			className,
			{ [ `ht-contactform-area-${ attributes.align }` ] : attributes.align }
		);

		let htBlockUniqId = `ht-editor-bock-${blockUniqId}`;

		return (
			<Fragment>
				<div id={ htBlockUniqId } className={ areaClasses } style={{
					pointerEvents: 'none',
				}}>
					<ServerSideRender
						block="block/ht-form"
						attributes = {{formId:formId}}
					/>
				</div>
	     		<Inspector { ...this.props } />
		    </Fragment>
		);
	}
}

export default edit;
