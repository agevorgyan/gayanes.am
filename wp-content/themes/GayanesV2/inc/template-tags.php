<?php
/**
 * Template helper tags: breadcrumbs and Partners showcase data.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render accessible breadcrumb trail.
 */
function gv2_breadcrumbs(): void {
	if ( is_front_page() ) {
		return;
	}

	if ( function_exists( 'woocommerce_breadcrumb' ) ) {
		woocommerce_breadcrumb(
			array(
				'delimiter'   => '<span class="gv2-bc-sep" aria-hidden="true">/</span>',
				'wrap_before' => '<nav class="gv2-breadcrumbs" aria-label="' . esc_attr( gv2_t( 'Breadcrumb' ) ) . '">',
				'wrap_after'  => '</nav>',
				'before'      => '<span>',
				'after'       => '</span>',
				'home'        => gv2_t( 'Home' ),
			)
		);
	}
}

/**
 * Get partners list from Customizer or clean default Armenian partner showcase.
 *
 * @return array<int, array{name: string, subtitle: string, url: string}>
 */
function gv2_get_partners(): array {
	$raw = trim( (string) get_theme_mod( 'gv2_partners_json', '' ) );
	if ( '' !== $raw ) {
		$partners = array();
		$lines    = preg_split( '/\r\n|\r|\n/', $raw );
		if ( is_array( $lines ) ) {
			foreach ( $lines as $line ) {
				$parts = array_map( 'trim', explode( '|', $line ) );
				if ( '' !== ( $parts[0] ?? '' ) ) {
					$logo = ( isset( $parts[1] ) && str_starts_with( $parts[1], 'http' ) ) ? $parts[1] : '';
					$url  = $parts[2] ?? ( '' === $logo ? '#' : ( $parts[1] ?? '#' ) );
					$partners[] = array(
						'name' => $parts[0],
						'logo' => $logo,
						'url'  => $url ?: '#',
					);
				}
			}
		}
		if ( ! empty( $partners ) ) {
			return $partners;
		}
	}

	return array(
		array( 'name' => 'Yerevan City', 'logo' => '', 'url' => '#' ),
		array( 'name' => 'SAS', 'logo' => '', 'url' => '#' ),
		array( 'name' => 'Parma', 'logo' => '', 'url' => '#' ),
		array( 'name' => 'Ararat Farms', 'logo' => '', 'url' => '#' ),
		array( 'name' => 'Menu.am', 'logo' => '', 'url' => '#' ),
		array( 'name' => 'Glovo', 'logo' => '', 'url' => '#' ),
	);
}

