<?php
/**
 * Template Name: About Us
 *
 * Editorial & iOS 27 Liquid Glass "About Us" (Մեր մասին) page template.
 * Automatically applies to the `about-us` slug and can also be selected
 * as a Page Template for translated pages.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$contact_url = home_url( '/contact/' );

// Gather authentic food photography: featured image first, then published product images.
$about_images = array();
if ( has_post_thumbnail() ) {
	$thumb_url = get_the_post_thumbnail_url( get_the_ID(), 'large' );
	if ( $thumb_url ) {
		$about_images[] = array(
			'url' => $thumb_url,
			'alt' => get_the_title(),
		);
	}
}

if ( function_exists( 'wc_get_products' ) && count( $about_images ) < 3 ) {
	$sample_products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 6,
			'orderby' => 'popularity',
			'order'   => 'DESC',
		)
	);
	foreach ( $sample_products as $prod ) {
		if ( ! $prod instanceof WC_Product ) {
			continue;
		}
		$img_id = (int) $prod->get_image_id();
		if ( $img_id > 0 ) {
			$img_url = wp_get_attachment_image_url( $img_id, 'large' );
			if ( $img_url ) {
				$about_images[] = array(
					'url' => $img_url,
					'alt' => $prod->get_name(),
				);
			}
		}
		if ( count( $about_images ) >= 3 ) {
			break;
		}
	}
}

$hero_img      = $about_images[0] ?? null;
$collage_img_1 = $about_images[1] ?? $hero_img;
$collage_img_2 = $about_images[2] ?? $collage_img_1;
?>

<main id="primary" class="gv2-main gv2-about-page">

	<!-- 1. Editorial Hero -->
	<section class="gv2-about-hero">
		<div class="gv2-container">
			<nav class="gv2-about-breadcrumb" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( gv2_t( 'Home' ) ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page"><?php echo esc_html( gv2_t( 'About Us' ) ); ?></span>
			</nav>

			<div class="gv2-about-hero__grid">
				<div class="gv2-about-hero__copy">
					<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Our Story' ) ); ?></span>
					<h1 class="gv2-about-hero__title">
						<?php echo esc_html( gv2_t( 'Crafted with Love, Just Like Home' ) ); ?>
					</h1>
					<p class="gv2-about-hero__lead">
						<?php echo esc_html( gv2_t( 'From our family kitchen in Yerevan to your table — handcrafted Armenian semi-finished dishes made daily from 100% natural local ingredients.' ) ); ?>
					</p>
					<div class="gv2-about-hero__actions">
						<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--primary gv2-btn--lg">
							<?php echo esc_html( gv2_t( 'Explore Menu' ) ); ?>
						</a>
						<a href="<?php echo esc_url( $contact_url ); ?>" class="gv2-btn gv2-btn--glass gv2-btn--lg">
							<?php echo esc_html( gv2_t( 'Contact' ) ); ?>
						</a>
					</div>
				</div>

				<div class="gv2-about-hero__media">
					<div class="gv2-about-hero__arch">
						<?php if ( $hero_img ) : ?>
							<img
								src="<?php echo esc_url( $hero_img['url'] ); ?>"
								alt="<?php echo esc_attr( $hero_img['alt'] ); ?>"
								class="gv2-about-hero__img"
								loading="eager"
								fetchpriority="high"
								decoding="async"
							/>
						<?php else : ?>
							<div class="gv2-hero__placeholder">
								<img src="<?php echo esc_url( GV2_URI . '/assets/images/logo-dark.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
							</div>
						<?php endif; ?>
					</div>

					<div class="gv2-about-float-badge gv2-glass-card">
						<span class="gv2-about-float-badge__dot" aria-hidden="true"></span>
						<span><?php echo esc_html( gv2_t( '100% Natural' ) ); ?> &bull; <?php echo esc_html( gv2_t( 'No Preservatives' ) ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- 2. Trust & Impact Metrics -->
	<section class="gv2-section gv2-container" aria-label="<?php echo esc_attr( gv2_t( 'Our Kitchen Standards' ) ); ?>">
		<div class="gv2-about-stats">
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">100%</span>
				<span class="gv2-about-stat__label"><?php echo esc_html( gv2_t( 'Natural Ingredients' ) ); ?></span>
			</div>
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">0%</span>
				<span class="gv2-about-stat__label"><?php echo esc_html( gv2_t( 'Preservatives & Additives' ) ); ?></span>
			</div>
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">24/7</span>
				<span class="gv2-about-stat__label"><?php echo esc_html( gv2_t( 'Handcrafted Daily' ) ); ?></span>
			</div>
			<div class="gv2-about-stat gv2-glass-card">
				<span class="gv2-about-stat__num">15,000&#1423;+</span>
				<span class="gv2-about-stat__label"><?php echo esc_html( gv2_t( 'Free Yerevan Delivery' ) ); ?></span>
			</div>
		</div>
	</section>

	<!-- 3. Brand Philosophy & Editorial Story -->
	<section class="gv2-section gv2-about-story-band">
		<div class="gv2-container gv2-about-story__grid">
			<div class="gv2-about-collage" aria-hidden="true">
				<div class="gv2-about-collage__card gv2-about-collage__card--primary">
					<?php if ( $collage_img_1 ) : ?>
						<img src="<?php echo esc_url( $collage_img_1['url'] ); ?>" alt="<?php echo esc_attr( $collage_img_1['alt'] ); ?>" loading="lazy" decoding="async" />
					<?php else : ?>
						<div class="gv2-hero__placeholder">
							<img src="<?php echo esc_url( GV2_URI . '/assets/images/logo-dark.png' ); ?>" alt="" />
						</div>
					<?php endif; ?>
				</div>
				<div class="gv2-about-collage__card gv2-about-collage__card--secondary">
					<?php if ( $collage_img_2 ) : ?>
						<img src="<?php echo esc_url( $collage_img_2['url'] ); ?>" alt="<?php echo esc_attr( $collage_img_2['alt'] ); ?>" loading="lazy" decoding="async" />
					<?php else : ?>
						<div class="gv2-hero__placeholder">
							<img src="<?php echo esc_url( GV2_URI . '/assets/images/logo-dark.png' ); ?>" alt="" />
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="gv2-about-story__content">
				<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Why Gayane’s Kitchen' ) ); ?></span>
				<h2 class="gv2-about-heading"><?php echo esc_html( gv2_t( 'Tradition Meets Modern Convenience' ) ); ?></h2>
				<p class="gv2-about-text">
					<?php echo esc_html( gv2_t( 'We started Gayane’s Kitchen with a simple belief: busy days should never mean compromising on real, wholesome homemade food. Every dolma, pelmeni, khinkali, and blini is shaped by hand following time-honored family recipes.' ) ); ?>
				</p>
				<p class="gv2-about-text">
					<?php echo esc_html( gv2_t( 'We carefully select fresh farm meats, aromatic Armenian herbs, and natural dairy every morning, then gently chill our dishes so you can serve a warm, homemade dinner in just minutes.' ) ); ?>
				</p>

				<blockquote class="gv2-about-quote gv2-glass-card">
					<p><?php echo esc_html( gv2_t( 'In our kitchen, every dish is prepared as if we are cooking for our own family.' ) ); ?></p>
					<footer>&mdash; <?php echo esc_html( gv2_t( 'Gayane, Founder & Chef' ) ); ?></footer>
				</blockquote>
			</div>
		</div>
	</section>

	<!-- 4. Four Pillars of Craft -->
	<section class="gv2-section gv2-container" aria-labelledby="gv2-about-values-heading">
		<div class="gv2-about-center-head">
			<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Crafted Like Home' ) ); ?></span>
			<h2 class="gv2-section__title" id="gv2-about-values-heading"><?php echo esc_html( gv2_t( 'Our Kitchen Standards' ) ); ?></h2>
		</div>

		<div class="gv2-about-values">
			<article class="gv2-about-value gv2-glass-card">
				<div class="gv2-about-value__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
				</div>
				<h3><?php echo esc_html( gv2_t( 'Selected Farm Produce' ) ); ?></h3>
				<p><?php echo esc_html( gv2_t( 'Only fresh meat, seasonal vegetables, and aromatic greens from trusted Armenian suppliers.' ) ); ?></p>
			</article>

			<article class="gv2-about-value gv2-glass-card">
				<div class="gv2-about-value__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
				</div>
				<h3><?php echo esc_html( gv2_t( 'Authentic Family Recipes' ) ); ?></h3>
				<p><?php echo esc_html( gv2_t( 'Hand-rolled dough and balanced spices passed down through generations without shortcuts.' ) ); ?></p>
			</article>

			<article class="gv2-about-value gv2-glass-card">
				<div class="gv2-about-value__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
				</div>
				<h3><?php echo esc_html( gv2_t( 'Clean & Certified Kitchen' ) ); ?></h3>
				<p><?php echo esc_html( gv2_t( 'Strict hygiene standards at our Davtashen kitchen ensure safety and consistency in every batch.' ) ); ?></p>
			</article>

			<article class="gv2-about-value gv2-glass-card">
				<div class="gv2-about-value__icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9 12h6"/><path d="M12 9v6"/></svg>
				</div>
				<h3><?php echo esc_html( gv2_t( 'Vegan & Fasting Friendly' ) ); ?></h3>
				<p><?php echo esc_html( gv2_t( 'Delicious plant-based falafel, potato and spinach vareniks, and veggie spring rolls crafted with care.' ) ); ?></p>
			</article>
		</div>
	</section>

	<!-- 5. 3-Step Process -->
	<section class="gv2-section gv2-container" aria-labelledby="gv2-about-process-heading">
		<div class="gv2-about-center-head">
			<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Chilled Delivery' ) ); ?></span>
			<h2 class="gv2-section__title" id="gv2-about-process-heading"><?php echo esc_html( gv2_t( 'From Our Kitchen to Your Table' ) ); ?></h2>
		</div>

		<div class="gv2-about-steps">
			<div class="gv2-about-step gv2-glass-card">
				<span class="gv2-about-step__num">01</span>
				<h3><?php echo esc_html( gv2_t( 'Handcrafted Daily' ) ); ?></h3>
				<p><?php echo esc_html( gv2_t( 'Every morning our chefs prepare fresh fillings and hand-fold each piece with care.' ) ); ?></p>
			</div>

			<div class="gv2-about-step gv2-glass-card">
				<span class="gv2-about-step__num">02</span>
				<h3><?php echo esc_html( gv2_t( 'Gentle Shock Chilling' ) ); ?></h3>
				<p><?php echo esc_html( gv2_t( 'Rapid chilling locks in natural taste, juiciness, and nutrients without any preservatives.' ) ); ?></p>
			</div>

			<div class="gv2-about-step gv2-glass-card">
				<span class="gv2-about-step__num">03</span>
				<h3><?php echo esc_html( gv2_t( 'Chilled Delivery in Yerevan' ) ); ?></h3>
				<p><?php echo esc_html( gv2_t( 'Delivered straight to your home in temperature-controlled packaging — ready to cook in minutes.' ) ); ?></p>
			</div>
		</div>
	</section>

	<!-- 6. Warm Brand CTA Band -->
	<section class="gv2-section gv2-container">
		<div class="gv2-about-cta">
			<div class="gv2-about-cta__copy">
				<h2><?php echo esc_html( gv2_t( 'Ready for a Homemade Dinner in Minutes?' ) ); ?></h2>
				<p><?php echo esc_html( gv2_t( 'Visit us in Davtashen or order online with same-day delivery across Yerevan.' ) ); ?></p>
				<div class="gv2-about-cta__meta">
					<a href="https://yandex.com/maps/-/CXSIyGZo" target="_blank" rel="noopener noreferrer" class="gv2-about-cta__pill">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
						<span><?php echo esc_html( gv2_t( 'Yerevan, Davtashen 2nd dist., 37' ) ); ?></span>
					</a>
					<a href="tel:+37455600655" class="gv2-about-cta__pill">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.58 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
						<span>+374 (55) 600655</span>
					</a>
				</div>
			</div>
			<div class="gv2-about-cta__actions">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-light">
					<?php echo esc_html( gv2_t( 'Explore Menu' ) ); ?>
				</a>
				<a href="<?php echo esc_url( $contact_url ); ?>" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-outline">
					<?php echo esc_html( gv2_t( 'Contact' ) ); ?>
				</a>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();

