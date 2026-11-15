<?php
/**
 * GayanesV2 Homepage Template: Mobile-first, iOS 27 Liquid Glass, SEO-optimized.
 *
 * Sections:
 * 1. Editorial Hero with arched food frame & trust pills
 * 2. Quick Product Categories (horizontal scroll on mobile)
 * 3. Best Sellers (2:3 portrait cards, instant +/- steppers)
 * 4. New Arrivals
 * 5. Crafted Like Home (Brand Story & 3 Pillars)
 * 6. Partners Section
 * 7. Blog & Recipes Section
 * 8. Customer Reviews & Newsletter
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>

<main id="primary" class="gv2-main">

	<!-- 1. Editorial Hero -->
	<section class="gv2-hero">
		<div class="gv2-container gv2-hero__grid">
			<div class="gv2-hero__media">
				<div class="gv2-hero__arch">
					<?php
					if ( has_post_thumbnail() ) {
						the_post_thumbnail(
							'large',
							array(
								'class'         => 'gv2-hero__img',
								'loading'       => 'eager',
								'fetchpriority' => 'high',
								'decoding'      => 'async',
							)
						);
					} else {
						?>
						<div class="gv2-hero__placeholder" role="img" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
							<img src="<?php echo esc_url( GV2_URI . '/assets/images/logo-dark.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
						</div>
						<?php
					}
					?>
				</div>
			</div>

			<div class="gv2-hero__content">
				<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Homemade Armenian Semi-Finished Food' ) ); ?></span>
				<h1 class="gv2-hero__title">
					<?php echo esc_html( gv2_t( 'Authentic Armenian Dishes, Ready in Minutes' ) ); ?>
				</h1>
				<p class="gv2-hero__subtitle">
					<?php echo esc_html( gv2_t( 'Handcrafted daily with 100% natural ingredients and delivered chilled to your door.' ) ); ?>
				</p>
				<div class="gv2-hero__cta">
					<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--primary gv2-btn--lg">
						<?php echo esc_html( gv2_t( 'Explore Menu' ) ); ?>
					</a>
					<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="gv2-btn gv2-btn--glass gv2-btn--lg">
						<?php echo esc_html( gv2_t( 'Our Story' ) ); ?>
					</a>
				</div>
				<div class="gv2-trust-row">
					<span class="gv2-trust-pill"><?php echo esc_html( gv2_t( '100% Natural' ) ); ?></span>
					<span class="gv2-trust-pill"><?php echo esc_html( gv2_t( 'No Preservatives' ) ); ?></span>
					<span class="gv2-trust-pill"><?php echo esc_html( gv2_t( 'Chilled Delivery' ) ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<!-- 2. Quick Product Categories -->
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
			<section class="gv2-section gv2-container">
				<div class="gv2-section__head">
					<h2 class="gv2-section__title"><?php echo esc_html( gv2_t( 'Categories' ) ); ?></h2>
					<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-link"><?php echo esc_html( gv2_t( 'View All' ) ); ?> &rarr;</a>
				</div>
				<div class="gv2-cat-rail">
					<?php foreach ( $categories as $cat ) : ?>
						<?php
						$thumb_id = (int) get_term_meta( $cat->term_id, 'thumbnail_id', true );
						$cat_link = get_term_link( $cat );
						?>
						<a href="<?php echo esc_url( is_wp_error( $cat_link ) ? $shop_url : $cat_link ); ?>" class="gv2-cat-chip">
							<div class="gv2-cat-chip__media">
								<?php if ( $thumb_id ) : ?>
									<?php echo wp_get_attachment_image( $thumb_id, 'thumbnail', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
								<?php else : ?>
									<span class="gv2-cat-chip__fallback" aria-hidden="true"><?php echo esc_html( mb_substr( $cat->name, 0, 1 ) ); ?></span>
								<?php endif; ?>
							</div>
							<span class="gv2-cat-chip__name"><?php echo esc_html( $cat->name ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<!-- 3. Best Sellers (2:3 Portrait Cards) -->
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
			<section class="gv2-section gv2-container">
				<div class="gv2-section__head">
					<h2 class="gv2-section__title"><?php echo esc_html( gv2_t( 'Best Sellers' ) ); ?></h2>
					<a href="<?php echo esc_url( add_query_arg( 'orderby', 'popularity', $shop_url ) ); ?>" class="gv2-link"><?php echo esc_html( gv2_t( 'View All' ) ); ?> &rarr;</a>
				</div>
				<div class="gv2-product-grid">
					<?php
					foreach ( $best_sellers as $product ) {
						gv2_render_product_card( $product );
					}
					?>
				</div>
			</section>
		<?php endif; ?>

		<!-- 4. New Arrivals -->
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
			<section class="gv2-section gv2-container">
				<div class="gv2-section__head">
					<h2 class="gv2-section__title"><?php echo esc_html( gv2_t( 'New Arrivals' ) ); ?></h2>
					<a href="<?php echo esc_url( add_query_arg( 'orderby', 'date', $shop_url ) ); ?>" class="gv2-link"><?php echo esc_html( gv2_t( 'View All' ) ); ?> &rarr;</a>
				</div>
				<div class="gv2-product-grid">
					<?php
					foreach ( $new_arrivals as $product ) {
						gv2_render_product_card( $product );
					}
					?>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<!-- 5. Brand Story: Crafted Like Home -->
	<section class="gv2-section gv2-story-band">
		<div class="gv2-container gv2-story-band__inner">
			<div class="gv2-story-band__copy">
				<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Our Story' ) ); ?></span>
				<h2><?php echo esc_html( gv2_t( 'Crafted Like Home' ) ); ?></h2>
				<p><?php echo esc_html( gv2_t( 'Gayane’s Kitchen brings homemade Armenian dolma, pelmeni, khinkali, blini, and seasonal delicacies straight from our kitchen to your family table.' ) ); ?></p>
				<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="gv2-btn gv2-btn--primary">
					<?php echo esc_html( gv2_t( 'Learn More' ) ); ?>
				</a>
			</div>
			<div class="gv2-pillars">
				<div class="gv2-pillar gv2-glass-card">
					<h3><?php echo esc_html( gv2_t( 'Fresh Local Ingredients' ) ); ?></h3>
					<p><?php echo esc_html( gv2_t( 'Handcrafted daily with 100% natural ingredients and delivered chilled to your door.' ) ); ?></p>
				</div>
				<div class="gv2-pillar gv2-glass-card">
					<h3><?php echo esc_html( gv2_t( 'Family Recipes' ) ); ?></h3>
					<p><?php echo esc_html( gv2_t( 'Authentic Armenian Dishes, Ready in Minutes' ) ); ?></p>
				</div>
				<div class="gv2-pillar gv2-glass-card">
					<h3><?php echo esc_html( gv2_t( 'Same-Day Yerevan Delivery' ) ); ?></h3>
					<p><?php echo esc_html( gv2_t( 'Free delivery in Yerevan on orders over 15,000 AMD' ) ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- 6. Partners Section -->
	<?php $partners = gv2_get_partners(); ?>
	<section class="gv2-section gv2-container" aria-labelledby="gv2-partners-heading">
		<div class="gv2-section__head">
			<div>
				<h2 class="gv2-section__title" id="gv2-partners-heading"><?php echo esc_html( gv2_t( 'Our Partners' ) ); ?></h2>
				<p class="gv2-section__sub"><?php echo esc_html( gv2_t( 'Trusted by leading supermarkets, restaurants, and local producers across Armenia.' ) ); ?></p>
			</div>
		</div>
		<div class="gv2-partners-grid">
			<?php foreach ( $partners as $partner ) : ?>
				<a
					href="<?php echo esc_url( $partner['url'] ?: '#' ); ?>"
					class="gv2-partner-card gv2-glass-card"
					<?php echo ( '#' !== ( $partner['url'] ?: '#' ) ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
				>
					<span class="gv2-partner-card__badge" aria-hidden="true"><?php echo esc_html( mb_substr( $partner['name'], 0, 2 ) ); ?></span>
					<span class="gv2-partner-card__name"><?php echo esc_html( $partner['name'] ); ?></span>
					<?php if ( '' !== $partner['subtitle'] ) : ?>
						<span class="gv2-partner-card__sub"><?php echo esc_html( $partner['subtitle'] ); ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- 7. Blog & Recipes Section -->
	<?php
	$posts_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'post_status'    => 'publish',
		)
	);
	?>
	<section class="gv2-section gv2-container" aria-labelledby="gv2-blog-heading">
		<div class="gv2-section__head">
			<h2 class="gv2-section__title" id="gv2-blog-heading"><?php echo esc_html( gv2_t( 'Blog & Recipes' ) ); ?></h2>
			<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="gv2-link"><?php echo esc_html( gv2_t( 'View All' ) ); ?> &rarr;</a>
		</div>
		<?php if ( $posts_query->have_posts() ) : ?>
			<div class="gv2-blog-grid">
				<?php
				while ( $posts_query->have_posts() ) :
					$posts_query->the_post();
					?>
					<article class="gv2-post-card gv2-glass-card">
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="gv2-post-card__media">
								<?php the_post_thumbnail( 'gv2-blog-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
							</a>
						<?php endif; ?>
						<div class="gv2-post-card__body">
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="gv2-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 16, '...' ) ); ?></p>
							<a href="<?php the_permalink(); ?>" class="gv2-link"><?php echo esc_html( gv2_t( 'Read Recipe' ) ); ?> &rarr;</a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<div class="gv2-blog-grid">
				<article class="gv2-post-card gv2-glass-card">
					<div class="gv2-post-card__body">
						<h3><?php echo esc_html( gv2_t( 'Authentic Armenian Dishes, Ready in Minutes' ) ); ?></h3>
						<p class="gv2-post-card__excerpt"><?php echo esc_html( gv2_t( 'Gayane’s Kitchen brings homemade Armenian dolma, pelmeni, khinkali, blini, and seasonal delicacies straight from our kitchen to your family table.' ) ); ?></p>
					</div>
				</article>
			</div>
		<?php endif; ?>
	</section>

	<!-- 8. Customer Reviews & Newsletter -->
	<section class="gv2-section gv2-container">
		<div class="gv2-section__head">
			<h2 class="gv2-section__title"><?php echo esc_html( gv2_t( 'Customer Reviews' ) ); ?></h2>
		</div>
		<div class="gv2-reviews-grid">
			<blockquote class="gv2-review gv2-glass-card">
				<p>&ldquo;Տնական տոլման և խինկալին ուղղակի անհավանական համեղ են, ճիշտ տատիկի պատրաստածի նման։&rdquo;</p>
				<footer>&mdash; Անի Մ.</footer>
			</blockquote>
			<blockquote class="gv2-review gv2-glass-card">
				<p>&ldquo;Մաքուր բաղադրություն և արագ առաքում Երևանում։ Մեր ընտանիքի ամենասիրելի ընտրությունն է։&rdquo;</p>
				<footer>&mdash; Արման Կ.</footer>
			</blockquote>
			<blockquote class="gv2-review gv2-glass-card">
				<p>&ldquo;Հրաշալի պահքի և վեգան տարբերակներ ունեն։ Անպայման խորհուրդ եմ տալիս։&rdquo;</p>
				<footer>&mdash; Լուսինե Ս.</footer>
			</blockquote>
		</div>
	</section>

	<section class="gv2-section gv2-container">
		<div class="gv2-newsletter gv2-glass-card">
			<div>
				<h2><?php echo esc_html( gv2_t( 'Seasonal Menu & Offers' ) ); ?></h2>
				<p><?php echo esc_html( gv2_t( 'Receive updates on new homemade dishes and seasonal specials.' ) ); ?></p>
			</div>
			<form class="gv2-newsletter__form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="post">
				<label class="gv2-sr-only" for="gv2-newsletter-email"><?php echo esc_html( gv2_t( 'Email address' ) ); ?></label>
				<input id="gv2-newsletter-email" type="email" name="email" required placeholder="name@example.com" />
				<button type="submit" class="gv2-btn gv2-btn--primary"><?php echo esc_html( gv2_t( 'Subscribe' ) ); ?></button>
			</form>
		</div>
	</section>

</main>

<?php
get_footer();

