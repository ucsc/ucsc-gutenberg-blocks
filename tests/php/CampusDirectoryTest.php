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
$ldap_searches             = array();
$ldap_options              = array();
$ldap_search_result        = true;
$transients                = array();

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
if (!defined('LDAP_ESCAPE_FILTER')) define('LDAP_ESCAPE_FILTER', 0);

function ldap_connect() { return true; }
function ldap_set_option($link, $option, $value) {
	global $ldap_options;
	$ldap_options[] = array(
		'option' => $option,
		'value'  => $value,
	);
	return true;
}
function ldap_bind() { return true; }
function ldap_search($link, $base_dn, $filter, $attributes = array()) {
	global $ldap_searches, $ldap_search_result;
	$ldap_searches[] = array(
		'base_dn'    => $base_dn,
		'filter'     => $filter,
		'attributes' => $attributes,
	);
	return $ldap_search_result;
}
function ldap_first_entry() { return false; }
function ldap_next_entry() { return false; }
function ldap_get_attributes() { return array(); }
function ldap_get_values() { return array(); }
function ldap_get_values_len() { return array(); }
function ldap_first_attribute() { return false; }
function ldap_next_attribute() { return false; }
function ldap_close() { return true; }
function ldap_error() { return ''; }
// Mirror the real extension's filter escaping for the characters that matter
// so tests can prove wildcard/injection input is neutralized.
function ldap_escape($str, $ignore = '', $flags = 0) {
	return str_replace(
		array( '\\', '*', '(', ')' ),
		array( '\5c', '\2a', '\28', '\29' ),
		$str
	);
}
function get_transient($key) {
	global $transients;
	return isset( $transients[ $key ] ) ? $transients[ $key ]['value'] : false;
}
function set_transient($key, $value, $expiration) {
	global $transients;
	$transients[ $key ] = array(
		'value'      => $value,
		'expiration' => $expiration,
	);
	return true;
}
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

function campus_directory_api_fixture( $overrides = array() ) {
	$defaults = array(
		'cruzidList'                    => 'jsmith',
		'pageLayout'                    => 'list',
		'linkToProfile'                 => false,
		'automatedFeeds'                => false,
		'manualAdd'                     => false,
		'excludeCruzids'                => '',
		'addCruzids'                    => '',
		'department'                    => '---',
		'division'                      => '---',
		'deptOrDiv'                     => 'dept',
		'displayDeptartmentAffiliates'  => false,
		'objGradTypes'                  => array(
			'Grad Students' => false,
		),
		'objFacultyTypes'               => array(
			'All'       => false,
			'Senate'    => false,
			'Lecturer'  => false,
			'Emeritus'  => false,
		),
		'objStaffTypes'                 => array(
			'Regular Staff'          => false,
			'Researcher'             => false,
			'Postdoctoral Scholar'   => false,
		),
		'objInformationTypes'           => array(),
		'objInformationTypesTable'      => array(),
	);

	return new CampusDirectoryAPI( array_replace_recursive( $defaults, $overrides ) );
}

function ldap_size_limit_was_set_to( $expected ) {
	global $ldap_options;

	foreach ( $ldap_options as $option ) {
		if ( LDAP_OPT_SIZELIMIT === $option['option'] && $expected === $option['value'] ) {
			return true;
		}
	}

	return false;
}

function transient_keys() {
	global $transients;
	return array_keys( $transients );
}

