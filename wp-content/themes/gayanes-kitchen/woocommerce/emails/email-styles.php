<?php
/**
 * Email Styles — Gayane's Kitchen (Option 3: Bold Culinary & Marketing-Driven)
 * Supports Light Mode, Dark Mode (prefers-color-scheme + Outlook), and Mobile-First layout.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bg_outer       = '#F7F6F1';
$bg_card        = '#FFFFFF';
$hero_bg        = '#304B39';
$strip_bg       = '#EAE6DC';
$cta_bg         = '#A35030';
$text_primary   = '#242923';
$text_muted     = '#5C625A';
$border_subtle  = '#E6E2D8';
$font_stack     = "'Inter', 'Noto Sans Armenian', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";
?>
:root {
	color-scheme: light dark;
	supported-color-schemes: light dark;
}

html, body {
	margin: 0 !important;
	padding: 0 !important;
	width: 100% !important;
	background-color: <?php echo esc_attr( $bg_outer ); ?>;
	color: <?php echo esc_attr( $text_primary ); ?>;
	font-family: <?php echo $font_stack; ?>;
	-webkit-font-smoothing: antialiased;
	-webkit-text-size-adjust: 100%;
	-ms-text-size-adjust: 100%;
}

img {
	border: 0;
	outline: none;
	text-decoration: none;
	-ms-interpolation-mode: bicubic;
	max-width: 100%;
}

table {
	border-collapse: collapse !important;
	mso-table-lspace: 0pt;
	mso-table-rspace: 0pt;
}

#outer_wrapper {
	width: 100%;
	background-color: <?php echo esc_attr( $bg_outer ); ?>;
	margin: 0;
	padding: 24px 12px 40px;
}

#wrapper {
	width: 100%;
	max-width: 600px;
	margin: 0 auto;
}

#template_container {
	width: 100%;
	max-width: 600px;
	background-color: <?php echo esc_attr( $bg_card ); ?>;
	border-radius: 24px;
	overflow: hidden;
	border: 1px solid <?php echo esc_attr( $border_subtle ); ?>;
	box-shadow: 0 12px 32px rgba(36, 41, 35, 0.08);
}

/* Hero Header */
.gv2-hero {
	background-color: <?php echo esc_attr( $hero_bg ); ?>;
	background-image: linear-gradient(<?php echo esc_attr( $hero_bg ); ?>, <?php echo esc_attr( $hero_bg ); ?>);
	padding: 22px 28px 26px;
	text-align: center;
}

.gv2-hero-logo img {
	width: 152px;
	max-width: 152px;
	height: auto;
	display: inline-block;
}

.gv2-hero-order-pill {
	display: inline-block;
	margin-top: 8px;
	padding: 4px 14px;
	border-radius: 999px;
	background-color: rgba(255, 255, 255, 0.14);
	border: 1px solid rgba(247, 246, 241, 0.35);
	font-size: 13px;
	font-weight: 600;
	color: #F7F6F1;
	letter-spacing: 0.02em;
}

.gv2-hero-wordmark {
	font-size: 22px;
	font-weight: 700;
	letter-spacing: 0.14em;
	color: #F7F6F1;
	text-transform: uppercase;
	text-decoration: none;
}

.gv2-hero-badge {
	display: inline-block;
	width: 44px;
	height: 44px;
	line-height: 44px;
	border-radius: 999px;
	border: 2px solid rgba(247, 246, 241, 0.55);
	background-color: rgba(255, 255, 255, 0.08);
	color: #F7F6F1;
	font-size: 20px;
	font-weight: 700;
	text-align: center;
	margin: 18px auto 12px;
}

.gv2-hero h1 {
	margin: 0 0 8px;
	font-family: <?php echo $font_stack; ?>;
	font-size: 26px;
	line-height: 1.25;
	font-weight: 700;
	color: #FFFFFF;
	text-align: center;
}

.gv2-hero-subtitle {
	margin: 0;
	font-size: 15px;
	line-height: 1.5;
	color: rgba(247, 246, 241, 0.88);
	text-align: center;
}

