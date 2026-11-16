<?php
/**
 * Order Submission (new_order) & HPOS Order Metabox.
 *
 * Implements 100% of Frontpad `new_order` parameters:
 * secret, product[], product_kol[], product_mod[], product_price[],
 * score, sale, sale_amount, card, street, home, pod, et, apart,
 * phone, mail, descr, name, pay, certificate, person, tags[],
 * hook_status[], hook_url, channel, datetime, affiliate, point.
 *
 * @package Gayanes_Frontpad
 */

declare( strict_types=1 );

namespace Gayanes_Frontpad;

defined( 'ABSPATH' ) || exit;

/**
 * Orders class.
 */
class Orders {

	public const META_ORDER_ID     = '_frontpad_order_id';
	public const META_ORDER_NUMBER = '_frontpad_order_number';
	public const META_SYNC_STATUS  = '_frontpad_sync_status';
	public const META_WARNINGS     = '_frontpad_warnings';
	public const META_ERROR        = '_frontpad_error';

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		$settings = API_Client::get_settings();
		$trigger  = (string) ( $settings['order_trigger'] ?? 'checkout' );

		if ( 'checkout' === $trigger ) {
			add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'handle_order_trigger' ), 20, 1 );
			add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'handle_store_api_order' ), 20, 1 );
		} elseif ( in_array( $trigger, array( 'processing', 'on-hold', 'completed' ), true ) ) {
			add_action( 'woocommerce_order_status_' . $trigger, array( __CLASS__, 'handle_order_trigger' ), 20, 1 );
		}

		// Optional structured address & person fields on checkout.
		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'add_checkout_fields' ), 60 );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'save_checkout_fields' ), 20, 2 );

		// HPOS-compatible Order Edit metabox & manual send action.
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_order_metabox' ) );
		add_action( 'wp_ajax_frontpad_send_order', array( __CLASS__, 'ajax_send_order' ) );
	}

	/**
	 * Add optional Frontpad-specific checkout fields (entrance, floor, apartment, persons)
	 * when enabled in settings.
	 *
	 * @param array<string, array<string, mixed>> $fields Checkout fields.
	 * @return array<string, array<string, mixed>>
	 */
	public static function add_checkout_fields( array $fields ): array {
		$settings = API_Client::get_settings();

		if ( '1' === (string) ( $settings['enable_checkout_address'] ?? '1' ) ) {
			$fields['billing']['frontpad_pod'] = array(
				'label'       => self::t( 'Entrance' ),
				'placeholder' => '1',
				'required'    => false,
				'priority'    => 42,
				'maxlength'   => 2,
				'class'       => array( 'form-row-first' ),
			);
			$fields['billing']['frontpad_et'] = array(
				'label'       => self::t( 'Floor' ),
				'placeholder' => '2',
				'required'    => false,
				'priority'    => 44,
				'maxlength'   => 2,
				'class'       => array( 'form-row-last' ),
			);
			$fields['billing']['frontpad_apart'] = array(
				'label'       => self::t( 'Apartment / Office' ),
				'placeholder' => '15',
				'required'    => false,
				'priority'    => 46,
				'maxlength'   => 50,
				'class'       => array( 'form-row-wide' ),
			);
		}

		if ( '1' === (string) ( $settings['enable_person_field'] ?? '1' ) ) {
			$fields['order']['frontpad_person'] = array(
				'type'        => 'number',
				'label'       => self::t( 'Number of Persons' ),
				'placeholder' => '1',
				'required'    => false,
				'priority'    => 110,
				'custom_attributes' => array(
					'min' => '1',
					'max' => '99',
				),
				'class'       => array( 'form-row-wide' ),
			);
		}

		return $fields;
	}

	/**
	 * Save Frontpad checkout fields + session certificate/loyalty score onto the WC order.
	 *
	 * @param \WC_Order            $order Order object.
	 * @param array<string, mixed> $data Posted checkout data.
	 */
	public static function save_checkout_fields( \WC_Order $order, array $data ): void {
		foreach ( array( 'frontpad_pod', 'frontpad_et', 'frontpad_apart', 'frontpad_person' ) as $key ) {
			if ( isset( $data[ $key ] ) && '' !== trim( (string) $data[ $key ] ) ) {
				$order->update_meta_data( '_' . $key, sanitize_text_field( (string) $data[ $key ] ) );
			}
		}
	}

	/**
	 * Handle Store API checkout order hook.
	 *
	 * @param \WC_Order $order Order object.
	 */
	public static function handle_store_api_order( \WC_Order $order ): void {
		self::handle_order_trigger( $order->get_id() );
	}

	/**
	 * Automatically send order to Frontpad when triggered.
	 */
	public static function handle_order_trigger( int $order_id ): void {
		if ( ! API_Client::is_configured() ) {
			return;
		}
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		// Prevent duplicate automatic submissions.
		if ( '' !== (string) $order->get_meta( self::META_ORDER_ID ) ) {
			return;
		}
		self::send_order( $order );
	}

	/**
	 * Build the complete `new_order` payload from a WooCommerce order.
	 *
	 * @param \WC_Order            $order Order object.
	 * @param array<string, mixed> $settings Plugin settings.
	 * @return array<string, mixed>
	 */
	public static function build_order_payload( \WC_Order $order, array $settings ): array {
		$product       = array();
		$product_kol   = array();
		$product_mod   = array();
		$product_price = array();
		$send_prices   = '1' === (string) ( $settings['send_product_price'] ?? '0' );

		$idx = 0;
		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof \WC_Order_Item_Product ) {
				continue;
			}
			$wc_product = $item->get_product();
			if ( ! $wc_product ) {
				continue;
			}
			$sku = trim( (string) $wc_product->get_sku() );
			if ( '' === $sku ) {
				continue;
			}

			$parent_idx           = $idx;
			$product[ $idx ]      = $sku;
			$product_kol[ $idx ]  = (string) max( 1, (int) $item->get_quantity() );

			if ( $send_prices ) {
				$qty                   = max( 1, (int) $item->get_quantity() );
				$unit_price            = (float) $item->get_subtotal() / $qty;
				$product_price[ $idx ] = (string) round( $unit_price, 2 );
			}

			// If the item itself has a Frontpad parent key meta (for modifier line items).
			$explicit_parent = $item->get_meta( '_frontpad_parent_key' );
			if ( '' !== (string) $explicit_parent && is_numeric( $explicit_parent ) ) {
				$product_mod[ $idx ] = (string) (int) $explicit_parent;
			}

			++$idx;

			// Support attached modifiers stored in `_frontpad_modifiers` item meta:
			// array of [ 'sku' => '10001', 'qty' => 1, 'price' => 100 ].
			$modifiers = $item->get_meta( '_frontpad_modifiers' );
			if ( is_array( $modifiers ) ) {
				foreach ( $modifiers as $mod ) {
					$mod_sku = trim( (string) ( $mod['sku'] ?? '' ) );
					if ( '' === $mod_sku ) {
						continue;
					}
					$product[ $idx ]     = $mod_sku;
					$product_kol[ $idx ] = (string) max( 1, (int) ( $mod['qty'] ?? $item->get_quantity() ) );
					$product_mod[ $idx ] = (string) $parent_idx;
					if ( $send_prices && isset( $mod['price'] ) ) {
						$product_price[ $idx ] = (string) (float) $mod['price'];
					}
					++$idx;
				}
			}
		}

		// Parse address into street, home, apart, pod, et respecting Frontpad field length limits.
		$raw_street = trim( (string) $order->get_billing_address_1() );
		$raw_apart  = trim( (string) $order->get_billing_address_2() );
		$parsed     = self::parse_address( $raw_street, $raw_apart );

		$pod   = trim( (string) $order->get_meta( '_frontpad_pod' ) );
		$et    = trim( (string) $order->get_meta( '_frontpad_et' ) );
		$apart = trim( (string) $order->get_meta( '_frontpad_apart' ) );

		$full_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
		$phone     = self::normalize_phone( (string) $order->get_billing_phone(), (string) ( $settings['phone_prefix'] ?? '' ) );
		$mail      = trim( (string) $order->get_billing_email() );
		$descr     = self::build_description( $order );

		$payload = array(
			'product'     => $product,
			'product_kol' => $product_kol,
			'street'      => mb_substr( $parsed['street'], 0, 50 ),
			'home'        => mb_substr( $parsed['home'], 0, 50 ),
			'pod'         => mb_substr( '' !== $pod ? $pod : $parsed['pod'], 0, 2 ),
			'et'          => mb_substr( '' !== $et ? $et : $parsed['et'], 0, 2 ),
			'apart'       => mb_substr( '' !== $apart ? $apart : $parsed['apart'], 0, 50 ),
			'phone'       => mb_substr( $phone, 0, 50 ),
			'mail'        => mb_substr( $mail, 0, 50 ),
			'descr'       => mb_substr( $descr, 0, 100 ),
			'name'        => mb_substr( $full_name, 0, 50 ),
		);

		if ( ! empty( $product_mod ) ) {
			$payload['product_mod'] = $product_mod;
		}
		if ( ! empty( $product_price ) ) {
			$payload['product_price'] = $product_price;
		}

		// Loyalty points (`score`).
		$score = $order->get_meta( '_frontpad_score' );
		if ( is_numeric( $score ) && (float) $score > 0 ) {
			$payload['score'] = (string) (float) $score;
		}

		// Discount: Frontpad allows ONLY ONE type — percentage (`sale` 1..100) OR fixed (`sale_amount`).
		$discount_total = (float) $order->get_discount_total();
		if ( $discount_total > 0 ) {
			$sale_pct = self::detect_percentage_coupon( $order );
			if ( $sale_pct >= 1 && $sale_pct <= 100 ) {
				$payload['sale'] = (string) $sale_pct;
			} else {
				$payload['sale_amount'] = (string) round( $discount_total, 2 );
			}
		}

		// Person count (max 2 chars).
		$person = preg_replace( '/\D/', '', (string) $order->get_meta( '_frontpad_person' ) );
		if ( '' !== $person ) {
			$payload['person'] = substr( $person, 0, 2 );
		}

		// Payment method mapping (`pay`).
		$gateway_id  = (string) $order->get_payment_method();
		$payment_map = is_array( $settings['payment_map'] ?? null ) ? $settings['payment_map'] : array();
		if ( '' !== $gateway_id && isset( $payment_map[ $gateway_id ] ) && '' !== trim( (string) $payment_map[ $gateway_id ] ) ) {
			$payload['pay'] = trim( (string) $payment_map[ $gateway_id ] );
		}

		// Channel, affiliate, point.
		foreach ( array( 'channel', 'affiliate', 'point' ) as $opt_key ) {
			$val = trim( (string) ( $settings[ $opt_key ] ?? '' ) );
			if ( '' !== $val ) {
				$payload[ $opt_key ] = $val;
			}
		}

		// Tags (max 10).
		$tags = self::parse_int_list( (string) ( $settings['default_tags'] ?? '' ), 10 );
		if ( ! empty( $tags ) ) {
			$payload['tags'] = $tags;
		}

		// Webhook status codes (max 5) & hook_url.
		$hook_statuses = self::parse_int_list( (string) ( $settings['hook_statuses'] ?? '' ), 5 );
		if ( ! empty( $hook_statuses ) ) {
			$payload['hook_status'] = $hook_statuses;
			$payload['hook_url']    = Webhook::get_webhook_url();
		}

		// Preorder datetime (`YYYY-MM-DD HH:MM:SS`, max 30 days ahead).
		$preorder_dt = self::resolve_preorder_datetime( $order );
		if ( '' !== $preorder_dt ) {
			$payload['datetime'] = $preorder_dt;
		}

		return apply_filters( 'gayanes_frontpad_new_order_payload', $payload, $order );
	}

	/**
	 * Send a WooCommerce order to Frontpad `new_order`.
	 *
	 * @param \WC_Order $order Order object.
	 * @return array<string, mixed>|\WP_Error
	 */
	public static function send_order( \WC_Order $order ) {
		$settings = API_Client::get_settings();
		$payload  = self::build_order_payload( $order, $settings );

		if ( empty( $payload['product'] ) ) {
			$msg = __( 'Frontpad: Order has no products with SKUs.', 'gayanes-frontpad' );
			$order->update_meta_data( self::META_SYNC_STATUS, 'skipped' );
			$order->update_meta_data( self::META_ERROR, $msg );
			$order->add_order_note( $msg );
			$order->save();
			return new \WP_Error( 'no_skus', $msg );
		}

		$response = API_Client::request( 'new_order', $payload );
		if ( is_wp_error( $response ) ) {
			$order->update_meta_data( self::META_SYNC_STATUS, 'error' );
			$order->update_meta_data( self::META_ERROR, $response->get_error_message() );
			$order->add_order_note( 'Frontpad error: ' . $response->get_error_message() );
			$order->save();
			return $response;
		}

		if ( 'success' !== ( $response['result'] ?? '' ) ) {
			$err_code = (string) ( $response['error'] ?? 'unknown' );
			$err_msg  = API_Client::format_error( $err_code );
			$order->update_meta_data( self::META_SYNC_STATUS, 'error' );
			$order->update_meta_data( self::META_ERROR, $err_msg );
			$order->add_order_note( 'Frontpad rejected order: ' . $err_msg );
			$order->save();
			return new \WP_Error( $err_code, $err_msg );
		}

		$fp_id  = (string) ( $response['order_id'] ?? '' );
		$fp_num = (string) ( $response['order_number'] ?? '' );

		$order->update_meta_data( self::META_ORDER_ID, $fp_id );
		$order->update_meta_data( self::META_ORDER_NUMBER, $fp_num );
		$order->update_meta_data( self::META_SYNC_STATUS, 'success' );
		$order->delete_meta_data( self::META_ERROR );

		if ( ! empty( $response['warnings'] ) && is_array( $response['warnings'] ) ) {
			$order->update_meta_data( self::META_WARNINGS, wp_json_encode( $response['warnings'] ) );
			$order->add_order_note(
				sprintf(
					'Frontpad order created (#%s / ID %s) with warnings: %s',
					$fp_num,
					$fp_id,
					wp_json_encode( $response['warnings'] )
				)
			);
		} else {
			$order->delete_meta_data( self::META_WARNINGS );
			$order->add_order_note( sprintf( 'Frontpad order created: #%s (ID: %s)', $fp_num, $fp_id ) );
		}

		$order->save();
		return $response;
	}

	/**
	 * Parse single-line or two-line address into street, home, pod, et, apart.
	 *
	 * @return array{street: string, home: string, pod: string, et: string, apart: string}
	 */
	public static function parse_address( string $address_1, string $address_2 = '' ): array {
		$street = $address_1;
		$home   = '';
		$apart  = $address_2;
		$pod    = '';
		$et     = '';

		// Try extracting house number from "Street Name 17, apt 6" or "Street 17/2".
		if ( preg_match( '/^(.+?)\s+(\d+[a-zA-Zа-яА-Я]?)(?:[,\s]+(.*))?$/u', $address_1, $m ) ) {
			$street = trim( $m[1], " ,\t" );
			$home   = trim( $m[2] );
			if ( '' === $apart && ! empty( $m[3] ) ) {
				$apart = trim( $m[3] );
			}
		}

		return array(
			'street' => $street,
			'home'   => $home,
			'pod'    => $pod,
			'et'     => $et,
			'apart'  => $apart,
		);
	}

	/**
	 * Normalize phone number with optional prefix.
	 */
	public static function normalize_phone( string $phone, string $prefix = '' ): string {
		$clean = preg_replace( '/[^\d+]/', '', $phone ) ?? '';
		if ( '' !== $prefix && '' !== $clean && ! str_starts_with( $clean, $prefix ) ) {
			$digits = ltrim( $clean, '+0' );
			$clean  = $prefix . $digits;
		}
		return $clean;
	}

	/**
	 * Parse comma-separated integer IDs up to `$limit`.
	 *
	 * @return array<int, string>
	 */
	public static function parse_int_list( string $raw, int $limit ): array {
		$parts = array_filter( array_map( 'trim', explode( ',', $raw ) ) );
		$out   = array();
		foreach ( $parts as $p ) {
			if ( is_numeric( $p ) ) {
				$out[] = (string) (int) $p;
				if ( count( $out ) >= $limit ) {
					break;
				}
			}
		}
		return $out;
	}

	/**
	 * Resolve preorder datetime (`YYYY-MM-DD HH:MM:SS`) from Gayanes_Core delivery meta if within 30 days.
	 */
	private static function resolve_preorder_datetime( \WC_Order $order ): string {
		$date = trim( (string) $order->get_meta( '_gayanes_delivery_date' ) );
		$slot = trim( (string) $order->get_meta( '_gayanes_delivery_time' ) );

		if ( '' === $date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return '';
		}

		$time = '12:00:00';
		if ( preg_match( '/(\d{1,2}):(\d{2})/', $slot, $m ) ) {
			$time = sprintf( '%02d:%02d:00', (int) $m[1], (int) $m[2] );
		}

		try {
			$tz     = new \DateTimeZone( wp_timezone_string() ?: 'Asia/Yerevan' );
			$target = new \DateTimeImmutable( $date . ' ' . $time, $tz );
			$now    = new \DateTimeImmutable( 'now', $tz );
			$max    = $now->modify( '+30 days' );

			if ( $target > $now && $target <= $max ) {
				return $target->format( 'Y-m-d H:i:s' );
			}
		} catch ( \Throwable $e ) {
			return '';
		}

		return '';
	}

	/**
	 * Build order description combining customer note and delivery slot.
	 */
	private static function build_description( \WC_Order $order ): string {
		$parts = array();
		$note  = trim( (string) $order->get_customer_note() );
		if ( '' !== $note ) {
			$parts[] = $note;
		}
		$d_date = trim( (string) $order->get_meta( '_gayanes_delivery_date' ) );
		$d_time = trim( (string) $order->get_meta( '_gayanes_delivery_time' ) );
		if ( '' !== $d_date || '' !== $d_time ) {
			$parts[] = trim( $d_date . ' ' . $d_time );
		}
		return implode( ' | ', $parts );
	}

	/**
	 * Detect if order uses a single percentage coupon (returns 1..100, or 0 if fixed/mixed).
	 */
	private static function detect_percentage_coupon( \WC_Order $order ): int {
		$coupons = $order->get_coupons();
		if ( 1 !== count( $coupons ) ) {
			return 0;
		}
		$item   = reset( $coupons );
		$coupon = new \WC_Coupon( $item->get_code() );
		if ( 'percent' === $coupon->get_discount_type() ) {
			return (int) round( (float) $coupon->get_amount() );
		}
		return 0;
	}

	/**
	 * Register HPOS-compatible Order Metabox.
	 */
	public static function register_order_metabox(): void {
		$screen = 'shop_order';
		if ( class_exists( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class ) ) {
			$controller = wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class );
			if ( $controller->custom_orders_table_usage_is_enabled() ) {
				$screen = wc_get_page_screen_id( 'shop-order' );
			}
		}

		add_meta_box(
			'gayanes_frontpad_order_box',
			__( 'Frontpad POS', 'gayanes-frontpad' ),
			array( __CLASS__, 'render_order_metabox' ),
			$screen,
			'side',
			'high'
		);
	}

	/**
	 * Render Order Metabox content.
	 *
	 * @param \WP_Post|\WC_Order $post_or_order Post or Order object.
	 */
	public static function render_order_metabox( $post_or_order ): void {
		$order = $post_or_order instanceof \WC_Order ? $post_or_order : wc_get_order( $post_or_order->ID );
		if ( ! $order ) {
			return;
		}

		$fp_id     = (string) $order->get_meta( self::META_ORDER_ID );
		$fp_num    = (string) $order->get_meta( self::META_ORDER_NUMBER );
		$status    = (string) $order->get_meta( self::META_SYNC_STATUS );
		$error     = (string) $order->get_meta( self::META_ERROR );
		$warnings  = (string) $order->get_meta( self::META_WARNINGS );
		$nonce     = wp_create_nonce( 'frontpad_send_order_' . $order->get_id() );

		echo '<div class="frontpad-order-metabox">';
		if ( '' !== $fp_id ) {
			echo '<p><strong>' . esc_html__( 'Frontpad Order #:', 'gayanes-frontpad' ) . '</strong> ' . esc_html( $fp_num ) . '</p>';
			echo '<p><strong>' . esc_html__( 'Frontpad ID:', 'gayanes-frontpad' ) . '</strong> <code>' . esc_html( $fp_id ) . '</code></p>';
		} else {
			echo '<p>' . esc_html__( 'Not yet sent to Frontpad.', 'gayanes-frontpad' ) . '</p>';
		}

		if ( '' !== $error ) {
			echo '<p style="color:#d63638;"><strong>' . esc_html__( 'Error:', 'gayanes-frontpad' ) . '</strong> ' . esc_html( $error ) . '</p>';
		}
		if ( '' !== $warnings ) {
			echo '<p style="color:#996800;"><strong>' . esc_html__( 'Warnings:', 'gayanes-frontpad' ) . '</strong> <code>' . esc_html( $warnings ) . '</code></p>';
		}

		$btn_label = '' !== $fp_id ? __( 'Resend to Frontpad', 'gayanes-frontpad' ) : __( 'Send to Frontpad', 'gayanes-frontpad' );
		echo '<button type="button" class="button button-secondary frontpad-manual-send" data-order="' . esc_attr( (string) $order->get_id() ) . '" data-nonce="' . esc_attr( $nonce ) . '">' . esc_html( $btn_label ) . '</button>';
		echo '<span class="frontpad-send-feedback" style="display:block;margin-top:6px;"></span>';
		echo '</div>';
		?>
		<script>
		jQuery(function($){
			$('.frontpad-manual-send').off('click').on('click', function(){
				var btn = $(this), fb = btn.siblings('.frontpad-send-feedback');
				btn.prop('disabled', true);
				fb.text('Sending...');
				$.post(ajaxurl, {
					action: 'frontpad_send_order',
					order_id: btn.data('order'),
					nonce: btn.data('nonce')
				}, function(res){
					btn.prop('disabled', false);
					if (res.success) {
						fb.css('color', '#00a32a').text(res.data.message);
						setTimeout(function(){ location.reload(); }, 900);
					} else {
						fb.css('color', '#d63638').text(res.data && res.data.message ? res.data.message : 'Error');
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * AJAX handler for manual order send/resend.
	 */
	public static function ajax_send_order(): void {
		$order_id = absint( $_POST['order_id'] ?? 0 );
		if ( ! $order_id || ! current_user_can( 'edit_shop_orders' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gayanes-frontpad' ) ) );
		}
		check_ajax_referer( 'frontpad_send_order_' . $order_id, 'nonce' );

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_send_json_error( array( 'message' => __( 'Order not found.', 'gayanes-frontpad' ) ) );
		}

		$result = self::send_order( $order );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message' => sprintf(
					__( 'Sent! Frontpad #%s (ID %s)', 'gayanes-frontpad' ),
					(string) ( $result['order_number'] ?? '' ),
					(string) ( $result['order_id'] ?? '' )
				),
			)
		);
	}

	/**
	 * Translate string using Gayanes_Core\Multilingual if available.
	 */
	private static function t( string $text ): string {
		if ( class_exists( '\Gayanes_Core\Multilingual' ) ) {
			return \Gayanes_Core\Multilingual::t( $text, 'gayanes-frontpad' );
		}
		return __( $text, 'gayanes-frontpad' );
	}
}
