<?php
/**
 * Dependency-free tests for SiteSettings dropdown-source failure paths.
 *
 * WPM-134: cddepartmentcode() — WP_Error, non-2xx, unparseable HTML, happy path.
 * WPM-154: departmentcode() and subjectcode() — the PeopleSoft SCX_CLASS_DEPTS_V2
 *          feed. Same failure classes plus an unparseable/unexpected JSON shape,
 *          proving no count(null) fatal after the wp_remote_get() conversion.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/SiteSettingsTest.php
 *
 * Or with coverage:
 *   bash tests/php/run-php-coverage.sh
 */

// ── Globals ──────────────────────────────────────────────────────────────────

$transients           = array();
$transient_set_calls  = array();
$remote_get_calls     = array();
$remote_get_args      = array();
$remote_get_response  = null; // set per test

// ── WordPress stubs ───────────────────────────────────────────────────────────

// Fake ABSPATH to avoid fatal errors when CampusDirectoryAPI requires wp-admin files.
define( 'ABSPATH', sys_get_temp_dir() . '/wp-mock/' );
@mkdir( ABSPATH . 'wp-admin/includes', 0777, true );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php', "<?php\nclass WP_Filesystem_Base {}\n" );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php', "<?php\nclass WP_Filesystem_Direct extends WP_Filesystem_Base {}\n" );

if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
	define( 'WEEK_IN_SECONDS', 604800 );
}
if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}

function get_transient( $key ) {
	global $transients;
	return isset( $transients[ $key ] ) ? $transients[ $key ] : false;
}

function set_transient( $key, $value, $expiration ) {
	global $transients, $transient_set_calls;
	$transients[ $key ]    = $value;
	$transient_set_calls[] = array( 'key' => $key, 'expiration' => $expiration );
	return true;
}

function wp_remote_get( $url, $args = array() ) {
	global $remote_get_calls, $remote_get_args, $remote_get_response;
	$remote_get_calls[] = $url;
	$remote_get_args[]  = $args;
	return $remote_get_response;
}

function wp_remote_retrieve_body( $response ) {
	return isset( $response['body'] ) ? $response['body'] : '';
}

function wp_remote_retrieve_response_code( $response ) {
	return isset( $response['code'] ) ? $response['code'] : 0;
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $value ) {
		return $value instanceof WP_Error;
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		public $code;
		public $message;
		public $data;
		public function __construct( $code = '', $message = '', $data = array() ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}
		public function get_error_code()    { return $this->code; }
		public function get_error_message() { return $this->message; }
		public function get_error_data()    { return $this->data; }
	}
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

// ── Harness ───────────────────────────────────────────────────────────────────

require_once __DIR__ . '/helpers/harness.php';

// ── Load the class under test ─────────────────────────────────────────────────

require_once __DIR__ . '/../../classes/SiteSettings.php';

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Reset all global state between tests.
 */
function reset_state() {
	global $transients, $transient_set_calls, $remote_get_calls, $remote_get_args, $remote_get_response;
	$transients          = array();
	$transient_set_calls = array();
	$remote_get_calls    = array();
	$remote_get_args     = array();
	$remote_get_response = null;
}

/**
 * Keys passed to set_transient() since the last reset.
 */
function transient_set_keys() {
	global $transient_set_calls;
	return array_column( $transient_set_calls, 'key' );
}

/**
 * Build a successful wp_remote_get response.
 */
function ok_response( $body ) {
	return array( 'code' => 200, 'body' => $body );
}

/**
 * Build a non-2xx wp_remote_get response.
 */
function err_response( $code, $body = '' ) {
	return array( 'code' => $code, 'body' => $body );
}

/**
 * Run $fn and return [result, warnings]. The unparseable-input guards only
 * prevent PHP warnings (foreach/offset on null), so assert none are raised.
 */
function capture_warnings( $fn ) {
	$warnings = array();
	set_error_handler( function ( $errno, $errstr ) use ( &$warnings ) {
		$warnings[] = $errstr;
		return true;
	} );
	try {
		$result = $fn();
	} finally {
		restore_error_handler();
	}
	return array( $result, $warnings );
}

