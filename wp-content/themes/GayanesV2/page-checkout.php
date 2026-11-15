<?php
/**
 * Checkout page template — iOS 27 Liquid Glass Checkout Flow.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();

$stage = ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) ? 'complete' : 'checkout';
?>

<main id="primary" class="gv2-main gv2-container gv2-flow-page">
	<?php
	while ( have_posts() ) :
		the_post();
		gv2_render_commerce_flow_header( $stage );
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
