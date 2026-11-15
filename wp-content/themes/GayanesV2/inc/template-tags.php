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
					$partners[] = array(
						'name'     => $parts[0],
						'subtitle' => $parts[1] ?? '',
						'url'      => $parts[2] ?? '#',
					);
				}
			}
		}
		if ( ! empty( $partners ) ) {
			return $partners;
		}
	}

	return array(
		array(
			'name'     => 'Yerevan City',
			'subtitle' => 'Supermarket Chain',
			'url'      => '#',
		),
		array(
			'name'     => 'SAS Supermarket',
			'subtitle' => 'Retail Partner',
			'url'      => '#',
		),
		array(
			'name'     => 'Parma',
			'subtitle' => 'Gourmet Market',
			'url'      => '#',
		),
		array(
			'name'     => 'Ararat Farms',
			'subtitle' => 'Fresh Local Dairy & Meat',
			'url'      => '#',
		),
		array(
			'name'     => 'Menu.am',
			'subtitle' => 'Express Delivery',
			'url'      => '#',
		),
		array(
			'name'     => 'Glovo Armenia',
			'subtitle' => 'Chilled Courier',
			'url'      => '#',
		),
	);
}

