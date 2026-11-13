<?php
/**
 * Theme Footer: Footer links, accessible Mini Cart / Side Cart drawer,
 * Mobile Navigation drawer, Fixed Bottom Navigation, and Toast region.
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );
$account_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
$cart_count   = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
?>

<footer class="gk-footer">
	<div class="gk-container gk-footer__grid">
		<div class="gk-footer__col">
			<h2 class="gk-footer__brand"><?php bloginfo( 'name' ); ?></h2>
			<p class="gk-footer__desc">
				<?php echo esc_html( gayanes_t( 'Fresh ingredients, traditional recipes, delivered to your door.' ) ); ?>
			</p>
			<?php gayanes_render_language_switcher(); ?>
		</div>

		<div class="gk-footer__col">
			<h3 class="gk-footer__heading"><?php echo esc_html( gayanes_t( 'Categories' ) ); ?></h3>
			<ul class="gk-footer__links">
				<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( gayanes_t( 'Shop All' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php echo esc_html( gayanes_t( 'About Us' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php echo esc_html( gayanes_t( 'Blog & Recipes' ) ); ?></a></li>
			</ul>
		</div>

		<div class="gk-footer__col">
			<h3 class="gk-footer__heading"><?php echo esc_html( gayanes_t( 'Customer Care' ) ); ?></h3>
			<ul class="gk-footer__links">
				<li><a href="<?php echo esc_url( home_url( '/delivery/' ) ); ?>"><?php echo esc_html( gayanes_t( 'Delivery & Payment' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php echo esc_html( gayanes_t( 'FAQ' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php echo esc_html( gayanes_t( 'Contact' ) ); ?></a></li>
			</ul>
		</div>

		<div class="gk-footer__col">
			<h3 class="gk-footer__heading"><?php echo esc_html( gayanes_t( 'Legal' ) ); ?></h3>
			<ul class="gk-footer__links">
				<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php echo esc_html( gayanes_t( 'Privacy Policy' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php echo esc_html( gayanes_t( 'Terms & Conditions' ) ); ?></a></li>
			</ul>
		</div>
	</div>

	<div class="gk-container gk-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.</p>
	</div>
</footer>

<!-- Fixed Mobile Bottom Navigation -->
<nav class="gk-bottom-nav" aria-label="<?php echo esc_attr( gayanes_t( 'Mobile quick navigation' ) ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gk-bottom-nav__item">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5V21h-6v-6H9v6H3z"/></svg>
		<span><?php echo esc_html( gayanes_t( 'Home' ) ); ?></span>
	</a>
	<a href="<?php echo esc_url( $shop_url ); ?>" class="gk-bottom-nav__item">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
		<span><?php echo esc_html( gayanes_t( 'Categories' ) ); ?></span>
	</a>
	<button type="button" class="gk-bottom-nav__item" data-gk-toggle-search>
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
		<span><?php echo esc_html( gayanes_t( 'Search' ) ); ?></span>
	</button>
	<button type="button" class="gk-bottom-nav__item" data-gk-drawer-open="cart">
		<span class="gk-bottom-nav__badge-wrap">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 7h15l-1.5 9h-12z"/><path d="M6 7 5 3H2" stroke-linecap="round"/></svg>
			<span class="gk-cart-count" data-count="<?php echo esc_attr( (string) $cart_count ); ?>"><?php echo esc_html( (string) $cart_count ); ?></span>
		</span>
		<span><?php echo esc_html( gayanes_t( 'Your Cart' ) ); ?></span>
	</button>
	<a href="<?php echo esc_url( $account_url ); ?>" class="gk-bottom-nav__item">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 5-6 8-6s6.5 2 8 6" stroke-linecap="round"/></svg>
		<span><?php echo esc_html( gayanes_t( 'Account' ) ); ?></span>
	</a>
</nav>

<!-- Mobile Navigation Drawer -->
<div class="gk-drawer-backdrop" id="gk-drawer-backdrop" hidden></div>
<aside
	class="gk-drawer gk-drawer--left"
	id="gk-mobile-nav"
	role="dialog"
	aria-modal="true"
	aria-label="<?php echo esc_attr( gayanes_t( 'Navigation Menu' ) ); ?>"
	hidden
>
	<div class="gk-drawer__header">
		<span class="gk-drawer__title"><?php bloginfo( 'name' ); ?></span>
		<button type="button" class="gk-icon-btn" data-gk-drawer-close aria-label="<?php echo esc_attr( gayanes_t( 'Close menu' ) ); ?>">&times;</button>
	</div>
	<div class="gk-drawer__body">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'gk-mobile-nav__list',
				'fallback_cb'    => false,
			)
		);
		?>
		<div class="gk-mobile-nav__lang">
			<?php gayanes_render_language_switcher(); ?>
		</div>
	</div>
</aside>

<!-- Accessible Mini Cart / Side Cart Drawer -->
<aside
	class="gk-drawer gk-drawer--right"
	id="gk-side-cart"
	role="dialog"
	aria-modal="true"
	aria-labelledby="gk-side-cart-title"
	hidden
>
	<div class="gk-drawer__header">
		<h2 class="gk-drawer__title" id="gk-side-cart-title">
			<?php echo esc_html( gayanes_t( 'Your Cart' ) ); ?>
			<span class="gk-cart-count" data-count="<?php echo esc_attr( (string) $cart_count ); ?>"><?php echo esc_html( (string) $cart_count ); ?></span>
		</h2>
		<button type="button" class="gk-icon-btn" data-gk-drawer-close aria-label="<?php echo esc_attr( gayanes_t( 'Close cart' ) ); ?>">&times;</button>
	</div>

	<div class="gk-drawer__body" id="gk-mini-cart-items">
		<!-- Hydrated dynamically by cart.js from WooCommerce state -->
	</div>

	<div class="gk-drawer__footer" id="gk-mini-cart-footer">
		<div class="gk-mini-cart__totals">
			<div class="gk-mini-cart__row">
				<span><?php echo esc_html( gayanes_t( 'Subtotal' ) ); ?></span>
				<strong id="gk-mini-cart-subtotal">&mdash;</strong>
			</div>
			<div class="gk-mini-cart__row gk-mini-cart__row--total">
				<span><?php echo esc_html( gayanes_t( 'Total' ) ); ?></span>
				<strong id="gk-mini-cart-total">&mdash;</strong>
			</div>
		</div>
		<div class="gk-mini-cart__actions">
			<a href="<?php echo esc_url( $cart_url ); ?>" class="gk-btn gk-btn--outline gk-btn--full">
				<?php echo esc_html( gayanes_t( 'View Cart' ) ); ?>
			</a>
			<a href="<?php echo esc_url( $checkout_url ); ?>" class="gk-btn gk-btn--primary gk-btn--full">
				<?php echo esc_html( gayanes_t( 'Checkout' ) ); ?>
			</a>
		</div>
	</div>
</aside>

<!-- Live Toast Notification -->
<div class="gk-toast" id="gk-toast" role="status" aria-live="polite" hidden></div>

<?php wp_footer(); ?>
</body>
</html>

