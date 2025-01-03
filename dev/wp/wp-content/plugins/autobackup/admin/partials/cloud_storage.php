<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }

$auto_backup_drp_app_key = $auto_backup_drp_app_secret = $auto_backup_gdrive_client_id = $auto_backup_gdrive_client_secret = $s3_access_key = $s3_access_secret = $s3_bucket_name = $ftp_host = $ftp_port = $ftp_username = $ftp_password = $nc_bucket = $nc_accesskey = $nc_secretkey = '';

$imgDir = AUTO_BACKUP_URL.'/admin/images';

if(isset($_GET['code'])){
	
	$auto_backup_storage_type = get_transient( 'auto_backup_storage_type' );
	
	if($auto_backup_storage_type == 'dropbox'){
		
		$obj = new Auto_Backup_DB_API();
		$obj->auto_backup_dropbox_generate_token(sanitize_text_field($_GET['code']));
		
	}
	
}
$data = get_option('auto_backup_cloud_storage');

if(!empty($data)){
	if(isset($data['dropbox'])){
		$auto_backup_drp_app_key = $data['dropbox']['app_key'];
		$auto_backup_drp_app_secret = $data['dropbox']['app_secret'];
	}
	
	if(isset($data['s3'])){
		$s3_access_key = $data['s3']['access_key'];
		$s3_access_secret = $data['s3']['access_secret'];
		$s3_bucket_name = $data['s3']['bucket_name'];
	}

	if(isset($data['neevcloud'])){
		$nc_bucket = $data['neevcloud']['bucket_name'];
		$nc_accesskey = $data['neevcloud']['access_key'];
		$nc_secretkey = $data['neevcloud']['access_secret'];
	}
}
?>
<div class="pb-main-wrapper">
    <!-- Main Content  -->
	<div class="pb-page-content pb-settings-page">

        <!-- Page Specific  -->
        <div class="pb-dashbord-wrap">
            <div class="pb-aside bckup-card">
                <div class="bckup-card-head">
                    <h4><?php echo esc_html__('Cloud Storage','autobackup'); ?></h4>
                </div>
                <div class="pb-aside-content">
                    <div class="pb-tabs-nav">
                        <ul>
							<li class="pb-nav-item">
                                <a class="pb-seting-link active" target="1">
                                    <img src="<?php echo esc_url($imgDir.'/neevcloud.png'); ?>" alt="<?php echo esc_attr__('neevcloud','autobackup'); ?>">
                                </a>
                            </li>
                            <li class="pb-nav-item">
                                <a class="pb-seting-link" target="2">
                                    <img src="<?php echo esc_url($imgDir.'/amazon.png'); ?>" alt="<?php echo esc_attr__('amazon','autobackup'); ?>">
                                </a>
                            </li>
                            <li class="pb-nav-item">
                                <a class="pb-seting-link" target="4">
                                    <img src="<?php echo esc_url($imgDir.'/dropbox.png'); ?>" alt="<?php echo esc_attr__('dropbox','autobackup'); ?>">
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="pb-data-wrapp bckup-card">
                <div class="bckup-card-head">
                    <h4><?php echo esc_html__('Connect your cloud storage.','autobackup'); ?></h4>
                </div>
                <div class="pb-card-content pb-tabs-content">
                    <!-- Tab One  -->
                    <div class="pb-single-tab setting1" id="neevcloud" >
                        <div class="pb-tab-section">
                            <h4 class="pb-tab-title"><?php echo esc_html__('NeevCloud Settings','autobackup'); ?></h4>
                            <div class="pb-pay-form">
                                <form id="pb-neevcloud-settings">
                                    <input type="hidden" name="auto_backup_storage_type" value="<?php echo esc_attr__('neevcloud','autobackup'); ?>">
                                    <div class="pb-paypal-set-wrap">
                                        <div class="pb-paypal-section">
                                            <div class="pb-input-wrapper">
                                                <label><?php echo esc_html__('Bucket Name','autobackup'); ?></label>
                                                <input type="text" name="bucket_name" value="<?php echo esc_attr($nc_bucket); ?>">
                                            </div>
											<div class="pb-input-wrapper">
                                                <label><?php echo esc_html__('Access Key','autobackup'); ?></label>
                                                <input type="text" name="access_key" value="<?php echo esc_attr($nc_accesskey); ?>">
                                            </div>
                                            <div class="pb-input-wrapper">
                                                <label><?php echo esc_html__('Secret Key','autobackup'); ?></label>
                                                <input type="text" name="access_secret" value="<?php echo esc_attr($nc_secretkey); ?>">
                                            </div>
                                        </div>	
                                    </div>
                                    <div class="pb-payment-btn">
                                        <button class="pb-btn" type="submit"><?php echo esc_html__('Submit','autobackup'); ?> 
                                        <span class="pb-btn-loader"><img src="<?php echo esc_url($imgDir.'/loader.svg'); ?>"></span></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="pb-single-tab setting2" id="awsS3Tab">
                        <div class="pb-tab-section">
                            <h4 class="pb-tab-title"><?php echo esc_html__('AWS S3 Settings','autobackup'); ?></h4>
                            <div class="pb-pay-form">
                                <form id="pb-aws-settings">
                                    <input type="hidden" name="auto_backup_storage_type" value="<?php echo esc_attr__('s3','autobackup'); ?>">
                                    <div class="pb-paypal-set-wrap">
                                        <div class="pb-paypal-section">
                                            <div class="pb-input-wrapper">
                                                <label><?php echo esc_html__('Bucket Name','autobackup'); ?></label>
                                                <input type="text" name="bucket_name" value="<?php echo esc_attr($s3_bucket_name); ?>">
												<span class="pb-input-note"><a href="<?php echo esc_url('https://docs.aws.amazon.com/AmazonS3/latest/userguide/bucketnamingrules.html'); ?>" target="_blank">
                                                <?php echo esc_html__('Bucket naming rules','autobackup'); ?></a></span>
                                            </div>
											<div class="pb-input-wrapper">
                                                <label><?php echo esc_html__('Access Key','autobackup'); ?></label>
                                                <input type="text" name="access_key" value="<?php echo esc_attr($s3_access_key); ?>">
                                            </div>
                                            <div class="pb-input-wrapper">
                                                <label><?php echo esc_html__('Secret Key','autobackup'); ?></label>
                                                <input type="text" name="access_secret" value="<?php echo esc_attr($s3_access_secret); ?>">
												<span class="pb-input-note"><a href="<?php echo esc_url('https://docs.aws.amazon.com/powershell/latest/userguide/pstools-appendix-sign-up.html'); ?>" target="_blank">
                                                <?php echo esc_html__('How to get credentials?','autobackup'); ?></a></span>
                                            </div>
                                        </div>	
                                    </div>
                                    <div class="pb-payment-btn">
                                        <button class="pb-btn" type="submit"><?php echo esc_html__('Submit','autobackup'); ?>
                                        <span class="pb-btn-loader"><img src="<?php echo esc_url($imgDir.'/loader.svg'); ?>" alt="<?php echo esc_attr__('loader','autobackup'); ?>"></span></button>
                                    </div>
                                </form>  
                            </div>
                        </div>
                    </div>
                    <!-- Tab three  -->
                    <div class="pb-single-tab setting4" id="dropbox" >
                        <div class="pb-tab-section">
                            <h4 class="pb-tab-title"><?php echo esc_html__('Dropbox Settings','autobackup'); ?></h4>
                            <div class="pb-pay-form">
                                <form id="pb-dropbox-settings">
                                    <input type="hidden" name="auto_backup_storage_type" value="dropbox">
                                    <div class="pb-paypal-set-wrap">
                                        <div class="pb-paypal-section">
                                            <div class="pb-input-wrapper">
                                                <label><?php echo esc_html__('App key','autobackup'); ?></label>
                                                <input type="text" name="app_key" value="<?php echo esc_attr($auto_backup_drp_app_key); ?>">
                                            </div>
                                            <div class="pb-input-wrapper">
                                                <label><?php echo esc_html__('App secret','autobackup'); ?></label>
                                                <input type="text" name="app_secret" value="<?php echo esc_attr($auto_backup_drp_app_secret); ?>">
                                                <span class="pb-input-note"><a href="<?php echo esc_url('https://www.dropbox.com/developers/','autobackup'); ?>" target="_blank"><?php echo esc_html__('Click here to get credentials.','autobackup'); ?></a></span>
                                                <span class="pb-input-note"><strong><?php echo esc_html__('Note','autobackup'); ?>:
                                                </strong><?php echo esc_html__('When you create your app, Dropbox will ask you to add redirect URI. Please add following URI on that place','autobackup'); ?> <strong><?php echo esc_url(admin_url('admin.php?page=autobackup-cloud-storage')); ?></strong></span>
                                            </div>
                                        </div> 	
                                    </div>
                                    <div class="pb-payment-btn">
                                        <button class="pb-btn" type="submit"><?php echo esc_html__('Submit','autobackup'); ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
					
				</div>
            </div>
        </div>
	</div>
</div>   