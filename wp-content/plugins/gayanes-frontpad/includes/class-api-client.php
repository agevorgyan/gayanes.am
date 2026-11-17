<?php
/**
 * Frontpad HTTP API Client with rate limiting, error mapping, and logging.
 *
 * API Base: https://app.frontpad.ru/api/index.php?{METHOD}
 * Limits:   30 req/min, max 2 req/sec; get_products: 1 req/hour.
 * Encoding: UTF-8, POST (application/x-www-form-urlencoded).
 *
 * @package Gayanes_Frontpad
 */

declare( strict_types=1 );

namespace Gayanes_Frontpad;

defined( 'ABSPATH' ) || exit;

/**
 * API_Client class.
 */
class API_Client {

	public const API_BASE_URL       = 'https://app.frontpad.ru/api/index.php';
	public const OPTION_SETTINGS    = 'gayanes_frontpad_settings';
	public const OPTION_LOGS        = 'gayanes_frontpad_api_logs';
	public const TRANSIENT_RATE_MIN = 'gayanes_fp_rate_min';
	public const TRANSIENT_RATE_SEC = 'gayanes_fp_rate_sec';
	public const TRANSIENT_PROD_LOCK = 'gayanes_fp_get_products_lock';

	/**
	 * Supported Frontpad API methods.
	 */
	public const METHODS = array(
		'new_order',
		'get_client',
		'get_certificate',
		'get_products',
		'get_stops',
	);

	/**
	 * Retrieve plugin settings merged with defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_settings(): array {
		$defaults = array(
			'enabled'                 => '0',
			'secret'                  => '',
			'webhook_token'           => '',
			'order_trigger'           => 'checkout', // 'checkout' | 'processing' | 'on-hold' | 'manual'
			'channel'                 => '',
			'affiliate'               => '',
			'point'                   => '',
			'default_tags'            => '',
			'hook_statuses'           => '',
			'send_product_price'      => '0',
			'payment_map'             => array(),
			'status_map'              => array(),
			'auto_sync_products'      => '0',
			'sync_product_names'      => '0',
			'sync_product_prices'     => '1',
			'create_missing_products' => '0',
			'auto_sync_stops'         => '0',
			'stops_interval'          => 'frontpad_15min',
			'recaptcha_secret'        => '',
			'recaptcha_site_key'      => '',
			'phone_prefix'            => '',
			'debug_log'               => '1',
		);

		$saved = get_option( self::OPTION_SETTINGS, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
	}

	/**
	 * Check if integration is enabled and secret is configured.
	 */
	public static function is_configured(): bool {
		$settings = self::get_settings();
		return '1' === (string) $settings['enabled'] && '' !== trim( (string) $settings['secret'] );
	}

