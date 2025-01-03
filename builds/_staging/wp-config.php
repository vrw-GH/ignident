<?php
include_once
   'X:/Apache-root/htdocs/common/includes/get_dotenvs/get-dotenvs.php'; 
   #$_SERVER['DOCUMENT_ROOT'].'/common/includes/get_dotenvs/get-dotenvs.php'; 
   #---------------------------------  vw- added local .env variables //see httpd.conf 
   #$_SERVER['MY_BASEDIR'].'/common/includes/get_dotenvs/get-dotenvs.php'; 
#
### DEV_MODEs: 0-Prod, 1-Alert/NoCache, 2-&Debug, 3-&Die
define('DEV_MODE',(int)getenv('ENV_DEV_MODE'));
echo (DEV_MODE<>0) ? "DevMode:".DEV_MODE : "";
#--------------------------------- 

/** Enable W3 Total Cache */
define('WP_CACHE',DEV_MODE<1?true:false); // Added by W3 Total Cache; //vw-no Cache if debug;

define( 'ITSEC_ENCRYPTION_KEY', 'IHpufDIkT191VTolelFVcn1wMmBdSzFmYWlUIzhAZVhrJD0vRllsMmxzMF5IQXVgUm8mbUw3WyN0L2AoTSp0KA==' );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
define( 'DB_NAME',		getenv('ENV_DB_NAME') );
define( 'DB_USER', 		getenv('ENV_DB_USER') );
define( 'DB_PASSWORD', 		getenv('ENV_DB_PASSWORD') );
define( 'DB_HOST', 		getenv('ENV_DB_HOST') );
define( 'DB_CHARSET', 		getenv('ENV_DB_CHARSET') );
define( 'DB_COLLATE', 		getenv('ENV_DB_COLLATE') );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         getenv('ENV_AUTH_KEY') );
define( 'SECURE_AUTH_KEY',  getenv('ENV_SECURE_AUTH_KEY') );
define( 'LOGGED_IN_KEY',    getenv('ENV_LOGGED_IN_KEY') );
define( 'NONCE_KEY',        getenv('ENV_NONCE_KEY') );
define( 'AUTH_SALT',        getenv('ENV_AUTH_SALT') );
define( 'SECURE_AUTH_SALT', getenv('ENV_SECURE_AUTH_SALT') );
define( 'LOGGED_IN_SALT',   getenv('ENV_LOGGED_IN_SALT') );
define( 'NONCE_SALT',       getenv('ENV_NONCE_SALT') );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = getenv('ENV_TABLE_PREFIX') ;

#define('FORCE_SSL_ADMIN', true);

#define('DISALLOW_FILE_EDIT', true);

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
# define( 'WP_DEBUG', false ); // Added by Defender;
define( 'WP_DEBUG', DEV_MODE<>0?true:false ); // vw
define( 'WP_DEBUG_LOG', WP_DEBUG );
define( 'WP_DEBUG_DISPLAY', WP_DEBUG );

/* -------------------------------------------------------------------- */
/* Add any custom values between this line and the "stop editing" line. */

   define('WP_HOME', 
      getenv('ENV_WP_HOME') ?
         getenv('ENV_WP_HOME')
         : "http://{$_SERVER['HTTP_HOST']}" .rtrim($_SERVER['REQUEST_URI'],"/")
   );
   define('WP_SITEURL', 
      getenv('ENV_WP_SITEURL') ?
         getenv('ENV_WP_SITEURL')
         : "http://{$_SERVER['HTTP_HOST']}" .rtrim($_SERVER['REQUEST_URI'],"/")
   );


/* That's all, stop editing! Happy publishing. */
/* ------------------------------------------- */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}


#---------------------------------------- vw.Debug
   echo (DEV_MODE>0)
   ? '<script>alert("'
         .'Host: '	    . $_SERVER['HTTP_HOST']
         .'\nWPHome: '      . WP_HOME        // browser url
         .'\nWPSite:     '  . WP_SITEURL
         .'\nReqURI:   '    . $_SERVER['REQUEST_URI']
         .'\nPHPSelf:  '    . $_SERVER['PHP_SELF']
#         .'\n'
         .'\nRootDir:  '    . __DIR__
         .'\nFILEdir:   '   . dirname(__FILE__)
         .'\nCWD:      '    . getcwd()      // Current Working Directory
         .'\nAbsPath: '	    . ABSPATH
         .'\nDatabase: '    . DB_NAME
         .'\nDebug/Cache: ' . WP_DEBUG.'/'.(int)WP_CACHE
      .'")</script>'
   :'';
   if (DEV_MODE>0) echo '>>here10 - WP-CONFIG loaded!';
   if (DEV_MODE>2) die;
#---------------------------------------- vw.

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
