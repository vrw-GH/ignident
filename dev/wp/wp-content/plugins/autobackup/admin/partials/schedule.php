<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }
$settings = get_option('auto_backup_sheduled_settings');
$fileBkp = isset($settings['file_schedule']) ? $settings['file_schedule'] : '';
$dbBkp = isset($settings['db_schedule']) ? $settings['db_schedule'] : '';
$location = isset($settings['location']) ? $settings['location'] : '';
?>
<div class="pb-main-wrapper">
    <!-- Main Content  -->
	<div class="pb-page-content pb-schedule-page">
        <!-- Page Specific  -->
        <div class="pb-dashbord-wrap">
			<div class="pb-aside bckup-card">
                <div class="bckup-card-head">
                    <h4><?php echo esc_html__('Schedule Backup', 'autobackup'); ?></h4>
                    <p><?php echo esc_html__('The action will trigger when someone visits your WordPress site if the scheduled time has passed', 'autobackup'); ?>.</p>
                </div>
                <div class="pb-aside-content">
                    <div class="pb-create-backup-wrap">
						<form id="pb-schedule-form">
							<div class="pb-input-wrapper"> 
								<label for=""><?php echo esc_html__('Files backup schedule', 'autobackup'); ?></label>
								<select name="file_schedule" id="pb-cloud">
									<option value="manual" <?php echo ($fileBkp == 'manual') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Manual', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('two_hourly','autobackup'); ?>" <?php echo ($fileBkp == 'two_hourly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Every 2 hours', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('four_hourly','autobackup'); ?>" <?php echo ($fileBkp == 'four_hourly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Every 4 hours', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('eight_hourly','autobackup'); ?>" <?php echo ($fileBkp == 'eight_hourly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Every 8 hours', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('twelve_hourly','autobackup'); ?>" <?php echo ($fileBkp == 'twelve_hourly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Every 12 hours', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('daily','autobackup'); ?>" <?php echo ($fileBkp == 'daily') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Daily', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('weekly','autobackup'); ?>" <?php echo ($fileBkp == 'weekly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Weekly', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('fortnightly','autobackup'); ?>" <?php echo ($fileBkp == 'fortnightly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Fortnightly', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('monthly','autobackup'); ?>" <?php echo ($fileBkp == 'monthly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Monthly', 'autobackup'); ?></option>
								</select>
							</div>
							<div class="pb-input-wrapper">
								<label for=""><?php echo esc_html__('Database backup schedule', 'autobackup'); ?></label>
								<select name="db_schedule" id="pb-cloud">
									<option value="manual" selected="<?php echo esc_attr__('selected','autobackup'); ?>"><?php echo esc_html__('Manual', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('hourly','autobackup'); ?>" <?php echo ($dbBkp == 'hourly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Every 1 hour', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('two_hourly','autobackup'); ?>" <?php echo ($dbBkp == 'two_hourly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Every 2 hours', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('four_hourly','autobackup'); ?>" <?php echo ($dbBkp == 'four_hourly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Every 4 hours', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('eight_hourly','autobackup'); ?>" <?php echo ($dbBkp == 'eight_hourly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Every 8 hours', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('twelve_hourly','autobackup'); ?>" <?php echo ($dbBkp == 'twelve_hourly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Every 12 hours', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('daily','autobackup'); ?>" <?php echo ($dbBkp == 'daily') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Daily', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('weekly','autobackup'); ?>" <?php echo ($dbBkp == 'weekly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Weekly', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('fortnightly','autobackup'); ?>" <?php echo ($dbBkp == 'fortnightly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Fortnightly', 'autobackup'); ?></option>
									<option value="<?php echo esc_attr__('monthly','autobackup'); ?>" <?php echo ($dbBkp == 'monthly') ? esc_attr__('selected','autobackup') : '';?>><?php echo esc_html__('Monthly', 'autobackup'); ?></option>
								</select>
							</div> 
							<div class="pb-input-wrapper">
								<label for="pb-cloud"><?php echo esc_html__('Backup Locations','autobackup'); ?></label>
								<select name="location" id="pb-cloud">
									<option value="<?php echo esc_attr__('local','autobackup'); ?>"><?php echo esc_html__('Local Folder (Default)','autobackup'); ?></option>
									<?php
									$storage = get_option('auto_backup_cloud_storage');
									if(!empty($storage)){
										foreach($storage as $cloud => $val){
											$lbl = $cloud == 'gdrive' ? esc_html__('Google Drive','autobackup') : ($cloud == 's3' ? esc_html__('AWS S3','autobackup') : ($cloud == 'ftp' ? esc_html__('FTP','autobackup') : $cloud)); 
											$sel = $location == $cloud ? esc_attr__('selected','autobackup') : '';
											echo '<option value="'.esc_attr($cloud).'" '.esc_attr($sel).'>'.esc_html($lbl).'</option>';
										}
									}
									?>
								</select>
							</div>
							<div class="pb-btn-wrap">
								<button type="submit" class="pb-btn"><?php echo esc_html__('Save Changes','autobackup'); ?>
							    </button>
							</div>
						</form>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>  