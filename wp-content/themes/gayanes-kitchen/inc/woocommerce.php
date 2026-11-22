<?php
/**
 * WooCommerce integration: 2:3 portrait product cards, instant +/- cart steppers,
 * Infinite Scroll AJAX endpoint, and Mini-Cart synchronization.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load bundled Armenian (hy) and Russian (ru_RU) translations for WooCommerce when active.
 */
add_filter(
	'load_textdomain_mofile',
	static function ( string $mofile, string $domain ): string {
		if ( 'woocommerce' !== $domain ) {
			return $mofile;
		}
		foreach ( array( 'hy', 'ru_RU' ) as $locale ) {
			if ( str_ends_with( $mofile, "-{$locale}.mo" ) ) {
				$bundled = GV2_DIR . "/languages/woocommerce-{$locale}.mo";
				if ( file_exists( $bundled ) ) {
					return $bundled;
				}
			}
		}
		return $mofile;
	},
	10,
	2
);

add_filter(
	'override_load_textdomain',
	static function ( bool $override, string $domain ): bool {
		if ( 'woocommerce' === $domain && ! ( is_admin() && ! wp_doing_ajax() ) ) {
			$locale = get_locale();
			if ( str_starts_with( $locale, 'en' ) ) {
				return true;
			}
		}
		return $override;
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
		$locale = get_locale();
		if ( str_starts_with( $locale, 'en' ) ) {
			unload_textdomain( 'woocommerce' );
			return;
		}
		if ( ! in_array( $locale, array( 'hy', 'ru_RU' ), true ) ) {
			return;
		}
		$bundled = GV2_DIR . "/languages/woocommerce-{$locale}.mo";
		$target  = file_exists( $bundled ) ? $bundled : WP_LANG_DIR . "/plugins/woocommerce-{$locale}.mo";
		if ( file_exists( $target ) ) {
			unload_textdomain( 'woocommerce' );
			load_textdomain( 'woocommerce', $target, $locale );
		}
	},
	20
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
	$qty_rules    = apply_filters(
		'gayanes_product_qty_rules',
		array(
			'min'  => 1,
			'step' => 1,
		),
		$product_id
	);
	$min_qty      = max( 1, (int) ( $qty_rules['min'] ?? 1 ) );
	$step_qty     = max( 1, (int) ( $qty_rules['step'] ?? 1 ) );
	$weight_label = gv2_get_product_weight_label( $product );
	$permalink    = $product->get_permalink();

	$current_qty   = 0;
	$cart_item_key = '';
	if ( function_exists( 'WC' ) && WC()->cart && ! $is_variable ) {
		foreach ( WC()->cart->get_cart() as $key => $item ) {
			$item_pid = (int) $item['product_id'];
			$matched  = ( $item_pid === $product_id );
			if ( ! $matched && function_exists( 'pll_get_post' ) ) {
				$matched = ( (int) pll_get_post( $item_pid ) === $product_id );
			}
			if ( $matched && 0 === (int) $item['variation_id'] ) {
				$current_qty   = (int) $item['quantity'];
				$cart_item_key = (string) $key;
				break;
			}
		}
	}

	$image_id      = $product->get_image_id();
	$display_title = $product->get_name();
	if ( '' !== $weight_label ) {
		$cleaned = preg_replace( '/\s*\(?\d+(?:[.,]\d+)?\s*(?:գ|կգ|г|кг|g|kg)\)?\s*$/ui', '', $display_title );
		if ( is_string( $cleaned ) && '' !== trim( $cleaned ) ) {
			$display_title = trim( $cleaned );
		}
	}
	?>
	<article
		class="gv2-card<?php echo ! $in_stock ? ' is-out-of-stock' : ''; ?>"
		data-product-id="<?php echo esc_attr( (string) $product_id ); ?>"
		data-product-type="<?php echo esc_attr( $product->get_type() ); ?>"
		data-min-qty="<?php echo esc_attr( (string) $min_qty ); ?>"
		data-step-qty="<?php echo esc_attr( (string) $step_qty ); ?>"
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
					<?php echo esc_html( $display_title ); ?>
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

	if ( function_exists( 'gm_plugin' ) && isset( gm_plugin()->translations ) ) {
		$current_lang = gv2_current_lang();
		$exclude_ids  = gm_plugin()->translations->get_object_ids_not_in_language( $current_lang, 'post' );
		if ( ! empty( $exclude_ids ) ) {
			$args['post__not_in'] = $exclude_ids;
		}
	}

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
			$args['orderby'] = array( 'menu_order' => 'ASC', 'title' => 'ASC', 'ID' => 'ASC' );
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
 * AJAX Live Product Search (triggers from 2+ characters, language-aware).
 */
function gv2_ajax_live_search(): void {
	$q = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	if ( '' === $q && isset( $_POST['q'] ) ) {
		$q = sanitize_text_field( wp_unslash( $_POST['q'] ) );
	}
	$q = trim( $q );

	if ( mb_strlen( $q, 'UTF-8' ) < 2 ) {
		wp_send_json_success( array( 'items' => array(), 'total' => 0 ) );
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			's'              => $q,
			'posts_per_page' => 8,
			'orderby'        => 'relevance',
		)
	);

	$items = array();
	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$product = wc_get_product( $post->ID );
			if ( ! $product instanceof WC_Product ) {
				continue;
			}
			$img_url = wp_get_attachment_image_url( (int) $product->get_image_id(), 'thumbnail' );
			if ( ! $img_url && function_exists( 'wc_placeholder_img_src' ) ) {
				$img_url = wc_placeholder_img_src( 'thumbnail' );
			}
			$price_num = (string) $product->get_price();
			$price_txt = '' !== $price_num ? number_format_i18n( (float) $price_num ) . ' ֏' : '';
			$items[]   = array(
				'id'    => $product->get_id(),
				'name'  => html_entity_decode( $product->get_name(), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
				'url'   => $product->get_permalink(),
				'price' => $price_txt,
				'image' => $img_url ?: '',
			);
		}
	}

	wp_send_json_success(
		array(
			'items' => $items,
			'total' => (int) $query->found_posts,
		)
	);
}
add_action( 'wp_ajax_gv2_live_search', 'gv2_ajax_live_search' );
add_action( 'wp_ajax_nopriv_gv2_live_search', 'gv2_ajax_live_search' );


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

