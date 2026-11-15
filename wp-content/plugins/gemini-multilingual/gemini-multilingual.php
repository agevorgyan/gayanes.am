<?php
/**
 * Plugin Name:       Gemini AI Multilingual
 * Plugin URI:        https://gayanes.am
 * Description:       Unified WordPress & WooCommerce multilingual plugin powered by Google Gemini AI (Gemini 4 & 3.8). Translates posts, pages, WooCommerce products, taxonomies, themes, and plugins with one click.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Gayanes Team
 * Text Domain:       gemini-multilingual
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GM_VERSION', '1.0.0' );
define( 'GM_PLUGIN_FILE', __FILE__ );
define( 'GM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once GM_PLUGIN_DIR . 'includes/class-gm-installer.php';
require_once GM_PLUGIN_DIR . 'includes/class-gm-languages.php';
require_once GM_PLUGIN_DIR . 'includes/class-gm-translations.php';
require_once GM_PLUGIN_DIR . 'includes/class-gm-gemini-client.php';
require_once GM_PLUGIN_DIR . 'includes/class-gm-translator.php';
require_once GM_PLUGIN_DIR . 'includes/class-gm-string-translator.php';
require_once GM_PLUGIN_DIR . 'includes/class-gm-router.php';
require_once GM_PLUGIN_DIR . 'includes/class-gm-woocommerce.php';
require_once GM_PLUGIN_DIR . 'includes/class-gm-admin.php';
require_once GM_PLUGIN_DIR . 'includes/polylang-compat.php';

register_activation_hook( __FILE__, array( 'GM_Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GM_Installer', 'deactivate' ) );

final class GM_Plugin {

	private static ?GM_Plugin $instance = null;

	public GM_Languages $languages;
	public GM_Translations $translations;
	public GM_Gemini_Client $gemini;
	public GM_Translator $translator;
	public GM_String_Translator $strings;
	public GM_Router $router;
	public ?GM_WooCommerce $woocommerce = null;
	public ?GM_Admin $admin = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->languages    = new GM_Languages();
		$this->translations = new GM_Translations();
		$this->gemini       = new GM_Gemini_Client();
		$this->translator   = new GM_Translator( $this->languages, $this->translations, $this->gemini );
		$this->strings      = new GM_String_Translator( $this->languages, $this->gemini );
		$this->router       = new GM_Router( $this->languages, $this->translations );

		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), 20 );
	}

	public function on_plugins_loaded(): void {
		load_plugin_textdomain( 'gemini-multilingual', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if ( class_exists( 'WooCommerce' ) ) {
			$this->woocommerce = new GM_WooCommerce( $this->languages, $this->translations, $this->translator );
		}

		if ( is_admin() ) {
			$this->admin = new GM_Admin(
				$this->languages,
				$this->translations,
				$this->gemini,
				$this->translator,
				$this->strings
			);
		}
	}

	/**
	 * Helper to retrieve plugin settings with defaults.
	 */
	public static function get_settings(): array {
		$defaults = array(
			'gemini_api_key'         => '',
			'gemini_model'           => 'gemini-3.8-flash',
			'gemini_custom_model'    => '',
			'url_mode'               => 'prefix', // 'prefix' or 'query'
			'hide_default_lang_url'  => true,
			'translate_slugs'        => true,
			'auto_translate_terms'   => true,
			'wc_sync_stock_price'    => true,
			'wc_sync_media'          => true,
			'wc_sync_attributes'     => true,
			'custom_prompt'          => 'Preserve brand names, HTML markup, WordPress shortcodes, and formatting placeholders.',
			'post_types'             => array( 'post', 'page', 'product' ),
			'taxonomies'             => array( 'category', 'post_tag', 'product_cat', 'product_tag' ),
		);

		$saved = get_option( 'gm_settings', array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		return wp_parse_args( $saved, $defaults );
	}

	public static function get_active_model(): string {
		$settings = self::get_settings();
		if ( 'custom' === $settings['gemini_model'] && ! empty( $settings['gemini_custom_model'] ) ) {
			return sanitize_text_field( $settings['gemini_custom_model'] );
		}
		return ! empty( $settings['gemini_model'] ) ? $settings['gemini_model'] : 'gemini-3.8-flash';
	}
}

function gm_plugin(): GM_Plugin {
	return GM_Plugin::instance();
}

gm_plugin();
