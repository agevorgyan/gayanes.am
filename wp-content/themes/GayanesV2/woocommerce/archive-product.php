<?php
/**
 * WooCommerce Shop / Category Archive with iOS 27 Filter Pills & Infinite Scroll.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );

global $wp_query;

$current_cat = '';
if ( is_product_category() ) {
	$term = get_queried_object();
	if ( $term instanceof WP_Term ) {
		$current_cat = $term->slug;
	}
}
$current_orderby = isset( $_GET['orderby'] ) ? sanitize_key( (string) wp_unslash( $_GET['orderby'] ) ) : 'menu_order';
$shop_url        = wc_get_page_permalink( 'shop' );
$max_pages       = (int) $wp_query->max_num_pages;
$paged           = max( 1, (int) get_query_var( 'paged' ) );
?>

<header class="gv2-shop-head">
	<h1 class="gv2-shop-head__title"><?php woocommerce_page_title(); ?></h1>
	<?php do_action( 'woocommerce_archive_description' ); ?>
</header>

<div class="gv2-shop-toolbar">
	<div class="gv2-filter-pills" role="navigation" aria-label="<?php echo esc_attr( gv2_t( 'Categories' ) ); ?>">
		<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-filter-pill<?php echo '' === $current_cat ? ' is-active' : ''; ?>">
			<?php echo esc_html( gv2_t( 'All' ) ); ?>
		</a>
		<?php
		$cats = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => 0,
			)
		);
		if ( is_array( $cats ) ) :
			foreach ( $cats as $cat ) :
				$cat_link = get_term_link( $cat );
				?>
				<a
					href="<?php echo esc_url( is_wp_error( $cat_link ) ? $shop_url : $cat_link ); ?>"
					class="gv2-filter-pill<?php echo $current_cat === $cat->slug ? ' is-active' : ''; ?>"
				>
					<?php echo esc_html( $cat->name ); ?>
				</a>
				<?php
			endforeach;
		endif;
		?>
	</div>

	<form class="gv2-sort-form" method="get">
		<label class="gv2-sr-only" for="gv2-orderby"><?php echo esc_html( gv2_t( 'Sort by' ) ); ?></label>
		<select id="gv2-orderby" name="orderby" class="gv2-select" onchange="this.form.submit()">
			<option value="menu_order" <?php selected( $current_orderby, 'menu_order' ); ?>><?php echo esc_html( gv2_t( 'Default' ) ); ?></option>
			<option value="popularity" <?php selected( $current_orderby, 'popularity' ); ?>><?php echo esc_html( gv2_t( 'Popularity' ) ); ?></option>
			<option value="date" <?php selected( $current_orderby, 'date' ); ?>><?php echo esc_html( gv2_t( 'Newest' ) ); ?></option>
			<option value="price" <?php selected( $current_orderby, 'price' ); ?>><?php echo esc_html( gv2_t( 'Price: Low to High' ) ); ?></option>
			<option value="price-desc" <?php selected( $current_orderby, 'price-desc' ); ?>><?php echo esc_html( gv2_t( 'Price: High to Low' ) ); ?></option>
		</select>
	</form>
</div>

<?php if ( woocommerce_product_loop() ) : ?>
	<div
		class="gv2-product-grid"
		id="gv2-infinite-grid"
		data-page="<?php echo esc_attr( (string) $paged ); ?>"
		data-max-pages="<?php echo esc_attr( (string) $max_pages ); ?>"
		data-category="<?php echo esc_attr( $current_cat ); ?>"
		data-orderby="<?php echo esc_attr( $current_orderby ); ?>"
		data-search="<?php echo esc_attr( get_search_query() ); ?>"
		data-per-page="<?php echo esc_attr( (string) get_query_var( 'posts_per_page', 12 ) ); ?>"
	>
		<?php
		while ( have_posts() ) {
			the_post();
			wc_get_template_part( 'content', 'product' );
		}
		?>
	</div>

	<div
		class="gv2-infinite-sentinel"
		id="gv2-infinite-sentinel"
		<?php echo $paged >= $max_pages ? 'data-done="1"' : ''; ?>
	>
		<?php if ( $paged < $max_pages ) : ?>
			<span class="gv2-spinner" aria-hidden="true"></span>
			<span><?php echo esc_html( gv2_t( 'Loading more dishes...' ) ); ?></span>
		<?php else : ?>
			<span class="gv2-muted"><?php echo esc_html( gv2_t( 'All dishes loaded' ) ); ?></span>
		<?php endif; ?>
	</div>
<?php else : ?>
	<p class="gv2-muted"><?php echo esc_html( gv2_t( 'No dishes found.' ) ); ?></p>
<?php endif; ?>

<?php
do_action( 'woocommerce_after_main_content' );
get_footer( 'shop' );

