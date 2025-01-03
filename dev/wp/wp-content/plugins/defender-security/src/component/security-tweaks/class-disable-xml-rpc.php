<?php
/**
 * Responsible for disabling XML-RPC functionality in WordPress.
 *
 * @package WP_Defender\Component\Security_Tweaks
 */

namespace WP_Defender\Component\Security_Tweaks;

use Calotes\Base\Component;

/**
 * XML-RPC can be a security risk if not properly managed, and this class provides methods to disable it and ensure it
 *  remains disabled.
 */
class Disable_XML_RPC extends Component {

	/**
	 * Unique identifier for the tweak.
	 *
	 * @var string $slug
	 */
	public $slug = 'disable-xml-rpc';
	/**
	 * Indicates whether the issue has been resolved.
	 *
	 * @var bool
	 */
	public $resolved = false;

	/**
	 * Check whether the issue has been resolved or not.
	 *
	 * @return bool
	 */
	public function check() {
		return $this->resolved;
	}

	/**
	 * Here is the code for processing, if the return is true, we add it to resolve list, WP_Error if any error.
	 *
	 * @return bool
	 */
	public function process() {
		return true;
	}

	/**
	 * This is for un-do stuff that has be done in @process.
	 *
	 * @return bool
	 */
	public function revert() {
		return true;
	}

	/**
	 * Shield up.
	 *
	 * @return void
	 */
	public function shield_up() {
		$this->resolved = true;

		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'xmlrpc_methods', array( $this, 'block_xmlrpc_attacks' ) );
	}

	/**
	 * Block XML-RFC attacks.
	 *
	 * @param  array $methods  of xmlrpc.
	 *
	 * @return array
	 */
	public function block_xmlrpc_attacks( $methods ) {
		unset( $methods['pingback.ping'] );
		unset( $methods['pingback.extensions.getPingbacks'] );

		return $methods;
	}

	/**
	 * Return a summary data of this tweak.
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'slug'             => $this->slug,
			'title'            => esc_html__( 'Disable XML-RPC', 'defender-security' ),
			'errorReason'      => esc_html__( 'XML-RPC is currently enabled.', 'defender-security' ),
			'successReason'    => esc_html__( 'XML-RPC is disabled, great job!', 'defender-security' ),
			'misc'             => array(),
			'bulk_description' => esc_html__(
				'In the past, there were security concerns with XML-RPC so we recommend making sure this feature is fully disabled if you don’t need it active. We will disable XML-RPC for you.',
				'defender-security'
			),
			'bulk_title'       => 'XML-RPC',
		);
	}
}
