<?php
/**
 * Auditing changes to WordPress settings.
 *
 * @package WP_Defender\Component\Audit
 */

namespace WP_Defender\Component\Audit;

use WP_Defender\Traits\User;
use WP_Defender\Model\Audit_Log;

/**
 * Handles changes to WordPress settings such as updating options and deleting options.
 */
class Options_Audit extends Audit_Event {

	use User;

	public const CONTEXT_SETTINGS = 'ct_setting';

	/**
	 * Returns an array of hooks that the audit system will listen to.
	 * Specifically, it listens to the update_option hook to audit changes made to WordPress options.
	 *
	 * @return array[]
	 */
	public function get_hooks(): array {
		return array(
			'update_option' => array(
				'args'        => array( 'option', 'old_value', 'value' ),
				'callback'    => array( self::class, 'process_options' ),
				'event_type'  => Audit_Log::EVENT_TYPE_SETTINGS,
				'action_type' => self::ACTION_UPDATED,
			),
		);
	}

	/**
	 * Processes the changes made to an option and generates an audit log entry if the option value has changed.
	 * It serializes array values for comparison and uses a human-readable format for logging.
	 *
	 * @return bool|array
	 */
	public function process_options() {
		$args              = func_get_args();
		$option            = $args[1]['option'];
		$old               = $args[1]['old_value'];
		$new               = $args[1]['value'];
		$option_human_read = self::key_to_human_name( $option );
		// To avoid the recursive compare if both are nested array, convert all to string.
		$check1 = is_array( $old ) ? wp_json_encode( $old ) : $old;
		$check2 = is_array( $new ) ? wp_json_encode( $new ) : $new;

		if ( $check1 === $check2 ) {
			return false;
		}
		if ( false !== $option_human_read ) {
			$user_name = $this->get_user_display( get_current_user_id() );
			$blog_name = is_multisite() ? '[' . get_bloginfo( 'name' ) . ']' : '';
			// We will need special case for reader.
			switch ( $option ) {
				case 'users_can_register':
					if ( 0 === $new ) {
						$text = sprintf(
						/* translators: 1: Blog name, 2: User's display name */
							esc_html__( '%1$s %2$s disabled site registration', 'defender-security' ),
							$blog_name,
							$user_name
						);
					} else {
						$text = sprintf(
						/* translators: 1: Blog name, 2: User's display name */
							esc_html__( '%1$s %2$s opened site registration', 'defender-security' ),
							$blog_name,
							$user_name
						);
					}
					break;
				case 'start_of_week':
					global $wp_locale;
					$old_day = $wp_locale->get_weekday( $old );
					$new_day = $wp_locale->get_weekday( $new );
					$text    = sprintf(
					/* translators: 1: Blog name, 2: User's display name, 3: Option: Week Starts On, 4: Old day, 5: New day */
						esc_html__( '%1$s %2$s update option %3$s from %4$s to %5$s', 'defender-security' ),
						$blog_name,
						$user_name,
						$option_human_read,
						$old_day,
						$new_day
					);
					break;
				case 'WPLANG':
					if ( '' !== $new ) {
						$text = sprintf(
						/* translators: 1: Blog name, 2: User's display name, 3: Option: Site Language, 4: New option value */
							esc_html__( '%1$s %2$s update option %3$s to %4$s', 'defender-security' ),
							$blog_name,
							$user_name,
							$option_human_read,
							$new
						);
					} else {
						$text = sprintf(
						/* translators: 1: Blog name, 2: User's display name, 3: Option: Site Language, 4: Old option value */
							esc_html__( '%1$s %2$s update option %3$s from %4$s', 'defender-security' ),
							$blog_name,
							$user_name,
							$option_human_read,
							$old
						);
					}
					break;
				default:
					$text = sprintf(
					/* translators: 1: Blog name, 2: User's display name, 3: Option label, 4: Old option value, 5: New option value */
						esc_html__( '%1$s %2$s update option %3$s from %4$s to %5$s', 'defender-security' ),
						$blog_name,
						$user_name,
						$option_human_read,
						$old,
						$new
					);
					break;
			}

			return array( $text, self::CONTEXT_SETTINGS );
		}

		return false;
	}

