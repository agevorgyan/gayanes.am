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
		<div class="gv2-footer__brand-col">
			<div class="gv2-footer__logo">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gv2-logo-link" rel="home">
					<img src="<?php echo esc_url( GV2_URI . '/assets/images/logo-light.png' ); ?>" class="gv2-logo-img gv2-logo-img--footer" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="170" height="48" loading="lazy" />
				</a>
			</div>
			<p class="gv2-footer__desc">
				<?php echo esc_html( gv2_t( 'Handcrafted daily with 100% natural ingredients and delivered chilled to your door.' ) ); ?>
			</p>
			<div class="gv2-footer__meta">
				<a class="gv2-footer__chip gv2-footer__chip--lg" href="tel:+37455600655">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.58 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
					<span>+374 (55) 600655</span>
				</a>
				<a class="gv2-footer__chip gv2-footer__chip--lg" href="tel:+37411600111">
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.58 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
					<span>+374 (11) 600111</span>
				</a>
			</div>

			<a class="gv2-footer__address" href="https://yandex.com/maps/-/CXSIyGZo" target="_blank" rel="noopener noreferrer">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
				<span>ք․ Երևան, 0054, Դավթաշեն 2թղմ 37</span>
			</a>

			<div class="gv2-footer__socials" aria-label="Social Media & Messengers">
				<a class="gv2-social-btn" href="https://www.facebook.com/gayanes.kitchen" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14.5 13.5h2.5l1-4h-3.5v-2c0-1.03.5-2 2-2h1.5V2.14C17.674 2.097 16.443 2 15.143 2 12.428 2 10.5 3.657 10.5 6.7V9.5H7.5v4h3V22h4v-8.5Z"/></svg>
				</a>
				<a class="gv2-social-btn" href="https://www.instagram.com/gayanes_kitchen" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2Zm-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6Zm9.65 1.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/></svg>
				</a>
				<a class="gv2-social-btn" href="https://wa.me/37455600655" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91C21.95 6.45 17.5 2 12.04 2Zm5.76 14.08c-.24.68-1.4 1.3-1.94 1.38-.51.08-1.16.11-1.87-.12-.43-.14-.98-.32-1.69-.63-2.98-1.28-4.92-4.29-5.07-4.49-.15-.2-1.21-1.61-1.21-3.07 0-1.46.77-2.18 1.04-2.48.27-.3.6-.37.8-.37.2 0 .4 0 .57.01.18.01.43-.07.67.51.24.59.83 2.02.9 2.17.07.15.12.33.02.53-.1.2-.15.33-.3.5-.15.17-.31.38-.45.51-.15.15-.3.31-.13.61.17.3.76 1.25 1.63 2.02 1.12 1 2.06 1.31 2.36 1.46.3.15.48.12.66-.07.18-.2.76-.88.96-1.18.2-.3.4-.25.67-.15.27.1 1.72.81 2.01.96.3.15.5.22.57.34.07.12.07.7-.17 1.38Z"/></svg>
				</a>
				<a class="gv2-social-btn" href="https://t.me/+37455600655" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.03-1.99 1.27-5.62 3.72-.53.36-1.01.54-1.44.53-.47-.01-1.38-.27-2.05-.49-.83-.27-1.49-.42-1.43-.88.03-.24.37-.49 1.02-.75 3.99-1.74 6.65-2.89 7.99-3.44 3.8-1.58 4.59-1.85 5.1-1.86.11 0 .37.03.54.17.14.12.18.28.2.45-.01.07.01.24 0 .37Z"/></svg>
				</a>
				<a class="gv2-social-btn" href="viber://chat?number=+37455600655" aria-label="Viber">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.8 2C6.4 2 2.5 5.6 2.5 10.6c0 2.6 1 4.9 2.8 6.4V21l3.6-2c.9.2 1.9.4 2.9.4 5.4 0 9.7-3.8 9.7-8.8S17.2 2 11.8 2Zm4.6 12.1c-.2.5-1.1 1-1.6 1.1-.4.1-.9.1-1.5-.1-.4-.1-.9-.3-1.5-.6-2.5-1.1-4.1-3.6-4.2-3.8-.1-.2-1-1.3-1-2.5s.6-1.8.8-2c.2-.2.5-.3.7-.3h.5c.2 0 .4 0 .5.4.2.5.7 1.6.7 1.7.1.1.1.3 0 .4-.1.1-.1.2-.2.4l-.4.4c-.1.1-.3.3-.1.5.2.3.7 1.1 1.4 1.8.9.8 1.7 1.1 2 1.2.3.1.4.1.5-.1.2-.2.6-.7.8-.9.2-.2.3-.2.5-.1.2.1 1.4.7 1.6.8.3.1.4.2.5.3.1.2.1.6-.1 1.1ZM12.3 5.2c2.5.2 4.3 2 4.5 4.5h-1.1c-.2-1.9-1.5-3.2-3.4-3.4V5.2Zm0 2c1.4.2 2.3 1.1 2.5 2.5h-1.1c-.1-.8-.6-1.3-1.4-1.4V7.2Z"/></svg>
				</a>
			</div>
			<div class="gv2-footer__lang">
				<?php gv2_render_language_switcher(); ?>
			</div>
		</div>

		<div class="gv2-footer__cols">
			<div class="gv2-footer__col">
				<h3 class="gv2-footer__heading"><?php echo esc_html( gv2_t( 'Categories' ) ); ?></h3>
				<ul class="gv2-footer__links">
					<li><a href="<?php echo esc_url( $shop_url ); ?>"><?php echo esc_html( gv2_t( 'Shop All' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php echo esc_html( gv2_t( 'About Us' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/corporate-clients/' ) ); ?>">Կորպորատիվ հաճախորդներ</a></li>
					<li><a href="<?php echo esc_url( home_url( '/career/' ) ); ?>">Կարիերա</a></li>
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

			<div class="gv2-footer__col gv2-footer__col--legal">
				<h3 class="gv2-footer__heading"><?php echo esc_html( gv2_t( 'Legal' ) ); ?></h3>
				<ul class="gv2-footer__links">
					<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php echo esc_html( gv2_t( 'Privacy Policy' ) ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php echo esc_html( gv2_t( 'Terms & Conditions' ) ); ?></a></li>
				</ul>
			</div>
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
	<div class="gv2-sheet__body gv2-mobile-nav__body">
		<div class="gv2-mobile-nav__lang">
			<?php gv2_render_language_switcher(); ?>
		</div>

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

		<div class="gv2-mobile-nav__quick">
			<a href="<?php echo esc_url( $account_url ); ?>" class="gv2-btn gv2-btn--glass gv2-btn--block">
				<?php echo esc_html( gv2_t( 'My Account' ) ); ?>
			</a>
			<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="gv2-btn gv2-btn--primary gv2-btn--block">
				<?php echo esc_html( $phone ); ?>
			</a>
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

