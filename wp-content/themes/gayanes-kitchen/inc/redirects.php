<?php
/**
 * Legacy URL 301 Redirects & Smart 404 Resolver for old.gayanes.am -> gayanes.am.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Exact 1-to-1 legacy URL map from old.gayanes.am to new gayanes.am paths.
 * Keys are UTF-8 decoded, lowercase paths with leading & trailing slashes.
 *
 * @return array<string, string>
 */
function gv2_get_legacy_redirect_map(): array {
	return array(
		// Legacy Pages (HY / EN / RU)
		'/menu/'                                => '/shop/',
		'/delivery-payment/'                    => '/delivery/',
		'/review-order/'                        => '/checkout/',
		'/privacy-am/'                          => '/privacy-policy/',
		'/wheel/'                               => '/',
		'/404-not-found-error/'                 => '/',
		'/ru/главная/'                          => '/ru/',
		'/ru/ассортимент/'                      => '/ru/shop/',
		'/ru/о-нас/'                            => '/ru/about-us/',
		'/ru/доставка-и-оплата/'                => '/ru/delivery/',
		'/ru/контакты/'                         => '/ru/contact/',
		'/ru/часто-задаваемые-вопросы/'         => '/ru/faq/',
		'/ru/корпоративным-клиентам/'           => '/ru/corporate-clients/',
		'/ru/карьера/'                          => '/ru/career/',
		'/ru/политика-конфиденциальности/'      => '/ru/privacy-policy/',
		'/ru/условия-и-положения/'              => '/ru/terms/',
		'/ru/кухонный-блог/'                    => '/ru/blog/',
		'/ru/корзина/'                          => '/ru/cart/',
		'/ru/оформление-заказа/'                => '/ru/checkout/',
		'/ru/мой-аккаунт/'                      => '/ru/my-account/',
		'/ru/призовое-колесо/'                  => '/ru/',

		// Legacy Armenian Products
		'/product/մանթի/'                       => '/product/manti/',
		'/product/chicken-nuggets/'             => '/product/nagets-havi-krtskamsov/',
		'/product/ishli-qyufta/'                => '/product/ishli-kyufta/',
		'/product/ishli-qyufta-popok/'          => '/product/ishli-kyufta-ynkuyzov/',
		'/product/վարենիկ-սնկով-500գ/'          => '/product/varenik-snkov/',
		'/product/blinchiki-tvorog/'            => '/product/nrbablit-katnashorov/',
		'/product/blinchik-tavari/'             => '/product/nrbablit-tavari-msov/',
		'/product/blinchik-list/'               => '/product/nrbabliti-sherter/',
		'/product/սուտ-տոլմա-թփով-500գր/'       => '/product/tolma-brndzov-khaghoghi-terevov/',
		'/product/պասուց-տոլմա/'                => '/product/tolma-pasuts/',
		'/product/կաղամբով-տոլմա/'              => '/product/tolma-kaghambov/',
		'/product/dolma-tpov/'                  => '/product/tolma-tpov/',
		'/product/tnakan-khinkali-10-hat/'      => '/product/khinkali-tnakan/',
		'/product/pelmen-sibir/'                => '/product/pelmeni-sibiryan/',
		'/product/ֆալաֆել-300գր/'               => '/product/falafel/',

		// Legacy Russian Products
		'/ru/product/блинные-листы/'                           => '/ru/product/blinnaya-zagotovka/',
		'/ru/product/пельмени-сибирские/'                      => '/ru/product/pelmeni-sibirskie/',
		'/ru/product/наггетсы-с-куриной-грудкой/'              => '/ru/product/naggetsy-iz-kurinoy-grudki/',
		'/ru/product/голубцы-капустная-долма/'                 => '/ru/product/golubtsy-tolma-v-kapustnykh-listyakh/',
		'/ru/product/цветные-пельмени/'                        => '/ru/product/pelmeni-tsvetnye/',
		'/ru/product/вареники-со-шпинатом-500-г/'              => '/ru/product/vareniki-so-shpinatom/',
		'/ru/product/вареники-с-картофелем-500-г/'             => '/ru/product/varenik-s-kartofelem/',
		'/ru/product/вареники-с-грибами-500-г/'                => '/ru/product/vareniki-s-gribami/',
		'/ru/product/постная-долма/'                           => '/ru/product/tolma-pasuts-postnaya/',
		'/ru/product/фалафель-300-г/'                          => '/ru/product/falafel-domashniy/',
		'/ru/product/толма-с-рисом-в-виноградных-листьях-500-г/' => '/ru/product/postnaya-tolma-s-risom/',
		'/ru/product/блинчик-с-творогом/'                      => '/ru/product/blinchik-s-tvorogom/',
		'/ru/product/ишли-кюфта/'                              => '/ru/product/ishli-kyufta-domashnyaya/',
		'/ru/product/блинчики-с-говядиной/'                    => '/ru/product/blinchik-s-govyadinoy/',
		'/ru/product/котлеты-из-говядины/'                     => '/ru/product/kotleta-govyazhya/',
		'/ru/product/котлеты-по-киевски/'                      => '/ru/product/kotleta-po-kievski/',
		'/ru/product/ишли-кюфта-с-грецкими-орехами/'           => '/ru/product/ishli-kyufta-s-gretskim-orekhom/',
		'/ru/product/куриные-котлеты/'                         => '/ru/product/kotleta-kurinaya/',
		'/ru/product/домашние-хинкали-10-шт/'                  => '/ru/product/khinkali-domashnie/',
		'/ru/product/долма-в-виноградных-листьях/'             => '/ru/product/tolma-v-vinogradnykh-listyakh/',

		// Legacy Armenian Categories
		'/product-category/առանձնահատուկ-պատվերներ/'  => '/product-category/ayl-utestner/',
		'/product-category/բաստուրմա-սուջուխ/'         => '/product-category/tnakan-msamterk/',
		'/product-category/բուսական/'                  => '/product-category/vegan-pahki/',
		'/product-category/թարմ-միս/'                  => '/product-category/tnakan-msamterk/',
		'/product-category/իշլի-քյուֆթա/'              => '/product-category/avandakan-utestner/',
		'/product-category/խինկալի/'                   => '/product-category/khinkali-ev-manti/',
		'/product-category/կոտլետ/'                    => '/product-category/kololak-ev-kotletner/',
		'/product-category/մանթի/'                     => '/product-category/khinkali-ev-manti/',
		'/product-category/մսամթերք/'                  => '/product-category/tnakan-msamterk/',
		'/product-category/նագեթս/'                    => '/product-category/nrbablitner-ev-nagetsner/',
		'/product-category/նրբաբլիթ-բլինչիկ/'          => '/product-category/nrbablitner/',
		'/product-category/պահքի-տեսականի/'            => '/product-category/vegan-pahki/',
		'/product-category/սպրինգ-ռոլլ/'               => '/product-category/vegan-pahki/',
		'/product-category/վարենիկ/'                   => '/product-category/vareniki/',
		'/product-category/տոլմա/'                     => '/product-category/dolma/',

		// Legacy Russian Categories
		'/ru/product-category/блины/'                  => '/ru/product-category/blinchiki/',
		'/ru/product-category/вареники/'               => '/ru/product-category/vareniki-ru/',
		'/ru/product-category/веган/'                  => '/ru/product-category/vegan-postnoe/',
		'/ru/product-category/долма/'                  => '/ru/product-category/tolma-ru/',
		'/ru/product-category/индивидуальные-заказы/'  => '/ru/product-category/drugie-blyuda/',
		'/ru/product-category/ишли-кюфта/'             => '/ru/product-category/traditsionnye-blyuda/',
		'/ru/product-category/котлеты/'                => '/ru/product-category/kololak-i-kotlety/',
		'/ru/product-category/манты/'                  => '/ru/product-category/khinkali-i-manty/',
		'/ru/product-category/мясо/'                   => '/ru/product-category/domashnie-myasnye-izdeliya/',
		'/ru/product-category/наггетсы/'               => '/ru/product-category/blinchiki-i-naggetsy/',
		'/ru/product-category/пельмени/'               => '/ru/product-category/pelmeni-ru/',
		'/ru/product-category/полуфабрикат/'           => '/ru/product-category/polufabrikaty/',
		'/ru/product-category/постное/'                => '/ru/product-category/vegan-postnoe/',
		'/ru/product-category/свежие-мясные-продукты/' => '/ru/product-category/domashnie-myasnye-izdeliya/',
		'/ru/product-category/спринг-роллы/'           => '/ru/product-category/vegan-postnoe/',

		// Legacy Blog Posts
		'/հավի-թռչնամսի-օգտակարության-մասին/'            => '/havi-misi-ogtakarutyan-masin/',
		'/ishli-qyufta/'                                 => '/ishli-kyuftayi-patmutyuny-ev-avanduytner/',
		'/ru/как-сварить-вкусные-пельмени-пошагов/'       => '/ru/kak-svarit-vkusnye-pelmeni/',
		'/inchpes-epel-hamegh-pelmenner/'                => '/inchpes-yepel-hamegh-pelmeniner/',
		'/ru/как-выбрать-качественные-полуфабрик/'       => '/ru/kak-vybrat-kachestvennye-polufabrikaty/',
		'/vorakyal-kisafabrikater-yntaniki-hamar/'       => '/inchpes-yntrel-vorakyal-kisafabrikatner/',
		'/ru/как-правильно-хранить-полуфабрикаты/'       => '/ru/kak-pravilno-khranit-polufabrikaty/',
		'/inchpes-pahpanel-kisafabrikaty-gayanes-kitchen/' => '/inchpes-chisht-pahpanel-kisafabrikatnere/',
		'/inchpes-ephel-khinkaly/'                       => '/inchpes-chisht-yepel-khinkalin/',
	);
}

