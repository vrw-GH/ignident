<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }
/** 
 * 
 * This Google Drive API handler class is a custom PHP library to handle the Google Drive API calls. 
 * 
 * @class        GoogleDriveApi 
 */ 
class Auto_Backup_Google_drive_api {
    const OAUTH2_TOKEN_URI = 'https://oauth2.googleapis.com/token'; 
    const DRIVE_FILE_UPLOAD_URI = 'https://www.googleapis.com/upload/drive/v3/files'; 
    const DRIVE_FILE_META_URI = 'https://www.googleapis.com/drive/v3/files/'; 
     
    function __construct($params = array()) { 
        if (count($params) > 0){ 
            $this->initialize($params);         
        } 
    } 
     
    function initialize($params = array()) { 
        if (count($params) > 0){ 
            foreach ($params as $key => $val){ 
                if (isset($this->$key)){ 
                    $this->$key = $val; 
                } 
            }         
        } 
    } 
    /**
	 *Get Access Token
	 */
	public function GetAccessToken( $code ) {
		$client_id      = get_transient( 'auto_backup_gdrive_client_id' );
		$client_secret  = get_transient( 'auto_backup_gdrive_client_secret' );
		$redirect_uri   = admin_url( 'admin.php?page=autobackup-cloud-storage' );
	
		$response = wp_remote_post(
			self::OAUTH2_TOKEN_URI,
			array(
				'body' => array(
					'grant_type'    => 'authorization_code',
					'client_secret' => $client_secret,
					'client_id'     => $client_id,
					'redirect_uri'  => $redirect_uri,
					'code'          => $code,
				),
			)
		);
	
		if ( is_wp_error( $response ) ) {
			$error_msg = esc_html__( 'Failed to receive access token', 'autobackup' );
			echo esc_html($error_msg . ': ' . $response->get_error_message());
			die();
		}
	
		$body      = wp_remote_retrieve_body( $response );
		$http_code = wp_remote_retrieve_response_code( $response );
	
		if ( $http_code !== 200 ) {
			$error_msg = esc_html__( 'Failed to receive access token', 'autobackup' );
			echo esc_html($error_msg) . ': ' . esc_html($body);
			die();
		} else {
			$data = get_option( 'auto_backup_cloud_storage', array() );
	
			$tokendata      = json_decode( $body );
			$token          = $tokendata->access_token;
			$refresh_token  = $tokendata->refresh_token;
	
			if ( ! $this->check_folder_exists( $token, 'autobackup' ) ) {
				$folder = $this->create_folder( $token, 'autobackup' );
				if ( $folder['status'] == 1 ) {
					$data['gdrive']['folderid'] = $folder['id'];
				}
			}
	
			$data['gdrive']['client_id']      = $client_id;
			$data['gdrive']['client_secret']  = $client_secret;
			$data['gdrive']['token']          = $token;
			$data['gdrive']['refresh_token']  = $refresh_token;
	
			update_option( 'auto_backup_cloud_storage', $data );
		}
	}
	/**
	 * Upload Drive Function
	 */
	public function uploadFileToDrive($cred, $file) {
		$GAPIS = 'https://www.googleapis.com/';
		
		$token = $cred['token'];
		$client_id = $cred['client_id'];
		$client_secret = $cred['client_secret'];
		$folder_id = isset($cred['folderid']) ? $cred['folderid'] : '';
		
		$pathinfo = pathinfo($file);
		$filename = $pathinfo['filename'];
		
		// Upload file
		$response = wp_remote_post(
			$GAPIS . 'upload/drive/v3/files?uploadType=media',
			array(
				'body' => file_get_contents($file),
				'headers' => array(
					'Content-Type' => 'application/zip',
					'Content-Length' => filesize($file),
					'Authorization' => 'Bearer ' . $token,
				),
			)
		);
		
		if (is_wp_error($response)) {
			return 'ERROR: ' . $response->get_error_message();
		}
		
		$response_arr = json_decode(wp_remote_retrieve_body($response), true);
		
		if (!isset($response_arr['error'])) {
			if (isset($response_arr['id'])) {
				$file_id = $response_arr['id'];
				
				// Update file name and move to folder
				$response = wp_remote_request(
					$GAPIS . 'drive/v3/files/' . $file_id . '?addParents=' . $folder_id,
					array(
						'method' => 'PATCH',
						'body' => wp_json_encode(array('name' => $filename)),
						'headers' => array(
							'Authorization' => 'Bearer ' . $token,
							'Accept' => 'application/json',
							'Content-Type' => 'application/json',
						),
					)
				);
			}
			
			return wp_remote_retrieve_body($response);
		} else {
			if ($response_arr['error']['code'] == 401) { // 401 = token expired
				$res = $this->refresh_token($cred);
				if ($res == 'success') {
					return $this->uploadFileToDrive($cred, $file);
				} else {
					return $res;
				}
			} else {
				return wp_remote_retrieve_body($response);
			}
		}
	}
	
