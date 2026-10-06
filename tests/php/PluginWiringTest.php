<?php
/**
 * Dependency-free tests for the plugin's WordPress wiring.
 *
 * Loads the real index.php with recording hook stubs, then fires the captured
 * callbacks the way WordPress would. Covers code that only runs from a hook
 * and so had no executing test:
 *
 *   WPM-191: REST route registration and permission callbacks.
 *   WPM-192: activation/deactivation rewrite flush, deploy auto-flush,
 *            editor script versioning, course-catalog asset/block registration.
 *   WPM-187: campusdirectoryrequirements endpoint, campus-directory block
 *            registration, directoryprofilecruzid query var, /directory/ rewrite.
 *   WPM-190: class-schedule asset and block registration.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/PluginWiringTest.php
 */

define( 'ABSPATH', sys_get_temp_dir() . '/wp-mock/' );
@mkdir( ABSPATH . 'wp-admin/includes', 0777, true );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php', "<?php\nclass WP_Filesystem_Base {}\n" );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php', "<?php\nclass WP_Filesystem_Direct extends WP_Filesystem_Base {}\n" );

// ── Recording stubs ─────────────────────────────────────────────────────────

$hooks            = array(); // hook => list of callbacks
$activation_hooks = array();
$rest_routes      = array(); // "namespace/route" => args
$rewrite_rules    = array();
$block_types      = array(); // name => args
$scripts          = array(); // handle => array( src, deps, ver, enqueued )
$styles           = array();
$calls            = array(); // ordered log of side effects
$options          = array();
$site_options     = array();
$is_multisite     = false;
$environment_type = 'production';

function add_action( $hook, $callback, $priority = 10 ) {
	global $hooks;
	$hooks[ $hook ][] = $callback;
}
function add_filter( $hook, $callback, $priority = 10 ) {
	add_action( $hook, $callback, $priority );
}
function add_shortcode() {}
function register_activation_hook( $file, $callback ) {
	global $activation_hooks;
	$activation_hooks['activate'] = $callback;
}
function register_deactivation_hook( $file, $callback ) {
	global $activation_hooks;
	$activation_hooks['deactivate'] = $callback;
}
function do_hook( $hook, ...$args ) {
	global $hooks;
	foreach ( $hooks[ $hook ] ?? array() as $callback ) {
		$result = call_user_func_array( $callback, $args );
		if ( $args ) {
			$args[0] = $result;
		}
	}
	return $args[0] ?? null;
}
function register_rest_route( $namespace, $route, $args ) {
	global $rest_routes;
	$rest_routes[ $namespace . $route ] = $args;
}
function add_rewrite_rule( $regex, $query, $after ) {
	global $rewrite_rules, $calls;
	$rewrite_rules[] = array( $regex, $query, $after );
	$calls[]         = 'add_rewrite_rule';
}
function flush_rewrite_rules() {
	global $calls;
	$calls[] = 'flush_rewrite_rules';
}
function register_block_type( $name, $args ) {
	global $block_types;
	$block_types[ $name ] = $args;
}
function wp_register_script( $handle, $src, $deps = array(), $ver = false ) {
	global $scripts;
	$scripts[ $handle ] = array( 'src' => $src, 'deps' => $deps, 'ver' => $ver, 'enqueued' => false );
}
function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false ) {
	global $scripts;
	if ( $src ) {
		wp_register_script( $handle, $src, $deps, $ver );
	}
	$scripts[ $handle ]['enqueued'] = true;
}
function wp_localize_script( $handle, $object_name, $l10n ) {
	global $scripts;
	$scripts[ $handle ]['l10n'][ $object_name ] = $l10n;
	return true;
}
function wp_register_style( $handle, $src, $deps = array(), $ver = false ) {
	global $styles;
	$styles[ $handle ] = array( 'src' => $src, 'deps' => $deps, 'ver' => $ver, 'enqueued' => false );
}
function wp_enqueue_style( $handle ) {
	global $styles;
	$styles[ $handle ]['enqueued'] = true;
}
function plugins_url( $path, $file = '' ) {
	return 'https://example.ucsc.edu/wp-content/plugins/ucsc-gutenberg-blocks/classes/' . $path;
}
function plugin_dir_url( $file ) {
	return 'https://example.ucsc.edu/wp-content/plugins/ucsc-gutenberg-blocks/';
}
function get_option( $name, $default = false ) {
	global $options;
	return $options[ $name ] ?? $default;
}
function update_option( $name, $value, $autoload = null ) {
	global $options, $calls;
	$options[ $name ] = $value;
	$calls[]          = 'update_option:' . $name;
	return true;
}
function delete_option( $name ) {
	global $options, $calls;
	unset( $options[ $name ] );
	$calls[] = 'delete_option:' . $name;
	return true;
}
function get_site_option( $name, $default = false ) {
	global $site_options;
	return $site_options[ $name ] ?? $default;
}
function is_multisite() {
	global $is_multisite;
	return $is_multisite;
}
function wp_get_environment_type() {
	global $environment_type;
	return $environment_type;
}
function get_file_data( $file, $headers ) {
	$contents = file_get_contents( $file );
	preg_match( '/Version:\s*(\S+)/', $contents, $m );
	return array( 'Version' => $m[1] ?? '' );
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	class WP_REST_Response {
		private $data;
		public function __construct( $data = null ) {
			$this->data = $data;
		}
		public function get_data() {
			return $this->data;
		}
	}
}

