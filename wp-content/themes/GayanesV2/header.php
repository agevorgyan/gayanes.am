<?php
/**
 * GayanesV2 Header: iOS 27 Liquid Glass sticky bar, mobile-first controls.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

$cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'gv2-body' ); ?>>
<?php wp_body_open(); ?>

<a class="gv2-skip-link" href="#primary"><?php echo esc_html( gv2_t( 'Skip to content' ) ); ?></a>

<div class="gv2-announcement">
	<div class="gv2-container">
		<span><?php echo esc_html( gv2_t( 'Free delivery in Yerevan on orders over 15,000 AMD' ) ); ?></span>
	</div>
</div>

<header class="gv2-header" id="site-header">
	<div class="gv2-container gv2-header__inner">
		<button
			type="button"
			class="gv2-glass-btn gv2-header__menu-btn"
			data-gv2-drawer-open="mobile-nav"
			aria-label="<?php echo esc_attr( gv2_t( 'Open menu' ) ); ?>"
			aria-expanded="false"
			aria-controls="gv2-mobile-nav"
		>
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
				<path d="M4 7h16M4 12h16M4 17h12" stroke-linecap="round"/>
			</svg>
		</button>

		<div class="gv2-header__brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gv2-logo-link" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<img src="<?php echo esc_url( GV2_URI . '/assets/images/logo-dark.png' ); ?>" class="gv2-logo-img" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="160" height="44" />
			</a>
		</div>

		<nav class="gv2-nav" aria-label="<?php echo esc_attr( gv2_t( 'Primary Navigation' ) ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'gv2-nav__list',
					'fallback_cb'    => static function () use ( $shop_url ): void {
						echo '<ul class="gv2-nav__list">';
						echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( gv2_t( 'Home' ) ) . '</a></li>';
						echo '<li><a href="' . esc_url( $shop_url ) . '">' . esc_html( gv2_t( 'Shop' ) ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/about-us/' ) ) . '">' . esc_html( gv2_t( 'About Us' ) ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/blog/' ) ) . '">' . esc_html( gv2_t( 'Blog & Recipes' ) ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/delivery/' ) ) . '">' . esc_html( gv2_t( 'Delivery & Payment' ) ) . '</a></li>';
						echo '</ul>';
					},
				)
			);
			?>
		</nav>

		<div class="gv2-header__actions">
			<div class="gv2-header__lang-desktop">
				<?php gv2_render_language_switcher(); ?>
			</div>

			<button
				type="button"
				class="gv2-glass-btn gv2-search-toggle"
				data-gv2-toggle-search
				aria-label="<?php echo esc_attr( gv2_t( 'Search' ) ); ?>"
				aria-expanded="false"
				aria-controls="gv2-header-search"
			>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
					<circle cx="11" cy="11" r="7"/>
					<path d="m20 20-3.5-3.5" stroke-linecap="round"/>
				</svg>
			</button>

			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a
					href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
					class="gv2-glass-btn gv2-header__account"
					aria-label="<?php echo esc_attr( gv2_t( 'My Account' ) ); ?>"
				>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<circle cx="12" cy="8" r="4"/>
						<path d="M4 20c1.5-4 5-6 8-6s6.5 2 8 6" stroke-linecap="round"/>
					</svg>
				</a>
			<?php endif; ?>

			<button
				type="button"
				class="gv2-cart-pill"
				data-gv2-drawer-open="cart"
				aria-label="<?php echo esc_attr( gv2_t( 'Your Cart' ) ); ?>"
				aria-controls="gv2-side-cart"
			>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
					<path d="M6 7h15l-1.5 9h-12z"/>
					<path d="M6 7 5 3H2" stroke-linecap="round"/>
					<circle cx="9.5" cy="20" r="1.3"/>
					<circle cx="17.5" cy="20" r="1.3"/>
				</svg>
				<span class="gv2-cart-pill__label"><?php echo esc_html( gv2_t( 'Cart' ) ); ?></span>
				<span class="gv2-cart-count" data-count="<?php echo esc_attr( (string) $cart_count ); ?>">
					<?php echo esc_html( (string) $cart_count ); ?>
				</span>
			</button>
		</div>
	</div>

	<div class="gv2-header-search" id="gv2-header-search" hidden>
		<div class="gv2-container">
			<form role="search" method="get" class="gv2-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input
					type="search"
					class="gv2-search-form__input"
					name="s"
					value="<?php echo esc_attr( get_search_query() ); ?>"
					placeholder="<?php echo esc_attr( gv2_t( 'Search products...' ) ); ?>"
					aria-label="<?php echo esc_attr( gv2_t( 'Search products...' ) ); ?>"
				/>
				<input type="hidden" name="post_type" value="product" />
				<button type="submit" class="gv2-btn gv2-btn--primary gv2-btn--sm">
					<?php echo esc_html( gv2_t( 'Search' ) ); ?>
				</button>
			</form>
		</div>
	</div>
</header>

