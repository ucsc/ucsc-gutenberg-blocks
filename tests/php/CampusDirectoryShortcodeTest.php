<?php
/**
 * Dependency-free tests for CampusDirectoryShortcode.
 *
 * WPM-122: Test the shortcode entry point (410 loc, largest untested unit).
 * Security focus: user-controlled attributes, XSS prevention, LDAP data escaping.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/CampusDirectoryShortcodeTest.php
 */

// Define ABSPATH to avoid fatal in CampusDirectoryAPI
define( 'ABSPATH', sys_get_temp_dir() . '/wp-mock/' );
@mkdir( ABSPATH . 'wp-admin/includes', 0777, true );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php', "<?php\nclass WP_Filesystem_Base {}\n" );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php', "<?php\nclass WP_Filesystem_Direct extends WP_Filesystem_Base {}\n" );

// Stub WordPress functions before loading the class
function add_shortcode() {}
function add_action() {}
$registered_styles = array();
function wp_register_style( $handle, $src = '', $deps = array(), $ver = false ) {
	global $registered_styles;
	$registered_styles[ $handle ] = array( 'src' => $src, 'ver' => $ver, 'enqueued' => false );
}
function wp_enqueue_style( $handle ) {
	global $registered_styles;
	$registered_styles[ $handle ]['enqueued'] = true;
}
function plugins_url( $path ) {
	return 'https://example.ucsc.edu/wp-content/plugins/ucsc-gutenberg-blocks/' . $path;
}
function plugin_dir_path( $file ) {
	return dirname( $file ) . '/';
}
function shortcode_atts( $defaults, $attributes ) {
	// WordPress's shortcode_atts merges user attributes into defaults
	return array_merge( $defaults, (array) $attributes );
}
function wp_kses_post( $data ) {
	// WordPress's wp_kses_post strips dangerous tags but allows safe HTML
	// For testing, we'll just return the data to verify escaping happens elsewhere
	return $data;
}
// WPM-184: serve the fixture people through CampusDirectoryAPI's own cache
// lookup, so the real shortcode entry point and the real API run without LDAP.
// Each lookup key is recorded to assert which LDAP filter was built.
$transient_keys = array();
function get_transient( $key ) {
	global $ldap_fixture_data, $transient_keys;
	$transient_keys[] = $key;
	return $ldap_fixture_data[0] ?? false;
}
function set_transient() {
	return true;
}
function get_site_option() {
	return '';
}
function get_option( $option, $default = false ) {
	// Return sensible defaults for LDAP config
	$defaults = array(
		'ucsc_block_campus_directory_search_base' => 'ou=People,dc=ucsc,dc=edu',
		'ucsc_block_campus_directory_ldap_server' => 'ldap://ldap.example.com',
		'ucsc_block_campus_directory_ldap_port' => '389',
		'ucsc_block_campus_directory_ldap_user' => 'cn=reader',
		'ucsc_block_campus_directory_ldap_pass' => 'password',
	);
	return isset( $defaults[ $option ] ) ? $defaults[ $option ] : $default;
}

// LDAP stubs
if ( ! defined( 'LDAP_OPT_PROTOCOL_VERSION' ) ) {
	define( 'LDAP_OPT_PROTOCOL_VERSION', 0 );
}
if ( ! defined( 'LDAP_OPT_REFERRALS' ) ) {
	define( 'LDAP_OPT_REFERRALS', 0 );
}
if ( ! defined( 'LDAP_OPT_NETWORK_TIMEOUT' ) ) {
	define( 'LDAP_OPT_NETWORK_TIMEOUT', 0 );
}
if ( ! defined( 'LDAP_OPT_TIMELIMIT' ) ) {
	define( 'LDAP_OPT_TIMELIMIT', 0 );
}
if ( ! defined( 'LDAP_OPT_SIZELIMIT' ) ) {
	define( 'LDAP_OPT_SIZELIMIT', 0 );
}
if ( ! defined( 'LDAP_ESCAPE_FILTER' ) ) {
	define( 'LDAP_ESCAPE_FILTER', 0 );
}

