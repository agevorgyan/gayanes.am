<?php
/**
 * Enqueue styles, fonts, deferred scripts, resource hints, and favicon assets.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output resource hints (preconnect) and LCP image preload in <head>.
 */
function gv2_output_resource_hints(): void {
	$lang = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';
	if ( 'ru' === $lang ) {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com" />' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />' . "\n";
	} else {
		// Preload primary self-hosted Armenian/Latin font weights to break CSS->font waterfall.
		$font_base = GV2_URI . '/assets/fonts/';
		echo '<link rel="preload" as="font" type="font/otf" href="' . esc_url( $font_base . 'GAA_Gayane_Reg.otf' ) . '" crossorigin />' . "\n";
		echo '<link rel="preload" as="font" type="font/otf" href="' . esc_url( $font_base . 'GAAGayane-Bold.otf' ) . '" crossorigin />' . "\n";
	}

	if ( is_front_page() && function_exists( 'wc_get_product' ) ) {
		$hero_img_id = absint( get_theme_mod( 'gv2_home_hero_image_id', 0 ) );
		if ( $hero_img_id > 0 ) {
			$src    = wp_get_attachment_image_url( $hero_img_id, 'large' );
			$srcset = wp_get_attachment_image_srcset( $hero_img_id, 'large' );
			if ( $src ) {
				$preload = '<link rel="preload" as="image" href="' . esc_url( $src ) . '" fetchpriority="high"';
				if ( $srcset ) {
					$preload .= ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="(max-width: 767px) 340px, 440px"';
				}
				$preload .= ' />' . "\n";
				echo $preload; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		} else {
			$deal_id = absint( get_theme_mod( 'gv2_daily_deal_product_id', 0 ) );
			if ( $deal_id > 0 && function_exists( 'pll_get_post' ) ) {
				$tr = (int) pll_get_post( $deal_id );
				if ( $tr > 0 ) {
					$deal_id = $tr;
				}
			}
			$deal_product = $deal_id > 0 ? wc_get_product( $deal_id ) : null;
			if ( ! $deal_product instanceof WC_Product && function_exists( 'wc_get_product_ids_on_sale' ) ) {
				$sale_ids = wc_get_product_ids_on_sale();
				if ( ! empty( $sale_ids ) ) {
					$deal_product = wc_get_product( (int) reset( $sale_ids ) );
				}
			}
			if ( $deal_product instanceof WC_Product && $deal_product->get_image_id() ) {
				$deal_img_id = (int) $deal_product->get_image_id();
				$src         = wp_get_attachment_image_url( $deal_img_id, 'gv2-product-portrait' );
				$srcset      = wp_get_attachment_image_srcset( $deal_img_id, 'gv2-product-portrait' );
				if ( $src ) {
					$preload = '<link rel="preload" as="image" href="' . esc_url( $src ) . '" fetchpriority="high"';
					if ( $srcset ) {
						$preload .= ' imagesrcset="' . esc_attr( $srcset ) . '" imagesizes="(max-width: 767px) 92vw, 380px"';
					}
					$preload .= ' />' . "\n";
					echo $preload; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
		}
	}
}
add_action( 'wp_head', 'gv2_output_resource_hints', 1 );

/**
 * Inline critical above-the-fold CSS so main.min.css can load asynchronously without FOUC or CLS.
 */
function gv2_output_critical_css(): void {
	if ( is_admin() ) {
		return;
	}
	$font_base = esc_url( GV2_URI . '/assets/fonts/' );
	?>
	<style id="gv2-critical-css">
	@font-face{font-family:'GAA Gayane';src:url('<?php echo $font_base; ?>GAA_Gayane_Reg.otf') format('opentype');font-weight:400;font-style:normal;font-display:swap;unicode-range:U+0020-058E,U+0590-FFFF}
	@font-face{font-family:'GAA Gayane';src:url('<?php echo $font_base; ?>GAAGayane-SemiBold.otf') format('opentype');font-weight:600;font-style:normal;font-display:swap;unicode-range:U+0020-058E,U+0590-FFFF}
	@font-face{font-family:'GAA Gayane';src:url('<?php echo $font_base; ?>GAAGayane-Bold.otf') format('opentype');font-weight:700;font-style:normal;font-display:swap;unicode-range:U+0020-058E,U+0590-FFFF}
	:root{--gv2-bg:#F7F6F1;--gv2-surface:#FFFFFF;--gv2-glass:rgba(255,255,255,0.82);--gv2-glass-border:rgba(255,255,255,0.65);--gv2-text:#242923;--gv2-muted:#5C625A;--gv2-primary:#304B39;--gv2-primary-hover:#223729;--gv2-terracotta:#A35030;--gv2-sand:#EAE6DC;--gv2-border:rgba(36,41,35,0.09);--gv2-radius:22px;--gv2-radius-pill:999px;--gv2-spring:cubic-bezier(0.22,1,0.36,1);--gv2-font-serif:'GAA Gayane','Cormorant Garamond',Georgia,serif;--gv2-font-sans:'GAA Gayane','Inter','Noto Sans Armenian',-apple-system,BlinkMacSystemFont,sans-serif;--gv2-container:1240px}
	html[lang^="ru"]{--gv2-font-serif:'Cormorant Garamond',Georgia,serif;--gv2-font-sans:'Inter',-apple-system,BlinkMacSystemFont,sans-serif}
	*,*::before,*::after{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
	[hidden],.is-hidden{display:none!important}
	.gv2-sr-only,.gv2-skip-link{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
	html,body{margin:0;padding:0;background:var(--gv2-bg);color:var(--gv2-text);font-family:var(--gv2-font-sans);font-size:15px;line-height:1.55;-webkit-font-smoothing:antialiased;overflow-x:clip}
	a{color:var(--gv2-primary);text-decoration:none}
	img{max-width:100%;height:auto;display:block}
	.gv2-container{width:100%;max-width:var(--gv2-container);margin-inline:auto;padding-inline:16px}
	.gv2-announcement{background:var(--gv2-primary);color:#fff;text-align:center;font-size:12px;font-weight:500;padding:7px 12px}
	.gv2-header{position:sticky;top:0;z-index:900;background:rgba(247,246,241,0.88);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);border-bottom:1px solid var(--gv2-border)}
	.gv2-header__inner{position:relative;display:flex;align-items:center;justify-content:space-between;gap:10px;height:66px}
	.gv2-header__brand{position:absolute;left:50%;transform:translateX(-50%);display:flex;align-items:center;justify-content:center}
	.gv2-logo-img{height:44px!important;max-height:44px!important;width:auto!important;object-fit:contain}
	.gv2-nav,.gv2-search-toggle,.gv2-header__lang-desktop,.gv2-header__account,.gv2-cart-pill__label{display:none}
	.gv2-header__actions{display:flex;align-items:center;gap:8px}
	.gv2-glass-btn{width:40px;height:40px;border-radius:50%;border:1px solid var(--gv2-border);background:var(--gv2-glass);color:var(--gv2-text);display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
	.gv2-cart-pill{display:inline-flex;align-items:center;gap:7px;height:40px;padding:0 14px;border-radius:var(--gv2-radius-pill);border:0;background:var(--gv2-primary);color:#fff;font-weight:600;font-size:13px;cursor:pointer}
	.gv2-cart-count{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:var(--gv2-radius-pill);background:rgba(255,255,255,0.22);font-size:12px;font-weight:700}
	.gv2-hero{padding:20px 0 28px}
	.gv2-hero__grid{display:grid;grid-template-columns:1fr;gap:22px;align-items:center}
	.gv2-hero__arch{width:100%;max-width:340px;margin-inline:auto;aspect-ratio:4/4.3;border-radius:170px 170px 28px 28px;overflow:hidden;background:var(--gv2-sand);box-shadow:0 18px 40px rgba(36,41,35,0.10);border:4px solid rgba(255,255,255,0.8)}
	.gv2-hero__img{width:100%;height:100%;object-fit:cover}
	.gv2-hero__content{text-align:center}
	.gv2-eyebrow{display:inline-block;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--gv2-terracotta);margin-bottom:8px}
	.gv2-hero__title{font-family:var(--gv2-font-serif);font-size:clamp(30px,7vw,52px);line-height:1.08;margin:0 0 12px;font-weight:700;color:var(--gv2-text)}
	.gv2-hero__subtitle{color:var(--gv2-muted);margin:0 auto 20px;max-width:48ch}
	.gv2-hero__cta{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;margin-bottom:18px}
	.gv2-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 20px;border-radius:var(--gv2-radius-pill);font-weight:600;font-size:14px;border:1px solid transparent;cursor:pointer;line-height:1.2}
	.gv2-btn--lg{padding:14px 26px;font-size:15px}
	.gv2-btn--primary{background:var(--gv2-primary);color:#fff}
	.gv2-btn--glass{background:var(--gv2-glass);border-color:var(--gv2-glass-border);color:var(--gv2-text)}
	.gv2-trust-row{display:flex;flex-wrap:wrap;justify-content:center;gap:8px}
	.gv2-trust-pill{font-size:12px;font-weight:500;padding:6px 12px;border-radius:var(--gv2-radius-pill);background:var(--gv2-glass);border:1px solid var(--gv2-glass-border);color:var(--gv2-text)}
	@media(min-width:900px){.gv2-tabbar,.gv2-header__menu-btn{display:none}.gv2-nav{display:block}.gv2-nav__list{list-style:none;margin:0;padding:0;display:flex;gap:22px;font-weight:600;font-size:14px}.gv2-header__lang-desktop,.gv2-header__account,.gv2-cart-pill__label{display:inline-flex}.gv2-hero{padding:42px 0 36px}.gv2-hero__grid{grid-template-columns:1.05fr 0.95fr;gap:48px}.gv2-hero__media{order:2}.gv2-hero__content{order:1;text-align:left}.gv2-hero__cta,.gv2-trust-row{justify-content:flex-start}.gv2-hero__arch{max-width:440px;border-radius:220px 220px 28px 28px}}
	</style>
	<?php
}
add_action( 'wp_head', 'gv2_output_critical_css', 5 );

/**
 * Enqueue GayanesV2 CSS and JS cleanly with minified assets when available.
 */
function gv2_enqueue_assets(): void {
	$lang = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';
	if ( 'ru' === $lang ) {
		wp_enqueue_style(
			'gv2-fonts',
			'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&display=swap',
			array(),
			null
		);
	}

	// Dequeue unused WooCommerce stylesheets on non-commerce pages (homepage, blog, info pages).
	$is_wc_page = ( function_exists( 'is_woocommerce' ) && is_woocommerce() )
		|| ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_account_page' ) && is_account_page() );

	if ( ! $is_wc_page ) {
		wp_dequeue_style( 'woocommerce-layout' );
		wp_dequeue_style( 'woocommerce-smallscreen' );
		wp_dequeue_style( 'woocommerce-general' );
		wp_dequeue_style( 'brands-styles' );
		wp_dequeue_style( 'wc-blocks-style' );
	}

	$css_rel = file_exists( GV2_DIR . '/assets/css/main.min.css' ) ? '/assets/css/main.min.css' : '/assets/css/main.css';
	wp_enqueue_style(
		'gv2-main',
		GV2_URI . $css_rel,
		array(),
		GV2_VERSION
	);

	$js_rel = file_exists( GV2_DIR . '/assets/js/app.min.js' ) ? '/assets/js/app.min.js' : '/assets/js/app.js';
	wp_enqueue_script(
		'gv2-app',
		GV2_URI . $js_rel,
		array(),
		GV2_VERSION,
		true
	);

	$nonce = wp_create_nonce( 'gayanes_cart_nonce' );

	wp_localize_script(
		'gv2-app',
		'gv2Config',
		array(
			'ajaxUrl'               => add_query_arg( 'lang', gv2_current_lang(), admin_url( 'admin-ajax.php' ) ),
			'nonce'                 => $nonce,
			'lang'                  => gv2_current_lang(),
			'freeDeliveryThreshold' => function_exists( 'gv2_get_free_shipping_threshold' ) ? gv2_get_free_shipping_threshold() : 15000,
			'currencySymbol'        => '֏',
			'i18n'                  => array(
				'add'            => gv2_t( 'Add' ),
				'addedToCart'    => gv2_t( 'Added to cart' ),
				'updatedQty'     => gv2_t( 'Updated quantity' ),
				'errorCart'      => gv2_t( 'Could not update cart' ),
				'freeUnlocked'   => gv2_t( 'Free delivery unlocked!' ),
				'addMoreForFree' => gv2_t( 'Add %s more for free delivery' ),
				'loadingMore'    => gv2_t( 'Loading more dishes...' ),
				'allLoaded'      => gv2_t( 'All dishes loaded' ),
				'decreaseQty'    => gv2_t( 'Decrease quantity' ),
				'increaseQty'    => gv2_t( 'Increase quantity' ),
				'removeItem'     => gv2_t( 'Remove item' ),
				'emptyCart'      => gv2_t( 'Your cart is empty' ),
				'phoneInvalid'   => gv2_t( 'Please enter a valid phone number (e.g. +374 99 12 34 56).' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'gv2_enqueue_assets' );

/**
 * Load Google Fonts and non-critical WooCommerce stylesheets asynchronously.
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Stylesheet handle.
 */
function gv2_async_noncritical_styles( string $html, string $handle ): string {
	if ( is_admin() ) {
		return $html;
	}

	$async_handles = array( 'gv2-fonts', 'gv2-main' );

	$is_commerce_flow = ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_account_page' ) && is_account_page() )
		|| ( function_exists( 'is_product' ) && is_product() );

	if ( ! $is_commerce_flow ) {
		$async_handles = array_merge(
			$async_handles,
			array(
				'woocommerce-layout',
				'woocommerce-smallscreen',
				'woocommerce-general',
				'brands-styles',
				'wc-blocks-style',
			)
		);
	}

	if ( in_array( $handle, $async_handles, true ) && preg_match( "/media=(['\"])([^'\"]+)\\1/", $html, $m ) ) {
		$orig_media = $m[2];
		$async      = str_replace(
			$m[0],
			"media='print' onload=\"this.media='" . esc_attr( $orig_media ) . "'\"",
			$html
		);
		return $async . '<noscript>' . $html . '</noscript>';
	}

	return $html;
}
add_filter( 'style_loader_tag', 'gv2_async_noncritical_styles', 10, 2 );

/**
 * Add defer attribute to theme script and non-checkout WooCommerce/jQuery scripts.
 *
 * @param string $tag    Script HTML tag.
 * @param string $handle Script handle.
 */
function gv2_defer_scripts( string $tag, string $handle ): string {
	if ( is_admin() ) {
		return $tag;
	}

	$defer_handles = array(
		'gv2-app',
	);

	// Delay heavy analytics scripts until first user interaction or idle after load.
	$delay_handles = array(
		'google_gtagjs',
		'googlesitekit-events-provider-content-events',
		'googlesitekit-events-provider-woocommerce',
	);
	if ( in_array( $handle, $delay_handles, true ) && preg_match( "/\ssrc=(['\"])([^'\"]+)\\1/", $tag, $m ) ) {
		$src = $m[2];
		$tag = str_replace( $m[0], ' data-gv2-delay-src="' . esc_url( $src ) . '"', $tag );
		return str_replace( ' defer', '', $tag );
	}

	$is_commerce_flow = ( function_exists( 'is_cart' ) && is_cart() )
		|| ( function_exists( 'is_checkout' ) && is_checkout() )
		|| ( function_exists( 'is_account_page' ) && is_account_page() );

	if ( ! $is_commerce_flow ) {
		$defer_handles = array_merge(
			$defer_handles,
			array(
				'jquery-core',
				'jquery-migrate',
				'wc-jquery-blockui',
				'js-cookie',
				'wc-js-cookie',
				'woocommerce',
				'wc-cart-fragments',
				'sourcebuster-js',
				'wc-order-attribution',
				'wc-add-to-cart',
			)
		);
	}

	if ( in_array( $handle, $defer_handles, true ) && ! str_contains( $tag, ' defer' ) ) {
		return str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'gv2_defer_scripts', 10, 2 );

/**
 * Output Gayane's Kitchen custom Favicon and Apple Touch Icon.
 */
function gv2_output_favicons(): void {
	$favicon_url = esc_url( GV2_URI . '/assets/images/favicon.png' );
	echo '<link rel="icon" type="image/png" sizes="512x512" href="' . $favicon_url . '" />' . "\n";
	echo '<link rel="apple-touch-icon" sizes="180x180" href="' . $favicon_url . '" />' . "\n";
	echo '<meta name="theme-color" content="#304B39" />' . "\n";
}
add_action( 'wp_head', 'gv2_output_favicons', 2 );

/**
 * Load delayed analytics scripts on first user interaction or 4s after window load.
 */
function gv2_output_delayed_scripts_loader(): void {
	if ( is_admin() ) {
		return;
	}
	?>
	<script>
	(function(){
		var loaded = false;
		function loadDelayed(){
			if(loaded) return;
			loaded = true;
			var nodes = document.querySelectorAll('script[data-gv2-delay-src]');
			for(var i=0;i<nodes.length;i++){
				var s = document.createElement('script');
				s.src = nodes[i].getAttribute('data-gv2-delay-src');
				s.async = true;
				document.head.appendChild(s);
			}
		}
		['pointerdown','keydown','scroll','touchstart'].forEach(function(ev){
			window.addEventListener(ev, loadDelayed, {once:true, passive:true});
		});
		window.addEventListener('load', function(){ setTimeout(loadDelayed, 4000); }, {once:true});
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'gv2_output_delayed_scripts_loader', 99 );
