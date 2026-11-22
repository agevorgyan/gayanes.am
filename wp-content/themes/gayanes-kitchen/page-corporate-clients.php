<?php
/**
 * Template Name: Corporate Clients
 *
 * iOS 27 Liquid Glass Corporate Clients page for gayanes.am.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="gv2-main gv2-info-page">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
