<?php
/**
 * WooCommerce Product Loop Item override.
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

gayanes_render_product_card( $product );

