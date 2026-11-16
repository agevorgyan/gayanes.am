<?php
/**
 * Frontpad Webhook Receiver (`change_status`).
 *
 * Endpoint: POST /wp-json/frontpad/v1/webhook
 * Payload:  { "action": "change_status", "order_id": 12345678, "status": 3, "datetime": "2020-08-21 12:03:00" }
 *
 * @package Gayanes_Frontpad
 */

declare( strict_types=1 );

namespace Gayanes_Frontpad;

defined( 'ABSPATH' ) || exit;

/**
 * Webhook class.
 */
class Webhook {

	public const REST_NAMESPACE = 'frontpad/v1';
	public const REST_ROUTE     = '/webhook';

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register REST API route.
	 */
	public static function register_routes(): void {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE,
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'handle_request' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
			)
		);
	}

	/**
	 * Build the full webhook URL (with optional security token if configured).
	 */
	public static function get_webhook_url(): string {
		$url      = rest_url( self::REST_NAMESPACE . self::REST_ROUTE );
		$settings = API_Client::get_settings();
		$token    = trim( (string) ( $settings['webhook_token'] ?? '' ) );
		if ( '' !== $token ) {
			$url = add_query_arg( 'token', $token, $url );
		}
		return $url;
	}

	/**
	 * Verify optional webhook token query parameter.
	 */
	public static function check_permission( \WP_REST_Request $request ): bool {
		$settings = API_Client::get_settings();
		$expected = trim( (string) ( $settings['webhook_token'] ?? '' ) );
		if ( '' === $expected ) {
			return true;
		}
		$provided = (string) $request->get_param( 'token' );
		return '' !== $provided && hash_equals( $expected, $provided );
	}

	/**
	 * Handle incoming Frontpad webhook POST.
	 */
	public static function handle_request( \WP_REST_Request $request ): \WP_REST_Response {
		$raw  = $request->get_body();
		$data = json_decode( $raw, true );

		if ( ! is_array( $data ) ) {
			$data = $request->get_json_params();
		}

		$result = self::process_payload( is_array( $data ) ? $data : array() );
		if ( is_wp_error( $result ) ) {
			$status_code = 'invalid_payload' === $result->get_error_code() ? 400 : 404;
			return new \WP_REST_Response(
				array(
					'result'  => 'error',
					'error'   => $result->get_error_code(),
					'message' => $result->get_error_message(),
				),
				$status_code
			);
		}

		return new \WP_REST_Response( $result, 200 );
	}

	/**
	 * Process decoded webhook payload (testable without WP_REST_Request).
	 *
	 * @param array<string, mixed> $data Decoded JSON payload.
	 * @return array<string, mixed>|\WP_Error
	 */
	public static function process_payload( array $data ) {
		$action   = sanitize_text_field( (string) ( $data['action'] ?? '' ) );
		$order_id = sanitize_text_field( (string) ( $data['order_id'] ?? '' ) );
		$status   = sanitize_text_field( (string) ( $data['status'] ?? '' ) );
		$datetime = sanitize_text_field( (string) ( $data['datetime'] ?? '' ) );

		if ( 'change_status' !== $action || '' === $order_id || '' === $status ) {
			return new \WP_Error( 'invalid_payload', 'Missing or invalid action, order_id, or status.' );
		}

		if ( ! function_exists( 'wc_get_orders' ) ) {
			return new \WP_Error( 'woocommerce_inactive', 'WooCommerce is not active.' );
		}

		$orders = wc_get_orders(
			array(
				'limit'      => 1,
				'meta_key'   => Orders::META_ORDER_ID,
				'meta_value' => $order_id,
			)
		);

		if ( empty( $orders ) ) {
			API_Client::log( 'webhook', 'error', sprintf( 'Order not found for Frontpad order_id=%s', $order_id ) );
			return new \WP_Error( 'order_not_found', 'WooCommerce order not found for Frontpad order_id.' );
		}

		/** @var \WC_Order $wc_order */
		$wc_order = $orders[0];
		$wc_order->update_meta_data( '_frontpad_last_status', $status );
		$wc_order->update_meta_data( '_frontpad_last_status_time', $datetime );

		$settings   = API_Client::get_settings();
		$status_map = is_array( $settings['status_map'] ?? null ) ? $settings['status_map'] : array();
		$wc_status  = isset( $status_map[ $status ] ) ? sanitize_key( (string) $status_map[ $status ] ) : '';

		$note = sprintf( 'Frontpad webhook: status=%s at %s', $status, $datetime );
		if ( '' !== $wc_status ) {
			$wc_order->update_status( $wc_status, $note );
		} else {
			$wc_order->add_order_note( $note );
			$wc_order->save();
		}

		API_Client::log( 'webhook', 'success', sprintf( 'WC #%d updated to Frontpad status %s', $wc_order->get_id(), $status ) );

		return array(
			'result'      => 'success',
			'wc_order_id' => $wc_order->get_id(),
			'fp_status'   => $status,
			'wc_status'   => $wc_status,
		);
	}
}
