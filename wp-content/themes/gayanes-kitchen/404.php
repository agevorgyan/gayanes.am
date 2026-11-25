<?php
/**
 * 404 Not Found template — Creative Liquid Glass + Homemade Kitchen Edition.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

$popular_cats = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'number'     => 6,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);
?>
<main id="primary" class="gv2-main gv2-container gv2-404">
	<section class="gv2-404-hero gv2-glass-card">
		<div class="gv2-404-orb gv2-404-orb--1" aria-hidden="true"></div>
		<div class="gv2-404-orb gv2-404-orb--2" aria-hidden="true"></div>

		<div class="gv2-404-visual" aria-hidden="true">
			<div class="gv2-404-plate">
				<svg class="gv2-404-illustration" width="110" height="110" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
					<circle cx="60" cy="64" r="46" fill="#FFFFFF" stroke="rgba(48,75,57,0.16)" stroke-width="2.5"/>
					<circle cx="60" cy="64" r="35" fill="rgba(48,75,57,0.04)" stroke="rgba(48,75,57,0.10)" stroke-width="1.5" stroke-dasharray="4 4"/>
					<!-- Steam lines -->
					<path d="M44 30C44 24 49 22 49 16" stroke="#b86b4b" stroke-width="2.5" stroke-linecap="round" opacity="0.75"/>
					<path d="M60 26C60 19 65 17 65 10" stroke="#304b39" stroke-width="2.5" stroke-linecap="round" opacity="0.75"/>
					<path d="M76 30C76 24 81 22 81 16" stroke="#b86b4b" stroke-width="2.5" stroke-linecap="round" opacity="0.75"/>
					<!-- Stylized handmade dumpling / khinkali -->
					<path d="M38 72C38 56 49 46 60 40C71 46 82 56 82 72C82 81 72 86 60 86C48 86 38 81 38 72Z" fill="#f7efe3" stroke="#304b39" stroke-width="2.4" stroke-linejoin="round"/>
					<path d="M55 42C55 37 57 35 60 35C63 35 65 37 65 42" fill="#f7efe3" stroke="#304b39" stroke-width="2.4" stroke-linecap="round"/>
					<path d="M52 50L56 64M60 46V64M68 50L64 64" stroke="#b86b4b" stroke-width="1.8" stroke-linecap="round" opacity="0.7"/>
				</svg>
				<span class="gv2-404-code">404</span>
			</div>
		</div>

		<h1 class="gv2-404-title"><?php echo esc_html( gv2_t( "Oops, this dish isn't on the shelf" ) ); ?></h1>
		<p class="gv2-404-desc"><?php echo esc_html( gv2_t( "Looks like this page has moved or is no longer available. Explore our fresh homemade menu — there's plenty more to enjoy!" ) ); ?></p>

		<form role="search" method="get" class="gv2-404-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="hidden" name="post_type" value="product" />
			<input
				type="search"
				class="gv2-404-search__input"
				name="s"
				placeholder="<?php echo esc_attr( gv2_t( 'Search dishes...' ) ); ?>"
				aria-label="<?php echo esc_attr( gv2_t( 'Search dishes...' ) ); ?>"
			/>
			<button type="submit" class="gv2-404-search__btn" aria-label="<?php echo esc_attr( gv2_t( 'Search' ) ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
			</button>
		</form>

		<div class="gv2-404-actions">
			<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--primary gv2-404-btn">
				<?php echo esc_html( gv2_t( 'Browse Menu' ) ); ?>
			</a>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gv2-btn gv2-btn--glass gv2-404-btn">
				<?php echo esc_html( gv2_t( 'Back to Home' ) ); ?>
			</a>
		</div>

		<?php if ( ! empty( $popular_cats ) && ! is_wp_error( $popular_cats ) ) : ?>
			<div class="gv2-404-cats">
				<span class="gv2-404-cats__label"><?php echo esc_html( gv2_t( 'Popular Categories' ) ); ?></span>
				<div class="gv2-404-cats__list">
					<?php foreach ( $popular_cats as $cat ) : ?>
						<?php $cat_link = get_term_link( $cat, 'product_cat' ); ?>
						<?php if ( ! is_wp_error( $cat_link ) ) : ?>
							<a href="<?php echo esc_url( $cat_link ); ?>" class="gv2-404-cat-pill">
								<?php echo esc_html( $cat->name ); ?>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</section>

	<?php
	if ( function_exists( 'wc_get_products' ) && function_exists( 'gv2_render_product_card' ) ) :
		$suggested = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 4,
				'orderby' => 'popularity',
				'order'   => 'DESC',
			)
		);
		if ( ! empty( $suggested ) ) :
			?>
			<section class="gv2-404-suggested">
				<h2 class="gv2-section-title"><?php echo esc_html( gv2_t( 'You Might Also Like' ) ); ?></h2>
				<div class="gv2-product-grid">
					<?php
					foreach ( $suggested as $prod ) {
						gv2_render_product_card( $prod );
					}
					?>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>
</main>
<?php
get_footer();
