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
$ldap_bind_result          = true;
$ldap_errno_result         = 0;
$ldap_entries              = array();
$ldap_attr_cursor          = array();
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
	global $template_include_callback, $the_content_callback, $template_redirect_callback;
	if ( 'template_redirect' === $hook ) {
		$template_redirect_callback = $callback;
	}
	if ( 'template_include' === $hook ) {
		$template_include_callback = $callback;
	}
	if ( 'the_content' === $hook ) {
		$the_content_callback = $callback;
	}
	// Filters the photo cache adds around one write; the media-store
	// wp_upload_bits() applies them the way core does.
	if ( in_array( $hook, array( 'upload_dir', 'pre_wp_unique_filename_file_list' ), true ) ) {
		$GLOBALS['upload_filters'][ $hook ] = $callback;
	}
}
function remove_filter( $hook, $callback, $priority = 10 ) {
	if ( ( $GLOBALS['upload_filters'][ $hook ] ?? null ) === $callback ) {
		unset( $GLOBALS['upload_filters'][ $hook ] );
	}
	return true;
}
function register_rest_route() {}
$status_headers = array();
function status_header( $code ) {
	global $status_headers;
	$status_headers[] = $code;
}
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
function get_site_option( $name ) {
	global $site_options;
	return $site_options[ $name ] ?? '';
}

if (!defined('LDAP_OPT_TIMELIMIT')) define('LDAP_OPT_TIMELIMIT', 0);
if (!defined('LDAP_OPT_PROTOCOL_VERSION')) define('LDAP_OPT_PROTOCOL_VERSION', 0);
if (!defined('LDAP_OPT_REFERRALS')) define('LDAP_OPT_REFERRALS', 0);
if (!defined('LDAP_OPT_NETWORK_TIMEOUT')) define('LDAP_OPT_NETWORK_TIMEOUT', 0);
if (!defined('LDAP_OPT_SIZELIMIT')) define('LDAP_OPT_SIZELIMIT', 0);
// Distinct from the zero-valued stubs above so a check cannot match the wrong option.
if (!defined('LDAP_OPT_TIMEOUT')) define('LDAP_OPT_TIMEOUT', 0x5002);
if (!defined('LDAP_ESCAPE_FILTER')) define('LDAP_ESCAPE_FILTER', 0);

$ldap_connect_uris  = array();
$ldap_bind_args     = array();
$ldap_len_reads     = array();
function ldap_connect( $uri = '' ) {
	global $ldap_connect_uris;
	$ldap_connect_uris[] = $uri;
	return true;
}
function ldap_set_option($link, $option, $value) {
	global $ldap_options;
	$ldap_options[] = array(
		'option' => $option,
		'value'  => $value,
	);
	return true;
}
function ldap_bind() {
	global $ldap_bind_result, $ldap_bind_args;
	$ldap_bind_args[] = array_slice( func_get_args(), 1 );
	return isset( $ldap_bind_result ) ? $ldap_bind_result : true;
}
function ldap_search($link, $base_dn, $filter, $attributes = array()) {
	global $ldap_searches, $ldap_search_result;
	$ldap_searches[] = array(
		'base_dn'    => $base_dn,
		'filter'     => $filter,
		'attributes' => $attributes,
	);
	return $ldap_search_result;
}
// Entry iteration is driven by $ldap_entries: a list of lowercase
// attribute => values maps, where values carry a 'count' key like the real
// extension. An entry handle is its 1-based position, so it is never falsy.
// With no entries seeded every search returns nothing.
function ldap_first_entry( $link, $result ) {
	global $ldap_entries;
	return ( $result && count( $ldap_entries ) ) ? 1 : false;
}
function ldap_next_entry( $link, $entry ) {
	global $ldap_entries;
	return isset( $ldap_entries[ $entry ] ) ? $entry + 1 : false;
}
function ldap_get_attributes( $link, $entry ) {
	global $ldap_entries;
	return $ldap_entries[ $entry - 1 ];
}
function ldap_get_values( $link, $entry, $attr ) {
	global $ldap_entries;
	return $ldap_entries[ $entry - 1 ][ $attr ];
}
function ldap_get_values_len( $link, $entry, $attr ) {
	global $ldap_len_reads;
	$ldap_len_reads[] = $attr;
	return ldap_get_values( $link, $entry, $attr );
}
function ldap_first_attribute( $link, $entry ) {
	global $ldap_entries, $ldap_attr_cursor;
	$ldap_attr_cursor[ $entry ] = 0;
	$keys = array_keys( $ldap_entries[ $entry - 1 ] );
	return $keys[0] ?? false;
}
function ldap_next_attribute( $link, $entry ) {
	global $ldap_entries, $ldap_attr_cursor;
	$keys = array_keys( $ldap_entries[ $entry - 1 ] );
	return $keys[ ++$ldap_attr_cursor[ $entry ] ] ?? false;
}
function ldap_close() { return true; }
function ldap_error() { return ''; }
$ldap_freed_results = 0;
function ldap_free_result( $result ) {
	$GLOBALS['ldap_freed_results']++;
	return true;
}
function ldap_errno() {
	global $ldap_errno_result;
	return $ldap_errno_result;
}
// Mirror the real extension's filter escaping for the characters that matter
// so tests can prove wildcard/injection input is neutralized.
function ldap_escape($str, $ignore = '', $flags = 0) {
	return str_replace(
		array( '\\', '*', '(', ')', "\0" ),
		array( '\5c', '\2a', '\28', '\29', '\00' ),
		$str
	);
}
// TLS preflight before each LDAP connection (WPM-233). Defined before the class
// file loads, so it replaces the real network check.
$ldap_handshake_ok    = true;
$ldap_handshake_calls = array();
function ucsc_cd_ldap_handshake_ok( $host, $timeout ) {
	global $ldap_handshake_ok, $ldap_handshake_calls;
	$ldap_handshake_calls[] = array( 'host' => $host, 'timeout' => $timeout );
	return $ldap_handshake_ok;
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
function get_option( $name ) {
	global $options;
	return $options[ $name ] ?? '';
}

// campusdirectory.ucsc.edu photo.php scrape. Default response mirrors photo.php
// for a uid with no photo: HTTP 500, empty body.
if (!defined('MINUTE_IN_SECONDS')) define('MINUTE_IN_SECONDS', 60);
$remote_get_calls    = array();
$remote_get_response = array( 'code' => 500, 'body' => '' );
function wp_remote_get( $url, $args = array() ) {
	global $remote_get_calls, $remote_get_response;
	$remote_get_calls[] = array( 'url' => $url, 'args' => $args );
	return $remote_get_response;
}
function wp_remote_retrieve_body( $response ) {
	return isset( $response['body'] ) ? $response['body'] : '';
}
function wp_remote_retrieve_response_code( $response ) {
	return isset( $response['code'] ) ? $response['code'] : '';
}

// We also need to mock a few template functions that DirectoryProfileTemplate calls
function get_theme_file_path() { return false; }
function get_header() {}
function get_footer() {}
function wp_kses_post($str) { return $str; }
function esc_url($str) { return $str; }
function esc_html($str) { return $str; }

require __DIR__ . '/../../classes/CampusDirectory.php';

// WPM-117: Use the shared instrumented harness. The harness guards all its stubs
// with function_exists, so our custom test-specific stubs above win.
require __DIR__ . '/helpers/harness.php';
require __DIR__ . '/helpers/media-store.php';

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
		// WPM-171: these are the faculty types the block actually offers, from
		// src/components/CampusDirectory/AutomatedFeeds.js. The fixture previously
		// declared 'Senate'/'Emeritus', which no UI control ever sets; that went
		// unnoticed while tests only exercised the 'All' branch.
		'objFacultyTypes'               => array(
			'All'                         => false,
			'Regular Faculty'             => false,
			'Lecturer'                    => false,
			'Emeriti'                     => false,
			'Research Professor'          => false,
			'Researcher'                  => false,
			'Adjunct Faculty'             => false,
			'Visiting Scholar'            => false,
			'Graduate Student Instructor' => false,
			'Retired'                     => false,
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
	global $query_vars, $is_admin, $is_singular, $is_main_query, $queried_object_id, $current_post_id, $ldap_searches, $ldap_options, $ldap_search_result, $ldap_bind_result, $ldap_entries, $ldap_attr_cursor, $transients, $ldap_connect_uris, $ldap_bind_args, $ldap_len_reads, $remote_get_calls, $remote_get_response, $ldap_handshake_ok, $ldap_handshake_calls, $ldap_errno_result, $site_options, $options, $status_headers;
	$site_options        = array();
	$options             = array();
	$status_headers      = array();
	$remote_get_calls    = array();
	$remote_get_response = array( 'code' => 500, 'body' => '' );
	$ldap_connect_uris  = array();
	$ldap_bind_args     = array();
	$ldap_len_reads     = array();
	$query_vars         = array();
	$is_admin           = false;
	$is_singular        = false;
	$is_main_query      = false;
	$queried_object_id  = 1;
	$current_post_id    = 1;
	$ldap_searches      = array();
	$ldap_options       = array();
	$ldap_search_result = true;
	$ldap_bind_result   = true;
	$ldap_errno_result  = 0;
	$ldap_entries       = array();
	$ldap_attr_cursor   = array();
	$transients         = array();
	$ldap_handshake_ok    = true;
	$ldap_handshake_calls = array();
	reset_media_store();
	// Each case is its own request.
	$GLOBALS['ldap_freed_results'] = 0;
	CampusDirectoryAPI::resetSharedConnections();
	CampusDirectoryPhotoCache::resetRequestBudget();
}

/**
 * Build a directory entry in the shape ldap_get_values() returns.
 *
 * @param array $attrs attribute => list of values.
 * @return array
 */
function ldap_entry_fixture( $attrs ) {
	$entry = array( 'ucscpersonpubdisplay' => array( 'TRUE', 'count' => 1 ) );
	foreach ( $attrs as $name => $values ) {
		$entry[ $name ] = $values + array( 'count' => count( $values ) );
	}
	return $entry;
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
check( 'returns standalone DirectoryProfileTemplate on singular pages too (WPM-114)', false !== strpos( $template, 'DirectoryProfileTemplate.php' ) );

reset_test_state();
$query_vars['directoryprofilecruzid'] = 'jsmith';
$is_singular = false;
$template = $template_include_callback( 'index.php' );
check( 'returns standalone DirectoryProfileTemplate when not singular', false !== strpos( $template, 'DirectoryProfileTemplate.php' ) );

echo "profile status tests (WPM-247):\n";

reset_test_state();
$query_vars = array( 'directoryprofilecruzid' => 'zzqnotreal9' );
call_user_func( $template_redirect_callback );
check( 'an unknown CruzID gets a 404 status', array( 404 ) === $status_headers );

reset_test_state();
$query_vars   = array( 'directoryprofilecruzid' => 'jdoe' );
$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'jdoe' ) ) ) );
call_user_func( $template_redirect_callback );
check( 'a known CruzID keeps the 200 status', array() === $status_headers );

