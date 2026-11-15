<?php
/**
 * GayanesV2 Footer: Dark brand footer, iOS 27 Floating Glass Bottom Tab Bar,
 * Mini Cart Bottom-Sheet/Drawer, Mobile Navigation Drawer, and Toast region.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );
$account_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' );
$cart_count   = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
$phone        = (string) get_theme_mod( 'gv2_phone', '+374 99 00 00 00' );
?>

<footer class="gv2-footer">
	<div class="gv2-container gv2-footer__grid">
		<div class="gv2-footer__col">
			<div class="gv2-footer__logo">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gv2-logo-link" rel="home">
					<img src="<?php echo esc_url( GV2_URI . '/assets/images/logo-light.png' ); ?>" class="gv2-logo-img gv2-logo-img--footer" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="170" height="48" loading="lazy" />
				</a>
			</div>
			<p class="gv2-footer__desc">
				<?php echo esc_html( gv2_t( 'Handcrafted daily with 100% natural ingredients and delivered chilled to your door.' ) ); ?>
			</p>
			<p class="gv2-footer__contact">
				<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a> &middot; Yerevan, Armenia
			</p>
			<?php gv2_render_language_switcher(); ?>
		</div>

		<div class="gv2-footer__col">
			<h3 class="gv2-footer__heading"><?php echo esc_html( gv2_t( 'Categories' ) ); ?></h3>
			<ul class="gv2-footer__links">
				<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( gv2_t( 'Shop All' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php echo esc_html( gv2_t( 'About Us' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php echo esc_html( gv2_t( 'Blog & Recipes' ) ); ?></a></li>
			</ul>
		</div>

		<div class="gv2-footer__col">
			<h3 class="gv2-footer__heading"><?php echo esc_html( gv2_t( 'Customer Care' ) ); ?></h3>
			<ul class="gv2-footer__links">
				<li><a href="<?php echo esc_url( home_url( '/delivery/' ) ); ?>"><?php echo esc_html( gv2_t( 'Delivery & Payment' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php echo esc_html( gv2_t( 'FAQ' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php echo esc_html( gv2_t( 'Contact' ) ); ?></a></li>
			</ul>
		</div>

		<div class="gv2-footer__col">
			<h3 class="gv2-footer__heading"><?php echo esc_html( gv2_t( 'Legal' ) ); ?></h3>
			<ul class="gv2-footer__links">
				<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php echo esc_html( gv2_t( 'Privacy Policy' ) ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php echo esc_html( gv2_t( 'Terms & Conditions' ) ); ?></a></li>
			</ul>
		</div>
	</div>

	<div class="gv2-container gv2-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> (gayanes.am). All rights reserved.</p>
	</div>
</footer>

<!-- iOS 27 Floating Liquid Glass Bottom Tab Bar (Mobile) -->
<nav class="gv2-tabbar" aria-label="<?php echo esc_attr( gv2_t( 'Primary Navigation' ) ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gv2-tabbar__item<?php echo is_front_page() ? ' is-active' : ''; ?>">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5V21h-6v-6H9v6H3z" stroke-linejoin="round"/></svg>
		<span><?php echo esc_html( gv2_t( 'Home' ) ); ?></span>
	</a>
	<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-tabbar__item<?php echo ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() ) ) ? ' is-active' : ''; ?>">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>
		<span><?php echo esc_html( gv2_t( 'Shop' ) ); ?></span>
	</a>
	<button type="button" class="gv2-tabbar__item" data-gv2-toggle-search>
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
		<span><?php echo esc_html( gv2_t( 'Search' ) ); ?></span>
	</button>
	<button type="button" class="gv2-tabbar__item" data-gv2-drawer-open="cart">
		<span class="gv2-tabbar__badge-wrap">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M6 7h15l-1.5 9h-12z"/><path d="M6 7 5 3H2" stroke-linecap="round"/></svg>
			<span class="gv2-cart-count" data-count="<?php echo esc_attr( (string) $cart_count ); ?>"><?php echo esc_html( (string) $cart_count ); ?></span>
		</span>
		<span><?php echo esc_html( gv2_t( 'Cart' ) ); ?></span>
	</button>
	<a href="<?php echo esc_url( $account_url ); ?>" class="gv2-tabbar__item">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 5-6 8-6s6.5 2 8 6" stroke-linecap="round"/></svg>
		<span><?php echo esc_html( gv2_t( 'Account' ) ); ?></span>
	</a>
</nav>

<div class="gv2-backdrop" id="gv2-backdrop" hidden></div>

<!-- Mobile Navigation Glass Sheet -->
<aside class="gv2-sheet gv2-sheet--left" id="gv2-mobile-nav" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( gv2_t( 'Primary Navigation' ) ); ?>" hidden>
	<div class="gv2-sheet__handle" aria-hidden="true"></div>
	<div class="gv2-sheet__header">
		<img src="<?php echo esc_url( GV2_URI . '/assets/images/logo-dark.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="gv2-logo-img" width="130" height="36" />
		<button type="button" class="gv2-glass-btn" data-gv2-drawer-close aria-label="<?php echo esc_attr( gv2_t( 'Close menu' ) ); ?>">&times;</button>
	</div>
	<div class="gv2-sheet__body">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'gv2-mobile-nav__list',
				'fallback_cb'    => static function () use ( $shop_url ): void {
					echo '<ul class="gv2-mobile-nav__list">';
					echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( gv2_t( 'Home' ) ) . '</a></li>';
					echo '<li><a href="' . esc_url( $shop_url ) . '">' . esc_html( gv2_t( 'Shop All' ) ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/about-us/' ) ) . '">' . esc_html( gv2_t( 'About Us' ) ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/blog/' ) ) . '">' . esc_html( gv2_t( 'Blog & Recipes' ) ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/delivery/' ) ) . '">' . esc_html( gv2_t( 'Delivery & Payment' ) ) . '</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/contact/' ) ) . '">' . esc_html( gv2_t( 'Contact' ) ) . '</a></li>';
					echo '</ul>';
				},
			)
		);
		?>
		<div class="gv2-mobile-nav__lang">
			<?php gv2_render_language_switcher(); ?>
		</div>
	</div>
</aside>

<!-- iOS 27 Mini Cart Glass Drawer / Bottom Sheet -->
<aside class="gv2-sheet gv2-sheet--right" id="gv2-side-cart" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( gv2_t( 'Your Cart' ) ); ?>" hidden>
	<div class="gv2-sheet__handle" aria-hidden="true"></div>
	<div class="gv2-sheet__header">
		<h2 class="gv2-sheet__title"><?php echo esc_html( gv2_t( 'Your Cart' ) ); ?></h2>
		<button type="button" class="gv2-glass-btn" data-gv2-drawer-close aria-label="<?php echo esc_attr( gv2_t( 'Close' ) ); ?>">&times;</button>
	</div>

	<div class="gv2-delivery-progress" id="gv2-delivery-progress">
		<div class="gv2-delivery-progress__text" id="gv2-delivery-text"></div>
		<div class="gv2-delivery-progress__bar"><span id="gv2-delivery-fill" style="width:0%"></span></div>
	</div>

	<div class="gv2-sheet__body" id="gv2-mini-cart-items">
		<?php
		if ( function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
			echo '<ul class="gv2-mini-cart__list">';
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product = $cart_item['data'] ?? null;
				if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
					continue;
				}
				$product_id = (int) $cart_item['product_id'];
				?>
				<li class="gv2-mini-item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" data-product-id="<?php echo esc_attr( (string) $product_id ); ?>">
					<div class="gv2-mini-item__thumb">
						<?php echo wp_kses_post( $_product->get_image( 'gv2-product-portrait' ) ); ?>
					</div>
					<div class="gv2-mini-item__info">
						<a href="<?php echo esc_url( $_product->get_permalink() ); ?>" class="gv2-mini-item__name"><?php echo esc_html( $_product->get_name() ); ?></a>
						<div class="gv2-mini-item__price"><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ) ); ?></div>
						<div class="gv2-qty gv2-qty--sm" data-gv2-qty-control data-product-id="<?php echo esc_attr( (string) $product_id ); ?>" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
							<button type="button" class="gv2-qty__btn" data-gv2-qty-step="-1" aria-label="<?php echo esc_attr( gv2_t( 'Decrease quantity' ) ); ?>">&minus;</button>
							<span class="gv2-qty__val" data-gv2-qty-val><?php echo esc_html( (string) $cart_item['quantity'] ); ?></span>
							<button type="button" class="gv2-qty__btn" data-gv2-qty-step="1" aria-label="<?php echo esc_attr( gv2_t( 'Increase quantity' ) ); ?>">+</button>
						</div>
					</div>
					<button type="button" class="gv2-mini-item__remove" data-gv2-remove-item="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php echo esc_attr( gv2_t( 'Remove item' ) ); ?>">&times;</button>
				</li>
				<?php
			}
			echo '</ul>';
		} else {
			echo '<div class="gv2-mini-cart__empty"><p>' . esc_html( gv2_t( 'Your cart is empty' ) ) . '</p><p class="gv2-muted">' . esc_html( gv2_t( 'Add some homemade Armenian dishes to get started.' ) ) . '</p></div>';
		}
		?>
	</div>

	<div class="gv2-sheet__footer">
		<div class="gv2-mini-cart__subtotal">
			<span><?php echo esc_html( gv2_t( 'Subtotal' ) ); ?></span>
			<strong id="gv2-mini-cart-subtotal">
				<?php echo ( function_exists( 'WC' ) && WC()->cart ) ? wp_kses_post( WC()->cart->get_cart_subtotal() ) : '0 ֏'; ?>
			</strong>
		</div>
		<div class="gv2-mini-cart__actions">
			<a href="<?php echo esc_url( $checkout_url ); ?>" class="gv2-btn gv2-btn--primary gv2-btn--block">
				<?php echo esc_html( gv2_t( 'Checkout' ) ); ?>
			</a>
			<a href="<?php echo esc_url( $cart_url ); ?>" class="gv2-btn gv2-btn--glass gv2-btn--block">
				<?php echo esc_html( gv2_t( 'View Cart' ) ); ?>
			</a>
		</div>
	</div>
</aside>

<div class="gv2-toast" id="gv2-toast" role="status" aria-live="polite" hidden></div>

<?php wp_footer(); ?>
</body>
</html>

