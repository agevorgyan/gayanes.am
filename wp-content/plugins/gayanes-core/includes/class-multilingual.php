<?php
/**
 * Multilingual integration for Armenian (hy), Russian (ru), and English (en).
 *
 * Supports Polylang (primary, detected on live site) and WPML as fallback.
 *
 * @package Gayanes_Core
 */

namespace Gayanes_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Multilingual class.
 */
class Multilingual {

	/**
	 * Supported language codes in display order.
	 */
	public const LANGUAGES = array( 'hy', 'ru', 'en' );

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_strings' ) );
		add_filter( 'language_attributes', array( __CLASS__, 'filter_language_attributes' ) );
	}

	/**
	 * Get current active language code ('hy', 'ru', or 'en').
	 */
	public static function current_lang(): string {
		if ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language( 'slug' );
			if ( is_string( $lang ) && '' !== $lang ) {
				return $lang;
			}
		}

		if ( defined( 'ICL_LANGUAGE_CODE' ) && is_string( ICL_LANGUAGE_CODE ) ) {
			return ICL_LANGUAGE_CODE;
		}

		$locale = determine_locale();
		if ( str_starts_with( $locale, 'ru' ) ) {
			return 'ru';
		}
		if ( str_starts_with( $locale, 'en' ) ) {
			return 'en';
		}

		return 'hy';
	}

	/**
	 * Translate a UI string via Polylang / WPML / built-in HY-RU fallback.
	 *
	 * @param string $string Original English or source string.
	 * @param string $domain Text domain.
	 */
	public static function t( string $string, string $domain = 'gayanes-theme' ): string {
		if ( function_exists( 'pll__' ) ) {
			$translated = (string) pll__( $string );
			if ( $translated !== $string ) {
				return $translated;
			}
		}
		if ( has_filter( 'wpml_translate_single_string' ) ) {
			$translated = (string) apply_filters( 'wpml_translate_single_string', $string, $domain, $string );
			if ( $translated !== $string ) {
				return $translated;
			}
		}

		$lang = self::current_lang();
		$map  = self::builtin_translations();
		if ( isset( $map[ $lang ][ $string ] ) ) {
			return $map[ $lang ][ $string ];
		}

		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText,WordPress.WP.I18n.NonSingularStringLiteralDomain
		return __( $string, $domain );
	}

	/**
	 * Built-in UI string translations for HY (Armenian) and RU (Russian)
	 * when Polylang string table has not yet been populated.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function builtin_translations(): array {
		return array(
			'hy' => array(
				'Home'                                                   => 'Գլխավոր',
				'Shop All'                                               => 'Բոլոր ապրանքները',
				'Categories'                                             => 'Բաժիններ',
				'Add to Cart'                                            => 'Ավելացնել զամբյուղ',
				'Added to cart'                                          => 'Ավելացվել է զամբյուղում',
				'Removed from cart'                                      => 'Հեռացվել է զամբյուղից',
				'Out of Stock'                                           => 'Առկա չէ',
				'In Stock'                                               => 'Առկա է',
				'Select Options'                                         => 'Ընտրել տարբերակ',
				'Sale'                                                   => 'Զեղչ',
				'Your Cart'                                              => 'Զամբյուղ',
				'View Cart'                                              => 'Դիտել զամբյուղը',
				'Checkout'                                               => 'Ձևակերպել պատվերը',
				'Your cart is empty'                                     => 'Ձեր զամբյուղը դատարկ է',
				'Subtotal'                                               => 'Ենթագումար',
				'Total'                                                  => 'Ընդամենը',
				'Remove'                                                 => 'Հեռացնել',
				'Decrease quantity'                                      => 'Նվազեցնել քանակը',
				'Increase quantity'                                      => 'Ավելացնել քանակը',
				'Search products...'                                     => 'Որոնել ապրանքներ...',
				'Search'                                                 => 'Որոնել',
				'Best Sellers'                                           => 'Ամենավաճառվածները',
				'Featured Products'                                      => 'Առաջարկվող ապրանքներ',
				'New Arrivals'                                           => 'Նորույթներ',
				'About Us'                                               => 'Մեր մասին',
				'Contact'                                                => 'Կապ',
				'Delivery & Payment'                                     => 'Առաքում և վճարում',
				'FAQ'                                                    => 'Հաճախ տրվող հարցեր',
				'Blog & Recipes'                                         => 'Բլոգ և բաղադրատոմսեր',
				'Privacy Policy'                                         => 'Գաղտնիության քաղաքականություն',
				'Terms & Conditions'                                     => 'Պայմաններ և դրույթներ',
				'Account'                                                => 'Հաշիվ',
				'My Account'                                             => 'Իմ հաշիվը',
				'View All'                                               => 'Տեսնել բոլորը',
				'Learn More'                                             => 'Իմանալ ավելին',
				'Customer Reviews'                                       => 'Հաճախորդների կարծիքները',
				'Page Not Found'                                         => 'Էջը չի գտնվել',
				'Go Home'                                                => 'Վերադառնալ գլխավոր էջ',
				'Free delivery in Yerevan on orders over 15,000 AMD'     => 'Անվճար առաքում Երևանում 15,000 դրամից բարձր պատվերների դեպքում',
			),
			'ru' => array(
				'Home'                                                   => 'Главная',
				'Shop All'                                               => 'Все товары',
				'Categories'                                             => 'Категории',
				'Add to Cart'                                            => 'В корзину',
				'Added to cart'                                          => 'Добавлено в корзину',
				'Removed from cart'                                      => 'Удалено из корзины',
				'Out of Stock'                                           => 'Нет в наличии',
				'In Stock'                                               => 'В наличии',
				'Select Options'                                         => 'Выбрать вариант',
				'Sale'                                                   => 'Скидка',
				'Your Cart'                                              => 'Корзина',
				'View Cart'                                              => 'Открыть корзину',
				'Checkout'                                               => 'Оформить заказ',
				'Your cart is empty'                                     => 'Ваша корзина пуста',
				'Subtotal'                                               => 'Подытог',
				'Total'                                                  => 'Итого',
				'Remove'                                                 => 'Удалить',
				'Decrease quantity'                                      => 'Уменьшить количество',
				'Increase quantity'                                      => 'Увеличить количество',
				'Search products...'                                     => 'Поиск товаров...',
				'Search'                                                 => 'Поиск',
				'Best Sellers'                                           => 'Хиты продаж',
				'Featured Products'                                      => 'Рекомендуемые товары',
				'New Arrivals'                                           => 'Новинки',
				'About Us'                                               => 'О нас',
				'Contact'                                                => 'Контакты',
				'Delivery & Payment'                                     => 'Доставка и оплата',
				'FAQ'                                                    => 'Частые вопросы',
				'Blog & Recipes'                                         => 'Блог и рецепты',
				'Privacy Policy'                                         => 'Политика конфиденциальности',
				'Terms & Conditions'                                     => 'Условия использования',
				'Account'                                                => 'Аккаунт',
				'My Account'                                             => 'Мой аккаунт',
				'View All'                                               => 'Смотреть все',
				'Learn More'                                             => 'Подробнее',
				'Customer Reviews'                                       => 'Отзывы покупателей',
				'Page Not Found'                                         => 'Страница не найдена',
				'Go Home'                                                => 'На главную',
				'Free delivery in Yerevan on orders over 15,000 AMD'     => 'Бесплатная доставка по Еревану при заказе от 15 000 AMD',
			),
		);
	}

	/**
	 * Register theme UI strings with Polylang / WPML.
	 */
	public static function register_strings(): void {
		$strings = array(
			'Add to Cart',
			'Added to cart',
			'Out of Stock',
			'In Stock',
			'Your Cart',
			'View Cart',
			'Checkout',
			'Your cart is empty',
			'Continue shopping',
			'Subtotal',
			'Total',
			'Remove',
			'Decrease quantity',
			'Increase quantity',
			'Search products...',
			'Search',
			'Categories',
			'Shop All',
			'Best Sellers',
			'Featured Products',
			'New Arrivals',
			'About Us',
			'Contact',
			'Delivery & Payment',
			'FAQ',
			'Blog & Recipes',
			'Privacy Policy',
			'Terms & Conditions',
			'Free delivery in Yerevan on orders over 15,000 AMD',
			'Homemade Armenian Semi-Finished Food',
			'Fresh ingredients, traditional recipes, delivered to your door.',
		);

		if ( function_exists( 'pll_register_string' ) ) {
			foreach ( $strings as $str ) {
				pll_register_string( sanitize_key( $str ), $str, 'Gayanes Theme' );
			}
		} elseif ( has_action( 'wpml_register_single_string' ) ) {
			foreach ( $strings as $str ) {
				do_action( 'wpml_register_single_string', 'gayanes-theme', $str, $str );
			}
		}
	}

	/**
	 * Build language switcher items preserving current product/category/page translation.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_switcher_items(): array {
		$labels = array(
			'hy' => 'ՀԱՅ',
			'ru' => 'РУС',
			'en' => 'ENG',
		);

		$names = array(
			'hy' => 'Հայերեն',
			'ru' => 'Русский',
			'en' => 'English',
		);

		// 1. Polylang native switcher.
		if ( function_exists( 'pll_the_languages' ) ) {
			$raw = pll_the_languages( array( 'raw' => 1 ) );
			if ( is_array( $raw ) && ! empty( $raw ) ) {
				$items = array();
				foreach ( $raw as $slug => $lang ) {
					$items[] = array(
						'code'    => (string) $slug,
						'label'   => $labels[ $slug ] ?? strtoupper( (string) $slug ),
						'name'    => $names[ $slug ] ?? (string) ( $lang['name'] ?? $slug ),
						'url'     => (string) ( $lang['url'] ?? home_url( '/' ) ),
						'current' => ! empty( $lang['current_lang'] ),
					);
				}
				return $items;
			}
		}

		// 2. WPML native switcher.
		if ( has_filter( 'wpml_active_languages' ) ) {
			$raw = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
			if ( is_array( $raw ) && ! empty( $raw ) ) {
				$items = array();
				foreach ( $raw as $code => $lang ) {
					$items[] = array(
						'code'    => (string) $code,
						'label'   => $labels[ $code ] ?? strtoupper( (string) $code ),
						'name'    => $names[ $code ] ?? (string) ( $lang['native_name'] ?? $code ),
						'url'     => (string) ( $lang['url'] ?? home_url( '/' ) ),
						'current' => ! empty( $lang['active'] ),
					);
				}
				return $items;
			}
		}

		// 3. Fallback static switcher if multilingual plugin is not active in local preview.
		$current = self::current_lang();
		$items   = array();
		foreach ( self::LANGUAGES as $code ) {
			$path    = 'hy' === $code ? '/' : "/{$code}/";
			$items[] = array(
				'code'    => $code,
				'label'   => $labels[ $code ],
				'name'    => $names[ $code ],
				'url'     => home_url( $path ),
				'current' => ( $code === $current ),
			);
		}
		return $items;
	}

	/**
	 * Get translated post/product/term URL when available.
	 *
	 * @param int    $object_id Post or Term ID.
	 * @param string $lang      Target language code.
	 * @param string $type      'post' or 'term'.
	 */
	public static function get_translated_url( int $object_id, string $lang, string $type = 'post' ): string {
		if ( 'term' === $type ) {
			if ( function_exists( 'pll_get_term' ) ) {
				$tr_id = pll_get_term( $object_id, $lang );
				if ( $tr_id ) {
					$link = get_term_link( (int) $tr_id );
					if ( ! is_wp_error( $link ) ) {
						return $link;
					}
				}
			}
			$link = get_term_link( $object_id );
			return is_wp_error( $link ) ? home_url( '/' ) : $link;
		}

		if ( function_exists( 'pll_get_post' ) ) {
			$tr_id = pll_get_post( $object_id, $lang );
			if ( $tr_id ) {
				return (string) get_permalink( (int) $tr_id );
			}
		} elseif ( has_filter( 'wpml_object_id' ) ) {
			$tr_id = apply_filters( 'wpml_object_id', $object_id, get_post_type( $object_id ), true, $lang );
			if ( $tr_id ) {
				return (string) get_permalink( (int) $tr_id );
			}
		}

		return (string) get_permalink( $object_id );
	}

	/**
	 * Ensure clean ISO language attribute on <html>.
	 *
	 * @param string $output Existing language attributes.
	 */
	public static function filter_language_attributes( string $output ): string {
		$lang = self::current_lang();
		$map  = array(
			'hy' => 'hy',
			'ru' => 'ru-RU',
			'en' => 'en-US',
		);
		if ( isset( $map[ $lang ] ) && ! str_contains( $output, 'lang=' ) ) {
			$output .= ' lang="' . esc_attr( $map[ $lang ] ) . '"';
		}
		return $output;
	}
}

