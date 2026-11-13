<?php
/**
 * WooCommerce Archive / Category / Shop Listing Template.
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );
?>

<header class="gk-archive-header">
	<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
		<h1 class="gk-archive-title"><?php woocommerce_page_title(); ?></h1>
	<?php endif; ?>
	<?php do_action( 'woocommerce_archive_description' ); ?>
</header>

<?php
if ( woocommerce_product_loop() ) {
	?>
	<div class="gk-archive-toolbar">
		<?php do_action( 'woocommerce_before_shop_loop' ); ?>
	</div>

	<div class="gk-product-grid">
		<?php
		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();
				do_action( 'woocommerce_shop_loop' );
				wc_get_template_part( 'content', 'product' );
			}
		}
		?>
	</div>

	<div class="gk-pagination">
		<?php do_action( 'woocommerce_after_shop_loop' ); ?>
	</div>
	<?php
} else {
	do_action( 'woocommerce_no_products_found' );
}

do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );

