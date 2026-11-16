<?php
/**
 * WooCommerce AJAX Cart & Quantity Controls + Side Cart API.
 *
 * All cart mutations are validated and persisted by WooCommerce WC()->cart.
 * Supports simple and variable products, stock validation, and synchronized
 * product card + mini-cart state.
 *
 * @package Gayanes_Core
 */

namespace Gayanes_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Ajax_Cart class.
 */
class Ajax_Cart {

	/**
	 * Initialize WooCommerce AJAX endpoints and fragments.
	 */
	public static function init(): void {
		$actions = array(
			'gayanes_add_to_cart'       => 'handle_add_to_cart',
			'gayanes_update_cart_qty'   => 'handle_update_qty',
			'gayanes_remove_cart_item'  => 'handle_remove_item',
			'gayanes_get_cart_state'    => 'handle_get_cart_state',
		);

		foreach ( $actions as $action => $method ) {
			add_action( "wp_ajax_{$action}", array( __CLASS__, $method ) );
			add_action( "wp_ajax_nopriv_{$action}", array( __CLASS__, $method ) );
		}

		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'cart_fragments' ) );
	}

	/**
	 * Ensure WooCommerce session & cart are initialized in AJAX requests.
	 */
	private static function ensure_cart_loaded(): void {
		if ( null === WC()->session ) {
			$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
			WC()->session  = new $session_class();
			WC()->session->init();
		}
		if ( null === WC()->customer ) {
			WC()->customer = new \WC_Customer( get_current_user_id(), true );
		}
		if ( null === WC()->cart ) {
			WC()->cart = new \WC_Cart();
		}
		WC()->cart->get_cart();
	}

	/**
	 * Handle AJAX Add to Cart (simple and variable products).
	 */
	public static function handle_add_to_cart(): void {
		Security::verify_ajax_nonce();
		self::ensure_cart_loaded();

		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		$quantity     = isset( $_POST['quantity'] ) ? max( 1, absint( $_POST['quantity'] ) ) : 1;
		$variation    = array();

		if ( isset( $_POST['variation'] ) && is_array( $_POST['variation'] ) ) {
			foreach ( wp_unslash( $_POST['variation'] ) as $key => $val ) {
				$variation[ sanitize_text_field( (string) $key ) ] = sanitize_text_field( (string) $val );
			}
		}

		$product = wc_get_product( $variation_id > 0 ? $variation_id : $product_id );
		if ( ! $product || ! $product->is_purchasable() ) {
			wp_send_json_error(
				array(
					'code'    => 'not_purchasable',
					'message' => Multilingual::t( 'This product cannot be purchased right now.' ),
				),
				400
			);
		}

		if ( $product->is_type( 'variable' ) && 0 === $variation_id ) {
			wp_send_json_error(
				array(
					'code'        => 'variation_required',
					'message'     => Multilingual::t( 'Please select product options first.' ),
					'redirectUrl' => get_permalink( $product_id ),
				),
				400
			);
		}

		$passed_validation = apply_filters(
			'woocommerce_add_to_cart_validation',
			true,
			$product_id,
			$quantity,
			$variation_id,
			$variation
		);

		if ( ! $passed_validation ) {
			$notices = wc_get_notices( 'error' );
			wc_clear_notices();
			$message = ! empty( $notices[0]['notice'] )
				? wp_strip_all_tags( (string) $notices[0]['notice'] )
				: Multilingual::t( 'Unable to add this product to your cart.' );

			wp_send_json_error(
				array(
					'code'    => 'validation_failed',
					'message' => $message,
				),
				400
			);
		}

		$cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
		if ( ! $cart_item_key ) {
			$notices = wc_get_notices( 'error' );
			wc_clear_notices();
			$message = ! empty( $notices[0]['notice'] )
				? wp_strip_all_tags( (string) $notices[0]['notice'] )
				: Multilingual::t( 'Could not add product to cart. Please check stock availability.' );

			wp_send_json_error(
				array(
					'code'    => 'add_failed',
					'message' => $message,
				),
				400
			);
		}

		WC()->cart->calculate_totals();
		WC()->cart->set_session();
		do_action( 'woocommerce_ajax_added_to_cart', $product_id );

		wp_send_json_success(
			array_merge(
				self::get_cart_payload(),
				array(
					'cart_item_key' => $cart_item_key,
					'message'       => Multilingual::t( 'Added to cart' ),
				)
			)
		);
	}

	/**
	 * Handle AJAX quantity update (+ / -). Removes item when quantity <= 0.
	 */
	public static function handle_update_qty(): void {
		Security::verify_ajax_nonce();
		self::ensure_cart_loaded();

		$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
		$product_id    = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$variation_id  = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		$quantity      = isset( $_POST['quantity'] ) ? (int) $_POST['quantity'] : 0;

		if ( '' === $cart_item_key && $product_id > 0 ) {
			$cart_item_key = self::find_cart_item_key( $product_id, $variation_id );
		}

		$cart_item = '' !== $cart_item_key ? WC()->cart->get_cart_item( $cart_item_key ) : null;
		if ( empty( $cart_item ) ) {
			// Item no longer in cart: if quantity > 0, add it fresh.
			if ( $quantity > 0 && $product_id > 0 ) {
				$_POST['quantity'] = $quantity;
				self::handle_add_to_cart();
				return;
			}
			wp_send_json_success( self::get_cart_payload() );
		}

		if ( $quantity <= 0 ) {
			WC()->cart->remove_cart_item( $cart_item_key );
			WC()->cart->calculate_totals();
			WC()->cart->set_session();

			wp_send_json_success(
				array_merge(
					self::get_cart_payload(),
					array(
						'removed' => true,
						'message' => Multilingual::t( 'Removed from cart' ),
					)
				)
			);
		}

		/** @var \WC_Product $product */
		$product = $cart_item['data'];
		$max_qty = $product->get_max_purchase_quantity();
		if ( $max_qty > 0 && $quantity > $max_qty ) {
			wp_send_json_error(
				array(
					'code'    => 'exceeds_stock',
					'message' => sprintf(
						/* translators: %d: maximum available quantity */
						Multilingual::t( 'Only %d available in stock.' ),
						$max_qty
					),
					'max_qty' => $max_qty,
					'cart'    => self::get_cart_payload(),
				),
				400
			);
		}

		$passed_validation = apply_filters(
			'woocommerce_update_cart_validation',
			true,
			$cart_item_key,
			$cart_item,
			$quantity
		);

		if ( ! $passed_validation ) {
			$notices = wc_get_notices( 'error' );
			wc_clear_notices();
			wp_send_json_error(
				array(
					'code'    => 'validation_failed',
					'message' => ! empty( $notices[0]['notice'] ) ? wp_strip_all_tags( (string) $notices[0]['notice'] ) : Multilingual::t( 'Cannot update quantity.' ),
					'cart'    => self::get_cart_payload(),
				),
				400
			);
		}

		WC()->cart->set_quantity( $cart_item_key, $quantity, true );
		WC()->cart->calculate_totals();
		WC()->cart->set_session();

		wp_send_json_success( self::get_cart_payload() );
	}

	/**
	 * Handle AJAX remove cart item.
	 */
	public static function handle_remove_item(): void {
		Security::verify_ajax_nonce();
		self::ensure_cart_loaded();

		$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
		if ( '' !== $cart_item_key && WC()->cart->get_cart_item( $cart_item_key ) ) {
			WC()->cart->remove_cart_item( $cart_item_key );
			WC()->cart->calculate_totals();
			WC()->cart->set_session();
		}

		wp_send_json_success( self::get_cart_payload() );
	}

	/**
	 * Handle AJAX get current cart state (useful after cached page loads).
	 */
	public static function handle_get_cart_state(): void {
		self::ensure_cart_loaded();
		wp_send_json_success( self::get_cart_payload() );
	}

	/**
	 * Find the cart item key for a given product_id / variation_id.
	 *
	 * @param int $product_id   Product ID.
	 * @param int $variation_id Variation ID.
	 */
	public static function find_cart_item_key( int $product_id, int $variation_id = 0 ): string {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return '';
		}
		foreach ( WC()->cart->get_cart() as $key => $item ) {
			$match_product   = (int) $item['product_id'] === $product_id;
			$match_variation = 0 === $variation_id || (int) $item['variation_id'] === $variation_id;
			if ( $match_product && $match_variation ) {
				return (string) $key;
			}
		}
		return '';
	}

	/**
	 * Convert a WooCommerce HTML price string into clean plain text (decoding entities like &nbsp;).
	 *
	 * @param string $price_html Price HTML from wc_price() or cart subtotal.
	 * @return string
	 */
	private static function format_plain_price( string $price_html ): string {
		return html_entity_decode( wp_strip_all_tags( $price_html ), ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Build structured cart payload for frontend hydration of cards & mini-cart.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_cart_payload(): array {
		$items      = array();
		$quantities = array();

		if ( function_exists( 'WC' ) && WC()->cart ) {
			foreach ( WC()->cart->get_cart() as $key => $item ) {
				/** @var \WC_Product $product */
				$product      = $item['data'];
				$product_id   = (int) $item['product_id'];
				$variation_id = (int) $item['variation_id'];
				$qty          = (int) $item['quantity'];
				$lookup_key   = $variation_id > 0 ? "{$product_id}:{$variation_id}" : (string) $product_id;

				$quantities[ $lookup_key ] = array(
					'qty'           => $qty,
					'cart_item_key' => (string) $key,
				);
				// Also track total per parent product_id for quick card lookup.
				if ( ! isset( $quantities[ (string) $product_id ] ) ) {
					$quantities[ (string) $product_id ] = array(
						'qty'           => $qty,
						'cart_item_key' => (string) $key,
					);
				}

				$display_product = $product;
				$display_id      = $product_id;
				if ( function_exists( 'pll_get_post' ) ) {
					$tr_id = (int) pll_get_post( $product_id );
					if ( $tr_id > 0 && $tr_id !== $product_id ) {
						$tr_product = wc_get_product( $tr_id );
						if ( $tr_product instanceof \WC_Product ) {
							$display_product = $tr_product;
							$display_id      = $tr_id;
							if ( ! isset( $quantities[ (string) $tr_id ] ) ) {
								$quantities[ (string) $tr_id ] = array(
									'qty'           => $qty,
									'cart_item_key' => (string) $key,
								);
							}
						}
					}
				}

				$thumb_id  = $display_product->get_image_id() ?: $product->get_image_id();
				$thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'woocommerce_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_thumbnail' );

				$items[] = array(
					'key'          => (string) $key,
					'product_id'   => $display_id,
					'variation_id' => $variation_id,
					'name'         => $display_product->get_name(),
					'permalink'    => $display_product->get_permalink(),
					'thumbnail'    => $thumb_url,
					'unit_price'   => self::format_plain_price( wc_price( wc_get_price_to_display( $product ) ) ),
					'line_total'   => self::format_plain_price( WC()->cart->get_product_subtotal( $product, $qty ) ),
					'quantity'     => $qty,
					'max_qty'      => $product->get_max_purchase_quantity(),
					'weight'       => $display_product->get_weight() ? $display_product->get_weight() . ' ' . get_option( 'woocommerce_weight_unit' ) : '',
				);
			}
		}

		$count = function_exists( 'WC' ) && WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;

		return array(
			'count'        => $count,
			'subtotal'     => function_exists( 'WC' ) && WC()->cart ? self::format_plain_price( WC()->cart->get_cart_subtotal() ) : '',
			'total'        => function_exists( 'WC' ) && WC()->cart ? self::format_plain_price( WC()->cart->get_total() ) : '',
			'items'        => $items,
			'quantities'   => $quantities,
			'cart_url'     => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ),
			'checkout_url' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' ),
		);
	}

	/**
	 * Provide WooCommerce cart fragments for compatibility with third-party plugins.
	 *
	 * @param array<string, string> $fragments Existing fragments.
	 * @return array<string, string>
	 */
	public static function cart_fragments( array $fragments ): array {
		$count = WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;

		$fragments['.gk-cart-count'] = '<span class="gk-cart-count" data-count="' . esc_attr( (string) $count ) . '">' . esc_html( (string) $count ) . '</span>';

		return $fragments;
	}
}

