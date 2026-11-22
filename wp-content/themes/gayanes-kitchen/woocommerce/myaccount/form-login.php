<?php
/**
 * Login & Registration Form — iOS 27 Liquid Glass Auth Experience.
 *
 * Overrides WooCommerce's `myaccount/form-login.php` while preserving all
 * WooCommerce hooks, nonces, and field names.
 *
 * @package GayanesV2
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

$registration_enabled = ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) );
// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
$is_register_active = $registration_enabled && ( isset( $_POST['register'] ) || ( isset( $_GET['action'] ) && 'register' === sanitize_key( wp_unslash( $_GET['action'] ) ) ) );

do_action( 'woocommerce_before_customer_login_form' );
?>

<div class="gv2-auth-shell">
	<div class="gv2-auth-card" id="customer_login" data-auth-initial="<?php echo $is_register_active ? 'register' : 'login'; ?>">

		<div class="gv2-auth-header">
			<img src="<?php echo esc_url( GV2_URI . '/assets/images/logo-dark.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="gv2-auth-logo" width="136" height="60" />
			<h2 class="gv2-auth-title"><?php echo esc_html( gv2_t( 'Welcome Back' ) ); ?></h2>
			<p class="gv2-auth-subtitle"><?php echo esc_html( gv2_t( 'Manage your orders, saved addresses, and favorite dishes.' ) ); ?></p>
		</div>

		<?php if ( $registration_enabled ) : ?>
			<div class="gv2-auth-tabs" role="tablist" aria-label="<?php echo esc_attr( gv2_t( 'Account' ) ); ?>">
				<span class="gv2-auth-tabs__thumb" aria-hidden="true"></span>
				<button
					type="button"
					role="tab"
					class="gv2-auth-tab<?php echo ! $is_register_active ? ' is-active' : ''; ?>"
					data-auth-tab="login"
					aria-selected="<?php echo ! $is_register_active ? 'true' : 'false'; ?>"
					aria-controls="gv2-panel-login"
				>
					<?php echo esc_html( gv2_t( 'Sign In' ) ); ?>
				</button>
				<button
					type="button"
					role="tab"
					class="gv2-auth-tab<?php echo $is_register_active ? ' is-active' : ''; ?>"
					data-auth-tab="register"
					aria-selected="<?php echo $is_register_active ? 'true' : 'false'; ?>"
					aria-controls="gv2-panel-register"
				>
					<?php echo esc_html( gv2_t( 'Register' ) ); ?>
				</button>
			</div>
		<?php endif; ?>

		<div class="gv2-auth-panels">
			<!-- Login Panel -->
			<div
				id="gv2-panel-login"
				class="gv2-auth-panel<?php echo ! $is_register_active ? ' is-active' : ''; ?>"
				role="tabpanel"
				<?php echo $is_register_active ? 'hidden' : ''; ?>
			>
				<form class="woocommerce-form woocommerce-form-login login gv2-auth-form" method="post" novalidate>

					<?php do_action( 'woocommerce_login_form_start' ); ?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gv2-auth-field">
						<label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
						<span class="gv2-input-icon-wrap">
							<svg class="gv2-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" />
						</span>
					</p>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gv2-auth-field">
						<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
						<span class="gv2-input-icon-wrap gv2-input-icon-wrap--pw">
							<svg class="gv2-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="3"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
							<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" />
							<button type="button" class="gv2-pw-toggle" data-pw-toggle="password" aria-label="<?php echo esc_attr( gv2_t( 'Show password' ) ); ?>" data-show-label="<?php echo esc_attr( gv2_t( 'Show password' ) ); ?>" data-hide-label="<?php echo esc_attr( gv2_t( 'Hide password' ) ); ?>">
								<svg class="gv2-pw-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
								<svg class="gv2-pw-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
							</button>
						</span>
					</p>

					<?php do_action( 'woocommerce_login_form' ); ?>

					<div class="gv2-auth-meta-row">
						<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
							<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
							<span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
						</label>
						<a class="gv2-auth-lost-link" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></a>
					</div>

					<p class="form-row gv2-auth-submit-row">
						<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
						<button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>
					</p>

					<?php do_action( 'woocommerce_login_form_end' ); ?>

					<?php if ( $registration_enabled ) : ?>
						<p class="gv2-auth-switch">
							<span><?php echo esc_html( gv2_t( 'New to Gayane’s?' ) ); ?></span>
							<button type="button" class="gv2-auth-switch__btn" data-auth-switch="register"><?php echo esc_html( gv2_t( 'Create an account' ) ); ?></button>
						</p>
					<?php endif; ?>

				</form>
			</div>

			<?php if ( $registration_enabled ) : ?>
				<!-- Register Panel -->
				<div
					id="gv2-panel-register"
					class="gv2-auth-panel<?php echo $is_register_active ? ' is-active' : ''; ?>"
					role="tabpanel"
					<?php echo ! $is_register_active ? 'hidden' : ''; ?>
				>
					<form method="post" class="woocommerce-form woocommerce-form-register register gv2-auth-form" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

						<?php do_action( 'woocommerce_register_form_start' ); ?>

						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gv2-auth-field">
								<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
								<span class="gv2-input-icon-wrap">
									<svg class="gv2-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 5-6 8-6s6.5 2 8 6"/></svg>
									<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" />
								</span>
							</p>
						<?php endif; ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gv2-auth-field">
							<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
							<span class="gv2-input-icon-wrap">
								<svg class="gv2-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
								<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" />
							</span>
						</p>

						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide gv2-auth-field">
								<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
								<span class="gv2-input-icon-wrap gv2-input-icon-wrap--pw">
									<svg class="gv2-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="3"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
									<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" />
									<button type="button" class="gv2-pw-toggle" data-pw-toggle="reg_password" aria-label="<?php echo esc_attr( gv2_t( 'Show password' ) ); ?>" data-show-label="<?php echo esc_attr( gv2_t( 'Show password' ) ); ?>" data-hide-label="<?php echo esc_attr( gv2_t( 'Hide password' ) ); ?>">
										<svg class="gv2-pw-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
										<svg class="gv2-pw-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
									</button>
								</span>
							</p>
						<?php else : ?>
							<p class="gv2-auth-info-pill"><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
						<?php endif; ?>

						<?php do_action( 'woocommerce_register_form' ); ?>

						<p class="woocommerce-form-row form-row gv2-auth-submit-row">
							<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
							<button type="submit" class="woocommerce-Button woocommerce-button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"><?php esc_html_e( 'Register', 'woocommerce' ); ?></button>
						</p>

						<?php do_action( 'woocommerce_register_form_end' ); ?>

						<p class="gv2-auth-switch">
							<span><?php echo esc_html( gv2_t( 'Already have an account?' ) ); ?></span>
							<button type="button" class="gv2-auth-switch__btn" data-auth-switch="login"><?php echo esc_html( gv2_t( 'Sign In' ) ); ?></button>
						</p>

					</form>
				</div>
			<?php endif; ?>
		</div>

		<div class="gv2-auth-perks" aria-hidden="true">
			<span class="gv2-auth-perk">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z" stroke-linejoin="round"/></svg>
				<?php echo esc_html( gv2_t( 'Fast Checkout' ) ); ?>
			</span>
			<span class="gv2-auth-perk">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2" stroke-linecap="round"/></svg>
				<?php echo esc_html( gv2_t( 'Order History' ) ); ?>
			</span>
			<span class="gv2-auth-perk">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
				<?php echo esc_html( gv2_t( 'Saved Addresses' ) ); ?>
			</span>
		</div>

	</div>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
