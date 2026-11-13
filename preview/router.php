<?php
/**
 * Localhost Interactive Preview Router for Gayane's Kitchen Theme & Core.
 *
 * Serves the real theme CSS/JS, Multilingual HY/RU/EN strings, and a
 * session-backed AJAX cart endpoint so the storefront can be previewed
 * interactively on http://127.0.0.1:8088 without a WordPress database.
 */

declare(strict_types=1);

session_start();

$root   = dirname( __DIR__ );
$uri    = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. Serve static theme assets directly.
if ( str_starts_with( $uri, '/wp-content/' ) ) {
	$file = $root . $uri;
	if ( is_file( $file ) ) {
		$ext = pathinfo( $file, PATHINFO_EXTENSION );
		$mime = match ( $ext ) {
			'css'  => 'text/css; charset=UTF-8',
			'js'   => 'application/javascript; charset=UTF-8',
			'svg'  => 'image/svg+xml',
			'png'  => 'image/png',
			'jpg', 'jpeg' => 'image/jpeg',
			'webp' => 'image/webp',
			default => 'application/octet-stream',
		};
		header( "Content-Type: {$mime}" );
		readfile( $file );
		exit;
	}
	http_response_code( 404 );
	exit;
}

// Minimal WP stubs for Multilingual class.
define( 'ABSPATH', $root . '/' );
$lang = $_GET['lang'] ?? ( $_SESSION['gk_lang'] ?? 'hy' );
if ( str_starts_with( $uri, '/ru' ) ) {
	$lang = 'ru';
} elseif ( str_starts_with( $uri, '/en' ) ) {
	$lang = 'en';
}
if ( ! in_array( $lang, array( 'hy', 'ru', 'en' ), true ) ) {
	$lang = 'hy';
}
$_SESSION['gk_lang'] = $lang;