	/**
	 * Converts an option key to a human-readable name using a predefined list. If the key is not found in the list, it
	 * returns the key itself.
	 *
	 * @param  mixed $key  The option key to convert.
	 *
	 * @return mixed
	 */
	private static function key_to_human_name( $key ) {
		$human_read = apply_filters(
			'wd_audit_settings_keys',
			array(
				'blogname'                      => esc_html__( 'Site Title', 'defender-security' ),
				'blogdescription'               => esc_html__( 'Tagline', 'defender-security' ),
				'gmt_offset'                    => esc_html__( 'Timezone', 'defender-security' ),
				'date_format'                   => esc_html__( 'Date Format', 'defender-security' ),
				'time_format'                   => esc_html__( 'Time Format', 'defender-security' ),
				'start_of_week'                 => esc_html__( 'Week Starts On', 'defender-security' ),
				'timezone_string'               => esc_html__( 'Timezone', 'defender-security' ),
				'WPLANG'                        => esc_html__( 'Site Language', 'defender-security' ),
				'siteurl'                       => esc_html__( 'WordPress Address (URL)', 'defender-security' ),
				'home'                          => esc_html__( 'Site Address (URL)', 'defender-security' ),
				'admin_email'                   => esc_html__( 'Email Address', 'defender-security' ),
				'users_can_register'            => esc_html__( 'Membership', 'defender-security' ),
				'default_role'                  => esc_html__( 'New User Default Role', 'defender-security' ),
				'default_pingback_flag'         => esc_html__( 'Default article settings', 'defender-security' ),
				'default_ping_status'           => esc_html__( 'Default article settings', 'defender-security' ),
				'default_comment_status'        => esc_html__( 'Default article settings', 'defender-security' ),
				'comments_notify'               => esc_html__( 'Email me whenever', 'defender-security' ),
				'moderation_notify'             => esc_html__( 'Email me whenever', 'defender-security' ),
				'comment_moderation'            => esc_html__( 'Before a comment appears', 'defender-security' ),
				'require_name_email'            => esc_html__( 'Other comment settings', 'defender-security' ),
				'comment_whitelist'             => esc_html__( 'Before a comment appears', 'defender-security' ),
				'comment_max_links'             => esc_html__( 'Comment Moderation', 'defender-security' ),
				'moderation_keys'               => esc_html__( 'Comment Moderation', 'defender-security' ),
				'blacklist_keys'                => esc_html__( 'Comment Blocklist', 'defender-security' ),
				'show_avatars'                  => esc_html__( 'Avatar Display', 'defender-security' ),
				'avatar_rating'                 => esc_html__( 'Maximum Rating', 'defender-security' ),
				'avatar_default'                => esc_html__( 'Default Avatar', 'defender-security' ),
				'close_comments_for_old_posts'  => esc_html__( 'Other comment settings', 'defender-security' ),
				'close_comments_days_old'       => esc_html__( 'Other comment settings', 'defender-security' ),
				'thread_comments'               => esc_html__( 'Other comment settings', 'defender-security' ),
				'thread_comments_depth'         => esc_html__( 'Other comment settings', 'defender-security' ),
				'page_comments'                 => esc_html__( 'Other comment settings', 'defender-security' ),
				'comments_per_page'             => esc_html__( 'Other comment settings', 'defender-security' ),
				'default_comments_page'         => esc_html__( 'Other comment settings', 'defender-security' ),
				'comment_order'                 => esc_html__( 'Other comment settings', 'defender-security' ),
				'comment_registration'          => esc_html__( 'Other comment settings', 'defender-security' ),
				'thumbnail_size_w'              => esc_html__( 'Thumbnail size', 'defender-security' ),
				'thumbnail_size_h'              => esc_html__( 'Thumbnail size', 'defender-security' ),
				'thumbnail_crop'                => esc_html__( 'Thumbnail size', 'defender-security' ),
				'medium_size_w'                 => esc_html__( 'Medium size', 'defender-security' ),
				'medium_size_h'                 => esc_html__( 'Medium size', 'defender-security' ),
				'medium_large_size_w'           => esc_html__( 'Medium size', 'defender-security' ),
				'medium_large_size_h'           => esc_html__( 'Medium size', 'defender-security' ),
				'large_size_w'                  => esc_html__( 'Large size', 'defender-security' ),
				'large_size_h'                  => esc_html__( 'Large size', 'defender-security' ),
				'image_default_size'            => esc_html__( 'Default image size', 'defender-security' ),
				'image_default_align'           => esc_html__( 'Default image align', 'defender-security' ),
				'image_default_link_type'       => esc_html__( 'Default image link type', 'defender-security' ),
				'uploads_use_yearmonth_folders' => esc_html__( 'Uploading Files', 'defender-security' ),
				'posts_per_page'                => esc_html__( 'Blog pages show at most', 'defender-security' ),
				'posts_per_rss'                 => esc_html__( 'Syndication feeds show the most recent', 'defender-security' ),
				'rss_use_excerpt'               => esc_html__( 'For each article in a feed, show', 'defender-security' ),
				'show_on_front'                 => esc_html__( 'Front page displays', 'defender-security' ),
				'page_on_front'                 => esc_html__( 'Front page', 'defender-security' ),
				'page_for_posts'                => esc_html__( 'Posts page', 'defender-security' ),
				'blog_public'                   => esc_html__( 'Search Engine Visibility', 'defender-security' ),
				'default_category'              => esc_html__( 'Default Post Category', 'defender-security' ),
				'default_email_category'        => esc_html__( 'Default Mail Category', 'defender-security' ),
				'default_link_category'         => esc_html__( 'Default Link Category', 'defender-security' ),
				'default_post_format'           => esc_html__( 'Default Post Format', 'defender-security' ),
				'mailserver_url'                => esc_html__( 'Mail Server', 'defender-security' ),
				'mailserver_port'               => esc_html__( 'Port', 'defender-security' ),
				'mailserver_login'              => esc_html__( 'Login Name', 'defender-security' ),
				'mailserver_pass'               => esc_html__( 'Password', 'defender-security' ),
				'ping_sites'                    => esc_html__( 'Update Services', 'defender-security' ),
				'permalink_structure'           => esc_html__( 'Permalink Setting', 'defender-security' ),
				'category_base'                 => esc_html__( 'Category base', 'defender-security' ),
				'tag_base'                      => esc_html__( 'Tag base', 'defender-security' ),
				'registrationnotification'      => esc_html__( 'Registration notification', 'defender-security' ),
				'registration'                  => esc_html__( 'Allow new registrations', 'defender-security' ),
				'add_new_users'                 => esc_html__( 'Add New Users', 'defender-security' ),
				'menu_items'                    => esc_html__( 'Enable administration menus', 'defender-security' ),
				'upload_space_check_disabled'   => esc_html__( 'Site upload space', 'defender-security' ),
				'blog_upload_space'             => esc_html__( 'Site upload space', 'defender-security' ),
				'upload_filetypes'              => esc_html__( 'Upload file types', 'defender-security' ),
				'site_name'                     => esc_html__( 'Network Title', 'defender-security' ),
				'first_post'                    => esc_html__( 'First Post', 'defender-security' ),
				'first_page'                    => esc_html__( 'First Page', 'defender-security' ),
				'first_comment'                 => esc_html__( 'First Comment', 'defender-security' ),
				'first_comment_url'             => esc_html__( 'First Comment URL', 'defender-security' ),
				'first_comment_author'          => esc_html__( 'First Comment Author', 'defender-security' ),
				'welcome_email'                 => esc_html__( 'Welcome Email', 'defender-security' ),
				'welcome_user_email'            => esc_html__( 'Welcome User Email', 'defender-security' ),
				'fileupload_maxk'               => esc_html__( 'Max upload file size', 'defender-security' ),
				'illegal_names'                 => esc_html__( 'Banned Names', 'defender-security' ),
				'limited_email_domains'         => esc_html__( 'Limited Email Registrations', 'defender-security' ),
				'banned_email_domains'          => esc_html__( 'Banned Email Domains', 'defender-security' ),
			)
		);

		if ( isset( $human_read[ $key ] ) ) {
			if ( empty( $human_read[ $key ] ) ) {
				return $key;
			}

			return $human_read[ $key ];
		}

		return false;
	}

	/**
	 * Provides a dictionary for translating audit contexts into human-readable formats.
	 *
	 * @return array
	 */
	public function dictionary(): array {
		return array(
			self::CONTEXT_SETTINGS => esc_html__( 'Settings', 'defender-security' ),
		);
	}
}