/* Warm Sand Summary Strip */
.gv2-strip {
	background-color: <?php echo esc_attr( $strip_bg ); ?>;
	padding: 13px 28px;
	border-bottom: 1px solid <?php echo esc_attr( $border_subtle ); ?>;
}

.gv2-strip-text {
	font-size: 13px;
	font-weight: 600;
	color: <?php echo esc_attr( $text_primary ); ?>;
	line-height: 1.4;
}

/* Main Body */
#body_content {
	background-color: <?php echo esc_attr( $bg_card ); ?>;
}

#body_content_inner {
	padding: 28px 28px 16px;
	color: <?php echo esc_attr( $text_primary ); ?>;
	font-family: <?php echo $font_stack; ?>;
	font-size: 15px;
	line-height: 1.6;
	text-align: left;
}

#body_content_inner p {
	margin: 0 0 14px;
	color: <?php echo esc_attr( $text_primary ); ?>;
}

.gv2-greeting {
	margin: 0 0 22px;
	padding-bottom: 18px;
	border-bottom: 1px solid <?php echo esc_attr( $border_subtle ); ?>;
}

.gv2-greeting-name {
	margin: 0 0 8px !important;
	font-size: 18px;
	font-weight: 700;
	color: <?php echo esc_attr( $text_primary ); ?>;
}

.gv2-greeting-body {
	margin: 0 0 8px !important;
	font-size: 15px;
	line-height: 1.6;
	color: <?php echo esc_attr( $text_primary ); ?>;
}

.gv2-greeting-thanks {
	margin: 0 !important;
	font-size: 14px;
	font-weight: 600;
	color: <?php echo esc_attr( $hero_bg ); ?> !important;
}

.gv2-section-title {
	margin: 4px 0 14px;
	font-size: 16px;
	font-weight: 700;
	color: <?php echo esc_attr( $text_primary ); ?>;
	letter-spacing: -0.01em;
}

/* Order Items */
.gv2-items-table {
	width: 100%;
	border-collapse: collapse;
}

.gv2-item-row td {
	padding: 12px 0;
	border-bottom: 1px solid <?php echo esc_attr( $border_subtle ); ?>;
	vertical-align: middle;
}

.gv2-item-thumb img {
	width: 60px !important;
	height: 60px !important;
	object-fit: cover;
	border-radius: 12px;
	display: block;
	background-color: <?php echo esc_attr( $strip_bg ); ?>;
}

.gv2-item-thumb-placeholder {
	width: 60px;
	height: 60px;
	border-radius: 12px;
	background-color: <?php echo esc_attr( $strip_bg ); ?>;
	border: 1px solid <?php echo esc_attr( $border_subtle ); ?>;
}

.gv2-item-name {
	font-size: 15px;
	font-weight: 600;
	color: <?php echo esc_attr( $text_primary ); ?>;
	line-height: 1.35;
	margin: 0 0 3px;
}

.gv2-item-meta {
	font-size: 13px;
	color: <?php echo esc_attr( $text_muted ); ?>;
	line-height: 1.4;
}

.gv2-item-price {
	font-size: 15px;
	font-weight: 600;
	color: <?php echo esc_attr( $text_primary ); ?>;
	text-align: right;
	white-space: nowrap;
}

/* Totals */
.gv2-totals-table {
	width: 100%;
	margin-top: 12px;
	border-collapse: collapse;
}

.gv2-totals-table td,
.gv2-totals-table th {
	padding: 6px 0;
	font-size: 14px;
	color: <?php echo esc_attr( $text_muted ); ?>;
	font-weight: 500;
}

.gv2-totals-table th {
	text-align: left;
}

.gv2-totals-table td {
	text-align: right;
	color: <?php echo esc_attr( $text_primary ); ?>;
}

.gv2-totals-table tr.gv2-total-grand th,
.gv2-totals-table tr.gv2-total-grand td {
	padding-top: 14px;
	border-top: 1px solid <?php echo esc_attr( $border_subtle ); ?>;
	font-size: 17px;
	font-weight: 700;
	color: <?php echo esc_attr( $text_primary ); ?>;
}

