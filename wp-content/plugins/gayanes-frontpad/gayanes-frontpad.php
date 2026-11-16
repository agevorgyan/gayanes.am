<?php
/**
 * Plugin Name:       Gayane's Frontpad Integration
 * Plugin URI:        https://gayanes.am/
 * Description:       Full WooCommerce & Frontpad POS integration: order submission (new_order), status webhooks, product catalog sync (get_products), and stop-list sync (get_stops).
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Gayane's Kitchen Engineering
 * Text Domain:       gayanes-frontpad
 * Domain Path:       /languages
 * WC requires at least: 8.0
 *
 * @package Gayanes_Frontpad
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

define( 'GAYANES_FRONTPAD_VERSION', '1.0.0' );
define( 'GAYANES_FRONTPAD_FILE', __FILE__ );
define( 'GAYANES_FRONTPAD_DIR', plugin_dir_path( __FILE__ ) );
define( 'GAYANES_FRONTPAD_URL', plugin_dir_url( __FILE__ ) );

require_once GAYANES_FRONTPAD_DIR . 'includes/class-api-client.php';
require_once GAYANES_FRONTPAD_DIR . 'includes/class-sync.php';
require_once GAYANES_FRONTPAD_DIR . 'includes/class-orders.php';
require_once GAYANES_FRONTPAD_DIR . 'includes/class-webhook.php';
require_once GAYANES_FRONTPAD_DIR . 'includes/class-admin.php';

/**
 * Declare WooCommerce HPOS compatibility.
 */
add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Bootstrap plugin modules after plugins are loaded.
 */
function gayanes_frontpad_init(): void {
	load_plugin_textdomain( 'gayanes-frontpad', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	Gayanes_Frontpad\Admin::init();
	Gayanes_Frontpad\Webhook::init();

	if ( class_exists( 'WooCommerce' ) ) {
		Gayanes_Frontpad\Sync::init();
		Gayanes_Frontpad\Orders::init();
	}
}
add_action( 'plugins_loaded', 'gayanes_frontpad_init', 25 );

/**
 * Register custom cron intervals (e.g., every 5 / 15 minutes for stop-list sync).
 *
 * @param array<string, array<string, mixed>> $schedules Existing schedules.
 * @return array<string, array<string, mixed>>
 */
function gayanes_frontpad_cron_schedules( array $schedules ): array {
	$schedules['frontpad_5min'] = array(
		'interval' => 5 * MINUTE_IN_SECONDS,
		'display'  => __( 'Every 5 Minutes (Frontpad Stop-List)', 'gayanes-frontpad' ),
	);
	$schedules['frontpad_15min'] = array(
		'interval' => 15 * MINUTE_IN_SECONDS,
		'display'  => __( 'Every 15 Minutes (Frontpad Stop-List)', 'gayanes-frontpad' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'gayanes_frontpad_cron_schedules' );

/**
 * Activation hook: schedule cron events if auto-sync is enabled.
 */
function gayanes_frontpad_activate(): void {
	Gayanes_Frontpad\Sync::reschedule_crons();
}
register_activation_hook( __FILE__, 'gayanes_frontpad_activate' );

/**
 * Deactivation hook: clear scheduled cron events.
 */
function gayanes_frontpad_deactivate(): void {
	wp_clear_scheduled_hook( Gayanes_Frontpad\Sync::CRON_PRODUCTS_HOOK );
	wp_clear_scheduled_hook( Gayanes_Frontpad\Sync::CRON_STOPS_HOOK );
}
register_deactivation_hook( __FILE__, 'gayanes_frontpad_deactivate' );
