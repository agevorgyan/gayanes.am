<?php
/**
 * WooCommerce Single Product Template.
 *
 * Preserves all WooCommerce hooks for simple & variable products,
 * reviews, and related products using the unified product card.
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );

while ( have_posts() ) :
	the_post();
	global $product;
	?>
	<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'gk-single-product', $product ); ?>>
		<div class="gk-single-product__grid">
			<div class="gk-single-product__gallery">
				<?php do_action( 'woocommerce_before_single_product_summary' ); ?>
			</div>

			<div class="gk-single-product__summary">
				<?php do_action( 'woocommerce_single_product_summary' ); ?>
			</div>
		</div>

		<div class="gk-single-product__tabs">
			<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
		</div>
	</div>
	<?php
endwhile;

do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );

