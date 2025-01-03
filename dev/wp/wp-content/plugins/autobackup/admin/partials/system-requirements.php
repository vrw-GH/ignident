<?php 
if ( ! defined( 'ABSPATH' ) ){ exit; }
$extensions = get_loaded_extensions();
?>
<div class="pb-main-wrapper">
	<div class="pb-page-content">
		<div class="autobackup_main_heading">
			<h2 class=""><?php echo esc_html__( 'System Requirements', 'autobackup' ); ?></h2>
		</div>
		<div class="pb-system-requirements">
			<div class="bckup-card">
				<div class="bckup-card-head">
					<h4 data-export-label="<?php esc_attr_e('WordPress Configuration','autobackup') ?>">
						<?php esc_html_e( 'WordPress Configuration', 'autobackup' ); ?>
					</h4>
				</div>
				<div class="pb-card-content">
					<table class="widefat" cellspacing="0">
						<tbody>
							<tr>
								<th data-export-label="<?php esc_attr_e('Home URL','autobackup') ?>"><?php esc_html_e( 'Home URL:', 'autobackup' ); ?></th>
								<td><?php echo esc_url( home_url() ); ?></td>
							</tr>
							<tr>
								<th data-export-label="<?php esc_attr_e('Site URL','autobackup') ?>"><?php esc_html_e( 'Site URL:', 'autobackup' ); ?></th>
								<td><?php echo esc_url( site_url() ); ?></td>
							</tr>
							<tr>
								<th data-export-label="<?php esc_attr_e('WP Version','autobackup') ?>"><?php esc_html_e( 'WP Version:', 'autobackup' ); ?></th>
								<?php
									$ver = (float) get_bloginfo( 'version' );
									$vercls =  $ver > 6 ? 'yes' : '';
								?>
								<td class="<?php echo esc_html($vercls); ?>"><?php esc_html(bloginfo( 'version' )); ?></td>
							</tr>
							<tr>
								<th data-export-label="<?php esc_attr_e('WP Multisite','autobackup') ?>"><?php esc_html_e( 'WP Multisite:', 'autobackup' ); ?></th>
								<td><?php echo ( is_multisite() ) ? '&#10004;' : '&ndash;'; ?></td>
							</tr>
							<tr>
								<th data-export-label="<?php esc_attr_e('PHP Memory Limit','autobackup') ?>"><?php esc_html_e( 'PHP Memory Limit:', 'autobackup' ); ?></th>
								<td>
									<?php
									// Get the memory from PHP's configuration.
									$memory = ini_get( 'memory_limit' );
									// If we can't get it, fallback to WP_MEMORY_LIMIT.
									if ( ! $memory || -1 === $memory ) {
										$memory = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
									}
									// Make sure the value is properly formatted in bytes.
									if ( ! is_numeric( $memory ) ) {
										$memory = wp_convert_hr_to_bytes( $memory );
									}
									?>
									<?php if ( $memory < 128000000 ) : ?>
										<mark class="error">
										<?php echo esc_attr( size_format( $memory ) ) . ' - We recommend setting memory to at least <strong>128MB</strong>.'
										?>
										</mark>
									<?php else : ?>
										<mark class="yes">
											<?php echo esc_attr( size_format( $memory ) ); ?>
										</mark>
										
									<?php endif; ?>
								</td>
							</tr>
							<tr>
								<th data-export-label="<?php esc_attr_e('WP Debug Mode','autobackup') ?>"><?php esc_html_e( 'WP Debug Mode:', 'autobackup' ); ?></th>
							
								<td>
									<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
										<mark class="yes"><?php echo esc_html__('Yes','autobackup'); ?></mark>
									<?php else : ?>
										<mark class="no"><?php echo esc_html__('No','autobackup'); ?></mark>
									<?php endif; ?>
								</td>
							</tr>
							<tr> 
								<th data-export-label="<?php esc_attr_e('Language','autobackup') ?>"><?php esc_html_e( 'Language:', 'autobackup' ); ?></th>
								
								<td><?php echo esc_attr( get_locale() ) ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="bckup-card">
				<div class="bckup-card-head">
					<h4 data-export-label="<?php esc_attr_e('Server Configuration','autobackup') ?>">
					<?php esc_html_e( 'Server Configuration', 'autobackup' ); ?>
					</h4>
				</div>
				<div class="pb-card-content">
					<table class="widefat" cellspacing="0">
						<tbody>
							<tr>
								<th data-export-label="<?php esc_attr_e('Server Info','autobackup') ?>"><?php esc_html_e( 'Server Info:', 'autobackup' ); ?></th>
								<td><?php echo isset( $_SERVER['SERVER_SOFTWARE'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) ) : esc_attr__( 'Unknown', 'autobackup' ); ?></td>
							</tr>
							<tr>
								<th data-export-label="<?php esc_attr_e('PHP Version','autobackup') ?>"><?php esc_html_e( 'PHP Version:', 'autobackup' ); ?></th>
								<td>
									<?php
									$php_version = null;
									if ( defined( 'PHP_VERSION' ) ) {
										$php_version = PHP_VERSION;
									} elseif ( function_exists( 'phpversion' ) ) {
										$php_version = phpversion();
									}
									if ( null === $php_version ) {
										$message = esc_attr__( 'PHP Version could not be detected.', 'autobackup' );
									} else {
										if ( version_compare( $php_version, '7.0.0' ) >= 0 ) {
											$message = '<mark class="yes">'.$php_version.'<mark>';
										} else {
											$message = '<mark class="error">' . $php_version . ' - Recommended 7.0 or grater. </mark>';
										}
									}
									echo '<mark class="error">'.wp_kses_post( $message ).'<mark>';
									?>
								</td>
							</tr>
							<?php if ( function_exists( 'ini_get' ) ) : ?>
								<tr>
									<th data-export-label="<?php esc_attr_e('PHP Post Max Size','autobackup') ?>"><?php esc_html_e( 'PHP Post Max Size:', 'autobackup' ); ?></th>
									<td><?php echo esc_attr( size_format( wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) ) ) ); ?></td>
								</tr>
								
								<tr>
									<th data-export-label="<?php esc_attr_e('Max Upload Size','autobackup') ?>"><?php esc_html_e( 'Max Upload Size:', 'autobackup' ); ?></th>
									<td><?php echo esc_attr( size_format( wp_max_upload_size() ) ); ?></td>
								</tr>
								
								<tr>
									<th data-export-label="<?php esc_attr_e('PHP Time Limit','autobackup') ?>"><?php esc_html_e( 'PHP Time Limit:', 'autobackup' ); ?></th>
									<td>
										<?php
										$time_limit = ini_get( 'max_execution_time' );

										if ( 180 > $time_limit && 0 != $time_limit ) {
											echo wp_kses_post( '<mark class="error">' . sprintf( __( '%1$s - We recommend setting max execution time to at least 180. <br /> To import classic demo content, <strong>300</strong> seconds of max execution time is required.<br />See: <a href="%2$s" target="_blank" rel="noopener noreferrer">Increasing max execution to PHP</a>', 'autobackup' ), $time_limit, 'http://codex.wordpress.org/Common_WordPress_Errors#Maximum_execution_time_exceeded' ) . '</mark>' );
										} else {
											echo '<mark class="yes">' . esc_attr( $time_limit ) . '</mark>';
											if ( 300 > $time_limit && 0 != $time_limit ) {
												echo wp_kses_post( '<br /><mark class="error">' . __( 'The current time limit is sufficient, but it should be increased if you got an issue during backup creation or import.', 'autobackup' ) . '</mark>' );
											}
										}
										?>
									</td>
								</tr>
								<tr>
									<th data-export-label="<?php esc_attr_e('PHP Max Input Vars','autobackup') ?>"><?php esc_html_e( 'PHP Max Input Vars:', 'autobackup' ); ?></th>
									<?php
									$registered_navs = get_nav_menu_locations();
									$menu_items_count = array(
										'0' => '0',
									);
									foreach ( $registered_navs as $handle => $registered_nav ) {
										$menu = wp_get_nav_menu_object( $registered_nav );
										if ( $menu ) {
											$menu_items_count[] = $menu->count;
										}
									}

									$max_items = max( $menu_items_count );
									$required_input_vars = $max_items * 20;
									?>
									<td>
										<?php
										$max_input_vars = ini_get( 'max_input_vars' );
										$required_input_vars = 3000;
										// 1000 = theme options
										if ( $max_input_vars < $required_input_vars ) {
											echo wp_kses_post( '<mark class="error">' . sprintf( __( '%1$s - Recommended Value: %2$s.<br />Max input vars limitation will truncate POST data such as menus. See: <a href="%3$s" target="_blank" rel="noopener noreferrer">Increasing max input vars limit.</a>', 'autobackup' ), $max_input_vars, '<strong>' . $required_input_vars . '</strong>', 'http://sevenspark.com/docs/ubermenu-3/faqs/menu-item-limit' ) . '</mark>' );
										} else {
											echo '<mark class="yes">' . esc_attr( $max_input_vars ) . '</mark>';
										}
										?>
									</td>
								</tr>
							<?php endif; ?>
							<tr>
								<th data-export-label="<?php esc_attr_e('MySQL Version','autobackup') ?>"><?php esc_html_e( 'MySQL Version:', 'autobackup' ); ?></th>
								
								<td>
									<?php global $wpdb; ?>
									<?php echo esc_attr( $wpdb->db_version() ); ?>
								</td>
							</tr>
							
							<tr>
								<th data-export-label="<?php esc_attr_e('ZipArchive','autobackup') ?>"><?php esc_html_e( 'ZipArchive:', 'autobackup' ); ?></th>
								
								<td><?php echo in_array('zip', $extensions) ? '<mark class="yes">&#10004;</mark>' : '<mark class="error">ZipArchive is not installed on your server, but it is required for decompressing Plugins, Themes, WordPress update packages, and demo content installations.</mark>'; ?></td>
							</tr>
							
							<tr>
								<th data-export-label="<?php esc_attr_e('Curl','autobackup') ?>"><?php esc_html_e( 'Curl:', 'autobackup' ); ?></th>
								
								<td><?php echo in_array('curl', $extensions) ? '<mark class="yes">&#10004;</mark>' : '<mark class="error">Curl is not installed on your server, it is required because it performs remote request operations.</mark>'; ?></td>
							</tr>
							
							<tr>
								<th data-export-label="<?php esc_attr_e('GD Library','autobackup') ?>"><?php esc_html_e( 'GD Library:', 'autobackup' ); ?></th>
								
								<td>
									<?php
									$info = esc_attr__( 'Not Installed', 'autobackup' );
									if ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) {
										$info = esc_attr__( 'Installed', 'autobackup' );
										$gd_info = gd_info();
										if ( isset( $gd_info['GD Version'] ) ) {
											$info = $gd_info['GD Version'];
										}
									}
									echo esc_attr( $info );
									?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div> 