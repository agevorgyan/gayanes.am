<?php
/**
 * WooCommerce integration: 2:3 portrait product cards, instant +/- cart steppers,
 * Infinite Scroll AJAX endpoint, and Mini-Cart synchronization.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load bundled Armenian (hy) translation for WooCommerce when active.
 */
add_filter(
	'load_textdomain_mofile',
	static function ( string $mofile, string $domain ): string {
		if ( 'woocommerce' !== $domain ) {
			return $mofile;
		}
		if ( str_ends_with( $mofile, '-hy.mo' ) ) {
			$bundled = GV2_DIR . '/languages/woocommerce-hy.mo';
			if ( file_exists( $bundled ) ) {
				return $bundled;
			}
		}
		return $mofile;
	},
	10,
	2
);

add_action(
	'init',
	static function (): void {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		if ( 'hy' !== get_locale() ) {
			return;
		}
		$bundled = GV2_DIR . '/languages/woocommerce-hy.mo';
		if ( file_exists( $bundled ) ) {
			unload_textdomain( 'woocommerce' );
			load_textdomain( 'woocommerce', $bundled, 'hy' );
		}
	},
	5
);

/**
 * Format all WooCommerce prices as "500 ֏" (symbol on right with space, no trailing .00).
 */
add_filter(
	'woocommerce_currency_symbol',
	static function ( string $symbol, string $currency ): string {
		return 'AMD' === $currency ? '֏' : $symbol;
	},
	20,
	2
);

add_filter(
	'woocommerce_price_format',
	static function ( string $format, string $currency_pos ): string {
		return '%2$s&nbsp;%1$s';
	},
	20,
	2
);

add_filter( 'woocommerce_price_trim_zeros', '__return_true' );
add_filter( 'pre_option_woocommerce_currency_pos', static fn(): string => 'right_space' );
add_filter( 'pre_option_woocommerce_price_num_decimals', static fn(): int => 0 );

/**
 * Server-side phone validation: Armenia (+374 + 8 digits) or valid international E.164.
 */
add_action(
	'woocommerce_checkout_process',
	static function (): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$raw = isset( $_POST['billing_phone'] ) ? sanitize_text_field( (string) wp_unslash( $_POST['billing_phone'] ) ) : '';
		$clean = preg_replace( '/[^\d+]/', '', $raw );

		if ( '' === $clean ) {
			return;
		}

		if ( str_starts_with( $clean, '+374' ) ) {
			if ( ! preg_match( '/^\+374\d{8}$/', $clean ) ) {
				wc_add_notice( gv2_t( 'Please enter a valid phone number (e.g. +374 99 12 34 56).' ), 'error' );
			}
		} elseif ( ! preg_match( '/^\+\d{8,15}$/', $clean ) ) {
			wc_add_notice( gv2_t( 'Please enter a valid phone number (e.g. +374 99 12 34 56).' ), 'error' );
		}
	}
);

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

add_action(
	'woocommerce_before_main_content',
	static function (): void {
		echo '<main id="primary" class="gv2-main gv2-container">';
		gv2_breadcrumbs();
	},
	10
);

add_action(
	'woocommerce_after_main_content',
	static function (): void {
		echo '</main>';
	},
	10
);

/**
 * Get product weight or portion label.
 *
 * @param WC_Product $product WooCommerce product.
 */