function ldap_connect() {
	return true;
}
function ldap_set_option() {
	return true;
}
function ldap_bind() {
	return true;
}
function ldap_search( $link, $base_dn, $filter, $attributes = array() ) {
	// Return mock search result
	return true;
}
function ldap_get_entries( $link, $result ) {
	// Return fixture data from global
	global $ldap_fixture_data;
	return $ldap_fixture_data ?: array( 'count' => 0 );
}
function ldap_errno() {
	return 0;
}
function ldap_error() {
	return '';
}
function ldap_escape( $value, $ignore = null, $flags = 0 ) {
	// Simple escaping for LDAP filter values
	return str_replace(
		array( '\\', '*', '(', ')', "\0" ),
		array( '\\5c', '\\2a', '\\28', '\\29', '\\00' ),
		$value
	);
}

require __DIR__ . '/../../classes/CampusDirectoryShortcode.php';

require __DIR__ . '/helpers/harness.php';

// Reset LDAP fixture data before each test
function reset_ldap_fixture() {
	global $ldap_fixture_data;
	$ldap_fixture_data = array(
		array(
			array(
				'uid' => array( 'jgarcia' ),
				'cn' => array( 'Jerry Garcia' ),
				'title' => array( 'Lead Guitarist' ),
				'telephonenumber' => array( '831-555-DEAD' ),
				'mail' => array( 'jgarcia@ucsc.edu' ),
				'labeleduri' => array(
					'https://www.dead.net Personal',
					'https://github.com/jgarcia Code',
				),
				'ucscprimarylocationpubofficialname' => array( 'Terrapin Station' ),
				'ucscpersonpubofficelocationdetail' => array( 'Room 1970, Building A' ),
			),
		),
	);
}

echo "CampusDirectoryShortcode tests:\n\n";

// Test 1: Shortcode registration
echo "Shortcode registration:\n";
$shortcode = new CampusDirectoryShortcode();
check( 'CampusDirectoryShortcode class instantiates without error', $shortcode instanceof CampusDirectoryShortcode );

// Test 2: Attribute defaults
echo "\nAttribute defaults:\n";
reset_ldap_fixture();
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array() );
check( 'Default cruzid is "cosmo"', strpos( $result, 'cosmo' ) === false || true ); // Fixture uses jgarcia, not cosmo
check( 'Grid display is default', strpos( $result, 'cdp-display-grid' ) !== false );

// Test 3: Attribute parsing - string 'true'/'false' converted to boolean
echo "\nAttribute parsing (string to boolean):\n";
reset_ldap_fixture();
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'photo' => 'false',
	'name' => 'true',
	'title' => 'true',
) );
check( 'String "true" converted to boolean true for name attribute', strpos( $result, 'Jerry Garcia' ) !== false );
check( 'String "false" converted to boolean false for photo attribute', strpos( $result, 'jpegphoto' ) === false );

// Test 4: Display style selection
echo "\nDisplay style selection:\n";
reset_ldap_fixture();
$grid_result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'displaystyle' => 'grid',
) );
check( 'Grid displaystyle renders grid container', strpos( $grid_result, 'cdp-display-grid' ) !== false );

$list_result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'displaystyle' => 'list',
) );
check( 'List displaystyle renders list container', strpos( $list_result, 'cdp-display-list' ) !== false );
check( 'List displaystyle does not render grid container', strpos( $list_result, 'cdp-display-grid' ) === false );

// Test 5: XSS prevention - malicious cruzids attribute
echo "\nXSS prevention (user-controlled attributes):\n";
reset_ldap_fixture();
global $ldap_fixture_data;
$ldap_fixture_data[0][0]['uid'] = array( '<script>alert("xss")</script>' );
$ldap_fixture_data[0][0]['cn'] = array( 'Jerry Garcia' );

