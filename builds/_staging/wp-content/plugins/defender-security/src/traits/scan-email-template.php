<?php
/**
 * Handle common Scan notification and reporting template.
 *
 * @since      3.8.0
 * @package WP_Defender\Traits
 */

namespace WP_Defender\Traits;

trait Scan_Email_Template {

	/**
	 * Get email template.
	 *
	 * @return array
	 */
	public function get_email_template(): array {
		return array(
			'found'     => array(
				'subject' => esc_html__(
					'Malware Scan of {SITE_URL} is complete. {ISSUES_COUNT} issue(s) found.',
					'defender-security'
				),
				'body'    => esc_html__(
					'Hi {USER_NAME},

Malware Scan identified {ISSUES_COUNT} issue(s) on {SITE_URL}. The identified issue(s) is/are listed below.

{ISSUES_LIST}',
					'defender-security'
				),
			),
			'not_found' => array(
				'subject' => esc_html__( 'Scan of {SITE_URL} complete. {ISSUES_COUNT} issues found.', 'defender-security' ),
				'body'    => esc_html__(
					'Hi {USER_NAME},

No vulnerabilities have been found for {SITE_URL}.',
					'defender-security'
				),
			),
			'error'     => array(
				'subject' => esc_html__( 'Couldn’t scan {SITE_URL} for vulnerabilities. ', 'defender-security' ),
				'body'    => esc_html__(
					'Hi {USER_NAME},

We couldn’t scan {SITE_URL} for vulnerabilities. Please visit your site and run a manual scan.',
					'defender-security'
				),
			),
		);
	}
}
