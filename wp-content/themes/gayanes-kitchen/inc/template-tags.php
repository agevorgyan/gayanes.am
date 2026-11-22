<?php
/**
 * Template helper tags: breadcrumbs and Partners showcase data.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render accessible breadcrumb trail.
 */
function gv2_breadcrumbs(): void {
	if ( is_front_page() ) {
		return;
	}

	if ( function_exists( 'woocommerce_breadcrumb' ) ) {
		woocommerce_breadcrumb(
			array(
				'delimiter'   => '<span class="gv2-bc-sep" aria-hidden="true">/</span>',
				'wrap_before' => '<nav class="gv2-breadcrumbs" aria-label="' . esc_attr( gv2_t( 'Breadcrumb' ) ) . '">',
				'wrap_after'  => '</nav>',
				'before'      => '<span>',
				'after'       => '</span>',
				'home'        => gv2_t( 'Home' ),
			)
		);
	}
}

/**
 * Get partners list from Customizer or clean default Armenian partner showcase.
 *
 * @return array<int, array{name: string, subtitle: string, url: string}>
 */
function gv2_get_partners(): array {
	$raw = trim( (string) get_theme_mod( 'gv2_partners_json', '' ) );
	if ( '' !== $raw ) {
		$partners = array();
		$lines    = preg_split( '/\r\n|\r|\n/', $raw );
		if ( is_array( $lines ) ) {
			foreach ( $lines as $line ) {
				$parts = array_map( 'trim', explode( '|', $line ) );
				if ( '' !== ( $parts[0] ?? '' ) ) {
					$logo = ( isset( $parts[1] ) && str_starts_with( $parts[1], 'http' ) ) ? $parts[1] : '';
					$url  = $parts[2] ?? ( '' === $logo ? '#' : ( $parts[1] ?? '#' ) );
					$partners[] = array(
						'name' => $parts[0],
						'logo' => $logo,
						'url'  => $url ?: '#',
					);
				}
			}
		}
		if ( ! empty( $partners ) ) {
			return $partners;
		}
	}

	$base = GV2_URI . '/assets/images/partners/';

	return array(
		array( 'name' => 'IDBank', 'logo' => $base . 'idbank.png', 'url' => 'https://idbank.am', 'dark' => false ),
		array( 'name' => 'Idram', 'logo' => $base . 'idram.png', 'url' => 'https://idram.am', 'dark' => false ),
		array( 'name' => 'Telcell Wallet', 'logo' => $base . 'telcell-wallet.png', 'url' => 'https://telcell.am', 'dark' => false ),
		array( 'name' => 'FastShift', 'logo' => $base . 'fastshift.png', 'url' => 'https://fastshift.am', 'dark' => false ),
		array( 'name' => 'Ameriabank', 'logo' => $base . 'ameriabank.png', 'url' => 'https://ameriabank.am', 'dark' => false ),
		array( 'name' => 'Սպիտակի թռչնաֆաբրիկա', 'logo' => $base . 'spitak.jpg', 'url' => '#', 'dark' => false ),
		array( 'name' => 'Aqlor.am', 'logo' => $base . 'aqlor.jpg', 'url' => 'https://aqlor.am', 'dark' => false ),
		array( 'name' => 'E-Lab Digital Studio', 'logo' => $base . 'elab-digital.png', 'url' => '#', 'dark' => true ),
	);
}

/**
 * Render iOS 27 Liquid Glass step header & live free delivery bar for Cart / Checkout.
 *
 * @param string $stage Current stage: 'cart', 'checkout', or 'complete'.
 */
