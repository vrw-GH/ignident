<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.autobackup.io/
 * @since      1.0.0
 *
 * @package    Auto_Backup
 * @subpackage Auto_Backup/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Auto_Backup
 * @subpackage Auto_Backup/admin
 * @author     Auto Backup <plugin@autobackup.io>
 */
class Auto_Backup_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Auto_Backup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Auto_Backup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/autobackup-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Auto_Backup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Auto_Backup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/autobackup-admin.js', array( 'jquery' ), $this->version, false );
		
		wp_localize_script('autobackup', 'ajax_object', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('autobackup_ajax_nonce')
		));

	}
	
	/**
	 * Register a custom menu page.
	 */
	public function auto_backup_custom_menu_page() {
		
		add_menu_page(
			esc_html__( 'Auto Backup', 'autobackup'),
			esc_html__('Auto Backup', 'autobackup'),
			'manage_options',
			'pb-dashboard',
			array($this, 'auto_backup_dashboard'),
			'dashicons-cloud-upload',
			80
		);
		
		add_submenu_page(
			'pb-dashboard',
			esc_html__('Cloud Storage','autobackup'),
			esc_html__('Cloud Storage','autobackup'),
			'manage_options',
			'autobackup-cloud-storage',
			array($this, 'auto_backup_storage_settings'),
		);
		
		add_submenu_page(
			'pb-dashboard',
			esc_html__('Schedule','autobackup'),
			esc_html__('Schedule','autobackup'),
			'manage_options',
			'pb-schedule',
			array($this, 'auto_backup_schedule_settings'),
		);
		
		add_submenu_page(
			'pb-dashboard',
			esc_html__('Schedule List','autobackup'),
			esc_html__('Schedule List','autobackup'),
			'manage_options',
			'pb-schedulelist',
			array($this, 'auto_backup_schedule_list'),
		);
		
		add_submenu_page(
			'pb-dashboard',
			esc_html__('Import','autobackup'),
			esc_html__('Import','autobackup'),
			'manage_options',
			'pb-import',
			array($this, 'auto_backup_import_settings'),
		);
		
		add_submenu_page(
			'pb-dashboard',
			esc_html__('Configuration','autobackup'),
			esc_html__('Configuration','autobackup'),
			'manage_options',
			'pb-config',
			array($this, 'auto_backup_configuration'),
		);
		
	}
	
	/**
	 * Plugin Dashboard
	 */
	public function auto_backup_dashboard(){
		require_once AUTO_BACKUP_PATH . '/admin/partials/dashboard.php';
	}
	
	/**
	 * Plugin Settings
	 */
	public function auto_backup_storage_settings(){
		require_once AUTO_BACKUP_PATH . '/admin/partials/cloud_storage.php';
	}
	
	/**
	 * Plugin Schedule
	 */
	public function auto_backup_schedule_settings(){
		require_once AUTO_BACKUP_PATH . '/admin/partials/schedule.php';
	}
	
	/**
	 * Plugin Schedule list
	 */
	public function auto_backup_schedule_list(){
		require_once AUTO_BACKUP_PATH . '/admin/partials/schedule_list.php';
	}
	/**
	 * Plugin Schedule
	 */
	public function auto_backup_import_settings(){
		require_once AUTO_BACKUP_PATH . '/admin/partials/import.php';
	}
	
	/**
	 * Plugin Schedule
	 */
	public function auto_backup_configuration(){
		require_once AUTO_BACKUP_PATH . '/admin/partials/system-requirements.php';
	}
	
	/**
	 * Backup create using ajax
	 */
	public function auto_backup_create_ajax(){
		if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'autobackup_ajax_nonce')) {
			if(!class_exists('ZipArchive')){
				echo wp_json_encode( array('status' => esc_html__('error', 'autobackup'), 'msg' => esc_html__("Class ZipArchive not found, this is required for taking backup.",'autobackup')) );
				die();
			} 
			$pathdir = ABSPATH; // folder name that need to be zip
			$created_date = time();
			
			$zipNewName = 'backup_'.date('Y_m_d').'_'.implode('_', sanitize_post($_POST['bkpopt'])).'_'.$created_date;
			
			$zipcreated = AUTO_BACKUP_DIR . '/backups/'.esc_html($zipNewName).'.zip'; // location where we create a zip
			
			$rootPath = realpath($pathdir);

			$zip = new ZipArchive();
			$zip->open($zipcreated, ZipArchive::CREATE | ZipArchive::OVERWRITE);

			// Create recursive directory iterator
			/** @var SplFileInfo[] $files */
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($rootPath),
				RecursiveIteratorIterator::LEAVES_ONLY
			);
			
			$db_bkp = '';
			$opt_dir = $opt_db = 'no';
			
			foreach($_POST['bkpopt'] as $opt){
				if($opt == 'database'){
					$opt_db = 'yes';
					$db = $this->auto_backup_create_database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
					if($db['status'] == 'success'){
						$zip->addFile($db['path'], 'pbdb_bkp.sql');
						$db_bkp = $db['path'];
					}else{
						echo "fail";
						die();
					}
				}else{
					$opt_dir = 'yes';
					foreach ($files as $name => $file) {
						// Skip directories (they would be added automatically)
						if (!$file->isDir() && strpos($file->getRealPath(), 'autobackups') == false) {
							$filePath = realpath($file->getRealPath());
							$relativePath = substr($filePath, strlen($rootPath) + 1);
							$zip->addFile($filePath, $relativePath);
						}
					}
				}
			}
			
			$zip->close();
			
			if($db_bkp){
			  unlink($db_bkp);
			}
			if(file_exists($zipcreated)){
				
				$location = 'local';
				$fileseize = filesize($zipcreated);
				$downloadURL = $fileid = '';
				
				$storage = get_option('auto_backup_cloud_storage');
				if($_POST['cloud'] == 'dropbox'){
					
					$obj = new Auto_Backup_DB_API();
					
					$location = 'Dropbox';
					/**
					* We will upload large file using chunk, If the filesize is grater than 140 MB
					* then we will use chunk function.
					* Dropbox allows upload sizes of 150 MB, but we will define our chunk size slightly smaller just to be safe.
					**/
					
					if($fileseize > 50 * 1048576){
						$res = $obj->DropboxUploadLargeFile($storage['dropbox'], $zipcreated);
					}else{
						$res = $obj->auto_backup_dropbox_upload_file($storage['dropbox'], $zipcreated);
					}
					
					if($res['status'] == 1){
						unlink($zipcreated);
						$downloadURL = $res['downloadurl'];
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res['error']) ) );
						die();
					}
				}elseif($_POST['cloud'] == 'gdrive'){
					
					$location = 'Google_Drive';
					$obj = new Auto_Backup_Google_drive_api();
					$res = $obj->uploadFileToDrive($storage['gdrive'], $zipcreated);
					$res = json_decode($res);
					if(isset($res->id)){
						unlink($zipcreated);
						$downloadURL = 'https://www.googleapis.com/drive/v3/files/'.$res->id.'?alt=media';
						$fileid = $res->id;
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res) ) );
						die();
					}
				}elseif($_POST['cloud'] == 's3'){
					
					$location = 'S3';
					$obj = new Auto_Backup_s3_API();
					$res = $obj->upload_object($zipcreated, $zipNewName);
					
					if (isset($res['url'])) {
						unlink($zipcreated);
						$downloadURL = $res['url'];
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res) ) );
						die();
					}
				}elseif($_POST['cloud'] == 'ftp'){
					
					$location = 'FTP';
					$obj = new Auto_Backup_FTP_API();
					$res = $obj->upload_object($zipcreated, $zipNewName);
					
					if (isset($res['url'])) {
						unlink($zipcreated);
						$downloadURL = $res['url'];
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res) ) );
						die();
					}
				}elseif($_POST['cloud'] == 'neevcloud'){
					$location = 'NeevCloud';
					$obj = new Auto_Backup_NeevCloud_API();
					
					$res = $obj->upload_object($zipcreated, $zipNewName);
					 
					if (isset($res['url'])) {
						unlink($zipcreated);
						$downloadURL = $res['url'];
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res) ) );
						die();
					}
				}
				
				//Add information about the created backup.
				$current_user = wp_get_current_user();
				$info = '{
					"name": "'.esc_html($zipNewName).'",
					"backup_dir": "'.esc_html($opt_dir).'",
					"backup_db": "'.esc_html($opt_db).'",
					"email": "'.esc_html($current_user->user_email).'",
					"auto_backup": false,
					"size": "'.esc_html($fileseize).'",
					"location": "'.esc_html($location).'",
					"downloadurl": "'.esc_html($downloadURL).'",
					"fileid": "'.esc_html($fileid).'",
					"created": "'.esc_html($created_date).'"
				}';

				file_put_contents(AUTO_BACKUP_DIR . '/backups-info/'.esc_html($zipNewName).'.php', $info);
				
				echo wp_json_encode( array('status' => 'success', 'msg' => esc_html__("Backup has been created successfully.","autobackup")) );
			}else{
				echo wp_json_encode( array('status' => esc_html__('error', 'autobackup'), 'msg' => esc_html__("Something is going wrong.","autobackup")) );
			}
		}else{
			echo wp_json_encode( array('status' => esc_html__('error', 'autobackup'), 'msg' => esc_html__("Invalid nonce.","autobackup")) );
		}			
		die();
	}
	
	/**
	 * Footer Hook
	 */
	public function auto_backup_admin_footer(){
		echo '<div class="pb-alert-wrap"><p></p></div>';
	}
	
	/**
	 * Create backup of mysql database.
	 */
	public function auto_backup_create_database($dbhost, $dbusername, $dbpassword, $dbname, $tables = '*'){
		$db = new mysqli($dbhost, $dbusername, $dbpassword, $dbname);
		if($tables == '*') {
			$tables = array();
			$result = $db->query("SHOW TABLES");
			while($row = $result->fetch_row()) { 
				$tables[] = $row[0];
			}
		} else { 
			$tables = is_array($tables)?$tables:explode(',',$tables);
		}

		$return = '';

		foreach($tables as $table){
			$result = $db->query("SELECT * FROM $table");
			$numColumns = $result->field_count;

			/* $return .= "DROP TABLE $table;"; */
			$result2 = $db->query("SHOW CREATE TABLE $table");
			$row2 = $result2->fetch_row();

			$return .= "\n\n".$row2[1].";\n\n";

			for($i = 0; $i < $numColumns; $i++) { 
				while($row = $result->fetch_row()) { 
					$return .= "INSERT INTO $table VALUES(";
					for($j=0; $j < $numColumns; $j++) { 
						$row[$j] = isset($row[$j]) ? addslashes($row[$j]) : '';
						$row[$j] = $row[$j];
						if (isset($row[$j])) { 
							$return .= '"'.$row[$j].'"' ;
						} else { 
							$return .= '""';
						}
						if ($j < ($numColumns-1)) {
							$return.= ',';
						}
					}
					$return .= ");\n";
				}
			}

			$return .= "\n\n\n";
		}
		$path = AUTO_BACKUP_DIR . '/backups/'.time().'.sql';
		$handle = fopen($path,'w+');
		fwrite($handle,$return);
		fclose($handle);
		return array( 'status' => 'success', 'path' => $path );
	}
	
	/**
	 * Delete backup.
	 */
	public function auto_backup_delete_ajax(){
		if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'autobackup_ajax_nonce')) {
			
			if($_POST['action'] != 'auto_backup_delete_ajax'){
				echo "Access is denied!";
				wp_die();
			}
			
			$count = 0;
			$storage = get_option('auto_backup_cloud_storage');
			
			foreach($_POST['bkp_info'] as $info){
				if($info['location'] == 'Dropbox'){
					
					if(isset($storage['dropbox'])){
						$obj = new Auto_Backup_DB_API();
						$res = $obj->dropboxDeleteObject($storage['dropbox'], $info['bkp_name']);
					}else{
						echo esc_html__("Please connect your Dropbox account.","autobackup");
						die();
					}
					
				}elseif($info['location'] == 'Google_Drive'){
					
					if(isset($storage['gdrive'])){
						$obj = new Auto_Backup_Google_drive_api();
						$res = $obj->delete_file($storage['gdrive'], $info['fileid']);
					}else{
						echo esc_html__("Please connect your Google Drive account.","autobackup");
						
						die();
					}
					
				}elseif($info['location'] == 'S3'){
					
					if(isset($storage['s3'])){
						$obj = new Auto_Backup_s3_API();
						$res = $obj->delete_object( $info['bkp_name'] );
						if (!$res) {
							echo esc_html($res); die();
						}
					}else{
						echo esc_html__("Please connect your S3 account.","autobackup");
					   die();
					}
				}elseif($info['location'] == 'NeevCloud'){
					
					if(isset($storage['neevcloud'])){
						$obj = new Auto_Backup_NeevCloud_API();
						$res = $obj->delete_object( $info['bkp_name'] );
						if (!$res) {
							echo esc_html($res); die();
						}
					}else{
						echo esc_html__("Please connect your NeevCloud account.","autobackup");
						die();
					}
				}elseif($info['location'] == 'FTP'){
					if(isset($storage['ftp'])){
						$obj = new Auto_Backup_FTP_API();
						$res = $obj->delete_object( $info['bkp_name'] );
						if (!$res) {
							echo esc_html($res); die();
						}
					}else{
						echo esc_html__("Please connect your FTP account.","autobackup");
						die();
					}
				}else{
					if(file_exists(AUTO_BACKUP_DIR.'/backups/'.$info['bkp_name'].'.zip')){
						$res = unlink(AUTO_BACKUP_DIR.'/backups/'.$info['bkp_name'].'.zip');
					}
				}

				if($res){
					unlink(AUTO_BACKUP_DIR.'/backups-info/'.$info['bkp_name'].'.php');
				}
				
				$count++;
			}
			
			if($count>0){
				echo esc_html__("success","autobackup");
			}else{
				echo esc_html__("file not found","autobackup");
			}
		}else{
			echo esc_html__("Invalid nonce","autobackup");
		}
		wp_die();
	}
	
	/**
	 * Backup restoration ajax
	 */
	public function auto_backup_restore_ajax(){
		if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'autobackup_ajax_nonce')) {
			if($_POST['action'] != 'auto_backup_restore_ajax'){
				echo esc_html__("Access is denied!","autobackup");
				wp_die();
			}
			
			$filePath = AUTO_BACKUP_DIR.'/backups/'.sanitize_text_field($_POST['bkp_name']);
			$storage = get_option('auto_backup_cloud_storage');
			
			if($_POST['location'] == 'Dropbox'){
				
				if(isset($storage['dropbox'])){
					$obj = new Auto_Backup_DB_API();
					$obj->dropboxDownloadFile($storage['dropbox'], sanitize_text_field($_POST['bkp_name']), esc_html($filePath).'.zip');
				}else{
					echo esc_html__("Please connect your Dropbox account.","autobackup");
					die();
				}
				
			}elseif($_POST['location'] == 'Google_Drive'){
				
				if(isset($storage['gdrive'])){
					$obj = new Auto_Backup_Google_drive_api();

					$gd_file = 'https://www.googleapis.com/drive/v3/files/'.sanitize_text_field($_POST['fileid']).'?alt=media';

					$obj->download_file($gd_file, $storage['gdrive'], sanitize_text_field($_POST['bkp_name']));

					rename(AUTO_BACKUP_DIR.'/temp/'.sanitize_text_field($_POST['bkp_name']).'.zip', esc_html($filePath).'.zip');

				}else{
					echo esc_html__("Please connect your Google Drive account.","autobackup");
					die();
				}
				
			}elseif($_POST['location'] == 'S3'){
				
				if(isset($storage['s3'])){
					$obj = new Auto_Backup_s3_API();
					$obj->get_object( sanitize_text_field($_POST['bkp_name']), esc_html($filePath).'.zip' );
				}else{
					echo esc_html__("Please connect your Google Drive account.","autobackup");
					die();
				}
				
			}elseif($_POST['location'] == 'NeevCloud'){
				
				if(isset($storage['neevcloud'])){
					$obj = new Auto_Backup_NeevCloud_API();
					$file = $obj->get_object( sanitize_text_field($_POST['bkp_name']), esc_html($filePath).'.zip' );
					if($file == 0){
						echo esc_html__("Bucket is not public or file does not exists.","autobackup");
						die();
					}
				}else{
					echo esc_html__("Please connect your Google Drive account.","autobackup");
					die();
				}
				
			}elseif($_POST['location'] == 'FTP'){
				
				if(isset($storage['ftp'])){
					$obj = new Auto_Backup_FTP_API();
					$obj->get_object( sanitize_text_field($_POST['bkp_name']), esc_html($filePath).'.zip' );
				}else{
					echo esc_html__("Please connect your Google Drive account.","autobackup");
					die();
				}
				
			}
					
			if(file_exists( $filePath .'.zip' )){
				$zip = new ZipArchive;
				$res = $zip->open( $filePath .'.zip' );
				if ($res === TRUE) {
				  $zip->extractTo( $filePath . '/' );
				  $zip->close();
				  
				  //Database Restore
				  if(file_exists( $filePath . '/' . 'pbdb_bkp.sql' )){
					  $restore_res = $this->auto_backup_restoreDatabaseTables( esc_html($filePath) . '/' . 'pbdb_bkp.sql' );
					  if($restore_res){
						  unlink($filePath . '/' . 'pbdb_bkp.sql');
					  }else{
						  echo esc_html($restore_res);
					  }
				  }
				  
				  //Files and Folder restore
				  //We are just checking if the wp backup exists or not
				  if(file_exists( $filePath . '/' . 'wp-config.php' )){
					$src = $filePath;
					$dst = ABSPATH;
					  
					try {
						$this->auto_backup_restore_files_folder($src, $dst);
					} catch (Error $e) {
					   echo esc_html__("Error caught: ","autobackup") . esc_html($e->getMessage());
					   die();
					}
				  }
				} else {
					echo esc_html__("File not found.","autobackup");
					
				  die();
				}
				
				if($_POST['location'] == 'Dropbox' || $_POST['location'] == 'Google_Drive' || $_POST['location'] == 'S3'){
					unlink($filePath .'.zip');
				}
				
				rmdir($filePath);
				echo esc_html__("success","autobackup");
				update_option('auto_backup_last_restore', time());
			}
		}else{
			echo esc_html__("Invalid nonce","autobackup");
		}
		wp_die();
	}
	
	/**
	 * Restore database function
	 */
	public function auto_backup_restoreDatabaseTables($filePath){
		// Connect & select the database
		$db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 

		// Temporary variable, used to store current query
		$templine = '';
		
		// Read in entire file
		$lines = file($filePath);
		
		$error = '';
		
		// Loop through each line
		foreach ($lines as $line){
			// Skip it if it's a comment
			if(substr($line, 0, 2) == '--' || $line == ''){
				continue;
			}
			
			// Add this line to the current segment
			$templine .= $line;
			
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';'){
				// Perform the query
				if(!$db->query($templine)){
					$error .= esc_html__('Error performing query "<b>','autobackup') . esc_html($templine) . '</b>": ' . esc_html($db->error) . '<br /><br />';
				}
				
				// Reset temp variable to empty
				$templine = '';
			}
		}
		return !empty($error) ? $error : true;
	}
	
	/**
	 * Restore files and folder ajax
	 */
	public function auto_backup_restore_files_folder($src, $dst){
		
		// open the source directory
		$dir = opendir($src); 
	  
		// Make the destination directory if not exist
		@mkdir($dst); 
	  
		// Loop through the files in source directory
		while( $file = readdir($dir) ) {
	  
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
	  
					// Recursively calling function
					// for sub directory 
					$this->auto_backup_restore_files_folder($src . '/' . $file, $dst . '/' . $file); 
					if(is_dir($src . '/' . $file)){
						rmdir($src . '/' . $file);
					}
				} 
				else { 
					copy($src . '/' . $file, $dst . '/' . $file);
					unlink($src . '/' . $file); // we can delete file after copy.
				} 
			} 
		} 
		
		rmdir($src);
		closedir($dir);
				
	}
	
	public function auto_backup_save_storage_data(){
		if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'autobackup_ajax_nonce')) {
		
			$act = sanitize_text_field($_POST['action']);
			if($act != 'auto_backup_save_storage_data'){
				echo esc_html__("Access is denied!","autobackup");
				wp_die();
			}
			
			$storageType = sanitize_text_field($_POST['auto_backup_storage_type']);
			
			set_transient('auto_backup_storage_type', $storageType, 60);
			if($storageType == 'dropbox'){
				
				set_transient('auto_backup_drp_app_key', sanitize_text_field($_POST['app_key']), 60);
				set_transient('auto_backup_drp_app_secret', sanitize_text_field($_POST['app_secret']), 60);
				
			}elseif($storageType == 'gDrive'){
				
				set_transient('auto_backup_gdrive_client_id', sanitize_text_field($_POST['client_id']), 60);
				set_transient('auto_backup_gdrive_client_secret', sanitize_text_field($_POST['client_secret']), 60);
				
			}elseif($storageType == 's3'){
				
				$obj = new Auto_Backup_s3_API();
				
				$res = $obj->check_credentials( sanitize_text_field($_POST['access_key']), sanitize_text_field($_POST['access_secret']) );
				
				if($res){
					
					$bkt = $obj->create_buckets(sanitize_text_field($_POST['bucket_name']), sanitize_text_field($_POST['access_key']), sanitize_text_field($_POST['access_secret'])); //Create bucket on success
					
					if(isset($bkt['Location'])){
						$data = get_option('auto_backup_cloud_storage');
						$data['s3']['bucket_name'] = sanitize_text_field($_POST['bucket_name']);
						$data['s3']['access_key'] = sanitize_text_field($_POST['access_key']);
						$data['s3']['access_secret'] = sanitize_text_field($_POST['access_secret']);		
						update_option('auto_backup_cloud_storage', $data);
					}else{
						echo esc_html($bkt); die();
					}
					
				}else{
					echo esc_html($res); die();
				}
				
			}elseif($storageType == 'ftp'){
				
				$obj = new Auto_Backup_FTP_API();
				$host = sanitize_text_field($_POST['host']);
				$ftp_username = sanitize_text_field($_POST['ftp_username']);
				$ftp_password = sanitize_text_field($_POST['ftp_password']);
				
				$conn = $obj->check_connection( $host, $ftp_username, $ftp_password );
				if($conn){
					
					$data = get_option('auto_backup_cloud_storage');
					$data['ftp']['host'] = $host;
					$data['ftp']['username'] = $ftp_username;
					$data['ftp']['password'] = $ftp_password;		
					update_option('auto_backup_cloud_storage', $data);
					
				}else{
					echo esc_html($conn); die();
				}
				
			}elseif($storageType == 'neevcloud'){
				
				$obj = new Auto_Backup_NeevCloud_API();
				
				$access_key = sanitize_text_field($_POST['access_key']);
				$access_secret = sanitize_text_field($_POST['access_secret']);
				$bucket_name = sanitize_text_field($_POST['bucket_name']);
				
				$res = $obj->check_credentials( $access_key, $access_secret );
				if($res){
					$data = get_option('auto_backup_cloud_storage');
					$data['neevcloud']['bucket_name'] = $bucket_name;
					$data['neevcloud']['access_key'] = $access_key;
					$data['neevcloud']['access_secret'] = $access_secret;		
					update_option('auto_backup_cloud_storage', $data);
				}else{
					echo esc_html($res); die();
				}
				
			}else{
				echo esc_html__("wrong selection, please check error.","autobackup");
				die();
			}
			
			echo esc_html__("success","autobackup");
		}else{
			echo esc_html__("Invalid nonce","autobackup");
		}
		wp_die();
	}
	
	public function auto_backup_download_file() {
		if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'autobackup_ajax_nonce')) {
			// Sanitize the POST values
			$gd_file = isset($_POST['gd_file']) ? sanitize_text_field($_POST['gd_file']) : '';
			$bkpname = isset($_POST['bkpname']) ? sanitize_text_field($_POST['bkpname']) : '';

			$cred = get_option('auto_backup_cloud_storage');

			$this->auto_backup_delete_files_from_folder(); // Remove old zip files from temp folder.

			$obj = new Auto_Backup_Google_drive_api();
			$res = $obj->download_file($gd_file, $cred['gdrive'], $bkpname);
			
			if ($res > 0) {
				$url = content_url('autobackups/temp/' . $bkpname . '.zip');
				echo wp_json_encode(array('status' => 1, 'url' => esc_url($url)));
			} else {
				echo wp_json_encode(array('status' => 0, 'msg' => 'Something went wrong'));
			}
		}else{
			echo wp_json_encode(array('status' => 0, 'msg' => 'Invalid nonce'));
		}
		wp_die();
	}

	
	public function auto_backup_delete_files_from_folder() {
		
		$folder_path = AUTO_BACKUP_DIR.'/temp';
		
		$files = glob($folder_path.'/*'); 
		if(!empty($files)){
			foreach($files as $file) {
				if(is_file($file)) {
					unlink($file);
				}
			}
		}
		
	}
	
	public function auto_backup_save_scheduled_data () {
		if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'autobackup_ajax_nonce')) {
			$arr = array(
				'file_schedule' => $_POST['file_schedule'],
				'db_schedule' => $_POST['db_schedule'],
				'location' => $_POST['location']
			);
			
			update_option('auto_backup_sheduled_settings', sanitize_post($arr) );
			
			//Delete old schedules before adding new ones
			wp_clear_scheduled_hook('auto_backup_sheduled_databaase_hook');
			wp_clear_scheduled_hook('auto_backup_sheduled_files_hook');
			
			$schedule_sett = get_option('auto_backup_sheduled_settings');
			if(!empty($schedule_sett)){
				
				//** For scheduled backup **//
				if( $schedule_sett['db_schedule'] != 'manual' ){
					if ( ! wp_next_scheduled( 'auto_backup_sheduled_databaase_hook' ) ) {
						wp_schedule_event( time(), $schedule_sett['db_schedule'], 'auto_backup_sheduled_databaase_hook' );
					}
				}
				
				if( $schedule_sett['file_schedule'] != 'manual' ){
					if ( ! wp_next_scheduled( 'auto_backup_sheduled_files_hook' ) ) {
						wp_schedule_event( time(), $schedule_sett['file_schedule'], 'auto_backup_sheduled_files_hook' );
					}
				}
				
			}
			echo 1;
		}else{
			echo "invalid-nonce";
		}
		
		die();
	}
		
	public function auto_backup_schedule_filter( $schedules ) {
		$schedules['two_hourly'] = array(
			'interval' => 60 * 60 * 2,
			'display' => esc_html__('Every Two Hour')
		);
		$schedules['four_hourly'] = array(
			'interval' => 60 * 60 * 4,
			'display' => esc_html__('Every Four Hour')
		);
		$schedules['eight_hourly'] = array(
			'interval' => 60 * 60 * 8,
			'display' => esc_html__('Every Eigth Hour')
		);
		$schedules['twelve_hourly'] = array(
			'interval' => 60 * 60 * 12,
			'display' => esc_html__('Every Twelve Hour')
		);
		$schedules['weekly'] = array(
			'interval' => 60 * 60 * 24 * 7,
			'display' => esc_html__('Once Weekly')
		);
		$schedules['fortnightly'] = array(
			'interval' => 60 * 60 * 24 * 14,
			'display' => esc_html__('Once 14 Days')
		);
		$schedules['monthly'] = array(
			'interval' => 60 * 60 * 24 * date('t'),
			'display' => esc_html__('Once Monthly')
		);
		
		
		return $schedules;
	}
		
	public function auto_backup_scheduled_db_backup() {
		
		$settings = get_option('auto_backup_sheduled_settings');
		
		if(!empty($settings)){
			
			if(!class_exists('ZipArchive')){
				echo wp_json_encode( array('status' => esc_html__('error', 'autobackup'), 'msg' => esc_html__("Class ZipArchive not found, this is required for taking backup.","autobackup") ));
				die();
			}
			
			$pathdir = ABSPATH; // folder name that need to be zip
			$created_date = time();
			$zipNewName = 'backup_'.date('Y_m_d').'_database'.'_'.$created_date;
			$zipcreated = AUTO_BACKUP_DIR . '/backups/'.$zipNewName.'.zip'; // location where we create a zip
			
			$rootPath = realpath($pathdir);

			$zip = new ZipArchive();
			$zip->open($zipcreated, ZipArchive::CREATE | ZipArchive::OVERWRITE);

			$db_bkp = '';
			$opt_dir = 'no';
			
			$opt_db = 'yes';
			$db = $this->auto_backup_create_database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			if($db['status'] == 'success'){
				$zip->addFile($db['path'], 'pbdb_bkp.sql');
				$db_bkp = $db['path'];
			}else{
				echo "fail";
				die();
			}
			
			$zip->close();
			
			unlink($db_bkp);
			
			if(file_exists($zipcreated)){
				
				$location = 'local';
				$cloud = $settings['location'];
				$fileseize = filesize($zipcreated);
				$downloadURL = $fileid = '';
				
				$storage = get_option('auto_backup_cloud_storage');
				if($cloud == 'dropbox'){
					
					$obj = new Auto_Backup_DB_API();
					
					$location = 'Dropbox';
					/**
					* We will upload large file using chunk, If the filesize is grater than 140 MB
					* then we will use chunk function.
					* Dropbox allows upload sizes of 150 MB, but we will define our chunk size slightly smaller just to be safe.
					**/
					if($fileseize > 50 * 1048576){
						$res = $obj->DropboxUploadLargeFile($storage['dropbox'], $zipcreated);
					}else{
						$res = $obj->auto_backup_dropbox_upload_file($storage['dropbox'], $zipcreated);
					}
					
					if($res['status'] == 1){
						unlink($zipcreated);
						$downloadURL = $res['downloadurl'];
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res['error']) ) );
						die();
					}
				}elseif($cloud == 'gdrive'){
					
					$location = 'Google_Drive';
					$obj = new Auto_Backup_Google_drive_api();
					$res = $obj->uploadFileToDrive($storage['gdrive'], $zipcreated);
					$res = json_decode($res);
					if(isset($res->id)){
						unlink($zipcreated);
						$downloadURL = esc_url('https://www.googleapis.com/drive/v3/files/'.$res->id.'?alt=media');
						$fileid = $res->id;
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res) ) );
						die();
					}
				}elseif($cloud == 's3'){
					
					$location = 'S3';
					$obj = new Auto_Backup_s3_API();
					$res = $obj->upload_object($zipcreated, $zipNewName);
					
					if (isset($res['url'])) {
						unlink($zipcreated);
						$downloadURL = esc_url($res['url']);
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res) ) );
						die();
					}
				}elseif($cloud == 'ftp'){
					
					$location = 'FTP';
					$obj = new Auto_Backup_FTP_API();
					$res = $obj->upload_object($zipcreated, $zipNewName);
					
					if (isset($res['url'])) {
						unlink($zipcreated);
						$downloadURL = esc_url($res['url']);
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res) ) );
						die();
					}
				}
				
				//Add information about the created backup.
				$current_user = wp_get_current_user();
				$info = '{
					"name": "'.esc_html($zipNewName).'",
					"backup_dir": "'.esc_html($opt_dir).'",
					"backup_db": "'.esc_html($opt_db).'",
					"email": "'.esc_html($current_user->user_email).'",
					"auto_backup": false,
					"size": "'.esc_html($fileseize).'",
					"location": "'.esc_html($location).'",
					"downloadurl": "'.esc_html($downloadURL).'",
					"fileid": "'.esc_html($fileid).'",
					"created": "'.esc_html($created_date).'"
				}';

				file_put_contents(AUTO_BACKUP_DIR . '/backups-info/'.esc_html($zipNewName).'.php', $info);
				
				echo wp_json_encode( array('status' => 'success', 'msg' => esc_html__("Backup has been created successfully.","autobackup")) );
			}else{
				echo wp_json_encode( array('status' => esc_html__('error', 'autobackup'), 'msg' => esc_html__("Something is going wrong.","autobackup") ));
			}
		} 
		
	}
	
	public function auto_backup_scheduled_files_backup() {
		
		$settings = get_option('auto_backup_sheduled_settings');
		if(!empty($settings)){
		
			if(!class_exists('ZipArchive')){
				echo wp_json_encode( array('status' => esc_html__('error', 'autobackup'), 'msg' => esc_html__("Class ZipArchive not found, this is required for taking backup.","autobackup")) );
				die();
			}
			
			$pathdir = ABSPATH; // folder name that need to be zip
			$created_date = time();
			$zipNewName = 'backup_'.date('Y_m_d').'_files'.'_'.esc_html($created_date);
			$zipcreated = AUTO_BACKUP_DIR . '/backups/'.esc_html($zipNewName).'.zip'; // location where we create a zip
			
			$rootPath = realpath($pathdir);

			$zip = new ZipArchive();
			$zip->open($zipcreated, ZipArchive::CREATE | ZipArchive::OVERWRITE);

			// Create recursive directory iterator
			/** @var SplFileInfo[] $files */
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($rootPath),
				RecursiveIteratorIterator::LEAVES_ONLY
			);
			
			$opt_db = 'no';
			
			$opt_dir = 'yes';
			foreach ($files as $name => $file) {
				// Skip directories (they would be added automatically)
				if (!$file->isDir() && strpos($file->getRealPath(), 'autobackups') == false) {
					$filePath = realpath($file->getRealPath());
					$relativePath = substr($filePath, strlen($rootPath) + 1);
					$zip->addFile($filePath, $relativePath);
				}
			}
			
			$zip->close();
			
			if(file_exists($zipcreated)){
				
				$location = 'local';
				$cloud = $settings['location'];
				$fileseize = filesize($zipcreated);
				$downloadURL = $fileid = '';
				
				$storage = get_option('auto_backup_cloud_storage');
				if($cloud == 'dropbox'){
					
					$obj = new Auto_Backup_DB_API();
					
					$location = 'Dropbox';
					/**
					* We will upload large file using chunk, If the filesize is grater than 140 MB
					* then we will use chunk function.
					* Dropbox allows upload sizes of 150 MB, but we will define our chunk size slightly smaller just to be safe.
					**/
					if($fileseize > 50 * 1048576){
						$res = $obj->DropboxUploadLargeFile($storage['dropbox'], $zipcreated);
					}else{
						$res = $obj->auto_backup_dropbox_upload_file($storage['dropbox'], $zipcreated);
					}
					
					if($res['status'] == 1){
						unlink($zipcreated);
						$downloadURL = $res['downloadurl'];
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res['error']) ) );
						die();
					}
				}elseif($cloud == 'gdrive'){
					
					$location = 'Google_Drive';
					$obj = new Auto_Backup_Google_drive_api();
					$res = $obj->uploadFileToDrive($storage['gdrive'], $zipcreated);
					$res = json_decode($res);
					if(isset($res->id)){
						unlink($zipcreated);
						$downloadURL = 'https://www.googleapis.com/drive/v3/files/'.$res->id.'?alt=media';
						$fileid = $res->id;
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res) ) );
						die();
					}
				}elseif($cloud == 's3'){
					
					$location = 'S3';
					$obj = new Auto_Backup_s3_API();
					$res = $obj->upload_object($zipcreated, $zipNewName);
					
					if (isset($res['url'])) {
						unlink($zipcreated);
						$downloadURL = $res['url'];
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res) ) );
						die();
					}
				}elseif($cloud == 'ftp'){
					
					$location = 'FTP';
					$obj = new Auto_Backup_FTP_API();
					$res = $obj->upload_object($zipcreated, $zipNewName);
					
					if (isset($res['url'])) {
						unlink($zipcreated);
						$downloadURL = $res['url'];
					}else{
						echo wp_json_encode( array( 'status' => esc_html__('error', 'autobackup'), 'msg' => esc_html($res) ) );
						die();
					}
				}
				
				//Add information about the created backup.
				$current_user = wp_get_current_user();
				$info = '{
					"name": "'.esc_html($zipNewName).'",
					"backup_dir": "'.esc_html($opt_dir).'",
					"backup_db": "'.esc_html($opt_db).'",
					"email": "'.esc_html($current_user->user_email).'",
					"auto_backup": false,
					"size": "'.esc_html($fileseize).'",
					"location": "'.esc_html($location).'",
					"downloadurl": "'.esc_html($downloadURL).'",
					"fileid": "'.esc_html($fileid).'",
					"created": "'.esc_html($created_date).'"
				}';

				file_put_contents(AUTO_BACKUP_DIR . '/backups-info/'.esc_html($zipNewName).'.php', $info);
				
				echo wp_json_encode( array('status' => 'success', 'msg' => esc_html__("Backup has been created successfully.","autobackup")) );
			}else{
				echo wp_json_encode( array('status' => esc_html__('error', 'autobackup'), 'msg' => esc_html__("Something is going wrong.","autobackup")) );
			}
		}
	}
	
	public function auto_backup_import_data() {
		if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'autobackup_ajax_nonce')) {
			check_ajax_referer('your_nonce_action', 'security');
		
			if (isset($_FILES['import_file'])) {
				$file_name = sanitize_file_name($_FILES['import_file']['name']);
				$array = explode(".", $file_name);
				$name = $array[0];
				$ext = $array[1];
		
				if ($ext == 'zip') {
					$extractTo = AUTO_BACKUP_DIR . '/' . esc_html($name);
		
					$uploaded_file = $_FILES['import_file'];
					$upload_overrides = array('test_form' => false);
					$movefile = wp_handle_upload($uploaded_file, $upload_overrides);
		
					if ($movefile && !isset($movefile['error'])) {
						$zip = new ZipArchive;
		
						if ($zip->open($movefile['file'])) {
							$zip->extractTo($extractTo);
							$zip->close();
		
							// Database Import
							$database_backup_path = $extractTo . '/pbdb_bkp.sql';
		
							if (file_exists($database_backup_path)) {
								$restore_res = $this->auto_backup_restoreDatabaseTables($database_backup_path);
		
								if ($restore_res) {
									unlink($database_backup_path);
								} else {
									wp_send_json_error(array('message' => esc_html__('Database restore failed.', 'autobackup')));
								}
							}
		
							// Files and Folder Import
							// Check if the wp backup exists or not
							if (file_exists($extractTo . '/wp-config.php')) {
								$src = $extractTo;
								$dst = ABSPATH;
		
								try {
									$this->auto_backup_restore_files_folder($src, $dst);
									unlink($movefile['file']);
									wp_send_json_success(array('message' => esc_html__('Import successful.', 'autobackup')));
								} catch (Error $e) {
									wp_send_json_error(array('message' => esc_html__('Error during file restore.', 'autobackup')));
								}
							}
						}
					} else {
						wp_send_json_error(array('message' => esc_html__('File upload failed.', 'autobackup')));
					}
				} else {
					wp_send_json_error(array('message' => esc_html__('File type not supported.', 'autobackup')));
				}
			} else {
				wp_send_json_error(array('message' => esc_html__('No file uploaded.', 'autobackup')));
			}
		}else{
			wp_send_json_error(array('message' => esc_html__('Invalid nonce.', 'autobackup')));
		}
		die();
	}
	
	public function auto_backup_delete_schedule() {
		if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'autobackup_ajax_nonce')) {
			if($_POST['action'] != 'auto_backup_delete_schedule'){
				echo wp_send_json_error(array('message' => esc_html__('Access is denied!', 'autobackup')));
				wp_die();
			}
			if(!empty($_POST['hook'])){
				$hook = $_POST['hook'];
				$data = wp_unschedule_event(wp_next_scheduled($hook), $hook);
				//$data = '1';
				if(!empty($data)){
					echo wp_send_json_success(array('message' => esc_html__('Schedule removed successfully.', 'autobackup')));
				}
			}else{
				echo wp_send_json_error(array('message' => esc_html__('Invalid Schedule.', 'autobackup')));
			}
		}else{
			echo wp_send_json_error(array('message' => esc_html__('Invalid nonce.', 'autobackup')));
		}
		
		die();
	}
	
	public function auto_backup_sortingbydate(){
		if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'autobackup_ajax_nonce')) {
			$order = '';
			$msg = '';
			if(!empty($_POST['order'])){
				$order = $_POST['order'];
				$imgDir = AUTO_BACKUP_URL.'/admin/images';
				$backups_info = WP_CONTENT_DIR . '/autobackups/backups-info';
				$info = glob($backups_info.'/*.*');
				// sort files by last modified date
				usort($info, function($x, $y) {
					return filemtime($x) < filemtime($y) ? 0 : 1;
				});
				//Sort by date 
				if(!empty($info)){
					
					$info = array_reverse($info);
					$size = new Auto_Backup();
					$count = 1;
					$gdrive_file = '';
					if($order == 'asc'){
						$info = array_reverse($info);
					}
					foreach($info as $file) {
						
						$data = file_get_contents($file);
						$data = json_decode($data);
						
						$bkpType = $data->backup_dir == 'yes' ? esc_html__('Files','autobackup') : '';
						$bkpType .= !empty($bkpType) && $data->backup_db == 'yes' ? ',' : '';
						$bkpType .= $data->backup_db == 'yes' ? esc_html__('Database','autobackup') : '';
						
						//Google dirve fileid
						$fileid = isset($data->fileid) ? "fileid=".$data->fileid."" : '';
						$msg .= '<tr bkpname="'. esc_attr($data->name).'" bklocation="'. esc_attr($data->location).'" '. esc_attr($fileid).'>
							<td>
								<div class="pb-checkbox">
									<input type="checkbox" id="row-'. esc_attr($count).'" name="bkprow">
									<label for="row-'. esc_attr($count).'"></label>
								</div>
							</td>
							<td>
								<span class="pb-tb-timing">
									'. esc_html(date('jS M Y H:i:s A', $data->created)).'
							</td>
							<td>
								<span class="pb-tb-location">';
								if($data->location == 'Dropbox'){
									$msg .= '<img src="'.esc_url($imgDir.'/drop-box.png').'" alt="'.esc_attr__('Dropbox','autobackup').'">'.esc_html__('Dropbox','autobackup');
								}elseif($data->location == 'Google_Drive'){
									$msg .= '<img src="'.esc_url($imgDir.'/drive.png').'" alt="'.esc_attr__('Google Drive','autobackup').'">'.esc_html__('Google Drive','autobackup');
								}elseif($data->location == 'S3'){
									$msg .= '<img src="'.esc_url($imgDir.'/amazons3.png').'" alt="'.esc_attr__('S3','autobackup').'">'.esc_html__('S3','autobackup');	
								}elseif($data->location == 'FTP'){
									$msg .= '<img src="'.esc_url($imgDir.'/ftp.png').'" alt="'.esc_attr__('FTP','autobackup').'">'.esc_html__('FTP','autobackup');	
								}elseif($data->location == 'NeevCloud'){
									$msg .= '<img src="'.esc_url($imgDir.'/neevcloud-icon.png').'" alt="'.esc_attr__('NeevCloud','autobackup').'">'.esc_html__('NeevCloud','autobackup');	
								}else{
									$msg .= '<img src="'.esc_url($imgDir.'/folder.png').'" alt="'.esc_attr__('Local','autobackup').'">'.esc_html__('Local','autobackup');	
								}
								
								$msg .= '</span>
							</td> 
							<td>
								<span class="pb-tb-file-size">
									'. esc_html($size->auto_backup_beautify_bytes($data->size)).'
								</span>
							</td>
							<td>
								<span class="pb-tb-will-restore">
									'. esc_html($bkpType).'
								</span>
							</td>
							<td>
								<div class="pb-tb-actions">
									<ul>
										<li class="pb-tooltip-wrap pb-restore">
											<div class="pb-tooltip">'. esc_html__('Restore','autobackup').'</div>
											<a href="javascript:;" class="pb-restore-backup">
												<img src="'. esc_url($imgDir.'/restore.svg').'" alt="'. esc_attr__('Restore','autobackup').'">
											</a>
										</li> 
										<li class="pb-tooltip-wrap pb-delete">
											<div class="pb-tooltip">'. esc_html__('Delete','autobackup').'</div>
											<a href="javascript:;" class="pb-delete-backup">
											<img src="'. esc_url($imgDir.'/delete.svg').'" alt="'. esc_attr__('delete','autobackup').'">
											</a>
										</li>
										<li class="pb-tooltip-wrap pb-download pb-location-'. esc_attr($data->location).'">
											<div class="pb-tooltip">'. esc_html__('Download','autobackup').'</div>';
											$downloadURL = $data->downloadurl;
											if($data->location == 'Dropbox'){
												$msg .= '<a href="'.esc_url($downloadURL).'" target="_blank">
													  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													 </a>';
											}elseif($data->location == 'Google_Drive'){
												$gdrive_file = $data->downloadurl;
												$msg .= '<a href="javascript:;" gdrivefile="'.esc_url($gdrive_file).'">
													<img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													</a>';
											}elseif($data->location == 'S3'){
												$s3 = $data->downloadurl;
												$msg .= '<a href="'.esc_url($downloadURL).'" target="_blank">
													  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													 </a>';
											}elseif($data->location == 'NeevCloud'){
												$msg .= '<a href="'.esc_url($downloadURL).'" target="_blank">
													  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													 </a>';
											}elseif($data->location == 'FTP'){
												$s3 = $data->downloadurl;
												$msg .= '<a href="'.esc_url($downloadURL).'" target="_blank">
													  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													 </a>';
											}else{
												$downloadURL = content_url('/autobackups/backups/'.$data->name.'.zip');
												$msg .= '<a href="'.esc_url($downloadURL).'">
													  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													 </a>';
											}
										$msg .='</li>
									</ul>
								</div>
							</td>
						</tr>';						
					}
					echo wp_send_json_success(array('message' => $msg));	
				}else{
					$msg = '<tr class="pb-no-data"><td colspan="6">'.esc_html__('No backup has been created', 'autobackup').'.</td></tr>';
					echo wp_send_json_success(array('message' => esc_html__($msg, 'autobackup')));
				}
			}else{
				echo wp_send_json_error(array('message' => esc_html__('Invalid Order Type.', 'autobackup')));
			}
		}else{
			echo wp_send_json_error(array('message' => esc_html__('Invalid nonce.', 'autobackup')));
		}
		die();
	}
	
	public function auto_backup_sortingbysize(){
		if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'autobackup_ajax_nonce')) {
			$order = '';
			$msg = '';
			if(!empty($_POST['order'])){
				$order = $_POST['order'];
				$imgDir = AUTO_BACKUP_URL.'/admin/images';
				$backups_info = WP_CONTENT_DIR . '/autobackups/backups-info';
				$fileinfo = glob($backups_info.'/*.*');
				// usort($fileinfo, function($x, $y) {
					// return filesize($x) < filesize($y) ? 0 : 1;
				// });
				//Sort by date 
				if(!empty($fileinfo)){
					
					//$fileinfo = array_reverse($fileinfo);
					$size = new Auto_Backup();
					$count = 1;
					$gdrive_file = '';
					if($order == 'asc'){
						$fileinfo = array_reverse($fileinfo);
					}
					
					foreach($fileinfo as $file) {
						
						$data = file_get_contents($file);
						$data = json_decode($data);
						
						$bkpType = $data->backup_dir == 'yes' ? esc_html__('Files','autobackup') : '';
						$bkpType .= !empty($bkpType) && $data->backup_db == 'yes' ? ',' : '';
						$bkpType .= $data->backup_db == 'yes' ? esc_html__('Database','autobackup') : '';
						
						//Google dirve fileid
						$fileid = isset($data->fileid) ? "fileid=".$data->fileid."" : '';
						$msg .= '<tr bkpname="'. esc_attr($data->name).'" bklocation="'. esc_attr($data->location).'" '. esc_attr($fileid).'>
							<td>
								<div class="pb-checkbox">
									<input type="checkbox" id="row-'. esc_attr($count).'" name="bkprow">
									<label for="row-'. esc_attr($count).'"></label>
								</div>
							</td>
							<td>
								<span class="pb-tb-timing">
									'. esc_html(date('jS M Y H:i:s A', $data->created)).'
							</td>
							<td>
								<span class="pb-tb-location">';
								if($data->location == 'Dropbox'){
									$msg .= '<img src="'.esc_url($imgDir.'/drop-box.png').'" alt="'.esc_attr__('Dropbox','autobackup').'">'.esc_html__('Dropbox','autobackup');
								}elseif($data->location == 'Google_Drive'){
									$msg .= '<img src="'.esc_url($imgDir.'/drive.png').'" alt="'.esc_attr__('Google Drive','autobackup').'">'.esc_html__('Google Drive','autobackup');
								}elseif($data->location == 'S3'){
									$msg .= '<img src="'.esc_url($imgDir.'/amazons3.png').'" alt="'.esc_attr__('S3','autobackup').'">'.esc_html__('S3','autobackup');	
								}elseif($data->location == 'FTP'){
									$msg .= '<img src="'.esc_url($imgDir.'/ftp.png').'" alt="'.esc_attr__('FTP','autobackup').'">'.esc_html__('FTP','autobackup');	
								}elseif($data->location == 'NeevCloud'){
									$msg .= '<img src="'.esc_url($imgDir.'/neevcloud-icon.png').'" alt="'.esc_attr__('NeevCloud','autobackup').'">'.esc_html__('NeevCloud','autobackup');	
								}else{
									$msg .= '<img src="'.esc_url($imgDir.'/folder.png').'" alt="'.esc_attr__('Local','autobackup').'">'.esc_html__('Local','autobackup');	
								}
								
								$msg .= '</span>
							</td> 
							<td>
								<span class="pb-tb-file-size">
									'. esc_html($size->auto_backup_beautify_bytes($data->size)).'
								</span>
							</td>
							<td>
								<span class="pb-tb-will-restore">
									'. esc_html($bkpType).'
								</span>
							</td>
							<td>
								<div class="pb-tb-actions">
									<ul>
										<li class="pb-tooltip-wrap pb-restore">
											<div class="pb-tooltip">'. esc_html__('Restore','autobackup').'</div>
											<a href="javascript:;" class="pb-restore-backup">
												<img src="'. esc_url($imgDir.'/restore.svg').'" alt="'. esc_attr__('Restore','autobackup').'">
											</a>
										</li> 
										<li class="pb-tooltip-wrap pb-delete">
											<div class="pb-tooltip">'. esc_html__('Delete','autobackup').'</div>
											<a href="javascript:;" class="pb-delete-backup">
											<img src="'. esc_url($imgDir.'/delete.svg').'" alt="'. esc_attr__('delete','autobackup').'">
											</a>
										</li>
										<li class="pb-tooltip-wrap pb-download pb-location-'. esc_attr($data->location).'">
											<div class="pb-tooltip">'. esc_html__('Download','autobackup').'</div>';
											$downloadURL = $data->downloadurl;
											if($data->location == 'Dropbox'){
												$msg .= '<a href="'.esc_url($downloadURL).'" target="_blank">
													  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													 </a>';
											}elseif($data->location == 'Google_Drive'){
												$gdrive_file = $data->downloadurl;
												$msg .= '<a href="javascript:;" gdrivefile="'.esc_url($gdrive_file).'">
													<img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													</a>';
											}elseif($data->location == 'S3'){
												$s3 = $data->downloadurl;
												$msg .= '<a href="'.esc_url($downloadURL).'" target="_blank">
													  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													 </a>';
											}elseif($data->location == 'NeevCloud'){
												$msg .= '<a href="'.esc_url($downloadURL).'" target="_blank">
													  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													 </a>';
											}elseif($data->location == 'FTP'){
												$s3 = $data->downloadurl;
												$msg .= '<a href="'.esc_url($downloadURL).'" target="_blank">
													  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													 </a>';
											}else{
												$downloadURL = content_url('/autobackups/backups/'.$data->name.'.zip');
												$msg .= '<a href="'.esc_url($downloadURL).'">
													  <img src="'.esc_url($imgDir.'/download.svg').'" alt="'.esc_attr__('download','autobackup').'">
													 </a>';
											}
										$msg .='</li>
									</ul>
								</div>
							</td>
						</tr>';						
					}
					echo wp_send_json_success(array('message' => $msg));	
				}else{
					$msg = '<tr class="pb-no-data"><td colspan="6">'.esc_html__('No backup has been created', 'autobackup').'.</td></tr>';
					echo wp_send_json_success(array('message' => esc_html__($msg, 'autobackup')));
				}
			}else{
				echo wp_send_json_error(array('message' => esc_html__('Invalid Order Type.', 'autobackup')));
			}
		}else{
			echo wp_send_json_error(array('message' => esc_html__('Invalid nonce.', 'autobackup')));
		}
		die();
	}
}