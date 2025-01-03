<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }
/**
 * The NeevCloud related functionality of the plugin.
 *
 * @link       https://www.autobackup.io/
 * @since      1.0.0
 *
 * @package    Auto_Backup
 * @subpackage Auto_Backup/admin
 */
require_once dirname(__FILE__) . '/aws/vendor/autoload.php';

use Aws\S3\S3Client;  
use Aws\Exception\AwsException; 

class Auto_Backup_NeevCloud_API {
	
	private $credentials;
	private $bucket;
	
	public function __construct() {
		
		$data = get_option('auto_backup_cloud_storage');
		
		if(isset($data['neevcloud'])){
			$this->bucket = $data['neevcloud']['bucket_name'];
			$this->credentials = [
				'version' => 'latest',
				'region'  => 'central',
				'endpoint' => 'https://s3-api-central.neevcloud.com',
				'use_path_style_endpoint' => true,
				'credentials' => [
					'key'    => $data['neevcloud']['access_key'],
					'secret' => $data['neevcloud']['access_secret'],
				],
			];
		}
		
	}
	
	public function check_credentials( $access_key, $access_secret ) {
		
		$s3Client = new S3Client( [
			'version' => 'latest',
			'region'  => 'central',
			'endpoint' => 'https://s3-api-central.neevcloud.com',
			'use_path_style_endpoint' => true,
			'credentials' => [
				'key'    => $access_key,
				'secret' => $access_secret,
			],
		] );
		
		try {
			$buckets = $s3Client->listBuckets();
			if(!empty($buckets)){
				return 1;
			}
		} catch (AwsException $e) {
			return esc_html__('Error: ','autobackup') . esc_html($e->getAwsErrorMessage());
		}
		
	}
	
	public function get_buckets() {
		
		$s3Client = new S3Client( $this->credentials );
				
		try {
			$buckets = $s3Client->listBuckets();
			foreach ($buckets['Buckets'] as $bucket) {
				echo esc_html($bucket['Name']) . "\n";
			}
		} catch (AwsException $e) {
			echo esc_html__('Error: ','autobackup') . esc_html($e->getAwsErrorMessage());
		}

	}
	
	public function create_buckets($bkt_name, $access_key, $secret_key) {
		
		$cred =	[
				'version'     => 'latest',
				'region'      => 'us-east-1',
				'credentials' => [
					'key'    => $access_key,
					'secret' => $secret_key,
				],
			];
		
		$s3Client = new S3Client( $cred );	
		try {
			$result = $s3Client->createBucket([
				'Bucket' => $bkt_name,
			]);
			return $result;
		} catch (AwsException $e) {
			return esc_html__('Error: ','autobackup') . esc_html($e->getAwsErrorMessage());
		}

	}
	
	public function upload_object($source, $key){
	
		try{
			$s3Client = new S3Client( $this->credentials );
			
			$object = array(
				'ACL' => 'public-read',
				'Bucket' => $this->bucket,
				'Key' => $key,
				'SourceFile' => $source,
				'ContentType'=> mime_content_type($source)
			);
			
			$result = $s3Client->putObject( $object );
			
			if ($result["@metadata"]["statusCode"] == '200') {
				$ObjectURL = $result["ObjectURL"];
			}
			
			return array( 'url' => $ObjectURL, 'key' => $key );
			
		}catch (S3Exception $e) {
			return esc_html($e->getMessage());
		}
		
	}
	
	public function delete_object( $key ){
		
		try{
			$s3Client = new S3Client( $this->credentials );
			$res = $s3Client->deleteObjects([
				'Bucket'  => $this->bucket,
				'Delete' => [
					'Objects' => [
						[
							'Key' => $key
						]
					]
				]
			]);
			$del = $res->get( 'Deleted' );
			return 1;
		}catch (S3Exception $e) {
			return esc_html($e->getMessage());
		}
		
	}
	
	public function get_object($keyname, $serverlocation) {
		$bkt = $this->bucket;
		
		$response = wp_remote_get("https://s3-api-central.neevcloud.com/$bkt/$keyname");
	
		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
			$contents = wp_remote_retrieve_body($response);
	
			return file_put_contents($serverlocation, $contents);
		} else {
			return false;
		}
	}
	
		     
}