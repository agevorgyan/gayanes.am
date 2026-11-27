<?php
/**
 * WooCommerce Email System: Trilingual (HY / RU / EN) Locale Switching,
 * Subject/Heading Translations, Product Thumbnails, and Option 3 UX Helpers.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Holds the active language code ('hy', 'ru', 'en') while rendering an email.
 */
$GLOBALS['gv2_active_email_lang']     = null;
$GLOBALS['gv2_email_previous_locale'] = null;

/**
 * Map a language slug ('hy', 'ru', 'en') to a WordPress locale.
 */
function gv2_lang_to_locale( string $lang ): string {
	return match ( $lang ) {
		'ru'    => 'ru_RU',
		'en'    => 'en_US',
		default => 'hy',
	};
}

/**
 * Map a WordPress locale to a language slug ('hy', 'ru', 'en').
 */
function gv2_locale_to_lang( string $locale ): string {
	if ( str_starts_with( $locale, 'ru' ) ) {
		return 'ru';
	}
	if ( str_starts_with( $locale, 'en' ) ) {
		return 'en';
	}
	return 'hy';
}

/**
 * Persist the customer's active site language on the WooCommerce order at checkout.
 */
add_action(
	'woocommerce_checkout_create_order',
	static function ( WC_Order $order ): void {
		$lang = gv2_current_lang();
		$order->update_meta_data( '_gv2_order_lang', $lang );
	},
	10,
	1
);

add_action(
	'woocommerce_store_api_checkout_update_order_meta',
	static function ( WC_Order $order ): void {
		if ( ! $order->get_meta( '_gv2_order_lang' ) ) {
			$order->update_meta_data( '_gv2_order_lang', gv2_current_lang() );
		}
	},
	10,
	1
);

/**
 * Resolve the language code ('hy', 'ru', 'en') for a given WC_Order or email context.
 *
 * @param WC_Order|WC_Email|mixed $context Order, email object, or null.
 */
function gv2_get_email_lang( $context = null ): string {
	if ( ! empty( $GLOBALS['gv2_active_email_lang'] ) && in_array( $GLOBALS['gv2_active_email_lang'], array( 'hy', 'ru', 'en' ), true ) ) {
		return (string) $GLOBALS['gv2_active_email_lang'];
	}

	$order = null;
	if ( $context instanceof WC_Order ) {
		$order = $context;
	} elseif ( is_object( $context ) && isset( $context->object ) && $context->object instanceof WC_Order ) {
		$order = $context->object;
	}

	if ( $order instanceof WC_Order ) {
		$meta_lang = $order->get_meta( '_gv2_order_lang' );
		if ( is_string( $meta_lang ) && in_array( $meta_lang, array( 'hy', 'ru', 'en' ), true ) ) {
			return $meta_lang;
		}

		if ( function_exists( 'pll_get_post_language' ) ) {
			$pll_lang = pll_get_post_language( $order->get_id(), 'slug' );
			if ( is_string( $pll_lang ) && in_array( $pll_lang, array( 'hy', 'ru', 'en' ), true ) ) {
				return $pll_lang;
			}
		}
	}

	return gv2_current_lang();
}

/**
 * Reload WooCommerce textdomain for the target email locale.
 */
function gv2_reload_wc_textdomain_for_locale( string $locale ): void {
	if ( str_starts_with( $locale, 'en' ) ) {
		unload_textdomain( 'woocommerce' );
		return;
	}

	$bundled = GV2_DIR . "/languages/woocommerce-{$locale}.mo";
	$target  = file_exists( $bundled ) ? $bundled : WP_LANG_DIR . "/plugins/woocommerce-{$locale}.mo";

	if ( file_exists( $target ) ) {
		unload_textdomain( 'woocommerce' );
		load_textdomain( 'woocommerce', $target, $locale );
	}
}

/**
 * Switch locale before WooCommerce builds an email so subjects, headings, and strings
 * match the language the customer used when placing the order.
 */
