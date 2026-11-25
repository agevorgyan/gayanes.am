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

	$deal_choices = array( 0 => __( 'Auto (First On-Sale or Featured Product)', 'gayanes-v2' ) );
	if ( function_exists( 'wc_get_products' ) ) {
		foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => 50 ) ) as $p ) {
			$deal_choices[ $p->get_id() ] = $p->get_name();
		}
	}

	$wp_customize->add_setting(
		'gv2_daily_deal_product_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'gv2_daily_deal_product_id',
		array(
			'label'   => __( 'Product of the Day (Homepage Promo)', 'gayanes-v2' ),
			'section' => 'gv2_brand_section',
			'type'    => 'select',
			'choices' => $deal_choices,
		)
	);

	foreach ( array(
		'gv2_home_hero_image_id'   => __( 'Homepage Hero Image', 'gayanes-kitchen' ),
		'gv2_about_hero_image_id'  => __( 'About Us Hero Image', 'gayanes-kitchen' ),
		'gv2_about_story_image_id' => __( 'About Us Story Image', 'gayanes-kitchen' ),
	) as $setting_key => $label ) {
		$wp_customize->add_setting(
			$setting_key,
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				$setting_key,
				array(
					'label'     => $label,
					'section'   => 'gv2_brand_section',
					'mime_type' => 'image',
				)
			)
		);
	}
}
add_action( 'customize_register', 'gv2_customize_register' );

/**
 * Replace the default WordPress logo on wp-login.php with the site logo.
 */
function gv2_login_logo_styles(): void {
	$logo_url = esc_url( GV2_URI . '/assets/images/logo-dark.png' );
	$css      = sprintf(
		'#login h1 a, .login h1 a {
			background-image: url("%s");
			background-size: contain;
			background-position: center center;
			background-repeat: no-repeat;
			width: 220px;
			height: 98px;
		}',
		$logo_url
	);

	wp_add_inline_style( 'login', $css );
}
add_action( 'login_enqueue_scripts', 'gv2_login_logo_styles' );

/**
 * Point the wp-login.php logo link to the site homepage.
 *
 * @return string
 */
function gv2_login_logo_url(): string {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'gv2_login_logo_url' );

/**
 * Set the wp-login.php logo accessible title to the site name.
 *
 * @return string
 */
function gv2_login_logo_text(): string {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'gv2_login_logo_text' );

/**
 * Ensure custom cron intervals used by Action Scheduler & Yoast SEO are always registered.
 *
 * @param array<string, array<string, mixed>> $schedules Existing cron schedules.
 * @return array<string, array<string, mixed>>
 */
function gv2_register_missing_cron_schedules( array $schedules ): array {
	if ( ! isset( $schedules['every_minute'] ) ) {
		$schedules['every_minute'] = array(
			'interval' => 60,
			'display'  => 'Every Minute',
		);
	}
	if ( ! isset( $schedules['fifteen_minutes'] ) ) {
		$schedules['fifteen_minutes'] = array(
			'interval' => 900,
			'display'  => 'Every 15 Minutes',
		);
	}
	return $schedules;
}
add_filter( 'cron_schedules', 'gv2_register_missing_cron_schedules', 5 );

/**
 * Automatically transliterate Armenian and Cyrillic (Russian) characters to Latin in WordPress slugs.
 *
 * @param string $title     Sanitized title.
 * @param string $raw_title Raw title before sanitization.
 * @param string $context   Context ('save' for slugs, 'display' otherwise).
 * @return string
 */
function gv2_transliterate_armenian_slug( string $title, string $raw_title = '', string $context = 'display' ): string {
	if ( 'save' !== $context ) {
		return $title;
	}

	$source = '' !== $raw_title ? $raw_title : urldecode( $title );
	if ( ! preg_match( '/[\x{0530}-\x{058F}\x{0400}-\x{04FF}]/u', $source ) ) {
		return $title;
	}

	$str = mb_strtolower( $source, 'UTF-8' );
	$str = str_replace( 'ու', 'u', $str );
	$str = preg_replace( '/(^|[\s\-_])ե/u', '$1ye', $str ) ?? $str;

	$map = array(
		// Armenian
		'ա' => 'a', 'բ' => 'b', 'գ' => 'g', 'դ' => 'd', 'ե' => 'e', 'զ' => 'z',
		'է' => 'e', 'ը' => 'y', 'թ' => 't', 'ժ' => 'zh', 'ի' => 'i', 'լ' => 'l',
		'խ' => 'kh', 'ծ' => 'ts', 'կ' => 'k', 'հ' => 'h', 'ձ' => 'dz', 'ղ' => 'gh',
		'ճ' => 'ch', 'մ' => 'm', 'յ' => 'y', 'ն' => 'n', 'շ' => 'sh', 'ո' => 'o',
		'չ' => 'ch', 'պ' => 'p', 'ջ' => 'j', 'ռ' => 'r', 'ս' => 's', 'վ' => 'v',
		'տ' => 't', 'ր' => 'r', 'ց' => 'ts', 'ւ' => 'v', 'փ' => 'p', 'ք' => 'k',
		'և' => 'ev', 'օ' => 'o', 'ֆ' => 'f',
		// Russian (Cyrillic)
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
		'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
		'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
		'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts',
		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
		'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
	);

	return strtr( $str, $map );
}
add_filter( 'sanitize_title', 'gv2_transliterate_armenian_slug', 9, 3 );

