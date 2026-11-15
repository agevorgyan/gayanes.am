<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GM_Installer {

	public static function activate(): void {
		self::create_tables();
		self::seed_defaults();
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	public static function create_tables(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$tr_table        = $wpdb->prefix . 'gm_translations';
		$str_table       = $wpdb->prefix . 'gm_strings';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql_tr = "CREATE TABLE {$tr_table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			group_id VARCHAR(64) NOT NULL,
			object_type VARCHAR(32) NOT NULL DEFAULT 'post',
			object_id BIGINT UNSIGNED NOT NULL,
			lang VARCHAR(16) NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY obj_unique (object_type, object_id),
			KEY group_lang (group_id, lang),
			KEY lang_type (lang, object_type)
		) {$charset_collate};";

		$sql_str = "CREATE TABLE {$str_table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			domain VARCHAR(128) NOT NULL,
			context VARCHAR(191) NOT NULL DEFAULT '',
			source_hash CHAR(32) NOT NULL,
			original LONGTEXT NOT NULL,
			lang VARCHAR(16) NOT NULL,
			translation LONGTEXT NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'untranslated',
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY domain_hash_lang (domain, source_hash, lang),
			KEY domain_lang (domain, lang),
			KEY status_lang (status, lang)
		) {$charset_collate};";

		dbDelta( $sql_tr );
		dbDelta( $sql_str );
		update_option( 'gm_db_version', GM_VERSION );
	}

	private static function seed_defaults(): void {
		if ( false === get_option( 'gm_languages' ) ) {
			$site_locale = get_locale();
			$default_lang = array(
				'slug'       => 'en',
				'name'       => 'English',
				'locale'     => 'en_US',
				'flag'       => '🇺🇸',
				'rtl'        => false,
				'is_default' => true,
				'active'     => true,
				'order'      => 0,
			);

			if ( str_starts_with( $site_locale, 'hy' ) ) {
				$default_lang = array(
					'slug'       => 'hy',
					'name'       => 'Հայերեն',
					'locale'     => 'hy',
					'flag'       => '🇦🇲',
					'rtl'        => false,
					'is_default' => true,
					'active'     => true,
					'order'      => 0,
				);
			} elseif ( str_starts_with( $site_locale, 'ru' ) ) {
				$default_lang = array(
					'slug'       => 'ru',
					'name'       => 'Русский',
					'locale'     => 'ru_RU',
					'flag'       => '🇷🇺',
					'rtl'        => false,
					'is_default' => true,
					'active'     => true,
					'order'      => 0,
				);
			}

			update_option( 'gm_languages', array( $default_lang['slug'] => $default_lang ) );
		}

		if ( false === get_option( 'gm_settings' ) ) {
			update_option( 'gm_settings', GM_Plugin::get_settings() );
		}
	}
}
