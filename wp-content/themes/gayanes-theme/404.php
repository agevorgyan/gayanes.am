<?php
/**
 * 404 Not Found Template.
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<main id="primary" class="gk-main gk-container gk-404">
	<span class="gk-eyebrow">404</span>
	<h1><?php echo esc_html( gayanes_t( 'Page Not Found' ) ); ?></h1>
	<p><?php echo esc_html( gayanes_t( 'The page you are looking for may have moved or no longer exists.' ) ); ?></p>
	<div class="gk-404__actions">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gk-btn gk-btn--primary">
			<?php echo esc_html( gayanes_t( 'Go Home' ) ); ?>
		</a>
		<a href="<?php echo esc_url( $shop_url ); ?>" class="gk-btn gk-btn--outline">
			<?php echo esc_html( gayanes_t( 'Shop All' ) ); ?>
		</a>
	</div>
</main>
<?php
get_footer();

