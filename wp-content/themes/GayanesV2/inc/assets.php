<?php
/**
 * Enqueue styles, fonts, deferred scripts, and favicon assets.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue GayanesV2 CSS and JS cleanly with zero bloat.
 */
function gv2_enqueue_assets(): void {
	wp_enqueue_style(
		'gv2-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&family=Noto+Sans+Armenian:wght@400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'gv2-main',
		GV2_URI . '/assets/css/main.css',
		array( 'gv2-fonts' ),
		GV2_VERSION
	);

	wp_enqueue_script(
		'gv2-app',
		GV2_URI . '/assets/js/app.js',
		array(),
		GV2_VERSION,
		true
	);

	$nonce = wp_create_nonce( 'gayanes_cart_nonce' );

	wp_localize_script(
		'gv2-app',
		'gv2Config',
		array(
			'ajaxUrl'               => admin_url( 'admin-ajax.php' ),
			'nonce'                 => $nonce,
			'lang'                  => gv2_current_lang(),
			'freeDeliveryThreshold' => 15000,
			'currencySymbol'        => '֏',
			'i18n'                  => array(
				'add'               => gv2_t( 'Add' ),
				'addedToCart'       => gv2_t( 'Added to cart' ),
				'updatedQty'        => gv2_t( 'Updated quantity' ),
				'errorCart'         => gv2_t( 'Could not update cart' ),
				'freeUnlocked'      => gv2_t( 'Free delivery unlocked!' ),
				'addMoreForFree'    => gv2_t( 'Add %s more for free delivery' ),
				'loadingMore'       => gv2_t( 'Loading more dishes...' ),
				'allLoaded'         => gv2_t( 'All dishes loaded' ),
				'decreaseQty'       => gv2_t( 'Decrease quantity' ),
				'increaseQty'       => gv2_t( 'Increase quantity' ),
				'removeItem'        => gv2_t( 'Remove item' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'gv2_enqueue_assets' );

/**
 * Add defer attribute to theme script for fast First Contentful Paint.
 *
 * @param string $tag    Script HTML tag.
 * @param string $handle Script handle.
 */
function gv2_defer_scripts( string $tag, string $handle ): string {
	if ( 'gv2-app' === $handle && ! str_contains( $tag, ' defer' ) ) {
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

