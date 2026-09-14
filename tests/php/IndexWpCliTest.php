<?php
/**
 * WPM-166: Dependency-free tests for the WP-CLI `course-catalog-cache clear`
 * command wiring registered in index.php.
 *
 * Only CourseCatalog::clearCachedCourses() itself was previously unit tested
 * (see CourseCatalogTest.php's "cache clearing" section). The CLI
 * registration, --target arg parsing/default, and WP_CLI::success() message
 * formatting were never exercised.
 *
 * index.php's `WP_CLI::add_command(...)` call sits at file top level guarded
 * by `if (defined('WP_CLI') && WP_CLI)`, so this test defines a capturing
 * WP_CLI stub, sets that constant, and requires index.php directly to pull
 * the real registered closure out — the same "capture and invoke the real
 * closure" shape used for the REST validate_callback tests in
 * CourseScheduleAPITest.php (WPM-157).
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/IndexWpCliTest.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Fake ABSPATH to avoid fatal errors when CampusDirectoryAPI (pulled in
	// transitively via index.php's CampusDirectory include) requires
	// wp-admin files. Mirrors CampusDirectoryTest.php's setup.
	define( 'ABSPATH', sys_get_temp_dir() . '/wp-mock/' );
	@mkdir( ABSPATH . 'wp-admin/includes', 0777, true );
	@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php', "<?php\nclass WP_Filesystem_Base {}\n" );
	@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php', "<?php\nclass WP_Filesystem_Direct extends WP_Filesystem_Base {}\n" );
}
define( 'WP_CLI', true );

// ── WordPress stubs needed for index.php's top-level execution ──────────────
//
// Every class index.php include_once's is constructed at file scope, but each
// constructor only registers deferred hook callbacks (add_action/add_filter/
// add_shortcode) — none of them execute WordPress-dependent work immediately.
// These no-op stubs are therefore sufficient to let the whole file load.

function add_action() {}
function add_filter() {}
function add_shortcode() {}
function register_activation_hook() {}
function register_deactivation_hook() {}

// ── WP_CLI capture stub ──────────────────────────────────────────────────────

$wp_cli_registered_commands = array();
$wp_cli_success_messages    = array();

class WP_CLI {
	public static function add_command( $name, $callback ) {
		global $wp_cli_registered_commands;
		$wp_cli_registered_commands[ $name ] = $callback;
	}

	public static function success( $message ) {
		global $wp_cli_success_messages;
		$wp_cli_success_messages[] = $message;
	}
}

// ── Test double for $wpdb, matching CourseCatalogTest.php's Test_WPDB ───────
//
// CourseCatalog::clearCachedCourses() is a real static method invoked by the
// CLI closure; controlling $wpdb lets us assert on the exact query it issues
// and the deleted-row count the command reports back through WP_CLI::success().

class Test_WPDB {
	public $options = 'wp_options';
	public $last_query;
	public $next_deleted_count = 2;

	public function esc_like( $text ) {
		return addcslashes( $text, '_%\\' );
	}

	public function prepare( $query, ...$args ) {
		foreach ( $args as $arg ) {
			$query = preg_replace( '/%s/', "'" . $arg . "'", $query, 1 );
		}
		return $query;
	}

	public function query( $query ) {
		$this->last_query = $query;
		return $this->next_deleted_count;
	}
}

$wpdb = new Test_WPDB();

require __DIR__ . '/helpers/harness.php';

// index.php include_once's classes/CourseCatalog.php etc. with their real
// paths relative to __FILE__ — require it from its real location so those
// resolve correctly, rather than copying/re-implementing the registration.
require __DIR__ . '/../../index.php';

function reset_wp_cli_test_state() {
	global $wp_cli_success_messages, $wpdb;
	$wp_cli_success_messages = array();
	$wpdb->last_query        = null;
	$wpdb->next_deleted_count = 2;
}

echo "WP-CLI course-catalog-cache clear command wiring:\n";

check(
	'registers the ucsc course-catalog-cache clear command',
	isset( $wp_cli_registered_commands['ucsc course-catalog-cache clear'] )
);
check(
	'registered command is callable',
	is_callable( $wp_cli_registered_commands['ucsc course-catalog-cache clear'] )
);

$cli_command = $wp_cli_registered_commands['ucsc course-catalog-cache clear'];

// No --target: defaults to 'all'.
reset_wp_cli_test_state();
$cli_command( array(), array() );
check(
	'no --target arg: defaults to "all" and hits the unscoped transient prefix',
	false !== strpos( $wpdb->last_query, $wpdb->esc_like( '_transient_course-catalog-' ) )
		&& false === strpos( $wpdb->last_query, $wpdb->esc_like( '_transient_course-catalog-csqa-' ) )
);
check(
	'no --target arg: success message reports "all"',
	false !== strpos( $wp_cli_success_messages[0], 'target "all"' )
);

// --target=qa: aliases to the csqa transient prefix (same alias CourseCatalog::clearCachedCourses applies).
reset_wp_cli_test_state();
$cli_command( array(), array( 'target' => 'qa' ) );
check(
	'--target=qa: reaches CourseCatalog::clearCachedCourses and maps to the csqa prefix',
	false !== strpos( $wpdb->last_query, $wpdb->esc_like( '_transient_course-catalog-csqa-' ) )
);
check(
	'--target=qa: success message reports the literal "qa" arg value, not the internal alias',
	false !== strpos( $wp_cli_success_messages[0], 'target "qa"' )
);

// --target=prod: passed through as a literal prefix (no alias applies).
reset_wp_cli_test_state();
$cli_command( array(), array( 'target' => 'prod' ) );
check(
	'--target=prod: scoped to the prod transient prefix',
	false !== strpos( $wpdb->last_query, $wpdb->esc_like( '_transient_course-catalog-prod-' ) )
);
check(
	'--target=prod: success message reports "prod"',
	false !== strpos( $wp_cli_success_messages[0], 'target "prod"' )
);

// Deleted row count from clearCachedCourses() is reported verbatim in the success message.
reset_wp_cli_test_state();
$wpdb->next_deleted_count = 7;
$cli_command( array(), array( 'target' => 'all' ) );
check(
	'success message reports the real deleted-row count from clearCachedCourses()',
	false !== strpos( $wp_cli_success_messages[0], 'Deleted 7 Course Catalog transient row' )
);

reset_wp_cli_test_state();
$wpdb->next_deleted_count = 0;
$cli_command( array(), array( 'target' => 'all' ) );
check(
	'zero deleted rows still reports success with a 0 count (not silently skipped)',
	false !== strpos( $wp_cli_success_messages[0], 'Deleted 0 Course Catalog transient row' )
);

finish_tests();
