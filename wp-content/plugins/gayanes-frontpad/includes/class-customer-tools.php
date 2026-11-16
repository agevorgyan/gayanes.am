<?php
/**
 * Customer Tools: Client Lookup (get_client) & Certificate Validation (get_certificate).
 *
 * Security:
 * - Nonce verification on all AJAX calls
 * - Per-IP rate limiting (anti-enumeration)
 * - Optional logged-in user requirement & Google reCAPTCHA verification
 *
 * @package Gayanes_Frontpad
 */

declare( strict_types=1 );

namespace Gayanes_Frontpad;

defined( 'ABSPATH' ) || exit;

/**
 * Customer_Tools class.
 */
class Customer_Tools {

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		$settings = API_Client::get_settings();

		// Public assets & shortcodes.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_shortcode( 'frontpad_client_lookup', array( __CLASS__, 'shortcode_client_lookup' ) );
		add_shortcode( 'frontpad_certificate_check', array( __CLASS__, 'shortcode_certificate_check' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_frontpad_get_client', array( __CLASS__, 'ajax_get_client' ) );
		add_action( 'wp_ajax_nopriv_frontpad_get_client', array( __CLASS__, 'ajax_get_client' ) );

		add_action( 'wp_ajax_frontpad_check_certificate', array( __CLASS__, 'ajax_check_certificate' ) );
		add_action( 'wp_ajax_nopriv_frontpad_check_certificate', array( __CLASS__, 'ajax_check_certificate' ) );

		add_action( 'wp_ajax_frontpad_remove_certificate', array( __CLASS__, 'ajax_remove_certificate' ) );
		add_action( 'wp_ajax_nopriv_frontpad_remove_certificate', array( __CLASS__, 'ajax_remove_certificate' ) );

		add_action( 'wp_ajax_frontpad_apply_score', array( __CLASS__, 'ajax_apply_score' ) );
		add_action( 'wp_ajax_nopriv_frontpad_apply_score', array( __CLASS__, 'ajax_apply_score' ) );

		// Checkout UI & Cart fee/discount hooks.
		if ( '1' === (string) ( $settings['enable_certificate'] ?? '1' ) ) {
			add_action( 'woocommerce_review_order_before_payment', array( __CLASS__, 'render_checkout_certificate_box' ) );
		}
		if ( '1' === (string) ( $settings['enable_client_lookup'] ?? '1' ) ) {
			add_action( 'woocommerce_before_checkout_billing_form', array( __CLASS__, 'render_checkout_client_box' ) );
		}

		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'apply_session_discounts_to_cart' ), 20 );
	}

	/**
	 * Enqueue frontend JS & CSS on Cart, Checkout, Account, or pages with shortcodes.
	 */
	public static function enqueue_assets(): void {
		$settings = API_Client::get_settings();
		if ( '1' !== (string) $settings['enabled'] ) {
			return;
		}

		wp_register_style(
			'gayanes-frontpad-public',
			GAYANES_FRONTPAD_URL . 'assets/css/frontpad-public.css',
			array(),
			GAYANES_FRONTPAD_VERSION
		);
		wp_enqueue_style( 'gayanes-frontpad-public' );

		wp_register_script(
			'gayanes-frontpad-public',
			GAYANES_FRONTPAD_URL . 'assets/js/frontpad-public.js',
			array( 'jquery' ),
			GAYANES_FRONTPAD_VERSION,
			true
		);

		wp_localize_script(
			'gayanes-frontpad-public',
			'gayanesFrontpad',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'frontpad_public_nonce' ),
				'recaptchaSiteKey' => trim( (string) ( $settings['recaptcha_site_key'] ?? '' ) ),
				'i18n'            => array(
					'loading'        => __( 'Checking...', 'gayanes-frontpad' ),
					'error'          => __( 'Request failed. Please try again.', 'gayanes-frontpad' ),
					'applied'        => __( 'Applied!', 'gayanes-frontpad' ),
					'points'         => __( 'Bonus points', 'gayanes-frontpad' ),
					'discount'       => __( 'Personal discount', 'gayanes-frontpad' ),
					'card'           => __( 'Card', 'gayanes-frontpad' ),
					'certProduct'    => __( 'Gift product', 'gayanes-frontpad' ),
					'certPercent'    => __( 'Discount', 'gayanes-frontpad' ),
					'certAmount'     => __( 'Certificate amount', 'gayanes-frontpad' ),
				),
			)
		);
		wp_enqueue_script( 'gayanes-frontpad-public' );
	}

	/**
	 * Shortcode: `[frontpad_client_lookup]`.
	 */
	public static function shortcode_client_lookup(): string {
		$settings = API_Client::get_settings();
		if ( '1' !== (string) $settings['enabled'] || '1' !== (string) $settings['enable_client_lookup'] ) {
			return '';
		}
		if ( '1' === (string) $settings['require_login_lookup'] && ! is_user_logged_in() ) {
			return '<p class="frontpad-notice">' . esc_html__( 'Please log in to check your Frontpad loyalty profile.', 'gayanes-frontpad' ) . '</p>';
		}

		ob_start();
		?>
		<div class="frontpad-widget frontpad-client-widget">
			<h4><?php esc_html_e( 'Loyalty Card & Client Lookup', 'gayanes-frontpad' ); ?></h4>
			<div class="frontpad-input-row">
				<input type="tel" class="frontpad-phone-input" placeholder="+374 __ ___ ___" autocomplete="tel" />
				<button type="button" class="button frontpad-client-lookup-btn"><?php esc_html_e( 'Check', 'gayanes-frontpad' ); ?></button>
			</div>
			<div class="frontpad-client-result" aria-live="polite"></div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Shortcode: `[frontpad_certificate_check]`.
	 */
	public static function shortcode_certificate_check(): string {
		$settings = API_Client::get_settings();
		if ( '1' !== (string) $settings['enabled'] || '1' !== (string) $settings['enable_certificate'] ) {
			return '';
		}

		ob_start();
		?>
		<div class="frontpad-widget frontpad-cert-widget">
			<h4><?php esc_html_e( 'Gift Certificate', 'gayanes-frontpad' ); ?></h4>
			<div class="frontpad-input-row">
				<input type="text" class="frontpad-cert-input" placeholder="<?php esc_attr_e( 'Certificate number', 'gayanes-frontpad' ); ?>" />
				<button type="button" class="button frontpad-cert-check-btn"><?php esc_html_e( 'Apply', 'gayanes-frontpad' ); ?></button>
			</div>
			<div class="frontpad-cert-result" aria-live="polite"></div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render compact client lookup box on Checkout.
	 */
	public static function render_checkout_client_box(): void {
		if ( ! API_Client::is_configured() ) {
			return;
		}
		$settings = API_Client::get_settings();
		if ( '1' === (string) $settings['require_login_lookup'] && ! is_user_logged_in() ) {
			return;
		}
		echo '<div class="frontpad-checkout-client">';
		echo '<p class="frontpad-checkout-hint">' . esc_html__( 'Have a Frontpad loyalty card? Enter your phone number to load your address & bonus points:', 'gayanes-frontpad' ) . '</p>';
		echo '<div class="frontpad-input-row">';
		echo '<input type="tel" class="frontpad-phone-input" placeholder="+374 __ ___ ___" />';
		echo '<button type="button" class="button frontpad-client-lookup-btn">' . esc_html__( 'Find Profile', 'gayanes-frontpad' ) . '</button>';
		echo '</div>';
		echo '<div class="frontpad-client-result" aria-live="polite"></div>';
		echo '</div>';
	}

	/**
	 * Render Certificate box on Checkout before payment methods.
	 */
	public static function render_checkout_certificate_box(): void {
		if ( ! API_Client::is_configured() ) {
			return;
		}
		$active_cert = ( function_exists( 'WC' ) && WC()->session ) ? WC()->session->get( 'frontpad_certificate' ) : null;
		echo '<div class="frontpad-checkout-cert">';
		echo '<label for="frontpad_cert_input"><strong>' . esc_html__( 'Frontpad Certificate', 'gayanes-frontpad' ) . '</strong></label>';
		echo '<div class="frontpad-input-row">';
		echo '<input type="text" id="frontpad_cert_input" class="frontpad-cert-input" value="' . esc_attr( is_array( $active_cert ) ? (string) ( $active_cert['code'] ?? '' ) : '' ) . '" placeholder="' . esc_attr__( 'Enter certificate code', 'gayanes-frontpad' ) . '" />';
		echo '<button type="button" class="button frontpad-cert-check-btn">' . esc_html__( 'Apply', 'gayanes-frontpad' ) . '</button>';
		if ( is_array( $active_cert ) ) {
			echo '<button type="button" class="button frontpad-cert-remove-btn">' . esc_html__( 'Remove', 'gayanes-frontpad' ) . '</button>';
		}
		echo '</div>';
		echo '<div class="frontpad-cert-result" aria-live="polite"></div>';
		echo '</div>';
	}

	/**
	 * AJAX: `get_client` lookup.
	 */
	public static function ajax_get_client(): void {
		check_ajax_referer( 'frontpad_public_nonce', 'nonce' );

		$settings = API_Client::get_settings();
		if ( '1' !== (string) $settings['enabled'] || '1' !== (string) $settings['enable_client_lookup'] ) {
			wp_send_json_error( array( 'message' => __( 'Client lookup is disabled.', 'gayanes-frontpad' ) ) );
		}
		if ( '1' === (string) $settings['require_login_lookup'] && ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Authentication required.', 'gayanes-frontpad' ) ) );
		}

		$rate = API_Client::check_ip_rate_limit( 'get_client', 5 );
		if ( is_wp_error( $rate ) ) {
			wp_send_json_error( array( 'message' => $rate->get_error_message() ) );
		}

		$recaptcha = API_Client::verify_recaptcha( sanitize_text_field( wp_unslash( $_POST['recaptcha_token'] ?? '' ) ) );
		if ( is_wp_error( $recaptcha ) ) {
			wp_send_json_error( array( 'message' => $recaptcha->get_error_message() ) );
		}

		$raw_phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$phone     = Orders::normalize_phone( $raw_phone, (string) ( $settings['phone_prefix'] ?? '' ) );
		if ( strlen( preg_replace( '/\D/', '', $phone ) ?? '' ) < 6 ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid phone number.', 'gayanes-frontpad' ) ) );
		}

		$response = API_Client::request( 'get_client', array( 'client_phone' => $phone ) );
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		if ( 'success' !== ( $response['result'] ?? '' ) ) {
			$err = (string) ( $response['error'] ?? 'unknown' );
			wp_send_json_error( array( 'message' => API_Client::format_error( $err ) ) );
		}

		$client = array(
			'name'   => (string) ( $response['name'] ?? '' ),
			'street' => (string) ( $response['street'] ?? '' ),
			'home'   => (string) ( $response['home'] ?? '' ),
			'pod'    => (string) ( $response['pod'] ?? '' ),
			'et'     => (string) ( $response['et'] ?? '' ),
			'apart'  => (string) ( $response['apart'] ?? '' ),
			'mail'   => (string) ( $response['mail'] ?? '' ),
			'descr'  => (string) ( $response['descr'] ?? '' ),
			'card'   => (string) ( $response['card'] ?? '' ),
			'sale'   => (string) ( $response['sale'] ?? '0' ),
			'score'  => (string) ( $response['score'] ?? '0' ),
		);

		if ( function_exists( 'WC' ) && WC()->session && '' !== $client['card'] ) {
			WC()->session->set( 'frontpad_client_card', $client['card'] );
			WC()->session->set( 'frontpad_max_score', (float) $client['score'] );
		}

		wp_send_json_success( $client );
	}

	/**
	 * AJAX: `get_certificate` validation and session application.
	 */
	public static function ajax_check_certificate(): void {
		check_ajax_referer( 'frontpad_public_nonce', 'nonce' );

		$settings = API_Client::get_settings();
		if ( '1' !== (string) $settings['enabled'] || '1' !== (string) $settings['enable_certificate'] ) {
			wp_send_json_error( array( 'message' => __( 'Certificate check is disabled.', 'gayanes-frontpad' ) ) );
		}

		$rate = API_Client::check_ip_rate_limit( 'get_certificate', 5 );
		if ( is_wp_error( $rate ) ) {
			wp_send_json_error( array( 'message' => $rate->get_error_message() ) );
		}

		$recaptcha = API_Client::verify_recaptcha( sanitize_text_field( wp_unslash( $_POST['recaptcha_token'] ?? '' ) ) );
		if ( is_wp_error( $recaptcha ) ) {
			wp_send_json_error( array( 'message' => $recaptcha->get_error_message() ) );
		}

		$code = sanitize_text_field( wp_unslash( $_POST['certificate'] ?? '' ) );
		if ( '' === $code ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a certificate number.', 'gayanes-frontpad' ) ) );
		}

		$response = API_Client::request( 'get_certificate', array( 'certificate' => $code ) );
		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => $response->get_error_message() ) );
		}

		if ( 'success' !== ( $response['result'] ?? '' ) ) {
			$err = (string) ( $response['error'] ?? 'unknown' );
			wp_send_json_error( array( 'message' => API_Client::format_error( $err ) ) );
		}

		$parsed = self::parse_certificate_response( $code, $response );
		if ( function_exists( 'WC' ) && WC()->session ) {
			WC()->session->set( 'frontpad_certificate', $parsed );
		}

		wp_send_json_success( $parsed );
	}

	/**
	 * Parse all 3 Frontpad certificate response types:
	 * 1. Product certificate (`product_id`, `name`, `price`)
	 * 2. Percentage discount certificate (`sale`)
	 * 3. Fixed amount discount certificate (`amount`)
	 *
	 * @param string               $code Certificate code.
	 * @param array<string, mixed> $response API response.
	 * @return array<string, mixed>
	 */
	public static function parse_certificate_response( string $code, array $response ): array {
		if ( ! empty( $response['product_id'] ) ) {
			return array(
				'type'       => 'product',
				'code'       => $code,
				'product_id' => (string) $response['product_id'],
				'name'       => (string) ( $response['name'] ?? '' ),
				'price'      => (float) ( $response['price'] ?? 0 ),
			);
		}
		if ( isset( $response['sale'] ) && is_numeric( $response['sale'] ) ) {
			return array(
				'type' => 'sale',
				'code' => $code,
				'sale' => (float) $response['sale'],
			);
		}
		return array(
			'type'   => 'amount',
			'code'   => $code,
			'amount' => (float) ( $response['amount'] ?? 0 ),
		);
	}

	/**
	 * AJAX: Remove applied certificate from session.
	 */
	public static function ajax_remove_certificate(): void {
		check_ajax_referer( 'frontpad_public_nonce', 'nonce' );
		if ( function_exists( 'WC' ) && WC()->session ) {
			WC()->session->set( 'frontpad_certificate', null );
		}
		wp_send_json_success();
	}

	/**
	 * AJAX: Apply bonus points (`score`) to current WooCommerce checkout session.
	 */
	public static function ajax_apply_score(): void {
		check_ajax_referer( 'frontpad_public_nonce', 'nonce' );
		$requested = max( 0.0, (float) ( $_POST['score'] ?? 0 ) );
		if ( function_exists( 'WC' ) && WC()->session ) {
			$max = (float) WC()->session->get( 'frontpad_max_score', 0 );
			$use = min( $requested, $max );
			WC()->session->set( 'frontpad_use_score', $use > 0 ? $use : null );
			wp_send_json_success( array( 'applied_score' => $use ) );
		}
		wp_send_json_error();
	}

	/**
	 * Apply active Frontpad certificate / bonus points as negative cart fee in WooCommerce.
	 *
	 * @param \WC_Cart $cart WooCommerce cart.
	 */
	public static function apply_session_discounts_to_cart( \WC_Cart $cart ): void {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return;
		}

		$subtotal = (float) $cart->get_subtotal();
		if ( $subtotal <= 0 ) {
			return;
		}

		$cert = WC()->session->get( 'frontpad_certificate' );
		if ( is_array( $cert ) ) {
			$discount = 0.0;
			if ( 'sale' === ( $cert['type'] ?? '' ) ) {
				$discount = $subtotal * ( min( 100.0, max( 0.0, (float) $cert['sale'] ) ) / 100.0 );
			} elseif ( 'amount' === ( $cert['type'] ?? '' ) ) {
				$discount = min( $subtotal, (float) ( $cert['amount'] ?? 0 ) );
			}
			if ( $discount > 0 ) {
				$cart->add_fee(
					sprintf( __( 'Frontpad Certificate (%s)', 'gayanes-frontpad' ), (string) $cert['code'] ),
					-round( $discount, 2 )
				);
			}
		}

		$score = (float) WC()->session->get( 'frontpad_use_score', 0 );
		if ( $score > 0 ) {
			$deduct = min( $subtotal, $score );
			$cart->add_fee( __( 'Frontpad Bonus Points', 'gayanes-frontpad' ), -round( $deduct, 2 ) );
		}
	}
}
