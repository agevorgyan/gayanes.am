<?php
/**
 * Email Header — Gayane's Kitchen
 * Compact #304B39 hero with dominant logo, confirmation status, and order number.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email      = $email ?? null;
$order      = ( is_object( $email ) && isset( $email->object ) && $email->object instanceof WC_Order ) ? $email->object : null;
$lang       = function_exists( 'gv2_get_email_lang' ) ? gv2_get_email_lang( $email ) : 'hy';
$store_name = $store_name ?? get_bloginfo( 'name', 'display' );
$logo_url   = defined( 'GV2_URI' ) ? GV2_URI . '/assets/images/logo-light.png' : get_option( 'woocommerce_email_header_image' );
$home_link  = apply_filters( 'woocommerce_email_header_image_url', home_url( '/' ) );
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( $lang ); ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="color-scheme" content="light dark" />
	<meta name="supported-color-schemes" content="light dark" />
	<title><?php echo esc_html( $store_name ); ?></title>
</head>
<body class="gv2-body" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
	<table width="100%" id="outer_wrapper" role="presentation" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td align="center" valign="top">
				<div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
					<table border="0" cellpadding="0" cellspacing="0" width="100%" id="template_container" role="presentation">
						<!-- Compact Brand Hero (#304B39 locked in Light & Dark mode) -->
						<tr>
							<td align="center" valign="top" class="gv2-hero" bgcolor="#304B39" style="background-color: #304B39; background-image: linear-gradient(#304B39, #304B39);">
								<div class="gv2-hero-logo">
									<a href="<?php echo esc_url( $home_link ); ?>" class="gv2-hero-wordmark" target="_blank">
										<?php if ( $logo_url ) : ?>
											<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $store_name ); ?>" width="152" height="152" style="width: 152px; max-width: 152px; height: auto; display: inline-block;" />
										<?php else : ?>
											GAYANE'S
										<?php endif; ?>
									</a>
								</div>

								<div class="gv2-blend-screen">
									<div class="gv2-blend-difference">
										<div class="gv2-hero-badge" style="color: #FFFFFF !important;" aria-hidden="true">&#10003;</div>
										<h1 style="color: #FFFFFF !important;"><?php echo esc_html( $email_heading ); ?></h1>
										<?php if ( $order instanceof WC_Order && function_exists( 'gv2_email_t' ) ) : ?>
											<div class="gv2-hero-order-pill" style="color: #F7F6F1 !important;">
												<?php echo esc_html( sprintf( gv2_email_t( 'order_number_label', $lang ), $order->get_order_number() ) ); ?>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</td>
						</tr>

						<?php if ( $order instanceof WC_Order && function_exists( 'gv2_email_t' ) ) : ?>
						<!-- Warm Sand Metadata Strip -->
						<tr>
							<td class="gv2-strip">
								<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
									<tr>
										<td class="gv2-strip-text" align="left">
											<?php echo esc_html( sprintf( gv2_email_t( 'order_number_label', $lang ), $order->get_order_number() ) ); ?>
										</td>
										<td class="gv2-strip-text" align="right">
											<?php
											$date_created = $order->get_date_created();
											if ( $date_created ) {
												$raw_date = $date_created->date_i18n( 'Y-m-d' );
												$fmt_date = function_exists( 'gv2_format_email_delivery_date' )
													? gv2_format_email_delivery_date( $raw_date, $lang )
													: $date_created->date_i18n( 'd.m.Y' );
												echo esc_html( $fmt_date );
											}
											?>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<?php endif; ?>

						<!-- Body -->
						<tr>
							<td align="center" valign="top" id="body_content">
								<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
									<tr>
										<td valign="top" id="body_content_inner">
