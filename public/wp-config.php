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
define('DB_NAME', 'tur');

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
define('AUTH_KEY',         'FQxoHoeVdqhgU@rcyPqMB$ u58L?S4H*41+h_;!&kjwCrv1a|}kfK]8In#?FN6y<');
define('SECURE_AUTH_KEY',  'xi,)AOi^:8B]Gbt~]G_&F0A|Xg}jnA^;gg.haF$y(Bg]Mg8cAa4]H`@hTm$Nn8yo');
define('LOGGED_IN_KEY',    'Dq^J{bzO0(=If_J5CgP68iM4V3=7(myPRs~ |`^!^oL=FTNO**5,1u1JAHiB]w*y');
define('NONCE_KEY',        'q[Jpk^N)SVh+LC):e.gK(Zx<RYvC*lFRpfEurj)2S:1<z[0tR.pL|G~l<GWO/U`W');
define('AUTH_SALT',        '~/r*)nhaDCuXDo uNGqy]*wHwCh.Z{?Z6>Mm f! oP1S$<sn_YD5BvA3|4<lyNlQ');
define('SECURE_AUTH_SALT', ',-cL4@x){-V~Mp9j>rOpSCQSd(hZXV)y}3cM ZMM9[DyXiTfF.i3iqX46f*eB1B%');
define('LOGGED_IN_SALT',   'v1x{1F&Aod-7Z xTxu&YhO3mv>J$)l#6D);_JnZlU;km<;J,$Rx8rJKz<eF$}cAs');
define('NONCE_SALT',       'ky8r:.!GHl6^}|7C#shNMaxL1NaHLB3B^gl9NqsCrikTrz&_G~=+{+zql`K0M8;a');

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
