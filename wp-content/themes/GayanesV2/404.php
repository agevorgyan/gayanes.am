<?php
/**
 * 404 Not Found template.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="gv2-main gv2-container gv2-404">
	<h1>404</h1>
	<p><?php echo esc_html( gv2_t( 'No dishes found.' ) ); ?></p>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gv2-btn gv2-btn--primary">
		<?php echo esc_html( gv2_t( 'Home' ) ); ?>
	</a>
</main>
<?php
get_footer();