add_action(
	'woocommerce_email_setup_locale',
	static function ( $email ): void {
		if ( ! is_object( $email ) ) {
			return;
		}

		// Admin emails stay in site default / Armenian; customer emails use the order's language.
		if ( method_exists( $email, 'is_customer_email' ) && ! $email->is_customer_email() ) {
			$lang = 'hy';
		} else {
			$lang = gv2_get_email_lang( $email );
		}

		$GLOBALS['gv2_active_email_lang']     = $lang;
		$GLOBALS['gv2_email_previous_locale'] = determine_locale();

		$target_locale = gv2_lang_to_locale( $lang );
		if ( $target_locale !== $GLOBALS['gv2_email_previous_locale'] ) {
			switch_to_locale( $target_locale );
			gv2_reload_wc_textdomain_for_locale( $target_locale );
		}
	},
	10,
	1
);

/**
 * Restore the previous locale after WooCommerce finishes rendering an email.
 */
add_action(
	'woocommerce_email_restore_locale',
	static function (): void {
		$prev = $GLOBALS['gv2_email_previous_locale'] ?? null;

		$GLOBALS['gv2_active_email_lang']     = null;
		$GLOBALS['gv2_email_previous_locale'] = null;

		if ( is_string( $prev ) && '' !== $prev ) {
			restore_previous_locale();
			gv2_reload_wc_textdomain_for_locale( $prev );
		}
	},
	10
);

/**
 * Trilingual dictionary for Gayane's Kitchen WooCommerce emails.
 *
 * @return array<string, array{hy: string, ru: string, en: string}>
 */
