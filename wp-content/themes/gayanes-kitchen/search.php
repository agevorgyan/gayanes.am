<?php
/**
 * Search results template (supports Infinite Scroll for product searches).
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="gv2-main gv2-container">
	<header class="gv2-page-head">
		<h1><?php echo esc_html( gv2_t( 'Search' ) ); ?>: &ldquo;<?php echo esc_html( get_search_query() ); ?>&rdquo;</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="gv2-product-grid" id="gv2-infinite-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				if ( 'product' === get_post_type() && function_exists( 'wc_get_product' ) ) {
					$product = wc_get_product( get_the_ID() );
					if ( $product instanceof WC_Product ) {
						gv2_render_product_card( $product );
					}
				} else {
					?>
					<article <?php post_class( 'gv2-post-card gv2-glass-card' ); ?>>
						<div class="gv2-post-card__body">
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="gv2-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '...' ) ); ?></p>
						</div>
					</article>
					<?php
				}
			endwhile;
			?>
		</div>
	<?php else : ?>
		<p class="gv2-muted"><?php echo esc_html( gv2_t( 'No dishes found.' ) ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();