/**
 * Transliterate Armenian and Russian characters into Latin slug form for fuzzy 404 matching.
 *
 * @param string $text Input slug or text.
 */
function gv2_transliterate_slug( string $text ): string {
	static $map = array(
		// Armenian
		'ա' => 'a', 'բ' => 'b', 'գ' => 'g', 'դ' => 'd', 'ե' => 'e', 'զ' => 'z',
		'է' => 'e', 'ը' => 'y', 'թ' => 't', 'ժ' => 'zh', 'ի' => 'i', 'լ' => 'l',
		'խ' => 'kh', 'ծ' => 'ts', 'կ' => 'k', 'հ' => 'h', 'ձ' => 'dz', 'ղ' => 'gh',
		'ճ' => 'ch', 'մ' => 'm', 'յ' => 'y', 'ն' => 'n', 'շ' => 'sh', 'ո' => 'o',
		'չ' => 'ch', 'պ' => 'p', 'ջ' => 'j', 'ռ' => 'r', 'ս' => 's', 'վ' => 'v',
		'տ' => 't', 'ր' => 'r', 'ց' => 'ts', 'ու' => 'u', 'փ' => 'p', 'ք' => 'k',
		'և' => 'ev', 'օ' => 'o', 'ֆ' => 'f',
		// Russian
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
		'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
		'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
		'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts',
		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
		'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
	);

	$lower = mb_strtolower( $text, 'UTF-8' );
	$latin = strtr( $lower, $map );
	$latin = preg_replace( '/[^a-z0-9-]+/', '-', $latin );
	$latin = preg_replace( '/-+/', '-', (string) $latin );

	return trim( (string) $latin, '-' );
}

