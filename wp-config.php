<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'woo_adons');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'Z8F80~EG}04f5),1JfZxe*{?`a~>u9R2+%~A) tBk&^%oT&S~Zkfpj;@Rdmc=UfY');
define('SECURE_AUTH_KEY',  'c8a&I]l@]1H]Lj_S>]K.c,Ur*3YA73Bkf>p5b7d&E>UVc/Y!ij-GZ/6Bbwc58;iE');
define('LOGGED_IN_KEY',    'BU1?,]ou|+eV?G0,=;:18JWuS`V2lD;x8G&dBg.B,YTqr-FzJ4bED9>2p&rJQ&C)');
define('NONCE_KEY',        'd++e9T_^BN9lOMG}+NWIIc2JQ%`Njw-f#O^xM5[c`yW?P.TutPC|29@,uLut8`@y');
define('AUTH_SALT',        'GaB.bw)Y9%#.jv]YvsAMf/|Xb>,X7f`7m<kFHet;wf~5Q06,Si{Ana+V=S)>qP;N');
define('SECURE_AUTH_SALT', ';R2Z2qo?CC+3rvMAp}{M31n]Zso6z_4rmmbp`_QNWsLCsDf4@:1j<wYJ1HpNa%xw');
define('LOGGED_IN_SALT',   'j_;; ae%n0y!%}3wW`6`g)5hp$%Nqd(L:-P:&P`oUMuAkxP.hXa#xcde&5)1Xy4y');
define('NONCE_SALT',       '<Pw>bH=Zj6ZX! }k$y>o_B,9`a?-x0,,#?:DDpayz9=XwXhj6!j1Wbr$$&[#>#uH');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);
define('FS_METHOD','direct');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
