<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }
/**
 * The Dropbox related functionality of the plugin.
 *
 * @link       https://www.autobackup.io/
 * @since      1.0.0
 *
 * @package    Auto_Backup
 * @subpackage Auto_Backup/admin
 */
class Auto_Backup_DB_API {

	/***
	 * Dropbox token generate 
	 */
	public function auto_backup_dropbox_generate_token($code) {
		$app_key = get_transient('auto_backup_drp_app_key');
		$app_secret = get_transient('auto_backup_drp_app_secret');
	
		$response = wp_remote_post(
			'https://api.dropbox.com/oauth2/token',
			array(
				'body' => array(
					'code' => $code,
					'grant_type' => 'authorization_code',
					'redirect_uri' => admin_url('admin.php?page=autobackup-cloud-storage'),
					'client_id' => $app_key,
					'client_secret' => $app_secret,
				),
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			)
		);
	
		if (is_wp_error($response)) {
			// Handle error
			echo esc_html__('Error: ','autobackup') . esc_html($response->get_error_message());
			die();
		} else {
			$tokendata = json_decode(wp_remote_retrieve_body($response));
	
			if (isset($tokendata->error)) {
				echo esc_html($tokendata->error);
				exit();
			} else {
				$response = wp_remote_post(
					'https://api.dropboxapi.com/2/files/create_folder_v2',
					array(
						'body' => wp_json_encode(array('autorename' => false, 'path' => '/autobackup')),
						'headers' => array(
							'Authorization' => 'Bearer ' . $tokendata->access_token,
							'Content-Type' => 'application/json',
						),
					)
				);
	
				$folderResponse = wp_remote_retrieve_body($response);
	
				$data = get_option('auto_backup_cloud_storage', array());
				$data['dropbox']['app_key'] = $app_key;
				$data['dropbox']['app_secret'] = $app_secret;
				$data['dropbox']['token'] = $tokendata->access_token;
				$data['dropbox']['refresh_token'] = $tokendata->refresh_token;
	
				update_option('auto_backup_cloud_storage', $data);
			}
		}
	}
	
	/***
	 * Dropbox refresh token 
	 */
	public function auto_backup_refresh_dropbox_token() {
		$data = get_option('auto_backup_cloud_storage');
	
		$refresh_token = $data['dropbox']['refresh_token'];
		$appKey = $data['dropbox']['app_key'];
		$appSecret = $data['dropbox']['app_secret'];
	
		$response = wp_remote_post(
			'https://api.dropbox.com/oauth2/token',
			array(
				'body' => array(
					'grant_type' => 'refresh_token',
					'refresh_token' => $refresh_token,
					'client_id' => $appKey,
					'client_secret' => $appSecret,
				),
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
			)
		);
	
		$res = json_decode(wp_remote_retrieve_body($response));
	
		if (isset($res->error)) {
			echo esc_html($res->error_description);
		} else {
			$data['dropbox']['token'] = $res->access_token;
			$update_response = update_option('auto_backup_cloud_storage', $data);
	
			if ($update_response) {
				echo esc_html__('success','autobackup');
			} else {
				echo esc_html__('error','autobackup');
			}
		}
	}
	
	/***
	 * Dropbox Upload File 
	 */
	public function auto_backup_dropbox_upload_file($cred, $file) {
		$filename = basename($file);
		$api_url = 'https://content.dropboxapi.com/2/files/upload';
		$headers = array(
			'Authorization' => 'Bearer ' . $cred['token'],
			'Content-Type' => 'application/octet-stream',
			'Dropbox-API-Arg' => wp_json_encode(
				array(
					"path" => '/autobackup/' . $filename,
					"mode" => "add",
					"autorename" => true,
					"mute" => false
				)
			)
		);

		// Read the file contents into a variable
		$file_contents = file_get_contents($file);

		$response = wp_remote_post(
			$api_url,
			array(
				'method' => 'POST',
				'headers' => $headers,
				'body' => $file_contents,
			)
		);

		$result = json_decode(wp_remote_retrieve_body($response));

	
		if (isset($result->error_summary)) {
			if (strpos($result->error_summary, "expired_access_token") !== false || strpos($result->error_summary, "invalid_access_token") !== false) {
				$res = $this->auto_backup_refresh_dropbox_token();
				if ($res == 'success') {
					return $this->auto_backup_dropbox_upload_file($cred, $file);
				} else {
					return array('status' => 0, 'error' => $res);
				}
			} else {
				return array('status' => 0, 'error' => $result->error_summary);
			}
		} else {
			$downloadURL = $this->getSharedLink($cred, $result->path_lower);
			return array('status' => 1, 'downloadurl' => $downloadURL);
		}
	}
	
