<?php
/**
 * Technical SEO, Meta Tags, Open Graph, Yandex/Google Verification,
 * and Rich JSON-LD Structured Data for Google & Yandex.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Initialize SEO hooks.
 */
function gv2_seo_init(): void {
	add_action( 'wp_head', 'gv2_output_seo_meta_tags', 4 );
	add_action( 'wp_head', 'gv2_output_json_ld_graph', 24 );
	add_action( 'wp_head', 'gv2_output_faq_json_ld', 25 );
	add_filter( 'wp_get_attachment_image_attributes', 'gv2_ensure_image_alt', 10, 2 );
}
add_action( 'init', 'gv2_seo_init' );

/**
 * Ensure every image has a descriptive alt attribute for Google Images & Yandex.
 *
 * @param array<string, string> $attr       Image attributes.
 * @param WP_Post               $attachment Attachment post.
 * @return array<string, string>
 */
function gv2_ensure_image_alt( array $attr, WP_Post $attachment ): array {
	if ( empty( $attr['alt'] ) ) {
		$parent_id    = (int) $attachment->post_parent;
		$attr['alt']  = $parent_id > 0
			? wp_strip_all_tags( get_the_title( $parent_id ) )
			: wp_strip_all_tags( $attachment->post_title );
		if ( '' === $attr['alt'] ) {
			$attr['alt'] = "Gayane's Kitchen — Homemade Armenian Food";
		}
	}
	return $attr;
}

/**
 * Output verification, meta description, canonical, hreflang, and Open Graph tags.
 */
function gv2_output_seo_meta_tags(): void {
	if ( is_admin() || is_feed() ) {
		return;
	}

	$yandex_code = trim( (string) get_theme_mod( 'gv2_yandex_verification', '' ) );
	$google_code = trim( (string) get_theme_mod( 'gv2_google_verification', '' ) );

	if ( '' !== $yandex_code ) {
		echo '<meta name="yandex-verification" content="' . esc_attr( $yandex_code ) . '" />' . "\n";
	}
	if ( '' !== $google_code ) {
		echo '<meta name="google-site-verification" content="' . esc_attr( $google_code ) . '" />' . "\n";
	}

	// If Yoast SEO is active, let Yoast handle canonical/description/OG to prevent duplicates.
	if ( defined( 'WPSEO_VERSION' ) ) {
		if ( function_exists( 'is_product' ) && is_product() ) {
			global $product;
			if ( $product instanceof WC_Product && '' !== $product->get_price() ) {
				echo '<meta property="product:price:amount" content="' . esc_attr( $product->get_price() ) . '" />' . "\n";
				echo '<meta property="product:price:currency" content="AMD" />' . "\n";
			}
		}
		return;
	}

	$site_name = get_bloginfo( 'name' );
	$desc      = gv2_t( 'Handcrafted daily with 100% natural ingredients and delivered chilled to your door.' );
	$title     = wp_get_document_title();
	$url       = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
	$image     = GV2_URI . '/assets/images/logo-dark.png';
	$og_type   = 'website';

	if ( is_singular() ) {
		$post_id = get_queried_object_id();
		$excerpt = get_the_excerpt( $post_id );
		if ( '' !== trim( $excerpt ) ) {
			$desc = wp_trim_words( wp_strip_all_tags( $excerpt ), 28, '...' );
		}
		$url = (string) get_permalink( $post_id );
		if ( has_post_thumbnail( $post_id ) ) {
			$thumb = get_the_post_thumbnail_url( $post_id, 'large' );
			if ( $thumb ) {
				$image = $thumb;
			}
		}
		if ( function_exists( 'is_product' ) && is_product() ) {
			$og_type = 'product';
		} elseif ( is_single() ) {
			$og_type = 'article';
		}
	}

	$lang_map = array( 'hy' => 'hy_AM', 'ru' => 'ru_RU', 'en' => 'en_US' );
	$locale   = $lang_map[ gv2_current_lang() ] ?? 'hy_AM';
	?>
	<meta name="description" content="<?php echo esc_attr( $desc ); ?>" />
	<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
	<link rel="canonical" href="<?php echo esc_url( $url ); ?>" />
	<meta property="og:locale" content="<?php echo esc_attr( $locale ); ?>" />
	<meta property="og:type" content="<?php echo esc_attr( $og_type ); ?>" />
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>" />
	<meta property="og:description" content="<?php echo esc_attr( $desc ); ?>" />
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>" />
	<meta property="og:site_name" content="<?php echo esc_attr( $site_name ); ?>" />
	<meta property="og:image" content="<?php echo esc_url( $image ); ?>" />
	<meta name="twitter:card" content="summary_large_image" />
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>" />
	<meta name="twitter:description" content="<?php echo esc_attr( $desc ); ?>" />
	<meta name="twitter:image" content="<?php echo esc_url( $image ); ?>" />
	<?php
	if ( ! function_exists( 'pll_the_languages' ) ) {
		echo '<link rel="alternate" hreflang="hy" href="' . esc_url( home_url( '/' ) ) . '" />' . "\n";
		echo '<link rel="alternate" hreflang="ru" href="' . esc_url( home_url( '/ru/' ) ) . '" />' . "\n";
		echo '<link rel="alternate" hreflang="en" href="' . esc_url( home_url( '/en/' ) ) . '" />' . "\n";
		echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( home_url( '/' ) ) . '" />' . "\n";
	}
}

