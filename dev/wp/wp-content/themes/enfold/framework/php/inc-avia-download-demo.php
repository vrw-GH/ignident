<?php
/**
 * Download demo files from external server
 *
 * @since 4.8.2
 * @added_by Günter
 */
if( ! defined( 'AVIA_FW' ) )	{	exit( 'No direct script access allowed' );	}

global $avia_config;

$demo_name = ! empty( $_REQUEST['demo_name'] ) ? stripslashes( $_REQUEST['demo_name'] ) : '';
$demo_full_name = ! empty( $_REQUEST['demo_full_name'] ) ? stripslashes( $_REQUEST['demo_full_name'] ) : '';
$debug_prefix = sprintf( __( 'Demo File Download (%s):', 'avia_framework' ), $demo_full_name ) . ' ';

if( defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( $debug_prefix . __( 'Download started', 'avia_framework' ) );
}

if( empty( $_REQUEST['download_url'] ) || '' === $demo_name )
{
	$msg = __( 'To few parameters provided - a download is not possible.', 'avia_framework' );
	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
	{
		error_log( $debug_prefix . $msg );
	}
	exit( 'avia_error-' . $msg );
}

/**
 * The target folder is always derived from the ( sanitized ) demo name on the server, never taken
 * from the request, so a download can only ever write inside the demo folder.
 *
 * @since 8.1
 */
$import_dir = avia_demo_import_dir( $demo_name );
if( '' === $import_dir )
{
	$msg = __( 'Invalid demo name - a download is not possible.', 'avia_framework' );
	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
	{
		error_log( $debug_prefix . $msg );
	}
	exit( 'avia_error-' . $msg );
}

/**
 * Only allow downloads from the first party demo download server. Self hosted demos ( set up via
 * the 'avf_demo_import_settings' filter ) can add their host with this filter.
 *
 * @since 8.1
 * @param string[] $hosts
 * @return string[]
 */
$allowed_hosts = apply_filters( 'avf_demo_import_download_hosts', array( 'kriesi.at' ) );
$allowed_hosts = array_map( 'strtolower', (array) $allowed_hosts );
$download_host = strtolower( (string) wp_parse_url( $_REQUEST['download_url'], PHP_URL_HOST ) );

if( '' === $download_host || ! in_array( $download_host, $allowed_hosts, true ) )
{
	$msg = __( 'The demo download URL is not from an allowed server - a download is not possible.', 'avia_framework' );
	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
	{
		error_log( $debug_prefix . $msg );
	}
	exit( 'avia_error-' . $msg );
}


$tmp_filename = download_url( $_REQUEST['download_url'] );
if( $tmp_filename instanceof WP_Error )
{
	$msg = __( 'Error accessing file for download:<br /><br />', 'avia_framework' ) . implode( '<br>', $tmp_filename->get_error_messages() );
	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
	{
		error_log( $debug_prefix . $msg );
	}
	exit( 'avia_error-' . $msg );
}

avia_backend_delete_folder( $import_dir );

if( ! avia_backend_create_folder( $import_dir ) )
{
	$msg = sprintf( __( 'Unable to create the download folder <pre>%s</pre> for demo files.', 'avia_framework' ), $import_dir );
	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
	{
		error_log( $debug_prefix . $msg );
	}

	exit( 'avia_error-' . $msg );
}

$zip = new ZipArchive();
if ( ! $zip->open( $tmp_filename ) )
{
	$msg = __( 'Wasn\'t able to work with Zip Archive', 'avia_framework' );
	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
	{
		error_log( $debug_prefix . $msg );
	}

	exit( 'avia_error-' . $msg );
}

/*
 * iOS systems may add system files to zip like _MACOSX.
 * We check for first files to copy and ignore other files included.
 *
 * Files must be located like:
 *
 *		$demo_name.*
 *
 * or
 *
 *		$demo_name/$demo_name.*
 *
 * @since 5.0
 */
$imported = array(
			'xml'	=> false,
			'txt'	=> false
		);

$check1 = $demo_name;
$check2 = $demo_name . '/' . $demo_name;

