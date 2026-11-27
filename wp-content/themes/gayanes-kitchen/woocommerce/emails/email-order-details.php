<?php
/**
 * Order Details Table — Gayane's Kitchen (Option 3)
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

$lang = function_exists( 'gv2_get_email_lang' ) ? gv2_get_email_lang( $order ) : 'hy';

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );
?>

<h2 class="gv2-section-title">
	<?php
	if ( function_exists( 'gv2_email_t' ) ) {
		echo esc_html( gv2_email_t( 'order_summary_title', $lang ) );
	} else {
		esc_html_e( 'Order summary', 'woocommerce' );
	}
	?>
</h2>

<table class="gv2-items-table" cellspacing="0" cellpadding="0" border="0" role="presentation">
	<tbody>
		<?php
		echo wc_get_email_order_items(
			$order,
			array(
				'show_sku'      => $sent_to_admin,
				'show_image'    => true,
				'image_size'    => array( 64, 64 ),
				'plain_text'    => $plain_text,
				'sent_to_admin' => $sent_to_admin,
			)
		);
		?>
	</tbody>
</table>

<table class="gv2-totals-table" cellspacing="0" cellpadding="0" border="0" role="presentation">
	<tbody>
		<?php
		$item_totals = $order->get_order_item_totals();
		if ( $item_totals ) {
			$total_keys = array_keys( $item_totals );
			$last_key   = end( $total_keys );
			foreach ( $item_totals as $key => $total ) {
				$is_grand = ( 'order_total' === $key || $key === $last_key );
				?>
				<tr class="<?php echo $is_grand ? 'gv2-total-grand' : ''; ?>">
					<th scope="row"><?php echo wp_kses_post( $total['label'] ); ?></th>
					<td><?php echo wp_kses_post( $total['value'] ); ?></td>
				</tr>
				<?php
			}
		}
		if ( $order->get_customer_note() ) {
			?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
				<td><?php echo wp_kses( nl2br( wptexturize( $order->get_customer_note() ) ), array( 'br' => array() ) ); ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>

<?php
$cta_url = $sent_to_admin ? $order->get_edit_order_url() : $order->get_view_order_url();
if ( $cta_url && function_exists( 'gv2_email_t' ) ) :
	?>
	<div class="gv2-cta-wrap">
		<a href="<?php echo esc_url( $cta_url ); ?>" class="gv2-cta-btn" target="_blank">
			<?php echo esc_html( gv2_email_t( 'track_order_cta', $lang ) ); ?>
		</a>
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
