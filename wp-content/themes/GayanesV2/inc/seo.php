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

	if ( ! defined( 'WPSEO_VERSION' ) ) {
		remove_action( 'wp_head', 'rel_canonical' );
		add_filter( 'wp_robots', 'gv2_filter_wp_robots', 20 );
	}

	add_action( 'template_redirect', 'gv2_redirect_legacy_sitemap_index', 1 );
	add_action( 'template_redirect', 'gv2_serve_llms_txt', 1 );
	add_action( 'wp_head', 'gv2_output_llms_head_link', 5 );
	add_filter( 'wpseo_title', 'gv2_normalize_seo_title_length', 20 );
	add_filter( 'robots_txt', 'gv2_optimize_robots_txt', 100000, 2 );
}
add_action( 'init', 'gv2_seo_init' );

/**
 * Advertise /llms.txt in HTML <head> for AI crawlers (Gemini, ChatGPT, Claude, Perplexity).
 */
function gv2_output_llms_head_link(): void {
	if ( is_admin() || is_feed() || is_404() ) {
		return;
	}
	echo '<link rel="alternate" type="text/plain" title="LLMs.txt" href="' . esc_url( home_url( '/llms.txt' ) ) . '" />' . "\n";
}

/**
 * Serve dynamic /llms.txt and /llms-full.txt Markdown endpoints for AI assistants & search agents.
 */
function gv2_serve_llms_txt(): void {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$path = '/' . trim( $path, '/' );

	if ( '/llms.txt' !== $path && '/llms-full.txt' !== $path ) {
		return;
	}

	status_header( 200 );
	header( 'Content-Type: text/markdown; charset=utf-8' );
	header( 'X-Robots-Tag: index, follow' );

	$home = untrailingslashit( home_url( '/' ) );

	if ( '/llms.txt' === $path ) {
		$out  = "# Gayane's Kitchen (Գայանեի Խոհանոց)\n\n";
		$out .= "> Homemade Armenian frozen food and semi-finished dishes handcrafted in Yerevan, Armenia using natural ingredients, available for online ordering and delivery in Yerevan.\n\n";
		$out .= "## Core Brand Facts\n";
		$out .= "- **Brand:** Gayane's Kitchen (Գայանեի Խոհանոց)\n";
		$out .= "- **Location & Service Area:** Yerevan, Armenia\n";
		$out .= "- **Languages:** Armenian (hy-AM), Russian (ru-AM), English (en-AM)\n";
		$out .= "- **Currency:** AMD (Armenian Dram)\n";
		$out .= "- **Specialties:** Dolma (grape leaf, cabbage, vegetable, lenten/pasuts), Khinkali, Pelmeni, Vareniks, Blini/Crepes, Ishli Kufta, Kololak, Cutlets, Falafel, Spring Rolls, Vegan & Fasting frozen dishes\n\n";
		$out .= "## Key Pages\n";
		$out .= "- [Homepage (Armenian)](" . $home . "/)\n";
		$out .= "- [Homepage (Russian)](" . $home . "/?lang=ru)\n";
		$out .= "- [Homepage (English)](" . $home . "/?lang=en)\n";
		$out .= "- [Online Shop Catalog](" . $home . "/shop/)\n";
		$out .= "- [Delivery & Payment](" . $home . "/delivery/)\n";
		$out .= "- [About Us](" . $home . "/about-us/)\n";
		$out .= "- [Corporate Clients (HoReCa / B2B)](" . $home . "/corporate-clients/)\n";
		$out .= "- [FAQ](" . $home . "/faq/)\n";
		$out .= "- [Blog & Recipes](" . $home . "/blog/)\n";
		$out .= "- [Contact](" . $home . "/contact/)\n\n";
		$out .= "## Complete Product & Category Index\n";
		$out .= "- [Full Product & Category Catalog (llms-full.txt)](" . $home . "/llms-full.txt)\n";
		$out .= "- [XML Sitemap](" . $home . "/wp-sitemap.xml)\n";
		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	// /llms-full.txt — Complete verified catalog across all languages.
	$out  = "# Gayane's Kitchen — Full Product & Category Catalog\n\n";
	$out .= "Primary website: " . $home . "/\n\n";

	$terms = get_terms(
		array(
			'taxonomy'           => 'product_cat',
			'hide_empty'         => true,
			'gm_skip_lang_filter' => true,
		)
	);
	if ( is_array( $terms ) && ! empty( $terms ) ) {
		$out .= "## Product Categories\n";
		foreach ( $terms as $term ) {
			if ( str_starts_with( $term->slug, 'uncategorized' ) ) {
				continue;
			}
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$out .= "- [" . html_entity_decode( $term->name, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) . "](" . $link . ")\n";
			}
		}
		$out .= "\n";
	}

	$products = get_posts(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => 200,
			'gm_skip_lang_filter' => true,
			'orderby'             => 'title',
			'order'               => 'ASC',
		)
	);
	if ( ! empty( $products ) && function_exists( 'wc_get_product' ) ) {
		$out .= "## Published Products (" . count( $products ) . ")\n";
		foreach ( $products as $p ) {
			$wc_p = wc_get_product( $p->ID );
			if ( ! $wc_p ) {
				continue;
			}
			$price = $wc_p->get_price();
			$stock = $wc_p->is_in_stock() ? 'InStock' : 'OutOfStock';
			$link  = get_permalink( $p->ID );
			$title = html_entity_decode( $wc_p->get_name(), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			$out  .= "- **{$title}** — {$price} AMD ({$stock}) — {$link}\n";
		}
	}

	echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}

