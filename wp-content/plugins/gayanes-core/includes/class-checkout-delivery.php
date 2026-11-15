<?php
/**
 * Streamlined Checkout Fields (Name, Phone, Email, Address, Note)
 * + Admin-configurable Delivery Day & Time Slot Picker.
 *
 * HPOS-compatible, preserves WooCommerce validation, shipping & payment gateways.
 *
 * @package Gayanes_Core
 */

namespace Gayanes_Core;

defined( 'ABSPATH' ) || exit;

/**
 * Checkout_Delivery class.
 */
class Checkout_Delivery {

	public const OPTION_KEY = 'gayanes_delivery_schedule';

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		// Checkout fields & layout.
		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'simplify_checkout_fields' ), 50 );
		add_filter( 'woocommerce_default_address_fields', array( __CLASS__, 'simplify_default_address_fields' ), 50 );
		add_filter( 'woocommerce_enable_order_notes_field', '__return_true' );
		add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );

		// Render Delivery Day & Time picker on checkout.
		add_action( 'woocommerce_after_checkout_billing_form', array( __CLASS__, 'render_delivery_picker' ) );

		// Validate, save (HPOS-safe), and display in Admin, Thank You, and Emails.
		add_action( 'woocommerce_checkout_process', array( __CLASS__, 'validate_delivery_fields' ) );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'save_delivery_meta' ), 10, 2 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( __CLASS__, 'display_admin_order_meta' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'display_order_details_meta' ) );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'display_email_meta' ), 10, 4 );

		// WP Admin Settings page under WooCommerce.
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Get delivery schedule settings with sensible defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_settings(): array {
		$defaults = array(
			'enabled_days'   => array( '1', '2', '3', '4', '5', '6', '0' ), // Mon-Sun (PHP date('w'))
			'days_ahead'     => 7,
			'cutoff_hour'    => 18,
			'time_slots'     => "10:00 - 13:00\n13:00 - 16:00\n16:00 - 19:00\n19:00 - 21:00",
			'blocked_dates'  => '',
		);

		$saved = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
	}

	/**
	 * Keep only Name, Phone, Email, Address, and Note fields on Checkout.
	 *
	 * @param array<string, array<string, mixed>> $fields Checkout fields.
	 * @return array<string, array<string, mixed>>
	 */
	public static function simplify_checkout_fields( array $fields ): array {
		$keep_billing = array(
			'billing_first_name' => array(
				'label'       => Multilingual::t( 'Full Name' ),
				'placeholder' => Multilingual::t( 'Your full name' ),
				'required'    => true,
				'priority'    => 10,
				'class'       => array( 'form-row-wide' ),
			),
			'billing_phone' => array(
				'label'       => Multilingual::t( 'Phone' ),
				'placeholder' => '+374 __ ___ ___',
				'required'    => true,
				'priority'    => 20,
				'class'       => array( 'form-row-first' ),
			),
			'billing_email' => array(
				'label'       => Multilingual::t( 'Email' ),
				'placeholder' => 'name@example.com',
				'required'    => true,
				'priority'    => 30,
				'class'       => array( 'form-row-last' ),
			),
			'billing_address_1' => array(
				'label'       => Multilingual::t( 'Delivery Address' ),
				'placeholder' => Multilingual::t( 'Street, building, apartment, entrance' ),
				'required'    => true,
				'priority'    => 40,
				'class'       => array( 'form-row-wide' ),
			),
		);

		$new_billing = array();
		foreach ( $keep_billing as $key => $custom ) {
			$base                = $fields['billing'][ $key ] ?? array();
			$new_billing[ $key ] = array_merge( $base, $custom );
		}
		$fields['billing'] = $new_billing;

		if ( isset( $fields['order']['order_comments'] ) ) {
			$fields['order']['order_comments']['label']       = Multilingual::t( 'Order Note' );
			$fields['order']['order_comments']['placeholder'] = Multilingual::t( 'Door code, floor, or special requests...' );
			$fields['order']['order_comments']['required']    = false;
		}

		// Ensure shipping fields don't block validation.
		$fields['shipping'] = array();

		return $fields;
	}

	/**
	 * Ensure removed default address fields are not marked required by WooCommerce locale rules.
	 *
	 * @param array<string, array<string, mixed>> $fields Address fields.
	 * @return array<string, array<string, mixed>>
	 */
	public static function simplify_default_address_fields( array $fields ): array {
		foreach ( array( 'last_name', 'company', 'country', 'address_2', 'city', 'state', 'postcode' ) as $k ) {
			if ( isset( $fields[ $k ] ) ) {
				$fields[ $k ]['required'] = false;
			}
		}
		return $fields;
	}

	/**
	 * Build available delivery dates based on admin schedule.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function get_available_dates(): array {
		$settings     = self::get_settings();
		$enabled_days = array_map( 'strval', (array) $settings['enabled_days'] );
		$days_ahead   = max( 1, min( 30, (int) $settings['days_ahead'] ) );
		$cutoff_hour  = (int) $settings['cutoff_hour'];
		$blocked_raw  = array_filter( array_map( 'trim', explode( ',', (string) $settings['blocked_dates'] ) ) );

		$tz           = new \DateTimeZone( 'Asia/Yerevan' );
		$now          = new \DateTimeImmutable( 'now', $tz );
		$current_hour = (int) $now->format( 'G' );
		$lang         = Multilingual::current_lang();

		$weekdays = array(
			'hy' => array( 'Կիր', 'Երկ', 'Երք', 'Չոր', 'Հնգ', 'Ուրբ', 'Շաբ' ),
			'ru' => array( 'Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб' ),
			'en' => array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ),
		);
		$today_lbl = array( 'hy' => 'Այսօր', 'ru' => 'Сегодня', 'en' => 'Today' )[ $lang ] ?? 'Today';
		$tom_lbl   = array( 'hy' => 'Վաղը', 'ru' => 'Завтра', 'en' => 'Tomorrow' )[ $lang ] ?? 'Tomorrow';

		$dates = array();
		for ( $i = 0; $i < $days_ahead + 7 && count( $dates ) < $days_ahead; $i++ ) {
			if ( 0 === $i && $current_hour >= $cutoff_hour ) {
				continue;
			}
			$dt   = $now->modify( "+{$i} days" );
			$ymd  = $dt->format( 'Y-m-d' );
			$dow  = $dt->format( 'w' );

			if ( ! in_array( $dow, $enabled_days, true ) || in_array( $ymd, $blocked_raw, true ) ) {
				continue;
			}

			$day_name = $weekdays[ $lang ][ (int) $dow ] ?? $dt->format( 'D' );
			if ( 0 === $i ) {
				$day_name = $today_lbl;
			} elseif ( 1 === $i ) {
				$day_name = $tom_lbl;
			}

			$dates[] = array(
				'value' => $ymd,
				'top'   => $day_name,
				'date'  => $dt->format( 'd.m' ),
			);
		}

		return $dates;
	}

	/**
	 * Get configured time slots.
	 *
	 * @return array<int, string>
	 */
	public static function get_time_slots(): array {
		$settings = self::get_settings();
		$lines    = preg_split( '/\r\n|\r|\n/', (string) $settings['time_slots'] );
		return array_values( array_filter( array_map( 'trim', is_array( $lines ) ? $lines : array() ) ) );
	}

	/**
	 * Render modern Delivery Day & Time Pill Selector on Checkout.
	 */
	public static function render_delivery_picker(): void {
		$dates = self::get_available_dates();
		$slots = self::get_time_slots();
		?>
		<div class="gk-delivery-picker" id="gk-delivery-picker">
			<div class="gk-delivery-picker__group">
				<label class="gk-delivery-picker__label">
					<?php echo esc_html( Multilingual::t( 'Delivery Day' ) ); ?> <abbr class="required" title="required">*</abbr>
				</label>
				<div class="gk-pill-grid gk-pill-grid--dates" role="radiogroup" aria-label="<?php echo esc_attr( Multilingual::t( 'Delivery Day' ) ); ?>">
					<?php foreach ( $dates as $idx => $d ) : ?>
						<label class="gk-pill-option">
							<input
								type="radio"
								name="gayanes_delivery_date"
								value="<?php echo esc_attr( $d['value'] ); ?>"
								<?php checked( 0 === $idx ); ?>
								required
							/>
							<span class="gk-pill-card">
								<span class="gk-pill-card__top"><?php echo esc_html( $d['top'] ); ?></span>
								<strong class="gk-pill-card__main"><?php echo esc_html( $d['date'] ); ?></strong>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="gk-delivery-picker__group">
				<label class="gk-delivery-picker__label">
					<?php echo esc_html( Multilingual::t( 'Delivery Time' ) ); ?> <abbr class="required" title="required">*</abbr>
				</label>
				<div class="gk-pill-grid gk-pill-grid--times" role="radiogroup" aria-label="<?php echo esc_attr( Multilingual::t( 'Delivery Time' ) ); ?>">
					<?php foreach ( $slots as $idx => $slot ) : ?>
						<label class="gk-pill-option">
							<input
								type="radio"
								name="gayanes_delivery_time"
								value="<?php echo esc_attr( $slot ); ?>"
								<?php checked( 0 === $idx ); ?>
								required
							/>
							<span class="gk-pill-card gk-pill-card--time">
								<strong class="gk-pill-card__main"><?php echo esc_html( $slot ); ?></strong>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Validate selected delivery date & time against admin schedule.
	 */
	public static function validate_delivery_fields(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$date = isset( $_POST['gayanes_delivery_date'] ) ? sanitize_text_field( wp_unslash( $_POST['gayanes_delivery_date'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$time = isset( $_POST['gayanes_delivery_time'] ) ? sanitize_text_field( wp_unslash( $_POST['gayanes_delivery_time'] ) ) : '';

		$valid_dates = wp_list_pluck( self::get_available_dates(), 'value' );
		$valid_slots = self::get_time_slots();

		if ( '' === $date || ! in_array( $date, $valid_dates, true ) ) {
			wc_add_notice( Multilingual::t( 'Please select a valid delivery day.' ), 'error' );
		}
		if ( '' === $time || ! in_array( $time, $valid_slots, true ) ) {
			wc_add_notice( Multilingual::t( 'Please select a valid delivery time slot.' ), 'error' );
		}
	}

	/**
	 * Save delivery day & time to WooCommerce Order (HPOS-compatible).
	 *
	 * @param \WC_Order            $order Order object.
	 * @param array<string, mixed> $data  Posted checkout data.
	 */
	public static function save_delivery_meta( \WC_Order $order, array $data ): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$date = isset( $_POST['gayanes_delivery_date'] ) ? sanitize_text_field( wp_unslash( $_POST['gayanes_delivery_date'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$time = isset( $_POST['gayanes_delivery_time'] ) ? sanitize_text_field( wp_unslash( $_POST['gayanes_delivery_time'] ) ) : '';

		if ( '' !== $date ) {
			$order->update_meta_data( '_gayanes_delivery_date', $date );
		}
		if ( '' !== $time ) {
			$order->update_meta_data( '_gayanes_delivery_time', $time );
		}
		if ( '' === $order->get_billing_country() ) {
			$order->set_billing_country( 'AM' );
		}
	}

	/**
	 * Display delivery day & time in WP Admin Order screen.
	 *
	 * @param \WC_Order $order Order object.
	 */
	public static function display_admin_order_meta( \WC_Order $order ): void {
		$date = $order->get_meta( '_gayanes_delivery_date' );
		$time = $order->get_meta( '_gayanes_delivery_time' );
		if ( $date || $time ) {
			echo '<p><strong>' . esc_html( Multilingual::t( 'Delivery Schedule' ) ) . ':</strong><br/>';
			echo esc_html( trim( "{$date} · {$time}", ' ·' ) ) . '</p>';
		}
	}

	/**
	 * Display delivery schedule on Thank You & View Order pages.
	 *
	 * @param \WC_Order $order Order object.
	 */
	public static function display_order_details_meta( \WC_Order $order ): void {
		$date = $order->get_meta( '_gayanes_delivery_date' );
		$time = $order->get_meta( '_gayanes_delivery_time' );
		if ( ! $date && ! $time ) {
			return;
		}
		?>
		<div class="gk-order-delivery-box">
			<strong><?php echo esc_html( Multilingual::t( 'Scheduled Delivery' ) ); ?>:</strong>
			<span><?php echo esc_html( trim( "{$date} · {$time}", ' ·' ) ); ?></span>
		</div>
		<?php
	}

	/**
	 * Include delivery schedule in WooCommerce order emails.
	 *
	 * @param \WC_Order $order         Order object.
	 * @param bool      $sent_to_admin Sent to admin.
	 * @param bool      $plain_text    Plain text email.
	 * @param \WC_Email $email         Email object.
	 */
	public static function display_email_meta( \WC_Order $order, bool $sent_to_admin, bool $plain_text, $email ): void {
		$date = $order->get_meta( '_gayanes_delivery_date' );
		$time = $order->get_meta( '_gayanes_delivery_time' );
		if ( ! $date && ! $time ) {
			return;
		}
		if ( $plain_text ) {
			echo "\n" . esc_html( Multilingual::t( 'Scheduled Delivery' ) ) . ': ' . esc_html( trim( "{$date} · {$time}", ' ·' ) ) . "\n";
		} else {
			echo '<p><strong>' . esc_html( Multilingual::t( 'Scheduled Delivery' ) ) . ':</strong> ' . esc_html( trim( "{$date} · {$time}", ' ·' ) ) . '</p>';
		}
	}

	/**
	 * Register WP Admin submenu under WooCommerce.
	 */
	public static function register_admin_page(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Delivery Schedule', 'gayanes-core' ),
			__( 'Delivery Schedule', 'gayanes-core' ),
			'manage_woocommerce',
			'gayanes-delivery-schedule',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Register option settings.
	 */
	public static function register_settings(): void {
		register_setting(
			'gayanes_delivery_group',
			self::OPTION_KEY,
			array( __CLASS__, 'sanitize_settings' )
		);
	}

	/**
	 * Sanitize admin settings.
	 *
	 * @param array<string, mixed> $input Raw input.
	 * @return array<string, mixed>
	 */
	public static function sanitize_settings( $input ): array {
		$input = is_array( $input ) ? $input : array();
		return array(
			'enabled_days'  => isset( $input['enabled_days'] ) && is_array( $input['enabled_days'] )
				? array_values( array_map( 'sanitize_text_field', $input['enabled_days'] ) )
				: array(),
			'days_ahead'    => isset( $input['days_ahead'] ) ? max( 1, min( 30, (int) $input['days_ahead'] ) ) : 7,
			'cutoff_hour'   => isset( $input['cutoff_hour'] ) ? max( 0, min( 23, (int) $input['cutoff_hour'] ) ) : 18,
			'time_slots'    => isset( $input['time_slots'] ) ? sanitize_textarea_field( (string) $input['time_slots'] ) : '',
			'blocked_dates' => isset( $input['blocked_dates'] ) ? sanitize_text_field( (string) $input['blocked_dates'] ) : '',
		);
	}

	/**
	 * Render WP Admin Delivery Schedule settings page.
	 */
	public static function render_admin_page(): void {
		$settings = self::get_settings();
		$days_map = array(
			'1' => __( 'Monday', 'gayanes-core' ),
			'2' => __( 'Tuesday', 'gayanes-core' ),
			'3' => __( 'Wednesday', 'gayanes-core' ),
			'4' => __( 'Thursday', 'gayanes-core' ),
			'5' => __( 'Friday', 'gayanes-core' ),
			'6' => __( 'Saturday', 'gayanes-core' ),
			'0' => __( 'Sunday', 'gayanes-core' ),
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Delivery Schedule Settings (Առաքման օրեր և ժամեր)', 'gayanes-core' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'gayanes_delivery_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Available Days of Week', 'gayanes-core' ); ?></th>
						<td>
							<?php foreach ( $days_map as $code => $label ) : ?>
								<label style="display:inline-block;margin-right:14px;">
									<input
										type="checkbox"
										name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enabled_days][]"
										value="<?php echo esc_attr( $code ); ?>"
										<?php checked( in_array( $code, (array) $settings['enabled_days'], true ) ); ?>
									/>
									<?php echo esc_html( $label ); ?>
								</label>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gk_days_ahead"><?php esc_html_e( 'Show Days Ahead', 'gayanes-core' ); ?></label></th>
						<td>
							<input id="gk_days_ahead" type="number" min="1" max="30" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[days_ahead]" value="<?php echo esc_attr( (string) $settings['days_ahead'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gk_cutoff_hour"><?php esc_html_e( 'Same-Day Cutoff Hour (0-23, Yerevan)', 'gayanes-core' ); ?></label></th>
						<td>
							<input id="gk_cutoff_hour" type="number" min="0" max="23" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[cutoff_hour]" value="<?php echo esc_attr( (string) $settings['cutoff_hour'] ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gk_time_slots"><?php esc_html_e( 'Available Time Slots (one per line)', 'gayanes-core' ); ?></label></th>
						<td>
							<textarea id="gk_time_slots" rows="5" cols="40" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[time_slots]"><?php echo esc_textarea( (string) $settings['time_slots'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="gk_blocked_dates"><?php esc_html_e( 'Blocked Dates (YYYY-MM-DD, comma-separated)', 'gayanes-core' ); ?></label></th>
						<td>
							<input id="gk_blocked_dates" type="text" class="regular-text" placeholder="2026-12-31, 2027-01-01" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[blocked_dates]" value="<?php echo esc_attr( (string) $settings['blocked_dates'] ); ?>" />
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