reset_test_state();
$query_vars = array( 'directoryprofilecruzid' => 'jdoe' );
$transients['ucsc_cd_ldap_down'] = array( 'value' => 1, 'expiration' => 300 );
call_user_func( $template_redirect_callback );
check( 'no 404 while LDAP is marked down', array() === $status_headers );

reset_test_state();
$query_vars       = array( 'directoryprofilecruzid' => 'jdoe' );
$ldap_bind_result = false;
call_user_func( $template_redirect_callback );
check( 'no 404 when the LDAP bind fails', array() === $status_headers );

reset_test_state();
$query_vars         = array( 'directoryprofilecruzid' => 'jdoe' );
$ldap_search_result = false;
call_user_func( $template_redirect_callback );
check( 'no 404 when the LDAP search fails', array() === $status_headers );

reset_test_state();
$query_vars        = array( 'directoryprofilecruzid' => 'jdoe' );
$ldap_handshake_ok = false;
call_user_func( $template_redirect_callback );
check( 'no 404 when LDAP does not answer', array() === $status_headers );

reset_test_state();
call_user_func( $template_redirect_callback );
check( 'pages without a CruzID are left alone', array() === $status_headers );

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

echo "LDAP settings scope (WPM-244):\n";

function ldap_url_for( $network, $site ) {
	global $site_options, $options;
	reset_test_state();
	$site_options = $network;
	$options      = $site;
	$api = new CampusDirectoryAPI( array() );
	$r   = new ReflectionProperty( $api, 'ldap_url' );
	$r->setAccessible( true );
	return $r->getValue( $api );
}

check( 'network password: a site ldap_url is ignored, the default is used',
	'ldap-blue.ucsc.edu' === ldap_url_for( array( 'ldap_api_key' => 'netpw' ), array( 'ldap_url' => 'evil.example.com' ) ) );
check( 'network password: the network ldap_url is used',
	'ldap-gold.ucsc.edu' === ldap_url_for( array( 'ldap_api_key' => 'netpw', 'ldap_url' => 'ldap-gold.ucsc.edu' ), array( 'ldap_url' => 'evil.example.com' ) ) );
check( 'site password: the site ldap_url is used',
	'ldap-gold.ucsc.edu' === ldap_url_for( array(), array( 'ldap_api_key' => 'sitepw', 'ldap_url' => 'ldap-gold.ucsc.edu' ) ) );

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

echo "CampusDirectoryAPI cache TTL tests (WPM-176):\n";

reset_test_state();
$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) ) );
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( 'jsmith' );
$key  = md5( $data[1] ) . '_l';
check( 'a successful lookup returns the matched person', 1 === count( $data[0] ) && 'Jan Smith' === $data[0][0]['cn'][0] );
check( 'a successful lookup is cached for 600 seconds', isset( $transients[ $key ] ) && 600 === $transients[ $key ]['expiration'] );

$data = $api->getCampusDirData( 'jsmith' );
check( 'a cached successful lookup is served without a second LDAP search', 1 === count( $ldap_searches ) && 'Jan Smith' === $data[0][0]['cn'][0] );

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
check( 'sets a 5s client-side LDAP_OPT_TIMEOUT for bind and search',
	in_array( array( 'option' => LDAP_OPT_TIMEOUT, 'value' => 5 ), $ldap_options, true )
);

echo "LDAP stall guard tests (WPM-233):\n";

reset_test_state();
$api = campus_directory_api_fixture();
$api->getCampusDirData( 'jsmith' );
check( 'checks the LDAP host answers TLS within 3s before connecting',
	array( array( 'host' => 'ldap-blue.ucsc.edu', 'timeout' => 3 ) ) === $ldap_handshake_calls && 1 === count( $ldap_connect_uris )
);

reset_test_state();
$ldap_handshake_ok = false;
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( 'jsmith' );
check( 'a silent LDAP host returns no people without connecting', array() === $data[0] && 0 === count( $ldap_connect_uris ) );
check( 'a silent LDAP host marks LDAP down for 5 minutes',
	isset( $transients['ucsc_cd_ldap_down'] ) && 300 === $transients['ucsc_cd_ldap_down']['expiration']
);

reset_test_state();
$transients['ucsc_cd_ldap_down'] = array( 'value' => 1, 'expiration' => 300 );
$api = campus_directory_api_fixture();
$api->getCampusDirData( 'jsmith' );
check( 'while LDAP is marked down, skips the check and the connection', 0 === count( $ldap_handshake_calls ) && 0 === count( $ldap_connect_uris ) );

echo "shared LDAP connection tests:\n";

function size_limits_set() {
	global $ldap_options;
	$limits = array();
	foreach ( $ldap_options as $option ) {
		if ( LDAP_OPT_SIZELIMIT === $option['option'] ) $limits[] = $option['value'];
	}
	return $limits;
}

reset_test_state();
campus_directory_api_fixture()->getCampusDirData( 'jsmith' );
$unscoped               = campus_directory_api_fixture();
$unscoped->deptOrDivSet = false;
$unscoped->getCampusDirData( 'jdoe' );
check( 'blocks on one page share one TLS check, connection, and bind',
	1 === count( $ldap_handshake_calls ) && 1 === count( $ldap_connect_uris ) && 1 === count( $ldap_bind_args ) && 2 === count( $ldap_searches )
);
// Most mock LDAP_OPT_* constants are 0, so this also sees the connection's
// options; the last two are the two searches' size limits.
check( 'each search sets its own size limit on the shared connection', array( 1000, 50 ) === array_slice( size_limits_set(), -2 ) );
check( 'frees each search\'s results, since the connection stays open', 2 === $ldap_freed_results );