/**
 * Clean up robots.txt: point Sitemap directly to /wp-sitemap.xml, welcome AI crawlers, and remove redundant empty block.
 *
 * @param string $output Robots.txt content.
 * @param bool   $public Site visibility flag.
 * @return string
 */
function gv2_optimize_robots_txt( string $output, $public ): string {
	if ( ! $public ) {
		return $output;
	}

	$output = str_replace( '/sitemap_index.xml', '/wp-sitemap.xml', $output );
	$output = preg_replace( "/User-agent:\s*\*\s*\r?\nDisallow:\s*\r?\n\r?\n(?=Sitemap:)/i", '', $output ) ?? $output;

	$ai_bots = array( 'GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-Web', 'Google-Extended', 'PerplexityBot', 'Applebot-Extended' );
	$output .= "\n# AI Search & Assistant Crawlers (GEO)\n";
	foreach ( $ai_bots as $bot ) {
		$output .= "User-agent: {$bot}\nAllow: /\nDisallow: /wp-admin/\nDisallow: /cart/\nDisallow: /checkout/\nDisallow: /my-account/\n\n";
	}
	$output .= '# LLMs.txt: ' . esc_url_raw( home_url( '/llms.txt' ) ) . "\n";

	return $output;
}

/**
 * Keep SEO titles concise (<= 65 chars) by compacting the bilingual site-name suffix when needed.
 *
 * @param string $title Document title.
 * @return string
 */
