<?php
/**
 * Enqueue styles, fonts, deferred scripts, resource hints, and favicon assets.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output resource hints (preconnect) and LCP image preload in <head>.
 */
function gv2_output_resource_hints(): void {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com" />' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />' . "\n";

	if ( is_front_page() && function_exists( 'wc_get_product' ) ) {
		$hero_img_id = absint( get_theme_mod( 'gv2_home_hero_image_id', 0 ) );
		if ( $hero_img_id > 0 ) {
			$src = wp_get_attachment_image_url( $hero_img_id, 'large' );
			if ( $src ) {
				echo '<link rel="preload" as="image" href="' . esc_url( $src ) . '" fetchpriority="high" />' . "\n";
			}
		} else {
			$deal_id = absint( get_theme_mod( 'gv2_daily_deal_product_id', 0 ) );
			if ( $deal_id > 0 && function_exists( 'pll_get_post' ) ) {
				$tr = (int) pll_get_post( $deal_id );
				if ( $tr > 0 ) {
					$deal_id = $tr;
				}
			}
			$deal_product = $deal_id > 0 ? wc_get_product( $deal_id ) : null;
			if ( ! $deal_product instanceof WC_Product && function_exists( 'wc_get_product_ids_on_sale' ) ) {
				$sale_ids = wc_get_product_ids_on_sale();
				if ( ! empty( $sale_ids ) ) {
					$deal_product = wc_get_product( (int) reset( $sale_ids ) );
				}
			}
			if ( $deal_product instanceof WC_Product && $deal_product->get_image_id() ) {
				$src = wp_get_attachment_image_url( (int) $deal_product->get_image_id(), 'gv2-product-portrait' );
				if ( $src ) {
					echo '<link rel="preload" as="image" href="' . esc_url( $src ) . '" fetchpriority="high" />' . "\n";
				}
			}
		}
	}
}
add_action( 'wp_head', 'gv2_output_resource_hints', 1 );

/**
 * Enqueue GayanesV2 CSS and JS cleanly with minified assets when available.
 */
function gv2_enqueue_assets(): void {
	wp_enqueue_style(
		'gv2-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&family=Noto+Sans+Armenian:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	$css_rel = file_exists( GV2_DIR . '/assets/css/main.min.css' ) ? '/assets/css/main.min.css' : '/assets/css/main.css';
	wp_enqueue_style(
		'gv2-main',
		GV2_URI . $css_rel,
		array(),
		GV2_VERSION
	);

	$js_rel = file_exists( GV2_DIR . '/assets/js/app.min.js' ) ? '/assets/js/app.min.js' : '/assets/js/app.js';
	wp_enqueue_script(
		'gv2-app',
		GV2_URI . $js_rel,
		array(),
		GV2_VERSION,
		true
	);

	$nonce = wp_create_nonce( 'gayanes_cart_nonce' );

	wp_localize_script(
		'gv2-app',
		'gv2Config',
		array(
			'ajaxUrl'               => add_query_arg( 'lang', gv2_current_lang(), admin_url( 'admin-ajax.php' ) ),
			'nonce'                 => $nonce,
			'lang'                  => gv2_current_lang(),
			'freeDeliveryThreshold' => function_exists( 'gv2_get_free_shipping_threshold' ) ? gv2_get_free_shipping_threshold() : 15000,
			'currencySymbol'        => '֏',
			'i18n'                  => array(
				'add'            => gv2_t( 'Add' ),
				'addedToCart'    => gv2_t( 'Added to cart' ),
				'updatedQty'     => gv2_t( 'Updated quantity' ),
				'errorCart'      => gv2_t( 'Could not update cart' ),
				'freeUnlocked'   => gv2_t( 'Free delivery unlocked!' ),
				'addMoreForFree' => gv2_t( 'Add %s more for free delivery' ),
				'loadingMore'    => gv2_t( 'Loading more dishes...' ),
				'allLoaded'      => gv2_t( 'All dishes loaded' ),
				'decreaseQty'    => gv2_t( 'Decrease quantity' ),
				'increaseQty'    => gv2_t( 'Increase quantity' ),
				'removeItem'     => gv2_t( 'Remove item' ),
				'emptyCart'      => gv2_t( 'Your cart is empty' ),
				'phoneInvalid'   => gv2_t( 'Please enter a valid phone number (e.g. +374 99 12 34 56).' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'gv2_enqueue_assets' );

/**
 * Load Google Fonts and non-critical WooCommerce stylesheets asynchronously.
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Stylesheet handle.
 */
function gv2_async_noncritical_styles( string $html, string $handle ): string {
	if ( is_admin() ) {
		return $html;
	}

	$async_handles = array( 'gv2-fonts' );

	$is_commerce_flow = ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_account_page' ) && is_account_page() )
		|| ( function_exists( 'is_product' ) && is_product() );

	if ( ! $is_commerce_flow ) {
		$async_handles = array_merge(
			$async_handles,
			array(
				'woocommerce-layout',
				'woocommerce-smallscreen',
				'woocommerce-general',
				'brands-styles',
				'wc-blocks-style',
			)
		);
	}

	if ( in_array( $handle, $async_handles, true ) && str_contains( $html, "media='all'" ) ) {
		$async = str_replace(
			"media='all'",
			"media='print' onload=\"this.media='all'\"",
			$html
		);
		return $async . '<noscript>' . $html . '</noscript>';
	}

	return $html;
}
add_filter( 'style_loader_tag', 'gv2_async_noncritical_styles', 10, 2 );

/**
 * Add defer attribute to theme script and non-checkout WooCommerce/jQuery scripts.
 *
 * @param string $tag    Script HTML tag.
 * @param string $handle Script handle.
 */
function gv2_defer_scripts( string $tag, string $handle ): string {
	if ( is_admin() ) {
		return $tag;
	}

	$defer_handles = array( 'gv2-app' );

	$is_commerce_flow = ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_account_page' ) && is_account_page() );

	if ( ! $is_commerce_flow ) {
		$defer_handles = array_merge(
			$defer_handles,
			array(
				'jquery-core',
				'jquery-migrate',
				'wc-jquery-blockui',
				'js-cookie',
				'woocommerce',
				'wc-cart-fragments',
				'sourcebuster-js',
				'wc-order-attribution',
				'wc-add-to-cart',
			)
		);
	}

	if ( in_array( $handle, $defer_handles, true ) && ! str_contains( $tag, ' defer' ) ) {
		return str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'gv2_defer_scripts', 10, 2 );

/**
 * Output Gayane's Kitchen custom Favicon and Apple Touch Icon.
 */
function gv2_output_favicons(): void {
	$favicon_url = esc_url( GV2_URI . '/assets/images/favicon.png' );
	echo '<link rel="icon" type="image/png" sizes="512x512" href="' . $favicon_url . '" />' . "\n";
	echo '<link rel="apple-touch-icon" sizes="180x180" href="' . $favicon_url . '" />' . "\n";
	echo '<meta name="theme-color" content="#304B39" />' . "\n";
}
add_action( 'wp_head', 'gv2_output_favicons', 2 );
