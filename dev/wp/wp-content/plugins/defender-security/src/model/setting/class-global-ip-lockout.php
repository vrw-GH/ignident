<?php
/**
 * Handles global ip lockout settings.
 *
 * @package WP_Defender\Model\Setting
 */

namespace WP_Defender\Model\Setting;

use Calotes\Model\Setting;

/**
 * Model for global ip lockout settings.
 */
class Global_Ip_Lockout extends Setting {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	protected $table = 'wd_global_ip_settings';

	/**
	 * Is module enabled.
	 *
	 * @var bool
	 * @defender_property
	 */
	public $enabled = false;

	/**
	 * Table column for autosync.
	 *
	 * @var bool
	 * @defender_property
	 */
	public $blocklist_autosync = false;

	/**
	 * Validation rules.
	 *
	 * @var array
	 */
	protected $rules = array(
		array( array( 'enabled', 'blocklist_autosync' ), 'boolean' ),
	);

	/**
	 * Define settings labels.
	 *
	 * @return array
	 */
	public function labels(): array {
		return array(
			'enabled'            => self::get_module_name(),
			'blocklist_autosync' => esc_html__( 'Permanently Blocked IPs', 'defender-security' ),
		);
	}

	/**
	 * Get the module name for the Global IP Blocker.
	 *
	 * @return string The module name.
	 */
	public static function get_module_name(): string {
		return esc_html__( 'Global IP Blocker', 'defender-security' );
	}

	/**
	 * Get the module state based on the given flag.
	 *
	 * @param  bool $flag  The flag indicating the module state.
	 *
	 * @return string The module state, either 'active' or 'inactive'.
	 */
	public static function get_module_state( $flag ): string {
		return $flag ? esc_html__( 'active', 'defender-security' ) : esc_html__( 'inactive', 'defender-security' );
	}
}
