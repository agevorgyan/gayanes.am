<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GM_String_Translator {

	private GM_Languages $languages;
	private GM_Gemini_Client $gemini;
	private string $table;

	/** @var array<string, array<string, string>> Runtime cache [lang][domain|context|original] => translation */
	private array $runtime_cache = array();
	private bool $loaded_runtime = false;

	public function __construct( GM_Languages $languages, GM_Gemini_Client $gemini ) {
		global $wpdb;
		$this->languages = $languages;
		$this->gemini    = $gemini;
		$this->table     = $wpdb->prefix . 'gm_strings';

		// Runtime gettext hooks on frontend (and AJAX).
		if ( ! is_admin() || wp_doing_ajax() ) {
			add_filter( 'gettext', array( $this, 'filter_gettext' ), 20, 3 );
			add_filter( 'gettext_with_context', array( $this, 'filter_gettext_with_context' ), 20, 4 );
			add_filter( 'ngettext', array( $this, 'filter_ngettext' ), 20, 5 );
		}
	}

	/**
	 * Lists all installed themes and plugins with their text domains & paths.
	 */
	public function get_scannable_packages(): array {
		$packages = array();

		if ( ! function_exists( 'wp_get_themes' ) ) {
			require_once ABSPATH . 'wp-includes/theme.php';
		}
		foreach ( wp_get_themes() as $slug => $theme ) {
			$domain     = $theme->get( 'TextDomain' ) ?: $slug;
			$packages[] = array(
				'id'     => 'theme:' . $slug,
				'type'   => 'theme',
				'name'   => $theme->get( 'Name' ) . ( get_stylesheet() === $slug ? ' (Active Theme)' : '' ),
				'domain' => $domain,
				'path'   => $theme->get_stylesheet_directory(),
			);
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		foreach ( get_plugins() as $plugin_file => $plugin_data ) {
			$dir = dirname( WP_PLUGIN_DIR . '/' . $plugin_file );
			if ( '.' === dirname( $plugin_file ) ) {
				$dir = WP_PLUGIN_DIR . '/' . $plugin_file;
			}
			$domain = ! empty( $plugin_data['TextDomain'] ) ? $plugin_data['TextDomain'] : sanitize_key( dirname( $plugin_file ) );
			if ( 'gemini-multilingual' === $domain ) {
				continue;
			}
			$packages[] = array(
				'id'     => 'plugin:' . $plugin_file,
				'type'   => 'plugin',
				'name'   => $plugin_data['Name'] . ( is_plugin_active( $plugin_file ) ? ' (Active)' : '' ),
				'domain' => $domain,
				'path'   => $dir,
			);
		}

		// Also include registered Polylang/Theme custom strings.
		$packages[] = array(
			'id'     => 'custom:polylang',
			'type'   => 'custom',
			'name'   => 'Registered Theme / Polylang Strings',
			'domain' => 'polylang',
			'path'   => '',
		);

		return $packages;
	}

	/**
	 * Scans a theme or plugin directory for translatable strings (.pot/.po + PHP gettext calls)
	 * and seeds untranslated rows for every active non-default language.
	 */
	public function scan_package( string $package_id ): array {
		$packages = $this->get_scannable_packages();
		$target   = null;
		foreach ( $packages as $pkg ) {
			if ( $pkg['id'] === $package_id ) {
				$target = $pkg;
				break;
			}
		}

		if ( ! $target ) {
			return array( 'found' => 0, 'inserted' => 0 );
		}

		$domain  = $target['domain'];
		$path    = $target['path'];
		$strings = array(); // [ md5 => [ 'original' => ..., 'context' => ... ] ]

		if ( '' !== $path && file_exists( $path ) ) {
			// 1. Parse .pot / .po files if present.
			$pot_files = $this->find_files( $path, array( 'pot', 'po' ) );
			foreach ( $pot_files as $pot_file ) {
				foreach ( $this->parse_po_file( $pot_file ) as $entry ) {
					if ( '' === trim( $entry['original'] ) ) {
						continue;
					}
					$hash             = md5( $domain . '|' . $entry['context'] . '|' . $entry['original'] );
					$strings[ $hash ] = $entry;
				}
			}

			// 2. Scan PHP files for gettext calls (__, _e, _x, _n, esc_html__, esc_html_e, esc_attr__, esc_attr_e).
			$php_files = is_file( $path ) ? array( $path ) : $this->find_files( $path, array( 'php' ) );
			foreach ( $php_files as $php_file ) {
				// Skip vendor/node_modules for speed.
				if ( str_contains( $php_file, '/vendor/' ) || str_contains( $php_file, '/node_modules/' ) ) {
					continue;
				}
				foreach ( $this->extract_php_gettext( $php_file ) as $entry ) {
					if ( '' === trim( $entry['original'] ) ) {
						continue;
					}
					$hash             = md5( $domain . '|' . $entry['context'] . '|' . $entry['original'] );
					$strings[ $hash ] = $entry;
				}
			}
		}

		$inserted = 0;
		$langs    = $this->languages->get_languages( true );
		foreach ( $langs as $lang ) {
			foreach ( $strings as $hash => $entry ) {
				$inserted += $this->upsert_source_string( $domain, $entry['original'], $lang['slug'], $entry['context'] );
			}
		}

		$this->clear_cache();

		return array(
			'domain'   => $domain,
			'found'    => count( $strings ),
			'inserted' => $inserted,
		);
	}

	public function upsert_source_string( string $domain, string $original, string $lang, string $context = '' ): int {
		global $wpdb;
		$hash = md5( $domain . '|' . $context . '|' . $original );

		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$this->table} WHERE domain = %s AND source_hash = %s AND lang = %s LIMIT 1",
				$domain,
				$hash,
				$lang
			)
		);

		if ( $exists ) {
			return 0;
		}

		$wpdb->insert(
			$this->table,
			array(
				'domain'      => $domain,
				'context'     => $context,
				'source_hash' => $hash,
				'original'    => $original,
				'lang'        => $lang,
				'translation' => '',
				'status'      => 'untranslated',
				'updated_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return 1;
	}

	public function save_translation( int $id, string $translation, string $status = 'manual' ): bool {
		global $wpdb;
		$updated = $wpdb->update(
			$this->table,
			array(
				'translation' => $translation,
				'status'      => $status,
				'updated_at'  => current_time( 'mysql' ),
			),
			array( 'id' => $id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);
		$this->clear_cache();
		return false !== $updated;
	}

	/**
	 * Queries strings for the admin UI table.
	 */
	public function query_strings( array $args = array() ): array {
		global $wpdb;

		$domain = sanitize_text_field( $args['domain'] ?? '' );
		$lang   = sanitize_key( $args['lang'] ?? $this->languages->get_default_slug() );
		$status = sanitize_key( $args['status'] ?? '' );
		$search = sanitize_text_field( $args['search'] ?? '' );
		$limit  = max( 1, min( 200, (int) ( $args['limit'] ?? 50 ) ) );
		$offset = max( 0, (int) ( $args['offset'] ?? 0 ) );

		$where  = array( 'lang = %s' );
		$params = array( $lang );

		if ( '' !== $domain ) {
			$where[]  = 'domain = %s';
			$params[] = $domain;
		}
		if ( '' !== $status && in_array( $status, array( 'untranslated', 'ai', 'manual' ), true ) ) {
			$where[]  = 'status = %s';
			$params[] = $status;
		}
		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(original LIKE %s OR translation LIKE %s)';
			$params[] = $like;
			$params[] = $like;
		}

		$where_sql = implode( ' AND ', $where );

		$total = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$this->table} WHERE {$where_sql}", ...$params )
		);

		$query_params   = $params;
		$query_params[] = $limit;
		$query_params[] = $offset;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, domain, context, original, lang, translation, status, updated_at
				 FROM {$this->table}
				 WHERE {$where_sql}
				 ORDER BY id ASC
				 LIMIT %d OFFSET %d",
				...$query_params
			),
			ARRAY_A
		);

		return array(
			'total' => $total,
			'rows'  => $rows ?: array(),
		);
	}

	/**
	 * Batch-translates up to $batch_size untranslated strings for a domain & language via Gemini AI.
	 *
	 * @return array|WP_Error
	 */
	public function ai_translate_domain_batch( string $domain, string $target_lang, int $batch_size = 35 ) {
		global $wpdb;

		$target_lang_obj = $this->languages->get_language( $target_lang );
		if ( ! $target_lang_obj ) {
			return new WP_Error( 'gm_invalid_lang', __( 'Invalid target language.', 'gemini-multilingual' ) );
		}

		$source_lang_obj  = $this->languages->get_default_language();
		$source_lang_name = $source_lang_obj ? $source_lang_obj['name'] : 'English';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, context, original FROM {$this->table}
				 WHERE domain = %s AND lang = %s AND status = 'untranslated'
				 ORDER BY id ASC
				 LIMIT %d",
				$domain,
				$target_lang,
				$batch_size
			),
			ARRAY_A
		);

		if ( empty( $rows ) ) {
			return array(
				'translated' => 0,
				'remaining'  => 0,
			);
		}

		$items = array();
		foreach ( $rows as $row ) {
			$items[] = array(
				'id'      => (int) $row['id'],
				'text'    => (string) $row['original'],
				'context' => (string) $row['context'],
			);
		}

		$translated_map = $this->gemini->translate_batch_strings(
			$items,
			$source_lang_name,
			$target_lang_obj['name'],
			$domain
		);

		if ( is_wp_error( $translated_map ) ) {
			return $translated_map;
		}

		$count = 0;
		foreach ( $translated_map as $id => $tr_text ) {
			$wpdb->update(
				$this->table,
				array(
					'translation' => $tr_text,
					'status'      => 'ai',
					'updated_at'  => current_time( 'mysql' ),
				),
				array( 'id' => (int) $id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);
			++$count;
		}

		$remaining = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table} WHERE domain = %s AND lang = %s AND status = 'untranslated'",
				$domain,
				$target_lang
			)
		);

		$this->clear_cache();

		return array(
			'translated' => $count,
			'remaining'  => $remaining,
		);
	}

	/**
	 * Runtime gettext filters.
	 */
	public function filter_gettext( string $translation, string $text, string $domain ): string {
		$found = $this->lookup_runtime( $text, $domain, '' );
		return null !== $found ? $found : $translation;
	}

	public function filter_gettext_with_context( string $translation, string $text, string $context, string $domain ): string {
		$found = $this->lookup_runtime( $text, $domain, $context );
		return null !== $found ? $found : $translation;
	}

	public function filter_ngettext( string $translation, string $single, string $plural, int $number, string $domain ): string {
		$source = ( 1 === $number ) ? $single : $plural;
		$found  = $this->lookup_runtime( $source, $domain, '' );
		return null !== $found ? $found : $translation;
	}

	public function translate_registered_string( string $text, string $domain = 'polylang' ): string {
		$found = $this->lookup_runtime( $text, $domain, '' );
		return null !== $found ? $found : $text;
	}

	private function lookup_runtime( string $text, string $domain, string $context ): ?string {
		if ( '' === $text ) {
			return null;
		}

		$lang = $this->languages->get_current_slug();
		if ( $lang === $this->languages->get_default_slug() ) {
			return null;
		}

		$this->ensure_runtime_loaded( $lang );

		$key = $domain . '|' . $context . '|' . $text;
		if ( isset( $this->runtime_cache[ $lang ][ $key ] ) ) {
			return $this->runtime_cache[ $lang ][ $key ];
		}

		// Fallback without context or default domain.
		$fallback_key = 'default|' . $context . '|' . $text;
		return $this->runtime_cache[ $lang ][ $fallback_key ] ?? null;
	}

	private function ensure_runtime_loaded( string $lang ): void {
		if ( $this->loaded_runtime && isset( $this->runtime_cache[ $lang ] ) ) {
			return;
		}

		$cache_key = 'gm_str_map_' . $lang;
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			$this->runtime_cache[ $lang ] = $cached;
			$this->loaded_runtime         = true;
			return;
		}

		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT domain, context, original, translation FROM {$this->table}
				 WHERE lang = %s AND status != 'untranslated' AND translation != ''",
				$lang
			),
			ARRAY_A
		);

		$map = array();
		foreach ( $rows ?: array() as $r ) {
			$k         = $r['domain'] . '|' . $r['context'] . '|' . $r['original'];
			$map[ $k ] = $r['translation'];
		}

		set_transient( $cache_key, $map, 6 * HOUR_IN_SECONDS );
		$this->runtime_cache[ $lang ] = $map;
		$this->loaded_runtime         = true;
	}

	public function clear_cache(): void {
		foreach ( $this->languages->get_languages( false ) as $lang ) {
			delete_transient( 'gm_str_map_' . $lang['slug'] );
		}
		$this->runtime_cache  = array();
		$this->loaded_runtime = false;
	}

	private function find_files( string $dir, array $extensions ): array {
		$results = array();
		if ( ! is_dir( $dir ) ) {
			return $results;
		}
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS )
		);
		foreach ( $iterator as $file ) {
			if ( $file->isFile() && in_array( strtolower( $file->getExtension() ), $extensions, true ) ) {
				$results[] = $file->getPathname();
			}
		}
		return $results;
	}

	private function parse_po_file( string $file ): array {
		$content = @file_get_contents( $file );
		if ( false === $content ) {
			return array();
		}

		$entries = array();
		$blocks  = preg_split( "/\R{2,}/", $content );
		foreach ( $blocks as $block ) {
			$msgid   = '';
			$msgctxt = '';
			if ( preg_match( '/^msgctxt\s+"(.*)"/m', $block, $m ) ) {
				$msgctxt = stripcslashes( $m[1] );
			}
			if ( preg_match( '/msgid\s+"((?:[^"\\\\]|\\\\.)*)"(?:\s*\R\s*"((?:[^"\\\\]|\\\\.)*)")*/s', $block, $m ) ) {
				// Collect all quoted lines after msgid until msgstr/msgid_plural.
				if ( preg_match( '/msgid\s+((?:"(?:[^"\\\\]|\\\\.)*"\s*)+)/s', $block, $all_m ) ) {
					preg_match_all( '/"((?:[^"\\\\]|\\\\.)*)"/', $all_m[1], $parts );
					$msgid = stripcslashes( implode( '', $parts[1] ) );
				}
			}
			if ( '' !== $msgid ) {
				$entries[] = array(
					'original' => $msgid,
					'context'  => $msgctxt,
				);
			}
		}
		return $entries;
	}

	private function extract_php_gettext( string $file ): array {
		$content = @file_get_contents( $file );
		if ( false === $content ) {
			return array();
		}

		$entries = array();
		$pattern = '/\b(?:__|esc_html__|esc_attr__|_e|esc_html_e|esc_attr_e|_n|_x|esc_html_x|esc_attr_x)\s*\(\s*([\'"])((?:\\\\.|(?!\1).)+)\1/s';
		if ( preg_match_all( $pattern, $content, $matches ) ) {
			foreach ( $matches[2] as $raw ) {
				$str = stripcslashes( $raw );
				if ( '' !== trim( $str ) ) {
					$entries[] = array(
						'original' => $str,
						'context'  => '',
					);
				}
			}
		}
		return $entries;
	}
}