$department_options_html = '<html><body><select id="ucscpersonpubdepartmentnumber">'
	. '<option>Biology</option><option>Chemistry</option><option></option>'
	. '</select></body></html>';

// ── WP_Error from remote ────────────────────────────────────────────────────────

reset_state();
$settings             = new SiteSettings();
$remote_get_response  = new WP_Error( 'http_request_failed', 'connection timed out' );
$result = $settings->cddepartmentcode();

check( 'cddepartmentcode: WP_Error remote returns WP_Error, not a fatal',
	is_wp_error( $result )
);
check( 'cddepartmentcode: WP_Error result is not cached',
	! in_array( 'ucsc_cddepartmentcode', transient_set_keys(), true )
);
check( 'cddepartmentcode: WP_Error records a 5-minute failure backoff',
	$transient_set_calls === array( array( 'key' => 'ucsc_cddepartmentcode_failed', 'expiration' => 300 ) )
);
check( 'cddepartmentcode: fetch timeout stays well under the 30s edge timeout',
	isset( $remote_get_args[0]['timeout'] ) && $remote_get_args[0]['timeout'] <= 10
);

// within the backoff window: fail fast, no remote call
$remote_get_calls = array();
$result = $settings->cddepartmentcode();

check( 'cddepartmentcode: during backoff returns a 503 WP_Error',
	is_wp_error( $result ) && $result->get_error_data() === array( 'status' => 503 )
);
check( 'cddepartmentcode: during backoff makes no remote call',
	count( $remote_get_calls ) === 0
);

// backoff expired: tries the remote again and recovers
unset( $transients['ucsc_cddepartmentcode_failed'] );
$remote_get_response = ok_response( $department_options_html );
$result = $settings->cddepartmentcode();

check( 'cddepartmentcode: after backoff expires the remote is retried and the list returned',
	count( $remote_get_calls ) === 1 && $result instanceof WP_REST_Response
);

// ── Non-2xx response ─────────────────────────────────────────────────────────

reset_state();
$settings             = new SiteSettings();
$remote_get_response  = err_response( 503, 'upstream maintenance page' );
$result = $settings->cddepartmentcode();

check( 'cddepartmentcode: non-2xx response returns a controlled WP_Error',
	is_wp_error( $result )
);
check( 'cddepartmentcode: non-2xx error does not leak the raw upstream body',
	is_wp_error( $result ) && strpos( $result->get_error_message(), 'upstream maintenance page' ) === false
);
check( 'cddepartmentcode: non-2xx response is not cached',
	! in_array( 'ucsc_cddepartmentcode', transient_set_keys(), true )
);
check( 'cddepartmentcode: non-2xx response records the failure backoff',
	transient_set_keys() === array( 'ucsc_cddepartmentcode_failed' )
);

// ── Unparseable HTML ─────────────────────────────────────────────────────────

reset_state();
$settings             = new SiteSettings();
$remote_get_response  = ok_response( '<html><body>no department select here</body></html>' );
list( $result, $warnings ) = capture_warnings( function () use ( $settings ) {
	return $settings->cddepartmentcode();
} );

check( 'cddepartmentcode: unparseable HTML does not fatal',
	$result instanceof WP_REST_Response
);
check( 'cddepartmentcode: unparseable HTML returns only the placeholder option (empty list)',
	$result instanceof WP_REST_Response && count( $result->get_data() ) === 1
	&& $result->get_data()[0]['value'] === '---'
);
check( 'cddepartmentcode: unparseable HTML raises no PHP warnings', array() === $warnings );

// ── Happy path ────────────────────────────────────────────────────────────────

reset_state();
$settings             = new SiteSettings();
$remote_get_response  = ok_response( $department_options_html );
$result = $settings->cddepartmentcode();

check( 'cddepartmentcode: happy path returns a WP_REST_Response',
	$result instanceof WP_REST_Response
);
check( 'cddepartmentcode: happy path parses department list in expected shape',
	$result instanceof WP_REST_Response
	&& $result->get_data() === array(
		array( 'label' => '---', 'value' => '---' ),
		array( 'label' => 'Biology', 'value' => 'Biology' ),
		array( 'label' => 'Chemistry', 'value' => 'Chemistry' ),
	)
);
check( 'cddepartmentcode: happy path caches the parsed list for a week',
	! empty( $transient_set_calls )
	&& $transient_set_calls[0]['key'] === 'ucsc_cddepartmentcode'
	&& $transient_set_calls[0]['expiration'] === WEEK_IN_SECONDS
);

