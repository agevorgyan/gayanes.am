<?php
/**
 * Single post / recipe template — iOS 27 Liquid Glass Editorial Article.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$blog_url = home_url( '/blog/' );
?>

<div class="gv2-read-progress" id="gv2-read-progress" aria-hidden="true"></div>

<main id="primary" class="gv2-main gv2-article-page">
	<?php
	while ( have_posts() ) :
		the_post();
		$post_id    = get_the_ID();
		$cats       = get_the_category( $post_id );
		$cat_label  = ( ! empty( $cats ) && isset( $cats[0]->name ) ) ? $cats[0]->name : gv2_t( 'Blog & Recipes' );
		$word_count = str_word_count( wp_strip_all_tags( get_the_content() ) );
		$read_mins  = max( 2, (int) ceil( $word_count / 180 ) );
		?>

		<!-- 1. Editorial Hero -->
		<section class="gv2-article-hero">
			<div class="gv2-container">
				<nav class="gv2-about-breadcrumb" aria-label="<?php echo esc_attr( gv2_t( 'Breadcrumb' ) ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( gv2_t( 'Home' ) ); ?></a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( $blog_url ); ?>"><?php echo esc_html( gv2_t( 'Blog & Recipes' ) ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php echo esc_html( wp_trim_words( get_the_title(), 6, '...' ) ); ?></span>
				</nav>

				<header class="gv2-article-hero__head">
					<div class="gv2-article-meta">
						<span class="gv2-article-cat"><?php echo esc_html( $cat_label ); ?></span>
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<span aria-hidden="true">&bull;</span>
						<span><?php echo esc_html( sprintf( gv2_t( '%d min read' ), $read_mins ) ); ?></span>
					</div>
					<h1 class="gv2-article-hero__title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="gv2-article-hero__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="gv2-article-hero__media">
						<?php
						the_post_thumbnail(
							'large',
							array(
								'class'         => 'gv2-article-hero__img',
								'loading'       => 'eager',
								'fetchpriority' => 'high',
								'decoding'      => 'async',
							)
						);
						?>
					</figure>
				<?php endif; ?>
			</div>
		</section>

		<!-- 2. Two-Column Article Body & Sticky Kitchen Sidebar -->
		<section class="gv2-section gv2-container">
			<div class="gv2-article-layout">
				<article <?php post_class( 'gv2-article-body gv2-glass-card' ); ?>>
					<div class="gv2-prose">
						<?php the_content(); ?>
					</div>

					<footer class="gv2-article-foot">
						<a href="<?php echo esc_url( $blog_url ); ?>" class="gv2-btn gv2-btn--glass">
							&larr; <?php echo esc_html( gv2_t( 'Blog & Recipes' ) ); ?>
						</a>
						<button type="button" class="gv2-btn gv2-btn--primary" id="gv2-copy-article-link" data-url="<?php echo esc_url( get_permalink() ); ?>">
							<?php echo esc_html( gv2_t( 'Share link' ) ); ?>
						</button>
					</footer>
				</article>

				<aside class="gv2-article-aside" aria-label="<?php echo esc_attr( gv2_t( 'Kitchen Recommendations' ) ); ?>">
					<!-- Kitchen Profile Card -->
					<div class="gv2-article-kitchen gv2-glass-card">
						<div class="gv2-article-kitchen__brand">
							<img src="<?php echo esc_url( GV2_URI . '/assets/images/logo-dark.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="120" height="34" loading="lazy" />
						</div>
						<h3><?php echo esc_html( gv2_t( 'Crafted Like Home' ) ); ?></h3>
						<p><?php echo esc_html( gv2_t( 'Handcrafted daily with 100% natural ingredients and delivered chilled to your door.' ) ); ?></p>
						<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--primary">
							<?php echo esc_html( gv2_t( 'Explore Menu' ) ); ?> &rarr;
						</a>
					</div>

					<!-- Featured Products Mini-List -->
					<?php if ( function_exists( 'wc_get_products' ) ) : ?>
						<?php
						$sidebar_products = wc_get_products(
							array(
								'status'  => 'publish',
								'limit'   => 3,
								'orderby' => 'popularity',
								'order'   => 'DESC',
							)
						);
						if ( ! empty( $sidebar_products ) ) :
							?>
							<div class="gv2-article-products gv2-glass-card">
								<h3 class="gv2-article-products__title"><?php echo esc_html( gv2_t( 'Best Sellers' ) ); ?></h3>
								<div class="gv2-article-products__list">
									<?php foreach ( $sidebar_products as $s_prod ) : ?>
										<?php
										if ( ! $s_prod instanceof WC_Product ) {
											continue;
										}
										$s_img = wp_get_attachment_image_url( (int) $s_prod->get_image_id(), 'thumbnail' );
										?>
										<a href="<?php echo esc_url( $s_prod->get_permalink() ); ?>" class="gv2-article-prod-row">
											<div class="gv2-article-prod-row__img">
												<?php if ( $s_img ) : ?>
													<img src="<?php echo esc_url( $s_img ); ?>" alt="<?php echo esc_attr( $s_prod->get_name() ); ?>" loading="lazy" />
												<?php endif; ?>
											</div>
											<div class="gv2-article-prod-row__info">
												<span class="gv2-article-prod-row__name"><?php echo esc_html( $s_prod->get_name() ); ?></span>
												<span class="gv2-article-prod-row__price"><?php echo wp_kses_post( $s_prod->get_price_html() ); ?></span>
											</div>
										</a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</aside>
			</div>
		</section>

		<!-- 3. Related Articles -->
		<?php
		$related = new WP_Query(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 2,
				'post__not_in'   => array( $post_id ),
				'post_status'    => 'publish',
			)
		);
		if ( $related->have_posts() ) :
			?>
			<section class="gv2-section gv2-blog-band" aria-labelledby="gv2-related-heading">
				<div class="gv2-container">
					<div class="gv2-section__head">
						<h2 class="gv2-section__title" id="gv2-related-heading"><?php echo esc_html( gv2_t( 'More Interesting Articles' ) ); ?></h2>
						<a href="<?php echo esc_url( $blog_url ); ?>" class="gv2-link"><?php echo esc_html( gv2_t( 'View All' ) ); ?> &rarr;</a>
					</div>
					<div class="gv2-info-grid-2">
						<?php
						while ( $related->have_posts() ) :
							$related->the_post();
							$r_cats      = get_the_category();
							$r_cat_label = ( ! empty( $r_cats ) && isset( $r_cats[0]->name ) ) ? $r_cats[0]->name : '';
							?>
							<article class="gv2-blog-mini gv2-glass-card">
								<?php if ( has_post_thumbnail() ) : ?>
									<a href="<?php the_permalink(); ?>" class="gv2-blog-mini__media">
										<?php the_post_thumbnail( 'gv2-blog-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
									</a>
								<?php endif; ?>
								<div class="gv2-blog-mini__body">
									<div class="gv2-blog-meta">
										<?php if ( '' !== $r_cat_label ) : ?>
											<span class="gv2-blog-mini__cat"><?php echo esc_html( $r_cat_label ); ?></span>
										<?php endif; ?>
										<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
									</div>
									<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p class="gv2-blog-mini__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 14, '...' ) ); ?></p>
									<a href="<?php the_permalink(); ?>" class="gv2-link"><?php echo esc_html( gv2_t( 'Read Recipe' ) ); ?> &rarr;</a>
								</div>
							</article>
						<?php endwhile; ?>
					</div>
				</div>
			</section>
			<?php wp_reset_postdata(); ?>
		<?php endif; ?>

	<?php endwhile; ?>
</main>

<?php
get_footer();
