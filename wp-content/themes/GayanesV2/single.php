<?php
/**
 * Single post / recipe template.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="gv2-main gv2-container">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'gv2-single-article gv2-glass-card' ); ?>>
			<header class="gv2-single-article__head">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<h1><?php the_title(); ?></h1>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="gv2-single-article__media">
					<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
				</div>
			<?php endif; ?>
			<div class="gv2-prose">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();

