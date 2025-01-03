<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }
echo '<input type="hidden" id="pb-max-execution-time" value="'.ini_get('max_execution_time').'">';
$backups_info = WP_CONTENT_DIR . '/autobackups/backups-info';
$info = glob($backups_info.'/*.*');
// sort files by last modified date
usort($info, function($x, $y) {
    return filemtime($x) < filemtime($y) ? 0 : 1;
});

$imgDir = AUTO_BACKUP_URL.'/admin/images';
$last_backup = esc_html__('No backup created.', 'autobackup');
if(!empty($info)){
	$last_backup = json_decode(file_get_contents(end($info)));
	$last_backup = date('jS M Y H:i:s A', $last_backup->created);
}
$last_restore = esc_html__('No backup restored yet.', 'autobackup');
if(!empty(get_option('auto_backup_last_restore'))){
	$last_restore = date('jS M Y H:i:s A', get_option('auto_backup_last_restore'));	
}
$storage = get_option('auto_backup_cloud_storage');
?>
<!-- Main Wrapper  -->
<div class="pb-main-wrapper">
     
    <!-- Main Content  -->
	<div class="pb-page-content">

        <!-- Page Specific  -->
        <div class="pb-dashbord-wrap">
            <div class="pb-aside bckup-card">
                <div class="bckup-card-head">
                    <h4><?php echo esc_html__('Auto Backup','autobackup'); ?></h4>
                </div>
                <div class="pb-aside-content">
                    <div class="pb-backup-status">
                        <div class="pb-status-info">
                            <h5>
                                <?php echo esc_html__('Last Backup','autobackup'); ?>
                            </h5>
                            <span>
                                <img src="<?php echo esc_url(plugins_url('autobackup/admin/images/date-time.svg')); ?>" alt="<?php echo esc_attr__('date-time','autobackup'); ?>">
                                <?php echo esc_html( $last_backup ); ?>
                            </span>
                        </div>
                        <div class="pb-status-info">
                            <h5>
                                <?php echo esc_html__('Last Restore','autobackup'); ?>
                            </h5>
                            <span> 
                                <img src="<?php echo esc_url(plugins_url('autobackup/admin/images/date-time.svg')); ?>" alt="<?php echo esc_attr__('date-time','autobackup'); ?>">
                                <?php echo esc_html( $last_restore ); ?>
                            </span>
                        </div>
                    </div>
                    <h4 class="pb-sub-title"><?php echo esc_html__('Create Backup','autobackup'); ?></h4>
                    <div class="pb-create-backup-wrap">
                        <div class="pb-input-wrapper pb-checkboxes">
                            <div class="pb-checkbox">
                                <input type="checkbox" id="directories_bkup" name="backupoptiopn" value="<?php echo esc_attr__('directories','autobackup'); ?>"/>
                                <label for="directories_bkup"><?php echo esc_html__('Backup Files','autobackup'); ?></label>
                            </div>
                            <div class="pb-checkbox">
                                <input type="checkbox" id="database_bkup" name="backupoptiopn" value="<?php echo esc_attr__('database','autobackup'); ?>" />
                                <label for="database_bkup"><?php echo esc_html__('Backup Database','autobackup'); ?></label>
                            </div>
                        </div>
                        <div class="pb-input-wrapper">
                            <label for=""><?php echo esc_html__('Backup Locations','autobackup'); ?></label>
                            <select id="pb-cloud">
                                <option value="local"><?php echo esc_html__('Local Folder (Default)','autobackup'); ?></option>
								<?php
								if(!empty($storage)){
									foreach($storage as $cloud => $val){
										$lbl = $cloud == 'gdrive' ? esc_html__('Google Drive','autobackup') : ($cloud == 's3' ? esc_html__('AWS S3','autobackup') : ($cloud == 'ftp' ? esc_html__('FTP','autobackup') : $cloud));
										echo '<option value="'.esc_attr($cloud).'">'.esc_html($lbl).'</option>';
									}
								}
								?>
                            </select> 
                        </div>
                        <div class="pb-preogress-bar-wrap" style="display:none;">
                            <div class="pb-progressbar">
                                <span class="pb-bar-filler"></span>
                                <span class="pb-bar-counter"></span>
                            </div>
                        </div>
                        <div class="pb-btn-wrap">
                            <a href="javascript:void(0)" id="pb-create-backup" class="pb-btn"><?php echo esc_html__('Create','autobackup'); ?> <span class="pb-btn-loader"><img src="<?php echo esc_url(AUTO_BACKUP_URL.'/admin/images/loader.svg'); ?>" alt="<?php echo esc_attr__('loader','autobackup'); ?>"></span></a>
                        </div>
                    </div>
               </div>
            </div>
            <div class="pb-data-wrapp bckup-card">
                <div class="bckup-card-head">
                    <h4><?php echo esc_html__('Backup History','autobackup'); ?></h4>
                </div>
                <div class="pb-card-content">
                    <div class="pb-table-action-btns">
                        <a href="javascript:void(0)" id="pb-delete-multi-bkp" class="pb-btn pb-dark-btn pb-btn-disable"><?php echo esc_html__('Delete','autobackup'); ?> <span class="pb-btn-loader"><img src="<?php echo esc_url($imgDir); ?>/loader.svg" alt="<?php echo esc_attr__('loader','autobackup'); ?>"></span></a>
                        <a href="javascript:void(0)" id="pb-select-all-bkp" class="pb-btn"><?php echo esc_html__('Select All','autobackup'); ?></a>
                    </div>
                    <div class="pb-table-wrap">
                        <div class="pb-table-responsive">
                            <table imgdir="<?php echo esc_url($imgDir); ?>">
                                <thead>
                                    <tr>
                                        <th><?php echo esc_html__('#','autobackup'); ?></th>
                                        <th><?php echo esc_html__('Backup Time','autobackup'); ?>
										  <div class="pb-sort-wrap">
											<img src="<?php echo esc_url($imgDir); ?>/up-arrow.png" class="pb-backup-date" data-attr="desc">
											<img src="<?php echo esc_url($imgDir); ?>/down.png" class="pb-backup-date" data-attr="asc">
										  </div>
										</th>
                                        <th><?php echo esc_html__('Backup Location','autobackup'); ?> </th>
                                        <th><?php echo esc_html__('File Size','autobackup'); ?> 
										  <div class="pb-sort-wrap">
											<img src="<?php echo esc_url($imgDir); ?>/up-arrow.png" class="pb-backup-size" data-attr="desc">
											<img src="<?php echo esc_url($imgDir); ?>/down.png" class="pb-backup-size" data-attr="asc">
										  </div>
										</th>
                                        <th><?php echo esc_html__('Backup Type','autobackup'); ?> </th>
                                        <th><?php echo esc_html__('Action','autobackup'); ?> </th>
                                    </tr>
                                </thead>
                                <tbody class="pb-sorting-table">
									<?php
									if(!empty($info)){
										$info = array_reverse($info);
										$size = new Auto_Backup();
										$count = 1;
										$gdrive_file = '';
										
										foreach($info as $file) {
											$data = file_get_contents($file);
											$data = json_decode($data);
										}
										foreach($info as $file) {
											$data = file_get_contents($file);
											$data = json_decode($data);
											
											$bkpType = $data->backup_dir == 'yes' ? esc_html__('Files','autobackup') : '';
											$bkpType .= !empty($bkpType) && $data->backup_db == 'yes' ? ',' : '';
											$bkpType .= $data->backup_db == 'yes' ? esc_html__('Database','autobackup') : '';
											
											//Google dirve fileid
											$fileid = isset($data->fileid) ? "fileid=".$data->fileid."" : '';
											?>
											<tr bkpname="<?php echo esc_attr($data->name); ?>" bklocation="<?php echo esc_attr($data->location); ?>" <?php echo esc_attr($fileid); ?>>
												<td>
													<div class="pb-checkbox">
														<input type="checkbox" id="row-'<?php echo esc_attr($count); ?>" name="bkprow">
														<label for="row-<?php echo esc_attr($count); ?>"></label>
													</div>
												</td>
												<td>
													<span class="pb-tb-timing">
														<?php echo esc_html(date('jS M Y H:i:s A', $data->created)); ?> 
													</span>
												</td>
												<td>
													<span class="pb-tb-location">
													<?php 
													if($data->location == 'Dropbox'){
														echo '<img src="'.esc_url($imgDir.'/drop-box.png').'" alt="'.esc_attr__('Dropbox','autobackup').'">'.esc_html__('Dropbox','autobackup');
                                                    }elseif($data->location == 'Google_Drive'){
														echo '<img src="'.esc_url($imgDir.'/drive.png').'" alt="'.esc_attr__('Google Drive','autobackup').'">'.esc_html__('Google Drive','autobackup');
													}elseif($data->location == 'S3'){
														echo '<img src="'.esc_url($imgDir.'/amazons3.png').'" alt="'.esc_attr__('S3','autobackup').'">'.esc_html__('S3','autobackup');	
													}elseif($data->location == 'FTP'){
														echo '<img src="'.esc_url($imgDir.'/ftp.png').'" alt="'.esc_attr__('FTP','autobackup').'">'.esc_html__('FTP','autobackup');	
													}elseif($data->location == 'NeevCloud'){
														echo '<img src="'.esc_url($imgDir.'/neevcloud-icon.png').'" alt="'.esc_attr__('NeevCloud','autobackup').'">'.esc_html__('NeevCloud','autobackup');	
													}else{
														echo '<img src="'.esc_url($imgDir.'/folder.png').'" alt="'.esc_attr__('Local','autobackup').'">'.esc_html__('Local','autobackup');	
													}
													?>
													</span>
												</td> 
												<td>
													<span class="pb-tb-file-size">
														<?php echo esc_html($size->auto_backup_beautify_bytes($data->size)); ?>
													</span>
												</td>
												<td>
													<span class="pb-tb-will-restore">
														<?php echo esc_html($bkpType); ?>
													</span>
												</td>
												<td>
													<div class="pb-tb-actions">
														<ul>
															<li class="pb-tooltip-wrap pb-restore">
																<div class="pb-tooltip"><?php echo esc_html__('Restore','autobackup'); ?></div>
																<a href="javascript:;" class="pb-restore-backup">
																	<img src="<?php echo esc_url($imgDir.'/restore.svg'); ?>" alt="<?php echo esc_attr__('Restore','autobackup'); ?>">
																</a>
															</li> 
															<li class="pb-tooltip-wrap pb-delete">
																<div class="pb-tooltip"><?php echo esc_html__('Delete','autobackup'); ?></div>
																<a href="javascript:;" class="pb-delete-backup">
																<img src="<?php echo esc_url($imgDir.'/delete.svg'); ?>" alt="<?php echo esc_attr__('delete','autobackup'); ?>">
																</a>
															</li>
															<li class="pb-tooltip-wrap pb-download pb-location-'<?php echo esc_attr($data->location); ?>">
																<div class="pb-tooltip"><?php echo esc_html__('Download','autobackup'); ?></div>
																<?php 
																$downloadURL = $data->downloadurl;
																if($data->location == 'Dropbox'){
																	echo '<a href="'.esc_url($downloadURL).'" target="_blank">
																     	  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
																	     </a>';
																}elseif($data->location == 'Google_Drive'){
																	$gdrive_file = $data->downloadurl;
																	echo '<a href="javascript:;" gdrivefile="'.esc_url($gdrive_file).'">
																		<img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
																	    </a>';
																}elseif($data->location == 'S3'){
																	$s3 = $data->downloadurl;
																	echo '<a href="'.esc_url($downloadURL).'" target="_blank">
																		  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
																	     </a>';
																}elseif($data->location == 'NeevCloud'){
																	echo '<a href="'.esc_url($downloadURL).'" target="_blank">
																		  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
																	     </a>';
																}elseif($data->location == 'FTP'){
																	$s3 = $data->downloadurl;
																	echo '<a href="'.esc_url($downloadURL).'" target="_blank">
																		  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
																	     </a>';
																}else{
																	$downloadURL = content_url('/autobackups/backups/'.$data->name.'.zip');
																	echo '<a href="'.esc_url($downloadURL).'">
																	      <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
																	     </a>';
																}
																?>
															</li>
														</ul>
													</div>
												</td>
											</tr>
										<?php
										}
									}else{
										echo '<tr class="pb-no-data"><td colspan="6">'.esc_html__('No backup has been created', 'autobackup').'.</td></tr>';
									}
									?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
     </div>
</div>  