reset_test_state();
campus_directory_api_fixture()->getCampusDirData( 'jsmith' );
$other_account          = campus_directory_api_fixture();
$other_account->ldap_cn = 'other-site-app';
$other_account->getCampusDirData( 'jdoe' );
$other_password                = campus_directory_api_fixture();
$other_password->ldap_password = 'other-site-key';
$other_password->getCampusDirData( 'jroe' );
check( 'a different bind account on the same host gets its own connection, never a shared one', 3 === count( $ldap_bind_args ) && 3 === count( $ldap_connect_uris ) );

reset_test_state();
$ldap_bind_result = false;
@campus_directory_api_fixture()->getCampusDirData( 'jsmith' );
$ldap_bind_result = true;
@campus_directory_api_fixture()->getCampusDirData( 'jdoe' );
check( 'a failed bind is not shared, and marks LDAP down so the next block skips it',
	1 === count( $ldap_bind_args ) && 0 === count( $ldap_searches ) && isset( $transients['ucsc_cd_ldap_down'] )
);

reset_test_state();
$ldap_errno_result = -1;
campus_directory_api_fixture()->getCampusDirData( 'jsmith' );
$ldap_errno_result = 0;
campus_directory_api_fixture()->getCampusDirData( 'jdoe' );
check( 'a connection the server dropped is not reused: the next block reconnects', 2 === count( $ldap_connect_uris ) && 2 === count( $ldap_handshake_calls ) );

echo "CampusDirectoryAPI profile-route hardening tests (WPM-152):\n";

// The profile route takes its cruzid straight from the URL
// (directoryprofilecruzid), so these assert on the filter getCampusDirData
// returns in $data[1] -- the string that would reach ldap_search.

reset_test_state();
$api   = campus_directory_api_fixture();
$data  = $api->getCampusDirData( '*', true );
check( 'profile-route wildcard cruzid is escaped', false !== strpos( $data[1], '(uid=\2a)' ) && false === strpos( $data[1], '(uid=*)' ) );

reset_test_state();
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( 'js(mi)th', true );
check( 'profile-route parenthesis cruzid is escaped', false !== strpos( $data[1], 'js\28mi\29th' ) && false === strpos( $data[1], 'js(mi)th' ) );

reset_test_state();
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( 'js\\mith', true );
check( 'profile-route backslash cruzid is escaped', false !== strpos( $data[1], 'js\5cmith' ) );

reset_test_state();
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( "jsmith\0admin", true );
check( 'profile-route NUL byte cruzid is escaped', false !== strpos( $data[1], 'jsmith\00admin' ) && false === strpos( $data[1], "\0" ) );

// The payload that motivated the ticket: unescaped, "*)(uid=*" would close the
// uid clause and OR in a whole-directory match.
reset_test_state();
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( '*)(uid=*', true );
check( 'profile-route injection payload cannot add an LDAP clause', 1 === substr_count( $data[1], '(' ) && 1 === substr_count( $data[1], ')' ) );
check( 'profile-route injection payload is fully neutralized', '(uid=\2a\29\28uid=\2a)' === $data[1] );

// Scope item: prove the profile route reuses the list-view escaping helper
// rather than duplicating (and drifting from) it.
reset_test_state();
$api          = campus_directory_api_fixture();
$profile      = $api->getCampusDirData( '*)(uid=*', true );
$list         = $api->getCampusDirData( '*)(uid=*' );
$helperOutput = $api->buildUidFilter( '*)(uid=*' );
check( 'profile and list routes build the same filter for the same input', $profile[1] === $list[1] );
check( 'profile route filter comes from buildUidFilter', $profile[1] === $helperOutput );

reset_test_state();
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( '', true );
check( 'empty profile-route cruzid issues no LDAP search', 0 === count( $ldap_searches ) && array() === $data[0] );

reset_test_state();
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( '   ', true );
check( 'whitespace-only profile-route cruzid issues no LDAP search', 0 === count( $ldap_searches ) && array() === $data[0] );

echo "theHTML attribute decode tests (WPM-113):\n";

// Raw block attributes as they arrive at CampusDirectory::theHTML() -- the
// str* keys are the JSON-encoded strings stored on the block, decoded into
// obj* keys inside theHTML() before CampusDirectoryAPI is constructed.
function campus_directory_theHTML_attributes_fixture( $overrides = array() ) {
	$defaults = array(
		'cruzidList'                   => '',
		'pageLayout'                   => 'list',
		'linkToProfile'                => false,
		'linkOutToCampusDirectory'     => false,
		'automatedFeeds'               => true,
		'manualAdd'                    => false,
		'excludeCruzids'               => '',
		'addCruzids'                   => '',
		'department'                   => '---',
		'division'                     => '---',
		'deptOrDiv'                    => 'dept',
		'displayDeptartmentAffiliates' => false,
		'strFacultyTypes'              => json_encode( array(
			'All'      => false,
			'Senate'   => false,
			'Lecturer' => false,
			'Emeritus' => false,
		) ),
		'strStaffTypes'                => json_encode( array(
			'Regular Staff'        => false,
			'Researcher'           => false,
			'Postdoctoral Scholar' => false,
		) ),
		'strGradTypes'                 => json_encode( array( 'Grad Students' => false ) ),
		'strInformationTypes'          => json_encode( array( 'Title' => true ) ),
		'strInformationTypesTable'     => json_encode( array() ),
	);

	return array_replace( $defaults, $overrides );
}

// Runs theHTML() and reports whether it fataled/threw and whether it raised
// any PHP warnings/notices along the way (both are regressions per WPM-113).
function run_theHTML_capturing_issues( $campus_directory, $attributes ) {
	$threw    = false;
	$warnings = array();

	set_error_handler( function ( $errno, $errstr ) use ( &$warnings ) {
		$warnings[] = $errstr;
		return true; // swallow so PHP's own handler doesn't echo it
	} );

	try {
		$output = $campus_directory->theHTML( $attributes );
	} catch ( \Throwable $e ) {
		$threw  = true;
		$output = '';
	} finally {
		restore_error_handler();
	}

	return array(
		'threw'    => $threw,
		'warnings' => $warnings,
		'output'   => $output,
	);
}

reset_test_state();
$result = run_theHTML_capturing_issues( $campus_directory, campus_directory_theHTML_attributes_fixture() );
check( 'a fully populated automated-feed block renders with no warnings (control)', ! $result['threw'] && array() === $result['warnings'] );

reset_test_state();
$attributes = campus_directory_theHTML_attributes_fixture();
unset( $attributes['strFacultyTypes'], $attributes['strStaffTypes'], $attributes['strGradTypes'], $attributes['strInformationTypes'], $attributes['strInformationTypesTable'] );
$result = run_theHTML_capturing_issues( $campus_directory, $attributes );
check( 'a block with all str* attributes absent renders instead of fataling', ! $result['threw'] );
check( 'a block with all str* attributes absent emits no warnings', array() === $result['warnings'] );
check( 'a block with all str* attributes absent issues no LDAP search', 0 === count( $ldap_searches ) );

reset_test_state();
$attributes = campus_directory_theHTML_attributes_fixture( array(
	'strFacultyTypes'          => '',
	'strStaffTypes'            => null,
	'strGradTypes'             => 'not valid json',
	'strInformationTypes'      => '',
	'strInformationTypesTable' => '',
) );
$result = run_theHTML_capturing_issues( $campus_directory, $attributes );
check( 'a block with empty/null/malformed str* attributes renders instead of fataling', ! $result['threw'] );
check( 'a block with empty/null/malformed str* attributes emits no warnings', array() === $result['warnings'] );

reset_test_state();
$attributes = campus_directory_theHTML_attributes_fixture( array(
	'department'    => 'MATH',
	'deptOrDiv'     => 'dept',
	'strStaffTypes' => json_encode( array(
		'Regular Staff'        => true,
		'Researcher'           => false,
		'Postdoctoral Scholar' => false,
	) ),
) );
$result = run_theHTML_capturing_issues( $campus_directory, $attributes );
check( 'a block with a staff type selected still renders without fataling', ! $result['threw'] );
check( 'a selected staff type still reaches the LDAP filter (guard does not swallow real config)', isset( $ldap_searches[0] ) && false !== strpos( $ldap_searches[0]['filter'], 'ucscpersonpubaffiliation=Staff' ) );

