<?php
/**
 * Homepage Template: Premium Minimal Food eCommerce.
 *
 * Sections:
 * 1. Hero with food photography
 * 2. Main product categories
 * 3. Best Sellers
 * 4. Featured Products
 * 5. New Arrivals
 * 6. Brand Benefits
 * 7. About Us
 * 8. Customer Reviews
 * 9. Blog / Recipes
 * 10. Newsletter subscription
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>

<main id="primary" class="gk-main">

	<!-- 1. Hero Section -->
	<section class="gk-hero">
		<div class="gk-container gk-hero__grid">
			<div class="gk-hero__content">
				<span class="gk-eyebrow"><?php echo esc_html( gayanes_t( 'Homemade Armenian Semi-Finished Food' ) ); ?></span>
				<h1 class="gk-hero__title">
					<?php echo esc_html( gayanes_t( 'Authentic Armenian Kitchen, Ready in Minutes' ) ); ?>
				</h1>
				<p class="gk-hero__subtitle">
					<?php echo esc_html( gayanes_t( 'Fresh ingredients, traditional recipes, delivered to your door.' ) ); ?>
				</p>
				<div class="gk-hero__cta">
					<a href="<?php echo esc_url( $shop_url ); ?>" class="gk-btn gk-btn--primary">
						<?php echo esc_html( gayanes_t( 'Shop All' ) ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="gk-btn gk-btn--outline">
						<?php echo esc_html( gayanes_t( 'About Us' ) ); ?>
					</a>
				</div>
			</div>

			<div class="gk-hero__media">
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail(
						'large',
						array(
							'class'         => 'gk-hero__img',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'decoding'      => 'async',
						)
					);
				} else {
					?>
					<div class="gk-hero__placeholder" role="img" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						<span><?php bloginfo( 'name' ); ?></span>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</section>

	<!-- 2. Main Product Categories -->
	<?php if ( taxonomy_exists( 'product_cat' ) ) : ?>
		<?php
		$categories = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => 0,
				'number'     => 8,
			)
		);
		if ( is_array( $categories ) && ! empty( $categories ) ) :
			?>
			<section class="gk-section gk-container">
				<div class="gk-section__head">
					<h2 class="gk-section__title"><?php echo esc_html( gayanes_t( 'Categories' ) ); ?></h2>
					<a href="<?php echo esc_url( $shop_url ); ?>" class="gk-link"><?php echo esc_html( gayanes_t( 'Shop All' ) ); ?> &rarr;</a>
				</div>
				<div class="gk-cat-grid">
					<?php foreach ( $categories as $cat ) : ?>
						<?php
						$thumb_id = (int) get_term_meta( $cat->term_id, 'thumbnail_id', true );
						$cat_link = get_term_link( $cat );
						?>
						<a href="<?php echo esc_url( is_wp_error( $cat_link ) ? $shop_url : $cat_link ); ?>" class="gk-cat-card">
							<div class="gk-cat-card__media">
								<?php if ( $thumb_id ) : ?>
									<?php echo wp_get_attachment_image( $thumb_id, 'woocommerce_thumbnail', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
								<?php else : ?>
									<span class="gk-cat-card__fallback" aria-hidden="true"><?php echo esc_html( mb_substr( $cat->name, 0, 1 ) ); ?></span>
								<?php endif; ?>
							</div>
							<span class="gk-cat-card__name"><?php echo esc_html( $cat->name ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<!-- 3. Best Sellers -->
	<?php if ( function_exists( 'wc_get_products' ) ) : ?>
		<?php
		$best_sellers = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 8,
				'orderby' => 'popularity',
				'order'   => 'DESC',
			)
		);
		if ( ! empty( $best_sellers ) ) :
			?>
			<section class="gk-section gk-container">
				<div class="gk-section__head">
					<h2 class="gk-section__title"><?php echo esc_html( gayanes_t( 'Best Sellers' ) ); ?></h2>
					<a href="<?php echo esc_url( add_query_arg( 'orderby', 'popularity', $shop_url ) ); ?>" class="gk-link"><?php echo esc_html( gayanes_t( 'View All' ) ); ?> &rarr;</a>
				</div>
				<div class="gk-product-grid">
					<?php
					foreach ( $best_sellers as $product ) {
						gayanes_render_product_card( $product );
					}
					?>
				</div>
			</section>
		<?php endif; ?>

		<!-- 4. Featured Products -->
		<?php
		$featured = wc_get_products(
			array(
				'status'   => 'publish',
				'limit'    => 4,
				'featured' => true,
			)
		);
		if ( ! empty( $featured ) ) :
			?>
			<section class="gk-section gk-container">
				<div class="gk-section__head">
					<h2 class="gk-section__title"><?php echo esc_html( gayanes_t( 'Featured Products' ) ); ?></h2>
				</div>
				<div class="gk-product-grid">
					<?php
					foreach ( $featured as $product ) {
						gayanes_render_product_card( $product );
					}
					?>
				</div>
			</section>
		<?php endif; ?>

		<!-- 5. New Arrivals -->
		<?php
		$new_arrivals = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 4,
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);
		if ( ! empty( $new_arrivals ) ) :
			?>
			<section class="gk-section gk-container">
				<div class="gk-section__head">
					<h2 class="gk-section__title"><?php echo esc_html( gayanes_t( 'New Arrivals' ) ); ?></h2>
				</div>
				<div class="gk-product-grid">
					<?php
					foreach ( $new_arrivals as $product ) {
						gayanes_render_product_card( $product );
					}
					?>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<!-- 6. Brand Benefits -->
	<section class="gk-section gk-section--surface">
		<div class="gk-container gk-benefits">
			<div class="gk-benefit">
				<h3><?php echo esc_html( gayanes_t( '100% Natural Ingredients' ) ); ?></h3>
				<p><?php echo esc_html( gayanes_t( 'No artificial preservatives, prepared fresh daily in small batches.' ) ); ?></p>
			</div>
			<div class="gk-benefit">
				<h3><?php echo esc_html( gayanes_t( 'Traditional Recipes' ) ); ?></h3>
				<p><?php echo esc_html( gayanes_t( 'Authentic Armenian homemade flavors crafted with care.' ) ); ?></p>
			</div>
			<div class="gk-benefit">
				<h3><?php echo esc_html( gayanes_t( 'Fast Chilled Delivery' ) ); ?></h3>
				<p><?php echo esc_html( gayanes_t( 'Reliable same-day and next-day delivery across Yerevan.' ) ); ?></p>
			</div>
		</div>
	</section>

	<!-- 7. About Us -->
	<section class="gk-section gk-container gk-about-band">
		<div class="gk-about-band__inner">
			<span class="gk-eyebrow"><?php echo esc_html( gayanes_t( 'Our Story' ) ); ?></span>
			<h2><?php echo esc_html( gayanes_t( 'About Us' ) ); ?></h2>
			<p>
				<?php echo esc_html( gayanes_t( 'Gayane’s Kitchen brings homemade Armenian dolma, pelmeni, khinkali, blini, and seasonal delicacies straight from our kitchen to your family table.' ) ); ?>
			</p>
			<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="gk-btn gk-btn--outline">
				<?php echo esc_html( gayanes_t( 'Learn More' ) ); ?>
			</a>
		</div>
	</section>

	<!-- 8. Customer Reviews -->
	<section class="gk-section gk-container">
		<div class="gk-section__head">
			<h2 class="gk-section__title"><?php echo esc_html( gayanes_t( 'Customer Reviews' ) ); ?></h2>
		</div>
		<div class="gk-reviews-grid">
			<blockquote class="gk-review">
				<p>&ldquo;<?php echo esc_html( gayanes_t( 'The homemade dolma and khinkali taste just like family cooking. Delivery was fast and carefully packed.' ) ); ?>&rdquo;</p>
				<footer>&mdash; Ani M.</footer>
			</blockquote>
			<blockquote class="gk-review">
				<p>&ldquo;<?php echo esc_html( gayanes_t( 'Clean ingredients and consistent quality every single order. Our go-to for busy weekdays.' ) ); ?>&rdquo;</p>
				<footer>&mdash; Arman K.</footer>
			</blockquote>
			<blockquote class="gk-review">
				<p>&ldquo;<?php echo esc_html( gayanes_t( 'Great vegan and fasting options as well. Highly recommended!' ) ); ?>&rdquo;</p>
				<footer>&mdash; Lusine S.</footer>
			</blockquote>
		</div>
	</section>

	<!-- 9. Blog / Recipes -->
	<?php
	$posts_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'post_status'    => 'publish',
		)
	);
	if ( $posts_query->have_posts() ) :
		?>
		<section class="gk-section gk-container">
			<div class="gk-section__head">
				<h2 class="gk-section__title"><?php echo esc_html( gayanes_t( 'Blog & Recipes' ) ); ?></h2>
				<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="gk-link"><?php echo esc_html( gayanes_t( 'View All' ) ); ?> &rarr;</a>
			</div>
			<div class="gk-blog-grid">
				<?php
				while ( $posts_query->have_posts() ) :
					$posts_query->the_post();
					?>
					<article class="gk-post-card">
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="gk-post-card__media">
								<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
							</a>
						<?php endif; ?>
						<div class="gk-post-card__body">
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
		</section>
		<?php
		wp_reset_postdata();
	endif;
	?>

	<!-- 10. Newsletter -->
	<section class="gk-section gk-container">
		<div class="gk-newsletter">
			<div>
				<h2><?php echo esc_html( gayanes_t( 'Seasonal Menu & Offers' ) ); ?></h2>
				<p><?php echo esc_html( gayanes_t( 'Receive updates on new homemade dishes and seasonal specials.' ) ); ?></p>
			</div>
			<form class="gk-newsletter__form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="post">
				<label class="gk-sr-only" for="gk-newsletter-email"><?php echo esc_html( gayanes_t( 'Email address' ) ); ?></label>
				<input id="gk-newsletter-email" type="email" name="email" required placeholder="name@example.com" />
				<button type="submit" class="gk-btn gk-btn--primary"><?php echo esc_html( gayanes_t( 'Subscribe' ) ); ?></button>
			</form>
		</div>
	</section>

</main>

<?php
get_footer();

