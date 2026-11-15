<?php
/**
 * Theme setup: supports, 2:3 portrait image sizes, menus, and Customizer options.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports, image sizes, and navigation menus.
 */
function gv2_theme_setup(): void {
	load_theme_textdomain( 'gayanes-v2', GV2_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 360,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// WooCommerce support + portrait 2:3 product thumbnails.
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_rows'    => 4,
				'default_columns' => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Dedicated 2:3 portrait image size for product cards & single product gallery.
	add_image_size( 'gv2-product-portrait', 600, 900, true );
	add_image_size( 'gv2-blog-card', 720, 480, true );

	register_nav_menus(
		array(
			'primary'           => __( 'Primary Navigation', 'gayanes-v2' ),
			'footer-categories' => __( 'Footer Categories', 'gayanes-v2' ),
			'footer-care'       => __( 'Footer Customer Care', 'gayanes-v2' ),
		)
	);
}
add_action( 'after_setup_theme', 'gv2_theme_setup' );

/**
 * Enforce 2:3 portrait aspect ratio on WooCommerce thumbnail size.
 *
 * @param array<string, mixed> $size Image size array.
 * @return array<string, mixed>
 */
function gv2_wc_portrait_thumbnail_size( array $size ): array {
	return array(
		'width'  => 600,
		'height' => 900,
		'crop'   => 1,
	);
}
add_filter( 'woocommerce_get_image_size_thumbnail', 'gv2_wc_portrait_thumbnail_size' );

/**
 * Register Customizer settings for SEO verification and Partners section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function gv2_customize_register( WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_section(
		'gv2_brand_section',
		array(
			'title'    => __( 'GayanesV2 — SEO & Partners', 'gayanes-v2' ),
			'priority' => 35,
		)
	);

	$wp_customize->add_setting(
		'gv2_yandex_verification',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'gv2_yandex_verification',
		array(
			'label'   => __( 'Yandex Webmaster Verification Code', 'gayanes-v2' ),
			'section' => 'gv2_brand_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'gv2_google_verification',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'gv2_google_verification',
		array(
			'label'   => __( 'Google Search Console Verification Code', 'gayanes-v2' ),
			'section' => 'gv2_brand_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'gv2_phone',
		array(
			'default'           => '+374 99 00 00 00',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'gv2_phone',
		array(
			'label'   => __( 'Contact Phone (Schema & Footer)', 'gayanes-v2' ),
			'section' => 'gv2_brand_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'gv2_partners_json',
		array(
			'default'           => '',
			'sanitize_callback' => 'wp_kses_post',
		)
	);
	$wp_customize->add_control(
		'gv2_partners_json',
		array(
			'label'       => __( 'Partners List (one per line: Name | Subtitle | URL)', 'gayanes-v2' ),
			'section'     => 'gv2_brand_section',
			'type'        => 'textarea',
			'description' => __( 'Leave empty to use default Armenian partner showcase.', 'gayanes-v2' ),
		)
	);
}
add_action( 'customize_register', 'gv2_customize_register' );