reset_test_state();
$attributes = campus_directory_theHTML_attributes_fixture( array(
	'pageLayout' => 'table',
	'department' => 'MATH',
) );
unset( $attributes['strInformationTypesTable'] );
$result = run_theHTML_capturing_issues( $campus_directory, $attributes );
check( 'a table-layout block with strInformationTypesTable absent renders with no warnings', ! $result['threw'] && array() === $result['warnings'] );

echo "addVacantPosition/addVacantPositions tests (WPM-172):\n";

reset_test_state();
$api      = campus_directory_api_fixture();
$position = $api->addVacantPosition( '%Jane Doe%Banana Slug Wrangler%' );
check( 'addVacantPosition parses %Name%Title% into a cn/title-shaped entry', array( 'cn' => array( 'Jane Doe' ), 'title' => array( 'Banana Slug Wrangler' ) ) === $position );

reset_test_state();
$api     = campus_directory_api_fixture( array( 'automatedFeeds' => true, 'addCruzids' => '%Jane Doe%Wrangler%%John Roe%Deputy%' ) );
$people  = array( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) );
$result  = $api->addVacantPositions( $people, true, array() );
check( 'automated-feed addVacantPositions appends every %Name%Title% vacancy to the LDAP results', 3 === count( $result ) );
check( 'automated-feed addVacantPositions preserves the LDAP results first', 'Jan Smith' === $result[0]['cn'][0] );
check( 'automated-feed addVacantPositions parses the first vacancy', array( 'cn' => array( 'Jane Doe' ), 'title' => array( 'Wrangler' ) ) === $result[1] );
check( 'automated-feed addVacantPositions parses the second vacancy', array( 'cn' => array( 'John Roe' ), 'title' => array( 'Deputy' ) ) === $result[2] );

reset_test_state();
$api    = campus_directory_api_fixture( array( 'automatedFeeds' => true, 'addCruzids' => '' ) );
$people = array( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) );
$result = $api->addVacantPositions( $people, true, array() );
check( 'automated-feed addVacantPositions with no addCruzids leaves the LDAP results untouched', array( $people[0] ) === $result );

reset_test_state();
$api    = campus_directory_api_fixture();
$people = array( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) );
$result = $api->addVacantPositions( $people, false, array( 'jsmith', '%Jane Doe%Wrangler%' ) );
check( 'manual-list addVacantPositions preserves author order and resolves a real cruzid from the LDAP map', 'Jan Smith' === $result[0]['cn'][0] );
check( 'manual-list addVacantPositions parses a %Name%Title% entry without a directory lookup', array( 'cn' => array( 'Jane Doe' ), 'title' => array( 'Wrangler' ) ) === $result[1] );

reset_test_state();
$api    = campus_directory_api_fixture();
$people = array( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) );
$result = $api->addVacantPositions( $people, false, array( 'nomatch', '' ) );
check( 'manual-list addVacantPositions skips a cruzid absent from the LDAP result map', array() === $result );

reset_test_state();
$api    = campus_directory_api_fixture();
$people = array( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) );
$result = $api->addVacantPositions( $people, false, array( '' ) );
check( 'manual-list addVacantPositions skips empty cruzid entries', array() === $result );

echo "affiliation narrowing filter tests (WPM-171):\n";

// The four branches below are exercised by existing tests but never asserted:
// WPM-113's staff-type test runs the Regular Staff NOT-clause and then only
// checks that "ucscpersonpubaffiliation=Staff" appears, so statement coverage
// reads 100% while the narrowing behaviour itself is unpinned. These assert the
// normative behaviour in openspec/specs/campus-directory/audience-selection/spec.md.

// --- processStaffFilterString(): NOT-clause exclusion for partial selections ---

reset_test_state();
$api = campus_directory_api_fixture( array(
	'automatedFeeds' => true,
	'objStaffTypes'  => array(
		'Regular Staff'        => true,
		'Researcher'           => false,
		'Postdoctoral Scholar' => false,
	),
) );
$filter = $api->buildFilterString();
check( 'Regular Staff alone excludes the unselected specialized types with a NOT clause', '(&(ucscpersonpubaffiliation=Staff)(!(|(ucscpersonpubstafftype=Researcher)(ucscPersonIsPostDoc=TRUE))))' === $filter );
check( 'Regular Staff alone does not silently include postdoctoral scholars', false !== strpos( $filter, '(!' ) && false !== strpos( $filter, 'ucscPersonIsPostDoc=TRUE' ) );
check( 'two excluded staff types are OR-combined inside the NOT clause', false !== strpos( $filter, '(!(|' ) );

reset_test_state();
$api = campus_directory_api_fixture( array(
	'automatedFeeds' => true,
	'objStaffTypes'  => array(
		'Regular Staff'        => true,
		'Researcher'           => true,
		'Postdoctoral Scholar' => false,
	),
) );
$filter = $api->buildFilterString();
check( 'a single excluded staff type is negated without an OR wrapper', '(&(ucscpersonpubaffiliation=Staff)(!(ucscPersonIsPostDoc=TRUE)))' === $filter );
check( 'a selected specialized staff type is not negated', false === strpos( $filter, 'ucscpersonpubstafftype=Researcher' ) );

reset_test_state();
$api = campus_directory_api_fixture( array(
	'automatedFeeds' => true,
	'objStaffTypes'  => array(
		'Regular Staff'        => false,
		'Researcher'           => false,
		'Postdoctoral Scholar' => true,
	),
) );
$filter = $api->buildFilterString();
check( 'specialized staff types without Regular Staff select positively rather than by exclusion', '(&(ucscpersonpubaffiliation=Staff)(ucscPersonIsPostDoc=TRUE))' === $filter );
check( 'Postdoctoral Scholar maps to ucscPersonIsPostDoc, not a stafftype attribute', false === strpos( $filter, 'ucscpersonpubstafftype=Postdoctoral Scholar' ) );

reset_test_state();
$api = campus_directory_api_fixture( array(
	'automatedFeeds' => true,
	'objStaffTypes'  => array(
		'Regular Staff'        => true,
		'Researcher'           => true,
		'Postdoctoral Scholar' => true,
	),
) );
$filter = $api->buildFilterString();
check( 'selecting all three staff types collapses to every staff affiliation', '(ucscpersonpubaffiliation=Staff)' === $filter );

// --- processFacultyFilterString(): specific-multi-type branch ---

reset_test_state();
$api = campus_directory_api_fixture( array(
	'automatedFeeds'  => true,
	'objFacultyTypes' => array(
		'Lecturer' => true,
		'Emeriti'  => true,
	),
) );
$filter = $api->buildFilterString();
check( 'two specific faculty types are OR-combined under the Faculty affiliation', '(&(ucscpersonpubaffiliation=Faculty)(|(ucscpersonpubfacultytype=Lecturer)(ucscpersonpubfacultytype=Emeriti)))' === $filter );
check( 'a specific faculty selection lists exactly the selected types', 2 === substr_count( $filter, 'ucscpersonpubfacultytype=' ) );
check( 'unselected faculty types stay out of the filter', false === strpos( $filter, 'Visiting Scholar' ) && false === strpos( $filter, 'Retired' ) );

reset_test_state();
$api = campus_directory_api_fixture( array(
	'automatedFeeds'  => true,
	'objFacultyTypes' => array( 'Lecturer' => true ),
) );
$filter = $api->buildFilterString();
check( 'a single specific faculty type is not OR-wrapped', '(&(ucscpersonpubaffiliation=Faculty)(ucscpersonpubfacultytype=Lecturer))' === $filter );

reset_test_state();
$api = campus_directory_api_fixture( array(
	'automatedFeeds'  => true,
	'objFacultyTypes' => array(
		'All'      => true,
		'Lecturer' => true,
		'Emeriti'  => true,
	),
) );
$filter = $api->buildFilterString();
check( 'All takes precedence over individually selected faculty types', '(ucscpersonpubaffiliation=Faculty)' === $filter );

// --- no affiliation selected renders empty ---

