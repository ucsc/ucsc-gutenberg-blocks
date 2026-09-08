<?php
/**
 * WPM-134: Dependency-free tests for SiteSettings::cddepartmentcode() failure paths.
 *
 * Covers: WP_Error from the remote fetch, non-2xx response, unparseable HTML,
 * and the happy path (valid HTML parsed into the department list).
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
	global $remote_get_calls, $remote_get_response;
	$remote_get_calls[] = $url;
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
	global $transients, $transient_set_calls, $remote_get_calls, $remote_get_response;
	$transients          = array();
	$transient_set_calls = array();
	$remote_get_calls    = array();
	$remote_get_response = null;
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
	empty( $transient_set_calls )
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
	empty( $transient_set_calls )
);

// ── Unparseable HTML ─────────────────────────────────────────────────────────

reset_state();
$settings             = new SiteSettings();
$remote_get_response  = ok_response( '<html><body>no department select here</body></html>' );
$result = $settings->cddepartmentcode();

check( 'cddepartmentcode: unparseable HTML does not fatal',
	$result instanceof WP_REST_Response
);
check( 'cddepartmentcode: unparseable HTML returns only the placeholder option (empty list)',
	$result instanceof WP_REST_Response && count( $result->get_data() ) === 1
	&& $result->get_data()[0]['value'] === '---'
);

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

// ── Done ──────────────────────────────────────────────────────────────────────

finish_tests();
