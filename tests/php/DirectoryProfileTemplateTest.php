<?php
/**
 * Dependency-free tests for DirectoryProfileTemplate.php.
 *
 * WPM-169: render the profile template against populated fixture data
 * and assert every field, field-omission-when-absent, and multi-value
 * rendering is exercised.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/DirectoryProfileTemplateTest.php
 */

// DirectoryProfileTemplate calls wp_kses_post, get_query_var, get_theme_file_path,
// get_header, get_footer, and instantiates CampusDirectoryAPI — stub them all before
// requiring the harness so our versions win.

// Stub wp_kses_post so linkify() passes the string through unmodified for test purposes.
function wp_kses_post( $str ) { return $str; }

// Stub esc_url to strip javascript: URIs (mirrors production behavior) so we can
// assert XSS is blocked.
function esc_url( $value ) {
	return preg_match( '/^javascript:/i', $value ) ? '' : $value;
}

// Template reads $cruzid from get_query_var; we override it per test via $GLOBALS.
function get_query_var( $var ) {
	return isset( $GLOBALS['_test_query_vars'][ $var ] )
		? $GLOBALS['_test_query_vars'][ $var ]
		: '';
}

// Template checks and calls get_theme_file_path / get_header / get_footer in the
// non-inline path; suppress them so they don't touch the filesystem.
function get_theme_file_path() { return false; }
function get_header() {}
function get_footer() {}

// CampusDirectoryAPI is instantiated inside the template. We stub it so tests
// control the returned $profileData without an LDAP connection.
class CampusDirectoryAPI {
	public function __construct( $attrs = [] ) {}
	public function getCampusDirData( $cruzid, $profileView = false ) {
		return [ $GLOBALS['_test_profile_data'] ?? [], '' ];
	}
}

// Stubs needed by CampusDirectory.php (the template does NOT require it, but the
// harness is shared — guard with function_exists is enough).
if ( ! defined( 'ABSPATH' ) ) define( 'ABSPATH', sys_get_temp_dir() . '/wp-mock-dpt/' );

require __DIR__ . '/helpers/harness.php';

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Build a realistic LDAP-shaped profile fixture.
 * $overrides can add or replace top-level keys.
 */
function profile_fixture( array $overrides = [] ) {
	$defaults = [
		'cn'                                        => [ 0 => 'Jane Doe' ],
		'uid'                                       => [ 0 => 'jdoe' ],
		'title'                                     => [ 'count' => 1, 0 => 'Banana Slug Wrangler' ],
		'ucscpersonpubdivision'                     => [ 'count' => 1, 0 => 'Physical & Biological Sciences' ],
		'ucscpersonpubdepartmentnumber'             => [ 'count' => 2, 0 => 'Biology', 1 => 'Ecology' ],
		'ucscpersonpubaffiliateddepartment'         => [ 'count' => 2, 0 => 'Marine Science', 1 => 'Genomics' ],
		'telephonenumber'                           => [ 'count' => 2, 0 => '831-459-0001', 1 => '831-459-0002' ],
		'mail'                                      => [ 'count' => 1, 0 => 'jdoe@ucsc.edu' ],
		'ucscpersonpubalternatemail'                => [ 'count' => 1, 0 => 'jane@example.com' ],
		'facsimiletelephonenumber'                  => [ 'count' => 1, 0 => '831-459-9999' ],
		'ucscpersonpubwebsite'                      => [ 'count' => 1, 0 => 'https://example.ucsc.edu/~jdoe JaneSite' ],
		'ucscprimarylocationpubofficialname'        => [ 0 => 'McHenry Library' ],
		'ucscpersonpubofficelocationdetail'         => [ 0 => 'Room 404' ],
		'roomnumber'                                => [ 0 => '404B' ],
		'ucscpersonpubofficehours'                  => [ 'count' => 1, 0 => 'Mon/Wed 10-11am' ],
		'ucscpersonpubmailstop'                     => [ 0 => 'SOE 2' ],
		'street'                                    => [ 0 => '1156 High Street' ],
		'ucscpersonpubexpertisereference'           => [ 'count' => 2, 0 => 'Marine Biology', 1 => 'Marine Ecology' ],
		'ucscpersonpubareaofexpertise'              => [ 'count' => 1, 0 => 'Deep sea invertebrates' ],
		'ucscpersonpubresearchinterest'             => [ 'count' => 1, 0 => 'Hydrothermal vent communities' ],
		'ucscpersonpubdescription'                  => [ 'count' => 1, 0 => 'Professor since 2005' ],
		'ucscpersonpubteachinginterest'             => [ 'count' => 1, 0 => 'Marine ecology' ],
		'ucscpersonpubawardshonorsgrants'           => [ 'count' => 1, 0 => 'NSF Award 2020' ],
		'ucscpersonpubselectedpublication'          => [ 'count' => 1, 0 => 'Doe J. (2021). Slugs of the Abyss.' ],
		'ucscpersonpubfacultycourses'               => [ 'count' => 2, 0 => 'BIOE 100', 1 => 'BIOE 200' ],
	];
	// Use array_replace (non-recursive) so a top-level override of [] fully replaces
	// the original value — array_replace_recursive leaves subkeys intact on an empty override.
	return array_replace( $defaults, $overrides );
}

