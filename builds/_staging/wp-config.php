<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/get-dotenvs.php';
#set_include_path(get_include_path() . PATH_SEPARATOR . 'X:\TLCDev\PHP\_local_root\dev\common\includes');
#include_once 'get-dotenvs.php';
# or??
# symlink /common/includes/get_dotenvs/ -> always in base
#include_once $_SERVER['DOCUMENT_ROOT'] . '/get-dotenvs.php';
#
### DEV_MODEs: 0-Prod, 1-Alert/NoCache, 2-&Debug, 3-&Die
define('DEV_MODE', (int)getenv('ENV_DEV_MODE'));

echo (DEV_MODE <> 0)
   ? '<script>alert("'
   . '👉👉 CLEAR WP CACHE!! 👈👈'
   . '\nDevMode: '   . DEV_MODE . ' (ℹ️ in Console)'
   . '\nServer: '   . $_SERVER['SERVER_NAME']
   . '\nProtocol: ' . $_SERVER['SERVER_PROTOCOL']
   . '\nHost: '   . $_SERVER['HTTP_HOST']
   . '\nPort: '   . $_SERVER['SERVER_PORT']
   . '\nDocRoot: '   . $_SERVER['DOCUMENT_ROOT']
   . '\nCWD: '   . getcwd()      // Current Working Directory
   . '")</script>'
   : '';

#--------------------------------- 

/** Enable W3 Total Cache */
define('WP_CACHE', DEV_MODE < 1 ? true : false); // Added by W3 Total Cache; //vw-no Cache if debug;

define('ITSEC_ENCRYPTION_KEY', 'IHpufDIkT191VTolelFVcn1wMmBdSzFmYWlUIzhAZVhrJD0vRllsMmxzMF5IQXVgUm8mbUw3WyN0L2AoTSp0KA==');

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
define('DB_NAME',      getenv('ENV_DB_NAME'));
define('DB_USER',       getenv('ENV_DB_USER'));
define('DB_PASSWORD',       getenv('ENV_DB_PASSWORD'));
define('DB_HOST',       getenv('ENV_DB_HOST'));
define('DB_CHARSET',       getenv('ENV_DB_CHARSET'));
define('DB_COLLATE',       getenv('ENV_DB_COLLATE'));

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
define('AUTH_KEY',         '*J2u[|o>Q4(8lPo3gP/MZ 3%M4DSpr|M+(shI$Cx7Y65ufk_Ph1T2YfE-Q|:-Tqb');
define('SECURE_AUTH_KEY',  'VjEWS2,39o#DMq-gq*b9{/x1V-3N!g9r+-K6B[I-$ ?C2RAA&$.Wpg,}<AezPQ_@');
define('LOGGED_IN_KEY',    'v%:DbIXrASE]~r%nHi`xTSq/!Lh`Y;$P0}vk_lL@$VBE_o2Yj8JDkK=>4P|d.,$p');
define('NONCE_KEY',        '/4X8gwfH#6>o+~gy482ggYW6UWu*w:&d|M=+9G!bDNu!AQ^r!+}/gb*-re^o~iEU');
define('AUTH_SALT',        'YB|DyJy-4M:P3Nv9]o@,++7h6 4Fb5;tQq@7~ID!;~F7Ht?X@k|~Oq7!#Y1+#s^H');
define('SECURE_AUTH_SALT', ':`2Y]J_N_i2 :yuK/xl<GRp+<#$dG{iSC/W0 <h;yJmN! y(K]HBcRlKi$+4!X`G');
define('LOGGED_IN_SALT',   '%#esRuZ-IvEjiP-se]0NS=H2>?N,|-..k?jX)>{3<k~QFB$l!ZI_hM53-o^_C`5C');
define('NONCE_SALT',       'pBBxwy/+Ui`h#MAB r h5&..9DvEl) Qw{6wmZxQYl_SJ6,<8cSt=73fVdy@bV5f');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = getenv('ENV_TABLE_PREFIX');

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
define('WP_DEBUG', DEV_MODE <> 0 ? true : false); // vw
define('WP_DEBUG_LOG', WP_DEBUG);
define('WP_DEBUG_DISPLAY', WP_DEBUG);

/* -------------------------------------------------------------------- */
/* Add any custom values between this line and the "stop editing" line. */

define(
   'WP_HOME',
   getenv('ENV_WP_HOME') ?
      getenv('ENV_WP_HOME')
      : "http://{$_SERVER['HTTP_HOST']}" . rtrim($_SERVER['REQUEST_URI'], "/")
);
define(
   'WP_SITEURL',
   getenv('ENV_WP_SITEURL') ?
      getenv('ENV_WP_SITEURL')
      : "http://{$_SERVER['HTTP_HOST']}" . rtrim($_SERVER['REQUEST_URI'], "/")
);


/* That's all, stop editing! Happy publishing. */
/* ------------------------------------------- */

/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
   define('ABSPATH', __DIR__ . '/');
}


#---------------------------------------- vw.Debug
echo (DEV_MODE > 0) ? '<script>console.warn("👉👉 CLEAR WP CACHE!! 👈👈")</script>' : '';
echo (DEV_MODE > 0)
   ? '<script>console.log("'
   . 'DevMode:    '    . DEV_MODE
   . '\nServer:     '  . $_SERVER['SERVER_NAME']
   . '\nProtocol:   '  . $_SERVER['SERVER_PROTOCOL']
   . '\nHost:       '  . $_SERVER['HTTP_HOST']
   . '\nPort:       '  . $_SERVER['SERVER_PORT']
   . '\nDocRoot:  '    . $_SERVER['DOCUMENT_ROOT']
   . '\n--------------------------------------------'
   . '\nDebug/Cache: ' . WP_DEBUG . '/' . (int)WP_CACHE
   . '\nDatabase: '    . DB_NAME
   . '\nWPHome:   '    . WP_HOME        // browser url
   . '\nWPSite:   '    . WP_SITEURL
   . '\nReqURI:   '    . $_SERVER['REQUEST_URI']
   . '\nPHPSelf:  '    . $_SERVER['PHP_SELF']
   #         .'\n'
   . '\nCWD:      '    . getcwd()      // Current Working Directory
   . '\nAbsPath:  '    . ABSPATH
   . '")</script>'
   : '';
if (DEV_MODE > 1) echo '<script>console.info("👉>>here10 - WP-CONFIG loaded!")</script>';
if (DEV_MODE > 2) die;
#---------------------------------------- vw.

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
