<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GM_Translator {

	private GM_Languages $languages;
	private GM_Translations $translations;
	private GM_Gemini_Client $gemini;

	public function __construct( GM_Languages $languages, GM_Translations $translations, GM_Gemini_Client $gemini ) {
		$this->languages    = $languages;
		$this->translations = $translations;
		$this->gemini       = $gemini;
	}

	/**
	 * One-click AI translation for any post, page, product, or custom post type.
	 *
	 * @return int|WP_Error Target post ID on success.
	 */
	public function translate_post( int $source_post_id, string $target_lang, bool $overwrite = true ) {
		$source_post = get_post( $source_post_id );
		if ( ! $source_post ) {
			return new WP_Error( 'gm_invalid_post', __( 'Source post not found.', 'gemini-multilingual' ) );
		}

		$target_lang_obj = $this->languages->get_language( $target_lang );
		if ( ! $target_lang_obj ) {
			return new WP_Error( 'gm_invalid_lang', __( 'Target language does not exist.', 'gemini-multilingual' ) );
		}

		$source_lang = $this->translations->get_object_language( $source_post_id, 'post' );
		if ( ! $source_lang ) {
			$source_lang = $this->languages->get_default_slug();
			$this->translations->set_object_language( $source_post_id, $source_lang, 'post' );
		}

		if ( $source_lang === $target_lang ) {
			return $source_post_id;
		}

		$existing_target_id = $this->translations->get_translation( $source_post_id, $target_lang, 'post' );
		if ( $existing_target_id > 0 && ! $overwrite && get_post( $existing_target_id ) ) {
			return $existing_target_id;
		}

		$source_lang_obj  = $this->languages->get_language( $source_lang );
		$source_lang_name = $source_lang_obj ? $source_lang_obj['name'] : $source_lang;
		$target_lang_name = $target_lang_obj['name'];
		$settings         = GM_Plugin::get_settings();

		// Collect translatable fields.
		$fields = array(
			'post_title'   => (string) $source_post->post_title,
			'post_content' => (string) $source_post->post_content,
			'post_excerpt' => (string) $source_post->post_excerpt,
		);

		if ( ! empty( $settings['translate_slugs'] ) ) {
			$fields['post_name'] = (string) $source_post->post_name;
		}

		// Collect SEO meta (Yoast & RankMath) if present.
		$seo_keys = array(
			'_yoast_wpseo_title',
			'_yoast_wpseo_metadesc',
			'_yoast_wpseo_focuskw',
			'rank_math_title',
			'rank_math_description',
			'_purchase_note',
		);
		foreach ( $seo_keys as $meta_key ) {
			$val = get_post_meta( $source_post_id, $meta_key, true );
			if ( is_string( $val ) && '' !== trim( $val ) ) {
				$fields[ 'meta_' . $meta_key ] = $val;
			}
		}

		$translated = $this->gemini->translate_structured(
			$fields,
			$source_lang_name,
			$target_lang_name,
			array(
				'post_type' => $source_post->post_type,
				'site_name' => get_bloginfo( 'name' ),
			)
		);

		if ( is_wp_error( $translated ) ) {
			return $translated;
		}

		$post_data = array(
			'post_type'      => $source_post->post_type,
			'post_status'    => $source_post->post_status,
			'post_author'    => $source_post->post_author,
			'post_title'     => $translated['post_title'] ?? $source_post->post_title,
			'post_content'   => $translated['post_content'] ?? $source_post->post_content,
			'post_excerpt'   => $translated['post_excerpt'] ?? $source_post->post_excerpt,
			'menu_order'     => $source_post->menu_order,
			'comment_status' => $source_post->comment_status,
			'ping_status'    => $source_post->ping_status,
		);

		if ( ! empty( $settings['translate_slugs'] ) && ! empty( $translated['post_name'] ) ) {
			$post_data['post_name'] = sanitize_title( $translated['post_name'] );
		} else {
			$post_data['post_name'] = sanitize_title( $post_data['post_title'] ) . '-' . $target_lang;
		}

		// Map parent page/post to its translation if available.
		if ( $source_post->post_parent > 0 ) {
			$tr_parent               = $this->translations->get_translation( (int) $source_post->post_parent, $target_lang, 'post' );
			$post_data['post_parent'] = $tr_parent > 0 ? $tr_parent : 0;
		}

		if ( $existing_target_id > 0 && get_post( $existing_target_id ) ) {
			$post_data['ID'] = $existing_target_id;
			$target_post_id  = wp_update_post( wp_slash( $post_data ), true );
		} else {
			$target_post_id = wp_insert_post( wp_slash( $post_data ), true );
		}

		if ( is_wp_error( $target_post_id ) ) {
			return $target_post_id;
		}

		$target_post_id = (int) $target_post_id;

		// Link translation group.
		$this->translations->link_translation( $source_post_id, $target_post_id, $source_lang, $target_lang, 'post' );

		// Save translated SEO meta & page template.
		foreach ( $seo_keys as $meta_key ) {
			if ( isset( $translated[ 'meta_' . $meta_key ] ) ) {
				update_post_meta( $target_post_id, $meta_key, $translated[ 'meta_' . $meta_key ] );
			}
		}

		$template = get_post_meta( $source_post_id, '_wp_page_template', true );
		if ( $template ) {
			update_post_meta( $target_post_id, '_wp_page_template', $template );
		}

		// Copy featured image.
		$thumb_id = get_post_thumbnail_id( $source_post_id );
		if ( $thumb_id ) {
			set_post_thumbnail( $target_post_id, $thumb_id );
		}

		// Copy & translate taxonomies (Categories, Tags, Product Categories, Attributes).
		$this->sync_post_taxonomies( $source_post_id, $target_post_id, $source_post->post_type, $target_lang );

		// WooCommerce product meta & variations sync/translation.
		if ( 'product' === $source_post->post_type && gm_plugin()->woocommerce ) {
			gm_plugin()->woocommerce->sync_and_translate_product( $source_post_id, $target_post_id, $source_lang, $target_lang );
		}

		do_action( 'gm_after_translate_post', $source_post_id, $target_post_id, $source_lang, $target_lang );

		return $target_post_id;
	}

	/**
	 * Syncs and optionally auto-translates taxonomy terms for a translated post.
	 */
	public function sync_post_taxonomies( int $source_post_id, int $target_post_id, string $post_type, string $target_lang ): void {
		$settings           = GM_Plugin::get_settings();
		$auto_translate     = ! empty( $settings['auto_translate_terms'] );
		$taxonomies         = get_object_taxonomies( $post_type );
		$skip_taxonomies    = array( 'product_type', 'product_visibility', 'product_shipping_class' );

		foreach ( $taxonomies as $taxonomy ) {
			if ( in_array( $taxonomy, $skip_taxonomies, true ) ) {
				$terms = wp_get_object_terms( $source_post_id, $taxonomy, array( 'fields' => 'slugs' ) );
				if ( ! is_wp_error( $terms ) ) {
					wp_set_object_terms( $target_post_id, $terms, $taxonomy );
				}
				continue;
			}

			$source_terms = wp_get_object_terms( $source_post_id, $taxonomy );
			if ( is_wp_error( $source_terms ) || empty( $source_terms ) ) {
				continue;
			}

			$target_term_ids = array();
			foreach ( $source_terms as $term ) {
				$tr_term_id = $this->translations->get_translation( (int) $term->term_id, $target_lang, 'term' );
				if ( $tr_term_id <= 0 && $auto_translate ) {
					$res = $this->translate_term( (int) $term->term_id, $taxonomy, $target_lang );
					if ( ! is_wp_error( $res ) ) {
						$tr_term_id = (int) $res;
					}
				}
				if ( $tr_term_id > 0 ) {
					$target_term_ids[] = $tr_term_id;
				}
			}

			if ( ! empty( $target_term_ids ) ) {
				wp_set_object_terms( $target_post_id, $target_term_ids, $taxonomy );
			}
		}
	}

	/**
	 * One-click AI translation for any taxonomy term (category, tag, product_cat, pa_*).
	 *
	 * @return int|WP_Error Target term ID.
	 */
	public function translate_term( int $source_term_id, string $taxonomy, string $target_lang, bool $overwrite = true ) {
		$source_term = get_term( $source_term_id, $taxonomy );
		if ( ! $source_term || is_wp_error( $source_term ) ) {
			return new WP_Error( 'gm_invalid_term', __( 'Source term not found.', 'gemini-multilingual' ) );
		}

		$target_lang_obj = $this->languages->get_language( $target_lang );
		if ( ! $target_lang_obj ) {
			return new WP_Error( 'gm_invalid_lang', __( 'Target language does not exist.', 'gemini-multilingual' ) );
		}

		$source_lang = $this->translations->get_object_language( $source_term_id, 'term' );
		if ( ! $source_lang ) {
			$source_lang = $this->languages->get_default_slug();
			$this->translations->set_object_language( $source_term_id, $source_lang, 'term' );
		}

		if ( $source_lang === $target_lang ) {
			return $source_term_id;
		}

		$existing_id = $this->translations->get_translation( $source_term_id, $target_lang, 'term' );
		if ( $existing_id > 0 && ! $overwrite && get_term( $existing_id, $taxonomy ) ) {
			return $existing_id;
		}

		$source_lang_obj  = $this->languages->get_language( $source_lang );
		$source_lang_name = $source_lang_obj ? $source_lang_obj['name'] : $source_lang;
		$target_lang_name = $target_lang_obj['name'];

		$fields = array(
			'name'        => (string) $source_term->name,
			'description' => (string) $source_term->description,
		);

		$translated = $this->gemini->translate_structured(
			$fields,
			$source_lang_name,
			$target_lang_name,
			array(
				'taxonomy' => $taxonomy,
				'type'     => 'taxonomy_term',
			)
		);

		if ( is_wp_error( $translated ) ) {
			return $translated;
		}

		$tr_name = $translated['name'] ?? $source_term->name;
		$tr_desc = $translated['description'] ?? $source_term->description;
		$tr_slug = sanitize_title( $tr_name );
		if ( '' === $tr_slug || $tr_slug === $source_term->slug ) {
			$tr_slug = $source_term->slug . '-' . $target_lang;
		}

		$parent_id = 0;
		if ( $source_term->parent > 0 ) {
			$tr_parent = $this->translations->get_translation( (int) $source_term->parent, $target_lang, 'term' );
			if ( $tr_parent > 0 ) {
				$parent_id = $tr_parent;
			}
		}

		if ( $existing_id > 0 && get_term( $existing_id, $taxonomy ) ) {
			$res = wp_update_term(
				$existing_id,
				$taxonomy,
				array(
					'name'        => $tr_name,
					'description' => $tr_desc,
					'parent'      => $parent_id,
				)
			);
		} else {
			$res = wp_insert_term(
				$tr_name,
				$taxonomy,
				array(
					'slug'        => $tr_slug,
					'description' => $tr_desc,
					'parent'      => $parent_id,
				)
			);

			// Handle slug collision if term name matches another language.
			if ( is_wp_error( $res ) && 'term_exists' === $res->get_error_code() ) {
				$res = wp_insert_term(
					$tr_name,
					$taxonomy,
					array(
						'slug'        => $tr_slug . '-' . $target_lang,
						'description' => $tr_desc,
						'parent'      => $parent_id,
					)
				);
			}
		}

		if ( is_wp_error( $res ) ) {
			return $res;
		}

		$target_term_id = (int) $res['term_id'];
		$this->translations->link_translation( $source_term_id, $target_term_id, $source_lang, $target_lang, 'term' );

		// Copy WooCommerce category thumbnail if any.
		$thumb_id = get_term_meta( $source_term_id, 'thumbnail_id', true );
		if ( $thumb_id ) {
			update_term_meta( $target_term_id, 'thumbnail_id', $thumb_id );
		}

		return $target_term_id;
	}
}
