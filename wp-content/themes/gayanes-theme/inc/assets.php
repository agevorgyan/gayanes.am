<?php
/**
 * Enqueue minimal CSS & vanilla JS assets cleanly.
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue theme stylesheet and cart/UI script.
 */
function gayanes_theme_enqueue_assets(): void {
	wp_enqueue_style(
		'gayanes-main',
		GAYANES_THEME_URI . '/assets/css/main.css',
		array(),
		GAYANES_THEME_VERSION
	);

	wp_enqueue_script(
		'gayanes-cart',
		GAYANES_THEME_URI . '/assets/js/cart.js',
		array(),
		GAYANES_THEME_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	$initial_cart = array(
		'count'        => 0,
		'subtotal'     => '',
		'total'        => '',
		'items'        => array(),
		'quantities'   => array(),
		'cart_url'     => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ),
		'checkout_url' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' ),
	);

	if ( class_exists( 'Gayanes_Core\Ajax_Cart' ) && function_exists( 'WC' ) ) {
		$initial_cart = Gayanes_Core\Ajax_Cart::get_cart_payload();
	}

	wp_localize_script(
		'gayanes-cart',
		'gayanesCartConfig',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'gayanes_cart_nonce' ),
			'initialCart' => $initial_cart,
			'i18n'        => array(
				'addToCart'   => gayanes_t( 'Add to Cart' ),
				'added'       => gayanes_t( 'Added to cart' ),
				'emptyCart'   => gayanes_t( 'Your cart is empty' ),
				'decreaseQty' => gayanes_t( 'Decrease quantity' ),
				'increaseQty' => gayanes_t( 'Increase quantity' ),
				'remove'      => gayanes_t( 'Remove' ),
				'subtotal'    => gayanes_t( 'Subtotal' ),
				'total'       => gayanes_t( 'Total' ),
				'viewCart'    => gayanes_t( 'View Cart' ),
				'checkout'    => gayanes_t( 'Checkout' ),
				'errorGeneric'=> gayanes_t( 'Something went wrong. Please try again.' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'gayanes_theme_enqueue_assets', 20 );

/**
 * Inline critical above-the-fold CSS variables to prevent FOUC and keep CLS <= 0.1.
 */
function gayanes_theme_inline_critical_css(): void {
	?>
	<style id="gayanes-critical-css">
		:root{--gk-bg:#F7F6F1;--gk-surface:#FFFFFF;--gk-text:#242923;--gk-primary:#304B39;--gk-secondary:#A8AD98;--gk-beige:#EAE6DC;--gk-border:#E2DFD5;--gk-danger:#B33A3A;--gk-radius:12px}
		html{box-sizing:border-box;background:var(--gk-bg);color:var(--gk-text);-webkit-text-size-adjust:100%}
		*,*::before,*::after{box-sizing:inherit}
		body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Noto Sans Armenian","Noto Sans",sans-serif;line-height:1.55;background:var(--gk-bg);color:var(--gk-text)}
		img{max-width:100%;height:auto;display:block}
	</style>
	<?php
}
add_action( 'wp_head', 'gayanes_theme_inline_critical_css', 5 );

