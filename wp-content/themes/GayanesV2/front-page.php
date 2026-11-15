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

	<!-- 3. Product of the Day (Daily Deal Promo) -->
	<?php if ( function_exists( 'wc_get_products' ) ) : ?>
		<?php
		$deal_id      = absint( get_theme_mod( 'gv2_daily_deal_product_id', 0 ) );
		$deal_product = $deal_id > 0 ? wc_get_product( $deal_id ) : null;

		if ( ! $deal_product instanceof WC_Product || 'publish' !== $deal_product->get_status() ) {
			$sale_ids = function_exists( 'wc_get_product_ids_on_sale' ) ? wc_get_product_ids_on_sale() : array();
			if ( ! empty( $sale_ids ) ) {
				$deal_product = wc_get_product( (int) reset( $sale_ids ) );
			}
		}

		if ( ! $deal_product instanceof WC_Product || 'publish' !== $deal_product->get_status() ) {
			$fallback_list = wc_get_products( array( 'status' => 'publish', 'limit' => 1, 'orderby' => 'popularity', 'order' => 'DESC' ) );
			$deal_product  = $fallback_list[0] ?? null;
		}

		if ( $deal_product instanceof WC_Product ) :
			$deal_pid        = $deal_product->get_id();
			$deal_img_id     = (int) $deal_product->get_image_id();
			$deal_weight     = gv2_get_product_weight_label( $deal_product );
			$deal_permalink  = $deal_product->get_permalink();
			$deal_reg        = (float) $deal_product->get_regular_price();
			$deal_sale       = (float) $deal_product->get_sale_price();
			$deal_discount   = ( $deal_product->is_on_sale() && $deal_reg > 0 && $deal_sale > 0 && $deal_sale < $deal_reg )
				? (int) round( ( ( $deal_reg - $deal_sale ) / $deal_reg ) * 100 )
				: 0;
			$deal_qty        = 0;
			$deal_cart_key   = '';
			if ( function_exists( 'WC' ) && WC()->cart && ! $deal_product->is_type( 'variable' ) ) {
				foreach ( WC()->cart->get_cart() as $k => $item ) {
					if ( (int) $item['product_id'] === $deal_pid && 0 === (int) $item['variation_id'] ) {
						$deal_qty      = (int) $item['quantity'];
						$deal_cart_key = (string) $k;
						break;
					}
				}
			}
			?>
			<section class="gv2-section gv2-container" aria-labelledby="gv2-deal-heading">
				<div class="gv2-deal-card gv2-glass-card gv2-card" data-product-id="<?php echo esc_attr( (string) $deal_pid ); ?>">
					<a href="<?php echo esc_url( $deal_permalink ); ?>" class="gv2-deal-card__media">
						<?php
						if ( $deal_img_id ) {
							echo wp_get_attachment_image( $deal_img_id, 'gv2-product-portrait', false, array( 'class' => 'gv2-card__img', 'loading' => 'lazy', 'decoding' => 'async' ) );
						} else {
							echo '<img class="gv2-card__img" src="' . esc_url( wc_placeholder_img_src( 'gv2-product-portrait' ) ) . '" alt="' . esc_attr( $deal_product->get_name() ) . '" loading="lazy" />';
						}
						?>
						<span class="gv2-deal-badge"><?php echo esc_html( gv2_t( 'Deal of the Day' ) ); ?></span>
						<?php if ( $deal_discount > 0 ) : ?>
							<span class="gv2-deal-discount">-<?php echo esc_html( (string) $deal_discount ); ?>%</span>
						<?php endif; ?>
					</a>

					<div class="gv2-deal-card__body">
						<div class="gv2-deal-card__top">
							<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Deal of the Day' ) ); ?></span>
							<div class="gv2-deal-timer" id="gv2-deal-timer" aria-label="<?php echo esc_attr( gv2_t( 'Ends in' ) ); ?>">
								<span class="gv2-deal-timer__label"><?php echo esc_html( gv2_t( 'Ends in' ) ); ?></span>
								<span class="gv2-deal-timer__pills">
									<span data-gv2-cd="h">00</span>:<span data-gv2-cd="m">00</span>:<span data-gv2-cd="s">00</span>
								</span>
							</div>
						</div>

						<h2 class="gv2-deal-card__title" id="gv2-deal-heading">
							<a href="<?php echo esc_url( $deal_permalink ); ?>"><?php echo esc_html( $deal_product->get_name() ); ?></a>
						</h2>

						<?php if ( '' !== $deal_weight ) : ?>
							<span class="gv2-deal-weight"><?php echo esc_html( $deal_weight ); ?></span>
						<?php endif; ?>

						<?php if ( '' !== trim( (string) $deal_product->get_short_description() ) ) : ?>
							<p class="gv2-deal-card__desc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $deal_product->get_short_description() ), 22, '...' ) ); ?></p>
						<?php endif; ?>

						<div class="gv2-deal-card__price">
							<?php echo wp_kses_post( $deal_product->get_price_html() ); ?>
						</div>

						<div class="gv2-deal-card__cta">
							<?php if ( $deal_product->is_in_stock() && $deal_product->is_purchasable() && ! $deal_product->is_type( 'variable' ) ) : ?>
								<button
									type="button"
									class="gv2-btn gv2-btn--primary gv2-btn--lg gv2-add-btn<?php echo $deal_qty > 0 ? ' is-hidden' : ''; ?>"
									data-gv2-add="<?php echo esc_attr( (string) $deal_pid ); ?>"
								>
									<span aria-hidden="true">+</span> <?php echo esc_html( gv2_t( 'Add to Cart' ) ); ?>
								</button>
								<div
									class="gv2-qty<?php echo $deal_qty <= 0 ? ' is-hidden' : ''; ?>"
									data-gv2-qty-control
									data-product-id="<?php echo esc_attr( (string) $deal_pid ); ?>"
									data-cart-item-key="<?php echo esc_attr( $deal_cart_key ); ?>"
								>
									<button type="button" class="gv2-qty__btn" data-gv2-qty-step="-1" aria-label="<?php echo esc_attr( gv2_t( 'Decrease quantity' ) ); ?>">&minus;</button>
									<span class="gv2-qty__val" data-gv2-qty-val><?php echo esc_html( (string) max( 0, $deal_qty ) ); ?></span>
									<button type="button" class="gv2-qty__btn" data-gv2-qty-step="1" aria-label="<?php echo esc_attr( gv2_t( 'Increase quantity' ) ); ?>">+</button>
								</div>
							<?php endif; ?>
							<a href="<?php echo esc_url( $deal_permalink ); ?>" class="gv2-btn gv2-btn--glass gv2-btn--lg">
								<?php echo esc_html( gv2_t( 'View Dish' ) ); ?>
							</a>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<!-- 4. Best Sellers (2:3 Portrait Cards) -->
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

	<!-- 6. Partners Section (iOS 27 Liquid Glass Showcase Band) -->
	<?php $partners = gv2_get_partners(); ?>
	<section class="gv2-section gv2-partners-band" aria-labelledby="gv2-partners-heading">
		<div class="gv2-container">
			<div class="gv2-partners-band__head">
				<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Our Partners' ) ); ?></span>
				<h2 class="gv2-section__title" id="gv2-partners-heading"><?php echo esc_html( gv2_t( 'Our Partners' ) ); ?></h2>
			</div>
			<div class="gv2-partners-logos">
				<?php foreach ( $partners as $partner ) : ?>
					<?php
					$is_dark = ! empty( $partner['dark'] );
					$url     = $partner['url'] ?: '#';
					?>
					<a
						href="<?php echo esc_url( $url ); ?>"
						class="gv2-partner-logo gv2-glass-card<?php echo $is_dark ? ' gv2-partner-logo--dark' : ''; ?>"
						aria-label="<?php echo esc_attr( $partner['name'] ); ?>"
						<?php echo ( '#' !== $url ) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
					>
						<?php if ( ! empty( $partner['logo'] ) ) : ?>
							<img src="<?php echo esc_url( $partner['logo'] ); ?>" alt="<?php echo esc_attr( $partner['name'] ); ?>" loading="lazy" decoding="async" />
						<?php else : ?>
							<span class="gv2-partner-logo__wordmark"><?php echo esc_html( $partner['name'] ); ?></span>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- 7. Blog & Recipes Section (Editorial Journal Band) -->
	<?php
	$posts_query = new WP_Query(
		array(
			'post_type'      => 'post',
			'posts_per_page' => 3,
			'post_status'    => 'publish',
		)
	);
	?>
	<section class="gv2-section gv2-blog-band" aria-labelledby="gv2-blog-heading">
		<div class="gv2-container">
			<div class="gv2-section__head gv2-blog-band__head">
				<div>
					<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Our Story' ) ); ?> &bull; <?php echo esc_html( gv2_t( 'Blog & Recipes' ) ); ?></span>
					<h2 class="gv2-section__title" id="gv2-blog-heading"><?php echo esc_html( gv2_t( 'Blog & Recipes' ) ); ?></h2>
				</div>
				<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="gv2-btn gv2-btn--glass"><?php echo esc_html( gv2_t( 'View All' ) ); ?> &rarr;</a>
			</div>

			<?php if ( $posts_query->have_posts() ) : ?>
				<?php $blog_posts = $posts_query->posts; ?>
				<div class="gv2-blog-bento">
					<?php
					foreach ( $blog_posts as $b_idx => $post_obj ) :
						setup_postdata( $GLOBALS['post'] = $post_obj );
						$cats      = get_the_category( $post_obj->ID );
						$cat_label = ( ! empty( $cats ) && isset( $cats[0]->name ) ) ? $cats[0]->name : '';
						if ( 0 === $b_idx ) :
							?>
							<article class="gv2-blog-featured gv2-glass-card">
								<?php if ( has_post_thumbnail() ) : ?>
									<a href="<?php the_permalink(); ?>" class="gv2-blog-featured__media">
										<?php the_post_thumbnail( 'large', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
										<?php if ( '' !== $cat_label ) : ?>
											<span class="gv2-blog-badge"><?php echo esc_html( $cat_label ); ?></span>
										<?php endif; ?>
									</a>
								<?php endif; ?>
								<div class="gv2-blog-featured__body">
									<div class="gv2-blog-meta">
										<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
									</div>
									<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p class="gv2-blog-featured__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 26, '...' ) ); ?></p>
									<a href="<?php the_permalink(); ?>" class="gv2-btn gv2-btn--primary gv2-blog-featured__cta">
										<?php echo esc_html( gv2_t( 'Read Recipe' ) ); ?> &rarr;
									</a>
								</div>
							</article>
							<div class="gv2-blog-stack">
						<?php else : ?>
							<article class="gv2-blog-mini gv2-glass-card">
								<?php if ( has_post_thumbnail() ) : ?>
									<a href="<?php the_permalink(); ?>" class="gv2-blog-mini__media">
										<?php the_post_thumbnail( 'gv2-blog-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
									</a>
								<?php endif; ?>
								<div class="gv2-blog-mini__body">
									<div class="gv2-blog-meta">
										<?php if ( '' !== $cat_label ) : ?>
											<span class="gv2-blog-mini__cat"><?php echo esc_html( $cat_label ); ?></span>
										<?php endif; ?>
										<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
									</div>
									<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
									<p class="gv2-blog-mini__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 14, '...' ) ); ?></p>
									<a href="<?php the_permalink(); ?>" class="gv2-link"><?php echo esc_html( gv2_t( 'Read Recipe' ) ); ?> &rarr;</a>
								</div>
							</article>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php if ( count( $blog_posts ) > 0 ) : ?>
						</div>
					<?php endif; ?>
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
		</div>
	</section>

	<!-- 8.5. Featured FAQs (Quick Answers) -->
	<?php if ( function_exists( 'gv2_get_faqs' ) ) : ?>
		<?php $featured_faqs = gv2_get_faqs( true ); ?>
		<?php if ( ! empty( $featured_faqs ) ) : ?>
			<section class="gv2-section gv2-container" aria-labelledby="gv2-home-faq-heading">
				<div class="gv2-section__head">
					<div>
						<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Questions & Answers' ) ); ?></span>
						<h2 class="gv2-section__title" id="gv2-home-faq-heading"><?php echo esc_html( gv2_t( 'Frequently Asked Questions' ) ); ?></h2>
					</div>
					<a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>" class="gv2-link"><?php echo esc_html( gv2_t( 'View All FAQs' ) ); ?> &rarr;</a>
				</div>
				<div class="gv2-faq-list gv2-faq-list--compact">
					<?php foreach ( $featured_faqs as $idx => $faq ) : ?>
						<details class="gv2-faq-item gv2-glass-card"<?php echo 0 === $idx ? ' open' : ''; ?>>
							<summary class="gv2-faq-item__summary">
								<h3 class="gv2-faq-item__q"><?php echo esc_html( $faq['question'] ); ?></h3>
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
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<!-- 9. Newsletter -->
	<section class="gv2-section gv2-container">
		<div class="gv2-newsletter">
			<div class="gv2-newsletter__copy">
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

