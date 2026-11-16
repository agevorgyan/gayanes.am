<?php
/**
 * Product Catalog (get_products) & Stop-List (get_stops) Synchronization.
 *
 * - get_products: rate-limited to 1 request/hour. Syncs SKU, price, optional name, and `sale` (discountable) flag.
 * - get_stops: marks stopped SKUs as `outofstock` and restores unstopped SKUs back to `instock`.
 * - Adds Frontpad product metadata panel in WooCommerce Product Edit screen.
 *
 * @package Gayanes_Frontpad
 */

declare( strict_types=1 );

namespace Gayanes_Frontpad;

defined( 'ABSPATH' ) || exit;

/**
 * Sync class.
 */
class Sync {

	public const CRON_PRODUCTS_HOOK  = 'gayanes_frontpad_cron_products';
	public const CRON_STOPS_HOOK     = 'gayanes_frontpad_cron_stops';
	public const OPTION_SYNC_STATUS  = 'gayanes_frontpad_sync_status';
	public const META_SALE_ALLOWED   = '_frontpad_sale_allowed';
	public const META_STOPPED        = '_frontpad_stopped';
	public const META_IS_MODIFIER    = '_frontpad_is_modifier';

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( self::CRON_PRODUCTS_HOOK, array( __CLASS__, 'cron_sync_products' ) );
		add_action( self::CRON_STOPS_HOOK, array( __CLASS__, 'cron_sync_stops' ) );

		// WooCommerce Product Data Panel (Frontpad tab).
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'add_product_data_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'render_product_data_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product_data_panel' ) );

