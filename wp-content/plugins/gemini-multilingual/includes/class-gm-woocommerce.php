<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GM_WooCommerce {

	private GM_Languages $languages;
	private GM_Translations $translations;
	private GM_Translator $translator;

	public function __construct( GM_Languages $languages, GM_Translations $translations, GM_Translator $translator ) {
		$this->languages    = $languages;
		$this->translations = $translations;
		$this->translator   = $translator;

		// Translate WooCommerce core page IDs (Shop, Cart, Checkout, My Account, Terms).
		add_filter( 'woocommerce_get_shop_page_id', array( $this, 'translate_wc_page_id' ) );
		add_filter( 'woocommerce_get_cart_page_id', array( $this, 'translate_wc_page_id' ) );
		add_filter( 'woocommerce_get_checkout_page_id', array( $this, 'translate_wc_page_id' ) );
		add_filter( 'woocommerce_get_myaccount_page_id', array( $this, 'translate_wc_page_id' ) );
		add_filter( 'woocommerce_get_terms_page_id', array( $this, 'translate_wc_page_id' ) );

		// Allow linked translations of the same product group to share the same SKU.
		add_filter( 'wc_product_has_unique_sku', array( $this, 'allow_shared_sku_for_translations' ), 10, 3 );

		// Sync stock/price when a product is saved.
		add_action( 'woocommerce_update_product', array( $this, 'on_woocommerce_update_product' ), 20 );
	}

	public function translate_wc_page_id( $page_id ): int {
		$page_id = (int) $page_id;
		if ( $page_id <= 0 || is_admin() ) {
			return $page_id;
		}
		$current_lang = $this->languages->get_current_slug();
		$tr_id        = $this->translations->get_translation( $page_id, $current_lang, 'post' );
		return $tr_id > 0 ? $tr_id : $page_id;
	}

	/**
	 * Allows translated copies of the same product to share the same SKU.
	 */
	public function allow_shared_sku_for_translations( bool $sku_found, int $product_id, string $sku ): bool {
		if ( ! $sku_found || '' === $sku ) {
			return $sku_found;
		}

		$group_id = $this->translations->get_group_id( $product_id, 'post' );
		if ( ! $group_id ) {
			return $sku_found;
		}

		$existing_id = wc_get_product_id_by_sku( $sku );
		if ( ! $existing_id || $existing_id === $product_id ) {
			return false;
		}

		$existing_group = $this->translations->get_group_id( (int) $existing_id, 'post' );
		if ( $existing_group === $group_id ) {
			return false;
		}

		return $sku_found;
	}

	/**
	 * Copies and translates WooCommerce product meta, attributes, gallery, and variations.
	 */
	public function sync_and_translate_product( int $source_id, int $target_id, string $source_lang, string $target_lang ): void {
		$settings = GM_Plugin::get_settings();

		// Sync pricing, stock, dimensions, SKU, type, external URL, etc.
		if ( ! empty( $settings['wc_sync_stock_price'] ) ) {
			$sync_meta_keys = array(
				'_sku',
				'_regular_price',
				'_sale_price',
				'_price',
				'_sale_price_dates_from',
				'_sale_price_dates_to',
				'_manage_stock',
				'_stock',
				'_stock_status',
				'_backorders',
				'_low_stock_amount',
				'_sold_individually',
				'_weight',
				'_length',
				'_width',
				'_height',
				'_tax_status',
				'_tax_class',
				'_virtual',
				'_downloadable',
				'_product_url',
				'_button_text',
			);
			foreach ( $sync_meta_keys as $key ) {
				$val = get_post_meta( $source_id, $key, true );
				if ( '' !== $val && null !== $val ) {
					update_post_meta( $target_id, $key, $val );
				} else {
					delete_post_meta( $target_id, $key );
				}
			}
		}

		// Sync gallery images.
		if ( ! empty( $settings['wc_sync_media'] ) ) {
			$gallery = get_post_meta( $source_id, '_product_image_gallery', true );
			update_post_meta( $target_id, '_product_image_gallery', $gallery );
		}

		// Sync product attributes.
		if ( ! empty( $settings['wc_sync_attributes'] ) ) {
			$attrs = get_post_meta( $source_id, '_product_attributes', true );
			if ( is_array( $attrs ) ) {
				update_post_meta( $target_id, '_product_attributes', $attrs );
			}
		}

		// Sync upsells & cross-sells mapped to target language.
		foreach ( array( '_upsell_ids', '_crosssell_ids' ) as $rel_key ) {
			$ids = get_post_meta( $source_id, $rel_key, true );
			if ( is_array( $ids ) && ! empty( $ids ) ) {
				$mapped = array();
				foreach ( $ids as $rel_id ) {
					$tr_rel   = $this->translations->get_translation( (int) $rel_id, $target_lang, 'post' );
					$mapped[] = $tr_rel > 0 ? $tr_rel : (int) $rel_id;
				}
				update_post_meta( $target_id, $rel_key, $mapped );
			}
		}

		// Handle variable product variations.
		$source_product = wc_get_product( $source_id );
		if ( $source_product && $source_product->is_type( 'variable' ) ) {
			$this->sync_variations( $source_id, $target_id, $source_lang, $target_lang );
		}

		if ( function_exists( 'wc_delete_product_transients' ) ) {
			wc_delete_product_transients( $target_id );
		}
	}

	private function sync_variations( int $source_parent_id, int $target_parent_id, string $source_lang, string $target_lang ): void {
		$variations = get_posts(
			array(
				'post_type'      => 'product_variation',
				'post_parent'    => $source_parent_id,
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'private' ),
			)
		);

		$source_lang_obj  = $this->languages->get_language( $source_lang );
		$target_lang_obj  = $this->languages->get_language( $target_lang );
		$source_lang_name = $source_lang_obj ? $source_lang_obj['name'] : $source_lang;
		$target_lang_name = $target_lang_obj ? $target_lang_obj['name'] : $target_lang;

		foreach ( $variations as $var_post ) {
			$tr_var_id = $this->translations->get_translation( (int) $var_post->ID, $target_lang, 'post' );

			$var_data = array(
				'post_type'   => 'product_variation',
				'post_parent' => $target_parent_id,
				'post_status' => $var_post->post_status,
				'post_title'  => $var_post->post_title,
				'menu_order'  => $var_post->menu_order,
			);

			if ( $tr_var_id > 0 && get_post( $tr_var_id ) ) {
				$var_data['ID'] = $tr_var_id;
				$target_var_id  = wp_update_post( $var_data, true );
			} else {
				$target_var_id = wp_insert_post( $var_data, true );
			}

			if ( is_wp_error( $target_var_id ) ) {
				continue;
			}

			$target_var_id = (int) $target_var_id;
			$this->translations->link_translation( (int) $var_post->ID, $target_var_id, $source_lang, $target_lang, 'post' );

			// Copy variation meta & map attribute_pa_* term slugs.
			$all_meta = get_post_meta( $var_post->ID );
			foreach ( $all_meta as $meta_key => $values ) {
				$val = maybe_unserialize( $values[0] ?? '' );
				if ( str_starts_with( $meta_key, 'attribute_pa_' ) && is_string( $val ) && '' !== $val ) {
					$taxonomy = substr( $meta_key, strlen( 'attribute_' ) );
					$term     = get_term_by( 'slug', $val, $taxonomy );
					if ( $term ) {
						$tr_term_id = $this->translations->get_translation( (int) $term->term_id, $target_lang, 'term' );
						if ( $tr_term_id > 0 ) {
							$tr_term = get_term( $tr_term_id, $taxonomy );
							if ( $tr_term && ! is_wp_error( $tr_term ) ) {
								$val = $tr_term->slug;
							}
						}
					}
				}
				update_post_meta( $target_var_id, $meta_key, $val );
			}

			// Translate variation description if non-empty.
			$var_desc = get_post_meta( $var_post->ID, '_variation_description', true );
			if ( is_string( $var_desc ) && '' !== trim( $var_desc ) ) {
				$tr_desc = gm_plugin()->gemini->translate_structured(
					array( 'description' => $var_desc ),
					$source_lang_name,
					$target_lang_name,
					array( 'type' => 'woocommerce_variation_description' )
				);
				if ( ! is_wp_error( $tr_desc ) && isset( $tr_desc['description'] ) ) {
					update_post_meta( $target_var_id, '_variation_description', $tr_desc['description'] );
				}
			}
		}
	}

	/**
	 * Keeps stock and price synced across linked product translations when edited manually.
	 */
	public function on_woocommerce_update_product( int $product_id ): void {
		static $syncing = false;
		if ( $syncing ) {
			return;
		}

		$settings = GM_Plugin::get_settings();
		if ( empty( $settings['wc_sync_stock_price'] ) ) {
			return;
		}

		$translations = $this->translations->get_translations( $product_id, 'post' );
		if ( count( $translations ) <= 1 ) {
			return;
		}

		$syncing = true;
		$keys    = array( '_regular_price', '_sale_price', '_price', '_manage_stock', '_stock', '_stock_status', '_sku' );
		foreach ( $translations as $tr_id ) {
			if ( $tr_id === $product_id ) {
				continue;
			}
			foreach ( $keys as $key ) {
				$val = get_post_meta( $product_id, $key, true );
				update_post_meta( $tr_id, $key, $val );
			}
		}
		$syncing = false;
	}
}
