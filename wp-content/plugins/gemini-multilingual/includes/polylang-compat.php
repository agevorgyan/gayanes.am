<?php
/**
 * Polylang API compatibility shims so themes & plugins calling pll_* functions
 * continue to work seamlessly when Gemini AI Multilingual is active instead of Polylang.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'pll_current_language' ) ) {
	function pll_current_language( string $field = 'slug' ) {
		$lang = gm_plugin()->languages->get_current_language();
		if ( ! $lang ) {
			return false;
		}
		return $lang[ $field ] ?? $lang['slug'];
	}
}

if ( ! function_exists( 'pll_default_language' ) ) {
	function pll_default_language( string $field = 'slug' ) {
		$lang = gm_plugin()->languages->get_default_language();
		if ( ! $lang ) {
			return false;
		}
		return $lang[ $field ] ?? $lang['slug'];
	}
}

if ( ! function_exists( 'pll_languages_list' ) ) {
	function pll_languages_list( array $args = array() ): array {
		$field = $args['fields'] ?? 'slug';
		$list  = array();
		foreach ( gm_plugin()->languages->get_languages( true ) as $lang ) {
			$list[] = $lang[ $field ] ?? $lang['slug'];
		}
		return $list;
	}
}

if ( ! function_exists( 'pll_the_languages' ) ) {
	function pll_the_languages( array $args = array() ) {
		$langs = gm_plugin()->languages->get_languages( true );
		$curr  = gm_plugin()->languages->get_current_slug();
		$raw   = array();

		foreach ( $langs as $slug => $lang ) {
			$raw[ $slug ] = array(
				'id'           => $slug,
				'slug'         => $slug,
				'name'         => $lang['name'],
				'locale'       => $lang['locale'],
				'flag'         => $lang['flag'],
				'url'          => gm_plugin()->router->get_switch_url( $slug ),
				'current_lang' => ( $slug === $curr ),
				'no_translation' => false,
			);
		}

		if ( ! empty( $args['raw'] ) ) {
			return $raw;
		}

		$out = gm_plugin()->router->render_language_switcher_shortcode();
		if ( isset( $args['echo'] ) && ! $args['echo'] ) {
			return $out;
		}
		echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return '';
	}
}

if ( ! function_exists( 'pll_get_post' ) ) {
	function pll_get_post( int $post_id, string $slug = '' ) {
		$target = '' !== $slug ? $slug : gm_plugin()->languages->get_current_slug();
		$tr_id  = gm_plugin()->translations->get_translation( $post_id, $target, 'post' );
		return $tr_id > 0 ? $tr_id : false;
	}
}

if ( ! function_exists( 'pll_get_term' ) ) {
	function pll_get_term( int $term_id, string $slug = '' ) {
		$target = '' !== $slug ? $slug : gm_plugin()->languages->get_current_slug();
		$tr_id  = gm_plugin()->translations->get_translation( $term_id, $target, 'term' );
		return $tr_id > 0 ? $tr_id : false;
	}
}

if ( ! function_exists( 'pll_register_string' ) ) {
	function pll_register_string( string $name, string $string, string $group = 'polylang', bool $multiline = false ): void {
		foreach ( gm_plugin()->languages->get_languages( true ) as $lang ) {
			gm_plugin()->strings->upsert_source_string( 'polylang', $string, $lang['slug'], $group );
		}
	}
}

if ( ! function_exists( 'pll__' ) ) {
	function pll__( string $string ): string {
		return gm_plugin()->strings->translate_registered_string( $string, 'polylang' );
	}
}

if ( ! function_exists( 'pll_e' ) ) {
	function pll_e( string $string ): void {
		echo esc_html( pll__( $string ) );
	}
}
