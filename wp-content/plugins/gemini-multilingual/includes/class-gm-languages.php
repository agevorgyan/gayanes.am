<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GM_Languages {

	private ?string $current_lang = null;

	/**
	 * Predefined language catalog for quick-add in Settings.
	 */
	public static function get_catalog(): array {
		return array(
			'hy' => array( 'slug' => 'hy', 'name' => 'Հայերեն (Armenian)', 'locale' => 'hy', 'flag' => '🇦🇲', 'rtl' => false ),
			'en' => array( 'slug' => 'en', 'name' => 'English', 'locale' => 'en_US', 'flag' => '🇺🇸', 'rtl' => false ),
			'ru' => array( 'slug' => 'ru', 'name' => 'Русский (Russian)', 'locale' => 'ru_RU', 'flag' => '🇷🇺', 'rtl' => false ),
			'fr' => array( 'slug' => 'fr', 'name' => 'Français (French)', 'locale' => 'fr_FR', 'flag' => '🇫🇷', 'rtl' => false ),
			'de' => array( 'slug' => 'de', 'name' => 'Deutsch (German)', 'locale' => 'de_DE', 'flag' => '🇩🇪', 'rtl' => false ),
			'es' => array( 'slug' => 'es', 'name' => 'Español (Spanish)', 'locale' => 'es_ES', 'flag' => '🇪🇸', 'rtl' => false ),
			'it' => array( 'slug' => 'it', 'name' => 'Italiano (Italian)', 'locale' => 'it_IT', 'flag' => '🇮🇹', 'rtl' => false ),
			'ka' => array( 'slug' => 'ka', 'name' => 'ქართული (Georgian)', 'locale' => 'ka_GE', 'flag' => '🇬🇪', 'rtl' => false ),
			'ar' => array( 'slug' => 'ar', 'name' => 'العربية (Arabic)', 'locale' => 'ar', 'flag' => '🇦🇪', 'rtl' => true ),
			'fa' => array( 'slug' => 'fa', 'name' => 'فارسی (Persian)', 'locale' => 'fa_IR', 'flag' => '🇮🇷', 'rtl' => true ),
			'zh' => array( 'slug' => 'zh', 'name' => '中文 (Chinese)', 'locale' => 'zh_CN', 'flag' => '🇨🇳', 'rtl' => false ),
			'ja' => array( 'slug' => 'ja', 'name' => '日本語 (Japanese)', 'locale' => 'ja', 'flag' => '🇯🇵', 'rtl' => false ),
			'pt' => array( 'slug' => 'pt', 'name' => 'Português (Portuguese)', 'locale' => 'pt_PT', 'flag' => '🇵🇹', 'rtl' => false ),
			'nl' => array( 'slug' => 'nl', 'name' => 'Nederlands (Dutch)', 'locale' => 'nl_NL', 'flag' => '🇳🇱', 'rtl' => false ),
			'pl' => array( 'slug' => 'pl', 'name' => 'Polski (Polish)', 'locale' => 'pl_PL', 'flag' => '🇵🇱', 'rtl' => false ),
			'uk' => array( 'slug' => 'uk', 'name' => 'Українська (Ukrainian)', 'locale' => 'uk', 'flag' => '🇺🇦', 'rtl' => false ),
			'el' => array( 'slug' => 'el', 'name' => 'Ελληνικά (Greek)', 'locale' => 'el', 'flag' => '🇬🇷', 'rtl' => false ),
		);
	}

	public function get_languages( bool $only_active = true ): array {
		$langs = get_option( 'gm_languages', array() );
		if ( ! is_array( $langs ) || empty( $langs ) ) {
			return array();
		}

		if ( $only_active ) {
			$langs = array_filter( $langs, static fn( $l ) => ! empty( $l['active'] ) );
		}

		uasort( $langs, static fn( $a, $b ) => (int) ( $a['order'] ?? 0 ) <=> (int) ( $b['order'] ?? 0 ) );
		return $langs;
	}

	public function get_language( string $slug ): ?array {
		$langs = $this->get_languages( false );
		return $langs[ $slug ] ?? null;
	}

	public function get_language_by_locale( string $locale ): ?array {
		foreach ( $this->get_languages( false ) as $lang ) {
			if ( $lang['locale'] === $locale ) {
				return $lang;
			}
		}
		return null;
	}

	public function get_default_language(): ?array {
		$langs = $this->get_languages( false );
		foreach ( $langs as $lang ) {
			if ( ! empty( $lang['is_default'] ) ) {
				return $lang;
			}
		}
		return ! empty( $langs ) ? reset( $langs ) : null;
	}

	public function get_default_slug(): string {
		$def = $this->get_default_language();
		return $def ? $def['slug'] : 'en';
	}

	public function get_current_slug(): string {
		if ( null !== $this->current_lang ) {
			return $this->current_lang;
		}
		return $this->get_default_slug();
	}

	public function get_current_language(): ?array {
		return $this->get_language( $this->get_current_slug() );
	}

	public function set_current_slug( string $slug ): void {
		$langs = $this->get_languages( true );
		if ( isset( $langs[ $slug ] ) ) {
			$this->current_lang = $slug;
		}
	}

	public function save_language( array $data ): bool {
		$slug = sanitize_key( $data['slug'] ?? '' );
		if ( '' === $slug ) {
			return false;
		}

		$langs      = $this->get_languages( false );
		$is_default = ! empty( $data['is_default'] );

		if ( $is_default ) {
			foreach ( $langs as &$existing ) {
				$existing['is_default'] = false;
			}
			unset( $existing );
		}

		if ( empty( $langs ) ) {
			$is_default = true;
		}

		$langs[ $slug ] = array(
			'slug'       => $slug,
			'name'       => sanitize_text_field( $data['name'] ?? strtoupper( $slug ) ),
			'locale'     => sanitize_text_field( $data['locale'] ?? $slug ),
			'flag'       => sanitize_text_field( $data['flag'] ?? '🌐' ),
			'rtl'        => ! empty( $data['rtl'] ),
			'is_default' => $is_default,
			'active'     => isset( $data['active'] ) ? (bool) $data['active'] : true,
			'order'      => isset( $data['order'] ) ? (int) $data['order'] : count( $langs ),
		);

		return update_option( 'gm_languages', $langs );
	}

	public function delete_language( string $slug ): bool {
		$langs = $this->get_languages( false );
		if ( ! isset( $langs[ $slug ] ) || ! empty( $langs[ $slug ]['is_default'] ) ) {
			return false;
		}
		unset( $langs[ $slug ] );
		return update_option( 'gm_languages', $langs );
	}

	public function set_default_language( string $slug ): bool {
		$langs = $this->get_languages( false );
		if ( ! isset( $langs[ $slug ] ) ) {
			return false;
		}
		foreach ( $langs as $k => &$lang ) {
			$lang['is_default'] = ( $k === $slug );
			if ( $k === $slug ) {
				$lang['active'] = true;
			}
		}
		unset( $lang );
		return update_option( 'gm_languages', $langs );
	}
}