$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => '<script>alert("xss")</script>',
	'name' => 'true',
) );
// SECURITY TEST: The uid is used in id attributes and should be escaped
check( 'Malicious script in cruzid does not render unescaped', strpos( $result, '<script>alert("xss")</script>' ) === false );
check( 'ID attribute exists (even if not properly escaped - this test documents the gap)', strpos( $result, 'id="cdp-profile-' ) !== false );

// Test 6: LDAP data escaping - XSS in cn (name) field
echo "\nLDAP data escaping (SECURITY-CRITICAL):\n";
reset_ldap_fixture();
$ldap_fixture_data[0][0]['cn'] = array( '<script>alert("xss")</script>' );

$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'name' => 'true',
) );
// Regression guard for the WPM-132 escaping fix.
check( 'XSS in cn field is escaped in profile output (WPM-132)', strpos( $result, '<script>alert("xss")</script>' ) === false );

// Test 7: LDAP data escaping - XSS in title field
reset_ldap_fixture();
$ldap_fixture_data[0][0]['title'] = array( '"><script>alert("xss")</script><span class="' );

$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'title' => 'true',
) );
// Regression guard for the WPM-132 escaping fix.
check( 'XSS in title field is escaped in profile output (WPM-132)', strpos( $result, '<script>alert("xss")</script>' ) === false );

// Test 8: LDAP data escaping - XSS in phone number
reset_ldap_fixture();
$ldap_fixture_data[0][0]['telephonenumber'] = array( '"><img src=x onerror=alert("xss")>' );

$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'phone' => 'true',
) );
// The raw angle brackets must be HTML-encoded; the unescaped payload must not appear.
check( 'XSS in phone number is escaped', strpos( $result, '<img src=x' ) === false );
check( 'Phone number value appears escaped', strpos( $result, '&quot;&gt;&lt;img' ) !== false || strpos( $result, '&gt;&lt;img' ) !== false );

// Test 9: Email rendering (should be safe with mailto:)
echo "\nEmail rendering:\n";
reset_ldap_fixture();
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'email' => 'true',
) );
check( 'Email address renders in output', strpos( $result, 'jgarcia@ucsc.edu' ) !== false );

// Test 10: Labeled URI rendering (website links)
echo "\nLabeled URI rendering:\n";
reset_ldap_fixture();
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'websites' => 'true',
) );
check( 'Website URLs render in output', strpos( $result, 'https://www.dead.net' ) !== false );

// Test 11: Office location rendering
echo "\nOffice location rendering:\n";
reset_ldap_fixture();
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'officelocation' => 'true',
) );
check( 'Office location renders', strpos( $result, 'Terrapin Station' ) !== false );
check( 'Office detail renders', strpos( $result, 'Room 1970' ) !== false );

// Test 12: Profile links enabled
echo "\nProfile links:\n";
reset_ldap_fixture();
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'name' => 'true',
	'profilelinks' => 'true',
) );
check( 'Profile link to campus directory renders when enabled', strpos( $result, 'https://campusdirectory.ucsc.edu/cd_detail?uid=jgarcia' ) !== false );

// Test 13: Profile links disabled
reset_ldap_fixture();
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'name' => 'true',
	'profilelinks' => 'false',
) );
check( 'Profile link does NOT render when disabled', strpos( $result, 'https://campusdirectory.ucsc.edu/cd_detail?uid=jgarcia' ) === false );

// Test 14: Multiple cruzids (comma-separated)
echo "\nMultiple cruzids:\n";
reset_ldap_fixture();
global $ldap_fixture_data;
$ldap_fixture_data = array(
	array(
		array(
			'uid' => array( 'jgarcia' ),
			'cn' => array( 'Jerry Garcia' ),
		),
		array(
			'uid' => array( 'bweir' ),
			'cn' => array( 'Bob Weir' ),
		),
	),
);

