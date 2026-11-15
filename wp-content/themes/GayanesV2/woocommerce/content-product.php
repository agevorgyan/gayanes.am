<?php
/**
 * Product card template part for WooCommerce loops.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( $product instanceof WC_Product && $product->is_visible() ) {
	gv2_render_product_card( $product );
}

