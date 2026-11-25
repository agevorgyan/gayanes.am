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
	$about_hero_id  = absint( get_theme_mod( 'gv2_about_hero_image_id', 0 ) );
	$about_story_id = absint( get_theme_mod( 'gv2_about_story_image_id', 0 ) );
	$about_hero_url = $about_hero_id > 0 ? wp_get_attachment_image_url( $about_hero_id, 'large' ) : '';
	$about_story_url = $about_story_id > 0 ? wp_get_attachment_image_url( $about_story_id, 'large' ) : '';

	while ( have_posts() ) :
		the_post();
		$content = apply_filters( 'the_content', get_the_content() );
		if ( $about_hero_url ) {
			$content = preg_replace_callback(
				'/<img\b[^>]*class=["\'][^"\']*gv2-about-hero__img[^"\']*["\'][^>]*>/i',
				static fn( array $m ): string => preg_replace( '/\bsrc=["\'][^"\']*["\']/i', 'src="' . esc_url( $about_hero_url ) . '"', $m[0] ) ?? $m[0],
				$content,
				1
			) ?? $content;
		}
		if ( $about_story_url ) {
			$content = preg_replace_callback(
				'/<img\b(?![^>]*gv2-about-hero__img)[^>]*>/i',
				static fn( array $m ): string => preg_replace( '/\bsrc=["\'][^"\']*["\']/i', 'src="' . esc_url( $about_story_url ) . '"', $m[0] ) ?? $m[0],
				$content
			) ?? $content;
		}
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	endwhile;
	?>
</main>
<?php
get_footer();
