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

$categories = function_exists( 'gv2_get_faq_categories' ) ? gv2_get_faq_categories() : array();
$faqs       = function_exists( 'gv2_get_faqs' ) ? gv2_get_faqs( false ) : array();
$lang       = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';
?>
<main id="primary" class="gv2-main gv2-faq-page">
	<!-- 1. Editorial Hero + Live Search & Category Pills -->
	<section class="gv2-faq-hero">
		<div class="gv2-container">
			<nav class="gv2-about-breadcrumb" aria-label="<?php echo esc_attr( gv2_t( 'Breadcrumb' ) ); ?>">
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
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<circle cx="11" cy="11" r="7"/>
						<path d="m20 20-3.5-3.5" stroke-linecap="round"/>
					</svg>
					<label class="gv2-sr-only" for="gv2-faq-search-input"><?php echo esc_html( gv2_t( 'Search questions...' ) ); ?></label>
					<input
						id="gv2-faq-search-input"
						type="search"
						class="gv2-faq-search__input"
						placeholder="<?php echo esc_attr( gv2_t( 'Search questions...' ) ); ?>"
					/>
				</div>

				<!-- Horizontal Scrollable Category Filter Pills -->
				<?php if ( ! empty( $categories ) ) : ?>
					<div class="gv2-faq-cats" role="tablist" aria-label="<?php echo esc_attr( gv2_t( 'Categories' ) ); ?>">
						<?php foreach ( $categories as $cat_key => $cat_labels ) : ?>
							<button
								type="button"
								role="tab"
								class="gv2-faq-cat-pill<?php echo 'all' === $cat_key ? ' is-active' : ''; ?>"
								data-faq-cat="<?php echo esc_attr( $cat_key ); ?>"
							>
								<?php echo esc_html( $cat_labels[ $lang ] ?? $cat_labels['en'] ); ?>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- 2. FAQ Accordion List -->
	<section class="gv2-section gv2-container">
		<div class="gv2-faq-list" id="gv2-faq-list">
			<?php foreach ( $faqs as $idx => $faq ) : ?>
				<details
					class="gv2-faq-item gv2-glass-card"
					data-faq-category="<?php echo esc_attr( (string) $faq['category'] ); ?>"
					<?php echo 0 === $idx ? 'open' : ''; ?>
				>
					<summary class="gv2-faq-item__summary">
						<h2 class="gv2-faq-item__q"><?php echo esc_html( $faq['question'] ); ?></h2>
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

		<div class="gv2-faq-empty gv2-glass-card" id="gv2-faq-empty" hidden>
			<p><?php echo esc_html( gv2_t( 'No matching questions found. Try another keyword or contact us directly.' ) ); ?></p>
		</div>
	</section>

	<!-- 3. Still Have Questions CTA -->
	<section class="gv2-section gv2-container">
		<div class="gv2-about-cta">
			<div class="gv2-about-cta__copy">
				<h2><?php echo esc_html( gv2_t( 'Still Have Questions?' ) ); ?></h2>
				<p><?php echo esc_html( gv2_t( 'Our team in Yerevan is happy to help with orders, ingredients, or special event requests.' ) ); ?></p>
			</div>
			<div class="gv2-about-cta__actions">
				<a href="tel:+37455600655" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-light">
					<?php echo esc_html( gv2_t( 'Call Us' ) ); ?>
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