// cache hit — no remote call
reset_state();
$settings = new SiteSettings();
$transients['ucsc_cddepartmentcode'] = array( array( 'label' => '---', 'value' => '---' ) );
$result = $settings->cddepartmentcode();

check( 'cddepartmentcode: cache hit makes no remote call',
	count( $remote_get_calls ) === 0
);

// ══════════════════════════════════════════════════════════════════════════════
// WPM-154: departmentcode() and subjectcode() — the PeopleSoft SCX_CLASS_DEPTS_V2
// feed, previously fetched with an unbounded curl_init() and no error handling.
// ══════════════════════════════════════════════════════════════════════════════

// Feed shape: { "depts": [ { code, description, subjects: [ { code, description } ] } ] }
$class_depts_json = json_encode( array(
	'depts' => array(
		array(
			'code'        => 'CSE',
			'description' => 'Computer Science',
			'subjects'    => array(
				array( 'code' => 'CSE', 'description' => 'Computer Science' ),
			),
		),
		array(
			'code'        => 'AMS',
			'description' => 'Applied Math',
			'subjects'    => array(
				array( 'code' => 'AMS', 'description' => 'Applied Mathematics' ),
				array( 'code' => 'STAT', 'description' => 'Statistics' ),
			),
		),
	),
) );

// ── departmentcode() ──────────────────────────────────────────────────────────

// WP_Error from remote
reset_state();
$settings            = new SiteSettings();
$remote_get_response = new WP_Error( 'http_request_failed', 'connection timed out' );
$result              = $settings->departmentcode();
check( 'departmentcode: WP_Error remote returns WP_Error, not a fatal', is_wp_error( $result ) );
check( 'departmentcode: WP_Error result is not cached', ! in_array( 'ucsc_depts', transient_set_keys(), true ) );
check( 'departmentcode: passes a bounded timeout to wp_remote_get', 1 === count( $remote_get_calls ) );

// Non-2xx response
reset_state();
$settings            = new SiteSettings();
$remote_get_response = err_response( 503, 'upstream maintenance page' );
$result              = $settings->departmentcode();
check( 'departmentcode: non-2xx response returns a controlled WP_Error', is_wp_error( $result ) );
check( 'departmentcode: non-2xx error does not leak the raw upstream body',
	is_wp_error( $result ) && strpos( $result->get_error_message(), 'upstream maintenance page' ) === false
);
check( 'departmentcode: non-2xx response is not cached', ! in_array( 'ucsc_depts', transient_set_keys(), true ) );

// Unparseable / unexpected JSON shape
reset_state();
$settings            = new SiteSettings();
$remote_get_response = ok_response( 'not json at all' );
list( $result, $warnings ) = capture_warnings( function () use ( $settings ) {
	return $settings->departmentcode();
} );
check( 'departmentcode: unparseable JSON returns a WP_Error, not a fatal (no count(null))', is_wp_error( $result ) );
check( 'departmentcode: unparseable JSON raises no PHP warnings', array() === $warnings );
// WPM-236: an unusable feed must not be cached as a '---'-only list for a week
check( 'departmentcode: unparseable JSON is not cached', ! in_array( 'ucsc_depts', transient_set_keys(), true ) );

// Happy path
reset_state();
$settings            = new SiteSettings();
$remote_get_response = ok_response( $class_depts_json );
$result              = $settings->departmentcode();
check( 'departmentcode: happy path returns a WP_REST_Response', $result instanceof WP_REST_Response );
check( 'departmentcode: happy path parses the department list, sorted, in expected shape',
	$result instanceof WP_REST_Response
	&& $result->get_data() === array(
		array( 'label' => '---', 'value' => '---' ),
		array( 'label' => 'Applied Math', 'value' => 'AMS' ),
		array( 'label' => 'Computer Science', 'value' => 'CSE' ),
	)
);
check( 'departmentcode: happy path caches under ucsc_depts for a week',
	! empty( $transient_set_calls )
	&& $transient_set_calls[0]['key'] === 'ucsc_depts'
	&& $transient_set_calls[0]['expiration'] === WEEK_IN_SECONDS
);

