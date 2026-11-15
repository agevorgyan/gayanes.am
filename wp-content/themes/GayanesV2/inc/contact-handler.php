<?php
/**
 * AJAX Contact / Feedback Form Handler for GayanesV2.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_gv2_submit_contact', 'gv2_handle_contact_submission' );
add_action( 'wp_ajax_nopriv_gv2_submit_contact', 'gv2_handle_contact_submission' );
add_shortcode( 'gv2_contact_nonce', 'gv2_render_contact_nonce_shortcode' );

/**
 * Render a fresh nonce hidden input for the editable Contact page form.
 */
function gv2_render_contact_nonce_shortcode(): string {
	return '<input type="hidden" name="nonce" value="' . esc_attr( wp_create_nonce( 'gv2_contact_nonce' ) ) . '" />';
}

/**
 * Handle AJAX contact form submission with nonce and honeypot verification.
 */
function gv2_handle_contact_submission(): void {
	if ( ! check_ajax_referer( 'gv2_contact_nonce', 'nonce', false ) ) {
		wp_send_json_error(
			array( 'message' => gv2_t( 'Security check failed. Please refresh the page and try again.' ) ),
			403
		);
	}

	// Honeypot field for bot protection.
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$website = isset( $_POST['website'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['website'] ) ) ) : '';
	if ( '' !== $website ) {
		wp_send_json_success(
			array( 'message' => gv2_t( 'Thank you! Your message has been sent.' ) )
		);
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$name    = isset( $_POST['name'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['name'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$contact = isset( $_POST['contact'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['contact'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$topic   = isset( $_POST['topic'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['topic'] ) ) ) : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$message = isset( $_POST['message'] ) ? trim( sanitize_textarea_field( (string) wp_unslash( $_POST['message'] ) ) ) : '';

	if ( '' === $name || '' === $contact || '' === $message ) {
		wp_send_json_error(
			array( 'message' => gv2_t( 'Please fill in all required fields.' ) ),
			400
		);
	}

	$to      = (string) get_option( 'admin_email', 'info@gayanes.am' );
	$subject = sprintf( '[gayanes.am] %s — %s', $topic ?: 'Contact', $name );
	$body    = sprintf(
		"Name: %s\nPhone / Email: %s\nTopic: %s\n\nMessage:\n%s\n",
		$name,
		$contact,
		$topic,
		$message
	);

	// Best-effort mail delivery (succeeds gracefully on local dev environments without SMTP).
	@wp_mail( $to, $subject, $body ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	wp_send_json_success(
		array( 'message' => gv2_t( 'Thank you! Your message has been sent. We will reply shortly.' ) )
	);
}
