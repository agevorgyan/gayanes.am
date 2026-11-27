<?php
/**
 * Email Order Items — Gayane's Kitchen
 * 1:1 60x60 rounded product thumbnails, natural wrapping, no horizontal overflow on 320px.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

$lang = function_exists( 'gv2_get_email_lang' ) ? gv2_get_email_lang( $order ) : 'hy';

foreach ( $items as $item_id => $item ) :
	$product       = $item->get_product();
	$sku           = '';
	$purchase_note = '';
	$image_url     = '';

	if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
		continue;
	}

	if ( is_object( $product ) ) {
		$sku           = $product->get_sku();
		$purchase_note = $product->get_purchase_note();
		$image_id      = $product->get_image_id();
		if ( $image_id ) {
			$src = wp_get_attachment_image_src( $image_id, array( 120, 120 ) );
			if ( is_array( $src ) && ! empty( $src[0] ) ) {
				$image_url = $src[0];
			}
		}
	}

	$qty          = $item->get_quantity();
	$refunded_qty = $order->get_qty_refunded_for_item( $item_id );
	if ( $refunded_qty ) {
		$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
	} else {
		$qty_display = esc_html( $qty );
	}
	?>
	<tr class="gv2-item-row <?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
		<?php if ( $show_image ) : ?>
			<td class="gv2-item-thumb" width="72" valign="middle" style="width: 72px; padding-right: 12px;">
				<?php if ( '' !== $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $item->get_name() ); ?>" width="60" height="60" style="width: 60px; height: 60px; object-fit: cover; border-radius: 12px; display: block;" />
				<?php else : ?>
					<div class="gv2-item-thumb-placeholder" aria-hidden="true"></div>
				<?php endif; ?>
			</td>
		<?php endif; ?>
		<td valign="middle" style="word-break: break-word;">
			<div class="gv2-item-name">
				<?php
				echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) );
				if ( $show_sku && $sku ) {
					echo wp_kses_post( ' (#' . $sku . ')' );
				}
				?>
			</div>
			<div class="gv2-item-meta">
				<?php
				if ( function_exists( 'gv2_email_t' ) ) {
					echo wp_kses_post( sprintf( gv2_email_t( 'qty_label', $lang ), $qty_display ) );
				} else {
					echo wp_kses_post( 'Qty: ' . $qty_display );
				}

				do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );

				$item_meta = wc_display_item_meta(
					$item,
					array(
						'before'       => '<br>',
						'after'        => '',
						'separator'    => '<br>',
						'echo'         => false,
						'label_before' => '<span>',
						'label_after'  => ':</span> ',
					)
				);
				if ( $item_meta ) {
					echo wp_kses_post( $item_meta );
				}

				do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );
				?>
			</div>
		</td>
		<td class="gv2-item-price" align="right" valign="middle" style="padding-left: 10px;">
			<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
		</td>
	</tr>
	<?php
	if ( $show_purchase_note && $purchase_note ) :
		?>
		<tr>
			<td colspan="3" class="gv2-item-meta" style="padding: 8px 0 12px;">
				<?php echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) ); ?>
			</td>
		</tr>
	<?php endif; ?>
<?php endforeach; ?>
