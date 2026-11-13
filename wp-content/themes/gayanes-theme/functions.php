<?php
/**
 * Gayane's Kitchen Theme bootstrap.
 *
 * Modular architecture: delegates setup, assets, template helpers,
 * and WooCommerce integration to dedicated files under /inc.
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

define( 'GAYANES_THEME_VERSION', '1.0.0' );
define( 'GAYANES_THEME_DIR', get_template_directory() );
define( 'GAYANES_THEME_URI', get_template_directory_uri() );

require_once GAYANES_THEME_DIR . '/inc/setup.php';
require_once GAYANES_THEME_DIR . '/inc/assets.php';
require_once GAYANES_THEME_DIR . '/inc/template-tags.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once GAYANES_THEME_DIR . '/inc/woocommerce.php';
}

