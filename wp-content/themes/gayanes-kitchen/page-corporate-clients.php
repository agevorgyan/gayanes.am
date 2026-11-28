<?php
/**
 * Template Name: Corporate Clients
 *
 * Conversion-focused B2B / HoReCa Food Production & Supply Landing Page
 * for gayanes.am (iOS 27 Liquid Glass design system).
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$c = function_exists( 'gv2_get_corporate_copy' ) ? gv2_get_corporate_copy() : array();

// Resolve authentic food photography from existing theme media & WooCommerce catalog.
$hero_img_url   = '';
$custom_img_url = '';
$hero_img_alt   = $c['h1'] ?? '';
$custom_img_alt = $c['custom_title'] ?? '';

$home_hero_id   = absint( get_theme_mod( 'gv2_home_hero_image_id', 0 ) );
$about_hero_id  = absint( get_theme_mod( 'gv2_about_hero_image_id', 0 ) );
$about_story_id = absint( get_theme_mod( 'gv2_about_story_image_id', 0 ) );

if ( $home_hero_id > 0 ) {
	$hero_img_url = (string) wp_get_attachment_image_url( $home_hero_id, 'large' );
} elseif ( $about_hero_id > 0 ) {
	$hero_img_url = (string) wp_get_attachment_image_url( $about_hero_id, 'large' );
}

if ( $about_story_id > 0 ) {
	$custom_img_url = (string) wp_get_attachment_image_url( $about_story_id, 'large' );
} elseif ( $about_hero_id > 0 ) {
	$custom_img_url = (string) wp_get_attachment_image_url( $about_hero_id, 'large' );
}

$b2b_products = array();
if ( function_exists( 'wc_get_products' ) ) {
	$b2b_products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 6,
			'orderby' => 'popularity',
			'order'   => 'DESC',
		)
	);
	foreach ( $b2b_products as $p ) {
		if ( $p instanceof WC_Product && $p->get_image_id() ) {
			$p_img = (string) wp_get_attachment_image_url( (int) $p->get_image_id(), 'large' );
			if ( '' === $hero_img_url ) {
				$hero_img_url = $p_img;
				$hero_img_alt = $p->get_name();
			} elseif ( '' === $custom_img_url ) {
				$custom_img_url = $p_img;
				$custom_img_alt = $p->get_name();
				break;
			}
		}
	}
}
if ( '' === $custom_img_url ) {
	$custom_img_url = $hero_img_url;
}

$privacy_url = function_exists( 'get_privacy_policy_url' ) && get_privacy_policy_url()
	? get_privacy_policy_url()
	: home_url( '/privacy-policy/' );

$b2b_icons = array(
	'utensils'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>',
	'coffee'    => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/></svg>',
	'tray'      => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18"/><path d="M8 14h8"/></svg>',
	'hotel'     => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Z"/><path d="M9 16h.01"/><path d="M15 16h.01"/><path d="M9 12h.01"/><path d="M15 12h.01"/><path d="M9 8h.01"/><path d="M15 8h.01"/></svg>',
	'sparkles'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/></svg>',
	'building'  => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>',
	'clock'     => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
	'shield'    => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>',
	'sliders'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>',
	'layers'    => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>',
	'phone'     => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
	'handshake' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m11 17 2 2a1 1 0 1 0 3-3"/><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4"/><path d="m21 3 1 11h-2"/><path d="M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3"/><path d="M3 4h8"/></svg>',
);
?>

<main id="primary" class="gv2-main gv2-b2b-page">
	<?php gv2_breadcrumbs(); ?>

	<!-- B2B Quick Anchor Navigation -->
	<nav class="gv2-b2b-subnav gv2-container" aria-label="B2B Navigation">
		<div class="gv2-b2b-subnav__inner gv2-glass-card">
			<div class="gv2-b2b-subnav__links">
				<a href="#b2b-products"><?php echo esc_html( $c['nav_products'] ); ?></a>
				<a href="#b2b-partners"><?php echo esc_html( $c['nav_partners'] ); ?></a>
				<a href="#b2b-custom"><?php echo esc_html( $c['nav_custom'] ); ?></a>
				<a href="#b2b-terms"><?php echo esc_html( $c['nav_terms'] ); ?></a>
				<a href="#b2b-faq"><?php echo esc_html( $c['nav_faq'] ); ?></a>
			</div>
			<a href="#corporate-apply" class="gv2-btn gv2-btn--primary gv2-b2b-subnav__cta">
				<?php echo esc_html( $c['nav_cta'] ); ?>
			</a>
		</div>
	</nav>

	<!-- SECTION 1 — HERO -->
	<section class="gv2-corp-hero gv2-container">
		<div class="gv2-corp-hero__grid">
			<div class="gv2-corp-hero__content">
				<span class="gv2-eyebrow"><?php echo esc_html( $c['eyebrow'] ); ?></span>
				<h1 class="gv2-corp-hero__h1"><?php echo esc_html( $c['h1'] ); ?></h1>
				<p class="gv2-corp-hero__lead"><?php echo esc_html( $c['hero_subheadline'] ); ?></p>
				<p class="gv2-corp-hero__sub"><?php echo esc_html( $c['hero_text'] ); ?></p>

				<div class="gv2-corp-hero__cta">
					<a href="#corporate-apply" class="gv2-btn gv2-btn--primary gv2-btn--lg">
						<?php echo esc_html( $c['cta_primary'] ); ?> &rarr;
					</a>
					<a href="#b2b-products" class="gv2-btn gv2-btn--glass gv2-btn--lg">
						<?php echo esc_html( $c['cta_products'] ); ?>
					</a>
					<a href="tel:+37455600655" class="gv2-btn gv2-btn--glass gv2-btn--lg">
						<?php echo esc_html( $c['cta_contact'] ); ?> · 055 60 06 55
					</a>
				</div>
			</div>

			<div class="gv2-corp-hero__media">
				<div class="gv2-hero__arch gv2-corp-hero__arch">
					<?php if ( '' !== $hero_img_url ) : ?>
						<img
							src="<?php echo esc_url( $hero_img_url ); ?>"
							alt="<?php echo esc_attr( $hero_img_alt ); ?>"
							class="gv2-hero__img"
							loading="eager"
							fetchpriority="high"
						/>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<!-- SECTION 2 — WHO WE WORK WITH -->
	<section class="gv2-section gv2-container" id="b2b-partners">
		<div class="gv2-section__head">
			<div>
				<span class="gv2-eyebrow"><?php echo esc_html( $c['partners_eyebrow'] ); ?></span>
				<h2 class="gv2-section__title"><?php echo esc_html( $c['partners_title'] ); ?></h2>
				<p class="gv2-muted gv2-b2b-section-desc"><?php echo esc_html( $c['partners_desc'] ); ?></p>
			</div>
		</div>

		<div class="gv2-b2b-grid-3">
			<?php foreach ( $c['partners'] as $partner ) : ?>
				<article class="gv2-corp-benefit gv2-glass-card">
					<div class="gv2-corp-benefit__top">
						<span class="gv2-corp-benefit__icon">
							<?php echo $b2b_icons[ $partner['icon'] ] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</div>
					<h3><?php echo esc_html( $partner['title'] ); ?></h3>
					<p class="gv2-muted gv2-b2b-card-text"><?php echo esc_html( $partner['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- SECTION 3 — OUR PRODUCTS FOR BUSINESS -->
	<?php if ( ! empty( $b2b_products ) ) : ?>
		<section class="gv2-section gv2-container" id="b2b-products">
			<div class="gv2-section__head">
				<div>
					<span class="gv2-eyebrow"><?php echo esc_html( $c['products_eyebrow'] ); ?></span>
					<h2 class="gv2-section__title"><?php echo esc_html( $c['products_title'] ); ?></h2>
					<p class="gv2-muted gv2-b2b-section-desc"><?php echo esc_html( $c['products_desc'] ); ?></p>
				</div>
				<a href="#corporate-apply" class="gv2-btn gv2-btn--primary">
					<?php echo esc_html( $c['products_cta_all'] ); ?> &rarr;
				</a>
			</div>

			<div class="gv2-b2b-products-grid">
				<?php
				foreach ( $b2b_products as $product ) :
					if ( ! $product instanceof WC_Product ) {
						continue;
					}
					$p_id      = $product->get_id();
					$p_name    = $product->get_name();
					$p_url     = $product->get_permalink();
					$p_img_id  = (int) $product->get_image_id();
					$p_img_url = $p_img_id > 0 ? (string) wp_get_attachment_image_url( $p_img_id, 'medium_large' ) : '';
					$p_short   = wp_strip_all_tags( (string) $product->get_short_description() );
					if ( '' !== $p_short && function_exists( 'mb_strimwidth' ) ) {
						$p_short = mb_strimwidth( $p_short, 0, 110, '…' );
					}
					$p_weight = $product->has_weight() ? wc_format_weight( (float) $product->get_weight() ) : '';
					$terms    = get_the_terms( $p_id, 'product_cat' );
					$cat_name = ( is_array( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
					?>
					<article class="gv2-b2b-product gv2-glass-card">
						<a href="<?php echo esc_url( $p_url ); ?>" class="gv2-b2b-product__media">
							<?php if ( '' !== $p_img_url ) : ?>
								<img src="<?php echo esc_url( $p_img_url ); ?>" alt="<?php echo esc_attr( $p_name ); ?>" loading="lazy" />
							<?php endif; ?>
						</a>
						<div class="gv2-b2b-product__body">
							<div class="gv2-b2b-product__meta">
								<?php if ( '' !== $cat_name ) : ?>
									<span class="gv2-corp-benefit__badge"><?php echo esc_html( $cat_name ); ?></span>
								<?php endif; ?>
								<?php if ( '' !== $p_weight ) : ?>
									<span class="gv2-b2b-product__weight"><?php echo esc_html( $p_weight ); ?></span>
								<?php endif; ?>
							</div>
							<h3 class="gv2-b2b-product__title">
								<a href="<?php echo esc_url( $p_url ); ?>"><?php echo esc_html( $p_name ); ?></a>
							</h3>
							<?php if ( '' !== $p_short ) : ?>
								<p class="gv2-muted gv2-b2b-product__desc"><?php echo esc_html( $p_short ); ?></p>
							<?php endif; ?>
							<button
								type="button"
								class="gv2-btn gv2-btn--glass gv2-b2b-inquire-btn"
								data-b2b-product="<?php echo esc_attr( $p_name ); ?>"
							>
								<?php echo esc_html( $c['product_cta'] ); ?> &rarr;
							</button>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<!-- SECTION 4 — CUSTOM PRODUCT DEVELOPMENT (KEY DIFFERENTIATOR) -->
	<section class="gv2-section gv2-container" id="b2b-custom">
		<div class="gv2-b2b-custom gv2-glass-card">
			<div class="gv2-b2b-custom__content">
				<span class="gv2-eyebrow"><?php echo esc_html( $c['custom_eyebrow'] ); ?></span>
				<h2><?php echo esc_html( $c['custom_title'] ); ?></h2>
				<p class="gv2-b2b-custom__lead"><?php echo esc_html( $c['custom_desc_1'] ); ?></p>
				<p class="gv2-muted"><?php echo esc_html( $c['custom_desc_2'] ); ?></p>

				<ul class="gv2-b2b-checklist">
					<?php foreach ( $c['custom_points'] as $pt ) : ?>
						<li>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
							<span><?php echo esc_html( $pt ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="gv2-b2b-custom__cta">
					<a href="#corporate-apply" class="gv2-btn gv2-btn--primary gv2-btn--lg gv2-b2b-custom-trigger">
						<?php echo esc_html( $c['custom_cta'] ); ?> &rarr;
					</a>
				</div>
			</div>

			<div class="gv2-b2b-custom__media">
				<?php if ( '' !== $custom_img_url ) : ?>
					<img
						src="<?php echo esc_url( $custom_img_url ); ?>"
						alt="<?php echo esc_attr( $custom_img_alt ); ?>"
						loading="lazy"
					/>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- SECTION 5 — WHY PARTNER WITH US -->
	<section class="gv2-section gv2-container" id="b2b-benefits">
		<div class="gv2-section__head">
			<div>
				<span class="gv2-eyebrow"><?php echo esc_html( $c['why_eyebrow'] ); ?></span>
				<h2 class="gv2-section__title"><?php echo esc_html( $c['why_title'] ); ?></h2>
			</div>
		</div>

		<div class="gv2-b2b-grid-3">
			<?php foreach ( $c['benefits'] as $benefit ) : ?>
				<article class="gv2-corp-benefit gv2-glass-card">
					<div class="gv2-corp-benefit__top">
						<span class="gv2-corp-benefit__icon">
							<?php echo $b2b_icons[ $benefit['icon'] ] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
					</div>
					<h3><?php echo esc_html( $benefit['title'] ); ?></h3>
					<p class="gv2-muted gv2-b2b-card-text"><?php echo esc_html( $benefit['desc'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- SECTION 6 — HOW PARTNERSHIP WORKS (4 STEPS) -->
	<section class="gv2-section gv2-container" id="b2b-steps">
		<div class="gv2-section__head">
			<div>
				<span class="gv2-eyebrow"><?php echo esc_html( $c['steps_eyebrow'] ); ?></span>
				<h2 class="gv2-section__title"><?php echo esc_html( $c['steps_title'] ); ?></h2>
			</div>
		</div>

		<ol class="gv2-corp-steps">
			<?php foreach ( $c['steps'] as $step ) : ?>
				<li class="gv2-corp-step gv2-glass-card">
					<span class="gv2-corp-step__num"><?php echo esc_html( $step['num'] ); ?></span>
					<h3><?php echo esc_html( $step['title'] ); ?></h3>
					<p><?php echo esc_html( $step['desc'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</section>

	<!-- SECTION 7 — INDIVIDUAL PRICING AND TERMS -->
	<section class="gv2-section gv2-container" id="b2b-terms">
		<div class="gv2-b2b-terms gv2-glass-card">
			<div class="gv2-b2b-terms__copy">
				<span class="gv2-eyebrow"><?php echo esc_html( $c['terms_eyebrow'] ); ?></span>
				<h2><?php echo esc_html( $c['terms_title'] ); ?></h2>
				<p class="gv2-muted"><?php echo esc_html( $c['terms_desc'] ); ?></p>
			</div>
			<div class="gv2-b2b-terms__box">
				<h3><?php echo esc_html( $c['terms_lead'] ); ?></h3>
				<ul class="gv2-b2b-checklist">
					<?php foreach ( $c['terms_items'] as $item ) : ?>
						<li>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
							<span><?php echo esc_html( $item ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</section>

	<!-- SECTION 8 — CORPORATE PARTNERSHIP FORM -->
	<section class="gv2-section gv2-container" id="corporate-apply">
		<div class="gv2-corp-apply-grid">
			<div class="gv2-contact-form-card gv2-glass-card">
				<span class="gv2-eyebrow"><?php echo esc_html( $c['form_eyebrow'] ); ?></span>
				<h2><?php echo esc_html( $c['form_title'] ); ?></h2>
				<p class="gv2-muted gv2-corp-form-intro"><?php echo esc_html( $c['form_subtitle'] ); ?></p>

				<form id="gv2-corporate-form" class="gv2-contact-form" method="post" novalidate>
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'gv2_corporate_nonce' ) ); ?>" />
					<input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true" />

					<div class="gv2-contact-form__row">
						<div class="gv2-contact-field">
							<label for="gv2-corp-name"><?php echo esc_html( $c['f_full_name'] ); ?> *</label>
							<input id="gv2-corp-name" type="text" name="full_name" required />
						</div>
						<div class="gv2-contact-field">
							<label for="gv2-corp-company"><?php echo esc_html( $c['f_company'] ); ?> *</label>
							<input id="gv2-corp-company" type="text" name="company" required />
						</div>
					</div>

					<div class="gv2-contact-form__row">
						<div class="gv2-contact-field">
							<label for="gv2-corp-sector"><?php echo esc_html( $c['f_sector'] ); ?></label>
							<select id="gv2-corp-sector" name="sector">
								<?php foreach ( $c['sector_options'] as $val => $label ) : ?>
									<option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="gv2-contact-field">
							<label for="gv2-corp-phone"><?php echo esc_html( $c['f_phone'] ); ?> *</label>
							<input id="gv2-corp-phone" type="tel" name="phone" required placeholder="+374 55 60 06 55" />
						</div>
					</div>

					<div class="gv2-contact-form__row">
						<div class="gv2-contact-field">
							<label for="gv2-corp-email"><?php echo esc_html( $c['f_email'] ); ?> *</label>
							<input id="gv2-corp-email" type="email" name="email" required placeholder="info@company.am" />
						</div>
						<div class="gv2-contact-field">
							<label for="gv2-corp-products"><?php echo esc_html( $c['f_products'] ); ?></label>
							<input id="gv2-corp-products" type="text" name="products" />
						</div>
					</div>

					<div class="gv2-contact-form__row">
						<div class="gv2-contact-field">
							<label for="gv2-corp-volume"><?php echo esc_html( $c['f_volume'] ); ?></label>
							<input id="gv2-corp-volume" type="text" name="volume" />
						</div>
						<div class="gv2-contact-field">
							<label for="gv2-corp-frequency"><?php echo esc_html( $c['f_frequency'] ); ?></label>
							<select id="gv2-corp-frequency" name="frequency">
								<?php foreach ( $c['freq_options'] as $val => $label ) : ?>
									<option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="gv2-contact-field">
						<label for="gv2-corp-comment"><?php echo esc_html( $c['f_comment'] ); ?></label>
						<textarea id="gv2-corp-comment" name="comment" rows="4"></textarea>
					</div>

					<label class="gv2-b2b-consent">
						<input type="checkbox" name="consent" value="1" required />
						<span>
							<?php echo esc_html( $c['f_consent'] ); ?>
							<a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener noreferrer">&rarr;</a>
						</span>
					</label>

					<div id="gv2-corporate-feedback" class="gv2-contact-feedback" role="status" aria-live="polite" hidden></div>

					<?php
					if ( function_exists( 'gv2_render_turnstile_widget' ) ) {
						gv2_render_turnstile_widget( 'corporate', 'gv2-turnstile-wrap--contact' );
					}
					?>

					<button type="submit" class="gv2-btn gv2-btn--primary gv2-btn--lg gv2-corp-submit">
						<?php echo esc_html( $c['f_submit'] ); ?> &rarr;
					</button>
				</form>
			</div>

			<aside class="gv2-contact-aside">
				<div class="gv2-info-card gv2-glass-card">
					<span class="gv2-info-card__badge"><?php echo esc_html( $c['eyebrow'] ); ?></span>
					<h3><?php echo esc_html( $c['form_aside_title'] ); ?></h3>
					<p class="gv2-muted"><?php echo esc_html( $c['form_aside_text'] ); ?></p>
					<p><strong>055 60 06 55</strong></p>
					<p>info@gayanes.am</p>
					<p><?php echo esc_html( gv2_t( 'Daily: 09:00 – 21:00' ) ); ?></p>
					<div class="gv2-contact-socials">
						<a href="tel:+37455600655" class="gv2-contact-pill"><?php echo esc_html( $c['cta_call'] ); ?></a>
						<a href="https://wa.me/37455600655" target="_blank" rel="noopener noreferrer" class="gv2-contact-pill">WhatsApp</a>
						<a href="https://t.me/+37455600655" target="_blank" rel="noopener noreferrer" class="gv2-contact-pill">Telegram</a>
					</div>
				</div>
			</aside>
		</div>
	</section>

	<!-- SECTION 9 — FAQ -->
	<section class="gv2-section gv2-container" id="b2b-faq">
		<div class="gv2-section__head">
			<div>
				<span class="gv2-eyebrow"><?php echo esc_html( $c['faq_eyebrow'] ); ?></span>
				<h2 class="gv2-section__title"><?php echo esc_html( $c['faq_title'] ); ?></h2>
			</div>
		</div>

		<div class="gv2-faq-list">
			<?php foreach ( $c['faqs'] as $idx => $faq ) : ?>
				<details class="gv2-faq-item gv2-glass-card"<?php echo 0 === $idx ? ' open' : ''; ?>>
					<summary class="gv2-faq-item__summary">
						<h3 class="gv2-faq-item__q"><?php echo esc_html( $faq['q'] ); ?></h3>
						<span class="gv2-faq-item__toggle" aria-hidden="true">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
						</span>
					</summary>
					<div class="gv2-faq-item__body">
						<p><?php echo esc_html( $faq['a'] ); ?></p>
					</div>
				</details>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- SECTION 10 — FINAL CTA -->
	<section class="gv2-section gv2-container">
		<div class="gv2-corp-cta-banner">
			<div class="gv2-corp-cta-banner__copy">
				<h2><?php echo esc_html( $c['final_title'] ); ?></h2>
				<p><?php echo esc_html( $c['final_text'] ); ?></p>
			</div>
			<div class="gv2-corp-cta-banner__actions">
				<a href="#corporate-apply" class="gv2-btn gv2-btn--primary gv2-btn--lg">
					<?php echo esc_html( $c['final_cta'] ); ?> &rarr;
				</a>
				<a href="tel:+37455600655" class="gv2-btn gv2-btn--glass gv2-btn--lg">
					<?php echo esc_html( $c['final_call'] ); ?> · 055 60 06 55
				</a>
			</div>
		</div>
	</section>
</main>

<!-- Mobile Sticky Conversion Bar -->
<div class="gv2-b2b-sticky-cta" role="region" aria-label="<?php echo esc_attr( $c['cta_primary'] ); ?>">
	<a href="#corporate-apply" class="gv2-btn gv2-btn--primary">
		<?php echo esc_html( $c['cta_primary'] ); ?>
	</a>
	<a href="tel:+37455600655" class="gv2-btn gv2-btn--glass">
		<?php echo esc_html( $c['cta_call'] ); ?>
	</a>
</div>

<?php
$faq_schema = array(
	'@context'   => 'https://schema.org',
	'@type'      => 'FAQPage',
	'mainEntity' => array_map(
		static fn( array $f ): array => array(
			'@type'          => 'Question',
			'name'           => $f['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $f['a'],
			),
		),
		$c['faqs']
	),
);
?>
<script type="application/ld+json"><?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>

<?php
get_footer();
