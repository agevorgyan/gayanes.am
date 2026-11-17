<?php
/**
 * Plugin Name:       Gayane's Quantity Rules
 * Plugin URI:        https://gayanes.am/
 * Description:       Per-product minimum order quantity and +/- step increment for WooCommerce products, cards, mini-cart, and cart.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Gayane's Kitchen Engineering
 * Text Domain:       gayanes-qty-rules
 * WC requires at least: 8.0
 *
 * @package Gayanes_Qty_Rules
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

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
 * Main Quantity Rules class.
 */
final class Gayanes_Qty_Rules {

	public const META_MIN_QTY  = '_gayanes_min_qty';
	public const META_STEP_QTY = '_gayanes_step_qty';

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Admin product & variation fields.
		add_action( 'woocommerce_product_options_inventory_product_data', array( __CLASS__, 'render_product_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product_fields' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'render_variation_fields' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_variation_fields' ), 10, 2 );

		// Shared rules filter for theme & gayanes-core.
		add_filter( 'gayanes_product_qty_rules', array( __CLASS__, 'filter_product_qty_rules' ), 10, 2 );
		add_filter( 'gayanes_add_to_cart_quantity', array( __CLASS__, 'filter_add_to_cart_quantity' ), 10, 2 );

		// WooCommerce core quantity & validation hooks.
		add_filter( 'woocommerce_quantity_input_args', array( __CLASS__, 'filter_quantity_input_args' ), 20, 2 );
		add_filter( 'woocommerce_loop_add_to_cart_args', array( __CLASS__, 'filter_loop_add_to_cart_args' ), 20, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validate_add_to_cart' ), 20, 4 );
		add_filter( 'woocommerce_update_cart_validation', array( __CLASS__, 'validate_update_cart' ), 20, 4 );
	}

	/**
	 * Resolve effective min & step rules for a product or variation.
	 *
	 * @param int $product_id Product or variation ID.
	 * @return array{min: int, step: int}
	 */
	public static function get_rules( int $product_id ): array {
		if ( $product_id <= 0 ) {
			return array( 'min' => 1, 'step' => 1 );
		}

		$min  = (int) get_post_meta( $product_id, self::META_MIN_QTY, true );
		$step = (int) get_post_meta( $product_id, self::META_STEP_QTY, true );

		// Fallback to parent product for variations.
		if ( ( $min <= 0 || $step <= 0 ) && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_type( 'variation' ) ) {
				$parent_id = $product->get_parent_id();
				if ( $parent_id > 0 ) {
					if ( $min <= 0 ) {
						$min = (int) get_post_meta( $parent_id, self::META_MIN_QTY, true );
					}
					if ( $step <= 0 ) {
						$step = (int) get_post_meta( $parent_id, self::META_STEP_QTY, true );
					}
				}
			}
		}

		// Fallback across Polylang translations if not explicitly set on this language.
		if ( ( $min <= 0 || $step <= 0 ) && function_exists( 'pll_get_post_translations' ) ) {
			$translations = pll_get_post_translations( $product_id );
			if ( is_array( $translations ) ) {
				foreach ( $translations as $tr_id ) {
					$tr_id = (int) $tr_id;
					if ( $tr_id <= 0 || $tr_id === $product_id ) {
						continue;
					}
					if ( $min <= 0 ) {
						$tr_min = (int) get_post_meta( $tr_id, self::META_MIN_QTY, true );
						if ( $tr_min > 0 ) {
							$min = $tr_min;
						}
					}
					if ( $step <= 0 ) {
						$tr_step = (int) get_post_meta( $tr_id, self::META_STEP_QTY, true );
						if ( $tr_step > 0 ) {
							$step = $tr_step;
						}
					}
				}
			}
		}

		return array(
			'min'  => max( 1, $min ),
			'step' => max( 1, $step ),
		);
	}

	/**
	 * Filter callback for `gayanes_product_qty_rules`.
	 *
	 * @param array<string, int> $rules      Default rules.
	 * @param int                  $product_id Product or variation ID.
	 * @return array{min: int, step: int}
	 */
	public static function filter_product_qty_rules( array $rules, int $product_id ): array {
		return self::get_rules( $product_id );
	}

	/**
	 * Ensure initial AJAX add-to-cart quantity respects minimum order quantity.
	 */
	public static function filter_add_to_cart_quantity( int $quantity, int $product_id ): int {
		$rules = self::get_rules( $product_id );
		return max( $quantity, $rules['min'] );
	}

	/**
	 * Render Min & Step fields in WooCommerce Product Data -> Inventory tab.
	 */
	public static function render_product_fields(): void {
		echo '<div class="options_group">';

		woocommerce_wp_text_input(
			array(
				'id'                => self::META_MIN_QTY,
				'label'             => self::t( 'Minimum order quantity' ),
				'placeholder'       => '1',
				'desc_tip'          => true,
				'description'       => self::t( 'Minimum quantity a customer can add/order for this product (e.g. 6).' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '1',
					'step' => '1',
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => self::META_STEP_QTY,
				'label'             => self::t( 'Quantity step (+/-)' ),
				'placeholder'       => '1',
				'desc_tip'          => true,
				'description'       => self::t( 'Amount added or subtracted each time + / - is clicked (e.g. 6).' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '1',
					'step' => '1',
				),
			)
		);

		echo '</div>';
	}

	/**
	 * Save Min & Step fields on simple/variable product save.
	 */
	public static function save_product_fields( int $post_id ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$min = isset( $_POST[ self::META_MIN_QTY ] ) ? absint( $_POST[ self::META_MIN_QTY ] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$step = isset( $_POST[ self::META_STEP_QTY ] ) ? absint( $_POST[ self::META_STEP_QTY ] ) : 0;

		if ( $min > 1 ) {
			update_post_meta( $post_id, self::META_MIN_QTY, $min );
		} else {
			delete_post_meta( $post_id, self::META_MIN_QTY );
		}

		if ( $step > 1 ) {
			update_post_meta( $post_id, self::META_STEP_QTY, $step );
		} else {
			delete_post_meta( $post_id, self::META_STEP_QTY );
		}
	}

	/**
	 * Render Min & Step fields on product variations.
	 *
	 * @param int     $loop           Variation loop index.
	 * @param array   $variation_data Variation data.
	 * @param WP_Post $variation      Variation post.
	 */
	public static function render_variation_fields( int $loop, array $variation_data, \WP_Post $variation ): void {
		$min  = get_post_meta( $variation->ID, self::META_MIN_QTY, true );
		$step = get_post_meta( $variation->ID, self::META_STEP_QTY, true );

		echo '<div class="form-row form-row-first">';
		woocommerce_wp_text_input(
			array(
				'id'                => self::META_MIN_QTY . "_{$loop}",
				'name'              => self::META_MIN_QTY . "[{$loop}]",
				'value'             => $min,
				'label'             => self::t( 'Minimum order quantity' ),
				'placeholder'       => '1',
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '1',
					'step' => '1',
				),
			)
		);
		echo '</div>';

		echo '<div class="form-row form-row-last">';
		woocommerce_wp_text_input(
			array(
				'id'                => self::META_STEP_QTY . "_{$loop}",
				'name'              => self::META_STEP_QTY . "[{$loop}]",
				'value'             => $step,
				'label'             => self::t( 'Quantity step (+/-)' ),
				'placeholder'       => '1',
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '1',
					'step' => '1',
				),
			)
		);
		echo '</div>';
	}

	/**
	 * Save Min & Step fields on product variation save.
	 */
	public static function save_variation_fields( int $variation_id, int $loop ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$min = isset( $_POST[ self::META_MIN_QTY ][ $loop ] ) ? absint( $_POST[ self::META_MIN_QTY ][ $loop ] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$step = isset( $_POST[ self::META_STEP_QTY ][ $loop ] ) ? absint( $_POST[ self::META_STEP_QTY ][ $loop ] ) : 0;

		if ( $min > 1 ) {
			update_post_meta( $variation_id, self::META_MIN_QTY, $min );
		} else {
			delete_post_meta( $variation_id, self::META_MIN_QTY );
		}

		if ( $step > 1 ) {
			update_post_meta( $variation_id, self::META_STEP_QTY, $step );
		} else {
			delete_post_meta( $variation_id, self::META_STEP_QTY );
		}
	}

	/**
	 * Apply min & step to WooCommerce quantity inputs (single product & classic cart).
	 *
	 * @param array<string, mixed> $args    Quantity input args.
	 * @param \WC_Product|null     $product Product object.
	 * @return array<string, mixed>
	 */
	public static function filter_quantity_input_args( array $args, $product ): array {
		if ( ! $product instanceof \WC_Product ) {
			return $args;
		}

		$rules = self::get_rules( $product->get_id() );
		if ( $rules['min'] > 1 ) {
			$args['min_value'] = max( (int) ( $args['min_value'] ?? 1 ), $rules['min'] );
			if ( ! is_cart() && (int) ( $args['input_value'] ?? 1 ) < $rules['min'] ) {
				$args['input_value'] = $rules['min'];
			}
		}
		if ( $rules['step'] > 1 ) {
			$args['step'] = $rules['step'];
		}

		return $args;
	}

	/**
	 * Apply minimum quantity to standard WooCommerce loop add-to-cart buttons.
	 *
	 * @param array<string, mixed> $args    Button args.
	 * @param \WC_Product          $product Product object.
	 * @return array<string, mixed>
	 */
	public static function filter_loop_add_to_cart_args( array $args, \WC_Product $product ): array {
		$rules            = self::get_rules( $product->get_id() );
		$args['quantity'] = max( (int) ( $args['quantity'] ?? 1 ), $rules['min'] );
		return $args;
	}

	/**
	 * Validate add-to-cart quantity against minimum and step rules.
	 *
	 * @param bool $passed       Validation state.
	 * @param int  $product_id   Product ID.
	 * @param int  $quantity     Quantity being added.
	 * @param int  $variation_id Variation ID.
	 */
	public static function validate_add_to_cart( bool $passed, int $product_id, int $quantity, int $variation_id = 0 ): bool {
		if ( ! $passed ) {
			return false;
		}

		$target_id = $variation_id > 0 ? $variation_id : $product_id;
		$rules     = self::get_rules( $target_id );
		if ( $rules['min'] <= 1 && $rules['step'] <= 1 ) {
			return true;
		}

		$existing = self::get_cart_qty( $product_id, $variation_id );
		$total    = $existing + $quantity;

		if ( $total < $rules['min'] ) {
			wc_add_notice(
				sprintf( self::t( 'Minimum order quantity for this product is %d.' ), $rules['min'] ),
				'error'
			);
			return false;
		}

		if ( $rules['step'] > 1 && 0 !== ( $total - $rules['min'] ) % $rules['step'] ) {
			wc_add_notice(
				sprintf( self::t( 'Quantity for this product must be in increments of %d.' ), $rules['step'] ),
				'error'
			);
			return false;
		}

		return true;
	}

	/**
	 * Validate cart quantity update against minimum and step rules.
	 *
	 * @param bool                 $passed        Validation state.
	 * @param string               $cart_item_key Cart item key.
	 * @param array<string, mixed> $values        Cart item values.
	 * @param int                  $quantity      New quantity.
	 */
	public static function validate_update_cart( bool $passed, string $cart_item_key, array $values, int $quantity ): bool {
		if ( ! $passed || $quantity <= 0 ) {
			return $passed;
		}

		$product_id   = (int) ( $values['product_id'] ?? 0 );
		$variation_id = (int) ( $values['variation_id'] ?? 0 );
		$rules        = self::get_rules( $variation_id > 0 ? $variation_id : $product_id );

		if ( $quantity < $rules['min'] ) {
			wc_add_notice(
				sprintf( self::t( 'Minimum order quantity for this product is %d.' ), $rules['min'] ),
				'error'
			);
			return false;
		}

		if ( $rules['step'] > 1 && 0 !== ( $quantity - $rules['min'] ) % $rules['step'] ) {
			wc_add_notice(
				sprintf( self::t( 'Quantity for this product must be in increments of %d.' ), $rules['step'] ),
				'error'
			);
			return false;
		}

		return true;
	}

	/**
	 * Get existing cart quantity for a product/variation.
	 */
	private static function get_cart_qty( int $product_id, int $variation_id = 0 ): int {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return 0;
		}
		$qty = 0;
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( (int) $item['product_id'] === $product_id && ( 0 === $variation_id || (int) $item['variation_id'] === $variation_id ) ) {
				$qty += (int) $item['quantity'];
			}
		}
		return $qty;
	}

