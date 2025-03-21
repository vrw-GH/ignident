import { registerBlockType } from '@wordpress/blocks';
import { RichText } from '@wordpress/block-editor';

registerBlockType( 'enfold/custom-text-block',
	{
		title: 'Enfold Block',
		description: '(in beta only - do not use)',
		icon: 'edit',
		category: 'text',
		attributes: {
			content: {
				type: 'string',
				source: 'html',
				selector: 'p'
			}
		},
		edit: ({ attributes, setAttributes }) => {
			const { content } = attributes;

			return (
				<RichText
					tagName="p"
					value={content}
					onChange={(value) => setAttributes({ content: value })}
					placeholder="Enter your text here (Do not use - this is currently only a beta element ..."
				/>
			);
		},
		save: ({ attributes }) => {
			const { content } = attributes;

			return <RichText.Content tagName="p" value={content} />;
		}
	});