reset_test_state();
$api = campus_directory_api_fixture( array(
	'automatedFeeds' => true,
	'department'     => 'MATH',
	'deptOrDiv'      => 'dept',
) );
$filter = $api->buildFilterString();
check( 'an automated feed with no affiliation selected builds no filter', '' === $filter );
check( 'an automated feed with no affiliation selected does not fall back to the department alone', false === strpos( $filter, 'ucscpersonpubdepartmentnumber' ) );
$api->getCampusDirData( '' );
check( 'an automated feed with no affiliation selected issues no LDAP search', 0 === count( $ldap_searches ) );

// --- union of groups ---

reset_test_state();
$api = campus_directory_api_fixture( array(
	'automatedFeeds'  => true,
	'department'      => 'MATH',
	'deptOrDiv'       => 'dept',
	'objGradTypes'    => array( 'Grad Students' => true ),
	'objFacultyTypes' => array( 'All' => true ),
	'objStaffTypes'   => array(
		'Regular Staff'        => true,
		'Researcher'           => true,
		'Postdoctoral Scholar' => true,
	),
) );
$filter = $api->buildFilterString();
check( 'faculty, staff and graduate students OR-combine under the department scope', '(&(ucscpersonpubdepartmentnumber=MATH)(|(ucscpersonpubaffiliation=Graduate)(ucscpersonpubaffiliation=Faculty)(ucscpersonpubaffiliation=Staff)))' === $filter );
check( 'each selected group contributes one affiliation clause to the union', 3 === substr_count( $filter, 'ucscpersonpubaffiliation=' ) );

reset_test_state();
$api = campus_directory_api_fixture( array(
	'automatedFeeds'  => true,
	'department'      => 'MATH',
	'deptOrDiv'       => 'dept',
	'objGradTypes'    => array( 'Grad Students' => true ),
	'objFacultyTypes' => array( 'All' => true ),
) );
$filter = $api->buildFilterString();
check( 'two selected groups OR-combine and exclude the unselected one', '(&(ucscpersonpubdepartmentnumber=MATH)(|(ucscpersonpubaffiliation=Graduate)(ucscpersonpubaffiliation=Faculty)))' === $filter );

reset_test_state();
$api = campus_directory_api_fixture( array(
	'automatedFeeds'  => true,
	'department'      => 'MATH',
	'deptOrDiv'       => 'dept',
	'objGradTypes'    => array( 'Grad Students' => true ),
) );
$filter = $api->buildFilterString();
check( 'a single selected group is ANDed to the department without an OR wrapper', '(&(ucscpersonpubdepartmentnumber=MATH)(ucscpersonpubaffiliation=Graduate))' === $filter );

echo "CampusDirectoryAPI LDAP bind-failure tests (WPM-175):\n";

// Seed a matching person so an empty result can only come from the bind gate.
reset_test_state();
$ldap_bind_result = false;
$ldap_entries     = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) ) );
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( 'jsmith' );
check( 'LDAP bind failure returns an empty result instead of fataling', array() === $data[0] );
check( 'LDAP bind failure issues no LDAP search', 0 === count( $ldap_searches ) );

echo "directory_profile_title tests (WPM-177):\n";

$host_title_parts = array( 'title' => 'People', 'site' => 'Example Dept' );

reset_test_state();
$query_vars['directoryprofilecruzid'] = 'jsmith';
$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) ) );
$parts = $campus_directory->directory_profile_title( $host_title_parts );
check( 'profile document title is the person\'s directory name', 'Jan Smith' === $parts['title'] );
check( 'profile document title leaves the other title parts alone', 'Example Dept' === $parts['site'] );

reset_test_state();
$query_vars['directoryprofilecruzid'] = 'nobody';
$parts = $campus_directory->directory_profile_title( $host_title_parts );
check( 'unresolved cruzid falls back to the host page title unchanged', $host_title_parts === $parts );

reset_test_state();
$parts = $campus_directory->directory_profile_title( $host_title_parts );
check( 'non-profile pages keep their title without an LDAP search', $host_title_parts === $parts && 0 === count( $ldap_searches ) );

// WPM-183: the title lookup builds the API with no automatedFeeds setting.
reset_test_state();
$query_vars['directoryprofilecruzid'] = 'jsmith';
$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) ) );
$title_warnings = array();
set_error_handler( function ( $errno, $errstr ) use ( &$title_warnings ) {
	$title_warnings[] = $errstr;
	return true;
} );
$campus_directory->directory_profile_title( $host_title_parts );
restore_error_handler();
check( 'profile document title lookup logs no PHP warnings', array() === $title_warnings );

echo "CampusDirectoryAPI untested branches (WPM-188):\n";

reset_test_state();
$api = campus_directory_api_fixture();
$api->getCampusDirData( 'jsmith' );
check( 'connects over ldaps:// outside the Docker dev stack', array( 'ldaps://ldap-blue.ucsc.edu' ) === $ldap_connect_uris );

// WPM-234: the old Docker dev switch to anonymous ldap:// is gone, so local
// dev sees exactly what the production bind sees.
reset_test_state();
putenv( 'DOCKER_DEV=docker_dev' );
$api = campus_directory_api_fixture();
$api->getCampusDirData( 'jsmith' );
putenv( 'DOCKER_DEV' );
check( 'DOCKER_DEV=docker_dev still connects over ldaps:// with the app bind', array( 'ldaps://ldap-blue.ucsc.edu' ) === $ldap_connect_uris && 1 === count( $ldap_bind_args ) && 0 === strpos( (string) $ldap_bind_args[0][0], 'cn=' ) );

reset_test_state();
$api    = campus_directory_api_fixture( array(
	'automatedFeeds' => true,
	'objStaffTypes'  => array( 'Researcher' => true ),
	'department'     => 'MATH',
) );
$filter = $api->buildFilterString();
check( 'a Researcher-only staff feed filters on ucscpersonpubstafftype=Researcher', false !== strpos( $filter, '(ucscpersonpubstafftype=Researcher)' ) && false === strpos( $filter, 'ucscPersonIsPostDoc' ) );

reset_test_state();
$api    = campus_directory_api_fixture( array(
	'automatedFeeds'               => true,
	'objFacultyTypes'              => array( 'All' => true ),
	'department'                   => 'MATH',
	'displayDeptartmentAffiliates' => true,
) );
$filter = $api->buildFilterString();
check( 'department affiliates filter on ucscpersonpubaffiliateddepartment', false !== strpos( $filter, '(ucscpersonpubaffiliateddepartment=MATH)' ) && false === strpos( $filter, 'ucscpersonpubdepartmentnumber=' ) );

reset_test_state();
$api    = campus_directory_api_fixture( array(
	'automatedFeeds'  => true,
	'objFacultyTypes' => array( 'All' => true ),
	'department'      => 'MATH',
) );
$filter = $api->buildFilterString();
check( 'without affiliates the department filters on ucscpersonpubdepartmentnumber', false !== strpos( $filter, '(ucscpersonpubdepartmentnumber=MATH)' ) && false === strpos( $filter, 'ucscpersonpubaffiliateddepartment' ) );

reset_test_state();
$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ), 'jpegphoto' => array( "\xFF\xD8\x00binary" ) ) ) );
$api          = campus_directory_api_fixture();
$data         = $api->getCampusDirData( 'jsmith', true );
check( 'reads jpegphoto with the binary-safe ldap_get_values_len() and nothing else with it', array( 'jpegphoto' ) === $ldap_len_reads );
check( 'keeps jpegphoto bytes intact, including NUL', "\xFF\xD8\x00binary" === ( $data[0][0]['jpegphoto'][0] ?? null ) );
echo "CampusDirectoryAPI LDAP size-limit ceiling tests (WPM-178):\n";

reset_test_state();
$api = campus_directory_api_fixture();
$api->deptOrDivSet = false;
$api->getCampusDirData( 'jsmith' );
check( 'unscoped automated feed applies the 50-record LDAP ceiling', ldap_size_limit_was_set_to( 50 ) );

reset_test_state();
$api = campus_directory_api_fixture(
	array(
		'automatedFeeds'  => true,
		'department'      => 'MATH',
		'objFacultyTypes' => array(
			'All'      => true,
			'Senate'   => false,
			'Lecturer' => false,
			'Emeritus' => false,
		),
	)
);
$api->getCampusDirData( '' );
check( 'scoped automated feed (dept=MATH) applies the 1000-record LDAP ceiling', ldap_size_limit_was_set_to( 1000 ) );

