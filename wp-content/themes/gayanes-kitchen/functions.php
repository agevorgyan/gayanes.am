<?php
/**
 * GayanesV2 Theme Bootstrap.
 *
 * Mobile-first, iOS 27 Liquid Glass inspired, SEO-hardened WooCommerce theme
 * for https://gayanes.am/.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

define( 'GV2_VERSION', '2.1.23' );
define( 'GV2_DIR', get_template_directory() );
define( 'GV2_URI', get_template_directory_uri() );

require_once GV2_DIR . '/inc/i18n.php';
require_once GV2_DIR . '/inc/setup.php';
require_once GV2_DIR . '/inc/theme-settings.php';
require_once GV2_DIR . '/inc/assets.php';
require_once GV2_DIR . '/inc/faq-data.php';
require_once GV2_DIR . '/inc/turnstile.php';
require_once GV2_DIR . '/inc/custom-login.php';
require_once GV2_DIR . '/inc/corporate.php';
require_once GV2_DIR . '/inc/contact-handler.php';
require_once GV2_DIR . '/inc/seo.php';
require_once GV2_DIR . '/inc/redirects.php';
require_once GV2_DIR . '/inc/ai-translator.php';
require_once GV2_DIR . '/inc/template-tags.php';

if ( class_exists( 'WooCommerce' ) ) {
	require_once GV2_DIR . '/inc/woocommerce.php';
	require_once GV2_DIR . '/inc/emails.php';
}

