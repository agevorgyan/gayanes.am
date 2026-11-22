<?php
/**
 * Technical SEO & JSON-LD Structured Data.
 *
 * Compatible with Yoast SEO and Polylang: avoids duplicate meta/canonical/hreflang
 * tags while ensuring complete Product, Organization, WebSite, BreadcrumbList,
 * and Article JSON-LD schema.
 *
 * @package Gayanes_Core
 */

namespace Gayanes_Core;

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Schema class.
 */
class SEO_Schema {

	/**
	 * Initialize SEO hooks.
	 */
	public static function init(): void {
		add_action( 'wp_head', array( __CLASS__, 'output_json_ld' ), 25 );
		add_filter( 'wp_get_attachment_image_attributes', array( __CLASS__, 'ensure_image_alt' ), 10, 2 );
		add_filter( 'woocommerce_structured_data_product', '__return_empty_array', 99 );
	}

	/**
	 * Ensure every product/content image has a meaningful alt attribute.
	 *
	 * @param array<string, string> $attr       Image attributes.
	 * @param \WP_Post              $attachment Attachment post.
	 * @return array<string, string>
	 */
	public static function ensure_image_alt( array $attr, \WP_Post $attachment ): array {
		if ( empty( $attr['alt'] ) ) {
			$parent_id = (int) $attachment->post_parent;
			if ( $parent_id > 0 ) {
				$attr['alt'] = wp_strip_all_tags( get_the_title( $parent_id ) );
			} else {
				$attr['alt'] = wp_strip_all_tags( $attachment->post_title );
			}
		}
		return $attr;
	}

	/**
	 * Output clean JSON-LD schema graph.
	 */
	public static function output_json_ld(): void {
		if ( is_admin() || is_feed() ) {
			return;
		}

		$graph = array();

		// Organization, WebSite & BreadcrumbList only when Yoast SEO is inactive (avoid duplicates).
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			$graph[] = self::get_organization_schema();
			$graph[] = self::get_website_schema();
		}

		if ( is_front_page() ) {
			$graph[] = self::get_local_business_schema();
		}

		if ( function_exists( 'is_product' ) && is_product() ) {
			$product_schema = self::get_product_schema();
			if ( ! empty( $product_schema ) ) {
				$graph[] = $product_schema;
			}
		}

		if ( is_singular( 'post' ) ) {
			$graph[] = self::get_article_schema();
		}

		if ( ! is_front_page() && ! defined( 'WPSEO_VERSION' ) ) {
			$breadcrumbs = self::get_breadcrumb_schema();
			if ( ! empty( $breadcrumbs ) ) {
				$graph[] = $breadcrumbs;
			}
		}

		if ( empty( $graph ) ) {
			return;
		}