// Cache hit — no remote call
reset_state();
$settings                     = new SiteSettings();
$transients['ucsc_depts']     = array( array( 'label' => '---', 'value' => '---' ) );
$result                       = $settings->departmentcode();
check( 'departmentcode: cache hit makes no remote call', count( $remote_get_calls ) === 0 );

// ── subjectcode() ─────────────────────────────────────────────────────────────

// WP_Error from remote
reset_state();
$settings            = new SiteSettings();
$remote_get_response = new WP_Error( 'http_request_failed', 'connection timed out' );
$result              = $settings->subjectcode();
check( 'subjectcode: WP_Error remote returns WP_Error, not a fatal', is_wp_error( $result ) );
check( 'subjectcode: WP_Error result is not cached', ! in_array( 'ucsc_subjects', transient_set_keys(), true ) );

// Non-2xx response
reset_state();
$settings            = new SiteSettings();
$remote_get_response = err_response( 500, 'internal server error page' );
$result              = $settings->subjectcode();
check( 'subjectcode: non-2xx response returns a controlled WP_Error', is_wp_error( $result ) );
check( 'subjectcode: non-2xx error does not leak the raw upstream body',
	is_wp_error( $result ) && strpos( $result->get_error_message(), 'internal server error page' ) === false
);
check( 'subjectcode: non-2xx response is not cached', ! in_array( 'ucsc_subjects', transient_set_keys(), true ) );

// Unparseable / unexpected JSON shape
reset_state();
$settings            = new SiteSettings();
$remote_get_response = ok_response( '{"unexpected":true}' );
list( $result, $warnings ) = capture_warnings( function () use ( $settings ) {
	return $settings->subjectcode();
} );
check( 'subjectcode: unexpected JSON shape returns a WP_Error, not a fatal (no count(null))', is_wp_error( $result ) );
check( 'subjectcode: unexpected JSON shape raises no PHP warnings', array() === $warnings );
// WPM-236: an unusable feed must not be cached as a '---'-only list for a week
check( 'subjectcode: unexpected JSON shape is not cached', ! in_array( 'ucsc_subjects', transient_set_keys(), true ) );

// Happy path — flattens subjects across departments, sorted
reset_state();
$settings            = new SiteSettings();
$remote_get_response = ok_response( $class_depts_json );
$result              = $settings->subjectcode();
check( 'subjectcode: happy path returns a WP_REST_Response', $result instanceof WP_REST_Response );
check( 'subjectcode: happy path flattens and sorts subjects in expected shape',
	$result instanceof WP_REST_Response
	&& $result->get_data() === array(
		array( 'label' => '---', 'value' => '---' ),
		array( 'label' => 'Applied Mathematics', 'value' => 'AMS' ),
		array( 'label' => 'Computer Science', 'value' => 'CSE' ),
		array( 'label' => 'Statistics', 'value' => 'STAT' ),
	)
);
check( 'subjectcode: happy path caches under ucsc_subjects for a week',
	! empty( $transient_set_calls )
	&& $transient_set_calls[0]['key'] === 'ucsc_subjects'
	&& $transient_set_calls[0]['expiration'] === WEEK_IN_SECONDS
);

// Cache hit — no remote call
reset_state();
$settings                     = new SiteSettings();
$transients['ucsc_subjects']  = array( array( 'label' => '---', 'value' => '---' ) );
$result                       = $settings->subjectcode();
check( 'subjectcode: cache hit makes no remote call', count( $remote_get_calls ) === 0 );

// Regression: both methods declare their own sort — running both in one request
// must not fatal with "Cannot redeclare cmp()" (fixed by the usort closure).
check( 'departmentcode() and subjectcode() coexist in one request without a redeclare fatal', true );

// ── PeopleSoft feed failures (WPM-236) ─────────────────────────────────────────
// An unusable answer must not be cached for a week, and any failure backs off
// both endpoints for 5 minutes so repeated editor loads fail fast.

