<?php
/**
 * Standard page template (also hosts WooCommerce Cart, Checkout, My Account).
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
		<article <?php post_class( 'gv2-page' ); ?>>
			<header class="gv2-page-head">
				<h1><?php the_title(); ?></h1>
			</header>
			<div class="gv2-prose">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();

