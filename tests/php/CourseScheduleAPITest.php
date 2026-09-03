<?php
/**
 * WPM-131: Dependency-free tests for Course_Schedule_API.
 *
 * Covers: route registration, get_terms, get_courses, get_course_details,
 * and validate_remote_response (via the public methods).
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/CourseScheduleAPITest.php
 *
 * Or with coverage:
 *   bash tests/php/run-php-coverage.sh
 */

// ── Globals ──────────────────────────────────────────────────────────────────

$transients            = array();
$transient_set_calls   = array();
$registered_routes     = array();
$remote_get_calls      = array();
$remote_get_response   = null; // set per test

// ── WordPress stubs ───────────────────────────────────────────────────────────

function add_action() {}

function register_rest_route( $namespace, $route, $args ) {
	global $registered_routes;
	$registered_routes[] = array(
		'namespace' => $namespace,
		'route'     => $route,
		'args'      => $args,
	);
}

function get_transient( $key ) {
	global $transients;
	return isset( $transients[ $key ] ) ? $transients[ $key ] : false;
}

function set_transient( $key, $value, $expiration ) {
	global $transients, $transient_set_calls;
	$transients[ $key ]      = $value;
	$transient_set_calls[]   = array( 'key' => $key, 'expiration' => $expiration );
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

function rest_ensure_response( $data ) {
	// Return as-is; tests inspect the return value directly.
	return $data;
}

function sanitize_text_field( $value ) {
	return trim( strip_tags( (string) $value ) );
}

function is_wp_error( $value ) {
	return $value instanceof WP_Error;
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

if ( ! class_exists( 'WP_REST_Request' ) ) {
	class WP_REST_Request {
		private $params = array();
		public function set_param( $key, $value ) { $this->params[ $key ] = $value; }
		public function get_param( $key )          { return isset( $this->params[ $key ] ) ? $this->params[ $key ] : null; }
	}
}

// ── Load the class under test ─────────────────────────────────────────────────

require_once __DIR__ . '/../../src/API/Course_Schedule_API.php';

// ── Harness ───────────────────────────────────────────────────────────────────

require_once __DIR__ . '/helpers/harness.php';

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Reset all global state between tests.
 */
function reset_state() {
	global $transients, $transient_set_calls, $registered_routes,
		   $remote_get_calls, $remote_get_response;
	$transients          = array();
	$transient_set_calls = array();
	$registered_routes   = array();
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
function err_response( $code ) {
	return array( 'code' => $code, 'body' => '' );
}

// ── Route registration (3 tests) ──────────────────────────────────────────────

reset_state();
$api = new Course_Schedule_API();
$api->register_routes();

check( 'registers /terms route',
	count( array_filter( $registered_routes, fn( $r ) => $r['route'] === '/terms' ) ) === 1
);

check( 'registers /courses/{term} route',
	count( array_filter( $registered_routes, fn( $r ) => str_contains( $r['route'], '/courses/' ) ) ) === 1
);

check( 'registers /course/{term}/{course} route',
	count( array_filter( $registered_routes, fn( $r ) => str_contains( $r['route'], '/course/' ) ) ) === 1
);

// ── get_terms ─────────────────────────────────────────────────────────────────

// cache hit — no remote call
reset_state();
$api = new Course_Schedule_API();
$transients['ucsc_ps_terms'] = array( 'cached' => true );
$result = $api->get_terms( new WP_REST_Request() );
check( 'get_terms: cache hit returns cached data',
	isset( $result['cached'] ) && $result['cached'] === true
);
check( 'get_terms: cache hit makes no remote call',
	count( $remote_get_calls ) === 0
);

// WP_Error from remote
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = new WP_Error( 'http_request_failed', 'timeout' );
$result = $api->get_terms( new WP_REST_Request() );
check( 'get_terms: WP_Error remote returns WP_Error',
	is_wp_error( $result )
);
check( 'get_terms: WP_Error remote error code is api_error',
	$result->get_error_code() === 'api_error'
);
check( 'get_terms: WP_Error result is not cached',
	empty( $transient_set_calls )
);

// non-2xx response
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = err_response( 404 );
$result = $api->get_terms( new WP_REST_Request() );
check( 'get_terms: 404 response returns WP_Error',
	is_wp_error( $result )
);
check( 'get_terms: 404 response not cached',
	empty( $transient_set_calls )
);

// valid JSON response
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = ok_response( json_encode( array( 'terms' => array( '2262', '2268' ) ) ) );
$result = $api->get_terms( new WP_REST_Request() );
check( 'get_terms: valid JSON returned as array',
	is_array( $result ) && isset( $result['terms'] )
);
check( 'get_terms: valid JSON response is cached',
	isset( $transients['ucsc_ps_terms'] )
);
check( 'get_terms: cached with 15-minute TTL',
	! empty( $transient_set_calls ) && $transient_set_calls[0]['expiration'] === 900
);

// invalid JSON
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = ok_response( 'not-json{{{' );
$result = $api->get_terms( new WP_REST_Request() );
check( 'get_terms: invalid JSON returns WP_Error',
	is_wp_error( $result )
);
check( 'get_terms: invalid JSON error code is json_error',
	$result->get_error_code() === 'json_error'
);
check( 'get_terms: invalid JSON not cached',
	empty( $transient_set_calls )
);

// ── get_courses ───────────────────────────────────────────────────────────────

// cache hit
reset_state();
$api       = new Course_Schedule_API();
$cache_key = 'ucsc_ps_courses_' . md5( '2262' . '' );
$transients[ $cache_key ] = array( 'courses' => array() );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$result = $api->get_courses( $req );
check( 'get_courses: cache hit returns cached data',
	is_array( $result ) && isset( $result['courses'] )
);
check( 'get_courses: cache hit makes no remote call',
	count( $remote_get_calls ) === 0
);

// WP_Error from remote
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = new WP_Error( 'http_request_failed', 'timeout' );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$result = $api->get_courses( $req );
check( 'get_courses: WP_Error remote returns WP_Error',
	is_wp_error( $result )
);
check( 'get_courses: WP_Error not cached',
	empty( $transient_set_calls )
);

// non-2xx
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = err_response( 503 );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$result = $api->get_courses( $req );
check( 'get_courses: 503 response returns WP_Error',
	is_wp_error( $result )
);

// valid JSON, no filters
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = ok_response( json_encode( array( 'courses' => array( 'CSE 101' ) ) ) );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$result = $api->get_courses( $req );
check( 'get_courses: valid JSON returned',
	is_array( $result ) && isset( $result['courses'] )
);
check( 'get_courses: valid JSON response cached',
	! empty( $transient_set_calls )
);
check( 'get_courses: no query params — URL has no query string',
	strpos( $remote_get_calls[0], '?' ) === false
);

// subject param uppercased in URL
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = ok_response( json_encode( array() ) );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$req->set_param( 'subject', 'his' );
$api->get_courses( $req );
check( 'get_courses: subject param uppercased in remote URL',
	strpos( $remote_get_calls[0], 'subject=HIS' ) !== false
);

// dept param uppercased in URL
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = ok_response( json_encode( array() ) );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$req->set_param( 'dept', 'cse' );
$api->get_courses( $req );
check( 'get_courses: dept param uppercased in remote URL',
	strpos( $remote_get_calls[0], 'dept=CSE' ) !== false
);

// invalid JSON
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = ok_response( 'not-json' );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$result = $api->get_courses( $req );
check( 'get_courses: invalid JSON returns WP_Error',
	is_wp_error( $result )
);

// subject and dept produce separate cache keys
reset_state();
$api = new Course_Schedule_API();
$remote_get_response = ok_response( json_encode( array() ) );

$req_subject = new WP_REST_Request();
$req_subject->set_param( 'term', '2262' );
$req_subject->set_param( 'subject', 'HIS' );
$api->get_courses( $req_subject );

$req_dept = new WP_REST_Request();
$req_dept->set_param( 'term', '2262' );
$req_dept->set_param( 'dept', 'HIS' );
$api->get_courses( $req_dept );

$keys = array_column( $transient_set_calls, 'key' );
check( 'get_courses: subject and dept queries use separate cache keys',
	count( array_unique( $keys ) ) === 2
);

// ── get_course_details ────────────────────────────────────────────────────────

// cache hit
reset_state();
$api = new Course_Schedule_API();
$transients['ucsc_ps_course_2262_12345'] = array( 'title' => 'Algorithms' );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$req->set_param( 'course', '12345' );
$result = $api->get_course_details( $req );
check( 'get_course_details: cache hit returns cached data',
	is_array( $result ) && $result['title'] === 'Algorithms'
);
check( 'get_course_details: cache hit makes no remote call',
	count( $remote_get_calls ) === 0
);

// WP_Error
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = new WP_Error( 'http_request_failed', 'timeout' );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$req->set_param( 'course', '12345' );
$result = $api->get_course_details( $req );
check( 'get_course_details: WP_Error remote returns WP_Error',
	is_wp_error( $result )
);
check( 'get_course_details: WP_Error not cached',
	empty( $transient_set_calls )
);

// non-2xx
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = err_response( 404 );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$req->set_param( 'course', '12345' );
$result = $api->get_course_details( $req );
check( 'get_course_details: 404 returns WP_Error',
	is_wp_error( $result )
);

// valid JSON
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = ok_response( json_encode( array( 'title' => 'Algorithms', 'units' => 5 ) ) );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$req->set_param( 'course', '12345' );
$result = $api->get_course_details( $req );
check( 'get_course_details: valid JSON returned',
	is_array( $result ) && $result['title'] === 'Algorithms'
);
check( 'get_course_details: valid JSON cached under term+course key',
	isset( $transients['ucsc_ps_course_2262_12345'] )
);
check( 'get_course_details: correct remote URL constructed',
	strpos( $remote_get_calls[0], '/SCX_CLASS_DETAIL.v1/2262/12345' ) !== false
);

// invalid JSON
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = ok_response( 'not-json' );
$req = new WP_REST_Request();
$req->set_param( 'term', '2262' );
$req->set_param( 'course', '12345' );
$result = $api->get_course_details( $req );
check( 'get_course_details: invalid JSON returns WP_Error',
	is_wp_error( $result )
);

// ── validate_remote_response via status codes ─────────────────────────────────

// 4xx status preserved in error data
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = err_response( 400 );
$req = new WP_REST_Request();
$result = $api->get_terms( $req );
check( 'validate_remote_response: 4xx status code preserved in WP_Error data',
	is_wp_error( $result ) && isset( $result->data['status'] ) && $result->data['status'] === 400
);

// 3xx/1xx mapped to 502
reset_state();
$api                 = new Course_Schedule_API();
$remote_get_response = err_response( 301 );
$req = new WP_REST_Request();
$result = $api->get_terms( $req );
check( 'validate_remote_response: 3xx status mapped to 502 in WP_Error data',
	is_wp_error( $result ) && isset( $result->data['status'] ) && $result->data['status'] === 502
);

// ── Done ──────────────────────────────────────────────────────────────────────

finish_tests();
