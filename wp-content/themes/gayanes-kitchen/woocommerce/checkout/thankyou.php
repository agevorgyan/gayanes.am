<?php
/**
 * Thankyou (Order Received) page — iOS 27 Liquid Glass Confirmation Experience.
 *
 * Overrides woocommerce/templates/checkout/thankyou.php while preserving all
 * standard WooCommerce hooks and payment gateway instructions.
 *
 * @package GayanesV2
 * @version 11.2.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$account_url = function_exists( 'wc_get_endpoint_url' ) ? wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) : home_url( '/my-account/' );
?>

<div class="woocommerce-order gv2-thankyou-wrap">

	<?php if ( $order ) : ?>

		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<div class="gv2-thankyou-hero gv2-thankyou-hero--failed gv2-glass-card">
				<div class="gv2-thankyou-badge gv2-thankyou-badge--error" aria-hidden="true">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
				</div>
				<h2 class="gv2-thankyou-hero__title"><?php esc_html_e( 'Order payment failed', 'woocommerce' ); ?></h2>
				<p class="gv2-thankyou-hero__sub">
					<?php
					$default_order_failed_text = esc_html__( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' );
					$order_failed_text         = apply_filters( 'woocommerce_thankyou_order_failed_text', $default_order_failed_text, $order );
					echo wp_kses_post( is_string( $order_failed_text ) ? $order_failed_text : $default_order_failed_text );
					?>
				</p>
				<div class="gv2-thankyou-hero__actions">
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="gv2-btn gv2-btn--primary gv2-btn--lg">
						<?php esc_html_e( 'Pay', 'woocommerce' ); ?>
					</a>
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="gv2-btn gv2-btn--glass gv2-btn--lg">
							<?php esc_html_e( 'My account', 'woocommerce' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

		<?php else : ?>

			<!-- Celebratory Liquid Glass Confirmation Hero -->
			<div class="gv2-thankyou-hero gv2-glass-card">
				<div class="gv2-thankyou-badge" aria-hidden="true">
					<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
				</div>

				<span class="gv2-eyebrow"><?php echo esc_html( gv2_t( 'Crafted Like Home' ) ); ?></span>
				<h2 class="gv2-thankyou-hero__title">
					<?php echo esc_html( gv2_t( 'Thank you! Your order has been received.' ) ); ?>
				</h2>
				<p class="gv2-thankyou-hero__sub">
					<?php echo esc_html( gv2_t( 'We have received your order and our kitchen in Yerevan will start preparing your fresh homemade dishes shortly.' ) ); ?>
				</p>

				<div class="gv2-thankyou-hero__actions">
					<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--primary gv2-btn--lg">
						<?php echo esc_html( gv2_t( 'Explore Menu' ) ); ?>
					</a>
					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( $account_url ); ?>" class="gv2-btn gv2-btn--glass gv2-btn--lg">
							<?php echo esc_html( gv2_t( 'My Orders' ) ); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gv2-btn gv2-btn--glass gv2-btn--lg">
							<?php echo esc_html( gv2_t( 'Back to Home' ) ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<!-- Order Overview Bento Cards -->
			<ul class="gv2-thankyou-overview woocommerce-order-overview woocommerce-thankyou-order-details order_details">
				<li class="gv2-thankyou-stat gv2-glass-card woocommerce-order-overview__order order">
					<span class="gv2-thankyou-stat__label"><?php echo esc_html( gv2_t( 'Order Number' ) ); ?></span>
					<strong class="gv2-thankyou-stat__val">#<?php echo esc_html( $order->get_order_number() ); ?></strong>
				</li>

				<li class="gv2-thankyou-stat gv2-glass-card woocommerce-order-overview__date date">
					<span class="gv2-thankyou-stat__label"><?php echo esc_html( gv2_t( 'Order Date' ) ); ?></span>
					<strong class="gv2-thankyou-stat__val"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
				</li>

				<li class="gv2-thankyou-stat gv2-glass-card gv2-thankyou-stat--highlight woocommerce-order-overview__total total">
					<span class="gv2-thankyou-stat__label"><?php echo esc_html( gv2_t( 'Total' ) ); ?></span>
					<strong class="gv2-thankyou-stat__val"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
				</li>

				<?php if ( $order->get_payment_method_title() ) : ?>
					<li class="gv2-thankyou-stat gv2-glass-card woocommerce-order-overview__payment-method method">
						<span class="gv2-thankyou-stat__label"><?php echo esc_html( gv2_t( 'Payment Method' ) ); ?></span>
						<strong class="gv2-thankyou-stat__val"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
					</li>
				<?php endif; ?>

				<?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
					<li class="gv2-thankyou-stat gv2-glass-card woocommerce-order-overview__email email">
						<span class="gv2-thankyou-stat__label"><?php echo esc_html( gv2_t( 'Email address' ) ); ?></span>
						<strong class="gv2-thankyou-stat__val"><?php echo esc_html( $order->get_billing_email() ); ?></strong>
					</li>
				<?php endif; ?>
			</ul>

		<?php endif; ?>

		<div class="gv2-thankyou-details">
			<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
			<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
		</div>

	<?php else : ?>

		<div class="gv2-thankyou-hero gv2-glass-card">
			<div class="gv2-thankyou-badge" aria-hidden="true">
				<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
			</div>
			<h2 class="gv2-thankyou-hero__title">
				<?php echo esc_html( gv2_t( 'Thank you! Your order has been received.' ) ); ?>
			</h2>
			<div class="gv2-thankyou-hero__actions">
				<a href="<?php echo esc_url( $shop_url ); ?>" class="gv2-btn gv2-btn--primary gv2-btn--lg">
					<?php echo esc_html( gv2_t( 'Explore Menu' ) ); ?>
				</a>
			</div>
		</div>

	<?php endif; ?>

</div>