function gv2_normalize_seo_title_length( string $title ): string {
	$title = html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$title = preg_replace( '/\s+/u', ' ', trim( $title ) ) ?? trim( $title );

	if ( mb_strlen( $title, 'UTF-8' ) <= 65 ) {
		return $title;
	}

	$brand = ( 'hy' === gv2_current_lang() ) ? 'Գայանեի Խոհանոց' : "Gayane's Kitchen";
	$title = str_replace(
		array(
			" - Gayane's Kitchen | Գայանեի Խոհանոց",
			" | Gayane's Kitchen | Գայանեի Խոհանոց",
			" - Gayane’s Kitchen | Գայանեի Խոհանոց",
		),
		' | ' . $brand,
		$title
	);

	if ( mb_strlen( $title, 'UTF-8' ) <= 65 ) {
		return $title;
	}

	$suffix = ' | ' . $brand;
	if ( str_ends_with( $title, $suffix ) ) {
		$max_head = 65 - mb_strlen( $suffix, 'UTF-8' );
		if ( $max_head > 15 ) {
			$head = mb_substr( mb_substr( $title, 0, -mb_strlen( $suffix, 'UTF-8' ), 'UTF-8' ), 0, $max_head - 3, 'UTF-8' );
			$head = preg_replace( '/\s+\S*$/u', '', $head ) ?: $head;
			return rtrim( $head, " \t\n\r\0\x0B,-:|" ) . '...' . $suffix;
		}
	}

	$cut = mb_substr( $title, 0, 62, 'UTF-8' );
	return ( preg_replace( '/\s+\S*$/u', '', $cut ) ?: $cut ) . '...';
}

// Register sitemap provider filter before core `wp_sitemaps_get_server()` runs at init:10.
add_filter(
	'wp_sitemaps_add_provider',
	static function ( $provider, string $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);
add_filter(
	'wp_sitemaps_posts_query_args',
	static function ( array $args ): array {
		$args['gm_skip_lang_filter'] = true;

		if ( 'page' === ( $args['post_type'] ?? '' ) && function_exists( 'wc_get_page_id' ) ) {
			$exclude = array_filter(
				array(
					(int) wc_get_page_id( 'cart' ),
					(int) wc_get_page_id( 'checkout' ),
					(int) wc_get_page_id( 'myaccount' ),
				),
				static fn( int $id ): bool => $id > 0
			);
			if ( ! empty( $exclude ) ) {
				$existing             = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
				$args['post__not_in'] = array_values( array_unique( array_merge( $existing, $exclude ) ) );
			}
		}

		return $args;
	}
);
add_filter(
	'wp_sitemaps_taxonomies_query_args',
	static function ( array $args ): array {
		$args['gm_skip_lang_filter'] = true;
		return $args;
	}
);

/**
 * Redirect legacy Yoast /sitemap_index.xml requests to WordPress core /wp-sitemap.xml (same host).
 */
function gv2_redirect_legacy_sitemap_index(): void {
	if ( ! is_404() ) {
		return;
	}
	$req_path = wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), PHP_URL_PATH );
	if ( '/sitemap_index.xml' === $req_path ) {
		wp_safe_redirect( home_url( '/wp-sitemap.xml' ), 301 );
		exit;
	}
}

/**
 * Enforce index/noindex directives via WordPress core wp_robots filter (single robots tag).
 *
 * @param array<string, bool|string> $robots Robots directives.
 * @return array<string, bool|string>
 */
