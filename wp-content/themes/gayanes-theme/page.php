<?php
/**
 * Standard Page Template (About, Contact, Delivery, FAQ, Privacy, Terms, Cart, Checkout, My Account).
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="gk-main gk-container">
	<?php gayanes_breadcrumbs(); ?>

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'gk-page' ); ?>>
			<header class="gk-page__header">
				<h1 class="gk-page__title"><?php the_title(); ?></h1>
			</header>
			<div class="gk-page__content">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();