require __DIR__ . '/helpers/harness.php';
require __DIR__ . '/../../index.php';

function reset_wiring_state() {
	global $rest_routes, $rewrite_rules, $block_types, $scripts, $styles, $calls, $options, $site_options, $is_multisite, $environment_type;
	$rest_routes      = array();
	$rewrite_rules    = array();
	$block_types      = array();
	$scripts          = array();
	$styles           = array();
	$calls            = array();
	$options          = array();
	$site_options     = array();
	$is_multisite     = false;
	$environment_type = 'production';
}

function callback_method( $callback ) {
	return is_array( $callback ) ? get_class( $callback[0] ) . '::' . $callback[1] : ( is_string( $callback ) ? $callback : 'closure' );
}

// ── WPM-191: REST routes ────────────────────────────────────────────────────

echo "REST route registration (WPM-191):\n";

reset_wiring_state();
do_hook( 'rest_api_init' );

$expected_routes = array(
	'ucscgutenbergblocks/v1/departmentcode/'              => 'SiteSettings::departmentcode',
	'ucscgutenbergblocks/v1/subjectcode/'                 => 'SiteSettings::subjectcode',
	'ucscgutenbergblocks/v1/cddepartmentcode/'            => 'SiteSettings::cddepartmentcode',
	'ucscgutenbergblocks/v1/divisioncode/'                => 'SiteSettings::divisioncode',
	'ucscgutenbergblocks/v1/campusdirectoryrequirements/' => 'CampusDirectory::requirements',
	'ucscgutenbergblocks/v1/classscheduledept/'           => 'ClassSchedule::classscheduledept',
);
foreach ( $expected_routes as $route => $method ) {
	$args = $rest_routes[ $route ] ?? null;
	check( "registers GET $route -> $method", $args && 'GET' === $args['methods'] && $method === callback_method( $args['callback'] ) );
	check( "$route is intentionally public (permission_callback returns true)", $args && true === call_user_func( $args['permission_callback'] ) );
}
$ours = array_filter( array_keys( $rest_routes ), function ( $route ) {
	return 0 === strpos( $route, 'ucscgutenbergblocks/v1/' );
} );
check( 'registers no unexpected ucscgutenbergblocks/v1 routes', count( $ours ) === count( $expected_routes ) );

// ── WPM-187: campus-directory ───────────────────────────────────────────────

echo "\ncampusdirectoryrequirements endpoint (WPM-187):\n";

$requirements = $rest_routes['ucscgutenbergblocks/v1/campusdirectoryrequirements/']['callback'];

reset_wiring_state();
$data = call_user_func( $requirements )->get_data();
check( 'reports no LDAP password when neither option is set', false === $data['ldap_pass'] );
check( 'reports a single site as not multisite', false === $data['multisite'] );

reset_wiring_state();
$site_options['ldap_api_key'] = 'secret';
$is_multisite                 = true;
$data                         = call_user_func( $requirements )->get_data();
check( 'reports an LDAP password set as a network (site) option', true === $data['ldap_pass'] );
check( 'reports multisite', true === $data['multisite'] );
check( 'never returns the LDAP password itself', false === strpos( json_encode( $data ), 'secret' ) );