function gv2_get_product_weight_label( WC_Product $product ): string {
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
 * Render a 2:3 portrait product card with instant Add -> [ - qty + ] stepper.
 *
 * @param WC_Product $product WooCommerce product.
 */
function gv2_render_product_card( WC_Product $product ): void {
	$product_id   = $product->get_id();
	$is_variable  = $product->is_type( 'variable' );
	$in_stock     = $product->is_in_stock();
	$purchasable  = $product->is_purchasable();
	$max_qty      = $product->get_max_purchase_quantity();
	$weight_label = gv2_get_product_weight_label( $product );
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
		class="gv2-card<?php echo ! $in_stock ? ' is-out-of-stock' : ''; ?>"
		data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
		data-product-type="<?php echo esc_attr( $product->get_type() ); ?>"
		data-max-qty="<?php echo esc_attr( (string) $max_qty ); ?>"
	>
		<a href="<?php echo esc_url( $permalink ); ?>" class="gv2-card__media">
			<?php
			if ( $image_id ) {
				echo wp_get_attachment_image(
					$image_id,
					'gv2-product-portrait',
					false,
					array(
						'class'    => 'gv2-card__img',
						'loading'  => 'lazy',
						'decoding' => 'async',
					)
				);
			} else {
				echo '<img class="gv2-card__img" src="' . esc_url( wc_placeholder_img_src( 'gv2-product-portrait' ) ) . '" alt="' . esc_attr( $product->get_name() ) . '" loading="lazy" decoding="async" />';
			}
			?>
			<?php if ( '' !== $weight_label ) : ?>
				<span class="gv2-card__weight-pill"><?php echo esc_html( $weight_label ); ?></span>
			<?php endif; ?>
			<?php if ( $product->is_on_sale() ) : ?>
				<span class="gv2-badge gv2-badge--sale"><?php echo esc_html( gv2_t( 'Sale' ) ); ?></span>
			<?php endif; ?>
		</a>

		<div class="gv2-card__body">
			<h3 class="gv2-card__title">
				<a href="<?php echo esc_url( $permalink ); ?>">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h3>

			<div class="gv2-card__footer">
				<div class="gv2-card__price">
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
				</div>

				<div class="gv2-card__actions">
					<?php if ( ! $in_stock || ! $purchasable ) : ?>
						<span class="gv2-btn gv2-btn--disabled"><?php echo esc_html( gv2_t( 'Out of Stock' ) ); ?></span>
					<?php elseif ( $is_variable ) : ?>
						<a href="<?php echo esc_url( $permalink ); ?>" class="gv2-btn gv2-btn--glass">
							<?php echo esc_html( gv2_t( 'Select Options' ) ); ?>
						</a>
					<?php else : ?>
						<button
							type="button"
							class="gv2-btn gv2-btn--primary gv2-add-btn<?php echo $current_qty > 0 ? ' is-hidden' : ''; ?>"
							data-gv2-add="<?php echo esc_attr( (string) $product_id ); ?>"
						>
							<span aria-hidden="true">+</span> <?php echo esc_html( gv2_t( 'Add' ) ); ?>
						</button>

						<div
							class="gv2-qty<?php echo $current_qty <= 0 ? ' is-hidden' : ''; ?>"
							data-gv2-qty-control
							data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
							data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
						>
							<button
								type="button"
								class="gv2-qty__btn"
								data-gv2-qty-step="-1"
								aria-label="<?php echo esc_attr( gv2_t( 'Decrease quantity' ) ); ?>"
							>&minus;</button>
							<span class="gv2-qty__val" data-gv2-qty-val><?php echo esc_html( (string) max( 0, $current_qty ) ); ?></span>
							<button
								type="button"
								class="gv2-qty__btn"
								data-gv2-qty-step="1"
								aria-label="<?php echo esc_attr( gv2_t( 'Increase quantity' ) ); ?>"
							>+</button>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</article>
	<?php
}

/**
 * Infinite Scroll AJAX endpoint for Shop, Category, and Search archives.
 */
function gv2_ajax_load_products(): void {
	$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	$per_page = isset( $_GET['per_page'] ) ? min( 24, max( 4, absint( $_GET['per_page'] ) ) ) : 12;
	$category = isset( $_GET['category'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['category'] ) ) : '';
	$orderby  = isset( $_GET['orderby'] ) ? sanitize_key( (string) wp_unslash( $_GET['orderby'] ) ) : 'menu_order';
	$search   = isset( $_GET['s'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['s'] ) ) : '';

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $paged,
	);

	if ( '' !== $search ) {
		$args['s'] = $search;
	}

	if ( '' !== $category ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $category,
			),
		);
	}

	switch ( $orderby ) {
		case 'popularity':
			$args['meta_key'] = 'total_sales';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'date':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
		case 'price':
			$args['meta_key'] = '_price';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'ASC';
			break;
		case 'price-desc':
			$args['meta_key'] = '_price';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		default:
			$args['orderby'] = array( 'menu_order' => 'ASC', 'title' => 'ASC' );
			break;
	}

	$query = new WP_Query( $args );

	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$product = wc_get_product( get_the_ID() );
			if ( $product instanceof WC_Product ) {
				gv2_render_product_card( $product );
			}
		}
	}
	wp_reset_postdata();
	$html = ob_get_clean();

	wp_send_json_success(
		array(
			'html'      => $html,
			'has_more'  => $paged < (int) $query->max_num_pages,
			'next_page' => $paged + 1,
			'max_pages' => (int) $query->max_num_pages,
		)
	);
}
add_action( 'wp_ajax_gv2_load_products', 'gv2_ajax_load_products' );
add_action( 'wp_ajax_nopriv_gv2_load_products', 'gv2_ajax_load_products' );


/**
 * Render an iOS 27 Liquid Glass Profile Header Card at the top of /my-account/.
 */
add_action(
	'woocommerce_before_account_navigation',
	static function (): void {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$user     = wp_get_current_user();
		$name     = trim( $user->display_name ?: $user->user_login );
		$initials = mb_strtoupper( mb_substr( $name, 0, 2 ) );
		?>
		<div class="gv2-account-hero gv2-glass-card">
			<div class="gv2-account-hero__avatar" aria-hidden="true"><?php echo esc_html( $initials ); ?></div>
			<div class="gv2-account-hero__info">
				<h2 class="gv2-account-hero__name"><?php echo esc_html( $name ); ?></h2>
				<p class="gv2-account-hero__email"><?php echo esc_html( $user->user_email ); ?></p>
			</div>
			<a href="<?php echo esc_url( wc_logout_url() ); ?>" class="gv2-btn gv2-btn--glass gv2-btn--sm gv2-account-hero__logout">
				<?php echo esc_html( __( 'Log out', 'woocommerce' ) ); ?>
			</a>
		</div>
		<?php
	}
);
