<?php
/**
 * WordPress Admin Settings Page & Diagnostics for Frontpad Integration.
 *
 * Location: WooCommerce -> Frontpad
 *
 * @package Gayanes_Frontpad
 */

declare( strict_types=1 );

namespace Gayanes_Frontpad;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin {

	public const PAGE_SLUG = 'gayanes-frontpad';

	/**
	 * Initialize hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 60 );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );

		// Admin AJAX tools.
		add_action( 'wp_ajax_frontpad_test_connection', array( __CLASS__, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_frontpad_sync_products', array( __CLASS__, 'ajax_sync_products' ) );
		add_action( 'wp_ajax_frontpad_sync_stops', array( __CLASS__, 'ajax_sync_stops' ) );
	}

	/**
	 * Register submenu under WooCommerce.
	 */
	public static function register_menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Frontpad POS', 'gayanes-frontpad' ),
			__( 'Frontpad POS', 'gayanes-frontpad' ),
			'manage_woocommerce',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register settings option and reschedule crons on update.
	 */
	public static function register_settings(): void {
		register_setting(
			'gayanes_frontpad_group',
			API_Client::OPTION_SETTINGS,
			array(
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			)
		);
	}

	/**
	 * Sanitize settings array and reschedule cron jobs.
	 *
	 * @param mixed $raw Raw posted settings.
	 * @return array<string, mixed>
	 */
	public static function sanitize_settings( $raw ): array {
		$raw      = is_array( $raw ) ? $raw : array();
		$existing = API_Client::get_settings();
		$clean    = $existing;

		$checkboxes = array(
			'enabled',
			'send_product_price',
			'enable_checkout_address',
			'enable_person_field',
			'auto_sync_products',
			'sync_product_names',
			'sync_product_prices',
			'create_missing_products',
			'auto_sync_stops',
			'enable_client_lookup',
			'enable_certificate',
			'require_login_lookup',
			'debug_log',
		);
		foreach ( $checkboxes as $cb ) {
			$clean[ $cb ] = ! empty( $raw[ $cb ] ) ? '1' : '0';
		}

		$text_fields = array(
			'secret',
			'webhook_token',
			'order_trigger',
			'channel',
			'affiliate',
			'point',
			'default_tags',
			'hook_statuses',
			'stops_interval',
			'recaptcha_secret',
			'recaptcha_site_key',
			'phone_prefix',
		);
		foreach ( $text_fields as $tf ) {
			if ( isset( $raw[ $tf ] ) ) {
				$clean[ $tf ] = sanitize_text_field( (string) $raw[ $tf ] );
			}
		}

		if ( isset( $raw['payment_map'] ) && is_array( $raw['payment_map'] ) ) {
			$clean['payment_map'] = array();
			foreach ( $raw['payment_map'] as $gw => $fp_pay ) {
				$clean['payment_map'][ sanitize_key( (string) $gw ) ] = sanitize_text_field( (string) $fp_pay );
			}
		}

		if ( isset( $raw['status_map_raw'] ) ) {
			$clean['status_map'] = self::parse_status_map( (string) $raw['status_map_raw'] );
		}

		// Reschedule cron jobs after settings save.
		add_action(
			'shutdown',
			static function (): void {
				Sync::reschedule_crons();
			}
		);

		return $clean;
	}