echo "directory photo cache tests (CampusDirectoryPhotoCache):\n";

if ( ! defined( 'HOUR_IN_SECONDS' ) ) define( 'HOUR_IN_SECONDS', 3600 );
$photo_bytes = "\xFF\xD8\xFF\xE0jpeg\x00bytes";
$new_bytes   = "\xFF\xD8\xFF\xE0new\x00photo";
$K_UID       = CampusDirectoryPhotoCache::META_UID;
$K_MD5       = CampusDirectoryPhotoCache::META_HASH;
$K_CHECKED   = CampusDirectoryPhotoCache::META_LAST_CHECKED;
$long_ago    = time() - HOUR_IN_SECONDS;

function stored_ids() {
	global $media_store;
	return array_keys( $media_store['posts'] );
}
function photo_entry( $uid, $bytes = null ) {
	$attrs = array( 'uid' => array( $uid ) );
	if ( null !== $bytes ) $attrs['jpegphoto'] = array( $bytes );
	return ldap_entry_fixture( $attrs );
}
function no_photo_remembered( $uid ) {
	global $media_store;
	return isset( $media_store['site_transients'][ CampusDirectoryPhotoCache::noPhotoKey( $uid ) ] );
}

reset_test_state();
$cache = new CampusDirectoryPhotoCache();
$urls  = $cache->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe', $photo_bytes ) ) );
$id    = stored_ids()[0] ?? null;
check( 'stores a new photo as one attachment', 1 === count( stored_ids() ) && 1 === count( $media_store['uploads'] ) );
check( 'writes the exact LDAP bytes, NUL included', $photo_bytes === ( $media_store['uploads'][0]['bits'] ?? null ) );
check( 'links to the stored file', isset( $urls['jdoe'] ) && $urls['jdoe'] === $media_store['posts'][ $id ]['url'] );
check( 'file name carries part of the md5, so a changed photo gets a new URL', 'cd-photo-jdoe-' . substr( md5( $photo_bytes ), 0, 12 ) . '.jpg' === $media_store['uploads'][0]['name'] );
check( 'records uid, md5, and check time on the attachment',
	'jdoe' === $media_store['posts'][ $id ]['meta'][ $K_UID ]
	&& md5( $photo_bytes ) === $media_store['posts'][ $id ]['meta'][ $K_MD5 ]
	&& abs( time() - $media_store['posts'][ $id ]['meta'][ $K_CHECKED ] ) < 5
);
check( 'generates no intermediate sizes', array() === $media_store['posts'][ $id ]['metadata']['sizes'] );
check( 'stores photos as private attachments, so the public media API does not list them', 'private' === $media_store['posts'][ $id ]['args']['post_status'] );
check( 'the cache never queries LDAP itself', 0 === count( $ldap_searches ) );
check( 'writes to the photo folder, not the YYYY/MM one', '/uploads/site-1/campus-directory-photos' === ( $media_store['uploads'][0]['path'] ?? null ) );
check( 'the stored file and its URL are in the photo folder',
	'/uploads/site-1/campus-directory-photos/' . $media_store['uploads'][0]['name'] === $media_store['posts'][ $id ]['file']
	&& 'https://example.test/uploads/site-1/campus-directory-photos/' . $media_store['uploads'][0]['name'] === $urls['jdoe']
);
check( 'skips listing the upload folder', array() === ( $media_store['uploads'][0]['file_list'] ?? null ) );
check( 'removes both upload filters after the write', empty( $GLOBALS['upload_filters'] ) );
check( 'reports ldap as the source', 'ldap' === $cache->photoSources['jdoe'] );

reset_test_state();
$id = seed_stored_photo( 'jdoe', $photo_bytes, $long_ago );
$urls = ( new CampusDirectoryPhotoCache() )->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe', $photo_bytes ) ) );
check( 'an unchanged photo is not rewritten', 0 === count( $media_store['uploads'] ) && array( $id ) === stored_ids() );
check( 'an unchanged photo keeps its URL', $media_store['posts'][ $id ]['url'] === $urls['jdoe'] );
check( 'an unchanged photo gets a new check time', $media_store['posts'][ $id ]['meta'][ $K_CHECKED ] > $long_ago );

reset_test_state();
$id = seed_stored_photo( 'jdoe', $photo_bytes, time() - 60 );
( new CampusDirectoryPhotoCache() )->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe', $photo_bytes ) ) );
check( 'a just-confirmed unchanged photo does not rewrite its check time', time() - 60 === $media_store['posts'][ $id ]['meta'][ $K_CHECKED ] );

reset_test_state();
$old  = seed_stored_photo( 'jdoe', $photo_bytes, $long_ago );
$urls = ( new CampusDirectoryPhotoCache() )->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe', $new_bytes ) ) );
$ids  = stored_ids();
check( 'a changed photo is stored anew', 1 === count( $media_store['uploads'] ) && $new_bytes === $media_store['uploads'][0]['bits'] );
check( 'a changed photo replaces the old attachment', array( $old ) === $media_store['deleted'] && 1 === count( $ids ) && $ids[0] !== $old );
check( 'a changed photo gets a new URL', false !== strpos( $urls['jdoe'], substr( md5( $new_bytes ), 0, 12 ) ) );

echo "directory photo cache: an entry without jpegPhoto deletes (photo removed or hidden):\n";

reset_test_state();
$old   = seed_stored_photo( 'jdoe', $photo_bytes, time() - 60 );
$cache = new CampusDirectoryPhotoCache();
$urls  = $cache->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe' ) ) );
check( 'deletes the stored photo, however recently confirmed', array( $old ) === $media_store['deleted'] && array() === stored_ids() );
check( 'gives no URL', '' === $urls['jdoe'] );
check( 'remembers "no photo" for an hour',
	no_photo_remembered( 'jdoe' )
	&& HOUR_IN_SECONDS === $media_store['site_transients'][ CampusDirectoryPhotoCache::noPhotoKey( 'jdoe' ) ]['expiration']
);
check( 'reports none', 'none' === $cache->photoSources['jdoe'] );

reset_test_state();
$media_store['site_transients'][ CampusDirectoryPhotoCache::noPhotoKey( 'jdoe' ) ] = array( 'value' => 1, 'expiration' => 3600 );
( new CampusDirectoryPhotoCache() )->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe', $photo_bytes ) ) );
check( 'a newly added photo clears the remembered "no photo"', ! no_photo_remembered( 'jdoe' ) );

echo "directory photo cache without an entry (people list served from cache):\n";

reset_test_state();
$id    = seed_stored_photo( 'jdoe', $photo_bytes, $long_ago );
$cache = new CampusDirectoryPhotoCache();
$urls  = $cache->urls( array( 'jdoe' ) );
check( 'uses the stored photo, with no LDAP and no write', $media_store['posts'][ $id ]['url'] === $urls['jdoe'] && 0 === count( $ldap_searches ) && 0 === count( $media_store['uploads'] ) );
check( 'reports media', 'media' === $cache->photoSources['jdoe'] );

reset_test_state();
$media_store['site_transients'][ CampusDirectoryPhotoCache::noPhotoKey( 'jdoe' ) ] = array( 'value' => 1, 'expiration' => 3600 );
$urls = ( new CampusDirectoryPhotoCache() )->urls( array( 'jdoe' ) );
check( 'a remembered "no photo" shows the fallback without a REST request', '' === $urls['jdoe'] );

reset_test_state();
$cache = new CampusDirectoryPhotoCache();
$urls  = $cache->urls( array( 'jdoe' ) );
check( 'nothing stored and nothing remembered links to the REST route', 'https://example.test/wp-json/ucscgutenbergblocks/v1/directoryphoto/jdoe' === $urls['jdoe'] );
check( 'reports unknown', 'unknown' === $cache->photoSources['jdoe'] );

reset_test_state();
$urls = ( new CampusDirectoryPhotoCache() )->urls( array( 'JDoe', '', ' jdoe ' ), array( 'JDOE' => photo_entry( 'jdoe', $photo_bytes ) ) );
check( 'answers each uid once, lowercased, ignoring blanks', array( 'jdoe' ) === array_keys( $urls ) && 1 === count( $media_store['uploads'] ) );

