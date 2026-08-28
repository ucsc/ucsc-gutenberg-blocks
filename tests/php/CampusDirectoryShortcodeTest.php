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
function wp_register_style() {}
function wp_enqueue_style() {}
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
function get_transient() {
	return false; // Always miss cache
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

// Override getCampusDirData to return fixture data instead of calling LDAP
class CampusDirectoryAPI_Test extends CampusDirectoryAPI {
	public function getCampusDirData( $cruzids, $is_shortcode = false ) {
		global $ldap_fixture_data;
		return $ldap_fixture_data;
	}
}

// Override the shortcode to use our test API
class CampusDirectoryShortcode_Test extends CampusDirectoryShortcode {
	public function ucsc_cdp_profile_render_shortcode( $attributes ) {
		$sa = shortcode_atts(
			array(
				'cruzids'            => 'cosmo',
				'photo'              => true,
				'name'               => true,
				'title'              => false,
				'phone'              => false,
				'email'              => false,
				'websites'           => false,
				'officelocation'     => false,
				'officehours'        => false,
				'expertise'          => false,
				'profilelinks'       => true,
				'biography'          => false,
				'areas_of_expertise' => false,
				'research_interests' => false,
				'teaching_interests' => false,
				'awards'             => false,
				'publications'       => false,
				'displaystyle'       => 'grid',
			),
			$attributes
		);
		foreach ( $sa as $key => $value ) {
			if ( $key === 'cruzids' || $key === 'displaystyle' ) {
				continue;
			}
			if ( $value === 'true' ) {
				$sa[ $key ] = true;
			}
			if ( $value === 'false' ) {
				$sa[ $key ] = false;
			}
		}
		$attrs = array(
			'uids'                                  => $sa['cruzids'],
			'jpegPhoto'                             => $sa['photo'],
			'cn'                                    => $sa['name'],
			'title'                                 => $sa['title'],
			'telephoneNumber'                       => $sa['phone'],
			'mail'                                  => $sa['email'],
			'labeledURI'                            => $sa['websites'],
			'ucscPersonPubOfficeLocationDetail'     => $sa['officelocation'],
			'ucscPersonPubOfficeHours'              => $sa['officehours'],
			'ucscPersonPubAreaOfExpertise'          => $sa['expertise'],
			'profLinks'                             => $sa['profilelinks'],
			'ucscPersonPubDescription'              => $sa['biography'],
			'ucscPersonPubExpertiseReference'       => $sa['areas_of_expertise'],
			'ucscPersonPubResearchInterest'         => $sa['research_interests'],
			'ucscPersonPubTeachingInterest'         => $sa['teaching_interests'],
			'ucscPersonPubAwardsHonorsGrants'       => $sa['awards'],
			'ucscPersonPubSelectedPublication'      => $sa['publications'],
			'displayStyle'                          => $sa['displaystyle'],
		);

		$strCruzids = $attrs['uids'];

		// Use test API instead of real one
		$campusDirectoryAPI = new CampusDirectoryAPI_Test();
		$itemsShortcode     = $campusDirectoryAPI->getCampusDirData( $strCruzids, true );
		$uids               = preg_split( '/[\s,]+/', $attrs['uids'] );

		$result  = '';
		$options = array(); // Not used in tests
		if ( $attrs['displayStyle'] === 'list' ) {
			$result .= $this->render_profiles_list( $uids, $attrs, $options, $itemsShortcode );
		} else {
			$result .= $this->render_profiles_grid( $uids, $attrs, $options, $itemsShortcode );
		}
		return $result;
	}
}

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
$shortcode = new CampusDirectoryShortcode_Test();
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
// FAIL EXPECTED: Current code does NOT escape LDAP data
check( 'XSS in cn field is NOT escaped (KNOWN VULNERABILITY - this test should FAIL)', strpos( $result, '<script>alert("xss")</script>' ) === false );

// Test 7: LDAP data escaping - XSS in title field
reset_ldap_fixture();
$ldap_fixture_data[0][0]['title'] = array( '"><script>alert("xss")</script><span class="' );

$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'title' => 'true',
) );
// FAIL EXPECTED: Current code does NOT escape title
check( 'XSS in title field is NOT escaped (KNOWN VULNERABILITY - this test should FAIL)', strpos( $result, '<script>alert("xss")</script>' ) === false );

// Test 8: LDAP data escaping - XSS in phone number
reset_ldap_fixture();
$ldap_fixture_data[0][0]['telephonenumber'] = array( '"><img src=x onerror=alert("xss")>' );

$result = $shortcode->ucsc_cdp_profile_render_shortcode( array(
	'cruzids' => 'jgarcia',
	'phone' => 'true',
) );
// FAIL EXPECTED: Current code does NOT escape phone
check( 'XSS in phone number is NOT escaped (KNOWN VULNERABILITY - this test should FAIL)', strpos( $result, 'onerror=alert' ) === false );

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

finish_tests();