function gv2_email_translations_map(): array {
	return array(
		// Hero & Status.
		'order_confirmed_heading'   => array(
			'hy' => 'Պատվերը հաստատված է',
			'ru' => 'Заказ подтверждён',
			'en' => 'Order Confirmed',
		),
		'order_completed_heading'   => array(
			'hy' => 'Պատվերը առաքված է',
			'ru' => 'Заказ доставлен',
			'en' => 'Order Completed',
		),
		'order_on_hold_heading'     => array(
			'hy' => 'Պատվերը սպասման մեջ է',
			'ru' => 'Заказ на удержании',
			'en' => 'Order On Hold',
		),
		'order_cancelled_heading'   => array(
			'hy' => 'Պատվերը չեղարկվել է',
			'ru' => 'Заказ отменён',
			'en' => 'Order Cancelled',
		),
		'order_refunded_heading'    => array(
			'hy' => 'Գումարը վերադարձվել է',
			'ru' => 'Возврат средств выполнен',
			'en' => 'Order Refunded',
		),
		'order_failed_heading'      => array(
			'hy' => 'Վճարումը չի հաջողվել',
			'ru' => 'Ошибка оплаты заказа',
			'en' => 'Payment Failed',
		),
		'invoice_heading'           => array(
			'hy' => 'Պատվերի հաշիվ',
			'ru' => 'Счёт на оплату заказа',
			'en' => 'Order Invoice',
		),
		'customer_note_heading'     => array(
			'hy' => 'Նոր հաղորդագրություն պատվերի վերաբերյալ',
			'ru' => 'Новое сообщение по заказу',
			'en' => 'Note Added to Your Order',
		),
		'new_account_heading'       => array(
			'hy' => 'Բարի գալուստ Gayane’s Kitchen',
			'ru' => 'Добро пожаловать в Gayane’s Kitchen',
			'en' => 'Welcome to Gayane’s Kitchen',
		),
		'reset_password_heading'    => array(
			'hy' => 'Գաղտնաբառի վերականգնում',
			'ru' => 'Восстановление пароля',
			'en' => 'Password Reset',
		),

		// Greeting & Confirmation Copy.
		'greeting_named'            => array(
			'hy' => 'Ողջույն, %s 👋',
			'ru' => 'Здравствуйте, %s 👋',
			'en' => 'Hello, %s 👋',
		),
		'greeting_generic'          => array(
			'hy' => 'Ողջույն 👋',
			'ru' => 'Здравствуйте 👋',
			'en' => 'Hello 👋',
		),
		'processing_body_1'         => array(
			'hy' => 'Ձեր պատվերը հաջողությամբ հաստատվել է։ Մենք արդեն պատրաստվում ենք, որպեսզի ձեր սիրելի ուտեստները թարմ և ժամանակին հասնեն ձեզ։',
			'ru' => 'Ваш заказ успешно подтверждён. Мы уже готовим ваши любимые блюда, чтобы доставить их свежими и вовремя.',
			'en' => 'Your order is confirmed. We are already preparing your favorite dishes so they reach you fresh and on time.',
		),
		'processing_body_2'         => array(
			'hy' => 'Շնորհակալություն, որ ընտրել եք Gayane’s Kitchen-ը։',
			'ru' => 'Спасибо, что выбрали Gayane’s Kitchen.',
			'en' => 'Thank you for choosing Gayane’s Kitchen.',
		),

		// Personalized Hero Subtitle.
		'hero_subtitle_named'       => array(
			'hy' => 'Շնորհակալություն, %s։ Ձեր պատվերը սիրով պատրաստվում է։',
			'ru' => 'Спасибо, %s! Ваш заказ готовится с любовью.',
			'en' => 'Thank you, %s. Your order is being lovingly prepared.',
		),
		'hero_subtitle_generic'     => array(
			'hy' => 'Շնորհակալություն Gayane’s Kitchen-ը ընտրելու համար։',
			'ru' => 'Спасибо, что выбрали Gayane’s Kitchen!',
			'en' => 'Thank you for choosing Gayane’s Kitchen.',
		),

		// Sand Summary Strip.
		'order_number_label'        => array(
			'hy' => 'Պատվեր #%s',
			'ru' => 'Заказ #%s',
			'en' => 'Order #%s',
		),
		'order_date_label'          => array(
			'hy' => 'Ամսաթիվ՝ %s',
			'ru' => 'Дата: %s',
			'en' => 'Date: %s',
		),

		// Order Items & Totals.
		'order_summary_title'       => array(
			'hy' => 'Պատվերի մանրամասները',
			'ru' => 'Детали заказа',
			'en' => 'Order Summary',
		),
		'qty_label'                 => array(
			'hy' => 'Քանակ՝ %s',
			'ru' => 'Количество: %s',
			'en' => 'Qty: %s',
		),
		'subtotal_label'            => array(
			'hy' => 'Ենթագումար',
			'ru' => 'Подытог',
			'en' => 'Subtotal',
		),
		'order_total_label'         => array(
			'hy' => 'Ընդամենը',
			'ru' => 'Итого',
			'en' => 'Order Total',
		),

		// CTA Button.
		'track_order_cta'           => array(
			'hy' => 'Դիտել իմ պատվերը',
			'ru' => 'Посмотреть мой заказ',
			'en' => 'View My Order',
		),

		// Payment Card.
		'payment_method_title'      => array(
			'hy' => 'Վճարման եղանակ',
			'ru' => 'Способ оплаты',
			'en' => 'Payment Method',
		),

		// Totals Note & Discount.
		'totals_discount'           => array(
			'hy' => 'Զեղչ՝',
			'ru' => 'Скидка:',
			'en' => 'Discount:',
		),
		'totals_note'               => array(
			'hy' => 'Նշում՝',
			'ru' => 'Примечание:',
			'en' => 'Note:',
		),

		// Addresses.
		'billing_address_title'     => array(
			'hy' => 'Վճարման տվյալներ',
			'ru' => 'Платёжные данные',
			'en' => 'Billing Details',
		),
		'shipping_address_title'    => array(
			'hy' => 'Առաքման հասցե',
			'ru' => 'Адрес доставки',
			'en' => 'Delivery Address',
		),

		// Scheduled Delivery Card.
		'scheduled_delivery_label'  => array(
			'hy' => 'Նախատեսված առաքում',
			'ru' => 'Ожидаемая доставка',
			'en' => 'Scheduled Delivery',
		),

		// Totals Labels.
		'totals_subtotal'           => array(
			'hy' => 'Ենթագումար՝',
			'ru' => 'Подытог:',
			'en' => 'Subtotal:',
		),
		'totals_shipping'           => array(
			'hy' => 'Առաքում՝',
			'ru' => 'Доставка:',
			'en' => 'Shipping:',
		),
		'totals_payment_method'     => array(
			'hy' => 'Վճարման եղանակ՝',
			'ru' => 'Способ оплаты:',
			'en' => 'Payment method:',
		),
		'totals_order_total'        => array(
			'hy' => 'Ընդամենը՝',
			'ru' => 'Итого:',
			'en' => 'Total:',
		),

		// Loyalty / Retention Card (Option 3).
		'loyalty_title'             => array(
			'hy' => 'Թարմ և տնական՝ ամեն օր Ձեր սեղանին',
			'ru' => 'Свежая домашняя кухня каждый день',
			'en' => 'Fresh Homemade Flavors, Every Day',
		),
		'loyalty_subtitle'          => array(
			'hy' => 'Բացահայտեք մեր նորույթները և պատվիրեք կրկին անմիջապես կայքից',
			'ru' => 'Откройте для себя новинки и заказывайте снова на нашем сайте',
			'en' => 'Explore seasonal favorites and order again anytime',
		),
		'loyalty_code_label'        => array(
			'hy' => 'Պրոմոկոդ՝ %s',
			'ru' => 'Промокод: %s',
			'en' => 'Code: %s',
		),
		'loyalty_cta'               => array(
			'hy' => 'Անցնել մենյու',
			'ru' => 'Перейти в меню',
			'en' => 'Browse Menu',
		),

		// Support & Footer.
		'questions_line'            => array(
			'hy' => 'Հարցեր ունե՞ք պատվերի վերաբերյալ։ Կապվեք մեզ հետ՝',
			'ru' => 'Есть вопросы по заказу? Свяжитесь с нами:',
			'en' => 'Questions about your order? Reach us at:',
		),
		'all_rights'                => array(
			'hy' => 'Բոլոր իրավունքները պաշտպանված են։',
			'ru' => 'Все права защищены.',
			'en' => 'All rights reserved.',
		),
	);
}

