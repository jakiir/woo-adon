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
define('AUTH_KEY',         'jvQZw`>hmDGEk}/m|iL&vHunLG^t3pv;sw(0;iG>YFGBJ?A;{FzPl_kI57f<EFU~');
define('SECURE_AUTH_KEY',  '$+t)]L.Ta+ni:-2I:B+m33J/AQ8q^VG=6^-b[Q[3F*dz{LC9s=&iIjo2k~2zflc<');
define('LOGGED_IN_KEY',    'ZJO!]CDO<4ETxf%n2rdA.m?Jxl-&*aE=<.|$]G3P8cfSwuCSf3`RZVd!D1a_#>r;');
define('NONCE_KEY',        'E`EtN$QEqy cWx.I=wa5E_Feqo>-il@W?5 f/XwSD3x,uGUj5*KtF~P+r}5L|&}B');
define('AUTH_SALT',        'KQJi kT*r<WUx=x@;YpZ+%b,G>+P82|B@w~~oZnb|V`wzpp Ldi0~XTkg)X&]5J^');
define('SECURE_AUTH_SALT', '$aY`Z6poQT&~G_}}m)W:DX$ba?RgD{[2d6Rh^|CH1!P>;az?Dk>|M+h4uS*WZYTN');
define('LOGGED_IN_SALT',   't&cLFI6MN$|[8M:Ih?rfYbNf6GB[=0/|5U2)M~WJQ^_,|a_b7*Av9<^2.CzmEjw6');
define('NONCE_SALT',       'oR[_s$6E/GHo&D<?R2I2t:3=oW[T5+b.9v,5~1p!R$g 3G).j$eF,)b*JzSDMT-4');

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

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