	/**
	 * Multilingual helper (Armenian / Russian / English).
	 */
	private static function t( string $text ): string {
		$lang = 'hy';
		if ( function_exists( 'pll_current_language' ) ) {
			$l = pll_current_language( 'slug' );
			if ( is_string( $l ) && '' !== $l ) {
				$lang = $l;
			}
		} elseif ( is_admin() ) {
			$locale = get_user_locale();
			if ( str_starts_with( $locale, 'ru' ) ) {
				$lang = 'ru';
			} elseif ( str_starts_with( $locale, 'en' ) ) {
				$lang = 'en';
			}
		}

		$map = array(
			'Minimum order quantity' => array(
				'hy' => 'Նվազագույն պատվիրման քանակ',
				'ru' => 'Минимальное количество заказа',
			),
			'Minimum quantity a customer can add/order for this product (e.g. 6).' => array(
				'hy' => 'Նվազագույն քանակը, որը հաճախորդը կարող է ավելացնել զամբյուղ (օր.՝ 6)։',
				'ru' => 'Минимальное количество товара для добавления в корзину (напр. 6).',
			),
			'Quantity step (+/-)' => array(
				'hy' => 'Քանակի քայլ (+/-)',
				'ru' => 'Шаг количества (+/-)',
			),
			'Amount added or subtracted each time + / - is clicked (e.g. 6).' => array(
				'hy' => 'Քանակը, որով ավելանում կամ պակասում է + կամ - սեղմելիս (օր.՝ 6)։',
				'ru' => 'Количество, на которое увеличивается или уменьшается товар при нажатии + / - (напр. 6).',
			),
			'Minimum order quantity for this product is %d.' => array(
				'hy' => 'Այս ապրանքի նվազագույն պատվիրման քանակը %d է։',
				'ru' => 'Минимальное количество заказа для этого товара: %d.',
			),
			'Quantity for this product must be in increments of %d.' => array(
				'hy' => 'Այս ապրանքի քանակը պետք է փոփոխվի %d քայլով։',
				'ru' => 'Количество этого товара должно изменяться с шагом %d.',
			),
			'Min. order: %d' => array(
				'hy' => 'Նվազագույնը՝ %d հատ',
				'ru' => 'Мин. заказ: %d шт.',
			),
			'Step: +%d' => array(
				'hy' => 'Քայլը՝ +%d հատ',
				'ru' => 'Шаг: +%d шт.',
			),
		);

		if ( 'en' === $lang || ! isset( $map[ $text ][ $lang ] ) ) {
			return $text;
		}
		return $map[ $text ][ $lang ];
	}
}

add_action( 'plugins_loaded', array( 'Gayanes_Qty_Rules', 'init' ), 22 );