reset_wiring_state();
$options['ldap_api_key'] = 'blog-secret';
$data                    = call_user_func( $requirements )->get_data();
check( 'falls back to the per-site option when no network option is set', true === $data['ldap_pass'] );

echo "\ncampus-directory registration (WPM-187):\n";

reset_wiring_state();
do_hook( 'init' );
check( 'registers ucscblocks/campusdirectory rendered by CampusDirectory::theHTML', isset( $block_types['ucscblocks/campusdirectory'] ) && 'CampusDirectory::theHTML' === callback_method( $block_types['ucscblocks/campusdirectory']['render_callback'] ) );
check( 'campus-directory block uses the shared editor script and its editor style', 'ucscblocks' === ( $block_types['ucscblocks/campusdirectory']['editor_script'] ?? '' ) && 'ucscblocks-editor' === ( $block_types['ucscblocks/campusdirectory']['editor_style'] ?? '' ) && isset( $styles['ucscblocks-editor'] ) );
check( 'registers ucscblocks/campusdirectory with block API version 3 (WPM-205)', 3 === ( $block_types['ucscblocks/campusdirectory']['api_version'] ?? null ) );
check( 'registers the directoryprofile style', isset( $styles['directoryprofile'] ) && false !== strpos( $styles['directoryprofile']['src'], 'CampusDirectory/directoryprofile.css' ) );
check( 'adds the /directory/{cruzid}/ rewrite rule', in_array( array( '^directory/([^/]+)/?$', 'index.php?directoryprofilecruzid=$matches[1]', 'top' ), $rewrite_rules, true ) );

$query_vars = do_hook( 'query_vars', array( 'existing' ) );
check( 'adds directoryprofilecruzid to the public query vars and keeps existing ones', in_array( 'directoryprofilecruzid', $query_vars, true ) && in_array( 'existing', $query_vars, true ) );

// ── WPM-190: class-schedule registration ────────────────────────────────────

echo "\nclass-schedule registration (WPM-190):\n";

check( 'registers ucscblocks/classschedule rendered by ClassSchedule::theHTML', isset( $block_types['ucscblocks/classschedule'] ) && 'ClassSchedule::theHTML' === callback_method( $block_types['ucscblocks/classschedule']['render_callback'] ) && 'ucscblocks' === $block_types['ucscblocks/classschedule']['editor_script'] );
check( 'registers ucscblocks/classschedule with block API version 3 (WPM-205)', 3 === ( $block_types['ucscblocks/classschedule']['api_version'] ?? null ) );
check( 'adds the /course/{term}/{id}/ rewrite rule', in_array( array( '^course/([0-9]+)/([0-9]+)/?$', 'index.php?course_term=$matches[1]&course_id=$matches[2]', 'top' ), $rewrite_rules, true ) );

reset_wiring_state();
do_hook( 'wp_enqueue_scripts' );
check( 'registers classschedule-js in the footer, versioned by file mtime', isset( $scripts['classschedule-js'] ) && false !== strpos( $scripts['classschedule-js']['src'], 'ClassSchedule/classschedule.js' ) && filemtime( __DIR__ . '/../../src/components/ClassSchedule/classschedule.js' ) === $scripts['classschedule-js']['ver'] );
check( 'registers the classschedule style without enqueueing it on every page', isset( $styles['classschedule'] ) && false === $styles['classschedule']['enqueued'] && false === $scripts['classschedule-js']['enqueued'] );

// ── WPM-192: course-catalog assets and plugin lifecycle ─────────────────────

echo "\ncourse-catalog registration (WPM-192):\n";

check( 'registers and enqueues the tablesorter script and style', ( $scripts['tablesorterjs']['enqueued'] ?? false ) && false !== strpos( $scripts['tablesorterjs']['src'], 'CourseCatalog/tablesorter.js' ) && ( $styles['tablesorter']['enqueued'] ?? false ) );
check( 'versions tablesorter assets by file mtime', filemtime( __DIR__ . '/../../src/components/CourseCatalog/tablesorter.js' ) === $scripts['tablesorterjs']['ver'] && filemtime( __DIR__ . '/../../src/components/CourseCatalog/tablesorter.css' ) === $styles['tablesorter']['ver'] );