/**
 * Translate cart item names and permalinks into the active language on Cart & Checkout.
 */
add_filter(
	'woocommerce_cart_item_name',
	static function ( string $name, array $cart_item ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $name;
		}
		if ( empty( $cart_item['product_id'] ) || ! function_exists( 'pll_get_post' ) ) {
			return $name;
		}
		$pid   = (int) $cart_item['product_id'];
		$tr_id = (int) pll_get_post( $pid );
		if ( $tr_id <= 0 || $tr_id === $pid ) {
			return $name;
		}
		$tr_product = wc_get_product( $tr_id );
		if ( ! $tr_product instanceof WC_Product ) {
			return $name;
		}
		if ( str_contains( $name, '<a ' ) ) {
			return sprintf( '<a href="%s">%s</a>', esc_url( $tr_product->get_permalink() ), esc_html( $tr_product->get_name() ) );
		}
		return esc_html( $tr_product->get_name() );
	},
	10,
	2
);

add_filter(
	'woocommerce_cart_item_permalink',
	static function ( string $permalink, array $cart_item ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $permalink;
		}
		if ( empty( $cart_item['product_id'] ) || ! function_exists( 'pll_get_post' ) ) {
			return $permalink;
		}
		$pid   = (int) $cart_item['product_id'];
		$tr_id = (int) pll_get_post( $pid );
		if ( $tr_id > 0 && $tr_id !== $pid ) {
			$tr_permalink = get_permalink( $tr_id );
			if ( $tr_permalink ) {
				return $tr_permalink;
			}
		}
		return $permalink;
	},
	10,
	2
);

/**
 * Translate shared WooCommerce system page titles (Shop, Cart, Checkout, My Account).
 */
function gv2_translate_wc_system_page_title( string $title, int $post_id = 0 ): string {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $title;
	}
	if ( $post_id <= 0 ) {
		return $title;
	}
	$map = array(
		(int) wc_get_page_id( 'shop' )                  => 'Shop',
		(int) wc_get_page_id( 'cart' )                  => 'Cart',
		(int) wc_get_page_id( 'checkout' )              => 'Checkout',
		(int) wc_get_page_id( 'myaccount' )             => 'My Account',
		(int) wc_get_page_id( 'terms' )                 => 'Terms & Conditions',
		(int) get_option( 'wp_page_for_privacy_policy' ) => 'Privacy Policy',
	);
	if ( isset( $map[ $post_id ] ) ) {
		return gv2_t( $map[ $post_id ] );
	}
	return $title;
}

add_filter(
	'the_title',
	static function ( string $title, int $post_id = 0 ): string {
		return gv2_translate_wc_system_page_title( $title, $post_id );
	},
	10,
	2
);

add_filter(
	'single_post_title',
	static function ( string $title, $post ): string {
		$pid = is_object( $post ) ? (int) $post->ID : 0;
		return gv2_translate_wc_system_page_title( $title, $pid );
	},
	10,
	2
);

add_filter(
	'woocommerce_page_title',
	static function ( string $title ): string {
		if ( function_exists( 'is_shop' ) && is_shop() ) {
			return gv2_t( 'Shop' );
		}
		return $title;
	}
);

/**
 * Translate WooCommerce checkout/registration privacy policy text option.
 */
