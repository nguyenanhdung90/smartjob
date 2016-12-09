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
define('WP_HOME','http://smartjob.vn/');
define('WP_SITEURL','http://smartjob.vn/');
define('DB_NAME', 'smartjobvn');

/** MySQL database username */
define('DB_USER', 'smartjobvn');

/** MySQL database password */
define('DB_PASSWORD', 'DCV@SmartJob#123');

/** MySQL hostname */
//define('DB_HOST', '125.212.225.108');
define('DB_HOST', 'localhost');


/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'latin1');

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

define('AUTH_KEY',         '[p|/|+[4JpRTop|nA=|ZibR~V>F+4;b%Hv{bOFnJoCcrw;9|{iRAbAJc1FI~yRRS');
define('SECURE_AUTH_KEY',  'hM,4?e${=lFo4cX&+;/+b$-m|UAQ%Kqz*AQ7O9J14CgfaT!3@w5lh]9GhuJ*+D9S');
define('LOGGED_IN_KEY',    '31|E5~Wjdn$N/KUF_Py:+o^vTXK.hC2@p.>gysq9`p#B:+M+Aoz!Cqml]3;-2WwJ');
define('NONCE_KEY',        '{dtZ|{th5L+9LR5C:zl=.?<4pk~qycx{D+&nOz<*sT/Qt8x`<B`HZO0&qfEA=qCE');
define('AUTH_SALT',        '9Ei<t]l#yuEX- Chx=0t%+8T`_7A*+cFn4)yv`G5zUY@-?[c&OiYEg:5U.wMAnKP');
define('SECURE_AUTH_SALT', 'IIf+0D!USut^OP2Es2etr!KHL^+m+,wM[?4D[j~e|Qs>W}|iohImpXL$8}KsDR+k');
define('LOGGED_IN_SALT',   ':H-)j0?G}FHDz)A%3qDK.}6gUiNzCqd|M~jG#>[HNb4No6N+hYIpsA}9J;7[hU[!');
define('NONCE_SALT',       '{ZP++L-+yCC|wh!&It?>bHR]8m1r<2%kPp(+7g!~FWOb[-slXxG|W#hlB KTV5FK');

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
define('WP_CACHE', true); // Added by W3 Total Cache
define('WP_DEBUG', false);
define( 'WP_POST_REVISIONS', false );
/* That's all, stop editing! Happy blogging. */
//define('WP_ALLOW_MULTISITE', true);
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');