function reset_test_state() {
	global $query_vars, $is_admin, $is_singular, $is_main_query, $queried_object_id, $current_post_id, $ldap_searches, $ldap_options, $ldap_search_result, $transients;
	$query_vars         = array();
	$is_admin           = false;
	$is_singular        = false;
	$is_main_query      = false;
	$queried_object_id  = 1;
	$current_post_id    = 1;
	$ldap_searches      = array();
	$ldap_options       = array();
	$ldap_search_result = true;
	$transients         = array();
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

echo "CampusDirectoryAPI LDAP query tests:\n";

reset_test_state();
$api = campus_directory_api_fixture();
$api->getCampusDirData( 'jsmith' );
check( 'list views request a limited LDAP attribute list', isset( $ldap_searches[0] ) && in_array( 'uid', $ldap_searches[0]['attributes'], true ) && ! in_array( 'jpegphoto', $ldap_searches[0]['attributes'], true ) );
check( 'list views apply the configured-feed size ceiling', ldap_size_limit_was_set_to( 1000 ) );

reset_test_state();
$api = campus_directory_api_fixture();
$api->getCampusDirData( 'jsmith', true );
check( 'profile views request all LDAP attributes for profile rendering', isset( $ldap_searches[0] ) && array( '*' ) === $ldap_searches[0]['attributes'] );

reset_test_state();
$api = campus_directory_api_fixture();
$api->getCampusDirData( 'jsmith' );
$listKeys = transient_keys();
$api->getCampusDirData( 'jsmith', true );
$listAndProfileKeys = transient_keys();
check( 'list and profile queries use separate transient keys for different LDAP attribute sets', 1 === count( $listKeys ) && 2 === count( $listAndProfileKeys ) && $listKeys[0] !== $listAndProfileKeys[1] );

reset_test_state();
$api = campus_directory_api_fixture(
	array(
		'automatedFeeds' => true,
		'department'     => 'MATH',
		'objFacultyTypes' => array(
			'All'       => true,
			'Senate'    => false,
			'Lecturer'  => false,
			'Emeritus'  => false,
		),
	)
);
$api->getCampusDirData( '' );
check( 'automated feed list queries also use limited LDAP attributes', isset( $ldap_searches[0] ) && in_array( 'uid', $ldap_searches[0]['attributes'], true ) && ! in_array( 'jpegphoto', $ldap_searches[0]['attributes'], true ) );

reset_test_state();
$api = campus_directory_api_fixture(
	array(
		'automatedFeeds' => true,
		'manualAdd'      => true,
		'excludeCruzids' => 'jsmith',
	)
);
$filter = $api->buildFilterString();
check( 'exclude-only automated feeds do not build a whole-directory LDAP filter', '' === $filter );
$api->getCampusDirData( '' );
check( 'exclude-only automated feeds do not issue an empty LDAP search', 0 === count( $ldap_searches ) );

reset_test_state();
$api = campus_directory_api_fixture(
	array(
		'automatedFeeds' => true,
		'manualAdd'      => true,
		'excludeCruzids' => 'jsmith',
		'department'     => 'MATH',
		'objFacultyTypes' => array(
			'All'       => true,
			'Senate'    => false,
			'Lecturer'  => false,
			'Emeritus'  => false,
		),
	)
);
$filter = $api->buildFilterString();
check( 'exclude is retained when there is an automated feed filter to subtract from', false !== strpos( $filter, '(!(uid=jsmith))' ) );

echo "CampusDirectoryAPI hardening tests (WPM-103):\n";

reset_test_state();
$api = campus_directory_api_fixture( array( 'cruzidList' => 'jsmith, *' ) );
$data = $api->getCampusDirData( 'jsmith, *' );
check( 'wildcard cruzids are escaped in manual list filters', false !== strpos( $data[1], '(uid=\2a)' ) && false === strpos( $data[1], '(uid=*)' ) );

reset_test_state();
$api = campus_directory_api_fixture(
	array(
		'automatedFeeds' => true,
		'manualAdd'      => true,
		'addCruzids'     => '*',
	)
);
$filter = $api->buildFilterString();
check( 'wildcard add cruzids are escaped in feed filters', false !== strpos( $filter, '(uid=\2a)' ) && false === strpos( $filter, '(uid=*)' ) );

reset_test_state();
$api = campus_directory_api_fixture( array( 'cruzidList' => '' ) );
$data = $api->getCampusDirData( '' );
check( 'empty manual cruzid list issues no LDAP search', 0 === count( $ldap_searches ) && array() === $data[0] );

reset_test_state();
$api = campus_directory_api_fixture( array( 'cruzidList' => 'jsmith,, ' ) );
$data = $api->getCampusDirData( 'jsmith,, ' );
check( 'blank entries in a cruzid list are skipped', 1 === substr_count( $data[1], '(uid=' ) );

reset_test_state();
$ldap_search_result = false;
$api = campus_directory_api_fixture();
$data = $api->getCampusDirData( 'jsmith' );
check( 'a failed LDAP search returns an empty result instead of fataling', array() === $data[0] );

reset_test_state();
$api = campus_directory_api_fixture();
$api->getCampusDirData( 'jsmith' );
$api->getCampusDirData( 'jsmith' );
$transient_values = array_values( $transients );
check( 'empty results are cached so repeat views issue one LDAP search', 1 === count( $ldap_searches ) );
check( 'empty results use the short negative-cache expiration', 1 === count( $transient_values ) && 60 === $transient_values[0]['expiration'] );

reset_test_state();
$api = campus_directory_api_fixture();
$api->getCampusDirData( 'jsmith' );
$time_limit_ok = false;
foreach ( $ldap_options as $option ) {
	if ( LDAP_OPT_TIMELIMIT === $option['option'] && 15 === $option['value'] ) {
		$time_limit_ok = true;
	}
}
check( 'LDAP time limit stays under the 30s edge proxy timeout', $time_limit_ok );

reset_test_state();
$api = campus_directory_api_fixture();
$api->getDirDropdowns( 'ucscpersonpubdepartmentnumber' );
$api->getDirDropdowns( 'ucscpersonpubdivision' );
check( 'getDirDropdowns can run twice in one request without redeclaring its sorter', true );
check( 'getDirDropdowns requests only the grouping attribute from LDAP', isset( $ldap_searches[0] ) && in_array( 'ucscpersonpubdepartmentnumber', $ldap_searches[0]['attributes'], true ) && ! in_array( '*', $ldap_searches[0]['attributes'], true ) );

echo "\n" . ( $tests - $failed ) . "/$tests passed\n";
exit( 0 === $failed ? 0 : 1 );
