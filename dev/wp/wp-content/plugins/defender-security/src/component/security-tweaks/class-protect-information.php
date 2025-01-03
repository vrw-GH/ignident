<?php
/**
 * Manages the protection against information disclosure by configuring server settings.
 *
 * @package WP_Defender\Component\Security_Tweaks
 */

namespace WP_Defender\Component\Security_Tweaks;

use WP_Error;
use Calotes\Base\Component;
use WP_Defender\Component\Security_Tweaks\Servers\Server;

/**
 * Class Protect_Information
 */
class Protect_Information extends Component {

	/**
	 * Slug identifier for the component.
	 *
	 * @var string
	 */
	public $slug = 'protect-information';

	/**
	 * Check whether the issue has been resolved or not.
	 *
	 * @return bool
	 */
	public function check() {
		return Server::create( Server::get_current_server() )->from( $this->slug )->check();
	}

	/**
	 * Here is the code for processing, if the return is true, we add it to resolve list, WP_Error if any error.
	 *
	 * @param  string $current_server  The current server software being used.
	 *
	 * @return bool|WP_Error
	 */
	public function process( $current_server ) {
		if ( 'apache' === $current_server || 'litespeed' === $current_server ) {
			// Because the Prevent_PHP tweak uses the 'process' method too.
			return Server::create( $current_server )->from( $this->slug )->process( false );
		} else {

			return Server::create( $current_server )->from( $this->slug )->process();
		}
	}

	/**
	 * This is for un-do stuff that has been done in @process.
	 *
	 * @param  string|null $current_server  The current server software being used.
	 *
	 * @return bool|WP_Error
	 */
	public function revert( $current_server = null ) {
		if ( is_null( $current_server ) ) {
			$current_server = Server::get_current_server();
		}

		return Server::create( $current_server )->from( $this->slug )->revert();
	}

	/**
	 * Shield up.
	 *
	 * @return bool
	 */
	public function shield_up() {
		return true;
	}

	/**
	 * Return a summary data of this tweak.
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'slug'            => $this->slug,
			'title'           => esc_html__( 'Prevent Information Disclosure', 'defender-security' ),
			'errorReason'     => esc_html__( 'You don\'t have information disclosure protection active.', 'defender-security' ),
			'successReason'   => esc_html__(
				'You\'ve automatically enabled information disclosure protection.',
				'defender-security'
			),
			'misc'            => array(
				'active_server' => Server::get_current_server(),
				'apache_rules'  => Server::create( 'apache' )->from( $this->slug )->get_rules_for_instruction(),
				'nginx_rules'   => Server::create( 'nginx' )->from( $this->slug )->get_rules(),
			),
			'blk_description' => esc_html__(
				'Often servers are incorrectly configured, and can allow an attacker to get access to sensitive files like your config, .htaccess and backup files. We will automatically add an .htaccess file to your root folder to action this fix.',
				'defender-security'
			),
			'bulk_title'      => esc_html__( 'Information Disclosure', 'defender-security' ),
		);
	}
}