/* Payment & Info Cards */
.gv2-info-card {
	margin: 18px 0 6px;
	background-color: <?php echo esc_attr( $bg_outer ); ?>;
	border: 1px solid <?php echo esc_attr( $border_subtle ); ?>;
	border-radius: 16px;
	padding: 13px 16px;
}

.gv2-info-label {
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.06em;
	color: <?php echo esc_attr( $text_muted ); ?>;
	margin: 0 0 3px;
}

.gv2-info-value {
	font-size: 14px;
	font-weight: 600;
	color: <?php echo esc_attr( $text_primary ); ?>;
	line-height: 1.45;
}

/* Scheduled Delivery Card */
.gv2-delivery-card {
	margin: 20px 0 6px;
	background-color: <?php echo esc_attr( $bg_outer ); ?>;
	border: 1px solid <?php echo esc_attr( $border_subtle ); ?>;
	border-left: 4px solid <?php echo esc_attr( $hero_bg ); ?>;
	border-radius: 16px;
	padding: 14px 16px;
}

.gv2-delivery-label {
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.06em;
	color: <?php echo esc_attr( $text_muted ); ?>;
	margin: 0 0 4px;
}

.gv2-delivery-value {
	font-size: 15px;
	font-weight: 700;
	color: <?php echo esc_attr( $text_primary ); ?>;
	line-height: 1.4;
}

.gv2-delivery-date {
	display: inline-block;
	margin-right: 8px;
	vertical-align: middle;
}

.gv2-delivery-slot {
	display: inline-block;
	padding: 3px 10px;
	border-radius: 999px;
	background-color: <?php echo esc_attr( $hero_bg ); ?>;
	color: #FFFFFF !important;
	font-size: 12px;
	font-weight: 600;
	vertical-align: middle;
	white-space: nowrap;
}

/* Primary Terracotta Pill CTA */
.gv2-cta-wrap {
	padding: 20px 0 8px;
	text-align: center;
}

.gv2-cta-td {
	border-radius: 999px;
	background-color: <?php echo esc_attr( $cta_bg ); ?>;
}

.gv2-cta-btn {
	display: block;
	color: #FFFFFF !important;
	font-size: 15px;
	font-weight: 700;
	line-height: 22px;
	text-decoration: none !important;
	padding: 16px 28px;
	border-radius: 999px;
	text-align: center;
}

/* Addresses */
.gv2-addresses {
	width: 100%;
	margin-top: 16px;
}

.gv2-address-card {
	background-color: <?php echo esc_attr( $bg_outer ); ?>;
	border: 1px solid <?php echo esc_attr( $border_subtle ); ?>;
	border-radius: 16px;
	padding: 16px;
	font-size: 13px;
	line-height: 1.55;
	color: <?php echo esc_attr( $text_muted ); ?>;
}

.gv2-address-card h3 {
	margin: 0 0 8px;
	font-size: 13px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.04em;
	color: <?php echo esc_attr( $text_primary ); ?>;
}

.gv2-address-card a {
	color: <?php echo esc_attr( $hero_bg ); ?>;
	text-decoration: none;
}

/* Loyalty / Marketing Card (Option 3) */
.gv2-loyalty-wrap {
	padding: 8px 28px 24px;
}

.gv2-loyalty-card {
	background-color: <?php echo esc_attr( $strip_bg ); ?>;
	border: 1px solid <?php echo esc_attr( $border_subtle ); ?>;
	border-radius: 18px;
	padding: 20px 22px;
	text-align: center;
}

.gv2-loyalty-title {
	margin: 0 0 4px;
	font-size: 16px;
	font-weight: 700;
	color: <?php echo esc_attr( $text_primary ); ?>;
}

.gv2-loyalty-sub {
	margin: 0 0 12px;
	font-size: 13px;
	line-height: 1.45;
	color: <?php echo esc_attr( $text_muted ); ?>;
}

