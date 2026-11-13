<?php
/**
 * One-time local WordPress + WooCommerce store seeder.
 * Creates WooCommerce pages, brand pages, Polylang languages (hy/ru/en),
 * product categories, simple & variable products, and local payment methods.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// 1. Create WooCommerce standard pages (Shop, Cart, Checkout, My Account).
if ( class_exists( 'WC_Install' ) ) {
	WC_Install::create_pages();
}

// 2. Create brand pages & set Homepage.
$pages = array(
	'home' => array(
		'title'   => 'Գլխավոր | Gayane’s Kitchen',
		'slug'    => 'home',
		'content' => '',
	),
	'about-us' => array(
		'title'   => 'Մեր մասին',
		'slug'    => 'about-us',
		'content' => '<p>Գայանեի Խոհանոցը պատրաստում է 100% բնական, տնական հայկական կիսաֆաբրիկատներ՝ ավանդական ընտանեկան բաղադրատոմսերով։</p>',
	),
	'delivery' => array(
		'title'   => 'Առաքում և վճարում',
		'slug'    => 'delivery',
		'content' => '<p>Անվճար առաքում Երևանում 15,000 դրամից բարձր պատվերների դեպքում։ Հասանելի վճարումներ՝ Idram, ArCa, Ameriabank, Telcell և կանխիկ։</p>',
	),
	'contact' => array(
		'title'   => 'Կապ',
		'slug'    => 'contact',
		'content' => '<p>Երևան, Հայաստան · Հեռ․՝ +374 00 000 000 · info@gayanes.am</p>',
	),
	'faq' => array(
		'title'    => 'Հաճախ տրվող հարցեր',
		'slug'     => 'faq',
		'template' => 'page-templates/template-faq.php',
		'content'  => '<div class="gk-faq-list"><details class="gk-faq-item" open><summary>Ինչպե՞ս պահպանել կիսաֆաբրիկատները</summary><p>Պահել սառցախցիկում -18°C ջերմաստիճանում մինչև 3 ամիս։</p></details><details class="gk-faq-item"><summary>Որքա՞ն է առաքման արժեքը Երևանում</summary><p>15,000 դրամից բարձր պատվերների դեպքում առաքումն անվճար է։</p></details></div>',
	),
	'blog' => array(
		'title'   => 'Բլոգ և բաղադրատոմսեր',
		'slug'    => 'blog',
		'content' => '',
	),
	'privacy-policy' => array(
		'title'   => 'Գաղտնիության քաղաքականություն',
		'slug'    => 'privacy-policy',
		'content' => '<p>Ձեր անձնական տվյալները պաշտպանված են և օգտագործվում են միայն պատվերների առաքման համար։</p>',
	),
	'terms' => array(
		'title'   => 'Պայմաններ և դրույթներ',
		'slug'    => 'terms',
		'content' => '<p>Օգտվելով gayanes.am կայքից՝ դուք համաձայնում եք պատվերների ընդունման և առաքման պայմաններին։</p>',
	),
);

$page_ids = array();
foreach ( $pages as $key => $cfg ) {
	$existing = get_page_by_path( $cfg['slug'] );
	if ( $existing ) {
		$page_ids[ $key ] = $existing->ID;
		continue;
	}
	$pid = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $cfg['title'],
		'post_name'    => $cfg['slug'],
		'post_content' => $cfg['content'],
	) );
	if ( $pid && ! is_wp_error( $pid ) ) {
		$page_ids[ $key ] = $pid;
		if ( ! empty( $cfg['template'] ) ) {
			update_post_meta( $pid, '_wp_page_template', $cfg['template'] );
		}
	}
}

if ( ! empty( $page_ids['home'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page_ids['home'] );
}
if ( ! empty( $page_ids['blog'] ) ) {
	update_option( 'page_for_posts', $page_ids['blog'] );
}

// 3. Helper to sideload image once.
$sideload_once = static function ( string $url, string $title ): int {
	$existing = get_posts( array(
		'post_type'      => 'attachment',
		'title'          => $title,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}
	$id = media_sideload_image( $url, 0, $title, 'id' );
	return is_wp_error( $id ) ? 0 : (int) $id;
};

// 4. Categories & Sample Products.
$categories = array(
	'khinkali' => array( 'name' => 'Խինկալի', 'img' => 'https://gayanes.am/wp-content/uploads/2026/06/Gemini_Generated_Image_fisrz0fisrz0fisr.png' ),
	'dolma'    => array( 'name' => 'Տոլմա', 'img' => 'https://gayanes.am/wp-content/uploads/2025/03/sut-dolma.jpg' ),
	'vareniki' => array( 'name' => 'Վարենիկներ', 'img' => 'https://gayanes.am/wp-content/uploads/2025/03/varenik-kartofilov.jpg' ),
	'vegan'    => array( 'name' => 'Վեգան / Պահքի', 'img' => 'https://gayanes.am/wp-content/uploads/2025/03/falafel.jpg' ),
);

$cat_ids = array();
foreach ( $categories as $slug => $c ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( ! $term ) {
		$res = wp_insert_term( $c['name'], 'product_cat', array( 'slug' => $slug ) );
		if ( ! is_wp_error( $res ) ) {
			$cat_ids[ $slug ] = (int) $res['term_id'];
		}
	} else {
		$cat_ids[ $slug ] = (int) $term->term_id;
	}
	if ( ! empty( $cat_ids[ $slug ] ) ) {
		$img_id = $sideload_once( $c['img'], 'cat-' . $slug );
		if ( $img_id ) {
			update_term_meta( $cat_ids[ $slug ], 'thumbnail_id', $img_id );
		}
	}
}

$products = array(
	array(
		'sku'     => 'GK-KHINKALI-900',
		'name'    => 'Խինկալի Տնական (900գ)',
		'price'   => '3600',
		'sale'    => '',
		'weight'  => '900',
		'cat'     => 'khinkali',
		'featured'=> true,
		'img'     => 'https://gayanes.am/wp-content/uploads/2026/06/Gemini_Generated_Image_fisrz0fisrz0fisr.png',
	),
	array(
		'sku'     => 'GK-DOLMA-500',
		'name'    => 'Տոլմա բրնձով խաղողի տերևով (500գ)',
		'price'   => '1800',
		'sale'    => '1400',
		'weight'  => '500',
		'cat'     => 'dolma',
		'featured'=> true,
		'img'     => 'https://gayanes.am/wp-content/uploads/2025/03/sut-dolma.jpg',
	),
	array(
		'sku'     => 'GK-FALAFEL-300',
		'name'    => 'Ֆալաֆել տնական (300գ)',
		'price'   => '1300',
		'sale'    => '',
		'weight'  => '300',
		'cat'     => 'vegan',
		'featured'=> true,
		'img'     => 'https://gayanes.am/wp-content/uploads/2025/03/falafel.jpg',
	),
	array(
		'sku'     => 'GK-VARENIK-POTATO-500',
		'name'    => 'Վարենիկ կարտոֆիլով (500գ)',
		'price'   => '1000',
		'sale'    => '',
		'weight'  => '500',
		'cat'     => 'vareniki',
		'featured'=> false,
		'img'     => 'https://gayanes.am/wp-content/uploads/2025/03/varenik-kartofilov.jpg',
	),
	array(
		'sku'     => 'GK-VARENIK-SPINACH-500',
		'name'    => 'Վարենիկ սպանախով (500գ)',
		'price'   => '2200',
		'sale'    => '',
		'weight'  => '500',
		'cat'     => 'vareniki',
		'featured'=> true,
		'img'     => 'https://gayanes.am/wp-content/uploads/2025/03/varenik-spanakhov.jpg',
	),
	array(
		'sku'     => 'GK-SPRINGROLL-VEG',
		'name'    => 'Սպրինգ-ռոլլ բանջարեղենով',
		'price'   => '350',
		'sale'    => '',
		'weight'  => '250',
		'cat'     => 'vegan',
		'featured'=> false,
		'img'     => 'https://gayanes.am/wp-content/uploads/2025/03/springroll-banjareghenov.jpg',
	),
	array(
		'sku'     => 'GK-SPRINGROLL-MUSH',
		'name'    => 'Սպրինգ-ռոլլ սնկով',
		'price'   => '450',
		'sale'    => '350',
		'weight'  => '250',
		'cat'     => 'vegan',
		'featured'=> false,
		'img'     => 'https://gayanes.am/wp-content/uploads/2025/03/springroll-snkov.jpg',
	),
);

foreach ( $products as $p ) {
	if ( wc_get_product_id_by_sku( $p['sku'] ) ) {
		continue;
	}
	$prod = new WC_Product_Simple();
	$prod->set_name( $p['name'] );
	$prod->set_sku( $p['sku'] );
	$prod->set_regular_price( $p['price'] );
	if ( '' !== $p['sale'] ) {
		$prod->set_sale_price( $p['sale'] );
	}
	$prod->set_weight( $p['weight'] );
	$prod->set_status( 'publish' );
	$prod->set_catalog_visibility( 'visible' );
	$prod->set_stock_status( 'instock' );
	$prod->set_featured( $p['featured'] );
	if ( ! empty( $cat_ids[ $p['cat'] ] ) ) {
		$prod->set_category_ids( array( $cat_ids[ $p['cat'] ] ) );
	}
	$img_id = $sideload_once( $p['img'], $p['sku'] );
	if ( $img_id ) {
		$prod->set_image_id( $img_id );
	}
	$prod->save();
}

// 5. Enable Cash on Delivery & disable Coming Soon mode for local verification.
$cod_settings = get_option( 'woocommerce_cod_settings', array() );
$cod_settings['enabled'] = 'yes';
update_option( 'woocommerce_cod_settings', $cod_settings );
update_option( 'woocommerce_coming_soon', 'no' );

echo "Local WordPress + WooCommerce store seeded successfully.\n";

