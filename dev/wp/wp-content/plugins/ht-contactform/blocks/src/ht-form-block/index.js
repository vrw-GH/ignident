/**
 * Internal dependencies
 */
import edit from './edit';
import Attributes from './block.json';
import icon from './icon';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { Icon } from '@wordpress/components';


registerBlockType( 'block/ht-form', {
	title: __( 'HT Form' ), 
	icon: <Icon icon={ icon } />,
	category: 'common',
	keywords: [
		__( 'ht-contactform', 'ht-contactform' ),
		__( 'contact-form', 'ht-contactform' ),
		__( 'ht form', 'ht-contactform' ),
	],
	supports: {
		align: ['wide','full']
	},
	attributes:Attributes,
	edit: edit,
	save: ( props ) => {
		return null;
	},
} );
