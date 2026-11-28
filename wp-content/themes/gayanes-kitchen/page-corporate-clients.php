<?php
/**
 * Template Name: Corporate Clients
 *
 * Conversion-optimized B2B / HoReCa Corporate Partnership landing page
 * for gayanes.am (iOS 27 Liquid Glass design system).
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$c = function_exists( 'gv2_get_corporate_copy' ) ? gv2_get_corporate_copy() : array();

// Resolve a real high-res food image from theme settings or published WooCommerce products.
$hero_img_url = '';
$hero_img_alt = $c['h1'] ?? '';
$hero_img_id  = absint( get_theme_mod( 'gv2_home_hero_image_id', 0 ) );
if ( $hero_img_id <= 0 ) {
	$hero_img_id = absint( get_theme_mod( 'gv2_about_hero_image_id', 0 ) );
}
if ( $hero_img_id > 0 ) {
	$hero_img_url = (string) wp_get_attachment_image_url( $hero_img_id, 'large' );
}

$catalog_products = array();
if ( function_exists( 'wc_get_products' ) ) {
	$catalog_products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 8,
			'orderby' => 'popularity',
			'order'   => 'DESC',
		)
	);
	if ( '' === $hero_img_url && ! empty( $catalog_products ) ) {
		foreach ( $catalog_products as $p ) {
			if ( $p instanceof WC_Product && $p->get_image_id() ) {
				$hero_img_url = (string) wp_get_attachment_image_url( (int) $p->get_image_id(), 'large' );
				$hero_img_alt = $p->get_name();
				break;
			}
		}
	}
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

$corp_icons = array(
	'percent' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>',
	'truck'   => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="3" width="15" height="13" rx="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
	'invoice' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/></svg>',
	'headset' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
	'grid'    => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>',
);
?>

<main id="primary" class="gv2-main gv2-corp-page">
	<?php gv2_breadcrumbs(); ?>

	<!-- 1. Hero Section -->
	<section class="gv2-corp-hero gv2-container">
		<div class="gv2-corp-hero__grid">
			<div class="gv2-corp-hero__content">
				<span class="gv2-eyebrow"><?php echo esc_html( $c['eyebrow'] ); ?></span>
				<h1 class="gv2-corp-hero__h1"><?php echo esc_html( $c['h1'] ); ?></h1>
				<p class="gv2-corp-hero__lead"><?php echo esc_html( $c['hero_title'] ); ?></p>
				<p class="gv2-corp-hero__sub"><?php echo esc_html( $c['hero_subtitle'] ); ?></p>

				<div class="gv2-corp-hero__cta">
					<a href="#corporate-apply" class="gv2-btn gv2-btn--primary gv2-btn--lg">
						<?php echo esc_html( $c['cta_primary'] ); ?> &rarr;
					</a>
					<a href="tel:+37455600655" class="gv2-btn gv2-btn--glass gv2-btn--lg">
						<?php echo esc_html( $c['cta_call'] ); ?>
					</a>
				</div>

				<div class="gv2-corp-sectors" aria-label="<?php echo esc_attr( $c['eyebrow'] ); ?>">
					<?php foreach ( $c['sectors'] as $sector_label ) : ?>
						<span class="gv2-trust-pill"><?php echo esc_html( $sector_label ); ?></span>
					<?php endforeach; ?>
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

	<!-- 2. Why Choose Us (5 Benefits) -->
	<section class="gv2-section gv2-container" id="corporate-benefits">
		<div class="gv2-section__head">
			<div>
				<span class="gv2-eyebrow"><?php echo esc_html( $c['benefits_eyebrow'] ); ?></span>
				<h2 class="gv2-section__title"><?php echo esc_html( $c['benefits_title'] ); ?></h2>
				<p class="gv2-muted"><?php echo esc_html( $c['benefits_subtitle'] ); ?></p>
			</div>
			<a href="#corporate-apply" class="gv2-btn gv2-btn--glass"><?php echo esc_html( $c['cta_manager'] ); ?></a>
		</div>

		<div class="gv2-corp-benefits">
			<?php foreach ( $c['benefits'] as $benefit ) : ?>
				<article class="gv2-corp-benefit gv2-glass-card<?php echo ! empty( $benefit['accent'] ) ? ' is-featured' : ''; ?>">
					<div class="gv2-corp-benefit__top">
						<span class="gv2-corp-benefit__icon">
							<?php echo $corp_icons[ $benefit['icon'] ] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<span class="gv2-corp-benefit__badge"><?php echo esc_html( $benefit['badge'] ); ?></span>
					</div>
					<h3><?php echo esc_html( $benefit['title'] ); ?></h3>
					<ul class="gv2-corp-benefit__list">
						<?php foreach ( $benefit['items'] as $item_text ) : ?>
							<li><?php echo esc_html( $item_text ); ?></li>
						<?php endforeach; ?>
					</ul>
				</article>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- 3. How Cooperation Works (4 Steps) -->
	<section class="gv2-section gv2-container" id="corporate-steps">
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

	<!-- 4. Our Product Range (Live WooCommerce) -->
	<?php if ( ! empty( $catalog_products ) && function_exists( 'gv2_render_product_card' ) ) : ?>
		<section class="gv2-section gv2-container" id="corporate-catalog">
			<div class="gv2-section__head">
				<div>
					<span class="gv2-eyebrow"><?php echo esc_html( $c['products_eyebrow'] ); ?></span>
					<h2 class="gv2-section__title"><?php echo esc_html( $c['products_title'] ); ?></h2>
					<p class="gv2-muted"><?php echo esc_html( $c['products_subtitle'] ); ?></p>
				</div>
				<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--primary">
					<?php echo esc_html( $c['products_cta'] ); ?> &rarr;
				</a>
			</div>

			<div class="gv2-product-grid">
				<?php
				foreach ( $catalog_products as $product ) {
					if ( $product instanceof WC_Product ) {
						gv2_render_product_card( $product );
					}
				}
				?>
			</div>
		</section>
	<?php endif; ?>

	<!-- 5. Corporate Partnership Application Form -->
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
							<label for="gv2-corp-company"><?php echo esc_html( $c['f_company'] ); ?> *</label>
							<input id="gv2-corp-company" type="text" name="company" required />
						</div>
						<div class="gv2-contact-field">
							<label for="gv2-corp-name"><?php echo esc_html( $c['f_full_name'] ); ?> *</label>
							<input id="gv2-corp-name" type="text" name="full_name" required />
						</div>
					</div>

					<div class="gv2-contact-form__row">
						<div class="gv2-contact-field">
							<label for="gv2-corp-position"><?php echo esc_html( $c['f_position'] ); ?></label>
							<input id="gv2-corp-position" type="text" name="position" />
						</div>
						<div class="gv2-contact-field">
							<label for="gv2-corp-phone"><?php echo esc_html( $c['f_phone'] ); ?> *</label>
							<input id="gv2-corp-phone" type="tel" name="phone" required placeholder="+374 55 60 06 55" />
						</div>
					</div>

					<div class="gv2-contact-form__row">
						<div class="gv2-contact-field">
							<label for="gv2-corp-email"><?php echo esc_html( $c['f_email'] ); ?> *</label>
							<input id="gv2-corp-email" type="email" name="email" required placeholder="company@example.am" />
						</div>
						<div class="gv2-contact-field">
							<label for="gv2-corp-sector"><?php echo esc_html( $c['f_sector'] ); ?></label>
							<select id="gv2-corp-sector" name="sector">
								<?php foreach ( $c['sector_options'] as $val => $label ) : ?>
									<option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="gv2-contact-form__row">
						<div class="gv2-contact-field">
							<label for="gv2-corp-products"><?php echo esc_html( $c['f_products'] ); ?></label>
							<input id="gv2-corp-products" type="text" name="products" />
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
					<ul class="gv2-corp-aside-list">
						<?php foreach ( $c['form_aside_points'] as $pt ) : ?>
							<li><?php echo esc_html( $pt ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>

				<div class="gv2-info-card gv2-glass-card">
					<span class="gv2-info-card__badge"><?php echo esc_html( $c['cta_manager'] ); ?></span>
					<h3>+374 (55) 60 06 55</h3>
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

	<!-- 6. FAQ Section -->
	<section class="gv2-section gv2-container" id="corporate-faq">
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

	<!-- 7. Final CTA Banner -->
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
					055 60 06 55
				</a>
			</div>
		</div>
	</section>
</main>

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
