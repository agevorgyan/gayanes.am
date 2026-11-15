<?php
/**
 * Plugin Name:       Gayane's Kitchen Core
 * Plugin URI:        https://gayanes.am/
 * Description:       Modular eCommerce functionality for Gayane's Kitchen: WooCommerce AJAX quantity & side cart, Polylang/WPML multilingual helpers, technical SEO & JSON-LD schema, performance optimization, and security hardening.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Gayane's Kitchen Engineering
 * Text Domain:       gayanes-core
 * Domain Path:       /languages
 *
 * @package Gayanes_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'GAYANES_CORE_VERSION', '1.0.0' );
define( 'GAYANES_CORE_FILE', __FILE__ );
define( 'GAYANES_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'GAYANES_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once GAYANES_CORE_DIR . 'includes/class-security.php';
require_once GAYANES_CORE_DIR . 'includes/class-multilingual.php';
require_once GAYANES_CORE_DIR . 'includes/class-ajax-cart.php';
require_once GAYANES_CORE_DIR . 'includes/class-seo-schema.php';
require_once GAYANES_CORE_DIR . 'includes/class-performance.php';
require_once GAYANES_CORE_DIR . 'includes/class-checkout-delivery.php';

/**
 * Bootstrap Gayanes Core modules after plugins are loaded.
 */
function gayanes_core_init(): void {
	Gayanes_Core\Security::init();
	Gayanes_Core\Multilingual::init();
	Gayanes_Core\Performance::init();
	Gayanes_Core\SEO_Schema::init();

	if ( class_exists( 'WooCommerce' ) ) {
		Gayanes_Core\Ajax_Cart::init();
		Gayanes_Core\Checkout_Delivery::init();
	}
}
add_action( 'plugins_loaded', 'gayanes_core_init', 20 );

/**
 * Activation hook: schedule transient cleanup without touching existing store data.
 */
function gayanes_core_activate(): void {
	if ( ! wp_next_scheduled( 'gayanes_core_cleanup_transients' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'gayanes_core_cleanup_transients' );
	}
}
register_activation_hook( __FILE__, 'gayanes_core_activate' );

/**
 * Deactivation hook: unschedule custom cron events.
 */
function gayanes_core_deactivate(): void {
	wp_clear_scheduled_hook( 'gayanes_core_cleanup_transients' );
}
register_deactivation_hook( __FILE__, 'gayanes_core_deactivate' );

