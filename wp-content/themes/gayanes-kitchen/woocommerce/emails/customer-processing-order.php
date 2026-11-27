<?php
/**
 * Customer Processing Order Email — Gayane's Kitchen
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */

defined( 'ABSPATH' ) || exit;

$lang = function_exists( 'gv2_get_email_lang' ) ? gv2_get_email_lang( $order ) : 'hy';

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<div class="gv2-greeting">
	<p class="gv2-greeting-name">
		<?php
		$first_name = $order instanceof WC_Order ? trim( (string) $order->get_billing_first_name() ) : '';
		if ( '' !== $first_name && function_exists( 'gv2_email_t' ) ) {
			echo esc_html( sprintf( gv2_email_t( 'greeting_named', $lang ), $first_name ) );
		} elseif ( function_exists( 'gv2_email_t' ) ) {
			echo esc_html( gv2_email_t( 'greeting_generic', $lang ) );
		}
		?>
	</p>
	<?php if ( function_exists( 'gv2_email_t' ) ) : ?>
		<p class="gv2-greeting-body"><?php echo esc_html( gv2_email_t( 'processing_body_1', $lang ) ); ?></p>
		<p class="gv2-greeting-thanks"><?php echo esc_html( gv2_email_t( 'processing_body_2', $lang ) ); ?></p>
	<?php endif; ?>
</div>

<?php
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_footer', $email );