echo "directory photo cache write limit:\n";

// The fake clock moves a second per read: 0 when urls() starts, then 1, 2,
// ... at each new photo's budget check. Checks at 1 to TIME_BUDGET - 1
// write; the one at TIME_BUDGET is out of time.
class SlowStorePhotoCache extends CampusDirectoryPhotoCache {
	public $ticks = 0;
	protected function clock() {
		return (float) $this->ticks++;
	}
}

reset_test_state();
$max     = (int) CampusDirectoryPhotoCache::TIME_BUDGET - 1;
$entries = array();
for ( $i = 0; $i <= $max; $i++ ) $entries[ 'p' . $i ] = photo_entry( 'p' . $i, $photo_bytes . $i );
$changed = seed_stored_photo( 'p' . ( $max + 1 ), $photo_bytes, $long_ago );
$entries[ 'p' . ( $max + 1 ) ] = photo_entry( 'p' . ( $max + 1 ), $new_bytes );
$cache = new SlowStorePhotoCache();
$urls  = $cache->urls( array_keys( $entries ), $entries );
check( 'stops storing photos once urls() has run for the time budget', $max === count( $media_store['uploads'] ) );
check( 'a new photo over the limit links to the REST route', 'https://example.test/wp-json/ucscgutenbergblocks/v1/directoryphoto/p' . $max === $urls[ 'p' . $max ] );
check( 'a changed photo over the limit keeps showing the old one', $media_store['posts'][ $changed ]['url'] === $urls[ 'p' . ( $max + 1 ) ] );
check( 'reports deferred', 'deferred' === $cache->photoSources[ 'p' . $max ] );
$next_block = new SlowStorePhotoCache();
$urls       = $next_block->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe', $photo_bytes ) ) );
check( 'the budget is shared by every block in the request: a later block gets none once it is spent', $max === count( $media_store['uploads'] ) && 'deferred' === $next_block->photoSources['jdoe'] );
CampusDirectoryPhotoCache::resetRequestBudget();
$next_request = new SlowStorePhotoCache();
$next_request->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe', $photo_bytes ) ) );
check( 'a new request starts with the full budget', $max + 1 === count( $media_store['uploads'] ) );

reset_test_state();
$media_store['upload_error'] = 'disk full';
$cache = new CampusDirectoryPhotoCache();
$urls  = @$cache->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe', $photo_bytes ) ) );
check( 'a failed write gives no URL instead of a broken one', '' === $urls['jdoe'] && 'store-failed' === $cache->photoSources['jdoe'] );
check( 'removes both upload filters after a failed write', empty( $GLOBALS['upload_filters'] ) );

reset_test_state();
$media_store['upload_throws'] = true;
$threw = false;
try {
	( new CampusDirectoryPhotoCache() )->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe', $photo_bytes ) ) );
} catch ( RuntimeException $e ) {
	$threw = true;
}
check( 'removes both upload filters when the write throws', $threw && empty( $GLOBALS['upload_filters'] ) );

reset_test_state();
$older = seed_stored_photo( 'jdoe', $photo_bytes, $long_ago );
$newer = seed_stored_photo( 'jdoe', $photo_bytes, $long_ago );
$urls  = ( new CampusDirectoryPhotoCache() )->urls( array( 'jdoe' ) );
check( 'a duplicate left by two simultaneous renders is deleted, newest kept', array( $older ) === $media_store['deleted'] && $media_store['posts'][ $newer ]['url'] === $urls['jdoe'] );

echo "directory photo cache store site tests:\n";

reset_test_state();
$media_store['multisite'] = true;
$media_store['main_site'] = 1;
$media_store['blog']      = 7;
( new CampusDirectoryPhotoCache() )->urls( array( 'jdoe', 'nobody' ), array( 'jdoe' => photo_entry( 'jdoe', $photo_bytes ) ) );
check( 'on a network, photos are stored on the main site', 1 === ( $media_store['uploads'][0]['blog'] ?? null ) && array( 1 ) === array_unique( $media_store['get_posts_blogs'] ) );
check( 'switches back to the viewing site afterwards', 7 === $media_store['blog'] && array() === $media_store['blog_stack'] );
check( 'REST URLs come from the viewing site, not the store site', array( 7 ) === array_unique( $media_store['rest_url_blogs'] ) );
$media_store['blog'] = 9;
$urls = ( new CampusDirectoryPhotoCache() )->urls( array( 'jdoe' ) );
check( 'a second site reuses the photo the first one stored', 1 === count( $media_store['uploads'] ) && false !== strpos( $urls['jdoe'], '/site-1/' ) );

reset_test_state();
$media_store['multisite'] = true;
$media_store['blog']      = 7;
$media_store['filters']['ucsc_cd_photo_store_site_id'] = function () { return 3; };
( new CampusDirectoryPhotoCache() )->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe', $photo_bytes ) ) );
check( 'the ucsc_cd_photo_store_site_id filter picks the store site', 3 === ( $media_store['uploads'][0]['blog'] ?? null ) );

reset_test_state();
( new CampusDirectoryPhotoCache() )->urls( array( 'jdoe' ), array( 'jdoe' => photo_entry( 'jdoe', $photo_bytes ) ) );
check( 'a single site stores photos in its own Media Library without switching', array() === $media_store['switches'] && 1 === count( $media_store['uploads'] ) );

echo "nightly prune tests (WP-Cron):\n";

reset_test_state();
$gone = seed_stored_photo( 'gone', $photo_bytes, time() - 15 * 86400 );
( new CampusDirectoryPhotoCache() )->urls( array( 'gone' ) );
check( 'a page view never prunes', isset( $media_store['posts'][ $gone ] ) && array() === $media_store['deleted'] );

reset_test_state();
$gone = seed_stored_photo( 'gone', $photo_bytes, time() - 15 * 86400 );
$kept = seed_stored_photo( 'kept', $photo_bytes, time() - 13 * 86400 );
$deleted = CampusDirectoryPhotoCache::pruneNightly();
check( 'deletes photos no entry has confirmed for 14 days', in_array( $gone, $media_store['deleted'], true ) && 1 === $deleted );
check( 'keeps photos confirmed within 14 days, so a page viewed less than daily keeps its photos', isset( $media_store['posts'][ $kept ] ) );
$gone2 = seed_stored_photo( 'gone2', $photo_bytes, time() - 15 * 86400 );
check( 'only the first site\'s job each night does the work', 0 === CampusDirectoryPhotoCache::pruneNightly() && isset( $media_store['posts'][ $gone2 ] ) );

reset_test_state();
$batch = CampusDirectoryPhotoCache::PRUNE_BATCH;
for ( $i = 0; $i < $batch * 2 + 5; $i++ ) seed_stored_photo( 'old' . $i, $photo_bytes, time() - 15 * 86400 );
check( 'deletes every stale photo in one run, past a single batch', $batch * 2 + 5 === CampusDirectoryPhotoCache::pruneNightly() && array() === stored_ids() );

reset_test_state();
for ( $i = 0; $i < $batch; $i++ ) $ids[ $i ] = seed_stored_photo( 'old' . $i, $photo_bytes, time() - 15 * 86400 );
$media_store['delete_fails'] = array( $ids[0] );
$deleted = CampusDirectoryPhotoCache::pruneNightly();
check( 'a failed delete stops the run instead of looping', $batch - 1 === $deleted );

reset_test_state();
$media_store['multisite'] = true;
$media_store['blog']      = 7;
$gone = seed_stored_photo( 'gone', $photo_bytes, time() - 15 * 86400, 1 );
CampusDirectoryPhotoCache::pruneNightly();
check( 'a job fired on any site prunes the main site\'s photos', in_array( $gone, $media_store['deleted'], true ) );
check( 'and switches back afterwards', 7 === $media_store['blog'] && array() === $media_store['blog_stack'] );

echo "people query carries photos for blocks that show them:\n";