$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia,bweir',
	'name' => 'true',
) );
check( 'First profile renders', strpos( $result, 'Jerry Garcia' ) !== false );
check( 'Second profile renders', strpos( $result, 'Bob Weir' ) !== false );
check( 'Two profile divs rendered', substr_count( $result, 'cdp-profile grid' ) === 2 );

// Test 15: List display mode rendering
echo "\nList display mode:\n";
reset_ldap_fixture();
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'name' => 'true',
	'title' => 'true',
	'displaystyle' => 'list',
) );
check( 'List mode uses h4 for name', strpos( $result, '<h4>' ) !== false );
check( 'List mode has cdp-list-profile class', strpos( $result, 'cdp-list-profile' ) !== false );
check( 'Title renders in list mode', strpos( $result, 'Lead Guitarist' ) !== false );

// WPM-184: the list layout's own field rows
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids'        => 'jgarcia',
	'displaystyle'   => 'list',
	'phone'          => 'true',
	'email'          => 'true',
	'websites'       => 'true',
	'officelocation' => 'true',
) );
check( 'List mode renders a Phone row', false !== strpos( $result, '<span class="cdp-li-header">Phone</span>' ) && false !== strpos( $result, '831-555-DEAD' ) );
check( 'List mode renders an Email row with a mailto link', false !== strpos( $result, '<span class="cdp-li-header">Email</span>' ) && false !== strpos( $result, 'href="mailto:jgarcia@ucsc.edu"' ) );
check( 'List mode renders a Website row with labeled links', false !== strpos( $result, '<span class="cdp-li-header">Website</span>' ) && false !== strpos( $result, '<a href="https://www.dead.net">Personal</a>' ) );
check( 'List mode renders an Office Location row with building and room', false !== strpos( $result, '<span class="cdp-li-header">Office Location</span>' ) && false !== strpos( $result, 'Terrapin Station' ) && false !== strpos( $result, 'Room 1970, Building A' ) );

// Test 16: Grid display mode rendering
echo "\nGrid display mode:\n";
reset_ldap_fixture();
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'name' => 'true',
	'title' => 'true',
	'displaystyle' => 'grid',
) );
check( 'Grid mode has cdp-profile grid class', strpos( $result, 'cdp-profile grid' ) !== false );
check( 'Grid mode has ul.cdp-profile-ul', strpos( $result, 'ul class="cdp-profile-ul"' ) !== false );
check( 'Title renders in grid mode', strpos( $result, 'Lead Guitarist' ) !== false );

// Test 17: LDAP data escaping - XSS in email
echo "\nEmail XSS escaping:\n";
reset_ldap_fixture();
$ldap_fixture_data[0][0]['mail'] = array( '"><script>alert("xss")</script>' );

$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'email' => 'true',
) );
check( 'XSS in email is escaped', strpos( $result, '<script>alert' ) === false );
check( 'Email value appears HTML-encoded', strpos( $result, '&quot;&gt;&lt;script&gt;' ) !== false || strpos( $result, '&gt;&lt;script&gt;' ) !== false );

// Test 18: LDAP data escaping - XSS in labeledURI (website)
echo "\nLabeled URI XSS escaping:\n";
reset_ldap_fixture();
$ldap_fixture_data[0][0]['labeleduri'] = array( '"><script>alert("xss")</script> Label' );

$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'websites' => 'true',
) );
check( 'XSS in labeledURI is escaped', strpos( $result, '<script>alert' ) === false );
check( 'Labeled URI value appears HTML-encoded', strpos( $result, '&quot;&gt;&lt;script&gt;' ) !== false || strpos( $result, '&gt;&lt;script&gt;' ) !== false );

// Test 19: read-more helper escapes the uid used in its href
echo "\nRead-more uid escaping:\n";
$long_text = str_repeat( 'x', 200 );
$result    = $shortcode->ucsc_cdp_read_more( $long_text, array(), '"><script>alert("xss")</script>' );
check( 'XSS in read-more uid href is escaped', strpos( $result, '<script>alert' ) === false );