add_filter(
	'woocommerce_get_privacy_policy_text',
	static function ( string $text, string $type ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $text;
		}
		$defaults = array(
			'checkout'     => 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our [privacy_policy].',
			'registration' => 'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our [privacy_policy].',
		);
		if ( isset( $defaults[ $type ] ) && trim( $text ) === $defaults[ $type ] ) {
			return gv2_t( $defaults[ $type ] );
		}
		return $text;
	},
	10,
	2
);

/**
 * Read the active Free Shipping minimum order threshold from WooCommerce Shipping Zones.
 *
 * @return float Minimum amount in AMD (0 if free shipping has no minimum; 15000 fallback if none configured).
 */
function gv2_get_free_shipping_threshold(): float {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}

	$cached = 15000.0;
	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return $cached;
	}

	$zones   = WC_Shipping_Zones::get_zones();
	$zones[] = array(
		'shipping_methods' => WC_Shipping_Zones::get_zone( 0 )->get_shipping_methods(),
	);

	foreach ( $zones as $zone ) {
		if ( empty( $zone['shipping_methods'] ) || ! is_array( $zone['shipping_methods'] ) ) {
			continue;
		}
		foreach ( $zone['shipping_methods'] as $method ) {
			if ( ! is_object( $method ) || 'free_shipping' !== ( $method->id ?? '' ) ) {
				continue;
			}
			if ( 'yes' !== ( $method->enabled ?? 'no' ) ) {
				continue;
			}
			$requires = (string) $method->get_option( 'requires', '' );
			if ( '' === $requires || 'coupon' === $requires ) {
				$cached = 0.0;
				return $cached;
			}
			$min    = (float) $method->get_option( 'min_amount', 0 );
			$cached = max( 0.0, $min );
			return $cached;
		}
	}

	return $cached;
}

/**
 * Build localized free-delivery banner/trust text synced with WooCommerce Shipping settings.
 */
function gv2_get_free_delivery_banner_text(): string {
	$threshold = gv2_get_free_shipping_threshold();
	if ( $threshold <= 0 ) {
		return gv2_t( 'Free delivery in Yerevan' );
	}
	return sprintf(
		gv2_t( 'Free delivery in Yerevan on orders over %s AMD' ),
		number_format( (int) round( $threshold ) )
	);
}

/**
 * Translate WooCommerce shipping method labels (Free shipping, Flat rate, Local pickup).
 */
add_filter(
	'woocommerce_shipping_rate_label',
	static function ( string $label ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $label;
		}
		$normalized = strtolower( trim( $label ) );
		if ( 'free shipping' === $normalized ) {
			return gv2_t( 'Free shipping' );
		}
		if ( 'flat rate' === $normalized ) {
			return gv2_t( 'Flat rate' );
		}
		if ( 'local pickup' === $normalized ) {
			return gv2_t( 'Local pickup' );
		}
		return $label;
	}
);

/**
 * Automatically switch from paid flat rate to Free Shipping once the threshold is met.
 *
 * @param array<string, WC_Shipping_Rate> $rates Available shipping rates.
 * @return array<string, WC_Shipping_Rate>
 */
add_filter(
	'woocommerce_package_rates',
	static function ( array $rates ): array {
		$free = array();
		foreach ( $rates as $rate_id => $rate ) {
			if ( 'free_shipping' === $rate->method_id ) {
				$free[ $rate_id ] = $rate;
			}
		}
		if ( empty( $free ) ) {
			return $rates;
		}
		// Keep free shipping and non-flat-rate methods (e.g. local_pickup), hiding redundant flat_rate.
		$filtered = array();
		foreach ( $rates as $rate_id => $rate ) {
			if ( 'flat_rate' === $rate->method_id ) {
				continue;
			}
			$filtered[ $rate_id ] = $rate;
		}
		return ! empty( $filtered ) ? $filtered : $free;
	},
	100
);

/**
 * Use concise "Add" label on single product Add to Cart button.
 */
add_filter(
	'woocommerce_product_single_add_to_cart_text',
	static function (): string {
		return gv2_t( 'Add' );
	}
);

/**
 * Render - / + stepper buttons around single product quantity input.
 */
add_action(
	'woocommerce_before_quantity_input_field',
	static function (): void {
		if ( function_exists( 'is_product' ) && is_product() ) {
			echo '<button type="button" class="gv2-single-qty-btn" data-gv2-single-qty="-1" aria-label="' . esc_attr( gv2_t( 'Decrease quantity' ) ) . '">&minus;</button>';
		}
	}
);

add_action(
	'woocommerce_after_quantity_input_field',
	static function (): void {
		if ( function_exists( 'is_product' ) && is_product() ) {
			echo '<button type="button" class="gv2-single-qty-btn" data-gv2-single-qty="1" aria-label="' . esc_attr( gv2_t( 'Increase quantity' ) ) . '">+</button>';
		}
	}
);

/**
 * Remove the generic "Shipping options will be updated during checkout." estimate note on Cart.
 */
add_filter( 'woocommerce_shipping_estimate_html', '__return_empty_string' );
