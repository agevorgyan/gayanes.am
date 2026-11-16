<?php
/**
 * GayanesV2 Cart Page — iOS 27 Liquid Glass, instant +/- stepper & trash remove, mobile-first.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package GayanesV2
 * @version 11.3.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<div class="gv2-cart-card gv2-glass-card woocommerce-cart-form__contents">
		<div class="gv2-cart-card__head" aria-hidden="true">
			<span><?php esc_html_e( 'Product', 'woocommerce' ); ?></span>
			<span><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></span>
			<span><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
		</div>

		<ul class="gv2-cart-list">
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>

			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
				$visible    = apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key );

				if ( ! ( $_product instanceof WC_Product ) || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! $visible ) {
					continue;
				}

				$display_id = (int) $product_id;
				if ( function_exists( 'pll_get_post' ) ) {
					$tr_id = (int) pll_get_post( $display_id );
					if ( $tr_id > 0 ) {
						$display_id = $tr_id;
					}
				}

				$cart_item_name    = WC()->cart->get_item_product_name( $cart_item, $_product );
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $cart_item_name, $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'gv2-product-portrait' ), $cart_item, $cart_item_key );
				$unit_price        = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$line_subtotal     = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
				$qty               = (int) $cart_item['quantity'];
				$max_qty           = $_product->is_sold_individually() ? 1 : $_product->get_max_purchase_quantity();
				$row_classes       = apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key );
				?>
				<li
					class="gv2-cart-row woocommerce-cart-form__cart-item <?php echo esc_attr( $row_classes ); ?>"
					data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
					data-product-id="<?php echo esc_attr( (string) $display_id ); ?>"
					data-max-qty="<?php echo esc_attr( (string) $max_qty ); ?>"
				>
					<div class="gv2-cart-row__media">
						<?php if ( $product_permalink ) : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
						<?php else : ?>
							<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
					</div>

					<div class="gv2-cart-row__info">
						<div class="gv2-cart-row__title">
							<?php
							if ( ! $product_permalink ) {
								echo wp_kses_post( $product_name );
							} else {
								echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $cart_item_name ), $cart_item, $cart_item_key ) );
							}
							?>
						</div>

						<div class="gv2-cart-row__unit">
							<?php echo wp_kses_post( $unit_price ); ?>
						</div>

						<?php
						do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
						echo wc_get_formatted_cart_item_data( $cart_item, false, $cart_item_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

						if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $qty ) ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id, $cart_item, $cart_item_key ) );
						}
						?>
					</div>

					<div class="gv2-cart-row__qty">
						<?php if ( $_product->is_sold_individually() ) : ?>
							<span class="gv2-cart-row__qty-fixed">1</span>
							<input type="hidden" name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]" value="1" />
						<?php else : ?>
							<div
								class="gv2-qty gv2-cart-qty"
								data-gv2-qty-control
								data-product-id="<?php echo esc_attr( (string) $display_id ); ?>"
								data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
							>
								<button
									type="button"
									class="gv2-qty__btn"
									data-gv2-qty-step="-1"
									aria-label="<?php echo esc_attr( gv2_t( 'Decrease quantity' ) ); ?>"
								>&minus;</button>
								<span class="gv2-qty__val" data-gv2-qty-val><?php echo esc_html( (string) $qty ); ?></span>
								<button
									type="button"
									class="gv2-qty__btn"
									data-gv2-qty-step="1"
									aria-label="<?php echo esc_attr( gv2_t( 'Increase quantity' ) ); ?>"
								>+</button>
								<input
									type="hidden"
									name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]"
									value="<?php echo esc_attr( (string) $qty ); ?>"
									data-gv2-qty-input
								/>
							</div>
						<?php endif; ?>
					</div>

					<div class="gv2-cart-row__total" data-gv2-cart-row-subtotal>
						<?php echo wp_kses_post( $line_subtotal ); ?>
					</div>

					<div class="gv2-cart-row__actions">
						<button
							type="button"
							class="gv2-cart-row__remove"
							data-gv2-remove-item="<?php echo esc_attr( $cart_item_key ); ?>"
							aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ); ?>"
							title="<?php echo esc_attr( gv2_t( 'Remove item' ) ); ?>"
						>
							<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
								<path d="M3 6h18"/>
								<path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/>
								<path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
								<path d="M10 11v6M14 11v6"/>
							</svg>
						</button>
					</div>
				</li>
				<?php
			}
			?>

			<?php do_action( 'woocommerce_cart_contents' ); ?>
		</ul>

		<div class="gv2-cart-card__footer actions">
			<?php if ( wc_coupons_enabled() ) : ?>
				<div class="coupon">
					<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
					<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
					<button type="submit" class="gv2-btn gv2-btn--glass button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
					<?php do_action( 'woocommerce_cart_coupon' ); ?>
				</div>
			<?php endif; ?>

			<button type="submit" class="gv2-cart-update-fallback button" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>" hidden><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

			<?php do_action( 'woocommerce_cart_actions' ); ?>
			<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
		</div>

		<?php do_action( 'woocommerce_after_cart_contents' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="cart-collaterals">
	<?php
	/**
	 * Cart collaterals hook.
	 *
	 * @hooked woocommerce_cross_sell_display
	 * @hooked woocommerce_cart_totals - 10
	 */
	do_action( 'woocommerce_cart_collaterals' );
	?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