/**
 * Smart 301 redirect handler for legacy URLs, ?lang= query params, and fuzzy 404 recovery.
 */
add_action(
	'template_redirect',
	static function (): void {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$parsed      = wp_parse_url( $request_uri );
		$raw_path    = $parsed['path'] ?? '/';
		$decoded     = mb_strtolower( rawurldecode( $raw_path ), 'UTF-8' );
		$normalized  = '/' . trim( $decoded, '/' ) . '/';
		if ( '//' === $normalized ) {
			$normalized = '/';
		}

		// 1. Exact legacy URL map (fast O(1) lookup).
		$map = gv2_get_legacy_redirect_map();
		if ( isset( $map[ $normalized ] ) ) {
			$target = home_url( $map[ $normalized ] );
			if ( ! empty( $parsed['query'] ) ) {
				parse_str( $parsed['query'], $qs );
				unset( $qs['lang'] );
				if ( ! empty( $qs ) ) {
					$target = add_query_arg( $qs, $target );
				}
			}
			wp_safe_redirect( $target, 301 );
			exit;
		}

		// 2. Legacy ?lang=ru / ?lang=en query parameter redirect.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['lang'] ) && in_array( $_GET['lang'], array( 'ru', 'en' ), true ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$q_lang = sanitize_key( (string) wp_unslash( $_GET['lang'] ) );
			if ( ! preg_match( '#^/(ru|en)(/|$)#', $normalized ) ) {
				$prefixed = '/' . $q_lang . ( '/' === $normalized ? '/' : $normalized );
				if ( isset( $map[ $prefixed ] ) ) {
					$prefixed = $map[ $prefixed ];
				}
				wp_safe_redirect( home_url( $prefixed ), 301 );
				exit;
			}
		}

		// 3. Smart 404 fuzzy resolver for any remaining unmapped old slugs.
		if ( ! is_404() ) {
			return;
		}

		$lang = 'hy';
		$sub  = $normalized;
		if ( preg_match( '#^/(ru|en)(/.*)$#', $normalized, $m ) ) {
			$lang = $m[1];
			$sub  = $m[2];
		}

		$segments = array_values( array_filter( explode( '/', trim( $sub, '/' ) ) ) );
		if ( empty( $segments ) ) {
			return;
		}

		$last_seg = end( $segments );
		$clean_lat = gv2_transliterate_slug( (string) $last_seg );
		// Strip common legacy weight/count suffixes like -500gr, -500g, -1kg, -10-hat, -10-sht.
		$clean_lat = preg_replace( '/-(?:\d+(?:gr|g|kg|hat|sht)|10-hat|10-sht)$/', '', $clean_lat );

		if ( '' === $clean_lat ) {
			return;
		}

		global $wpdb;

		// 3a. Check _wp_old_slug meta.
		$old_post_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT pm.post_id
				FROM {$wpdb->postmeta} pm
				JOIN {$wpdb->posts} p ON p.ID = pm.post_id AND p.post_status = 'publish'
				WHERE pm.meta_key = '_wp_old_slug' AND pm.meta_value IN (%s, %s)
				LIMIT 1",
				$last_seg,
				$clean_lat
			)
		);
		if ( $old_post_id > 0 ) {
			$url = get_permalink( $old_post_id );
			if ( $url ) {
				wp_safe_redirect( $url, 301 );
				exit;
			}
		}

		// 3b. Determine target type (product_cat, product, or post/page) and find closest slug in same language.
		$is_cat     = str_contains( $sub, '/product-category/' );
		$is_product = str_contains( $sub, '/product/' );

		if ( $is_cat ) {
			$candidates = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT t.term_id, t.slug
					FROM {$wpdb->terms} t
					JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id AND tt.taxonomy = 'product_cat' AND tt.count > 0
					LEFT JOIN {$wpdb->prefix}gm_translations tr ON tr.object_id = t.term_id AND tr.object_type = 'term'
					WHERE COALESCE(tr.lang, 'hy') = %s",
					$lang
				)
			);
			$best_id    = 0;
			$best_score = 0.0;
			foreach ( (array) $candidates as $cand ) {
				similar_text( $clean_lat, (string) $cand->slug, $pct );
				if ( $pct > $best_score ) {
					$best_score = $pct;
					$best_id    = (int) $cand->term_id;
				}
			}
			if ( $best_id > 0 && $best_score >= 68.0 ) {
				$term_link = get_term_link( $best_id, 'product_cat' );
				if ( ! is_wp_error( $term_link ) ) {
					wp_safe_redirect( $term_link, 301 );
					exit;
				}
			}
		} else {
			$post_types = $is_product ? array( 'product' ) : array( 'post', 'page', 'product' );
			$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = $wpdb->prepare(
				"SELECT p.ID, p.post_name
				FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->prefix}gm_translations tr ON tr.object_id = p.ID AND tr.object_type = 'post'
				WHERE p.post_status = 'publish'
				  AND p.post_type IN ($placeholders)
				  AND COALESCE(tr.lang, 'hy') = %s",
				array_merge( $post_types, array( $lang ) )
			);
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$candidates = $wpdb->get_results( $sql );
			$best_id    = 0;
			$best_score = 0.0;
			foreach ( (array) $candidates as $cand ) {
				similar_text( $clean_lat, (string) $cand->post_name, $pct );
				if ( $pct > $best_score ) {
					$best_score = $pct;
					$best_id    = (int) $cand->ID;
				}
			}
			if ( $best_id > 0 && $best_score >= 70.0 ) {
				$url = get_permalink( $best_id );
				if ( $url ) {
					wp_safe_redirect( $url, 301 );
					exit;
				}
			}
		}
	},
	1
);