for( $i = 0; $i < $zip->numFiles; $i++ )
{
	try
	{
		$source_file = $zip->getNameIndex( $i );
		$source_file_check = trim( str_replace( '\\', '/', $source_file ) );

		if( 0 !== stripos( $source_file_check, $check1 ) && 0 !== stripos( $source_file_check, $check2 ) )
		{
			continue;
		}

		//	Only the demo content ( .xml ) and the theme options ( .txt ) are extracted.
		//	.php option files are no longer supported - never extract executable files from a zip.
		if( false === stripos( $source_file_check, 'xml' ) && false === stripos( $source_file_check, 'txt' ) )
		{
			continue;
		}

		if( false !== stripos( $source_file_check, 'xml' ) )
		{
			if( $imported['xml'] )
			{
				continue;
			}

			$imported['xml'] = true;
		}
		else
		{
			if( $imported['txt'] )
			{
				continue;
			}

			$imported['txt'] = true;
		}

		$skip = stripos( $source_file_check, '/' );
		if( false === $skip )
		{
			$source_file_name = $source_file_check;
		}
		else
		{
			$source_file_name = substr( $source_file_check, $skip + 1 );
		}

		$dest_file = trailingslashit( $import_dir ) . $source_file_name;

		$fp = $zip->getStream( $source_file );
		if( ! $fp )
		{
			throw new Exception();
		}

		$ofp = fopen( $dest_file, 'w' );
		if( false === $ofp )
		{
			throw new Exception();
		}

		while( ! feof( $fp ) )
		{
			$content = fread( $fp, 8192 );
			if( false === $content )
			{
				throw new Exception();
			}
			if( false === fwrite( $ofp, $content ) )
			{
				throw new Exception();
			}
		}

		fclose( $fp );
		fclose( $ofp );
	}
	catch( Exception $ex )
	{
		if( ! empty( $fp ) )
		{
			fclose( $fp );
		}
		if( ! empty( $ofp ) )
		{
			fclose( $ofp );
		}

		$zip->close();
		unlink( $tmp_filename );

		avia_backend_delete_folder( $import_dir );

		$msg = __( 'Wasn\'t able to read demo files from downloaded zip file.', 'avia_framework' );
		if( defined( 'WP_DEBUG' ) && WP_DEBUG )
		{
			error_log( $debug_prefix . $msg );
		}
		exit( 'avia_error-' . $msg );
	}
}

/*
 * Files not matching the demo name are silently skipped in the loop above.
 * A wrongly packed zip file would report a successfull download and the user only gets a
 * misleading "XML file is missing" message in the following import step.
 * Therefore we collect the content of the zip file to report a usefull error message.
 *
 * @since 8.0
 */
$zip_content = array();

for( $i = 0; $i < $zip->numFiles; $i++ )
{
	$source_file_check = trim( str_replace( '\\', '/', $zip->getNameIndex( $i ) ) );

	//	skip folders and system files added by iOS systems
	if( 0 === stripos( $source_file_check, '__MACOSX' ) || '/' == substr( $source_file_check, -1 ) )
	{
		continue;
	}

	$zip_content[] = $source_file_check;
}

$zip->close();
unlink( $tmp_filename );

if( ! $imported['xml'] )
{
	avia_backend_delete_folder( $import_dir );

	$msg = sprintf( __( 'The downloaded zip file does not contain the demo content file <strong>%1$s.xml</strong>.<br/>Files found in zip file: <strong>%2$s</strong><br/>Demo files must be named <strong>%1$s.xml</strong> and <strong>%1$s.txt</strong> and are allowed to be placed in a folder <strong>%1$s/</strong>.', 'avia_framework' ), $demo_name, implode( ', ', $zip_content ) );

	if( defined( 'WP_DEBUG' ) && WP_DEBUG )
	{
		error_log( $debug_prefix . $msg );
	}

	exit( 'avia_error-' . $msg );
}

if( ! $imported['txt'] && defined( 'WP_DEBUG' ) && WP_DEBUG )
{
	error_log( $debug_prefix . sprintf( __( 'The downloaded zip file does not contain the theme options file <strong>%1$s.txt</strong> - demo is imported without theme options.', 'avia_framework' ), $demo_name ) );
}