	/**
	 * Send a POST request to Frontpad API.
	 *
	 * @param string               $method API method name.
	 * @param array<string, mixed> $params Request parameters (without secret).
	 * @param bool                 $bypass_products_lock Allow bypassing local 1hr get_products lock (use carefully).
	 * @return array<string, mixed>|\WP_Error Decoded JSON array on API response, or WP_Error on transport/local guard failure.
	 */
	public static function request( string $method, array $params = array(), bool $bypass_products_lock = false ) {
		if ( ! in_array( $method, self::METHODS, true ) ) {
			return new \WP_Error( 'invalid_method', sprintf( 'Unsupported Frontpad API method: %s', $method ) );
		}

		$settings = self::get_settings();
		$secret   = trim( (string) ( $params['secret'] ?? $settings['secret'] ?? '' ) );
		if ( '' === $secret ) {
			return new \WP_Error( 'missing_secret', __( 'Frontpad API secret is not configured.', 'gayanes-frontpad' ) );
		}

		// Enforce 1 request/hour rule for get_products.
		if ( 'get_products' === $method && ! $bypass_products_lock ) {
			$locked_until = (int) get_transient( self::TRANSIENT_PROD_LOCK );
			if ( $locked_until > time() ) {
				$mins = (int) ceil( ( $locked_until - time() ) / 60 );
				return new \WP_Error(
					'get_products_rate_limit',
					sprintf(
						/* translators: %d: minutes remaining */
						__( 'Frontpad limits get_products to 1 request per hour. Please wait %d minute(s).', 'gayanes-frontpad' ),
						$mins
					)
				);
			}
		}

		// Enforce global rate limits: max 2 req/sec and 30 req/min.
		$rate_check = self::check_and_increment_rate_limit();
		if ( is_wp_error( $rate_check ) ) {
			return $rate_check;
		}

		$params['secret'] = $secret;
		$body             = self::build_post_body( $params );
		$url              = self::API_BASE_URL . '?' . $method;

		$response = wp_remote_post(
			$url,
			array(
				'timeout'     => 15,
				'redirection' => 2,
				'httpversion' => '1.1',
				'headers'     => array(
					'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
				),
				'body'        => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			self::log( $method, 'error', $response->get_error_message() );
			return $response;
		}

		$code     = (int) wp_remote_retrieve_response_code( $response );
		$raw_body = (string) wp_remote_retrieve_body( $response );
		$decoded  = json_decode( $raw_body, true );

		if ( $code < 200 || $code >= 300 || ! is_array( $decoded ) ) {
			$msg = sprintf( 'HTTP %d: %s', $code, substr( $raw_body, 0, 300 ) );
			self::log( $method, 'error', $msg );
			return new \WP_Error( 'http_error', $msg );
		}

		if ( 'get_products' === $method ) {
			set_transient( self::TRANSIENT_PROD_LOCK, time() + HOUR_IN_SECONDS, HOUR_IN_SECONDS );
		}

		$result_status = (string) ( $decoded['result'] ?? 'unknown' );
		$error_code    = (string) ( $decoded['error'] ?? '' );
		self::log(
			$method,
			$result_status,
			'' !== $error_code ? $error_code : (string) wp_json_encode( array_keys( $decoded ) )
		);

		return $decoded;
	}

	/**
	 * Build application/x-www-form-urlencoded body matching Frontpad's PHP specification.
	 *
	 * Supports nested arrays like product[0]=10000, product_kol[0]=1, product_mod[1]=0,
	 * tags[0]=1, hook_status[0]=3, etc.
	 *
	 * @param array<string, mixed> $params Request parameters.
	 */
	public static function build_post_body( array $params ): string {
		$pairs = array();
		foreach ( $params as $key => $value ) {
			if ( null === $value || '' === $value ) {
				continue;
			}
			if ( is_array( $value ) ) {
				foreach ( $value as $sub_key => $sub_val ) {
					if ( null === $sub_val || '' === $sub_val ) {
						continue;
					}
					$pairs[] = rawurlencode( (string) $key ) . '[' . rawurlencode( (string) $sub_key ) . ']=' . rawurlencode( (string) $sub_val );
				}
			} else {
				$pairs[] = rawurlencode( (string) $key ) . '=' . rawurlencode( (string) $value );
			}
		}
		return implode( '&', $pairs );
	}

	/**
	 * Translate Frontpad error code to a human-readable message.
	 *
	 * @param string $error_code Error code from API response.
	 */
	public static function format_error( string $error_code ): string {
		$messages = array(
			'invalid_secret'       => __( 'Invalid Frontpad API secret.', 'gayanes-frontpad' ),
			'requests_limit'       => __( 'Frontpad API rate limit exceeded (max 30/min, 2/sec).', 'gayanes-frontpad' ),
			'api_off'              => __( 'API is disabled in Frontpad general settings.', 'gayanes-frontpad' ),
			'invalid_plant'        => __( 'API is not available on your current Frontpad tariff plan.', 'gayanes-frontpad' ),
			'cash_close'           => __( 'Frontpad cash shift is closed.', 'gayanes-frontpad' ),
			'invalid_product_keys' => __( 'Invalid product SKUs array in order.', 'gayanes-frontpad' ),
			'invalid_certificate'  => __( 'Invalid or non-existent certificate number.', 'gayanes-frontpad' ),
			'invalid_client_phone' => __( 'Client not found for the specified phone number.', 'gayanes-frontpad' ),
			'invalid_products'     => __( 'No products with SKUs found to export from Frontpad.', 'gayanes-frontpad' ),
			'no_stops'             => __( 'No products are currently in the stop-list.', 'gayanes-frontpad' ),
		);

		return $messages[ $error_code ] ?? sprintf( __( 'Frontpad API error: %s', 'gayanes-frontpad' ), $error_code );
	}

	/**
	 * Check and increment global outbound rate limits (30/min, 2/sec).
	 *
	 * @return true|\WP_Error
	 */
	private static function check_and_increment_rate_limit() {
		$now = time();

		// Per-second window (max 2 req/sec).
		$sec_data = get_transient( self::TRANSIENT_RATE_SEC );
		if ( ! is_array( $sec_data ) || (int) ( $sec_data['ts'] ?? 0 ) !== $now ) {
			$sec_data = array( 'ts' => $now, 'count' => 0 );
		}
		if ( (int) $sec_data['count'] >= 2 ) {
			// Wait 1 second cleanly when called from backend/cron, or fail fast if needed.
			usleep( 600000 );
			$now      = time();
			$sec_data = array( 'ts' => $now, 'count' => 0 );
		}
		$sec_data['count'] = (int) $sec_data['count'] + 1;
		set_transient( self::TRANSIENT_RATE_SEC, $sec_data, 5 );

		// Per-minute window (max 30 req/min).
		$min_data = get_transient( self::TRANSIENT_RATE_MIN );
		if ( ! is_array( $min_data ) || ( $now - (int) ( $min_data['ts'] ?? 0 ) ) >= 60 ) {
			$min_data = array( 'ts' => $now, 'count' => 0 );
		}
		if ( (int) $min_data['count'] >= 30 ) {
			return new \WP_Error( 'local_rate_limit', __( 'Local Frontpad API rate limit reached (30 requests/minute). Please retry shortly.', 'gayanes-frontpad' ) );
		}
		$min_data['count'] = (int) $min_data['count'] + 1;
		set_transient( self::TRANSIENT_RATE_MIN, $min_data, 65 );

		return true;
	}

	/**
	 * Verify per-IP rate limit for public endpoints (anti-enumeration rule).
	 *
	 * @param string $bucket Action bucket name.
	 * @param int    $max_per_minute Maximum requests allowed per minute per IP.
	 * @return true|\WP_Error
	 */
	public static function check_ip_rate_limit( string $bucket, int $max_per_minute = 5 ) {
		$ip  = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' ) );
		$key = 'gayanes_fp_ip_' . md5( $bucket . '|' . $ip );
		$cnt = (int) get_transient( $key );

		if ( $cnt >= $max_per_minute ) {
			return new \WP_Error( 'too_many_requests', __( 'Too many requests. Please wait a minute before trying again.', 'gayanes-frontpad' ) );
		}

		set_transient( $key, $cnt + 1, MINUTE_IN_SECONDS );
		return true;
	}

	/**
	 * Verify optional Google reCAPTCHA v3/v2 token if secret is configured.
	 *
	 * @param string $token Token submitted from frontend.
	 * @return true|\WP_Error
	 */
	public static function verify_recaptcha( string $token ) {
		$settings = self::get_settings();
		$secret   = trim( (string) ( $settings['recaptcha_secret'] ?? '' ) );
		if ( '' === $secret ) {
			return true;
		}
		if ( '' === $token ) {
			return new \WP_Error( 'recaptcha_missing', __( 'Security verification (reCAPTCHA) is required.', 'gayanes-frontpad' ) );
		}

		$res = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => $secret,
					'response' => $token,
					'remoteip' => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
				),
			)
		);

		if ( is_wp_error( $res ) ) {
			return new \WP_Error( 'recaptcha_failed', __( 'Could not verify reCAPTCHA.', 'gayanes-frontpad' ) );
		}

		$data = json_decode( (string) wp_remote_retrieve_body( $res ), true );
		if ( empty( $data['success'] ) ) {
			return new \WP_Error( 'recaptcha_invalid', __( 'reCAPTCHA verification failed.', 'gayanes-frontpad' ) );
		}

		return true;
	}

	/**
	 * Append a log entry to WC logger and recent logs option (max 50 entries).
	 */
	public static function log( string $method, string $status, string $message ): void {
		$settings = self::get_settings();
		if ( '1' !== (string) ( $settings['debug_log'] ?? '1' ) && 'error' !== $status ) {
			return;
		}

		if ( function_exists( 'wc_get_logger' ) ) {
			$logger = wc_get_logger();
			$level  = ( 'error' === $status ) ? 'error' : 'info';
			$logger->log( $level, sprintf( '[%s] (%s) %s', $method, $status, $message ), array( 'source' => 'gayanes-frontpad' ) );
		}

		if ( function_exists( 'get_option' ) && function_exists( 'update_option' ) ) {
			$logs = get_option( self::OPTION_LOGS, array() );
			if ( ! is_array( $logs ) ) {
				$logs = array();
			}
			array_unshift(
				$logs,
				array(
					'time'    => gmdate( 'Y-m-d H:i:s' ),
					'method'  => $method,
					'status'  => $status,
					'message' => mb_substr( $message, 0, 250 ),
				)
			);
			$logs = array_slice( $logs, 0, 50 );
			update_option( self::OPTION_LOGS, $logs, false );
		}
	}
}