/**
 * Translate an email UI key into the active email language.
 */
function gv2_email_t( string $key, ?string $lang = null ): string {
	$lang = $lang ?? gv2_get_email_lang();
	$map  = gv2_email_translations_map();

	if ( isset( $map[ $key ][ $lang ] ) ) {
		return $map[ $key ][ $lang ];
	}
	if ( isset( $map[ $key ]['en'] ) ) {
		return $map[ $key ]['en'];
	}
	return $key;
}

/**
 * Translate WooCommerce customer email subjects and headings into the order's language.
 */
add_filter(
	'woocommerce_email_subject_customer_processing_order',
	static function ( string $subject, $order ): string {
		$lang = gv2_get_email_lang( $order );
		$num  = $order instanceof WC_Order ? $order->get_order_number() : '';
		return match ( $lang ) {
			'ru'    => sprintf( 'Ваш заказ #%s в Gayane’s Kitchen подтверждён', $num ),
			'en'    => sprintf( 'Your Gayane’s Kitchen order #%s is confirmed', $num ),
			default => sprintf( 'Ձեր #%s պատվերը Gayane’s Kitchen-ում հաստատված է', $num ),
		};
	},
	10,
	2
);

add_filter(
	'woocommerce_email_heading_customer_processing_order',
	static function ( string $heading, $order ): string {
		return gv2_email_t( 'order_confirmed_heading', gv2_get_email_lang( $order ) );
	},
	10,
	2
);

add_filter(
	'woocommerce_email_subject_customer_completed_order',
	static function ( string $subject, $order ): string {
		$lang = gv2_get_email_lang( $order );
		$num  = $order instanceof WC_Order ? $order->get_order_number() : '';
		return match ( $lang ) {
			'ru'    => sprintf( 'Ваш заказ #%s доставлен — Gayane’s Kitchen', $num ),
			'en'    => sprintf( 'Your Gayane’s Kitchen order #%s is complete', $num ),
			default => sprintf( 'Ձեր #%s պատվերը առաքված է — Gayane’s Kitchen', $num ),
		};
	},
	10,
	2
);

