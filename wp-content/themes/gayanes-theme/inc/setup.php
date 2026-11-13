<?php
/**
 * Theme setup, supports, and navigation menus.
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Configure theme defaults and register support for WordPress/WooCommerce features.
 */
function gayanes_theme_setup(): void {
	load_theme_textdomain( 'gayanes-theme', GAYANES_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// WooCommerce support without heavy gallery zoom/slider bloat.
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 480,
		'single_image_width'    => 760,
		'product_grid'          => array(
			'default_rows'    => 4,
			'min_rows'        => 1,
			'default_columns' => 4,
			'min_columns'     => 2,
			'max_columns'     => 4,
		),
	) );
	add_theme_support( 'wc-product-gallery-lightbox' );

	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'gayanes-theme' ),
		'footer'  => __( 'Footer Navigation', 'gayanes-theme' ),
	) );
}
add_action( 'after_setup_theme', 'gayanes_theme_setup' );

