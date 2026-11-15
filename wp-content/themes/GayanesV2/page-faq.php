<?php
/**
 * Template Name: FAQ
 *
 * iOS 27 Liquid Glass & SEO/GEO-optimized Frequently Asked Questions page.
 * Automatically applies to the `/faq/` slug and can be selected as a Page Template.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="primary" class="gv2-main gv2-faq-page">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