add_filter(
	'woocommerce_email_heading_customer_completed_order',
	static function ( string $heading, $order ): string {
		return gv2_email_t( 'order_completed_heading', gv2_get_email_lang( $order ) );
	},
	10,
	2
);

add_filter(
	'woocommerce_email_subject_customer_on_hold_order',
	static function ( string $subject, $order ): string {
		$lang = gv2_get_email_lang( $order );
		$num  = $order instanceof WC_Order ? $order->get_order_number() : '';
		return match ( $lang ) {
			'ru'    => sprintf( 'Заказ #%s принят и ожидает подтверждения — Gayane’s Kitchen', $num ),
			'en'    => sprintf( 'Your Gayane’s Kitchen order #%s is on hold', $num ),
			default => sprintf( 'Ձեր #%s պատվերը ընդունված է և սպասում է հաստատման', $num ),
		};
	},
	10,
	2
);

add_filter(
	'woocommerce_email_heading_customer_on_hold_order',
	static function ( string $heading, $order ): string {
		return gv2_email_t( 'order_on_hold_heading', gv2_get_email_lang( $order ) );
	},
	10,
	2
);

add_filter(
	'woocommerce_email_heading_customer_cancelled_order',
	static function ( string $heading, $order ): string {
		return gv2_email_t( 'order_cancelled_heading', gv2_get_email_lang( $order ) );
	},
	10,
	2
);

add_filter(
	'woocommerce_email_heading_customer_refunded_order',
	static function ( string $heading, $order ): string {
		return gv2_email_t( 'order_refunded_heading', gv2_get_email_lang( $order ) );
	},
	10,
	2
);

add_filter(
	'woocommerce_email_heading_customer_failed_order',
	static function ( string $heading, $order ): string {
		return gv2_email_t( 'order_failed_heading', gv2_get_email_lang( $order ) );
	},
	10,
	2
);

add_filter(
	'woocommerce_email_heading_customer_invoice',
	static function ( string $heading, $order ): string {
		return gv2_email_t( 'invoice_heading', gv2_get_email_lang( $order ) );
	},
	10,
	2
);

add_filter(
	'woocommerce_email_heading_customer_note',
	static function ( string $heading, $order ): string {
		return gv2_email_t( 'customer_note_heading', gv2_get_email_lang( $order ) );
	},
	10,
	2
);

add_filter(
	'woocommerce_email_heading_customer_new_account',
	static function ( string $heading, $user ): string {
		return gv2_email_t( 'new_account_heading', gv2_get_email_lang() );
	},
	10,
	2
);

add_filter(
	'woocommerce_email_heading_customer_reset_password',
	static function ( string $heading, $user ): string {
		return gv2_email_t( 'reset_password_heading', gv2_get_email_lang() );
	},
	10,
	2
);

/**
 * Show product thumbnails in HTML order emails to match Option 3 visual design.
 */
add_filter(
	'woocommerce_email_order_items_args',
	static function ( array $args ): array {
		if ( empty( $args['plain_text'] ) ) {
			$args['show_image'] = true;
			$args['image_size'] = array( 64, 64 );
		}
		return $args;
	},
	10,
	1
);

/**
 * Remove WooCommerce's default English additional_content ("Thanks again! If you need any help...")
 * from customer emails, since our branded footer handles support & contact in HY/RU/EN.
 */
foreach (
	array(
		'customer_processing_order',
		'customer_completed_order',
		'customer_on_hold_order',
		'customer_invoice',
		'customer_note',
		'customer_refunded_order',
		'customer_cancelled_order',
		'customer_failed_order',
		'customer_new_account',
		'customer_reset_password',
	) as $gv2_email_id
) {
	add_filter( "woocommerce_email_additional_content_{$gv2_email_id}", '__return_empty_string', 99 );
}

