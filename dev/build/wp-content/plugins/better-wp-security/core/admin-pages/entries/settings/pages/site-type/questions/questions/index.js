/**
 * WordPress dependencies
 */
import { useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useSingletonEffect } from '@ithemes/security-hocs';
import { STORE_NAME } from '../../../../stores/onboard';
import IsClient from './is-client';
import IpDetection from './ip-detection';

export default function useQuestions() {
	const { registerQuestionComponent } = useDispatch( STORE_NAME );

	useSingletonEffect( useQuestions, () => {
		registerQuestionComponent( 'is-client', IsClient );
		registerQuestionComponent( 'ip-detection', IpDetection );
	} );
}
