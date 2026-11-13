<?php
/**
 * Template helper functions for multilingual UI and navigation.
 *
 * @package Gayanes_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Translate a UI string cleanly using Gayanes_Core\Multilingual if available.
 *
 * @param string $text Source string.
 */
function gayanes_t( string $text ): string {
	if ( class_exists( 'Gayanes_Core\Multilingual' ) ) {
		return Gayanes_Core\Multilingual::t( $text );
	}
	// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
	return __( $text, 'gayanes-theme' );
}

/**
 * Render language switcher list.
 */
function gayanes_render_language_switcher(): void {
	$items = class_exists( 'Gayanes_Core\Multilingual' )
		? Gayanes_Core\Multilingual::get_switcher_items()
		: array(
			array( 'code' => 'hy', 'label' => 'ՀԱՅ', 'name' => 'Հայերեն', 'url' => home_url( '/' ), 'current' => true ),
			array( 'code' => 'ru', 'label' => 'РУС', 'name' => 'Русский', 'url' => home_url( '/ru/' ), 'current' => false ),
			array( 'code' => 'en', 'label' => 'ENG', 'name' => 'English', 'url' => home_url( '/en/' ), 'current' => false ),
		);
	?>
	<nav class="gk-lang-switcher" aria-label="<?php echo esc_attr( gayanes_t( 'Language selector' ) ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<a
				href="<?php echo esc_url( $item['url'] ); ?>"
				class="gk-lang-link<?php echo ! empty( $item['current'] ) ? ' is-active' : ''; ?>"
				hreflang="<?php echo esc_attr( $item['code'] ); ?>"
				lang="<?php echo esc_attr( $item['code'] ); ?>"
				<?php echo ! empty( $item['current'] ) ? 'aria-current="page"' : ''; ?>
				title="<?php echo esc_attr( $item['name'] ); ?>"
			>
				<?php echo esc_html( $item['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php
}

/**
 * Render accessible breadcrumb trail.
 */
function gayanes_breadcrumbs(): void {
	if ( is_front_page() ) {
		return;
	}

	if ( function_exists( 'woocommerce_breadcrumb' ) ) {
		woocommerce_breadcrumb(
			array(
				'delimiter'   => '<span class="gk-bc-sep" aria-hidden="true">/</span>',
				'wrap_before' => '<nav class="gk-breadcrumbs" aria-label="' . esc_attr( gayanes_t( 'Breadcrumb' ) ) . '">',
				'wrap_after'  => '</nav>',
				'before'      => '<span>',
				'after'       => '</span>',
				'home'        => gayanes_t( 'Home' ),
			)
		);
	}
}

