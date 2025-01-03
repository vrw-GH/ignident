<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }
$post_max_size ='';
$post_max_size = ini_get('post_max_size');
?>
<div class="pb-main-wrapper">
    <!-- Main Content  -->
	<div class="pb-page-content pb-schedule-page pb-import-page">
        <!-- Page Specific  -->
        <div class="pb-dashbord-wrap">
			<div class="pb-aside bckup-card">
                <div class="bckup-card-head">
                    <h4><?php echo esc_html__('Import Data', 'autobackup'); ?></h4>
                </div>
                <div class="pb-aside-content">
                    <div class="pb-create-backup-wrap">
						<form id="pb-import-form">
							<div class="pb-input-wrapper">
								<label for=""><?php echo esc_html__('Select file to Import', 'autobackup'); ?></label>
								<input id="pb-import-file" type="file" name="import_file" />
								<p><?php printf(esc_html__('Choose a file from your computer: (Maximum size: %s)', 'autobackup'),esc_html($post_max_size)); ?></p>
							</div> 
							<div class="pb-notice">
								<p><span class="pb-warning"><?php echo esc_html__('Warning', 'autobackup'); ?>: </span><?php echo esc_html__('Please take full backup before importing because this is undone processs. It will replace all files and folders and database also if you are importing Database.', 'autobackup')?></p>
							</div>
							<div class="pb-btn-wrap">
								<button type="submit" class="pb-btn btn-disabled"><?php echo esc_html__('Import', 'autobackup'); ?> 
								<span class="pb-btn-loader">
									<img src="<?php echo esc_url(AUTO_BACKUP_URL.'/admin/images/loader.svg'); ?>">
							    </span>
							    </button>
							</div>
						</form>
                    </div>
                </div> 
            </div>
        </div>
	</div>
</div>