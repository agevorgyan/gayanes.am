<?php
/**
 * Admin Settings Page for Gayane's Kitchen (Deal of the Day & Homepage / About Us Images).
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	static function (): void {
		add_menu_page(
			"Gayane's Kitchen Settings",
			"Gayane's Settings",
			'manage_options',
			'gv2-site-settings',
			'gv2_render_site_settings_page',
			'dashicons-store',
			58
		);
	}
);

add_action(
	'admin_enqueue_scripts',
	static function ( string $hook ): void {
		if ( 'toplevel_page_gv2-site-settings' !== $hook ) {
			return;
		}
		wp_enqueue_media();
	}
);

/**
 * Render the Site Settings page in WP Admin.
 */
function gv2_render_site_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$saved = false;
	if ( isset( $_POST['gv2_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gv2_settings_nonce'] ) ), 'gv2_save_site_settings' ) ) {
		set_theme_mod( 'gv2_daily_deal_product_id', absint( $_POST['gv2_daily_deal_product_id'] ?? 0 ) );
		set_theme_mod( 'gv2_home_hero_image_id', absint( $_POST['gv2_home_hero_image_id'] ?? 0 ) );
		set_theme_mod( 'gv2_about_hero_image_id', absint( $_POST['gv2_about_hero_image_id'] ?? 0 ) );
		set_theme_mod( 'gv2_about_story_image_id', absint( $_POST['gv2_about_story_image_id'] ?? 0 ) );
		set_theme_mod( 'gv2_turnstile_site_key', trim( sanitize_text_field( (string) wp_unslash( $_POST['gv2_turnstile_site_key'] ?? '' ) ) ) );
		set_theme_mod( 'gv2_turnstile_secret_key', trim( sanitize_text_field( (string) wp_unslash( $_POST['gv2_turnstile_secret_key'] ?? '' ) ) ) );
		$saved = true;
	}

	$current_deal       = absint( get_theme_mod( 'gv2_daily_deal_product_id', 0 ) );
	$home_hero_id       = absint( get_theme_mod( 'gv2_home_hero_image_id', 0 ) );
	$about_hero_id      = absint( get_theme_mod( 'gv2_about_hero_image_id', 0 ) );
	$about_story_id     = absint( get_theme_mod( 'gv2_about_story_image_id', 0 ) );
	$turnstile_site_key = (string) get_theme_mod( 'gv2_turnstile_site_key', '' );
	$turnstile_secret   = (string) get_theme_mod( 'gv2_turnstile_secret_key', '' );
	$turnstile_active   = function_exists( 'gv2_is_turnstile_enabled' ) && gv2_is_turnstile_enabled();

	$products = function_exists( 'wc_get_products' )
		? wc_get_products( array( 'status' => 'publish', 'limit' => 150, 'orderby' => 'title', 'order' => 'ASC' ) )
		: array();
	?>
	<div class="wrap">
		<h1>Gayane's Kitchen — Կայքի կարգավորումներ</h1>
		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p>Կարգավորումները պահպանվեցին։</p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'gv2_save_site_settings', 'gv2_settings_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="gv2_daily_deal_product_id">Օրվա առաջարկ (Deal of the Day)</label></th>
					<td>
						<select name="gv2_daily_deal_product_id" id="gv2_daily_deal_product_id" class="regular-text">
							<option value="0" <?php selected( $current_deal, 0 ); ?>>Ավտոմատ (առաջին զեղչված կամ պրոդուկտ)</option>
							<?php foreach ( $products as $p ) : ?>
								<option value="<?php echo esc_attr( (string) $p->get_id() ); ?>" <?php selected( $current_deal, $p->get_id() ); ?>>
									<?php echo esc_html( $p->get_name() . ' — ' . $p->get_price() . ' ֏ (#' . $p->get_id() . ')' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">Ընտրեք գլխավոր էջի «Օրվա առաջարկ» բաժնի պրոդուկտը։</p>
					</td>
				</tr>

				<?php
				$image_fields = array(
					'gv2_home_hero_image_id'   => array( 'title' => 'Գլխավոր էջի նկար (Hero Image)', 'val' => $home_hero_id ),
					'gv2_about_hero_image_id'  => array( 'title' => 'Մեր մասին էջի գլխավոր նկար', 'val' => $about_hero_id ),
					'gv2_about_story_image_id' => array( 'title' => 'Մեր մասին էջի երկրորդական նկար', 'val' => $about_story_id ),
				);
				foreach ( $image_fields as $field_id => $meta ) :
					$preview_url = $meta['val'] > 0 ? wp_get_attachment_image_url( $meta['val'], 'medium' ) : '';
					?>
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $meta['title'] ); ?></label></th>
						<td>
							<input type="hidden" name="<?php echo esc_attr( $field_id ); ?>" id="<?php echo esc_attr( $field_id ); ?>" value="<?php echo esc_attr( (string) $meta['val'] ); ?>" />
							<div class="gv2-media-preview" id="<?php echo esc_attr( $field_id ); ?>_preview" style="margin-bottom:8px;">
								<?php if ( $preview_url ) : ?>
									<img src="<?php echo esc_url( $preview_url ); ?>" style="max-width:220px;height:auto;border-radius:10px;border:1px solid #ddd;" alt="" />
								<?php endif; ?>
							</div>
							<button type="button" class="button gv2-pick-media" data-target="<?php echo esc_attr( $field_id ); ?>">Ընտրել / Վերբեռնել նկար</button>
							<button type="button" class="button gv2-clear-media" data-target="<?php echo esc_attr( $field_id ); ?>">Մաքրել</button>
						</td>
					</tr>
				<?php endforeach; ?>

				<tr>
					<th scope="row">Cloudflare Turnstile (CAPTCHA)</th>
					<td>
						<p>
							<strong>Կարգավիճակ․</strong>
							<?php if ( $turnstile_active ) : ?>
								<span style="color:#0a7d32;font-weight:600;">Ակտիվ է</span>
							<?php else : ?>
								<span style="color:#b33a3a;font-weight:600;">Անջատված է (լրացրեք երկու բանալիները)</span>
							<?php endif; ?>
						</p>
						<p class="description">
							Ստացեք անվճար Site Key և Secret Key <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" rel="noopener noreferrer">Cloudflare Turnstile</a>-ից։
							Կարող եք նաև սահմանել <code>GV2_TURNSTILE_SITE_KEY</code> և <code>GV2_TURNSTILE_SECRET_KEY</code> հաստատունները <code>wp-config.php</code>-ում։
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gv2_turnstile_site_key">Turnstile Site Key</label></th>
					<td>
						<input type="text" name="gv2_turnstile_site_key" id="gv2_turnstile_site_key" class="regular-text" value="<?php echo esc_attr( $turnstile_site_key ); ?>" placeholder="0x4AAAAAAA..." autocomplete="off" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="gv2_turnstile_secret_key">Turnstile Secret Key</label></th>
					<td>
						<input type="password" name="gv2_turnstile_secret_key" id="gv2_turnstile_secret_key" class="regular-text" value="<?php echo esc_attr( $turnstile_secret ); ?>" placeholder="0x4AAAAAAA..." autocomplete="new-password" />
					</td>
				</tr>
			</table>

			<?php submit_button( 'Պահպանել կարգավորումները' ); ?>
		</form>
	</div>
	<script>
	jQuery(function($){
		$('.gv2-pick-media').on('click', function(e){
			e.preventDefault();
			var target = $(this).data('target');
			var frame = wp.media({ title: 'Ընտրել նկար', button: { text: 'Օգտագործել' }, multiple: false });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				$('#' + target).val(att.id);
				var url = (att.sizes && att.sizes.medium) ? att.sizes.medium.url : att.url;
				$('#' + target + '_preview').html('<img src="' + url + '" style="max-width:220px;height:auto;border-radius:10px;border:1px solid #ddd;" alt="" />');
			});
			frame.open();
		});
		$('.gv2-clear-media').on('click', function(){
			var target = $(this).data('target');
			$('#' + target).val('0');
			$('#' + target + '_preview').empty();
		});
	});
	</script>
	<?php
}
