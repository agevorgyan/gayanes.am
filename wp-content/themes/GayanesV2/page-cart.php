<?php
/**
 * Cart page template — iOS 27 Liquid Glass Checkout Flow.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="primary" class="gv2-main gv2-container gv2-flow-page">
	<?php
	while ( have_posts() ) :
		the_post();
		gv2_render_commerce_flow_header( 'cart' );
		?>
		<div class="gv2-flow-content">
			<?php the_content(); ?>
		</div>
		<?php
		gv2_render_commerce_trust_bar();
	endwhile;
	?>
</main>

<?php
get_footer();