reset_wiring_state();
do_hook( 'init' );
check( 'registers ucscblocks/coursecatalog rendered by CourseCatalog::theHTML', isset( $block_types['ucscblocks/coursecatalog'] ) && 'CourseCatalog::theHTML' === callback_method( $block_types['ucscblocks/coursecatalog']['render_callback'] ) && 'ucscblocks' === $block_types['ucscblocks/coursecatalog']['editor_script'] );
check( 'registers ucscblocks/coursecatalog with block API version 3 (WPM-205)', 3 === ( $block_types['ucscblocks/coursecatalog']['api_version'] ?? null ) );

echo "\nactivation and deactivation (WPM-192):\n";

reset_wiring_state();
call_user_func( $activation_hooks['activate'] );
$first_flush = array_search( 'flush_rewrite_rules', $calls, true );
$patterns    = array_column( $rewrite_rules, 0 );
check( 'activation registers the /course/, legacy course and /directory/ rewrites', in_array( '^course/([0-9]+)/([0-9]+)/?$', $patterns, true ) && in_array( '(?:.+/)?course/([0-9]+)-([0-9]+)(?:/[^/]*)?/?$', $patterns, true ) && in_array( '^directory/([^/]+)/?$', $patterns, true ) );
check( 'activation flushes only after every rewrite is registered', false !== $first_flush && count( $rewrite_rules ) === count( array_keys( array_slice( $calls, 0, $first_flush ), 'add_rewrite_rule' ) ) );

reset_wiring_state();
$options['ucsc_gutenberg_blocks_rwflush'] = '123';
call_user_func( $activation_hooks['deactivate'] );
check( 'deactivation flushes and deletes the auto-flush marker', in_array( 'flush_rewrite_rules', $calls, true ) && ! isset( $options['ucsc_gutenberg_blocks_rwflush'] ) );

echo "\ndeploy auto-flush (WPM-192):\n";

$index_mtime = (string) filemtime( __DIR__ . '/../../index.php' );

reset_wiring_state();
ucsc_gutenberg_blocks_maybe_flush_rewrites();
check( 'flushes when no marker is stored', in_array( 'flush_rewrite_rules', $calls, true ) );
check( 'stores index.php mtime as the marker', $index_mtime === ( $options['ucsc_gutenberg_blocks_rwflush'] ?? null ) );

$calls = array();
ucsc_gutenberg_blocks_maybe_flush_rewrites();
check( 'does not flush again while the plugin files are unchanged', ! in_array( 'flush_rewrite_rules', $calls, true ) );

$options['ucsc_gutenberg_blocks_rwflush'] = 'older';
$calls                                    = array();
ucsc_gutenberg_blocks_maybe_flush_rewrites();
check( 'flushes again after a deploy changes the plugin files', in_array( 'flush_rewrite_rules', $calls, true ) && $index_mtime === $options['ucsc_gutenberg_blocks_rwflush'] );

check( 'auto-flush runs on init', in_array( 'ucsc_gutenberg_blocks_maybe_flush_rewrites', $hooks['init'], true ) );

echo "\neditor script versioning (WPM-192):\n";

reset_wiring_state();
do_hook( 'admin_enqueue_scripts' );
check( 'enqueues build/index.js as the ucscblocks editor script with block-editor deps', ( $scripts['ucscblocks']['enqueued'] ?? false ) && 'https://example.ucsc.edu/wp-content/plugins/ucsc-gutenberg-blocks/build/index.js' === $scripts['ucscblocks']['src'] && in_array( 'wp-blocks', $scripts['ucscblocks']['deps'], true ) && in_array( 'wp-block-editor', $scripts['ucscblocks']['deps'], true ) );
check( 'production versions the editor script with the plugin Version header', '1.2.1' === $scripts['ucscblocks']['ver'] );
check( 'localizes ucscBlocksConfig.version as the plugin Version header (WPM-89)', '1.2.1' === ( $scripts['ucscblocks']['l10n']['ucscBlocksConfig']['version'] ?? null ) );

reset_wiring_state();
$environment_type = 'local';
do_hook( 'admin_enqueue_scripts' );
$build = __DIR__ . '/../../build/index.js';
check( 'local environments version the editor script by build file mtime', file_exists( $build ) ? filemtime( $build ) === $scripts['ucscblocks']['ver'] : '1.2.1' === $scripts['ucscblocks']['ver'] );
check( 'local environments still localize the plugin Version header, not the build mtime (WPM-89)', '1.2.1' === ( $scripts['ucscblocks']['l10n']['ucscBlocksConfig']['version'] ?? null ) );

finish_tests();
