<?php
/**
 * Search Results Template (Products & Content).
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="gk-main gk-container">
	<?php gayanes_breadcrumbs(); ?>

	<header class="gk-archive-header">
		<h1 class="gk-archive-title">
			<?php
			printf(
				/* translators: %s: search query */
				esc_html( gayanes_t( 'Search results for: %s' ) ),
				'<span>' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="gk-product-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				if ( 'product' === get_post_type() && function_exists( 'wc_get_product' ) ) {
					$product = wc_get_product( get_the_ID() );
					if ( $product ) {
						gayanes_render_product_card( $product );
					}
				} else {
					?>
					<article class="gk-post-card">
						<div class="gk-post-card__body">
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
						</div>
					</article>
					<?php
				}
			endwhile;
			?>
		</div>
		<div class="gk-pagination">
			<?php the_posts_pagination(); ?>
		</div>
	<?php else : ?>
		<p><?php echo esc_html( gayanes_t( 'No matching products found. Try another search term.' ) ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();