reset_test_state();
$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'JSmith' ), 'cn' => array( 'Jan Smith' ), 'jpegphoto' => array( $photo_bytes ) ) ) );
$api  = campus_directory_api_fixture( array( 'cruzidList' => 'JSmith', 'objInformationTypes' => array( 'Photo' => true ) ) );
$data = $api->getCampusDirData( 'JSmith' );
check( 'a block showing photos asks for jpegphoto in its one people query', 1 === count( $ldap_searches ) && in_array( 'jpegphoto', $ldap_searches[0]['attributes'], true ) );
check( 'hands the photo over for the cache', $photo_bytes === ( $api->photoEntries['jsmith']['jpegphoto'][0] ?? null ) );
check( 'never returns or caches photo bytes with the people list',
	! isset( $data[0][0]['jpegphoto'] ) && ! isset( $transients[ md5( $data[1] ) . '_lp' ]['value'][0]['jpegphoto'] )
	&& isset( $transients[ md5( $data[1] ) . '_lp' ] )
);
$api2 = campus_directory_api_fixture( array( 'cruzidList' => 'JSmith', 'objInformationTypes' => array( 'Photo' => true ) ) );
$api2->getCampusDirData( 'JSmith' );
check( 'a cached people list hands over no photos', array() === $api2->photoEntries && 1 === count( $ldap_searches ) );

reset_test_state();
$transients = array();
$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ) ) ) );
campus_directory_api_fixture()->getCampusDirData( 'jsmith' );
$api = campus_directory_api_fixture( array( 'objInformationTypes' => array( 'Photo' => true ) ) );
$api->getCampusDirData( 'jsmith' );
check( 'a list cached without photos never stands in for a block showing them', 2 === count( $ldap_searches ) && in_array( 'jpegphoto', $ldap_searches[1]['attributes'], true ) );

reset_test_state();
$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'jpegphoto' => array( $photo_bytes ) ) ) );
$api = campus_directory_api_fixture( array( 'pageLayout' => 'table', 'objInformationTypesTable' => array( 'Photo' => true ) ) );
$api->getCampusDirData( 'jsmith' );
check( 'table layouts showing photos ask for jpegphoto too', in_array( 'jpegphoto', $ldap_searches[0]['attributes'] ?? array(), true ) );

reset_test_state();
$hidden = ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'jpegphoto' => array( $photo_bytes ) ) );
$hidden['ucscpersonpubdisplay'] = array( 'FALSE', 'count' => 1 );
$ldap_entries = array( $hidden );
$api = campus_directory_api_fixture( array( 'objInformationTypes' => array( 'Photo' => true ) ) );
$api->getCampusDirData( 'jsmith' );
check( 'never hands over the photo of a person who is not publicly listed', array() === $api->photoEntries );

reset_test_state();
$ldap_errno_result = 3;
$ldap_entries      = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'jpegphoto' => array( $photo_bytes ) ) ) );
$api = campus_directory_api_fixture( array( 'objInformationTypes' => array( 'Photo' => true ) ) );
$api->getCampusDirData( 'jsmith' );
check( 'a people query cut short hands over no photos', array() === $api->photoEntries );

echo "theHTML photo tests:\n";

$photo_block = campus_directory_theHTML_attributes_fixture( array( 'automatedFeeds' => false, 'cruzidList' => 'jdoe', 'strInformationTypes' => json_encode( array( 'Photo' => true ) ) ) );

reset_test_state();
$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'jdoe' ), 'cn' => array( 'Jane Doe' ), 'jpegphoto' => array( $photo_bytes ) ) ) );
$html = $campus_directory->theHTML( $photo_block );
$id   = stored_ids()[0] ?? null;
check( 'stores photos from the people query, with no second LDAP query', 1 === count( $ldap_searches ) && 1 === count( $media_store['uploads'] ) );
check( 'links each photo to its stored file', $id && false !== strpos( $html, 'src="' . $media_store['posts'][ $id ]['url'] . '"' ) );
check( 'stored photos carry the cd-photo class', false !== strpos( $html, 'class="cd-photo ' ) );
check( 'no photo goes through the REST route', false === strpos( $html, 'directoryphoto/' ) );
$ldap_searches = array();
$html = $campus_directory->theHTML( $photo_block );
check( 'a cached render uses the stored photo with no LDAP at all', 0 === count( $ldap_searches ) && false !== strpos( $html, 'src="' . $media_store['posts'][ $id ]['url'] . '"' ) );

reset_test_state();
$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'jdoe' ), 'cn' => array( 'Jane Doe' ) ) ) );
$old  = seed_stored_photo( 'jdoe', $photo_bytes, time() - 60 );
$html = $campus_directory->theHTML( $photo_block );
check( 'a photo hidden in the directory is deleted when the people list refreshes', array( $old ) === $media_store['deleted'] );
check( 'and the page shows the fallback image', false !== strpos( $html, 'src="//static.ucsc.edu/images/icon-slug.jpg" loading="lazy"' ) );

reset_test_state();
$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'jdoe' ), 'cn' => array( 'Jane Doe' ) ) ) );
$campus_directory->theHTML( campus_directory_theHTML_attributes_fixture( array( 'automatedFeeds' => false, 'cruzidList' => 'jdoe' ) ) );
check( 'a block not showing photos touches no photo storage', array() === $media_store['get_posts_blogs'] );
check( 'a block not showing photos does not ask LDAP for them', ! in_array( 'jpegphoto', $ldap_searches[0]['attributes'] ?? array(), true ) );
check( 'no photo path ever asks campusdirectory photo.php (WPM-230)', 0 === count( $remote_get_calls ) );

echo "LDAP failure is not a miss tests:\n";

// A failed lookup says nothing about the person, so nothing may remember it as
// "no photo" or "no such person" once LDAP is back.

reset_test_state();
$ldap_bind_result = false;
$ldap_entries     = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) ) );
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( 'jsmith' );
check( 'a failed LDAP bind is not cached as an empty result', ! isset( $transients[ md5( $data[1] ) . '_l' ] ) );
check( 'a failed LDAP bind skips LDAP for a minute',
	isset( $transients['ucsc_cd_ldap_down'] ) && 60 === $transients['ucsc_cd_ldap_down']['expiration']
);

reset_test_state();
$ldap_search_result = false;
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( 'jsmith' );
check( 'a failed LDAP search is not cached as an empty result', ! isset( $transients[ md5( $data[1] ) . '_l' ] ) );

echo "no LDAP entry is not a known miss tests (WPM-257):\n";

// Only an entry that came back without jpegPhoto is a real "no photo". No entry
// at all may be a person who is not listed, or an answer cut short.

reset_test_state();
$ldap_errno_result = 3;
$ldap_entries      = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) ) );
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( 'jsmith' );
check( 'a search cut short by the LDAP time limit is not cached', ! isset( $transients[ md5( $data[1] ) . '_l' ] ) );

reset_test_state();
$ldap_errno_result = 4;
$ldap_entries      = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) ) );
$api  = campus_directory_api_fixture();
$data = $api->getCampusDirData( 'jsmith' );
check( 'a search that hits the size limit is still cached', isset( $transients[ md5( $data[1] ) . '_l' ] ) );

echo "theHTML heading level tests (WPM-92):\n";

// The template tests set $headingLevel directly; these go through theHTML() so
// the block attribute must actually reach the template.
function render_manual_person_with( $campus_directory, $overrides ) {
	global $ldap_entries;
	reset_test_state();
	$ldap_entries = array( ldap_entry_fixture( array( 'uid' => array( 'jsmith' ), 'cn' => array( 'Jan Smith' ) ) ) );
	$attributes   = campus_directory_theHTML_attributes_fixture( array_replace( array(
		'automatedFeeds' => false,
		'manualAdd'      => true,
		'cruzidList'     => 'jsmith',
	), $overrides ) );
	return run_theHTML_capturing_issues( $campus_directory, $attributes )['output'];
}

$html = render_manual_person_with( $campus_directory, array() );
check( 'a block with no headingLevel renders names as H3', 1 === preg_match( '/<h3 class="item-name">.*?Jan Smith.*?<\/h3>/s', $html ) );

$html = render_manual_person_with( $campus_directory, array( 'headingLevel' => 2 ) );
check( 'a block with headingLevel 2 renders names as H2', 1 === preg_match( '/<h2 class="item-name">.*?Jan Smith.*?<\/h2>/s', $html ) );

$html = render_manual_person_with( $campus_directory, array( 'headingLevel' => 4 ) );
check( 'a block with headingLevel 4 renders names as H4', 1 === preg_match( '/<h4 class="item-name">.*?Jan Smith.*?<\/h4>/s', $html ) );

finish_tests();