		$payload = array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		);

		echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}

	/**
	 * Organization schema.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_organization_schema(): array {
		$logo_id  = get_theme_mod( 'custom_logo' );
		$logo_url = $logo_id ? wp_get_attachment_image_url( (int) $logo_id, 'full' ) : '';

		$schema = array(
			'@type' => 'Organization',
			'@id'   => home_url( '/#organization' ),
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		);

		if ( $logo_url ) {
			$schema['logo'] = array(
				'@type' => 'ImageObject',
				'url'   => $logo_url,
			);
		}

		return $schema;
	}

	/**
	 * WebSite schema with SearchAction.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_website_schema(): array {
		return array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'url'             => home_url( '/' ),
			'name'            => get_bloginfo( 'name' ),
			'inLanguage'      => Multilingual::current_lang(),
			'publisher'       => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}&post_type=product' ),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	/**
	 * LocalBusiness / FoodEstablishment schema for Armenia.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_local_business_schema(): array {
		return array(
			'@type'             => array( 'FoodEstablishment', 'LocalBusiness' ),
			'@id'               => home_url( '/#localbusiness' ),
			'name'              => "Gayane's Kitchen | Գայանեի Խոհանոց",
			'url'               => home_url( '/' ),
			'description'       => 'Homemade Armenian frozen food and semi-finished dishes handcrafted in Yerevan using natural ingredients: dolma, khinkali, pelmeni, vareniks, crepes, ishli kufta, falafel, and vegan options.',
			'servesCuisine'     => array( 'Armenian', 'Homemade Frozen Food', 'Vegan & Fasting Food' ),
			'priceRange'        => 'AMD',
			'availableLanguage' => array( 'Armenian', 'Russian', 'English' ),
			'knowsAbout'        => array( 'Armenian Dolma', 'Homemade Khinkali', 'Pelmeni', 'Vareniks', 'Ishli Kufta', 'Frozen Semi-Finished Food Yerevan' ),
			'hasOfferCatalog'   => array(
				'@type' => 'OfferCatalog',
				'name'  => "Gayane's Kitchen Menu",
				'url'   => home_url( '/shop/' ),
			),
			'address'           => array(
				'@type'           => 'PostalAddress',
				'addressLocality' => 'Yerevan',
				'addressCountry'  => 'AM',
			),
			'areaServed'        => array(
				'@type' => 'City',
				'name'  => 'Yerevan',
			),
		);
	}

	/**
	 * WooCommerce Product schema.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_product_schema(): array {
		$product = wc_get_product( get_the_ID() );
		if ( ! $product ) {
			return array();
		}

		$image_url = wp_get_attachment_image_url( $product->get_image_id(), 'large' );
		if ( ! $image_url && function_exists( 'wc_placeholder_img_src' ) ) {
			$image_url = wc_placeholder_img_src( 'large' );
		}
		$currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'AMD';

		$schema = array(
			'@type'       => 'Product',
			'@id'         => get_permalink() . '#product',
			'name'        => $product->get_name(),
			'url'         => get_permalink(),
			'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
			'sku'         => $product->get_sku() ?: (string) $product->get_id(),
			'brand'       => array(
				'@type' => 'Brand',
				'name'  => "Gayane's Kitchen",
			),
			'offers'      => array(
				'@type'         => 'Offer',
				'url'           => get_permalink(),
				'priceCurrency' => $currency,
				'price'         => (string) $product->get_price(),
				'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			),
		);

		if ( $image_url ) {
			$schema['image'] = $image_url;
		}

		if ( method_exists( $product, 'get_global_unique_id' ) && '' !== $product->get_global_unique_id() ) {
			$schema['gtin'] = $product->get_global_unique_id();
		}

		if ( $product->get_rating_count() > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => (string) $product->get_average_rating(),
				'reviewCount' => (int) $product->get_review_count(),
			);
		}

		return $schema;
	}

	/**
	 * Blog / Recipe Article schema.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_article_schema(): array {
		$image_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );

		$schema = array(
			'@type'            => 'Article',
			'@id'              => get_permalink() . '#article',
			'headline'         => get_the_title(),
			'datePublished'    => get_the_date( DATE_W3C ),
			'dateModified'     => get_the_modified_date( DATE_W3C ),
			'author'           => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
			'publisher'        => array( '@id' => home_url( '/#organization' ) ),
			'mainEntityOfPage' => get_permalink(),
		);

		if ( $image_url ) {
			$schema['image'] = $image_url;
		}

		return $schema;
	}

	/**
	 * BreadcrumbList schema.
	 *
	 * @return array<string, mixed>
	 */
	private static function get_breadcrumb_schema(): array {
		$items = array(
			array(
				'@type'    => 'ListItem',
				'position' => 1,
				'name'     => Multilingual::t( 'Home' ),
				'item'     => home_url( '/' ),
			),
		);

		$pos = 2;

		if ( function_exists( 'is_product_category' ) && is_product_category() ) {
			$term = get_queried_object();
			if ( $term instanceof \WP_Term ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $term->name,
					'item'     => get_term_link( $term ),
				);
			}
		} elseif ( function_exists( 'is_product' ) && is_product() ) {
			$terms = get_the_terms( get_the_ID(), 'product_cat' );
			if ( is_array( $terms ) && ! empty( $terms ) ) {
				$primary = $terms[0];
				$link    = get_term_link( $primary );
				if ( ! is_wp_error( $link ) ) {
					$items[] = array(
						'@type'    => 'ListItem',
						'position' => $pos++,
						'name'     => $primary->name,
						'item'     => $link,
					);
				}
			}
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos,
				'name'     => get_the_title(),
				'item'     => get_permalink(),
			);
		} elseif ( is_singular() ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos,
				'name'     => get_the_title(),
				'item'     => get_permalink(),
			);
		}

		if ( count( $items ) < 2 ) {
			return array();
		}

		return array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);
	}
}

