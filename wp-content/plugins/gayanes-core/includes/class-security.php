<?php
/**
 * Security hardening & sanitization helpers.
 *
 * @package Gayanes_Core
 */

namespace Gayanes_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Security class.
 */
class Security {

	/**
	 * Initialize security hooks.
	 */
	public static function init(): void {
		add_action( 'send_headers', array( __CLASS__, 'add_security_headers' ) );
		add_filter( 'xmlrpc_enabled', '__return_false' );
		remove_action( 'wp_head', 'wp_generator' );
	}

	/**
	 * Send safe HTTP security headers without breaking payment gateway redirects/iframes.
	 */
	public static function add_security_headers(): void {
		if ( headers_sent() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
	}

	/**
	 * Verify AJAX nonce safely.
	 *
	 * @param string $action Nonce action name.
	 * @param string $field  Request field name.
	 */
	public static function verify_ajax_nonce( string $action = 'gayanes_cart_nonce', string $field = 'nonce' ): void {
		$nonce = isset( $_REQUEST[ $field ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $field ] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_send_json_error(
				array(
					'code'    => 'invalid_nonce',
					'message' => __( 'Security check failed. Please refresh the page and try again.', 'gayanes-core' ),
				),
				403
			);
		}
	}
}