	/**
	 * Check Folder Exists or Not
	 */
	public function check_folder_exists($token, $folder_name) {
		$response = wp_remote_get(
			self::DRIVE_FILE_META_URI . '?q=mimeType%20%3D%20%27application%2Fvnd.google-apps.folder%27',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Accept' => 'application/json',
				),
			)
		);
	
		if (is_wp_error($response)) {
			// Handle error
			return 0;
		}
	
		$data = json_decode(wp_remote_retrieve_body($response));
	
		foreach ($data->files as $file) {
			if ($file->name == $folder_name) {
				return 1;
			}
		}
	
		return 0;
	}
	
	/**
	 * Create Folder
	 */
	public function create_folder($token, $folder_name) {
		$response = wp_remote_post(
			'https://www.googleapis.com/drive/v2/files',
			array(
				'body' => wp_json_encode(array(
					'title' => $folder_name,
					'parents' => '',
					'mimeType' => 'application/vnd.google-apps.folder',
				)),
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $token,
				),
			)
		);
	
		if (is_wp_error($response)) {
			// Handle error
			return $response->get_error_message();
		}
	
		$data = json_decode(wp_remote_retrieve_body($response));
	
		if (isset($data->id)) {
			return array('status' => 1, 'id' => $data->id);
		} else {
			return wp_remote_retrieve_body($response);
		}
	}
	/**
	 * refresh_token
	 */
	public function refresh_token($cred) {
		$client_id = $cred['client_id'];
		$client_secret = $cred['client_secret'];
		$refresh_token = isset($cred['refresh_token']) ? $cred['refresh_token'] : '';
	
		$response = wp_remote_post(
			self::OAUTH2_TOKEN_URI,
			array(
				'body' => array(
					'grant_type' => 'refresh_token',
					'refresh_token' => $refresh_token,
					'client_secret' => $client_secret,
					'client_id' => $client_id,
				),
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			)
		);
	
		if (is_wp_error($response)) {
			// Handle error
			return esc_html($response->get_error_message());
		}
	
		$res = json_decode(wp_remote_retrieve_body($response));
	
		if (isset($res->access_token)) {
			$data = get_option('auto_backup_cloud_storage', array());
			$data['gdrive']['token'] = $res->access_token;
			$upd = update_option('auto_backup_cloud_storage', $data);
	
			return $upd ? 'success' : 'error';
		} else {
			return wp_remote_retrieve_body($response);
		}
	}
	
	/**
	 * Get File 
	 */
	public function get_files($token, $folderid) {
		$response = wp_remote_get(
			'https://www.googleapis.com/drive/v2/files?q=%27' . $folderid . '%27+in+parents',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Accept' => 'application/json',
				),
			)
		);
	
		if (is_wp_error($response)) {
			// Handle error
			return $response->get_error_message();
		}
	
		return wp_remote_retrieve_body($response);
	}
	
	/**
	 * Delete File
	 */
	public function delete_file($cred, $fileid) {
		$response = wp_remote_request(
			'https://www.googleapis.com/drive/v2/files/' . $fileid,
			array(
				'method' => 'DELETE',
				'headers' => array(
					'Authorization' => 'Bearer ' . $cred['token'],
					'Accept' => 'application/json',
				),
			)
		);
	
		if (is_wp_error($response)) {
			// Handle error
			$res = json_decode(wp_remote_retrieve_body($response));
	
			if (isset($res->error) && $res->error->code == 401) { // 401 = token expired
				$token_res = $this->refresh_token($cred);
				if ($token_res == 'success') {
					return $this->delete_file($cred, $fileid);
				} else {
					return $token_res;
				}
			} else {
				return wp_remote_retrieve_body($response);
			}
		} else {
			return 1;
		}
	}
	
	/**
	 * Download File
	 */
	public function download_file($file, $cred, $bakname) {
		$response = wp_remote_get(
			$file,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $cred['token'],
					'Accept' => 'application/zip',
				),
			)
		);
	
		if (is_wp_error($response)) {
			// Handle error
			$res = json_decode(wp_remote_retrieve_body($response));
	
			if (isset($res->error) && $res->error->code == 401) { // 401 = token expired
				$token_res = $this->refresh_token($cred);
				if ($token_res == 'success') {
					return $this->download_file($file, $cred, $bakname);
				} else {
					return $token_res;
				}
			} else {
				return wp_remote_retrieve_body($response);
			}
		} else {
			return file_put_contents(AUTO_BACKUP_DIR . '/temp/' . $bakname . '.zip', wp_remote_retrieve_body($response));
		}
	}
} 
?>