function gv2_filter_wp_robots( array $robots ): array {
	if ( is_404() ) {
		return array(
			'noindex'  => true,
			'nofollow' => true,
		);
	}

	$is_transactional = ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_account_page' ) && is_account_page() );

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$has_non_canonical_param = isset( $_GET['add-to-cart'] ) || isset( $_GET['orderby'] );

	if ( $is_transactional || is_search() || is_author() || $has_non_canonical_param ) {
		return array(
			'noindex' => true,
			'follow'  => true,
		);
	}

	return array(
		'index'               => true,
		'follow'              => true,
		'max-image-preview'   => 'large',
		'max-snippet'         => '-1',
		'max-video-preview'   => '-1',
	);
}

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
	if ( is_admin() || is_feed() || is_404() ) {
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
	$url       = home_url( '/' );
	$image     = GV2_URI . '/assets/images/logo-dark.png';
	$og_type   = 'website';

	if ( is_front_page() ) {
		$url      = home_url( '/' );
		$front_id = (int) get_option( 'page_on_front' );
		if ( $front_id > 0 ) {
			$meta_desc = trim( (string) get_post_meta( $front_id, '_yoast_wpseo_metadesc', true ) );
			if ( '' !== $meta_desc ) {
				$desc = $meta_desc;
			}
		}
		if ( 'ru' === gv2_current_lang() ) {
			$desc = 'Домашние замороженные полуфабрикаты ручной лепки от Gayane’s Kitchen в Ереване: долма, хинкали, пельмени, вареники, блинчики и постные блюда.';
		} elseif ( 'en' === gv2_current_lang() ) {
			$desc = "Homemade Armenian frozen food & semi-finished dishes by Gayane's Kitchen in Yerevan: dolma, khinkali, pelmeni, vareniks, crepes, and vegan options.";
		}
	} elseif ( is_singular() ) {
		$post_id   = get_queried_object_id();
		$meta_desc = trim( (string) get_post_meta( $post_id, '_yoast_wpseo_metadesc', true ) );
		$excerpt   = '' !== $meta_desc ? $meta_desc : trim( (string) get_the_excerpt( $post_id ) );
		if ( '' !== $excerpt ) {
			$desc = wp_strip_all_tags( $excerpt );
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
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$url     = (string) wc_get_page_permalink( 'shop' );
		$shop_id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0;
		if ( 'ru' === gv2_current_lang() ) {
			$desc = 'Каталог домашних замороженных полуфабрикатов Gayane’s Kitchen: хинкали, пельмени, вареники, толма, блинчики и постные блюда с доставкой в Ереване.';
		} elseif ( 'en' === gv2_current_lang() ) {
			$desc = "Shop homemade Armenian frozen food from Gayane's Kitchen: khinkali, pelmeni, vareniks, dolma, crepes, and vegan dishes with delivery in Yerevan.";
		} elseif ( $shop_id > 0 ) {
			$meta_desc = trim( (string) get_post_meta( $shop_id, '_yoast_wpseo_metadesc', true ) );
			if ( '' !== $meta_desc ) {
				$desc = $meta_desc;
			}
		}
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$term_link = get_term_link( $term );
			if ( ! is_wp_error( $term_link ) ) {
				$url = (string) $term_link;
			}
			$term_meta_desc = trim( (string) get_term_meta( $term->term_id, '_yoast_wpseo_metadesc', true ) );
			$raw_term_desc  = '' !== $term_meta_desc ? $term_meta_desc : trim( (string) $term->description );
			if ( '' !== $raw_term_desc ) {
				$desc = wp_strip_all_tags( $raw_term_desc );
			}
		}
	} else {
		$url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
	}

	$desc = preg_replace( '/\s+/u', ' ', trim( $desc ) ) ?? trim( $desc );
	if ( mb_strlen( $desc, 'UTF-8' ) > 160 ) {
		$cut  = mb_substr( $desc, 0, 157, 'UTF-8' );
		$desc = preg_replace( '/\s+\S*$/u', '', $cut ) . '...';
	}

	if ( function_exists( 'gm_plugin' ) && isset( gm_plugin()->router ) ) {
		$url = gm_plugin()->router->add_lang_to_url( $url, gv2_current_lang() );
	}

	$lang_map = array( 'hy' => 'hy_AM', 'ru' => 'ru_RU', 'en' => 'en_US' );
	$locale   = $lang_map[ gv2_current_lang() ] ?? 'hy_AM';
	?>
	<meta name="description" content="<?php echo esc_attr( $desc ); ?>" />
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
	if ( ! function_exists( 'pll_the_languages' ) && ! function_exists( 'gm_plugin' ) ) {
		echo '<link rel="alternate" hreflang="hy-AM" href="' . esc_url( home_url( '/' ) ) . '" />' . "\n";
		echo '<link rel="alternate" hreflang="ru-AM" href="' . esc_url( add_query_arg( 'lang', 'ru', home_url( '/' ) ) ) . '" />' . "\n";
		echo '<link rel="alternate" hreflang="en-AM" href="' . esc_url( add_query_arg( 'lang', 'en', home_url( '/' ) ) ) . '" />' . "\n";
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
			'url'         => $site_url,
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