.gv2-loyalty-code {
	display: inline-block;
	padding: 6px 14px;
	border-radius: 999px;
	background-color: #FFFFFF;
	border: 1px dashed <?php echo esc_attr( $cta_bg ); ?>;
	color: <?php echo esc_attr( $cta_bg ); ?>;
	font-size: 13px;
	font-weight: 700;
	margin-bottom: 10px;
}

.gv2-loyalty-link {
	display: inline-block;
	font-size: 13px;
	font-weight: 600;
	color: <?php echo esc_attr( $hero_bg ); ?> !important;
	text-decoration: underline;
}

/* Footer */
#template_footer {
	padding: 24px 16px 12px;
	text-align: center;
}

.gv2-social-table {
	margin: 0 auto 18px;
}

.gv2-social-cell {
	padding: 0 6px;
}

.gv2-social-icon-link {
	display: inline-block;
	text-decoration: none !important;
	border-radius: 999px;
}

.gv2-social-icon-link img {
	width: 40px;
	height: 40px;
	display: block;
	border-radius: 999px;
}

.gv2-phone-box {
	margin: 0 auto 16px;
	text-align: center;
}

.gv2-phone-label {
	font-size: 13px;
	color: <?php echo esc_attr( $text_muted ); ?>;
	margin: 0 0 8px;
}

.gv2-phone-cta {
	display: inline-block;
	padding: 10px 20px;
	border-radius: 999px;
	background-color: <?php echo esc_attr( $bg_card ); ?>;
	border: 1px solid <?php echo esc_attr( $border_subtle ); ?>;
	color: <?php echo esc_attr( $text_primary ); ?> !important;
	font-size: 16px;
	font-weight: 700;
	text-decoration: none !important;
	white-space: nowrap;
}

.gv2-footer-text {
	font-size: 12px;
	line-height: 1.6;
	color: <?php echo esc_attr( $text_muted ); ?>;
	text-align: center;
}

.gv2-footer-text a {
	color: <?php echo esc_attr( $hero_bg ); ?>;
	text-decoration: none;
	font-weight: 600;
}

/* Mobile Optimization */
@media only screen and (max-width: 620px) {
	#outer_wrapper {
		padding: 12px 8px 28px !important;
	}
	#template_container {
		border-radius: 18px !important;
	}
	.gv2-hero {
		padding: 26px 18px 22px !important;
	}
	.gv2-hero h1 {
		font-size: 22px !important;
	}
	.gv2-strip {
		padding: 12px 18px !important;
	}
	#body_content_inner {
		padding: 22px 18px 12px !important;
	}
	.gv2-loyalty-wrap {
		padding: 6px 18px 20px !important;
	}
	.gv2-address-col {
		display: block !important;
		width: 100% !important;
		padding: 0 0 12px 0 !important;
	}
	.gv2-cta-btn {
		max-width: 100% !important;
	}
}

