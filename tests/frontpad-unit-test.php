<?php
/**
 * PHP Unit Tests for Gayane's Frontpad Integration plugin.
 * Runs in PHP CLI with minimal WordPress stubs.
 */

declare( strict_types=1 );

define( 'ABSPATH', __DIR__ . '/' );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'MINUTE_IN_SECONDS', 60 );

// Minimal WordPress function stubs.
function add_action( string $hook, $cb, int $priority = 10, int $args = 1 ): void {}
function add_filter( string $hook, $cb, int $priority = 10, int $args = 1 ): void {}
function add_shortcode( string $tag, $cb ): void {}
function __( string $text, string $domain = 'default' ): string { return $text; }
function sanitize_text_field( string $str ): string { return trim( strip_tags( $str ) ); }
function sanitize_key( string $key ): string { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) ) ?? ''; }
function wp_parse_args( $args, array $defaults = array() ): array { return array_merge( $defaults, (array) $args ); }
function get_option( string $option, $default = false ) { return $default; }
function update_option( string $option, $value, $autoload = null ): bool { return true; }
function is_wp_error( $thing ): bool { return $thing instanceof WP_Error; }

class WP_Error {
	private string $code;
	private string $message;
	public function __construct( string $code = '', string $message = '' ) {
		$this->code    = $code;
		$this->message = $message;
	}
	public function get_error_code(): string { return $this->code; }
	public function get_error_message(): string { return $this->message; }
}

$plugin_dir = __DIR__ . '/../wp-content/plugins/gayanes-frontpad/includes/';
require_once $plugin_dir . 'class-api-client.php';
require_once $plugin_dir . 'class-sync.php';
require_once $plugin_dir . 'class-orders.php';
require_once $plugin_dir . 'class-webhook.php';

// 1. Test build_post_body matching Frontpad PHP example (pizza + 2 modifiers + juice + tags + hook_status).
$body = Gayanes_Frontpad\API_Client::build_post_body(
	array(
		'secret'      => 'sec123',
		'street'      => 'Мира',
		'home'        => '17',
		'apart'       => '6',
		'phone'       => '79000000001',
		'tags'        => array( 1, 5 ),
		'hook_status' => array( 3, 4 ),
		'product'     => array( '10000', '10001', '10002', '10003' ),
		'product_kol' => array( '1', '1', '1', '1' ),
		'product_mod' => array( 1 => '0', 2 => '0' ),
	)
);

assert( str_contains( $body, 'secret=sec123' ), 'Missing secret' );
assert( str_contains( $body, 'street=' . rawurlencode( 'Мира' ) ), 'UTF-8 street encoding mismatch' );
assert( str_contains( $body, 'tags[0]=1' ) && str_contains( $body, 'tags[1]=5' ), 'Tags array mismatch' );
assert( str_contains( $body, 'hook_status[0]=3' ) && str_contains( $body, 'hook_status[1]=4' ), 'hook_status array mismatch' );
assert( str_contains( $body, 'product[0]=10000' ) && str_contains( $body, 'product_mod[1]=0' ) && str_contains( $body, 'product_mod[2]=0' ), 'Product/modifier array mismatch' );

// 2. Test address parser & phone normalization & int list limits.
$addr = Gayanes_Frontpad\Orders::parse_address( 'Մաշտոցի պող. 17, բն. 6' );
assert( $addr['street'] === 'Մաշտոցի պող.' && $addr['home'] === '17' && $addr['apart'] === 'բն. 6', 'Address parser failed' );

$phone = Gayanes_Frontpad\Orders::normalize_phone( '091 12-34-56', '+374' );
assert( $phone === '+37491123456', 'Phone normalization failed: ' . $phone );

$tags = Gayanes_Frontpad\Orders::parse_int_list( '1, 2, abc, 3, 4, 5, 6', 5 );
assert( count( $tags ) === 5 && $tags === array( '1', '2', '3', '4', '5' ), 'parse_int_list limit failed' );

// 3. Test get_products / get_stops parallel array normalization.
$norm = Gayanes_Frontpad\Sync::normalize_product_arrays(
	array(
		'result'     => 'success',
		'product_id' => array( '0' => '001', '1' => '002' ),
		'name'       => array( '0' => 'Филадельфия', '1' => 'Калифорния' ),
		'price'      => array( '0' => '250', '1' => '350' ),
		'sale'       => array( '0' => '1', '1' => '0' ),
	)
);
assert( count( $norm ) === 2 && $norm[0]['sku'] === '001' && $norm[1]['sale'] === '0', 'normalize_product_arrays failed' );

// 4. Test webhook payload validation.
$bad_wh = Gayanes_Frontpad\Webhook::process_payload( array( 'action' => 'unknown' ) );
assert( $bad_wh instanceof WP_Error && $bad_wh->get_error_code() === 'invalid_payload', 'Webhook validation failed' );

echo "ALL FRONTPAD UNIT TESTS PASSED.\n";
