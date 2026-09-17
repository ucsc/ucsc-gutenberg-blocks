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
		array( '\\', '*', '(', ')', "\0" ),
		array( '\5c', '\2a', '\28', '\29', '\00' ),
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

// WPM-117: Use the shared instrumented harness. The harness guards all its stubs
// with function_exists, so our custom test-specific stubs above win.
require __DIR__ . '/helpers/harness.php';

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
check( 'returns standalone DirectoryProfileTemplate on singular pages too (WPM-114)', false !== strpos( $template, 'DirectoryProfileTemplate.php' ) );

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

finish_tests();