function gv2_render_commerce_flow_header( string $stage ): void {
	$cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
	$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );
	$threshold    = function_exists( 'gv2_get_free_shipping_threshold' ) ? gv2_get_free_shipping_threshold() : 15000.0;
	$subtotal     = ( function_exists( 'WC' ) && WC()->cart ) ? (float) WC()->cart->get_subtotal() : 0.0;
	$pct          = $threshold > 0 ? (int) min( 100, round( ( $subtotal / $threshold ) * 100 ) ) : 100;
	$remaining    = max( 0, (int) round( $threshold - $subtotal ) );
	$is_complete  = ( 'complete' === $stage );
	$is_checkout  = ( 'checkout' === $stage );
	?>
	<section class="gv2-flow-hero">
		<nav class="gv2-about-breadcrumb" aria-label="Breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( gv2_t( 'Home' ) ); ?></a>
			<span aria-hidden="true">/</span>
			<?php if ( $is_checkout || $is_complete ) : ?>
				<a href="<?php echo esc_url( $cart_url ); ?>"><?php echo esc_html( gv2_t( 'Cart' ) ); ?></a>
				<span aria-hidden="true">/</span>
			<?php endif; ?>
			<span aria-current="page"><?php echo esc_html( get_the_title() ); ?></span>
		</nav>

		<div class="gv2-flow-head">
			<h1 class="gv2-flow-head__title"><?php the_title(); ?></h1>

			<ol class="gv2-flow-steps" aria-label="<?php echo esc_attr( gv2_t( 'Order progress' ) ); ?>">
				<li class="gv2-flow-step <?php echo 'cart' === $stage ? 'is-active' : 'is-done'; ?>">
					<a href="<?php echo esc_url( $cart_url ); ?>" class="gv2-flow-step__link">
						<span class="gv2-flow-step__num">01</span>
						<span class="gv2-flow-step__label"><?php echo esc_html( gv2_t( 'Cart' ) ); ?></span>
					</a>
				</li>
				<li class="gv2-flow-step <?php echo $is_checkout ? 'is-active' : ( $is_complete ? 'is-done' : '' ); ?>">
					<a href="<?php echo esc_url( $checkout_url ); ?>" class="gv2-flow-step__link">
						<span class="gv2-flow-step__num">02</span>
						<span class="gv2-flow-step__label"><?php echo esc_html( gv2_t( 'Checkout' ) ); ?></span>
					</a>
				</li>
				<li class="gv2-flow-step <?php echo $is_complete ? 'is-active is-done' : ''; ?>">
					<span class="gv2-flow-step__link">
						<span class="gv2-flow-step__num">03</span>
						<span class="gv2-flow-step__label"><?php echo esc_html( gv2_t( 'Confirmation' ) ); ?></span>
					</span>
				</li>
			</ol>
		</div>

		<?php if ( ! $is_complete && $subtotal > 0 ) : ?>
			<div class="gv2-flow-delivery gv2-glass-card">
				<div class="gv2-flow-delivery__row">
					<span class="gv2-flow-delivery__text">
						<?php if ( $remaining <= 0 ) : ?>
							<?php echo esc_html( gv2_t( 'Free delivery in Yerevan is unlocked' ) ); ?>
						<?php else : ?>
							<?php
							echo wp_kses_post(
								sprintf(
									esc_html( gv2_t( 'Add %s more for free delivery in Yerevan' ) ),
									'<strong>' . esc_html( number_format( $remaining ) ) . ' ֏</strong>'
								)
							);
							?>
						<?php endif; ?>
					</span>
					<span class="gv2-flow-delivery__pct"><?php echo esc_html( (string) $pct ); ?>%</span>
				</div>
				<div class="gv2-flow-delivery__track" role="progressbar" aria-valuenow="<?php echo esc_attr( (string) $pct ); ?>" aria-valuemin="0" aria-valuemax="100">
					<div class="gv2-flow-delivery__fill" style="width: <?php echo esc_attr( (string) $pct ); ?>%;"></div>
				</div>
			</div>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Render trust guarantees strip below Cart and Checkout.
 */
function gv2_render_commerce_trust_bar(): void {
	?>
	<section class="gv2-flow-trust" aria-label="<?php echo esc_attr( gv2_t( "Gayane's Kitchen guarantees" ) ); ?>">
		<div class="gv2-flow-trust__item gv2-glass-card">
			<span class="gv2-flow-trust__icon" aria-hidden="true">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
			</span>
			<span><?php echo esc_html( gv2_t( '100% Natural' ) ); ?> &bull; <?php echo esc_html( gv2_t( 'No Preservatives' ) ); ?></span>
		</div>
		<div class="gv2-flow-trust__item gv2-glass-card">
			<span class="gv2-flow-trust__icon" aria-hidden="true">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8Z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
			</span>
			<span><?php echo esc_html( function_exists( 'gv2_get_free_delivery_banner_text' ) ? gv2_get_free_delivery_banner_text() : gv2_t( 'Free delivery in Yerevan on orders over 15,000 AMD' ) ); ?></span>
		</div>
		<div class="gv2-flow-trust__item gv2-glass-card">
			<span class="gv2-flow-trust__icon" aria-hidden="true">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
			</span>
			<span><?php echo esc_html( gv2_t( 'Cash, ArCa, Idram, Telcell' ) ); ?></span>
		</div>
	</section>
	<?php
}

