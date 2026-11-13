<?php
/**
 * Performance & Caching Safeguards.
 *
 * Optimizes frontend asset loading, image lazy/priority attributes,
 * WooCommerce transient cleanup, and LiteSpeed/CDN cache exclusions
 * for dynamic eCommerce routes (cart, checkout, account, payment callbacks).
 *
 * @package Gayanes_Core
 */

namespace Gayanes_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Performance class.
 */
class Performance {

	/**
	 * Initialize performance hooks.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'disable_wp_bloat' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_unused_assets' ), 100 );
		add_action( 'send_headers', array( __CLASS__, 'protect_dynamic_routes_cache' ) );
		add_action( 'gayanes_core_cleanup_transients', array( __CLASS__, 'cleanup_expired_transients' ) );
	}

	/**
	 * Remove WordPress emoji scripts/styles and unnecessary head links.
	 */
	public static function disable_wp_bloat(): void {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	}

	/**
	 * Dequeue unused block CSS on non-block pages and redundant WooCommerce styles
	 * since gayanes-theme provides lean, purpose-built styles.
	 */
	public static function dequeue_unused_assets(): void {
		if ( is_admin() ) {
			return;
		}

		// Keep block styles only on single blog posts or pages using blocks.
		if ( ! is_singular( 'post' ) ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'wc-blocks-style' );
		}

		// Remove classic theme styles if unused.
		wp_dequeue_style( 'classic-theme-styles' );
		wp_dequeue_style( 'global-styles' );
	}

	/**
	 * Ensure LiteSpeed Cache & CDN never cache personalized or payment-critical routes.
	 */
	public static function protect_dynamic_routes_cache(): void {
		if ( is_admin() || headers_sent() ) {
			return;
		}

		$is_dynamic = false;

		if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
			$is_dynamic = true;
		}

		// Protect WooCommerce payment gateway callbacks (Idram, ArCa, Ameriabank, Telcell, wc-api).
		if ( isset( $_GET['wc-api'] ) || ( isset( $_SERVER['REQUEST_URI'] ) && str_contains( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), '/wc-api/' ) ) ) {
			$is_dynamic = true;
		}

		if ( $is_dynamic ) {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			header( 'X-LiteSpeed-Cache-Control: no-cache' );
		}
	}

	/**
	 * Clean up expired WooCommerce transients safely via daily cron.
	 */
	public static function cleanup_expired_transients(): void {
		if ( function_exists( 'wc_delete_expired_transients' ) ) {
			wc_delete_expired_transients();
		}
	}
}

