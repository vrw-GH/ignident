<?php
if ( ! defined( 'ABSPATH' ) ){ exit; }
/**
 * The FTP related functionality of the plugin.
 *
 * @link       https://www.autobackup.io/
 * @since      1.0.0
 *
 * @package    Auto_Backup
 * @subpackage Auto_Backup/admin
 */
class Auto_Backup_FTP_API {

    private $credentials;

    public function __construct() {
        $data = get_option('auto_backup_cloud_storage');
        $this->credentials = $data['ftp'];
    }

    // Check Connection
    public function check_connection($host, $username, $password) {
        $ftp_conn = ftp_connect($host);
        if (!$ftp_conn) {

            return printf(esc_html__("Could not connect to %s", "autobackup"), esc_html($host));

        }

        $login = ftp_login($ftp_conn, $username, $password);

        if (!$login) {
            return esc_html__("Connection failed, please check credentials.", "autobackup");
        } else {
            $dir = $this->create_directory($host, $username, $password);
            if ($dir) {
                return 1;
            } else {
                return $dir;
            }
        }
    }

    // Create Directory
    public function create_directory($host, $username, $password) {
        $dir = '/public_html/autobackup';

        $ftp_conn = ftp_connect($host);
        $login = ftp_login($ftp_conn, $username, $password);

        if (ftp_mkdir($ftp_conn, $dir)) {
            return 1;
        } else {
            return printf(esc_html__("Error while creating %s", "autobackup"),esc_html($dir));
        }
    }

    // Upload File
    public function upload_object($zipcreated, $zipNewName) {
        $cred = $this->credentials;

        $ftp_conn = ftp_connect($cred['host']);
        $login = ftp_login($ftp_conn, $cred['username'], $cred['password']);

        $dir = '/public_html/autobackup';

        $fp = fopen($zipcreated, "r");

        // Upload file
        if (ftp_fput($ftp_conn, $dir . '/' . $zipNewName . ".zip", $fp)) {
            $hostArr = explode('ftp.', $cred['host']);
            return array('status' => 1, 'url' => 'https://' . $hostArr[1] . '/autobackup/' . $zipNewName . ".zip");
        } else {
            return array('status' => 0, 'error' => esc_html__("Error uploading.", "autobackup"));
        }

        // Close connection
        ftp_close($ftp_conn);
    }

    // Download File
    public function download_object($zipcreated, $zipNewName) {
        $cred = $this->credentials;

        $ftp_conn = ftp_connect($cred['host']);
        $login = ftp_login($ftp_conn, $cred['username'], $cred['password']);

        $dir = '/public_html/autobackup';

        $fp = fopen($zipcreated, "r");

        // Upload file
        if (ftp_fput($ftp_conn, $dir . '/' . $zipNewName . ".zip", $fp)) {
            $hostArr = explode('ftp.', $cred['host']);
            return array('status' => 1, 'url' => 'https://' . $hostArr[1] . '/autobackup/' . $zipNewName . ".zip");
        } else {
            return array('status' => 0, 'error' => esc_html__("Error uploading.", "autobackup"));
        }

        // Close connection
        ftp_close($ftp_conn);
    }

    // Delete File
    public function delete_object($bkp_name) {
        $cred = $this->credentials;

        $ftp_conn = ftp_connect($cred['host']);
        $login = ftp_login($ftp_conn, $cred['username'], $cred['password']);

        $path = '/public_html/autobackup/' . $bkp_name . ".zip";

        // Try to delete file
        if (ftp_delete($ftp_conn, $path)) {
            return 1;
        } else {
            return esc_html__("Could not delete", "autobackup");
        }

        // Close connection
        ftp_close($ftp_conn);
    }
}	