<?php
/**
 * Order Details Table — Gayane's Kitchen
 * Renders Order Items, Order Summary (financial totals), Payment Card,
 * Conditional Delivery Card, and Bulletproof Primary CTA.
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
	echo esc_html(
		function_exists( 'gv2_email_t' )
			? gv2_email_t( 'order_summary_title', $lang )
			: __( 'Order summary', 'woocommerce' )
	);
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
				'image_size'    => array( 120, 120 ),
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
		$item_totals       = $order->get_order_item_totals();
		$payment_title_row = '';

		if ( $item_totals ) {
			foreach ( $item_totals as $key => $total ) {
				// Payment method is rendered in its own dedicated card below.
				if ( 'payment_method' === $key ) {
					$payment_title_row = (string) $total['value'];
					continue;
				}
				$is_grand = ( 'order_total' === $key );
				?>
				<tr class="<?php echo $is_grand ? 'gv2-total-grand' : ''; ?>">
					<th scope="row"><?php echo wp_kses_post( $total['label'] ); ?></th>
					<td><?php echo wp_kses_post( $total['value'] ); ?></td>
				</tr>
				<?php
			}
		}

		$raw_note   = (string) $order->get_customer_note();
		$clean_note = function_exists( 'gv2_clean_email_customer_note' ) ? gv2_clean_email_customer_note( $raw_note ) : $raw_note;
		if ( '' !== $clean_note ) {
			?>
			<tr>
				<th scope="row">
					<?php
					echo esc_html(
						function_exists( 'gv2_email_t' )
							? gv2_email_t( 'totals_note', $lang )
							: __( 'Note:', 'woocommerce' )
					);
					?>
				</th>
				<td><?php echo wp_kses( nl2br( wptexturize( $clean_note ) ), array( 'br' => array() ) ); ?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>

<?php
// Dedicated Payment Information Card.
$payment_method_display = $order->get_payment_method_title();
if ( '' === $payment_method_display ) {
	$payment_method_display = $payment_title_row;
}
if ( '' !== trim( wp_strip_all_tags( $payment_method_display ) ) ) :
	?>
	<div class="gv2-info-card gv2-payment-card">
		<div class="gv2-info-label">
			<?php
			echo esc_html(
				function_exists( 'gv2_email_t' )
					? gv2_email_t( 'payment_method_title', $lang )
					: __( 'Payment method', 'woocommerce' )
			);
			?>
		</div>
		<div class="gv2-info-value"><?php echo wp_kses_post( $payment_method_display ); ?></div>
	</div>
<?php endif; ?>

<?php
// Dedicated Conditional Delivery Information Card (no duplication).
$delivery_date = trim( (string) $order->get_meta( '_gayanes_delivery_date' ) );
$delivery_time = trim( (string) $order->get_meta( '_gayanes_delivery_time' ) );
if ( '' !== $delivery_date || '' !== $delivery_time ) :
	$clock_icon = defined( 'GV2_URI' ) ? GV2_URI . '/assets/images/email/clock.png?v=2' : '';
	?>
	<div class="gv2-delivery-card">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
			<tr>
				<?php if ( $clock_icon ) : ?>
					<td width="48" valign="middle" style="width: 48px; padding-right: 12px;">
						<img src="<?php echo esc_url( $clock_icon ); ?>" alt="" width="40" height="40" style="width: 40px; height: 40px; display: block; border-radius: 999px;" />
					</td>
				<?php endif; ?>
				<td valign="middle">
					<div class="gv2-delivery-label">
						<?php
						echo esc_html(
							function_exists( 'gv2_email_t' )
								? gv2_email_t( 'scheduled_delivery_label', $lang )
								: 'Scheduled Delivery'
						);
						?>
					</div>
					<div class="gv2-delivery-value">
						<?php if ( '' !== $delivery_date ) : ?>
							<span class="gv2-delivery-date">
								<?php
								echo esc_html(
									function_exists( 'gv2_format_email_delivery_date' )
										? gv2_format_email_delivery_date( $delivery_date, $lang )
										: $delivery_date
								);
								?>
							</span>
						<?php endif; ?>
						<?php if ( '' !== $delivery_time ) : ?>
							<span class="gv2-delivery-slot"><?php echo esc_html( $delivery_time ); ?></span>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		</table>
	</div>
<?php endif; ?>

<?php
// Bulletproof Primary CTA (54px height, Outlook VML + modern pill).
$cta_url = $sent_to_admin ? $order->get_edit_order_url() : $order->get_view_order_url();
if ( $cta_url && function_exists( 'gv2_email_t' ) ) :
	$cta_label = gv2_email_t( 'track_order_cta', $lang );
	?>
	<div class="gv2-cta-wrap">
		<!--[if mso]>
		<v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?php echo esc_url( $cta_url ); ?>" style="height:54px;v-text-anchor:middle;width:320px;" arcsize="50%" strokecolor="#A35030" fillcolor="#A35030">
			<w:anchorlock/>
			<center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;"><?php echo esc_html( $cta_label ); ?></center>
		</v:roundrect>
		<![endif]-->
		<!--[if !mso]><!-->
		<table border="0" cellpadding="0" cellspacing="0" align="center" role="presentation" style="margin: 0 auto; width: 100%; max-width: 340px;">
			<tr>
				<td align="center" bgcolor="#A35030" class="gv2-cta-td" style="border-radius: 999px; background-color: #A35030;">
					<a href="<?php echo esc_url( $cta_url ); ?>" class="gv2-cta-btn" target="_blank" style="display: block; padding: 16px 28px; color: #FFFFFF; font-size: 15px; font-weight: 700; line-height: 22px; text-decoration: none; border-radius: 999px;">
						<?php echo esc_html( $cta_label ); ?>
					</a>
				</td>
			</tr>
		</table>
		<!--<![endif]-->
	</div>
<?php endif; ?>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
