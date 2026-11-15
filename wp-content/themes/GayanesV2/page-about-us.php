<?php
/**
 * Template Name: About Us
 *
 * Editorial & iOS 27 Liquid Glass "About Us" (Մեր մասին) page template.
 * Automatically applies to the `about-us` slug and can also be selected
 * as a Page Template for translated pages.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="gv2-main gv2-about-page">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