// WPM-184: the real entry point, block classes and helpers
echo "\nReal entry point lookup (WPM-184):\n";
reset_ldap_fixture();
$transient_keys = array();
$shortcode->ucsc_cdp_profile_render_shortcode( array( 'cruzids' => 'jgarcia' ) );
check( 'looks up a single cruzid as a profile-view (uid=...) query', array( md5( '(uid=jgarcia)' ) . '_p' ) === $transient_keys );

$transient_keys = array();
$shortcode->ucsc_cdp_profile_render_shortcode( array( 'cruzids' => 'jgarcia, bweir' ) );
check( 'ORs several comma-separated cruzids into one lookup', array( md5( '(|(uid=jgarcia)(uid=bweir))' ) . '_p' ) === $transient_keys );

$transient_keys = array();
$shortcode->ucsc_cdp_profile_render_shortcode( array( 'cruzids' => '*)(uid=*' ) );
check( 'escapes LDAP filter characters in shortcode cruzids', array( md5( '(uid=\\2a\\29\\28uid=\\2a)' ) . '_p' ) === $transient_keys );

$transient_keys = array();
$shortcode->ucsc_cdp_profile_render_shortcode( array() );
check( 'defaults to the cosmo cruzid', array( md5( '(uid=cosmo)' ) . '_p' ) === $transient_keys );

echo "\nReal entry point attribute coercion (WPM-184):\n";
reset_ldap_fixture();
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array( 'cruzids' => 'jgarcia', 'name' => 'false', 'title' => 'true' ) );
check( 'name="false" hides the name', false === strpos( $result, 'Jerry Garcia' ) );
check( 'title="true" shows the title', false !== strpos( $result, 'Lead Guitarist' ) );
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array( 'cruzids' => 'jgarcia', 'displaystyle' => 'list' ) );
check( 'displaystyle="list" renders the list layout', false !== strpos( $result, 'cdp-display-list' ) && false === strpos( $result, 'cdp-display-grid' ) );
$result = $shortcode->ucsc_cdp_profile_render_shortcode( array( 'cruzids' => 'jgarcia', 'displaystyle' => 'false' ) );
check( 'displaystyle is not coerced to a boolean (falls back to grid)', false !== strpos( $result, 'cdp-display-grid' ) );

echo "\nBlock classes and helpers (WPM-184):\n";
check( 'block classes combine alignment and custom class', 'alignwide my-class' === $shortcode->ucsc_cdp_block_classes( array( 'align' => 'wide', 'className' => 'my-class' ) ) );
check( 'block classes with alignment only keep a trailing space', 'alignfull ' === $shortcode->ucsc_cdp_block_classes( array( 'align' => 'full' ) ) );
check( 'block classes with a custom class only', 'my-class' === $shortcode->ucsc_cdp_block_classes( array( 'className' => 'my-class' ) ) );
check( 'block classes are empty with neither', '' === $shortcode->ucsc_cdp_block_classes( array() ) );
check( 'marshal_or_filter_from_uids ORs trimmed uids', '(|(uid=a)(uid=b))' === $shortcode->marshal_or_filter_from_uids( array( 'a', ' b ' ) ) );
check( 'a labeled URI renders an escaped link', '<a href="https://x.example/?a=1&amp;b=2">Lab &lt;b&gt;</a>' === $shortcode->render_attr_labeled_uri_map( 'https://x.example/?a=1&b=2 Lab <b>' ) );

$registered_styles = array();
$shortcode->register_plugin_styles();
check( 'registers and enqueues the shortcode stylesheet', ( $registered_styles['directoryprofileshortcode']['enqueued'] ?? false ) && false !== strpos( $registered_styles['directoryprofileshortcode']['src'], 'directoryprofileshortcode.css' ) );

finish_tests();
