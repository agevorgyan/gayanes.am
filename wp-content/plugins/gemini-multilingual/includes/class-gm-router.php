<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GM_Router {

	private GM_Languages $languages;
	private GM_Translations $translations;

	public function __construct( GM_Languages $languages, GM_Translations $translations ) {
		$this->languages    = $languages;
		$this->translations = $translations;

		add_action( 'init', array( $this, 'detect_language_early' ), 1 );
		add_filter( 'determine_locale', array( $this, 'filter_locale' ), 20 );
		add_filter( 'locale', array( $this, 'filter_locale' ), 20 );

		// Rewrite rules for /en/... /ru/... prefixes.
		add_action( 'init', array( $this, 'register_rewrite_rules' ), 5 );
		add_filter( 'query_vars', array( $this, 'register_query_vars' ) );

		// Filter frontend queries by current language.
		add_action( 'pre_get_posts', array( $this, 'filter_frontend_query' ) );
		add_filter( 'woocommerce_product_data_store_cpt_get_products_query', array( $this, 'filter_wc_products_query' ), 10, 2 );
		add_filter( 'get_terms_args', array( $this, 'filter_terms_args' ), 10, 2 );
		add_filter( 'wp_get_nav_menu_items', array( $this, 'filter_nav_menu_items' ), 10, 3 );

		// Prefix links on frontend.
		add_filter( 'home_url', array( $this, 'filter_home_url' ), 10, 2 );
		add_filter( 'post_link', array( $this, 'filter_object_permalink' ), 10, 2 );
		add_filter( 'page_link', array( $this, 'filter_object_permalink' ), 10, 2 );
		add_filter( 'post_type_link', array( $this, 'filter_object_permalink' ), 10, 2 );
		add_filter( 'term_link', array( $this, 'filter_term_link' ), 10, 3 );

		// SEO hreflang tags & language switcher shortcode.
		add_action( 'wp_head', array( $this, 'output_hreflang_tags' ), 2 );
		add_shortcode( 'gm_language_switcher', array( $this, 'render_language_switcher_shortcode' ) );
	}

	public function detect_language_early(): void {
		$langs = $this->languages->get_languages( true );
		if ( empty( $langs ) ) {
			return;
		}

		// 1. Explicit ?lang=xx query param.
		if ( isset( $_GET['lang'] ) ) {
			$q_lang = sanitize_key( wp_unslash( $_GET['lang'] ) );
			if ( isset( $langs[ $q_lang ] ) ) {
				$this->languages->set_current_slug( $q_lang );
				return;
			}
		}

		// 2. URL path prefix (/en/..., /ru/...).
		if ( ! is_admin() && isset( $_SERVER['REQUEST_URI'] ) ) {
			$uri_path  = (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
			$home_path = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
			$rel_path  = trim( substr( $uri_path, strlen( rtrim( $home_path, '/' ) ) ), '/' );
			$segments  = '' !== $rel_path ? explode( '/', $rel_path ) : array();

			if ( ! empty( $segments[0] ) && isset( $langs[ $segments[0] ] ) ) {
				$this->languages->set_current_slug( $segments[0] );
				return;
			}
		}

		$this->languages->set_current_slug( $this->languages->get_default_slug() );
	}

	public function filter_locale( string $locale ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $locale;
		}
		$lang = $this->languages->get_current_language();
		return ( $lang && ! empty( $lang['locale'] ) ) ? $lang['locale'] : $locale;
	}

	public function register_query_vars( array $vars ): array {
		$vars[] = 'gm_lang';
		return $vars;
	}

	public function register_rewrite_rules(): void {
		$langs = $this->languages->get_languages( true );
		$slugs = array_keys( $langs );
		if ( empty( $slugs ) ) {
			return;
		}

		$pattern = implode( '|', array_map( 'preg_quote', $slugs ) );
		add_rewrite_rule( "^({$pattern})/?$", 'index.php?gm_lang=$matches[1]', 'top' );
		add_rewrite_rule( "^({$pattern})/(.+?)/?$", 'index.php?gm_lang=$matches[1]&pagename=$matches[2]', 'top' );
	}

	public function filter_frontend_query( WP_Query $query ): void {
		if ( is_admin() || $query->get( 'gm_skip_lang_filter' ) ) {
			return;
		}

		$settings   = GM_Plugin::get_settings();
		$post_types = (array) ( $settings['post_types'] ?? array( 'post', 'page', 'product' ) );
		$q_pt       = $query->get( 'post_type' );

		if ( '' === $q_pt ) {
			if ( ! $query->is_main_query() ) {
				$q_pt = 'post';
			}
		}

		if ( is_array( $q_pt ) ) {
			if ( empty( array_intersect( $q_pt, $post_types ) ) ) {
				return;
			}
		} elseif ( '' !== $q_pt && ! in_array( $q_pt, $post_types, true ) ) {
			return;
		}

		$current_lang = $this->languages->get_current_slug();

		// If a page/post slug is shared across languages (e.g. /ru/about-us/, /en/about-us/),
		// WordPress's get_page_by_path() picks the first matching ID regardless of language.
		// Resolve it to the translated post ID for the active language before excluding other languages.
		if ( $query->is_main_query() ) {
			$pagename = (string) $query->get( 'pagename' );
			if ( '' !== $pagename ) {
				$found_id = (int) $query->get( 'page_id' );
				if ( $found_id <= 0 && isset( $query->queried_object_id ) ) {
					$found_id = (int) $query->queried_object_id;
				}
				if ( $found_id <= 0 ) {
					$found_page = get_page_by_path( $pagename );
					if ( $found_page instanceof WP_Post ) {
						$found_id = (int) $found_page->ID;
					}
				}
				if ( $found_id > 0 ) {
					$found_lang = $this->translations->get_object_language( $found_id, 'post' );
					if ( $found_lang && $found_lang !== $current_lang ) {
						$tr_id = $this->translations->get_translation( $found_id, $current_lang, 'post' );
						if ( $tr_id > 0 ) {
							$query->set( 'pagename', '' );
							$query->set( 'page_id', $tr_id );
							$query->queried_object    = get_post( $tr_id );
							$query->queried_object_id = $tr_id;
						}
					}
				}
			}
		}

		$exclude_ids = $this->translations->get_object_ids_not_in_language( $current_lang, 'post' );

		if ( ! empty( $exclude_ids ) ) {
			$existing_not_in = (array) $query->get( 'post__not_in', array() );
			$query->set( 'post__not_in', array_unique( array_merge( $existing_not_in, $exclude_ids ) ) );
		}
	}

	public function filter_wc_products_query( array $query, array $query_vars ): array {
		if ( is_admin() ) {
			return $query;
		}

		$current_lang = $this->languages->get_current_slug();
		$exclude_ids  = $this->translations->get_object_ids_not_in_language( $current_lang, 'post' );

		if ( ! empty( $exclude_ids ) ) {
			$existing              = isset( $query['post__not_in'] ) ? (array) $query['post__not_in'] : array();
			$query['post__not_in'] = array_unique( array_merge( $existing, $exclude_ids ) );
		}

		return $query;
	}

	public function filter_terms_args( array $args, array $taxonomies ): array {
		if ( is_admin() || ! empty( $args['gm_skip_lang_filter'] ) ) {
			return $args;
		}

		$settings      = GM_Plugin::get_settings();
		$enabled_taxes = (array) ( $settings['taxonomies'] ?? array( 'category', 'post_tag', 'product_cat', 'product_tag' ) );

		$intersects = array_intersect( $taxonomies, $enabled_taxes );
		if ( empty( $intersects ) ) {
			return $args;
		}

		$current_lang = $this->languages->get_current_slug();
		$exclude_ids  = $this->translations->get_object_ids_not_in_language( $current_lang, 'term' );
		if ( ! empty( $exclude_ids ) ) {
			$existing        = isset( $args['exclude'] ) ? wp_parse_id_list( $args['exclude'] ) : array();
			$args['exclude'] = array_unique( array_merge( $existing, $exclude_ids ) );
		}

		return $args;
	}

	/**
	 * Filter navigation menu items on the frontend by the current language.
	 *
	 * @param array<int, object> $items Nav menu item objects.
	 * @param object             $menu  Menu object.
	 * @param array<string, mixed> $args Query arguments.
	 * @return array<int, object>
	 */
	public function filter_nav_menu_items( array $items, $menu, array $args ): array {
		if ( is_admin() || empty( $items ) ) {
			return $items;
		}

		$current_lang = $this->languages->get_current_slug();
		$filtered     = array();
		$removed_ids  = array();

		foreach ( $items as $item ) {
			if ( ! is_object( $item ) ) {
				continue;
			}

			$parent_id = isset( $item->menu_item_parent ) ? (int) $item->menu_item_parent : 0;
			if ( $parent_id > 0 && isset( $removed_ids[ $parent_id ] ) ) {
				$removed_ids[ (int) $item->ID ] = true;
				continue;
			}

			$item_lang = '';
			if ( 'post_type' === ( $item->type ?? '' ) && ! empty( $item->object_id ) ) {
				$item_lang = $this->translations->get_object_language( (int) $item->object_id, 'post' );
			} elseif ( 'taxonomy' === ( $item->type ?? '' ) && ! empty( $item->object_id ) ) {
				$item_lang = $this->translations->get_object_language( (int) $item->object_id, 'term' );
			}

			if ( '' !== $item_lang && $item_lang !== $current_lang ) {
				$removed_ids[ (int) $item->ID ] = true;
				continue;
			}

			$filtered[] = $item;
		}

		return $filtered;
	}

	public function filter_home_url( string $url, string $path ): string {
		if ( is_admin() ) {
			return $url;
		}
		return $this->add_lang_to_url( $url, $this->languages->get_current_slug() );
	}

	public function filter_object_permalink( string $url, $post ): string {
		if ( is_admin() ) {
			return $url;
		}
		$post_id = is_object( $post ) ? (int) $post->ID : (int) $post;
		$lang    = $this->translations->get_object_language( $post_id, 'post' ) ?: $this->languages->get_current_slug();
		return $this->add_lang_to_url( $url, $lang );
	}

	public function filter_term_link( string $url, $term, string $taxonomy ): string {
		if ( is_admin() || ! is_object( $term ) ) {
			return $url;
		}
		$lang = $this->translations->get_object_language( (int) $term->term_id, 'term' ) ?: $this->languages->get_current_slug();
		return $this->add_lang_to_url( $url, $lang );
	}

	public function get_switch_url( string $target_lang ): string {
		if ( is_singular() ) {
			$post_id = get_queried_object_id();
			$tr_id   = $this->translations->get_translation( $post_id, $target_lang, 'post' );
			if ( $tr_id > 0 ) {
				return $this->add_lang_to_url( (string) get_permalink( $tr_id ), $target_lang );
			}
		} elseif ( is_tax() || is_category() || is_tag() ) {
			$term = get_queried_object();
			if ( $term && isset( $term->term_id ) ) {
				$tr_id = $this->translations->get_translation( (int) $term->term_id, $target_lang, 'term' );
				if ( $tr_id > 0 ) {
					$link = get_term_link( $tr_id, $term->taxonomy );
					if ( ! is_wp_error( $link ) ) {
						return $this->add_lang_to_url( $link, $target_lang );
					}
				}
			}
		}

		$current_url = ( is_ssl() ? 'https://' : 'http://' )
			. ( isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' )
			. ( isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/' );

		return $this->add_lang_to_url( $current_url, $target_lang );
	}

	public function add_lang_to_url( string $url, string $lang_slug ): string {
		$settings     = GM_Plugin::get_settings();
		$default_slug = $this->languages->get_default_slug();
		$hide_default = ! empty( $settings['hide_default_lang_url'] );

		if ( 'query' === ( $settings['url_mode'] ?? 'prefix' ) ) {
			$url    = remove_query_arg( 'lang', $url );
			$parsed = wp_parse_url( $url );
			if ( ! empty( $parsed['path'] ) && ! str_ends_with( $parsed['path'], '/' ) && ! str_contains( basename( $parsed['path'] ), '.' ) ) {
				$url = str_replace( $parsed['path'], $parsed['path'] . '/', $url );
			}
			if ( $hide_default && $lang_slug === $default_slug ) {
				return $url;
			}
			return add_query_arg( 'lang', $lang_slug, $url );
		}

		// Prefix mode: strip any existing language prefix first.
		$parsed = wp_parse_url( $url );
		$home   = wp_parse_url( home_url( '/' ) );
		$base   = rtrim( $home['path'] ?? '/', '/' );
		$path   = $parsed['path'] ?? '/';
		$rel    = ltrim( substr( $path, strlen( $base ) ), '/' );

		$all_slugs = array_keys( $this->languages->get_languages( false ) );
		$parts     = '' !== $rel ? explode( '/', $rel ) : array();
		if ( ! empty( $parts[0] ) && in_array( $parts[0], $all_slugs, true ) ) {
			array_shift( $parts );
		}

		if ( ! ( $hide_default && $lang_slug === $default_slug ) ) {
			array_unshift( $parts, $lang_slug );
		}

		$new_path = $base . '/' . implode( '/', $parts );
		if ( str_ends_with( $path, '/' ) && ! str_ends_with( $new_path, '/' ) ) {
			$new_path .= '/';
		}

		$scheme = isset( $parsed['scheme'] ) ? $parsed['scheme'] . '://' : '';
		$host   = $parsed['host'] ?? '';
		$port   = isset( $parsed['port'] ) ? ':' . $parsed['port'] : '';
		$query  = isset( $parsed['query'] ) ? '?' . $parsed['query'] : '';

		return $scheme . $host . $port . $new_path . $query;
	}

	public function output_hreflang_tags(): void {
		if ( is_404() || is_search() ) {
			return;
		}
		if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) || ( function_exists( 'is_account_page' ) && is_account_page() ) ) {
			return;
		}
		$langs = $this->languages->get_languages( true );
		if ( count( $langs ) <= 1 ) {
			return;
		}
		$am_map       = array(
			'hy' => 'hy-AM',
			'en' => 'en-AM',
			'ru' => 'ru-AM',
		);
		$default_slug = $this->languages->get_default_slug();
		$default_url  = '';
		foreach ( $langs as $lang ) {
			$url = $this->get_switch_url( $lang['slug'] );
			if ( $lang['slug'] === $default_slug ) {
				$default_url = $url;
			}
			$hreflang = $am_map[ $lang['slug'] ] ?? str_replace( '_', '-', $lang['locale'] ?: $lang['slug'] );
			printf(
				"<link rel=\"alternate\" hreflang=\"%s\" href=\"%s\" />\n",
				esc_attr( $hreflang ),
				esc_url( $url )
			);
		}
		if ( '' !== $default_url ) {
			printf(
				"<link rel=\"alternate\" hreflang=\"x-default\" href=\"%s\" />\n",
				esc_url( $default_url )
			);
		}
	}

	public function render_language_switcher_shortcode( $atts = array() ): string {
		$atts  = shortcode_atts( array( 'show_flags' => '1', 'show_names' => '1' ), $atts );
		$langs = $this->languages->get_languages( true );
		$curr  = $this->languages->get_current_slug();

		if ( empty( $langs ) ) {
			return '';
		}

		$html = '<ul class="gm-language-switcher" style="display:flex;gap:10px;list-style:none;margin:0;padding:0;">';
		foreach ( $langs as $lang ) {
			$active = ( $lang['slug'] === $curr ) ? ' class="gm-lang-active" style="font-weight:700;"' : '';
			$label  = '';
			if ( '1' === (string) $atts['show_flags'] ) {
				$label .= esc_html( $lang['flag'] ) . ' ';
			}
			if ( '1' === (string) $atts['show_names'] ) {
				$label .= esc_html( $lang['name'] );
			}
			$html .= sprintf(
				'<li%s><a href="%s" hreflang="%s">%s</a></li>',
				$active,
				esc_url( $this->get_switch_url( $lang['slug'] ) ),
				esc_attr( $lang['slug'] ),
				trim( $label )
			);
		}
		$html .= '</ul>';
		return $html;
	}
}
