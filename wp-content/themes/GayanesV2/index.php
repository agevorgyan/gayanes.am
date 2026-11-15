<?php
/**
 * Main blog & category archive template — iOS 27 Liquid Glass Culinary Journal.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$blog_url    = home_url( '/blog/' );
$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$current_cat = is_category() ? (int) get_queried_object_id() : 0;
$post_cats   = get_categories(
	array(
		'hide_empty' => true,
		'exclude'    => array( 1 ), // Exclude default Uncategorized if empty/unused.
	)
);
?>

<main id="primary" class="gv2-main gv2-blog-archive-page">

	<!-- 1. Editorial Journal Hero + Category Pills -->
	<section class="gv2-blog-archive-hero">
		<div class="gv2-container">
			<nav class="gv2-about-breadcrumb" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( gv2_t( 'Home' ) ); ?></a>
				<span aria-hidden="true">/</span>
				<?php if ( is_category() ) : ?>
					<a href="<?php echo esc_url( $blog_url ); ?>"><?php echo esc_html( gv2_t( 'Blog & Recipes' ) ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php single_cat_title(); ?></span>
				<?php else : ?>
					<span aria-current="page"><?php echo esc_html( gv2_t( 'Blog & Recipes' ) ); ?></span>
				<?php endif; ?>
			</nav>

			<div class="gv2-blog-archive-hero__inner">
				<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Our Story' ) ); ?> &bull; <?php echo esc_html( gv2_t( 'Blog & Recipes' ) ); ?></span>
				<h1 class="gv2-blog-archive-hero__title">
					<?php echo is_home() ? esc_html( gv2_t( 'Blog & Recipes' ) ) : wp_kses_post( get_the_archive_title() ); ?>
				</h1>
				<p class="gv2-blog-archive-hero__lead">
					Տնական բաղադրատոմսեր, պատրաստման գաղտնիքներ և առողջ սննդի խորհուրդներ «Գայանեի Խոհանոց»-ից։
				</p>

				<?php if ( ! empty( $post_cats ) ) : ?>
					<nav class="gv2-faq-cats gv2-blog-cats" aria-label="<?php echo esc_attr( gv2_t( 'Categories' ) ); ?>">
						<a href="<?php echo esc_url( $blog_url ); ?>" class="gv2-faq-cat-pill<?php echo 0 === $current_cat ? ' is-active' : ''; ?>">
							<?php echo esc_html( gv2_t( 'All' ) ); ?>
						</a>
						<?php foreach ( $post_cats as $cat ) : ?>
							<a
								href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
								class="gv2-faq-cat-pill<?php echo $current_cat === (int) $cat->term_id ? ' is-active' : ''; ?>"
							>
								<?php echo esc_html( $cat->name ); ?>
							</a>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- 2. Spotlight Post + Posts Grid -->
	<section class="gv2-section gv2-container">
		<?php if ( have_posts() ) : ?>
			<?php
			$post_index     = 0;
			$is_first_paged = ! is_paged();
			$opened_grid    = false;

			while ( have_posts() ) :
				the_post();
				$pid        = get_the_ID();
				$cats       = get_the_category( $pid );
				$cat_label  = ( ! empty( $cats ) && isset( $cats[0]->name ) ) ? $cats[0]->name : '';
				$word_count = str_word_count( wp_strip_all_tags( get_the_content() ) );
				$read_mins  = max( 2, (int) ceil( $word_count / 180 ) );

				if ( 0 === $post_index && $is_first_paged ) :
					?>
					<article <?php post_class( 'gv2-blog-spotlight gv2-glass-card' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="gv2-blog-spotlight__media">
								<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async' ) ); ?>
								<?php if ( '' !== $cat_label ) : ?>
									<span class="gv2-blog-badge"><?php echo esc_html( $cat_label ); ?></span>
								<?php endif; ?>
							</a>
						<?php endif; ?>
						<div class="gv2-blog-spotlight__body">
							<div class="gv2-blog-meta">
								<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								<span aria-hidden="true">&bull;</span>
								<span><?php echo esc_html( (string) $read_mins ); ?> րոպե ընթերցում</span>
							</div>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="gv2-blog-spotlight__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30, '...' ) ); ?></p>
							<a href="<?php the_permalink(); ?>" class="gv2-btn gv2-btn--primary gv2-blog-spotlight__cta">
								<?php echo esc_html( gv2_t( 'Read Recipe' ) ); ?> &rarr;
							</a>
						</div>
					</article>
					<?php
				else :
					if ( ! $opened_grid ) :
						$opened_grid = true;
						echo '<div class="gv2-blog-archive-grid">';
					endif;
					?>
					<article <?php post_class( 'gv2-blog-card gv2-glass-card' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>" class="gv2-blog-card__media">
								<?php the_post_thumbnail( 'gv2-blog-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
								<?php if ( '' !== $cat_label ) : ?>
									<span class="gv2-blog-badge"><?php echo esc_html( $cat_label ); ?></span>
								<?php endif; ?>
							</a>
						<?php endif; ?>
						<div class="gv2-blog-card__body">
							<div class="gv2-blog-meta">
								<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								<span aria-hidden="true">&bull;</span>
								<span><?php echo esc_html( (string) $read_mins ); ?> րոպե</span>
							</div>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p class="gv2-blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '...' ) ); ?></p>
							<a href="<?php the_permalink(); ?>" class="gv2-link"><?php echo esc_html( gv2_t( 'Read Recipe' ) ); ?> &rarr;</a>
						</div>
					</article>
					<?php
				endif;

				$post_index++;
			endwhile;

			if ( $opened_grid ) {
				echo '</div>';
			}
			?>

			<div class="gv2-blog-pagination">
				<?php the_posts_pagination(); ?>
			</div>
		<?php else : ?>
			<div class="gv2-faq-empty gv2-glass-card">
				<p><?php echo esc_html( gv2_t( 'No dishes found.' ) ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<!-- 3. Warm CTA Band -->
	<section class="gv2-section gv2-container">
		<div class="gv2-about-cta">
			<div class="gv2-about-cta__copy">
				<h2><?php echo esc_html( gv2_t( 'Ready for a Homemade Dinner in Minutes?' ) ); ?></h2>
				<p><?php echo esc_html( gv2_t( 'Handcrafted daily with 100% natural ingredients and delivered chilled to your door.' ) ); ?></p>
			</div>
			<div class="gv2-about-cta__actions">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-light">
					<?php echo esc_html( gv2_t( 'Explore Menu' ) ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>" class="gv2-btn gv2-btn--lg gv2-about-cta__btn-outline">
					<?php echo esc_html( gv2_t( 'About Us' ) ); ?>
				</a>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