/**
 * Output rich JSON-LD @graph for Organization, FoodEstablishment, WebSite, Product, and BlogPosting.
 */
function gv2_output_json_ld_graph(): void {
	if ( is_admin() || is_feed() ) {
		return;
	}

	// Avoid duplicating Gayanes_Core\SEO_Schema if active, unless we are on a product/archive that benefits.
	$has_core_seo = class_exists( 'Gayanes_Core\SEO_Schema' );
	if ( $has_core_seo && ! ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() ) ) ) {
		return;
	}

	$site_url = home_url( '/' );
	$phone    = (string) get_theme_mod( 'gv2_phone', '+374 99 00 00 00' );
	$graph    = array();

	if ( ! $has_core_seo ) {
		$graph[] = array(
			'@type'       => array( 'Organization', 'FoodEstablishment' ),
			'@id'         => $site_url . '#organization',
			'name'        => "Gayane's Kitchen",
			'url'         => 'https://gayanes.am/',
			'logo'        => GV2_URI . '/assets/images/logo-dark.png',
			'telephone'   => $phone,
			'servesCuisine' => 'Armenian',
			'priceRange'  => '$$',
			'address'     => array(
				'@type'           => 'PostalAddress',
				'addressLocality' => 'Yerevan',
				'addressCountry'  => 'AM',
			),
		);

		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => $site_url . '#website',
			'url'             => $site_url,
			'name'            => get_bloginfo( 'name' ),
			'inLanguage'      => gv2_current_lang(),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => $site_url . '?s={search_term_string}&post_type=product',
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	if ( ! $has_core_seo && function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product instanceof WC_Product ) {
			$img = wp_get_attachment_image_url( (int) $product->get_image_id(), 'large' );
			$product_schema = array(
				'@type'       => 'Product',
				'name'        => $product->get_name(),
				'url'         => $product->get_permalink(),
				'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
				'sku'         => $product->get_sku() ?: (string) $product->get_id(),
				'brand'       => array(
					'@type' => 'Brand',
					'name'  => "Gayane's Kitchen",
				),
				'offers'      => array(
					'@type'         => 'Offer',
					'priceCurrency' => 'AMD',
					'price'         => (string) $product->get_price(),
					'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
					'url'           => $product->get_permalink(),
				),
			);
			if ( $img ) {
				$product_schema['image'] = $img;
			}
			if ( $product->get_review_count() > 0 ) {
				$product_schema['aggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => (string) $product->get_average_rating(),
					'reviewCount' => (int) $product->get_review_count(),
				);
			}
			$graph[] = $product_schema;
		}
	}

	if ( ! $has_core_seo && is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		$graph[] = array(
			'@type'         => 'BlogPosting',
			'headline'      => get_the_title( $post_id ),
			'datePublished' => get_the_date( DATE_W3C, $post_id ),
			'dateModified'  => get_the_modified_date( DATE_W3C, $post_id ),
			'author'        => array(
				'@type' => 'Organization',
				'name'  => "Gayane's Kitchen",
			),
		);
	}

	if ( empty( $graph ) ) {
		return;
	}

	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	) . '</script>' . "\n";
}

/**
 * Output Schema.org FAQPage JSON-LD on the FAQ page and Homepage (for SEO & AI search engines).
 */
function gv2_output_faq_json_ld(): void {
	if ( is_admin() || is_feed() || ! function_exists( 'gv2_get_faqs' ) ) {
		return;
	}

	$is_faq_page = is_page( 'faq' ) || is_page_template( 'page-faq.php' );
	$is_home     = is_front_page();

	if ( ! $is_faq_page && ! $is_home ) {
		return;
	}

	$faqs = gv2_get_faqs( $is_home && ! $is_faq_page );
	if ( empty( $faqs ) ) {
		return;
	}

	$schema = array_merge(
		array( '@context' => 'https://schema.org' ),
		gv2_build_faq_schema( $faqs )
	);

	echo '<script type="application/ld+json">' . wp_json_encode(
		$schema,
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	) . '</script>' . "\n";
}