	/**
	 * Dropbox Upload File 
	 */
	public function DropboxUploadLargeFile($cred, $file) {
		$token = $cred['token'];
		$append_url = esc_url('https://content.dropboxapi.com/2/files/upload_session/append_v2');
		$start_url = esc_url('https://content.dropboxapi.com/2/files/upload_session/start');
		$finish_url = esc_url('https://content.dropboxapi.com/2/files/upload_session/finish');

		$file_name = basename($file);
		$destination_folder = '/autobackup/' . $file_name;
		$info_array = array();
		$info_array["close"] = false;
		$headers = array(
			'Authorization' => 'Bearer ' . $token,
			'Content-Type' => 'application/octet-stream',
			'Dropbox-API-Arg' => wp_json_encode($info_array), // Updated: Use json_encode directly
		);
		$chunk_size = 50000000; // 50mb

		$fp = fopen($file, 'rb');
		$fileSize = filesize($file);
		$tosend = $fileSize;
		$first = $tosend > $chunk_size ? $chunk_size : $tosend;

		$response = wp_remote_post(
			$start_url,
			array(
				'headers' => $headers,
				'body' => fread($fp, $first),
			)
		);

		$resp = explode('"', wp_remote_retrieve_body($response));

		$session = $resp[3];
		$position = $first;

		$info_array["cursor"] = array();
		$info_array["cursor"]["session_id"] = $session;

		while ($tosend > $chunk_size) {
			$info_array["cursor"]["offset"] = $position;
			$headers['Dropbox-API-Arg'] = wp_json_encode($info_array);

			wp_remote_post(
				$append_url,
				array(
					'headers' => $headers,
					'body' => fread($fp, $chunk_size),
				)
			);

			$tosend -= $chunk_size;
			$position += $chunk_size;
		}
		unset($info_array["close"]);
		$info_array["cursor"]["offset"] = $position;
		$info_array["commit"] = array();
		$info_array["commit"]["path"] = $destination_folder;
		$info_array["commit"]["mode"] = array();
		$info_array["commit"]["mode"][".tag"] = "overwrite";
		$info_array["commit"]["autorename"] = true;
		$info_array["commit"]["mute"] = false;
		$info_array["commit"]["strict_conflict"] = false;
		$headers['Dropbox-API-Arg'] = wp_json_encode($info_array);

		$response = wp_remote_post(
			$finish_url,
			array(
				'headers' => $headers,
				'body' => $tosend > 0 ? fread($fp, $tosend) : null,
			)
		);

		fclose($fp);

		$res = json_decode(wp_remote_retrieve_body($response));

		if (isset($res->name)) {
			$downloadURL = $this->getSharedLink($cred, $res->path_lower);
			return array('status' => 1, 'downloadurl' => $downloadURL);
		} else {
			return array('status' => 0, 'error' => wp_remote_retrieve_body($response));
		}
	}

	
	/**
	 * Share Link
	 */
	public function getSharedLink($cred, $path) {

		$url = '';
	    $response = wp_remote_post(
			'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings',
			array(
				'body' => wp_json_encode(
					array(
						'path' => $path,
						'settings' => array(
							'access' => 'viewer',
							'allow_download' => true,
							'audience' => 'public',
							'requested_visibility' => 'public'
						),
					)
				),
				'headers' => array(
					'Authorization' => 'Bearer ' . $cred['token'],
					'Content-Type' => 'application/json'
				),
			)
		);
	
		$res = json_decode(wp_remote_retrieve_body($response));
	
		if (isset($res->url)) {
			$url = $res->url;
		} elseif (strpos($res->error->shared_link_already_exists->error_summary, 'shared_link_already_exists') !== false) {
			$url = $res->error->shared_link_already_exists->url;
		}
	
		return $url = str_replace("dl=0", "dl=1", $url);
	}
	
	/**
	 * Drop Box Delete 
	 */
	public function dropboxDeleteObject($cred, $filename) {
		$response = wp_remote_post(
			'https://api.dropboxapi.com/2/files/delete_v2',
			array(
				'body' => wp_json_encode(array('path' => '/autobackup/' . $filename . '.zip')),
				'headers' => array(
					'Authorization' => 'Bearer ' . $cred['token'],
					'Content-Type' => 'application/json',
				),
			)
		);
	
		$res = json_decode(wp_remote_retrieve_body($response));
	
		if (!isset($res->error)) {
			return 1;
		} else {
			return $res->error_summary;
		}
	}
	
	/*** 
	 * Dropbox Download File 
	 */	
	public function dropboxDownloadFile($cred, $filename, $serverlocation) {
		$response = wp_remote_post(
			'https://content.dropboxapi.com/2/files/download',
			array(
				'body' => '',
				'headers' => array(
					'Authorization' => 'Bearer ' . $cred['token'],
					'Dropbox-API-Arg' => wp_json_encode(array('path' => '/autobackup/' . $filename . '.zip')),
				),
			)
		);
	
		$content = wp_remote_retrieve_body($response);
	
		if (!is_wp_error($response) && !empty($content)) {
			return file_put_contents($serverlocation, $content);
		} else {
			return false;
		}
	}
}