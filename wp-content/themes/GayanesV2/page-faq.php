<?php
/**
 * Template Name: FAQ
 *
 * iOS 27 Liquid Glass & SEO/GEO-optimized Frequently Asked Questions page.
 * Automatically applies to the `/faq/` slug and can be selected as a Page Template.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$lang       = gv2_current_lang();
$categories = gv2_get_faq_categories();
$faqs       = gv2_get_faqs( false );
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>

<main id="primary" class="gv2-main gv2-faq-page">

	<!-- 1. Editorial Hero + Live Search & Category Pills -->
	<section class="gv2-faq-hero">
		<div class="gv2-container">
			<nav class="gv2-about-breadcrumb" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( gv2_t( 'Home' ) ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page"><?php echo esc_html( gv2_t( 'Frequently Asked Questions' ) ); ?></span>
			</nav>

			<div class="gv2-faq-hero__inner">
				<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Questions & Answers' ) ); ?></span>
				<h1 class="gv2-faq-hero__title"><?php echo esc_html( gv2_t( 'Frequently Asked Questions' ) ); ?></h1>
				<p class="gv2-faq-hero__lead">
					<?php echo esc_html( gv2_t( 'Everything you need to know about Gayane’s Kitchen homemade Armenian semi-finished dishes, natural ingredients, cooking tips, and Yerevan delivery.' ) ); ?>
				</p>

				<!-- Frosted Glass Search Pill -->
				<div class="gv2-faq-search gv2-glass-card">
					<svg class="gv2-faq-search__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
					<label class="gv2-sr-only" for="gv2-faq-search-input"><?php echo esc_html( gv2_t( 'Search questions...' ) ); ?></label>
					<input
						id="gv2-faq-search-input"
						type="search"
						class="gv2-faq-search__input"
						placeholder="<?php echo esc_attr( gv2_t( 'Search questions...' ) ); ?>"
						autocomplete="off"
					/>
				</div>

				<!-- Horizontal Scrollable Category Filter Pills -->
				<div class="gv2-faq-cats" role="tablist" aria-label="<?php echo esc_attr( gv2_t( 'Categories' ) ); ?>">
					<?php foreach ( $categories as $cat_key => $cat_labels ) : ?>
						<?php $cat_label = $cat_labels[ $lang ] ?? $cat_labels['hy']; ?>
						<button
							type="button"
							role="tab"
							class="gv2-faq-cat-pill<?php echo 'all' === $cat_key ? ' is-active' : ''; ?>"
							data-faq-cat="<?php echo esc_attr( $cat_key ); ?>"
							aria-selected="<?php echo 'all' === $cat_key ? 'true' : 'false'; ?>"
						>
							<?php echo esc_html( $cat_label ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>

	<!-- 2. FAQ Accordion List -->
	<section class="gv2-section gv2-container" aria-label="<?php echo esc_attr( gv2_t( 'Frequently Asked Questions' ) ); ?>">
		<div class="gv2-faq-list" id="gv2-faq-list">
			<?php foreach ( $faqs as $index => $faq ) : ?>
				<?php
				$cat_key   = (string) $faq['category'];
				$cat_label = $categories[ $cat_key ][ $lang ] ?? ( $categories[ $cat_key ]['hy'] ?? '' );
				$search_blob = mb_strtolower( $faq['question'] . ' ' . $faq['answer'] . ' ' . $cat_label );
				?>
				<details
					id="<?php echo esc_attr( $faq['id'] ); ?>"
					class="gv2-faq-item gv2-glass-card"
					data-category="<?php echo esc_attr( $cat_key ); ?>"
					data-search="<?php echo esc_attr( $search_blob ); ?>"
					<?php echo 0 === $index ? 'open' : ''; ?>
				>
					<summary class="gv2-faq-item__summary">
						<div class="gv2-faq-item__head">
							<?php if ( '' !== $cat_label ) : ?>
								<span class="gv2-faq-item__tag"><?php echo esc_html( $cat_label ); ?></span>
							<?php endif; ?>
							<h2 class="gv2-faq-item__q"><?php echo esc_html( $faq['question'] ); ?></h2>
						</div>
						<span class="gv2-faq-item__toggle" aria-hidden="true">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
						</span>
					</summary>
					<div class="gv2-faq-item__body">
						<p><?php echo esc_html( $faq['answer'] ); ?></p>
					</div>
				</details>
			<?php endforeach; ?>
		</div>

		<div id="gv2-faq-empty" class="gv2-faq-empty gv2-glass-card" hidden>
			<p><?php echo esc_html( gv2_t( 'No matching questions found. Try another keyword or contact us directly.' ) ); ?></p>
		</div>
	</section>

	<!-- 3. Still Have Questions? CTA Card -->
	<section class="gv2-section gv2-container">
		<div class="gv2-about-cta">
			<div class="gv2-about-cta__copy">
				<h2><?php echo esc_html( gv2_t( 'Still Have Questions?' ) ); ?></h2>
				<p><?php echo esc_html( gv2_t( 'Our team in Yerevan is happy to help with orders, ingredients, or special event requests.' ) ); ?></p>
				<div class="gv2-about-cta__meta">
					<a href="tel:+37455600655" class="gv2-about-cta__pill">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.58 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
						<span>+374 (55) 600655</span>
					</a>
					<a href="https://wa.me/37455600655" target="_blank" rel="noopener noreferrer" class="gv2-about-cta__pill">
						<span>WhatsApp</span>
					</a>
					<a href="https://t.me/+37455600655" target="_blank" rel="noopener noreferrer" class="gv2-about-cta__pill">
						<span>Telegram</span>
					</a>
				</div>
			</div>
			<div class="gv2-about-cta__actions">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-light">
					<?php echo esc_html( gv2_t( 'Explore Menu' ) ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-outline">
					<?php echo esc_html( gv2_t( 'Contact' ) ); ?>
				</a>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
