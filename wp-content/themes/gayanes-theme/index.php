<?php
/**
 * Main fallback & Blog / Recipes archive template.
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
			<?php echo is_home() && ! is_front_page() ? esc_html( single_post_title( '', false ) ) : esc_html( gayanes_t( 'Blog & Recipes' ) ); ?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="gk-blog-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'gk-post-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>" class="gk-post-card__media">
							<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
						</a>
					<?php endif; ?>
					<div class="gk-post-card__body">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<div class="gk-pagination">
			<?php the_posts_pagination(); ?>
		</div>
	<?php else : ?>
		<p><?php echo esc_html( gayanes_t( 'No posts found.' ) ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();

