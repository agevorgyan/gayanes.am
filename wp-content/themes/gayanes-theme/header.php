<?php
/**
 * Theme Header: compact, mobile-first, accessible.
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

$cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="gk-skip-link" href="#primary"><?php echo esc_html( gayanes_t( 'Skip to content' ) ); ?></a>

<div class="gk-announcement">
	<div class="gk-container">
		<span><?php echo esc_html( gayanes_t( 'Free delivery in Yerevan on orders over 15,000 AMD' ) ); ?></span>
	</div>
</div>

<header class="gk-header" id="site-header">
	<div class="gk-container gk-header__inner">
		<button
			type="button"
			class="gk-icon-btn gk-header__menu-btn"
			data-gk-drawer-open="mobile-nav"
			aria-label="<?php echo esc_attr( gayanes_t( 'Open menu' ) ); ?>"
			aria-expanded="false"
			aria-controls="gk-mobile-nav"
		>
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
				<path d="M3 6h18M3 12h18M3 18h18" stroke-linecap="round"/>
			</svg>
		</button>

		<div class="gk-header__brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gk-brand-text">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<nav class="gk-nav" aria-label="<?php echo esc_attr( gayanes_t( 'Primary Navigation' ) ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'gk-nav__list',
					'fallback_cb'    => static function (): void {
						echo '<ul class="gk-nav__list">';
						echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( gayanes_t( 'Home' ) ) . '</a></li>';
						if ( function_exists( 'wc_get_page_permalink' ) ) {
							echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html( gayanes_t( 'Shop All' ) ) . '</a></li>';
						}
						echo '<li><a href="' . esc_url( home_url( '/about-us/' ) ) . '">' . esc_html( gayanes_t( 'About Us' ) ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/delivery/' ) ) . '">' . esc_html( gayanes_t( 'Delivery & Payment' ) ) . '</a></li>';
						echo '<li><a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html( gayanes_t( 'Contact' ) ) . '</a></li>';
						echo '</ul>';
					},
				)
			);
			?>
		</nav>

		<div class="gk-header__actions">
			<?php gayanes_render_language_switcher(); ?>

			<button
				type="button"
				class="gk-icon-btn gk-search-toggle"
				data-gk-toggle-search
				aria-label="<?php echo esc_attr( gayanes_t( 'Search' ) ); ?>"
				aria-expanded="false"
				aria-controls="gk-header-search"
			>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
					<circle cx="11" cy="11" r="7"/>
					<path d="m20 20-3.5-3.5" stroke-linecap="round"/>
				</svg>
			</button>

			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a
					href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
					class="gk-icon-btn gk-header__account"
					aria-label="<?php echo esc_attr( gayanes_t( 'My Account' ) ); ?>"
				>
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
						<circle cx="12" cy="8" r="4"/>
						<path d="M4 20c1.5-4 5-6 8-6s6.5 2 8 6" stroke-linecap="round"/>
					</svg>
				</a>
			<?php endif; ?>

			<button
				type="button"
				class="gk-cart-trigger"
				data-gk-drawer-open="cart"
				aria-label="<?php echo esc_attr( gayanes_t( 'Your Cart' ) ); ?>"
				aria-controls="gk-side-cart"
			>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
					<path d="M6 7h15l-1.5 9h-12z"/>
					<path d="M6 7 5 3H2" stroke-linecap="round"/>
					<circle cx="9.5" cy="20" r="1.3"/>
					<circle cx="17.5" cy="20" r="1.3"/>
				</svg>
				<span class="gk-cart-count" data-count="<?php echo esc_attr( (string) $cart_count ); ?>">
					<?php echo esc_html( (string) $cart_count ); ?>
				</span>
			</button>
		</div>
	</div>

	<div class="gk-header-search" id="gk-header-search" hidden>
		<div class="gk-container">
			<form role="search" method="get" class="gk-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input
					type="search"
					class="gk-search-form__input"
					name="s"
					value="<?php echo esc_attr( get_search_query() ); ?>"
					placeholder="<?php echo esc_attr( gayanes_t( 'Search products...' ) ); ?>"
					aria-label="<?php echo esc_attr( gayanes_t( 'Search products...' ) ); ?>"
				/>
				<input type="hidden" name="post_type" value="product" />
				<button type="submit" class="gk-btn gk-btn--primary gk-btn--sm">
					<?php echo esc_html( gayanes_t( 'Search' ) ); ?>
				</button>
			</form>
		</div>
	</div>
</header>

