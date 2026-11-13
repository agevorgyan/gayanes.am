<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'gayanes_local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',          ']Vdy4B>cYtLS}q$!~f!DeBo7X2oj?0q(LQG&EY|*;UsK1v)h1Pm?YEiyw3%7@r?5' );
define( 'SECURE_AUTH_KEY',   '}T1c-.(Fh2Mdi*CX+4bw8+,[gJcp)b&GE+)&(V)%.B6lV2,g`c}Qu2:QB(ZDG9.t' );
define( 'LOGGED_IN_KEY',     '4o8&&gIJV.fC%X?1B{H,2)qxz$Eo*b+CJT?V[?F ~cg8,| !-3EE#D!&gfs-CT62' );
define( 'NONCE_KEY',         '|^($5jzhH8@:3=>nyo=Lx%#G2YmeXJV4?1*]B3sU{f3Wy) #74X_>+uvcU~up&T$' );
define( 'AUTH_SALT',         '-Fc=qI]_Uq<3W[/n1Z8La+s:V`vNzo:n*$Y)P/x+T7C6BE@by7`q%w$Z4aVpxOpY' );
define( 'SECURE_AUTH_SALT',  'Tu>/8_~>Etzsg+S|>p@dL#hn6c^-Af>y5ddj-S@y}h+zQ{SY0]I4G{@[|rtfvaRP' );
define( 'LOGGED_IN_SALT',    '8Z7B!58^d `fy+hK{;5 >Fdu]>uAh]7aIdB+ozcmg~fcm>ioANIGj>9?j/XZ 9UF' );
define( 'NONCE_SALT',        'q>[QQ*fiu7=:=Gs|L?zgGsZ Ti9AU~ VqZ#wl}X@@[]O#Lj&LhyT79@Jx,ElmE{C' );
define( 'WP_CACHE_KEY_SALT', '$k)Ul8m3Io:z5E75:Hq?sDka9*MA3pp9|5PtgR+d!}P!gBB0^*^b6!M(ev{-~f=l' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
