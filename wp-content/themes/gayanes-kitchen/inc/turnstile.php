<?php
/**
 * Cloudflare Turnstile CAPTCHA integration for GayanesV2.
 *
 * Protects Contact, Newsletter, WooCommerce Auth (Login, Register, Lost Password),
 * WooCommerce Checkout, WordPress Core Login/Register, and Comment forms.
 *
 * Keys can be defined in wp-config.php via GV2_TURNSTILE_SITE_KEY /
 * GV2_TURNSTILE_SECRET_KEY or configured in WP Admin -> Gayane's Settings.
 *
 * @package GayanesV2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get Cloudflare Turnstile Site Key.
 */
function gv2_get_turnstile_site_key(): string {
	if ( defined( 'GV2_TURNSTILE_SITE_KEY' ) && is_string( GV2_TURNSTILE_SITE_KEY ) && '' !== trim( GV2_TURNSTILE_SITE_KEY ) ) {
		return trim( GV2_TURNSTILE_SITE_KEY );
	}
	return trim( (string) get_theme_mod( 'gv2_turnstile_site_key', '' ) );
}

/**
 * Get Cloudflare Turnstile Secret Key.
 */
function gv2_get_turnstile_secret_key(): string {
	if ( defined( 'GV2_TURNSTILE_SECRET_KEY' ) && is_string( GV2_TURNSTILE_SECRET_KEY ) && '' !== trim( GV2_TURNSTILE_SECRET_KEY ) ) {
		return trim( GV2_TURNSTILE_SECRET_KEY );
	}
	return trim( (string) get_theme_mod( 'gv2_turnstile_secret_key', '' ) );
}

/**
 * Check whether Turnstile is configured and active.
 */
function gv2_is_turnstile_enabled(): bool {
	return '' !== gv2_get_turnstile_site_key() && '' !== gv2_get_turnstile_secret_key();
}

/**
 * Enqueue Cloudflare Turnstile API script on frontend and wp-login.php.
 */