		// Cart discount eligibility helper based on Frontpad `sale` flag.
		add_filter( 'woocommerce_coupon_is_valid_for_product', array( __CLASS__, 'respect_sale_flag_on_coupon' ), 10, 4 );
	}

	/**
	 * Reschedule WP-Cron jobs based on current settings.
	 */
	public static function reschedule_crons(): void {
		$settings = API_Client::get_settings();

		wp_clear_scheduled_hook( self::CRON_PRODUCTS_HOOK );
		if ( '1' === (string) $settings['enabled'] && '1' === (string) $settings['auto_sync_products'] ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', self::CRON_PRODUCTS_HOOK );
		}

		wp_clear_scheduled_hook( self::CRON_STOPS_HOOK );
		if ( '1' === (string) $settings['enabled'] && '1' === (string) $settings['auto_sync_stops'] ) {
			$interval = in_array( $settings['stops_interval'], array( 'frontpad_5min', 'frontpad_15min', 'hourly' ), true )
				? (string) $settings['stops_interval']
				: 'frontpad_15min';
			wp_schedule_event( time() + 60, $interval, self::CRON_STOPS_HOOK );
		}
	}

	/**
	 * Cron callback for product sync.
	 */
	public static function cron_sync_products(): void {
		if ( API_Client::is_configured() ) {
			self::sync_products( false );
		}
	}

	/**
	 * Cron callback for stop-list sync.
	 */
	public static function cron_sync_stops(): void {
		if ( API_Client::is_configured() ) {
			self::sync_stops();
		}
	}

	/**
	 * Synchronize WooCommerce products with Frontpad `get_products`.
	 *
	 * @param bool $force Bypass local 1-hour lock if explicitly requested by admin.
	 * @return array<string, mixed>|\WP_Error Summary of sync results.
	 */
	public static function sync_products( bool $force = false ) {
		$response = API_Client::request( 'get_products', array(), $force );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( 'success' !== ( $response['result'] ?? '' ) ) {
			$err = (string) ( $response['error'] ?? 'unknown' );
			return new \WP_Error( $err, API_Client::format_error( $err ) );
		}

		$items   = self::normalize_product_arrays( $response );
		$summary = self::apply_product_updates( $items, API_Client::get_settings() );

		self::save_sync_status( 'products', $summary );
		return $summary;
	}

	/**
	 * Normalize Frontpad's parallel-object response (`product_id`, `name`, `price`, `sale`)
	 * into a clean list of item arrays.
	 *
	 * @param array<string, mixed> $response API JSON response.
	 * @return array<int, array<string, string>>
	 */
	public static function normalize_product_arrays( array $response ): array {
		$ids    = is_array( $response['product_id'] ?? null ) ? $response['product_id'] : array();
		$names  = is_array( $response['name'] ?? null ) ? $response['name'] : array();
		$prices = is_array( $response['price'] ?? null ) ? $response['price'] : array();
		$sales  = is_array( $response['sale'] ?? null ) ? $response['sale'] : array();

		$items = array();
		foreach ( $ids as $idx => $sku ) {
			$sku_str = trim( (string) $sku );
			if ( '' === $sku_str ) {
				continue;
			}
			$items[] = array(
				'sku'   => $sku_str,
				'name'  => trim( (string) ( $names[ $idx ] ?? '' ) ),
				'price' => trim( (string) ( $prices[ $idx ] ?? '' ) ),
				'sale'  => isset( $sales[ $idx ] ) ? (string) $sales[ $idx ] : '1',
			);
		}
		return $items;
	}

	/**
	 * Apply normalized Frontpad product items to WooCommerce products.
	 *
	 * @param array<int, array<string, string>> $items Normalized items.
	 * @param array<string, mixed>              $settings Plugin settings.
	 * @return array<string, int|string>
	 */
	public static function apply_product_updates( array $items, array $settings ): array {
		$update_names  = '1' === (string) ( $settings['sync_product_names'] ?? '0' );
		$update_prices = '1' === (string) ( $settings['sync_product_prices'] ?? '1' );
		$create_new    = '1' === (string) ( $settings['create_missing_products'] ?? '0' );

		$updated = 0;
		$created = 0;
		$skipped = 0;

		foreach ( $items as $item ) {
			$sku        = $item['sku'];
			$product_id = wc_get_product_id_by_sku( $sku );

			if ( ! $product_id && $create_new ) {
				$product = new \WC_Product_Simple();
				$product->set_sku( $sku );
				$product->set_name( '' !== $item['name'] ? $item['name'] : 'Frontpad #' . $sku );
				if ( '' !== $item['price'] ) {
					$product->set_regular_price( $item['price'] );
				}
				$product->set_status( 'draft' );
				$product->update_meta_data( self::META_SALE_ALLOWED, $item['sale'] );
				$product->save();
				++$created;
				continue;
			}

			if ( ! $product_id ) {
				++$skipped;
				continue;
			}

			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				++$skipped;
				continue;
			}

			if ( $update_names && '' !== $item['name'] ) {
				$product->set_name( $item['name'] );
			}
			if ( $update_prices && '' !== $item['price'] && is_numeric( $item['price'] ) ) {
				$product->set_regular_price( $item['price'] );
				if ( '' === $product->get_sale_price() ) {
					$product->set_price( $item['price'] );
				}
			}
			$product->update_meta_data( self::META_SALE_ALLOWED, $item['sale'] );
			$product->save();
			++$updated;
		}

		return array(
			'total'   => count( $items ),
			'updated' => $updated,
			'created' => $created,
			'skipped' => $skipped,
			'time'    => gmdate( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Synchronize WooCommerce stock status with Frontpad `get_stops`.
	 *
	 * @return array<string, mixed>|\WP_Error
	 */
	public static function sync_stops() {
		$response = API_Client::request( 'get_stops' );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( 'success' !== ( $response['result'] ?? '' ) ) {
			$err = (string) ( $response['error'] ?? 'unknown' );
			return new \WP_Error( $err, API_Client::format_error( $err ) );
		}

		// Doc: when no products are in stop-list, API returns result: success and error/description: no_stops.
		$stopped_skus = array();
		if ( 'no_stops' !== ( $response['error'] ?? '' ) && 'no_stops' !== ( $response['description'] ?? '' ) ) {
			$items        = self::normalize_product_arrays( $response );
			$stopped_skus = array_column( $items, 'sku' );
		}

		$stopped_ids = array();
		$marked_out  = 0;
		$restored    = 0;

		foreach ( $stopped_skus as $sku ) {
			$pid = wc_get_product_id_by_sku( $sku );
			if ( ! $pid ) {
				continue;
			}
			$stopped_ids[] = $pid;
			$product       = wc_get_product( $pid );
			if ( $product ) {
				$product->set_stock_status( 'outofstock' );
				$product->update_meta_data( self::META_STOPPED, 'yes' );
				$product->save();
				++$marked_out;
			}
		}

		// Restore products that were previously stopped by Frontpad but are no longer in the stop-list.
		$previously_stopped = wc_get_products(
			array(
				'limit'      => -1,
				'return'     => 'ids',
				'meta_key'   => self::META_STOPPED,
				'meta_value' => 'yes',
			)
		);

		foreach ( $previously_stopped as $pid ) {
			if ( in_array( (int) $pid, $stopped_ids, true ) ) {
				continue;
			}
			$product = wc_get_product( $pid );
			if ( $product ) {
				$product->set_stock_status( 'instock' );
				$product->update_meta_data( self::META_STOPPED, 'no' );
				$product->save();
				++$restored;
			}
		}

		$summary = array(
			'stopped_total' => count( $stopped_skus ),
			'marked_out'    => $marked_out,
			'restored'      => $restored,
			'time'          => gmdate( 'Y-m-d H:i:s' ),
		);

		self::save_sync_status( 'stops', $summary );
		return $summary;
	}

	/**
	 * Respect Frontpad `sale` flag (`0` = no discounts) on WooCommerce coupons.
	 *
	 * @param bool        $valid Current validity.
	 * @param \WC_Product $product Product object.
	 * @param \WC_Coupon  $coupon Coupon object.
	 * @param array       $values Cart item values.
	 */
	public static function respect_sale_flag_on_coupon( bool $valid, $product, $coupon, $values ): bool {
		if ( ! $valid || ! $product instanceof \WC_Product ) {
			return $valid;
		}
		$sale_allowed = $product->get_meta( self::META_SALE_ALLOWED );
		if ( '0' === (string) $sale_allowed ) {
			return false;
		}
		return $valid;
	}

	/**
	 * Add Frontpad tab in WooCommerce Product Data metabox.
	 *
	 * @param array<string, array<string, mixed>> $tabs Product data tabs.
	 * @return array<string, array<string, mixed>>
	 */
	public static function add_product_data_tab( array $tabs ): array {
		$tabs['frontpad'] = array(
			'label'    => __( 'Frontpad', 'gayanes-frontpad' ),
			'target'   => 'frontpad_product_data',
			'class'    => array(),
			'priority' => 85,
		);
		return $tabs;
	}

	/**
	 * Render Frontpad fields inside Product Data metabox.
	 */
	public static function render_product_data_panel(): void {
		global $post;
		$product = wc_get_product( $post->ID );
		if ( ! $product ) {
			return;
		}

		echo '<div id="frontpad_product_data" class="panel woocommerce_options_panel">';
		echo '<div class="options_group">';

		woocommerce_wp_checkbox(
			array(
				'id'          => self::META_IS_MODIFIER,
				'value'       => 'yes' === $product->get_meta( self::META_IS_MODIFIER ) ? 'yes' : 'no',
				'label'       => __( 'Frontpad Modifier', 'gayanes-frontpad' ),
				'description' => __( 'Mark this product as a Frontpad modifier (product_mod) when attached to a parent item.', 'gayanes-frontpad' ),
			)
		);

		$sale_allowed = $product->get_meta( self::META_SALE_ALLOWED );
		$stopped      = $product->get_meta( self::META_STOPPED );

		echo '<p class="form-field"><label>' . esc_html__( 'Frontpad SKU', 'gayanes-frontpad' ) . '</label>';
		echo '<span><code>' . esc_html( $product->get_sku() ?: __( '(Not set — set SKU in Inventory tab)', 'gayanes-frontpad' ) ) . '</code></span></p>';

		echo '<p class="form-field"><label>' . esc_html__( 'Discounts Allowed (sale)', 'gayanes-frontpad' ) . '</label>';
		echo '<span>' . ( '0' === (string) $sale_allowed ? esc_html__( 'No (0)', 'gayanes-frontpad' ) : esc_html__( 'Yes (1)', 'gayanes-frontpad' ) ) . '</span></p>';

		echo '<p class="form-field"><label>' . esc_html__( 'Stop-List Status', 'gayanes-frontpad' ) . '</label>';
		echo '<span>' . ( 'yes' === (string) $stopped ? '<strong style="color:#d63638;">' . esc_html__( 'In Stop-List', 'gayanes-frontpad' ) . '</strong>' : esc_html__( 'Available', 'gayanes-frontpad' ) ) . '</span></p>';

		echo '</div></div>';
	}

	/**
	 * Save Frontpad product metadata.
	 */
	public static function save_product_data_panel( int $post_id ): void {
		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Handled by WooCommerce process_product_meta.
		$is_mod = isset( $_POST[ self::META_IS_MODIFIER ] ) ? 'yes' : 'no';
		$product->update_meta_data( self::META_IS_MODIFIER, $is_mod );
		$product->save();
	}

	/**
	 * Save last sync summary for Admin dashboard display.
	 *
	 * @param string               $type 'products' or 'stops'.
	 * @param array<string, mixed> $summary Summary data.
	 */
	private static function save_sync_status( string $type, array $summary ): void {
		$all          = get_option( self::OPTION_SYNC_STATUS, array() );
		$all          = is_array( $all ) ? $all : array();
		$all[ $type ] = $summary;
		update_option( self::OPTION_SYNC_STATUS, $all, false );
	}
}
