<?php
/**
 * WooCommerce customization: compact product cards, AJAX quantity controls,
 * and preserved checkout/payment gateway compatibility.
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove default WooCommerce wrappers and replace with theme layout container.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

add_action( 'woocommerce_before_main_content', static function (): void {
	echo '<main id="primary" class="gk-main gk-container">';
	gayanes_breadcrumbs();
}, 10 );

add_action( 'woocommerce_after_main_content', static function (): void {
	echo '</main>';
}, 10 );

/**
 * Get product weight or size label if configured.
 *
 * @param WC_Product $product WooCommerce product.
 */
function gayanes_get_product_weight_label( WC_Product $product ): string {
	$weight = $product->get_weight();
	if ( '' !== $weight && null !== $weight ) {
		return trim( $weight . ' ' . get_option( 'woocommerce_weight_unit', 'g' ) );
	}

	foreach ( array( 'pa_weight', 'pa_size', 'weight', 'size' ) as $attr_name ) {
		$val = $product->get_attribute( $attr_name );
		if ( '' !== $val ) {
			return $val;
		}
	}

	return '';
}

/**
 * Render a compact, accessible product card with AJAX Add-to-Cart / [ - ] [ qty ] [ + ].
 *
 * Used uniformly across Homepage, Category pages, Shop listing, Search results,
 * and Related products.
 *
 * @param WC_Product $product WooCommerce product.
 */
function gayanes_render_product_card( WC_Product $product ): void {
	$product_id   = $product->get_id();
	$is_variable  = $product->is_type( 'variable' );
	$in_stock     = $product->is_in_stock();
	$purchasable  = $product->is_purchasable();
	$max_qty      = $product->get_max_purchase_quantity();
	$weight_label = gayanes_get_product_weight_label( $product );
	$permalink    = $product->get_permalink();

	$current_qty   = 0;
	$cart_item_key = '';
	if ( function_exists( 'WC' ) && WC()->cart && ! $is_variable ) {
		foreach ( WC()->cart->get_cart() as $key => $item ) {
			if ( (int) $item['product_id'] === $product_id && 0 === (int) $item['variation_id'] ) {
				$current_qty   = (int) $item['quantity'];
				$cart_item_key = (string) $key;
				break;
			}
		}
	}

	$image_id = $product->get_image_id();
	?>
	<article
		class="gk-card<?php echo ! $in_stock ? ' is-out-of-stock' : ''; ?>"
		data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
		data-product-type="<?php echo esc_attr( $product->get_type() ); ?>"
		data-max-qty="<?php echo esc_attr( (string) $max_qty ); ?>"
	>
		<a href="<?php echo esc_url( $permalink ); ?>" class="gk-card__media">
			<?php
			if ( $image_id ) {
				echo wp_get_attachment_image(
					$image_id,
					'woocommerce_thumbnail',
					false,
					array(
						'class'    => 'gk-card__img',
						'loading'  => 'lazy',
						'decoding' => 'async',
					)
				);
			} else {
				echo '<img class="gk-card__img" src="' . esc_url( wc_placeholder_img_src( 'woocommerce_thumbnail' ) ) . '" alt="' . esc_attr( $product->get_name() ) . '" loading="lazy" decoding="async" />';
			}
			?>
			<?php if ( $product->is_on_sale() ) : ?>
				<span class="gk-badge gk-badge--sale"><?php echo esc_html( gayanes_t( 'Sale' ) ); ?></span>
			<?php endif; ?>
		</a>

		<div class="gk-card__body">
			<div class="gk-card__meta">
				<?php if ( '' !== $weight_label ) : ?>
					<span class="gk-card__weight"><?php echo esc_html( $weight_label ); ?></span>
				<?php endif; ?>
				<span class="gk-card__stock <?php echo $in_stock ? 'is-in' : 'is-out'; ?>">
					<?php echo esc_html( $in_stock ? gayanes_t( 'In Stock' ) : gayanes_t( 'Out of Stock' ) ); ?>
				</span>
			</div>

			<h3 class="gk-card__title">
				<a href="<?php echo esc_url( $permalink ); ?>">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h3>

			<div class="gk-card__footer">
				<div class="gk-card__price">
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
				</div>

				<div
					class="gk-qty-control"
					data-gk-cart-control
					data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
					data-variation-id="0"
					data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
					data-qty="<?php echo esc_attr( (string) $current_qty ); ?>"
					data-max-qty="<?php echo esc_attr( (string) $max_qty ); ?>"
				>
					<?php if ( ! $in_stock || ! $purchasable ) : ?>
						<button type="button" class="gk-btn gk-btn--disabled" disabled>
							<?php echo esc_html( gayanes_t( 'Out of Stock' ) ); ?>
						</button>
					<?php elseif ( $is_variable ) : ?>
						<a href="<?php echo esc_url( $permalink ); ?>" class="gk-btn gk-btn--outline gk-btn--sm">
							<?php echo esc_html( gayanes_t( 'Select Options' ) ); ?>
						</a>
					<?php else : ?>
						<button
							type="button"
							class="gk-btn gk-btn--primary gk-btn--sm gk-add-btn<?php echo $current_qty > 0 ? ' is-hidden' : ''; ?>"
							data-gk-action="add"
							aria-label="<?php echo esc_attr( sprintf( '%s: %s', gayanes_t( 'Add to Cart' ), $product->get_name() ) ); ?>"
						>
							<?php echo esc_html( gayanes_t( 'Add to Cart' ) ); ?>
						</button>

						<div class="gk-stepper<?php echo $current_qty > 0 ? '' : ' is-hidden'; ?>" role="group" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
							<button
								type="button"
								class="gk-stepper__btn"
								data-gk-action="dec"
								aria-label="<?php echo esc_attr( gayanes_t( 'Decrease quantity' ) ); ?>"
							>&minus;</button>
							<span class="gk-stepper__val" aria-live="polite"><?php echo esc_html( (string) $current_qty ); ?></span>
							<button
								type="button"
								class="gk-stepper__btn"
								data-gk-action="inc"
								aria-label="<?php echo esc_attr( gayanes_t( 'Increase quantity' ) ); ?>"
							>+</button>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</article>
	<?php
}