	/**
	 * Parse `fp_status=wc_status` lines into an associative array.
	 *
	 * @return array<string, string>
	 */
	private static function parse_status_map( string $raw ): array {
		$map   = array();
		$lines = preg_split( '/\r\n|\r|\n/', $raw ) ?: array();
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line || ! str_contains( $line, '=' ) ) {
				continue;
			}
			list( $fp, $wc ) = array_map( 'trim', explode( '=', $line, 2 ) );
			if ( '' !== $fp && '' !== $wc ) {
				$map[ sanitize_text_field( $fp ) ] = sanitize_key( $wc );
			}
		}
		return $map;
	}

	/**
	 * Render Admin Settings Page.
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$s           = API_Client::get_settings();
		$sync_status = get_option( Sync::OPTION_SYNC_STATUS, array() );
		$logs        = get_option( API_Client::OPTION_LOGS, array() );
		$nonce       = wp_create_nonce( 'frontpad_admin_nonce' );
		$opt         = API_Client::OPTION_SETTINGS;

		$status_map_lines = array();
		if ( is_array( $s['status_map'] ) ) {
			foreach ( $s['status_map'] as $fp => $wc ) {
				$status_map_lines[] = $fp . '=' . $wc;
			}
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Frontpad POS Integration', 'gayanes-frontpad' ); ?></h1>

			<div style="background:#fff;border:1px solid #ccd0d4;padding:14px 18px;margin:15px 0;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
				<button type="button" class="button button-secondary" id="fp-test-btn"><?php esc_html_e( 'Test Connection (get_stops)', 'gayanes-frontpad' ); ?></button>
				<button type="button" class="button button-primary" id="fp-sync-stops-btn"><?php esc_html_e( 'Sync Stop-List Now', 'gayanes-frontpad' ); ?></button>
				<button type="button" class="button button-secondary" id="fp-sync-products-btn"><?php esc_html_e( 'Sync Products (get_products)', 'gayanes-frontpad' ); ?></button>
				<span id="fp-action-feedback" style="font-weight:600;"></span>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'gayanes_frontpad_group' ); ?>

				<h2 class="title"><?php esc_html_e( '1. API Connection & Webhooks', 'gayanes-frontpad' ); ?></h2>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Enable Integration', 'gayanes-frontpad' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[enabled]" value="1" <?php checked( $s['enabled'], '1' ); ?> /> <?php esc_html_e( 'Enable Frontpad API integration', 'gayanes-frontpad' ); ?></label></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'API Secret Code', 'gayanes-frontpad' ); ?></th>
						<td><input type="password" class="regular-text" name="<?php echo esc_attr( $opt ); ?>[secret]" value="<?php echo esc_attr( (string) $s['secret'] ); ?>" autocomplete="off" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Webhook URL', 'gayanes-frontpad' ); ?></th>
						<td>
							<code><?php echo esc_html( Webhook::get_webhook_url() ); ?></code>
							<p class="description"><?php esc_html_e( 'Set this URL in Frontpad API settings or it will be passed automatically via hook_url.', 'gayanes-frontpad' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Webhook Security Token (optional)', 'gayanes-frontpad' ); ?></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt ); ?>[webhook_token]" value="<?php echo esc_attr( (string) $s['webhook_token'] ); ?>" placeholder="my-secret-token" /></td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( '2. Order Sending (new_order) & Status Mapping', 'gayanes-frontpad' ); ?></h2>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Auto-Send Trigger', 'gayanes-frontpad' ); ?></th>
						<td>
							<select name="<?php echo esc_attr( $opt ); ?>[order_trigger]">
								<option value="checkout" <?php selected( $s['order_trigger'], 'checkout' ); ?>><?php esc_html_e( 'Immediately on Checkout', 'gayanes-frontpad' ); ?></option>
								<option value="processing" <?php selected( $s['order_trigger'], 'processing' ); ?>><?php esc_html_e( 'When status becomes Processing', 'gayanes-frontpad' ); ?></option>
								<option value="on-hold" <?php selected( $s['order_trigger'], 'on-hold' ); ?>><?php esc_html_e( 'When status becomes On Hold', 'gayanes-frontpad' ); ?></option>
								<option value="manual" <?php selected( $s['order_trigger'], 'manual' ); ?>><?php esc_html_e( 'Manual only (from Order screen)', 'gayanes-frontpad' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Channel / Affiliate / Point IDs', 'gayanes-frontpad' ); ?></th>
						<td>
							<input type="text" class="small-text" name="<?php echo esc_attr( $opt ); ?>[channel]" value="<?php echo esc_attr( (string) $s['channel'] ); ?>" placeholder="channel" />
							<input type="text" class="small-text" name="<?php echo esc_attr( $opt ); ?>[affiliate]" value="<?php echo esc_attr( (string) $s['affiliate'] ); ?>" placeholder="affiliate" />
							<input type="text" class="small-text" name="<?php echo esc_attr( $opt ); ?>[point]" value="<?php echo esc_attr( (string) $s['point'] ); ?>" placeholder="point" />
							<p class="description"><?php esc_html_e( 'Optional Frontpad directory IDs for channel, affiliate, and point of sale.', 'gayanes-frontpad' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Default Tags (max 10)', 'gayanes-frontpad' ); ?></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt ); ?>[default_tags]" value="<?php echo esc_attr( (string) $s['default_tags'] ); ?>" placeholder="1, 5" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Webhook Status IDs (max 5)', 'gayanes-frontpad' ); ?></th>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( $opt ); ?>[hook_statuses]" value="<?php echo esc_attr( (string) $s['hook_statuses'] ); ?>" placeholder="3, 4" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Frontpad Status → WC Status Map', 'gayanes-frontpad' ); ?></th>
						<td>
							<textarea name="<?php echo esc_attr( $opt ); ?>[status_map_raw]" rows="4" cols="40" placeholder="3=completed&#10;4=cancelled"><?php echo esc_textarea( implode( "\n", $status_map_lines ) ); ?></textarea>
							<p class="description"><?php esc_html_e( 'One per line: frontpad_status_id=wc_status_slug (e.g., 3=completed, 4=cancelled).', 'gayanes-frontpad' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Payment Gateway → Frontpad `pay` ID', 'gayanes-frontpad' ); ?></th>
						<td>
							<?php
							if ( function_exists( 'WC' ) && WC()->payment_gateways() ) {
								foreach ( WC()->payment_gateways()->payment_gateways() as $gw_id => $gw ) {
									$val = (string) ( $s['payment_map'][ $gw_id ] ?? '' );
									echo '<div style="margin-bottom:6px;"><label style="display:inline-block;min-width:220px;">' . esc_html( $gw->get_title() ) . ' (<code>' . esc_html( $gw_id ) . '</code>):</label> ';
									echo '<input type="text" class="small-text" name="' . esc_attr( $opt ) . '[payment_map][' . esc_attr( $gw_id ) . ']" value="' . esc_attr( $val ) . '" /></div>';
								}
							}
							?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Order Options', 'gayanes-frontpad' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[send_product_price]" value="1" <?php checked( $s['send_product_price'], '1' ); ?> /> <?php esc_html_e( 'Send product_price[] (only if price override is enabled in Frontpad)', 'gayanes-frontpad' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[enable_checkout_address]" value="1" <?php checked( $s['enable_checkout_address'], '1' ); ?> /> <?php esc_html_e( 'Show Entrance / Floor / Apartment fields on Checkout', 'gayanes-frontpad' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[enable_person_field]" value="1" <?php checked( $s['enable_person_field'], '1' ); ?> /> <?php esc_html_e( 'Show Number of Persons field on Checkout', 'gayanes-frontpad' ); ?></label>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( '3. Product & Stop-List Synchronization', 'gayanes-frontpad' ); ?></h2>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Product Sync (get_products, max 1/hr)', 'gayanes-frontpad' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[auto_sync_products]" value="1" <?php checked( $s['auto_sync_products'], '1' ); ?> /> <?php esc_html_e( 'Enable hourly automatic product sync', 'gayanes-frontpad' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[sync_product_prices]" value="1" <?php checked( $s['sync_product_prices'], '1' ); ?> /> <?php esc_html_e( 'Update WooCommerce prices from Frontpad', 'gayanes-frontpad' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[sync_product_names]" value="1" <?php checked( $s['sync_product_names'], '1' ); ?> /> <?php esc_html_e( 'Overwrite WooCommerce product names from Frontpad', 'gayanes-frontpad' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[create_missing_products]" value="1" <?php checked( $s['create_missing_products'], '1' ); ?> /> <?php esc_html_e( 'Create missing SKUs as Draft products', 'gayanes-frontpad' ); ?></label>
							<?php if ( ! empty( $sync_status['products'] ) ) : ?>
								<p class="description"><?php echo esc_html( 'Last product sync: ' . wp_json_encode( $sync_status['products'] ) ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Stop-List Sync (get_stops)', 'gayanes-frontpad' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[auto_sync_stops]" value="1" <?php checked( $s['auto_sync_stops'], '1' ); ?> /> <?php esc_html_e( 'Enable automatic stop-list sync', 'gayanes-frontpad' ); ?></label>
							<select name="<?php echo esc_attr( $opt ); ?>[stops_interval]">
								<option value="frontpad_5min" <?php selected( $s['stops_interval'], 'frontpad_5min' ); ?>><?php esc_html_e( 'Every 5 Minutes', 'gayanes-frontpad' ); ?></option>
								<option value="frontpad_15min" <?php selected( $s['stops_interval'], 'frontpad_15min' ); ?>><?php esc_html_e( 'Every 15 Minutes', 'gayanes-frontpad' ); ?></option>
								<option value="hourly" <?php selected( $s['stops_interval'], 'hourly' ); ?>><?php esc_html_e( 'Hourly', 'gayanes-frontpad' ); ?></option>
							</select>
							<?php if ( ! empty( $sync_status['stops'] ) ) : ?>
								<p class="description"><?php echo esc_html( 'Last stop-list sync: ' . wp_json_encode( $sync_status['stops'] ) ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				</table>

				<h2 class="title"><?php esc_html_e( '4. Client Lookup, Certificates & Security', 'gayanes-frontpad' ); ?></h2>
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Frontend Features', 'gayanes-frontpad' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[enable_client_lookup]" value="1" <?php checked( $s['enable_client_lookup'], '1' ); ?> /> <?php esc_html_e( 'Enable Client Lookup (get_client) on Checkout & [frontpad_client_lookup]', 'gayanes-frontpad' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[enable_certificate]" value="1" <?php checked( $s['enable_certificate'], '1' ); ?> /> <?php esc_html_e( 'Enable Certificate Check (get_certificate) on Checkout & [frontpad_certificate_check]', 'gayanes-frontpad' ); ?></label><br>
							<label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[require_login_lookup]" value="1" <?php checked( $s['require_login_lookup'], '1' ); ?> /> <?php esc_html_e( 'Require customer login for get_client lookup', 'gayanes-frontpad' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Phone Prefix Normalization', 'gayanes-frontpad' ); ?></th>
						<td><input type="text" class="small-text" name="<?php echo esc_attr( $opt ); ?>[phone_prefix]" value="<?php echo esc_attr( (string) $s['phone_prefix'] ); ?>" placeholder="+374" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Google reCAPTCHA Site / Secret Key', 'gayanes-frontpad' ); ?></th>
						<td>
							<input type="text" class="regular-text" name="<?php echo esc_attr( $opt ); ?>[recaptcha_site_key]" value="<?php echo esc_attr( (string) $s['recaptcha_site_key'] ); ?>" placeholder="Site Key" /><br><br>
							<input type="password" class="regular-text" name="<?php echo esc_attr( $opt ); ?>[recaptcha_secret]" value="<?php echo esc_attr( (string) $s['recaptcha_secret'] ); ?>" placeholder="Secret Key" autocomplete="off" />
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Debug Logging', 'gayanes-frontpad' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( $opt ); ?>[debug_log]" value="1" <?php checked( $s['debug_log'], '1' ); ?> /> <?php esc_html_e( 'Log all API requests to WooCommerce logs and recent log table', 'gayanes-frontpad' ); ?></label></td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<h2 class="title"><?php esc_html_e( 'Recent API Logs', 'gayanes-frontpad' ); ?></h2>
			<table class="widefat striped">
				<thead><tr><th>Time (UTC)</th><th>Method</th><th>Status</th><th>Details</th></tr></thead>
				<tbody>
				<?php if ( empty( $logs ) ) : ?>
					<tr><td colspan="4"><?php esc_html_e( 'No logs recorded yet.', 'gayanes-frontpad' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( array_slice( $logs, 0, 20 ) as $row ) : ?>
						<tr>
							<td><?php echo esc_html( (string) ( $row['time'] ?? '' ) ); ?></td>
							<td><code><?php echo esc_html( (string) ( $row['method'] ?? '' ) ); ?></code></td>
							<td><?php echo esc_html( (string) ( $row['status'] ?? '' ) ); ?></td>
							<td><?php echo esc_html( (string) ( $row['message'] ?? '' ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<script>
		jQuery(function($){
			var nonce = '<?php echo esc_js( $nonce ); ?>';
			function runAction(action, extra) {
				var fb = $('#fp-action-feedback').css('color', '#2271b1').text('Running...');
				$.post(ajaxurl, $.extend({ action: action, nonce: nonce }, extra || {}), function(res){
					if (res.success) {
						fb.css('color', '#00a32a').text(res.data.message);
					} else {
						fb.css('color', '#d63638').text(res.data && res.data.message ? res.data.message : 'Error');
					}
				});
			}
			$('#fp-test-btn').on('click', function(){ runAction('frontpad_test_connection'); });
			$('#fp-sync-stops-btn').on('click', function(){ runAction('frontpad_sync_stops'); });
			$('#fp-sync-products-btn').on('click', function(){ runAction('frontpad_sync_products', { force: 0 }); });
		});
		</script>
		<?php
	}

	/**
	 * AJAX: Test connection via `get_stops`.
	 */
	public static function ajax_test_connection(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ) );
		}
		check_ajax_referer( 'frontpad_admin_nonce', 'nonce' );

		$res = API_Client::request( 'get_stops' );
		if ( is_wp_error( $res ) ) {
			wp_send_json_error( array( 'message' => $res->get_error_message() ) );
		}
		if ( 'success' !== ( $res['result'] ?? '' ) ) {
			wp_send_json_error( array( 'message' => API_Client::format_error( (string) ( $res['error'] ?? 'unknown' ) ) ) );
		}
		wp_send_json_success( array( 'message' => __( 'Connection successful! Frontpad API responded OK.', 'gayanes-frontpad' ) ) );
	}

	/**
	 * AJAX: Trigger product sync.
	 */
	public static function ajax_sync_products(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ) );
		}
		check_ajax_referer( 'frontpad_admin_nonce', 'nonce' );

		$res = Sync::sync_products( ! empty( $_POST['force'] ) );
		if ( is_wp_error( $res ) ) {
			wp_send_json_error( array( 'message' => $res->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'message' => sprintf(
					'Products synced: %d total, %d updated, %d created, %d skipped.',
					$res['total'],
					$res['updated'],
					$res['created'],
					$res['skipped']
				),
			)
		);
	}

	/**
	 * AJAX: Trigger stop-list sync.
	 */
	public static function ajax_sync_stops(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ) );
		}
		check_ajax_referer( 'frontpad_admin_nonce', 'nonce' );

		$res = Sync::sync_stops();
		if ( is_wp_error( $res ) ) {
			wp_send_json_error( array( 'message' => $res->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'message' => sprintf(
					'Stop-list synced: %d in Frontpad, %d marked out of stock, %d restored.',
					$res['stopped_total'],
					$res['marked_out'],
					$res['restored']
				),
			)
		);
	}
}