function gv2_enqueue_turnstile_script(): void {
	if ( ! gv2_is_turnstile_enabled() ) {
		return;
	}

	wp_enqueue_script(
		'cloudflare-turnstile',
		'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=gv2InitTurnstile',
		array(),
		null,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	$inline_init = <<<'JS'
window.gv2InitTurnstile = function () {
	if (!window.turnstile || typeof window.turnstile.render !== 'function') return;
	document.querySelectorAll('.cf-turnstile').forEach(function (el) {
		if (el.dataset.gv2Rendered === '1' || el.querySelector('iframe')) return;
		el.dataset.gv2Rendered = '1';
		try {
			window.turnstile.render(el);
		} catch (e) {
			delete el.dataset.gv2Rendered;
		}
	});
};
JS;
	wp_add_inline_script( 'cloudflare-turnstile', $inline_init, 'before' );
}
add_action( 'wp_enqueue_scripts', 'gv2_enqueue_turnstile_script' );
add_action( 'login_enqueue_scripts', 'gv2_enqueue_turnstile_script' );

/**
 * Add async/defer attributes to Turnstile script tag for older WP compatibility.
 *
 * @param string $tag    Script HTML tag.
 * @param string $handle Script handle.
 */
function gv2_turnstile_script_attributes( string $tag, string $handle ): string {
	if ( 'cloudflare-turnstile' !== $handle ) {
		return $tag;
	}
	if ( ! str_contains( $tag, ' async' ) ) {
		$tag = str_replace( ' src=', ' async defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'gv2_turnstile_script_attributes', 10, 2 );

/**
 * Render the Cloudflare Turnstile widget container.
 *
 * @param string $action      Optional Turnstile action label (e.g. 'contact', 'login').
 * @param string $extra_class Optional extra CSS class for wrapper.
 */
function gv2_render_turnstile_widget( string $action = '', string $extra_class = '' ): void {
	if ( ! gv2_is_turnstile_enabled() ) {
		return;
	}

	$site_key = gv2_get_turnstile_site_key();
	$raw_lang = function_exists( 'gv2_current_lang' ) ? gv2_current_lang() : 'hy';
	$lang     = in_array( $raw_lang, array( 'en', 'ru' ), true ) ? $raw_lang : 'auto';
	$classes  = trim( 'gv2-turnstile-wrap ' . $extra_class );
	?>
	<div class="<?php echo esc_attr( $classes ); ?>">
		<div
			class="cf-turnstile"
			data-sitekey="<?php echo esc_attr( $site_key ); ?>"
			data-theme="light"
			data-language="<?php echo esc_attr( $lang ); ?>"
			<?php if ( '' !== $action ) : ?>
				data-action="<?php echo esc_attr( sanitize_key( $action ) ); ?>"
			<?php endif; ?>
		></div>
	</div>
	<?php
}

/**
 * Verify a Cloudflare Turnstile response token via siteverify API.
 *
 * Caches verification results per-request for identical tokens so multiple
 * WordPress/WooCommerce hooks on the same POST do not trigger duplicate errors.
 *
 * @param string|null $token Optional token; defaults to $_POST['cf-turnstile-response'].
 * @return true|WP_Error True on success, WP_Error on failure.
 */
function gv2_verify_turnstile_token( ?string $token = null ) {
	if ( ! gv2_is_turnstile_enabled() ) {
		return true;
	}

	static $request_cache = array();

	if ( null === $token ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$token = isset( $_POST['cf-turnstile-response'] ) ? trim( sanitize_text_field( (string) wp_unslash( $_POST['cf-turnstile-response'] ) ) ) : '';
	} else {
		$token = trim( sanitize_text_field( $token ) );
	}

	if ( '' === $token ) {
		return new WP_Error(
			'turnstile_missing',
			function_exists( 'gv2_t' ) ? gv2_t( 'Please complete the security check.' ) : __( 'Please complete the security check.', 'gayanes-kitchen' )
		);
	}

	if ( isset( $request_cache[ $token ] ) ) {
		return $request_cache[ $token ];
	}

	$body = array(
		'secret'   => gv2_get_turnstile_secret_key(),
		'response' => $token,
	);

	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP );
		if ( false !== $ip ) {
			$body['remoteip'] = $ip;
		}
	}

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'timeout' => 8,
			'body'    => $body,
		)
	);

	if ( is_wp_error( $response ) ) {
		$err                     = new WP_Error(
			'turnstile_http_error',
			function_exists( 'gv2_t' ) ? gv2_t( 'Security check failed. Please try again.' ) : __( 'Security check failed. Please try again.', 'gayanes-kitchen' )
		);
		$request_cache[ $token ] = $err;
		return $err;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );

	if ( 200 !== $code || ! is_array( $data ) || empty( $data['success'] ) ) {
		$err                     = new WP_Error(
			'turnstile_failed',
			function_exists( 'gv2_t' ) ? gv2_t( 'Security check failed. Please try again.' ) : __( 'Security check failed. Please try again.', 'gayanes-kitchen' )
		);
		$request_cache[ $token ] = $err;
		return $err;
	}

	$request_cache[ $token ] = true;
	return true;
}

/* --------------------------------------------------------------------------
 * WooCommerce Forms: Login, Register, Lost Password, Checkout
 * -------------------------------------------------------------------------- */

add_action(
	'woocommerce_login_form',
	static function (): void {
		gv2_render_turnstile_widget( 'wc_login', 'gv2-turnstile-wrap--auth' );
	}
);

add_filter(
	'woocommerce_process_login_errors',
	static function ( $validation_error ) {
		$check = gv2_verify_turnstile_token();
		if ( is_wp_error( $check ) ) {
			return $check;
		}
		return $validation_error;
	},
	10,
	1
);

add_action(
	'woocommerce_register_form',
	static function (): void {
		gv2_render_turnstile_widget( 'wc_register', 'gv2-turnstile-wrap--auth' );
	}
);