/**
 * Replace Gayanes_Core\Checkout_Delivery plain <p> email meta in HTML emails
 * with our styled Scheduled Delivery Card in email-order-details.php.
 */
add_action(
	'woocommerce_email_after_order_table',
	static function ( $order, $sent_to_admin, $plain_text ): void {
		if ( ! $plain_text && class_exists( 'Gayanes_Core\Checkout_Delivery' ) ) {
			remove_action( 'woocommerce_email_after_order_table', array( 'Gayanes_Core\Checkout_Delivery', 'display_email_meta' ), 10 );
		}
	},
	1,
	3
);

/**
 * Format a YYYY-MM-DD delivery date into a human-friendly localized string.
 */
function gv2_format_email_delivery_date( string $raw_date, string $lang ): string {
	$ts = strtotime( $raw_date );
	if ( false === $ts ) {
		return $raw_date;
	}

	$day   = (int) gmdate( 'j', $ts );
	$month = (int) gmdate( 'n', $ts );
	$year  = gmdate( 'Y', $ts );

	$hy_months = array(
		1  => 'հունվարի',
		2  => 'փետրվարի',
		3  => 'մարտի',
		4  => 'ապրիլի',
		5  => 'մայիսի',
		6  => 'հունիսի',
		7  => 'հուլիսի',
		8  => 'օգոստոսի',
		9  => 'սեպտեմբերի',
		10 => 'հոկտեմբերի',
		11 => 'նոյեմբերի',
		12 => 'դեկտեմբերի',
	);

	$ru_months = array(
		1  => 'января',
		2  => 'февраля',
		3  => 'марта',
		4  => 'апреля',
		5  => 'мая',
		6  => 'июня',
		7  => 'июля',
		8  => 'августа',
		9  => 'сентября',
		10 => 'октября',
		11 => 'ноября',
		12 => 'декабря',
	);

	return match ( $lang ) {
		'hy'    => sprintf( '%d %s, %s', $day, $hy_months[ $month ] ?? '', $year ),
		'ru'    => sprintf( '%d %s %s', $day, $ru_months[ $month ] ?? '', $year ),
		default => gmdate( 'M j, Y', $ts ),
	};
}

/**
 * Remove auto-appended Scheduled Delivery line from customer_note in emails
 * so delivery date & time are shown only in the dedicated Delivery Card.
 */
function gv2_clean_email_customer_note( string $note ): string {
	if ( '' === trim( $note ) ) {
		return '';
	}
	$lines   = preg_split( '/\r\n|\r|\n/', $note );
	$cleaned = array();
	foreach ( $lines as $line ) {
		$trimmed = trim( $line );
		if ( '' === $trimmed ) {
			continue;
		}
		if (
			str_starts_with( $trimmed, 'Նախատեսված առաքում:' ) ||
			str_starts_with( $trimmed, 'Ожидаемая доставка:' ) ||
			str_starts_with( $trimmed, 'Scheduled Delivery:' )
		) {
			continue;
		}
		$cleaned[] = $trimmed;
	}
	return implode( "\n", $cleaned );
}

/**
 * Ensure order totals labels (Subtotal, Shipping, Discount, Payment method, Total) match the order's language.
 */
add_filter(
	'woocommerce_get_order_item_totals',
	static function ( array $total_rows, $order ): array {
		$lang = gv2_get_email_lang( $order );
		$map  = array(
			'cart_subtotal'  => 'totals_subtotal',
			'shipping'       => 'totals_shipping',
			'discount'       => 'totals_discount',
			'payment_method' => 'totals_payment_method',
			'order_total'    => 'totals_order_total',
		);
		foreach ( $map as $row_key => $tr_key ) {
			if ( isset( $total_rows[ $row_key ]['label'] ) ) {
				$total_rows[ $row_key ]['label'] = gv2_email_t( $tr_key, $lang );
			}
		}
		return $total_rows;
	},
	20,
	2
);