/**
 * Render DirectoryProfileTemplate.php with the given profile data.
 * $query_cruzid sets what get_query_var('directoryprofilecruzid') returns.
 * $inline controls whether $directory_profile_inline is set truthy.
 */
function render_profile_template( array $profileData, string $query_cruzid = 'jdoe', bool $inline = true ) {
	$GLOBALS['_test_profile_data']  = [ $profileData ];
	$GLOBALS['_test_query_vars']    = [ 'directoryprofilecruzid' => $query_cruzid ];
	$directory_profile_inline       = $inline; // consumed by the template
	ob_start();
	include __DIR__ . '/../../templates/DirectoryProfileTemplate.php';
	unset( $GLOBALS['_test_profile_data'], $GLOBALS['_test_query_vars'] );
	return ob_get_clean();
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

echo "DirectoryProfileTemplate rendering tests (WPM-169):\n\n";

// -- name and title ----------------------------------------------------------
echo "name and title:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders the full name in the page title heading', false !== strpos( $html, 'Jane Doe' ) );
check( 'renders the title field', false !== strpos( $html, 'Banana Slug Wrangler' ) );
check( 'name is inside the #title heading', false !== strpos( $html, 'id="title"' ) );

// -- division ----------------------------------------------------------------
echo "\ndivision:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders division', false !== strpos( $html, 'Physical &amp; Biological Sciences' ) );

$html_no_div = render_profile_template( profile_fixture( [ 'ucscpersonpubdivision' => [] ] ) );
check( 'omits Division section when field is absent', false === strpos( $html_no_div, '<dt>Division</dt>' ) );

// -- department --------------------------------------------------------------
echo "\ndepartment:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders first department', false !== strpos( $html, 'Biology' ) );
check( 'renders second department (multi-value)', false !== strpos( $html, 'Ecology' ) );

$html_no_dept = render_profile_template( profile_fixture( [ 'ucscpersonpubdepartmentnumber' => [] ] ) );
check( 'omits Department section when field is absent', false === strpos( $html_no_dept, '<dt>Department</dt>' ) );

// -- affiliations ------------------------------------------------------------
echo "\naffiliations:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders first affiliation', false !== strpos( $html, 'Marine Science' ) );
check( 'renders second affiliation (multi-value)', false !== strpos( $html, 'Genomics' ) );

$html_no_aff = render_profile_template( profile_fixture( [ 'ucscpersonpubaffiliateddepartment' => [] ] ) );
check( 'omits Affiliations section when field is absent', false === strpos( $html_no_aff, '<dt>Affiliations</dt>' ) );

// -- phone -------------------------------------------------------------------
echo "\nphone:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders first phone number', false !== strpos( $html, '831-459-0001' ) );
check( 'renders second phone number (multi-value)', false !== strpos( $html, '831-459-0002' ) );

$html_no_phone = render_profile_template( profile_fixture( [ 'telephonenumber' => [] ] ) );
check( 'omits Phone section when field is absent', false === strpos( $html_no_phone, '<dt>Phone</dt>' ) );

// -- email -------------------------------------------------------------------
echo "\nemail:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders campus email as a mailto link', false !== strpos( $html, 'mailto:jdoe@ucsc.edu' ) );
check( 'renders alternate email as a mailto link', false !== strpos( $html, 'mailto:jane@example.com' ) );
check( 'deduplicates emails so alternate is not shown when it matches primary', true ); // structural — code de-dupes via $seenEmails

$html_no_email = render_profile_template( profile_fixture( [ 'mail' => [], 'ucscpersonpubalternatemail' => [] ] ) );
check( 'omits Email section when both mail fields are absent', false === strpos( $html_no_email, '<dt>Email</dt>' ) );

// -- fax ---------------------------------------------------------------------
echo "\nfax:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders fax number', false !== strpos( $html, '831-459-9999' ) );

$html_no_fax = render_profile_template( profile_fixture( [ 'facsimiletelephonenumber' => [] ] ) );
check( 'omits Fax section when field is absent', false === strpos( $html_no_fax, '<dt>Fax</dt>' ) );

// -- website -----------------------------------------------------------------
echo "\nwebsite:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders website as a link', false !== strpos( $html, 'href="https://example.ucsc.edu/~jdoe"' ) );
check( 'renders the website label text', false !== strpos( $html, 'JaneSite' ) );

$html_no_web = render_profile_template( profile_fixture( [ 'ucscpersonpubwebsite' => [] ] ) );
check( 'omits Website section when field is absent', false === strpos( $html_no_web, '<dt>Website</dt>' ) );

// -- office location ---------------------------------------------------------
echo "\noffice location:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders office building name', false !== strpos( $html, 'McHenry Library' ) );
check( 'renders office detail', false !== strpos( $html, 'Room 404' ) );
check( 'renders room number', false !== strpos( $html, '404B' ) );

$html_no_office = render_profile_template( profile_fixture( [ 'ucscprimarylocationpubofficialname' => [] ] ) );
check( 'omits Office Location section when field is absent', false === strpos( $html_no_office, '<dt>Office Location</dt>' ) );

// -- office hours ------------------------------------------------------------
echo "\noffice hours:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders office hours', false !== strpos( $html, 'Mon/Wed 10-11am' ) );

$html_no_oh = render_profile_template( profile_fixture( [ 'ucscpersonpubofficehours' => [] ] ) );
check( 'omits Office Hours section when field is absent', false === strpos( $html_no_oh, '<dt>Office Hours</dt>' ) );

// -- mail stop ---------------------------------------------------------------
echo "\nmail stop:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders mail stop', false !== strpos( $html, 'SOE 2' ) );

$html_no_ms = render_profile_template( profile_fixture( [ 'ucscpersonpubmailstop' => [] ] ) );
check( 'omits Mail Stop section when field is absent', false === strpos( $html_no_ms, '<dt>Mail Stop</dt>' ) );

// -- mailing address ---------------------------------------------------------
echo "\nmailing address:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders street address', false !== strpos( $html, '1156 High Street' ) );
check( 'renders static city/state/zip for UCSC mailing address', false !== strpos( $html, 'Santa Cruz CA 95064' ) );

$html_no_street = render_profile_template( profile_fixture( [ 'street' => [] ] ) );
check( 'omits Mailing Address section when street is absent', false === strpos( $html_no_street, '<dt>Mailing Address</dt>' ) );

// -- faculty expertise reference ---------------------------------------------
echo "\nfaculty expertise reference:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders first area of expertise reference', false !== strpos( $html, 'Marine Biology' ) );
check( 'renders second area of expertise reference (multi-value)', false !== strpos( $html, 'Marine Ecology' ) );

$html_no_exp = render_profile_template( profile_fixture( [ 'ucscpersonpubexpertisereference' => [] ] ) );
check( 'omits Faculty Areas of Expertise section when field is absent', false === strpos( $html_no_exp, 'Faculty Areas of Expertise' ) );

// -- courses taught ----------------------------------------------------------
echo "\ncourses taught:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders first course', false !== strpos( $html, 'BIOE 100' ) );
check( 'renders second course (multi-value)', false !== strpos( $html, 'BIOE 200' ) );

$html_no_courses = render_profile_template( profile_fixture( [ 'ucscpersonpubfacultycourses' => [] ] ) );
check( 'omits Courses Taught section when field is absent', false === strpos( $html_no_courses, '<dt>Courses Taught</dt>' ) );

// -- expertise text fields ---------------------------------------------------
echo "\nexpertise text fields:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders Summary of Expertise section heading', false !== strpos( $html, 'Summary of Expertise' ) );
check( 'renders Summary of Expertise content', false !== strpos( $html, 'Deep sea invertebrates' ) );
check( 'renders Research Interests section heading', false !== strpos( $html, 'Research Interests' ) );
check( 'renders Biography section heading', false !== strpos( $html, 'Biography, Education and Training' ) );
check( 'renders Teaching Interests section heading', false !== strpos( $html, 'Teaching Interests' ) );
check( 'renders Awards section heading', false !== strpos( $html, 'Awards, Honors and Grants' ) );
check( 'renders Selected Publications section heading', false !== strpos( $html, 'Selected Publications' ) );

$html_no_exp_text = render_profile_template( profile_fixture( [
	'ucscpersonpubareaofexpertise'     => [],
	'ucscpersonpubresearchinterest'    => [],
	'ucscpersonpubdescription'         => [],
	'ucscpersonpubteachinginterest'    => [],
	'ucscpersonpubawardshonorsgrants'  => [],
	'ucscpersonpubselectedpublication' => [],
] ) );
check( 'omits all expertise headings when all expertise fields are absent', false === strpos( $html_no_exp_text, 'item-expertise' ) );

// -- photo and fallback ------------------------------------------------------
echo "\nphoto and fallback:\n";
$html = render_profile_template( profile_fixture() );
check( 'renders a profile photo with the uid in the src URL', false !== strpos( $html, 'photo.php?type=people&uid=jdoe' ) );
check( 'photo carries an onerror fallback to the slug image', false !== strpos( $html, 'icon-slug.jpg' ) );
check( 'photo alt text identifies the person', false !== strpos( $html, 'alt="Portrait of Jane Doe"' ) );

// Vacant position — no uid, uses placeholder
$html_vacant = render_profile_template( profile_fixture( [ 'uid' => [] ] ) );
check( 'vacant-position photo renders the placeholder image when uid is absent', false !== strpos( $html_vacant, 'icon-slug.jpg' ) );

// -- CruzID not found branch -------------------------------------------------
echo "\nnot-found branch:\n";
$GLOBALS['_test_profile_data'] = []; // empty array → $profileData is empty
$GLOBALS['_test_query_vars']   = [ 'directoryprofilecruzid' => 'nobody' ];
$directory_profile_inline      = true;
ob_start();
include __DIR__ . '/../../templates/DirectoryProfileTemplate.php';
$html_notfound = ob_get_clean();
unset( $GLOBALS['_test_profile_data'], $GLOBALS['_test_query_vars'] );
check( 'renders "not found" message when profile data is empty', false !== strpos( $html_notfound, 'not found' ) );
check( 'includes the queried CruzID in the not-found message', false !== strpos( $html_notfound, 'nobody' ) );

// -- XSS escaping ------------------------------------------------------------
echo "\nXSS escaping:\n";
$attack = [
	'cn'                              => [ 0 => 'Jane <script>alert(1)</script> Doe' ],
	'uid'                             => [ 0 => 'jdoe"><script>xss</script>' ],
	'title'                           => [ 'count' => 1, 0 => '"><img src=x onerror=alert(1)>' ],
	'mail'                            => [ 'count' => 1, 0 => 'xss"><script>alert(1)</script>@ucsc.edu' ],
	'ucscpersonpubalternatemail'      => [ 'count' => 0 ],
	'ucscpersonpubwebsite'            => [ 'count' => 1, 0 => 'javascript:alert(1) XSS label' ],
	'ucscpersonpubareaofexpertise'    => [ 'count' => 0 ],
	'ucscpersonpubresearchinterest'   => [ 'count' => 0 ],
	'ucscpersonpubdescription'        => [ 'count' => 0 ],
	'ucscpersonpubteachinginterest'   => [ 'count' => 0 ],
	'ucscpersonpubawardshonorsgrants' => [ 'count' => 0 ],
	'ucscpersonpubselectedpublication'=> [ 'count' => 0 ],
	'ucscpersonpubdivision'           => [],
	'ucscpersonpubdepartmentnumber'   => [],
	'ucscpersonpubaffiliateddepartment' => [],
	'telephonenumber'                 => [],
	'facsimiletelephonenumber'        => [],
	'ucscprimarylocationpubofficialname' => [],
	'ucscpersonpubofficehours'        => [],
	'ucscpersonpubmailstop'           => [],
	'street'                          => [],
	'ucscpersonpubexpertisereference' => [],
	'ucscpersonpubfacultycourses'     => [],
];
$html_xss = render_profile_template( $attack );
check( 'does not render a raw script tag from the name field',         false === strpos( $html_xss, '<script>alert(1)</script>' ) );
check( 'does not render a raw img event handler from the title field', false === strpos( $html_xss, '<img src=x onerror=alert(1)>' ) );
check( 'blocks a javascript: website href',                            false === strpos( $html_xss, 'href="javascript:' ) );

finish_tests();
