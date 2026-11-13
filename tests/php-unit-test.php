<?php
/**
 * PHP Unit Smoke Tests for Gayanes Core modules.
 * Runs in PHP CLI with minimal WordPress stubs.
 */

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$mock_locale = 'hy';
function determine_locale(): string {
	global $mock_locale;
	return $mock_locale;
}
function add_action( string $hook, $cb, int $priority = 10 ): void {}
function add_filter( string $hook, $cb, int $priority = 10, int $args = 1 ): void {}
function remove_action( string $hook, $cb, int $priority = 10 ): void {}
function has_filter( string $hook ): bool { return false; }
function has_action( string $hook ): bool { return false; }
function home_url( string $path = '/' ): string { return 'https://gayanes.am' . $path; }
function __( string $text, string $domain = 'default' ): string { return $text; }

require_once __DIR__ . '/../wp-content/plugins/gayanes-core/includes/class-multilingual.php';
require_once __DIR__ . '/../wp-content/plugins/gayanes-core/includes/class-security.php';

// 1. Test Armenian (default)
$mock_locale = 'hy';
assert( Gayanes_Core\Multilingual::current_lang() === 'hy', 'Expected hy locale' );
assert( Gayanes_Core\Multilingual::t( 'Add to Cart' ) === 'Ավելացնել զամբյուղ', 'Expected HY Add to Cart' );

// 2. Test Russian
$mock_locale = 'ru_RU';
assert( Gayanes_Core\Multilingual::current_lang() === 'ru', 'Expected ru locale' );
assert( Gayanes_Core\Multilingual::t( 'Add to Cart' ) === 'В корзину', 'Expected RU Add to Cart' );

// 3. Test English
$mock_locale = 'en_US';
assert( Gayanes_Core\Multilingual::current_lang() === 'en', 'Expected en locale' );
assert( Gayanes_Core\Multilingual::t( 'Add to Cart' ) === 'Add to Cart', 'Expected EN Add to Cart' );

// 4. Test Language Switcher
$switcher = Gayanes_Core\Multilingual::get_switcher_items();
assert( count( $switcher ) === 3, 'Expected 3 languages in switcher' );
assert( $switcher[0]['code'] === 'hy' && $switcher[0]['url'] === 'https://gayanes.am/' );
assert( $switcher[1]['code'] === 'ru' && $switcher[1]['url'] === 'https://gayanes.am/ru/' );
assert( $switcher[2]['code'] === 'en' && $switcher[2]['url'] === 'https://gayanes.am/en/' && $switcher[2]['current'] === true );

echo "ALL PHP UNIT TESTS PASSED (HY/RU/EN localization, switcher URLs).\n";