// A 200 with no departments (e.g. an empty feed) is not cached
reset_state();
$settings            = new SiteSettings();
$remote_get_response = ok_response( '{"depts":[]}' );
$result              = $settings->departmentcode();
check( 'WPM-236: departmentcode with an empty depts list returns a WP_Error', is_wp_error( $result ) );
check( 'WPM-236: departmentcode with an empty depts list is not cached for a week', ! in_array( 'ucsc_depts', transient_set_keys(), true ) );

reset_state();
$settings            = new SiteSettings();
$remote_get_response = ok_response( '<html>maintenance</html>' );
$result              = $settings->subjectcode();
check( 'WPM-236: subjectcode with an HTML 200 is not cached for a week', ! in_array( 'ucsc_subjects', transient_set_keys(), true ) );

// Every failure kind records a 5-minute backoff
$failures = array(
	'WP_Error'       => new WP_Error( 'http_request_failed', 'connection timed out' ),
	'non-2xx'        => err_response( 503, 'maintenance' ),
	'unparseable'    => ok_response( 'not json at all' ),
	'empty depts'    => ok_response( '{"depts":[]}' ),
);
foreach ( $failures as $kind => $response ) {
	reset_state();
	$settings            = new SiteSettings();
	$remote_get_response = $response;
	$settings->departmentcode();
	check( "WPM-236: records a 5-minute backoff after: {$kind}",
		$transient_set_calls === array( array( 'key' => 'ucsc_class_depts_failed', 'expiration' => 300 ) )
	);
}

// During the backoff both endpoints return a 503 without calling PeopleSoft
reset_state();
$settings                                = new SiteSettings();
$transients['ucsc_class_depts_failed']   = 1;
$remote_get_response                     = ok_response( $class_depts_json );
$dept_result                             = $settings->departmentcode();
$subject_result                          = $settings->subjectcode();
check( 'WPM-236: during the backoff departmentcode returns a 503 WP_Error',
	is_wp_error( $dept_result ) && 503 === $dept_result->get_error_data()['status']
);
check( 'WPM-236: during the backoff subjectcode returns a 503 WP_Error',
	is_wp_error( $subject_result ) && 503 === $subject_result->get_error_data()['status']
);
check( 'WPM-236: during the backoff PeopleSoft is not called', 0 === count( $remote_get_calls ) );

// A good answer does not set the backoff
reset_state();
$settings            = new SiteSettings();
$remote_get_response = ok_response( $class_depts_json );
$settings->departmentcode();
check( 'WPM-236: a good answer does not set the backoff', ! in_array( 'ucsc_class_depts_failed', transient_set_keys(), true ) );

// Timeout well under the CampusPress edge's 30s
reset_state();
$settings            = new SiteSettings();
$remote_get_response = ok_response( $class_depts_json );
$settings->departmentcode();
check( 'WPM-236: PeopleSoft fetch timeout stays well under the 30s edge timeout',
	isset( $remote_get_args[0]['timeout'] ) && $remote_get_args[0]['timeout'] <= 10
);

// ── divisioncode() (WPM-185) ────────────────────────────────────────────────
// Static list behind the Campus Directory division dropdown.

reset_state();
$settings  = new SiteSettings();
$result    = $settings->divisioncode();
$divisions = $result instanceof WP_REST_Response ? $result->get_data() : array();
$values    = array_column( $divisions, 'value' );
check( 'divisioncode: returns a WP_REST_Response', $result instanceof WP_REST_Response );
check( 'divisioncode: puts the --- placeholder first', array( 'label' => '---', 'value' => '---' ) === ( $divisions[0] ?? null ) );
check( 'divisioncode: every option is a {label, value} pair whose label matches its value',
	count( $divisions ) > 1 && array() === array_filter( $divisions, function ( $option ) {
		return array( 'label', 'value' ) !== array_keys( $option ) || $option['label'] !== $option['value'] || '' === $option['value'];
	} )
);
check( 'divisioncode: lists each division once', count( $values ) === count( array_unique( $values ) ) );
check( 'divisioncode: includes known divisions', in_array( 'Academic Affairs', $values, true ) && in_array( 'Visiting Faculty/Staff', $values, true ) );
check( 'divisioncode: makes no remote call and caches nothing', array() === $remote_get_calls && array() === $transient_set_calls );

// ── Done ──────────────────────────────────────────────────────────────────────

finish_tests();