/* Dark Mode (Apple Mail, iOS Mail, macOS Mail, Outlook.com, Thunderbird) */
@media (prefers-color-scheme: dark) {
	html, body, #outer_wrapper {
		background-color: #0E130F !important;
		color: #F7F6F1 !important;
	}
	#template_container, #body_content {
		background-color: #1B221D !important;
		border-color: #2B362E !important;
	}
	/* Keep Hero exact brand #304B39 in Dark Mode */
	.gv2-hero {
		background-color: #304B39 !important;
		background-image: linear-gradient(#304B39, #304B39) !important;
	}
	.gv2-hero h1, .gv2-hero-wordmark, .gv2-hero-badge {
		color: #FFFFFF !important;
	}
	.gv2-hero-subtitle {
		color: #EBF0EC !important;
	}
	.gv2-strip {
		background-color: #232D26 !important;
		border-bottom-color: #2F3C33 !important;
	}
	.gv2-greeting {
		border-bottom-color: #2B362E !important;
	}
	.gv2-greeting-name,
	.gv2-greeting-body,
	.gv2-info-value,
	.gv2-strip-text,
	#body_content_inner,
	#body_content_inner p,
	.gv2-section-title,
	.gv2-item-name,
	.gv2-item-price,
	.gv2-totals-table td,
	.gv2-totals-table tr.gv2-total-grand th,
	.gv2-totals-table tr.gv2-total-grand td,
	.gv2-address-card h3,
	.gv2-delivery-value {
		color: #F7F6F1 !important;
	}
	.gv2-greeting-thanks {
		color: #8FC0A0 !important;
	}
	.gv2-item-meta,
	.gv2-totals-table th,
	.gv2-address-card,
	.gv2-footer-text,
	.gv2-delivery-label,
	.gv2-info-label,
	.gv2-phone-label {
		color: #B4BDB6 !important;
	}
	.gv2-item-row td,
	.gv2-totals-table tr.gv2-total-grand th,
	.gv2-totals-table tr.gv2-total-grand td {
		border-color: #2B362E !important;
	}
	.gv2-info-card,
	.gv2-item-thumb-placeholder {
		background-color: #151B17 !important;
		border-color: #2B362E !important;
	}
	.gv2-delivery-card {
		background-color: #151B17 !important;
		border-color: #2B362E !important;
		border-left-color: #4F7A5D !important;
	}
	.gv2-delivery-slot {
		background-color: #304B39 !important;
		color: #FFFFFF !important;
	}
	.gv2-cta-td,
	.gv2-cta-btn {
		background-color: #C46845 !important;
		color: #FFFFFF !important;
	}
	.gv2-address-card {
		background-color: #151B17 !important;
		border-color: #2B362E !important;
	}
	.gv2-address-card a,
	.gv2-loyalty-link,
	.gv2-footer-text a {
		color: #8FC0A0 !important;
	}
	.gv2-loyalty-card {
		background-color: #161C18 !important;
		border-color: #C46845 !important;
	}
	.gv2-loyalty-code {
		background-color: #232D26 !important;
		border-color: #C46845 !important;
		color: #F09C7C !important;
	}
	.gv2-phone-cta {
		background-color: #1B221D !important;
		border-color: #3B4A40 !important;
		color: #F7F6F1 !important;
	}
}

/* Gmail iOS/Android Dark Mode Hero Lock */
u + .gv2-body .gv2-hero {
	background-color: #304B39 !important;
	background-image: linear-gradient(#304B39, #304B39) !important;
}
u + .gv2-body .gv2-blend-screen {
	background: #000000;
	mix-blend-mode: screen;
}
u + .gv2-body .gv2-blend-difference {
	background: #000000;
	mix-blend-mode: difference;
	color: #FFFFFF !important;
}
u + .gv2-body .gv2-hero h1,
u + .gv2-body .gv2-hero-subtitle,
u + .gv2-body .gv2-hero-badge {
	color: #FFFFFF !important;
}

/* Outlook App / Web Dark Mode Attributes */
[data-ogsc] #outer_wrapper { background-color: #0E130F !important; }
[data-ogsc] #template_container,
[data-ogsc] #body_content { background-color: #1B221D !important; }
[data-ogsc] .gv2-hero { background-color: #304B39 !important; background-image: linear-gradient(#304B39, #304B39) !important; }
[data-ogsc] .gv2-strip { background-color: #232D26 !important; }
[data-ogsc] .gv2-delivery-card { background-color: #151B17 !important; border-color: #2B362E !important; }
[data-ogsc] .gv2-cta-btn { background-color: #C46845 !important; }
[data-ogsc] .gv2-loyalty-card { background-color: #161C18 !important; border-color: #C46845 !important; }
[data-ogsc] .gv2-phone-cta { background-color: #1B221D !important; border-color: #3B4A40 !important; color: #F7F6F1 !important; }
[data-ogsc] .gv2-item-name,
[data-ogsc] .gv2-item-price,
[data-ogsc] .gv2-section-title,
[data-ogsc] .gv2-strip-text,
[data-ogsc] .gv2-delivery-value { color: #F7F6F1 !important; }
