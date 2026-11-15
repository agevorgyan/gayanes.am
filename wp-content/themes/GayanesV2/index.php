<?php
/**
 * Main blog / archive template.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="gv2-main gv2-container">
	<header class="gv2-page-head">
		<h1><?php echo is_home() ? esc_html( gv2_t( 'Blog & Recipes' ) ) : esc_html( get_the_archive_title() ); ?></h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="gv2-blog-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'gv2-post-card gv2-glass-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>" class="gv2-post-card__media">
							<?php the_post_thumbnail( 'gv2-blog-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
						</a>
					<?php endif; ?>
					<div class="gv2-post-card__body">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="gv2-post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '...' ) ); ?></p>
						<a href="<?php the_permalink(); ?>" class="gv2-link"><?php echo esc_html( gv2_t( 'Read Recipe' ) ); ?> &rarr;</a>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p class="gv2-muted"><?php echo esc_html( gv2_t( 'No dishes found.' ) ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();

