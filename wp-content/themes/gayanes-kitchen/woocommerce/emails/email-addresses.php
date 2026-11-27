<?php
/**
 * Email Addresses — Gayane's Kitchen (Option 3)
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 9.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lang     = function_exists( 'gv2_get_email_lang' ) ? gv2_get_email_lang( $order ) : 'hy';
$address  = $order->get_formatted_billing_address();
$shipping = $order->get_formatted_shipping_address();
$show_ship = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $shipping;
?>
<table class="gv2-addresses" cellspacing="0" cellpadding="0" border="0" role="presentation">
	<tr>
		<td class="gv2-address-col" valign="top" width="<?php echo $show_ship ? '50%' : '100%'; ?>" style="<?php echo $show_ship ? 'padding-right: 8px;' : ''; ?>">
			<div class="gv2-address-card">
				<h3>
					<?php
					echo esc_html(
						function_exists( 'gv2_email_t' )
							? gv2_email_t( 'billing_address_title', $lang )
							: __( 'Billing address', 'woocommerce' )
					);
					?>
				</h3>
				<address style="font-style: normal; margin: 0;">
					<?php echo wp_kses_post( $address ? $address : esc_html__( 'N/A', 'woocommerce' ) ); ?>
					<?php if ( $order->get_billing_phone() ) : ?>
						<br /><?php echo wc_make_phone_clickable( $order->get_billing_phone() ); ?>
					<?php endif; ?>
					<?php if ( $order->get_billing_email() ) : ?>
						<br /><?php echo esc_html( $order->get_billing_email() ); ?>
					<?php endif; ?>
					<?php do_action( 'woocommerce_email_customer_address_section', 'billing', $order, $sent_to_admin, false ); ?>
				</address>
			</div>
		</td>
		<?php if ( $show_ship ) : ?>
			<td class="gv2-address-col" valign="top" width="50%" style="padding-left: 8px;">
				<div class="gv2-address-card">
					<h3>
						<?php
						echo esc_html(
							function_exists( 'gv2_email_t' )
								? gv2_email_t( 'shipping_address_title', $lang )
								: __( 'Shipping address', 'woocommerce' )
						);
						?>
					</h3>
					<address style="font-style: normal; margin: 0;">
						<?php echo wp_kses_post( $shipping ); ?>
						<?php if ( $order->get_shipping_phone() ) : ?>
							<br /><?php echo wc_make_phone_clickable( $order->get_shipping_phone() ); ?>
						<?php endif; ?>
						<?php do_action( 'woocommerce_email_customer_address_section', 'shipping', $order, $sent_to_admin, false ); ?>
					</address>
				</div>
			</td>
		<?php endif; ?>
	</tr>
</table>