add_filter(
	'woocommerce_registration_errors',
	static function ( WP_Error $errors ): WP_Error {
		$check = gv2_verify_turnstile_token();
		if ( is_wp_error( $check ) ) {
			$errors->add( $check->get_error_code(), $check->get_error_message() );
		}
		return $errors;
	},
	10,
	1
);

add_action(
	'woocommerce_lostpassword_form',
	static function (): void {
		gv2_render_turnstile_widget( 'lost_password', 'gv2-turnstile-wrap--auth' );
	}
);

add_action(
	'woocommerce_review_order_before_submit',
	static function (): void {
		gv2_render_turnstile_widget( 'wc_checkout', 'gv2-turnstile-wrap--checkout' );
	}
);

add_action(
	'woocommerce_checkout_process',
	static function (): void {
		$check = gv2_verify_turnstile_token();
		if ( is_wp_error( $check ) && function_exists( 'wc_add_notice' ) ) {
			wc_add_notice( $check->get_error_message(), 'error' );
		}
	}
);

/* --------------------------------------------------------------------------
 * WordPress Core Forms: wp-login.php, Register, Lost Password, Comments
 * -------------------------------------------------------------------------- */

add_action(
	'login_form',
	static function (): void {
		gv2_render_turnstile_widget( 'wp_login', 'gv2-turnstile-wrap--wp-login' );
	}
);

add_filter(
	'authenticate',
	static function ( $user, string $username, string $password ) {
		if ( ! gv2_is_turnstile_enabled() ) {
			return $user;
		}
		if ( '' === $username && '' === $password ) {
			return $user;
		}
		// Skip when WooCommerce handles login via woocommerce_process_login_errors,
		// or for REST/XML-RPC/CLI requests.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['woocommerce-login-nonce'] ) ) {
			return $user;
		}
		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return $user;
		}
		// Only enforce on standard wp-login.php POST submissions.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['wp-submit'] ) ) {
			return $user;
		}

		$check = gv2_verify_turnstile_token();
		if ( is_wp_error( $check ) ) {
			return $check;
		}
		return $user;
	},
	30,
	3
);

add_action(
	'register_form',
	static function (): void {
		gv2_render_turnstile_widget( 'wp_register', 'gv2-turnstile-wrap--wp-login' );
	}
);

add_filter(
	'registration_errors',
	static function ( WP_Error $errors ): WP_Error {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['woocommerce-register-nonce'] ) ) {
			return $errors;
		}
		$check = gv2_verify_turnstile_token();
		if ( is_wp_error( $check ) ) {
			$errors->add( $check->get_error_code(), $check->get_error_message() );
		}
		return $errors;
	},
	10,
	1
);

add_action(
	'lostpassword_form',
	static function (): void {
		gv2_render_turnstile_widget( 'lost_password', 'gv2-turnstile-wrap--wp-login' );
	}
);

add_action(
	'lostpassword_post',
	static function ( WP_Error $errors ): void {
		$check = gv2_verify_turnstile_token();
		if ( is_wp_error( $check ) ) {
			$errors->add( $check->get_error_code(), $check->get_error_message() );
		}
	},
	10,
	1
);

add_action(
	'comment_form_after_fields',
	static function (): void {
		gv2_render_turnstile_widget( 'comment', 'gv2-turnstile-wrap--comment' );
	}
);

add_action(
	'comment_form_logged_in_after',
	static function (): void {
		gv2_render_turnstile_widget( 'comment', 'gv2-turnstile-wrap--comment' );
	}
);

add_filter(
	'preprocess_comment',
	static function ( array $commentdata ): array {
		if ( is_admin() ) {
			return $commentdata;
		}
		$check = gv2_verify_turnstile_token();
		if ( is_wp_error( $check ) ) {
			wp_die(
				esc_html( $check->get_error_message() ),
				esc_html__( 'Comment Submission Failure', 'gayanes-kitchen' ),
				array( 'back_link' => true )
			);
		}
		return $commentdata;
	}
);
