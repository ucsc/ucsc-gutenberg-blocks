<?php
/**
 * Dependency-free tests for CampusDirectory.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/CampusDirectoryTest.php
 */

$template_include_callback = null;
$the_content_callback      = null;
$query_vars                = array();
$is_admin                  = false;
$is_singular               = false;
$is_main_query             = false;
$queried_object_id         = 1;
$current_post_id           = 1;
$included_files            = array();

// Fake ABSPATH to avoid fatal errors when CampusDirectoryAPI requires wp-admin files.
define( 'ABSPATH', sys_get_temp_dir() . '/wp-mock/' );
@mkdir( ABSPATH . 'wp-admin/includes', 0777, true );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php', "<?php\nclass WP_Filesystem_Base {}\n" );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php', "<?php\nclass WP_Filesystem_Direct extends WP_Filesystem_Base {}\n" );

function add_action( $hook, $callback, $priority = 10 ) {
	add_filter( $hook, $callback, $priority );
}
function add_filter( $hook, $callback, $priority = 10 ) {
	global $template_include_callback, $the_content_callback;
	if ( 'template_include' === $hook ) {
		$template_include_callback = $callback;
	}
	if ( 'the_content' === $hook ) {
		$the_content_callback = $callback;
	}
}
function register_rest_route() {}
function add_rewrite_rule() {}
function get_query_var( $var ) {
	global $query_vars;
	return isset( $query_vars[ $var ] ) ? $query_vars[ $var ] : false;
}
function is_admin() {
	global $is_admin;
	return $is_admin;
}
function is_singular() {
	global $is_singular;
	return $is_singular;
}
function is_main_query() {
	global $is_main_query;
	return $is_main_query;
}
function get_the_ID() {
	global $current_post_id;
	return $current_post_id;
}
function get_queried_object_id() {
	global $queried_object_id;
	return $queried_object_id;
}
function plugin_dir_path( $file ) {
	return dirname( $file ) . '/';
}
function plugins_url( $path ) {
	return 'http://example.com/wp-content/plugins/ucsc-gutenberg-blocks/' . $path;
}
function wp_register_style() {}
function get_site_option() { return ''; }

if (!defined('LDAP_OPT_TIMELIMIT')) define('LDAP_OPT_TIMELIMIT', 0);
if (!defined('LDAP_OPT_PROTOCOL_VERSION')) define('LDAP_OPT_PROTOCOL_VERSION', 0);
if (!defined('LDAP_OPT_REFERRALS')) define('LDAP_OPT_REFERRALS', 0);
if (!defined('LDAP_OPT_NETWORK_TIMEOUT')) define('LDAP_OPT_NETWORK_TIMEOUT', 0);
if (!defined('LDAP_OPT_SIZELIMIT')) define('LDAP_OPT_SIZELIMIT', 0);

function ldap_connect() { return true; }
function ldap_set_option() { return true; }
function ldap_bind() { return true; }
function ldap_search() { return true; }
function ldap_first_entry() { return false; }
function ldap_next_entry() { return false; }
function ldap_get_attributes() { return array(); }
function ldap_get_values() { return array(); }
function ldap_get_values_len() { return array(); }
function ldap_first_attribute() { return false; }
function ldap_next_attribute() { return false; }
function ldap_close() { return true; }
function ldap_error() { return ''; }
function ldap_escape($str) { return $str; }
function get_transient() { return false; }
function set_transient() { return true; }
function get_option() { return ''; }

// We also need to mock a few template functions that DirectoryProfileTemplate calls
function get_theme_file_path() { return false; }
function get_header() {}
function get_footer() {}
function wp_kses_post($str) { return $str; }
function esc_url($str) { return $str; }
function esc_html($str) { return $str; }

require __DIR__ . '/../../classes/CampusDirectory.php';

$tests  = 0;
$failed = 0;

function check( $label, $condition ) {
	global $tests, $failed;
	$tests++;
	if ( $condition ) {
		echo "  PASS  $label\n";
		return;
	}
	$failed++;
	echo "  FAIL  $label\n";
}

function reset_test_state() {
	global $query_vars, $is_admin, $is_singular, $is_main_query, $queried_object_id, $current_post_id;
	$query_vars        = array();
	$is_admin          = false;
	$is_singular       = false;
	$is_main_query     = false;
	$queried_object_id = 1;
	$current_post_id   = 1;
}

$campus_directory = new CampusDirectory();

echo "template_include tests:\n";

reset_test_state();
$template = $template_include_callback( 'index.php' );
check( 'returns original template when cruzid is missing', 'index.php' === $template );

reset_test_state();
$query_vars['directoryprofilecruzid'] = 'jsmith';
$is_singular = true;
$template = $template_include_callback( 'index.php' );
check( 'returns original template for singular pages (relies on the_content)', 'index.php' === $template );

reset_test_state();
$query_vars['directoryprofilecruzid'] = 'jsmith';
$is_singular = false;
$template = $template_include_callback( 'index.php' );
check( 'returns standalone DirectoryProfileTemplate when not singular', false !== strpos( $template, 'DirectoryProfileTemplate.php' ) );

echo "the_content tests (renderDirectoryProfile):\n";

reset_test_state();
$content = $the_content_callback( '<p>Original</p>' );
check( 'returns original content when cruzid is missing', '<p>Original</p>' === $content );

reset_test_state();
$query_vars['directoryprofilecruzid'] = 'jsmith';
$is_admin = true;
$content = $the_content_callback( '<p>Original</p>' );
check( 'returns original content in admin area', '<p>Original</p>' === $content );

reset_test_state();
$query_vars['directoryprofilecruzid'] = 'jsmith';
$is_singular = false;
$content = $the_content_callback( '<p>Original</p>' );
check( 'returns original content when not singular', '<p>Original</p>' === $content );

reset_test_state();
$query_vars['directoryprofilecruzid'] = 'jsmith';
$is_singular = true;
$is_main_query = false;
$content = $the_content_callback( '<p>Original</p>' );
check( 'returns original content when not the main query', '<p>Original</p>' === $content );

reset_test_state();
$query_vars['directoryprofilecruzid'] = 'jsmith';
$is_singular = true;
$is_main_query = true;
$queried_object_id = 1;
$current_post_id = 2;
$content = $the_content_callback( '<p>Original</p>' );
check( 'returns original content for posts inside a loop (ID mismatch)', '<p>Original</p>' === $content );

reset_test_state();
$query_vars['directoryprofilecruzid'] = 'jsmith';
$is_singular = true;
$is_main_query = true;
$queried_object_id = 1;
$current_post_id = 1;
$content = $the_content_callback( '<p>Original</p>' );
check( 'concatenates the profile output to the original content', false !== strpos( $content, '<p>Original</p>' ) && false !== strpos( $content, 'jsmith' ) );
check( 'does not render the <main> wrapper since it is inline', false === strpos( $content, '<main class="is-layout-flow' ) );

echo "\n" . ( $tests - $failed ) . "/$tests passed\n";
exit( 0 === $failed ? 0 : 1 );
