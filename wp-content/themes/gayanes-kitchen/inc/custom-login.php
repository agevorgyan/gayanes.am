<?php
/**
 * Custom Admin Login URL (/panel) for GayanesV2.
 *
 * Hides /wp-login.php and unauthenticated /wp-admin/ access,
 * routing WordPress core login, logout, and password reset through /panel.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

define( 'GV2_LOGIN_SLUG', 'panel' );

/**
 * Rewrite wp-login.php URLs to /panel.
 *
 * @param string $url URL to filter.
 * @return string
 */
function gv2_rewrite_login_url( string $url ): string {
	if ( ! str_contains( $url, 'wp-login.php' ) ) {
		return $url;
	}

	// Preserve query string (e.g. ?action=logout&_wpnonce=...).
	return str_replace( 'wp-login.php', GV2_LOGIN_SLUG, $url );
}

add_filter( 'site_url', 'gv2_rewrite_login_url', 10, 1 );
add_filter( 'network_site_url', 'gv2_rewrite_login_url', 10, 1 );
add_filter( 'wp_redirect', 'gv2_rewrite_login_url', 10, 1 );

/**
 * Block direct access to /wp-login.php and unauthenticated /wp-admin/ requests.
 */
add_action(
	'init',
	static function (): void {
		if ( ( defined( 'WP_CLI' ) && WP_CLI ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || wp_doing_ajax() ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path        = (string) wp_parse_url( $request_uri, PHP_URL_PATH );

		// 1. Block direct /wp-login.php requests (when not internally rewritten from /panel).
		if ( preg_match( '#/wp-login\.php$#i', $path ) ) {
			status_header( 404 );
			nocache_headers();
			$template_404 = get_404_template();
			if ( $template_404 && file_exists( $template_404 ) ) {
				include $template_404;
			} else {
				wp_die( esc_html__( 'Page not found.', 'gayanes-kitchen' ), '', array( 'response' => 404 ) );
			}
			exit;
		}

		// 2. Redirect unauthenticated /wp-admin/ access to homepage instead of exposing /panel.
		if ( is_admin() && ! is_user_logged_in() ) {
			$script = isset( $_SERVER['PHP_SELF'] ) ? basename( (string) $_SERVER['PHP_SELF'] ) : '';
			if ( ! in_array( $script, array( 'admin-ajax.php', 'admin-post.php', 'async-upload.php' ), true ) ) {
				wp_safe_redirect( home_url( '/' ), 302 );
				exit;
			}
		}
	},
	1
);