function determine_locale(): string {
	return match ( $_SESSION['gk_lang'] ?? 'hy' ) {
		'ru' => 'ru_RU',
		'en' => 'en_US',
		default => 'hy',
	};
}
function add_action( string $h, $c, int $p = 10 ): void {}
function add_filter( string $h, $c, int $p = 10, int $a = 1 ): void {}
function has_filter( string $h ): bool { return false; }
function has_action( string $h ): bool { return false; }
function home_url( string $p = '/' ): string { return 'http://127.0.0.1:8088' . $p; }
function __( string $t, string $d = 'default' ): string { return $t; }
function esc_html( string $t ): string { return htmlspecialchars( $t, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( string $t ): string { return htmlspecialchars( $t, ENT_QUOTES, 'UTF-8' ); }
function esc_url( string $t ): string { return htmlspecialchars( $t, ENT_QUOTES, 'UTF-8' ); }

require_once $root . '/wp-content/plugins/gayanes-core/includes/class-multilingual.php';

$t = static fn( string $s ): string => Gayanes_Core\Multilingual::t( $s );

$catalog = array(
	10862 => array(
		'id'     => 10862,
		'hy'     => 'Խինկալի Տնական',
		'ru'     => 'Домашние хинкали',
		'en'     => 'Homemade Khinkali',
		'weight' => '900գ (10 հատ)',
		'price'  => 3600,
		'sale'   => null,
		'img'    => 'https://gayanes.am/wp-content/uploads/2026/06/Gemini_Generated_Image_fisrz0fisrz0fisr.png',
	),
	10570 => array(
		'id'     => 10570,
		'hy'     => 'Տոլմա խաղողի տերևով',
		'ru'     => 'Толма в виноградных листьях',
		'en'     => 'Grape Leaf Dolma',
		'weight' => '500գ',
		'price'  => 1800,
		'sale'   => 1400,
		'img'    => 'https://gayanes.am/wp-content/uploads/2025/03/sut-dolma.jpg',
	),
	10569 => array(
		'id'     => 10569,
		'hy'     => 'Ֆալաֆել տնական',
		'ru'     => 'Фалафель домашний',
		'en'     => 'Homemade Falafel',
		'weight' => '300գ',
		'price'  => 1300,
		'sale'   => null,
		'img'    => 'https://gayanes.am/wp-content/uploads/2025/03/falafel.jpg',
	),
	10566 => array(
		'id'     => 10566,
		'hy'     => 'Վարենիկ կարտոֆիլով',
		'ru'     => 'Вареники с картофелем',
		'en'     => 'Potato Vareniki',
		'weight' => '500գ',
		'price'  => 1000,
		'sale'   => null,
		'img'    => 'https://gayanes.am/wp-content/uploads/2025/03/varenik-kartofilov.jpg',
	),
	10565 => array(
		'id'     => 10565,
		'hy'     => 'Վարենիկ սպանախով',
		'ru'     => 'Вареники со шпинатом',
		'en'     => 'Spinach Vareniki',
		'weight' => '500գ',
		'price'  => 2200,
		'sale'   => null,
		'img'    => 'https://gayanes.am/wp-content/uploads/2025/03/varenik-spanakhov.jpg',
	),
	10568 => array(
		'id'     => 10568,
		'hy'     => 'Սպրինգ-ռոլլ բանջարեղենով',
		'ru'     => 'Спринг-роллы с овощами',
		'en'     => 'Vegetable Spring Rolls',
		'weight' => '250գ',
		'price'  => 350,
		'sale'   => null,
		'img'    => 'https://gayanes.am/wp-content/uploads/2025/03/springroll-banjareghenov.jpg',
	),
	10567 => array(
		'id'     => 10567,
		'hy'     => 'Սպրինգ-ռոլլ սնկով',
		'ru'     => 'Спринг-роллы с грибами',
		'en'     => 'Mushroom Spring Rolls',
		'weight' => '250գ',
		'price'  => 450,
		'sale'   => 350,
		'img'    => 'https://gayanes.am/wp-content/uploads/2025/03/springroll-snkov.jpg',
	),
	10905 => array(
		'id'     => 10905,
		'hy'     => 'Խինկալի ընտանեկան',
		'ru'     => 'Хинкали семейные',
		'en'     => 'Family Pack Khinkali',
		'weight' => '1.2կգ',
		'price'  => 4600,
		'sale'   => null,
		'img'    => 'https://gayanes.am/wp-content/uploads/2026/06/Gemini_Generated_Image_fisrz0fisrz0fisr.png',
	),
);

$_SESSION['gk_cart'] = $_SESSION['gk_cart'] ?? array();

$build_cart_payload = static function () use ( &$catalog, $lang ): array {
	$items      = array();
	$quantities = array();
	$total      = 0;
	$count      = 0;

	foreach ( $_SESSION['gk_cart'] as $pid => $qty ) {
		$pid = (int) $pid;
		$qty = (int) $qty;
		if ( $qty <= 0 || ! isset( $catalog[ $pid ] ) ) {
			continue;
		}
		$p          = $catalog[ $pid ];
		$unit_price = $p['sale'] ?? $p['price'];
		$line_total = $unit_price * $qty;
		$total     += $line_total;
		$count     += $qty;
		$key        = "key_{$pid}";

		$quantities[ (string) $pid ] = array(
			'qty'           => $qty,
			'cart_item_key' => $key,
		);
		$items[] = array(
			'key'          => $key,
			'product_id'   => $pid,
			'variation_id' => 0,
			'name'         => $p[ $lang ],
			'permalink'    => "#product-{$pid}",
			'thumbnail'    => $p['img'],
			'unit_price'   => number_format( $unit_price ) . ' ֏',
			'line_total'   => number_format( $line_total ) . ' ֏',
			'quantity'     => $qty,
		);
	}

	return array(
		'count'        => $count,
		'subtotal'     => number_format( $total ) . ' ֏',
		'total'        => number_format( $total ) . ' ֏',
		'items'        => $items,
		'quantities'   => $quantities,
		'cart_url'     => '#cart',
		'checkout_url' => '#checkout',
	);
};

// 2. Session-backed AJAX Cart endpoint.
if ( '/wp-admin/admin-ajax.php' === $uri ) {
	header( 'Content-Type: application/json; charset=UTF-8' );
	$action = $_REQUEST['action'] ?? '';
	$pid    = (int) ( $_POST['product_id'] ?? 0 );
	$qty    = (int) ( $_POST['quantity'] ?? 1 );
	$msg    = '';

	if ( 'gayanes_add_to_cart' === $action && isset( $catalog[ $pid ] ) ) {
		$_SESSION['gk_cart'][ $pid ] = ( $_SESSION['gk_cart'][ $pid ] ?? 0 ) + max( 1, $qty );
		$msg = $t( 'Added to cart' );
	} elseif ( 'gayanes_update_cart_qty' === $action ) {
		if ( 0 === $pid && isset( $_POST['cart_item_key'] ) ) {
			$pid = (int) str_replace( 'key_', '', (string) $_POST['cart_item_key'] );
		}
		if ( $qty <= 0 ) {
			unset( $_SESSION['gk_cart'][ $pid ] );
			$msg = $t( 'Removed from cart' );
		} elseif ( isset( $catalog[ $pid ] ) ) {
			$_SESSION['gk_cart'][ $pid ] = $qty;
		}
	} elseif ( 'gayanes_remove_cart_item' === $action && isset( $_POST['cart_item_key'] ) ) {
		$pid = (int) str_replace( 'key_', '', (string) $_POST['cart_item_key'] );
		unset( $_SESSION['gk_cart'][ $pid ] );
	}

	echo json_encode(
		array(
			'success' => true,
			'data'    => array_merge( $build_cart_payload(), array( 'message' => $msg ) ),
		),
		JSON_UNESCAPED_UNICODE
	);
	exit;
}

$initial_cart = $build_cart_payload();
$switcher     = Gayanes_Core\Multilingual::get_switcher_items();
$cats         = array(
	'hy' => array( 'Տոլմա', 'Խինկալի', 'Պելմենի', 'Նրբաբլիթ', 'Իշլի քյուֆթա', 'Կոտլետներ', 'Վարենիկներ', 'Պահածոներ և սոուսներ' ),
	'ru' => array( 'Долма', 'Хинкали', 'Пельмени', 'Блины', 'Ишли Кюфта', 'Котлеты', 'Вареники', 'Постное / Веган' ),
	'en' => array( 'Dolma', 'Khinkali', 'Pelmeni', 'Blini', 'Ishli Kyufta', 'Cutlets', 'Vareniki', 'Vegan & Fasting' ),
)[ $lang ];
?><!doctype html>
<html lang="<?php echo esc_attr( $lang ); ?>">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Gayane's Kitchen | Գայանեի Խոհանոց (Local Preview)</title>
	<style id="gayanes-critical-css">
		:root{--gk-bg:#F7F6F1;--gk-surface:#FFFFFF;--gk-text:#242923;--gk-primary:#304B39;--gk-secondary:#A8AD98;--gk-beige:#EAE6DC;--gk-border:#E2DFD5;--gk-danger:#B33A3A;--gk-radius:12px}
		html{box-sizing:border-box;background:var(--gk-bg);color:var(--gk-text)}
		*,*::before,*::after{box-sizing:inherit}
		body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Noto Sans Armenian","Noto Sans",sans-serif;line-height:1.55;background:var(--gk-bg);color:var(--gk-text)}
	</style>
	<link rel="stylesheet" href="/wp-content/themes/gayanes-theme/assets/css/main.css" />
</head>
<body>
<div class="gk-announcement">
	<div class="gk-container">
		<span><?php echo esc_html( $t( 'Free delivery in Yerevan on orders over 15,000 AMD' ) ); ?></span>
	</div>
</div>

<header class="gk-header" id="site-header">
	<div class="gk-container gk-header__inner">
		<button type="button" class="gk-icon-btn gk-header__menu-btn" data-gk-drawer-open="mobile-nav" aria-label="Menu">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round"/></svg>
		</button>
		<div class="gk-header__brand">
			<a href="/" class="gk-brand-text">Gayane’s Kitchen</a>
		</div>
		<nav class="gk-nav">
			<ul class="gk-nav__list">
				<li><a href="#top"><?php echo esc_html( $t( 'Home' ) ); ?></a></li>
				<li><a href="#products"><?php echo esc_html( $t( 'Shop All' ) ); ?></a></li>
				<li><a href="#about"><?php echo esc_html( $t( 'About Us' ) ); ?></a></li>
				<li><a href="#blog"><?php echo esc_html( $t( 'Blog & Recipes' ) ); ?></a></li>
			</ul>
		</nav>
		<div class="gk-header__actions">
			<nav class="gk-lang-switcher" aria-label="Language">
				<?php foreach ( $switcher as $item ) : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>" class="gk-lang-link<?php echo $item['current'] ? ' is-active' : ''; ?>"><?php echo esc_html( $item['label'] ); ?></a>
				<?php endforeach; ?>
			</nav>
			<button type="button" class="gk-icon-btn gk-search-toggle" data-gk-toggle-search aria-label="<?php echo esc_attr( $t( 'Search' ) ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
			</button>
			<button type="button" class="gk-cart-trigger" data-gk-drawer-open="cart" aria-label="<?php echo esc_attr( $t( 'Your Cart' ) ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 7h15l-1.5 9h-12z"/><path d="M6 7 5 3H2" stroke-linecap="round"/><circle cx="9.5" cy="20" r="1.3"/><circle cx="17.5" cy="20" r="1.3"/></svg>
				<span class="gk-cart-count" data-count="<?php echo (int) $initial_cart['count']; ?>"><?php echo (int) $initial_cart['count']; ?></span>
			</button>
		</div>
	</div>
	<div class="gk-header-search" id="gk-header-search" hidden>
		<div class="gk-container">
			<form class="gk-search-form" onsubmit="return false;">
				<input type="search" class="gk-search-form__input" placeholder="<?php echo esc_attr( $t( 'Search products...' ) ); ?>" />
				<button type="submit" class="gk-btn gk-btn--primary gk-btn--sm"><?php echo esc_html( $t( 'Search' ) ); ?></button>
			</form>
		</div>
	</div>
</header>

<main id="primary" class="gk-main">
	<section class="gk-hero">
		<div class="gk-container gk-hero__grid">
			<div class="gk-hero__content">
				<span class="gk-eyebrow">GAYANES.AM &middot; <?php echo strtoupper( esc_html( $lang ) ); ?></span>
				<h1 class="gk-hero__title">
					<?php echo esc_html( match ( $lang ) {
						'hy' => 'Տնական հայկական կիսաֆաբրիկատներ՝ պատրաստված սիրով',
						'ru' => 'Домашние армянские полуфабрикаты — свежие каждый день',
						default => 'Authentic Armenian Homemade Kitchen, Ready in Minutes',
					} ); ?>
				</h1>
				<p class="gk-hero__subtitle">
					<?php echo esc_html( match ( $lang ) {
						'hy' => 'Թարմ բաղադրիչներ, ավանդական բաղադրատոմսեր և արագ առաքում Երևանում։',
						'ru' => 'Натуральные ингредиенты, традиционные рецепты и быстрая доставка по Еревану.',
						default => 'Fresh natural ingredients, traditional family recipes, delivered chilled to your door.',
					} ); ?>
				</p>
				<div class="gk-hero__cta">
					<a href="#products" class="gk-btn gk-btn--primary"><?php echo esc_html( $t( 'Shop All' ) ); ?></a>
					<a href="#about" class="gk-btn gk-btn--outline"><?php echo esc_html( $t( 'About Us' ) ); ?></a>
				</div>
			</div>
			<div class="gk-hero__media">
				<img
					class="gk-hero__img"
					src="https://gayanes.am/wp-content/uploads/2025/03/sut-dolma.jpg"
					alt="Gayane’s Kitchen"
					fetchpriority="high"
				/>
			</div>
		</div>
	</section>

	<section class="gk-section gk-container">
		<div class="gk-section__head">
			<h2 class="gk-section__title"><?php echo esc_html( $t( 'Categories' ) ); ?></h2>
			<a href="#products" class="gk-link"><?php echo esc_html( $t( 'Shop All' ) ); ?> &rarr;</a>
		</div>
		<div class="gk-cat-grid">
			<?php
			$cat_imgs = array(
				'https://gayanes.am/wp-content/uploads/2025/03/sut-dolma.jpg',
				'https://gayanes.am/wp-content/uploads/2026/06/Gemini_Generated_Image_fisrz0fisrz0fisr.png',
				'https://gayanes.am/wp-content/uploads/2025/03/varenik-kartofilov.jpg',
				'https://gayanes.am/wp-content/uploads/2025/03/varenik-spanakhov.jpg',
				'https://gayanes.am/wp-content/uploads/2025/03/falafel.jpg',
				'https://gayanes.am/wp-content/uploads/2025/03/springroll-banjareghenov.jpg',
				'https://gayanes.am/wp-content/uploads/2025/03/springroll-snkov.jpg',
				'https://gayanes.am/wp-content/uploads/2025/03/sut-dolma.jpg',
			);
			foreach ( $cats as $idx => $c ) :
				$c_img = $cat_imgs[ $idx % count( $cat_imgs ) ];
				?>
				<a href="#products" class="gk-cat-card">
					<div class="gk-cat-card__media">
						<img src="<?php echo esc_url( $c_img ); ?>" alt="<?php echo esc_attr( $c ); ?>" loading="lazy" />
					</div>
					<span class="gk-cat-card__name"><?php echo esc_html( $c ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="gk-section gk-container" id="products">
		<div class="gk-section__head">
			<h2 class="gk-section__title"><?php echo esc_html( $t( 'Best Sellers' ) ); ?></h2>
		</div>
		<div class="gk-product-grid">
			<?php foreach ( $catalog as $p ) : ?>
				<?php $qty = (int) ( $_SESSION['gk_cart'][ $p['id'] ] ?? 0 ); ?>
				<article class="gk-card" data-product-id="<?php echo (int) $p['id']; ?>">
					<a href="#product-<?php echo (int) $p['id']; ?>" class="gk-card__media">
						<img class="gk-card__img" src="<?php echo esc_url( $p['img'] ); ?>" alt="<?php echo esc_attr( $p[ $lang ] ); ?>" loading="lazy" />
						<?php if ( null !== $p['sale'] ) : ?>
							<span class="gk-badge gk-badge--sale"><?php echo esc_html( $t( 'Sale' ) ); ?></span>
						<?php endif; ?>
					</a>
					<div class="gk-card__body">
						<div class="gk-card__meta">
							<span class="gk-card__weight"><?php echo esc_html( $p['weight'] ); ?></span>
							<span class="gk-card__stock is-in"><?php echo esc_html( $t( 'In Stock' ) ); ?></span>
						</div>
						<h3 class="gk-card__title"><a href="#product-<?php echo (int) $p['id']; ?>"><?php echo esc_html( $p[ $lang ] ); ?></a></h3>
						<div class="gk-card__footer">
							<div class="gk-card__price">
								<?php if ( null !== $p['sale'] ) : ?>
									<del><?php echo number_format( $p['price'] ); ?> ֏</del>
									<ins><?php echo number_format( $p['sale'] ); ?> ֏</ins>
								<?php else : ?>
									<?php echo number_format( $p['price'] ); ?> ֏
								<?php endif; ?>
							</div>
							<div
								class="gk-qty-control"
								data-gk-cart-control
								data-product-id="<?php echo (int) $p['id']; ?>"
								data-variation-id="0"
								data-cart-item-key="<?php echo $qty > 0 ? 'key_' . (int) $p['id'] : ''; ?>"
								data-qty="<?php echo $qty; ?>"
							>
								<button type="button" class="gk-btn gk-btn--primary gk-btn--sm gk-add-btn<?php echo $qty > 0 ? ' is-hidden' : ''; ?>" data-gk-action="add">
									<?php echo esc_html( $t( 'Add to Cart' ) ); ?>
								</button>
								<div class="gk-stepper<?php echo $qty > 0 ? '' : ' is-hidden'; ?>" role="group">
									<button type="button" class="gk-stepper__btn" data-gk-action="dec" aria-label="<?php echo esc_attr( $t( 'Decrease quantity' ) ); ?>">&minus;</button>
									<span class="gk-stepper__val"><?php echo $qty; ?></span>
									<button type="button" class="gk-stepper__btn" data-gk-action="inc" aria-label="<?php echo esc_attr( $t( 'Increase quantity' ) ); ?>">+</button>
								</div>
							</div>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="gk-section gk-container gk-about-band" id="about">
		<div class="gk-about-band__inner">
			<span class="gk-eyebrow">GAYANE’S KITCHEN</span>
			<h2><?php echo esc_html( $t( 'About Us' ) ); ?></h2>
			<p>100% natural homemade Armenian semi-finished products crafted daily with traditional family recipes.</p>
		</div>
	</section>
</main>

<div class="gk-drawer-backdrop" id="gk-drawer-backdrop" hidden></div>
<aside class="gk-drawer gk-drawer--right" id="gk-side-cart" role="dialog" aria-modal="true" aria-labelledby="gk-side-cart-title" hidden>
	<div class="gk-drawer__header">
		<h2 class="gk-drawer__title" id="gk-side-cart-title">
			<?php echo esc_html( $t( 'Your Cart' ) ); ?>
			<span class="gk-cart-count" data-count="<?php echo (int) $initial_cart['count']; ?>"><?php echo (int) $initial_cart['count']; ?></span>
		</h2>
		<button type="button" class="gk-icon-btn" data-gk-drawer-close aria-label="Close">&times;</button>
	</div>
	<div class="gk-drawer__body" id="gk-mini-cart-items"></div>
	<div class="gk-drawer__footer" id="gk-mini-cart-footer">
		<div class="gk-mini-cart__totals">
			<div class="gk-mini-cart__row"><span><?php echo esc_html( $t( 'Subtotal' ) ); ?></span><strong id="gk-mini-cart-subtotal">&mdash;</strong></div>
			<div class="gk-mini-cart__row gk-mini-cart__row--total"><span><?php echo esc_html( $t( 'Total' ) ); ?></span><strong id="gk-mini-cart-total">&mdash;</strong></div>
		</div>
		<div class="gk-mini-cart__actions">
			<a href="#cart" class="gk-btn gk-btn--outline gk-btn--full"><?php echo esc_html( $t( 'View Cart' ) ); ?></a>
			<a href="#checkout" class="gk-btn gk-btn--primary gk-btn--full"><?php echo esc_html( $t( 'Checkout' ) ); ?></a>
		</div>
	</div>
</aside>

<aside class="gk-drawer gk-drawer--left" id="gk-mobile-nav" role="dialog" aria-modal="true" hidden>
	<div class="gk-drawer__header">
		<span class="gk-drawer__title">Gayane’s Kitchen</span>
		<button type="button" class="gk-icon-btn" data-gk-drawer-close>&times;</button>
	</div>
	<div class="gk-drawer__body">
		<nav class="gk-lang-switcher">
			<?php foreach ( $switcher as $item ) : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>" class="gk-lang-link<?php echo $item['current'] ? ' is-active' : ''; ?>"><?php echo esc_html( $item['label'] ); ?></a>
			<?php endforeach; ?>
		</nav>
	</div>
</aside>

<nav class="gk-bottom-nav">
	<a href="/" class="gk-bottom-nav__item"><span><?php echo esc_html( $t( 'Home' ) ); ?></span></a>
	<a href="#products" class="gk-bottom-nav__item"><span><?php echo esc_html( $t( 'Categories' ) ); ?></span></a>
	<button type="button" class="gk-bottom-nav__item" data-gk-toggle-search><span><?php echo esc_html( $t( 'Search' ) ); ?></span></button>
	<button type="button" class="gk-bottom-nav__item" data-gk-drawer-open="cart">
		<span class="gk-bottom-nav__badge-wrap"><span class="gk-cart-count" data-count="<?php echo (int) $initial_cart['count']; ?>"><?php echo (int) $initial_cart['count']; ?></span></span>
		<span><?php echo esc_html( $t( 'Your Cart' ) ); ?></span>
	</button>
	<a href="#account" class="gk-bottom-nav__item"><span><?php echo esc_html( $t( 'Account' ) ); ?></span></a>
</nav>

<div class="gk-toast" id="gk-toast" role="status" aria-live="polite" hidden></div>

<script>
window.gayanesCartConfig = <?php echo json_encode(
	array(
		'ajaxUrl'     => '/wp-admin/admin-ajax.php',
		'nonce'       => 'local_preview_nonce',
		'initialCart' => $initial_cart,
		'i18n'        => array(
			'addToCart'    => $t( 'Add to Cart' ),
			'added'        => $t( 'Added to cart' ),
			'emptyCart'    => $t( 'Your cart is empty' ),
			'decreaseQty'  => $t( 'Decrease quantity' ),
			'increaseQty'  => $t( 'Increase quantity' ),
			'remove'       => $t( 'Remove' ),
			'subtotal'     => $t( 'Subtotal' ),
			'total'        => $t( 'Total' ),
			'viewCart'     => $t( 'View Cart' ),
			'checkout'     => $t( 'Checkout' ),
			'errorGeneric' => 'Error',
		),
	),
	JSON_UNESCAPED_UNICODE
); ?>;
</script>
<script src="/wp-content/themes/gayanes-theme/assets/js/cart.js" defer></script>
</body>
</html>

