<?php
/**
 * Dependency-free tests for CourseCatalog feed target and cache controls.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/CourseCatalogTest.php
 */

define( 'WEEK_IN_SECONDS', 604800 );

$actions              = array();
$current_user_id      = 0;
$can_manage_options   = false;
$transients           = array();
$remote_requests      = array();
$block_supports_class = '';
$remote_response      = array(
	'code' => 200,
	'body' => '<?xml version="1.0"?><catalog><course><subject>LIT</subject><catalog_nbr>1</catalog_nbr><title>Intro</title><level>Lower Division</level><units>5</units><description>Test</description></course></catalog>',
);

function add_action( $hook, $callback ) {
	global $actions;
	$actions[ $hook ] = $callback;
}
function wp_enqueue_script() {}
function wp_register_script() {}
function wp_enqueue_style() {}
function wp_register_style() {}
function plugins_url( $path ) {
	return 'https://example.ucsc.edu/wp-content/plugins/ucsc-gutenberg-blocks/' . $path;
}
function plugin_dir_path( $file ) {
	return dirname( $file ) . '/';
}
function is_user_logged_in() {
	global $current_user_id;
	return 0 !== $current_user_id;
}
function current_user_can( $capability ) {
	global $can_manage_options;
	return 'manage_options' === $capability && $can_manage_options;
}
function wp_unslash( $value ) {
	return stripslashes( $value );
}
function get_transient( $key ) {
	global $transients;
	return isset( $transients[ $key ] ) ? $transients[ $key ] : false;
}
function set_transient( $key, $value, $expiration ) {
	global $transients;
	$transients[ $key ] = array(
		'value'      => $value,
		'expiration' => $expiration,
	);
	return true;
}
function wp_remote_post( $url, $args ) {
	global $remote_requests, $remote_response;
	$remote_requests[] = array(
		'url'  => $url,
		'args' => $args,
	);
	return $remote_response;
}
function wp_remote_retrieve_response_code( $response ) {
	return $response['code'];
}
function wp_remote_retrieve_body( $response ) {
	return $response['body'];
}
function is_wp_error( $value ) {
	return $value instanceof WP_Error;
}
// Simulates WordPress merging block-supports classes (e.g. the "Additional CSS
// class(es)" field a site editor sets in the block inspector) into the wrapper
// attributes, so tests can exercise the WPM-23 custom-class support end to end.
function set_block_custom_class( $class ) {
	global $block_supports_class;
	$block_supports_class = $class;
}

function get_block_wrapper_attributes( $attributes = array() ) {
	global $block_supports_class;
	$output = array();
	if ( isset( $attributes['id'] ) ) {
		$output[] = 'id="' . $attributes['id'] . '"';
	}
	if ( '' !== $block_supports_class ) {
		$output[] = 'class="' . $block_supports_class . '"';
	}
	return implode( ' ', $output );
}

class WP_Error {
	private $code;
	private $message;
	private $data;

	public function __construct( $code = '', $message = '', $data = array() ) {
		$this->code    = $code;
		$this->message = $message;
		$this->data    = $data;
	}

	public function get_error_code() {
		return $this->code;
	}

	public function get_error_message() {
		return $this->message;
	}

	public function get_error_data() {
		return $this->data;
	}
}

class Test_WPDB {
	public $options = 'wp_options';
	public $last_query;

	public function esc_like( $text ) {
		return addcslashes( $text, '_%\\' );
	}

	public function prepare( $query, ...$args ) {
		foreach ( $args as $arg ) {
			$query = preg_replace( '/%s/', "'" . $arg . "'", $query, 1 );
		}
		return $query;
	}

	public function query( $query ) {
		$this->last_query = $query;
		return 2;
	}
}

$wpdb = new Test_WPDB();

require __DIR__ . '/../../classes/CourseCatalog.php';

// WPM-117: Use the shared instrumented harness. The harness guards all its stubs
// with function_exists, so our custom test-specific stubs above win.
require __DIR__ . '/helpers/harness.php';

function reset_test_state() {
	global $current_user_id, $can_manage_options, $transients, $remote_requests, $remote_response, $wpdb, $block_supports_class;

	$current_user_id      = 0;
	$can_manage_options   = false;
	$transients           = array();
	$remote_requests      = array();
	$block_supports_class = '';
	$remote_response      = array(
		'code' => 200,
		'body' => '<?xml version="1.0"?><catalog><course><subject>LIT</subject><catalog_nbr>1</catalog_nbr><title>Intro</title><level>Lower Division</level><units>5</units><description>Test</description></course></catalog>',
	);
	$wpdb->last_query   = null;

	$_GET    = array();
	$_SERVER = array();

	putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET' );
	putenv( 'UCSC_COURSE_CATALOG_BYPASS_CACHE' );
	putenv( 'UCSC_COURSE_CATALOG_ALLOW_REQUEST_OVERRIDE' );
}

function make_catalog() {
	reset_test_state();
	return new CourseCatalog();
}

echo "PeopleSoft target selection:\n";

$catalog = make_catalog();
$target  = $catalog->getPeopleSoftTarget();
check( 'defaults to production target', 'prod' === $target['target'] );
check( 'defaults to production Host header value', 'my.prd.ais.aws.ucsc.edu' === $target['host'] );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=qa' );
$target = $catalog->getPeopleSoftTarget();
check( 'maps qa env alias to csqa target', 'csqa' === $target['target'] );
check( 'uses CSQA To header value', 'PSFT_CSQA' === $target['to'] );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=definitely-not-real' );
$target = $catalog->getPeopleSoftTarget();
check( 'falls back to production for unknown env target', 'prod' === $target['target'] );

// Stage/QA feed testing override (GET arg based) — disabled in CourseCatalog.php now that testing
// is done, so these assertions are commented out alongside it.
// echo "request override gating:\n";
//
// $catalog = make_catalog();
// putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=csqa' );
// $_SERVER['HTTP_HOST']                 = 'wp-dev.ucsc:443';
// $_GET['ucsc_course_catalog_target']   = 'prod';
// $current_user_id                      = 1;
// $can_manage_options                   = true;
// $target                              = $catalog->getPeopleSoftTarget();
// check( 'admin can override target on dev host with port', 'prod' === $target['target'] );
//
// $catalog = make_catalog();
// putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=csqa' );
// $_SERVER['HTTP_HOST']                 = 'wordpress.ucsc.edu:443';
// $_GET['ucsc_course_catalog_target']   = 'prod';
// $current_user_id                      = 1;
// $can_manage_options                   = true;
// $target                              = $catalog->getPeopleSoftTarget();
// check( 'admin cannot override target on production host by default', 'csqa' === $target['target'] );
//
// $catalog = make_catalog();
// putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=csqa' );
// $_SERVER['HTTP_HOST']                 = 'localhost:8080';
// $_GET['ucsc_course_catalog_target']   = 'prod';
// $target                              = $catalog->getPeopleSoftTarget();
// check( 'logged-out request cannot override target on dev host', 'csqa' === $target['target'] );
//
// $catalog = make_catalog();
// putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=csqa' );
// putenv( 'UCSC_COURSE_CATALOG_ALLOW_REQUEST_OVERRIDE=true' );
// $_SERVER['HTTP_HOST']                 = 'wordpress.ucsc.edu';
// $_GET['ucsc_course_catalog_target']   = 'prod';
// $current_user_id                      = 1;
// $can_manage_options                   = true;
// $target                              = $catalog->getPeopleSoftTarget();
// check( 'explicit server opt-in allows production-host admin override', 'prod' === $target['target'] );

echo "cache controls:\n";

$catalog = make_catalog();
check( 'cache bypass defaults off', false === $catalog->shouldBypassCache() );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_BYPASS_CACHE=true' );
check( 'env var enables cache bypass', true === $catalog->shouldBypassCache() );

// Stage/QA feed testing override (GET arg based) — disabled in CourseCatalog.php now that testing
// is done, so this assertion is commented out alongside it.
// $catalog = make_catalog();
// putenv( 'UCSC_COURSE_CATALOG_BYPASS_CACHE=true' );
// $_SERVER['HTTP_HOST']                         = 'wp-dev.ucsc';
// $_GET['ucsc_course_catalog_bypass_cache']     = '0';
// $current_user_id                              = 1;
// $can_manage_options                           = true;
// check( 'admin request can disable cache bypass on dev host', false === $catalog->shouldBypassCache() );

$catalog = make_catalog();
$catalog->getCachedCourses(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
check( 'successful response is cached under target-aware key', isset( $transients['course-catalog-prod-lit-dept'] ) );
check( 'successful response uses one remote request', 1 === count( $remote_requests ) );

echo "prod vs QA cache coexistence (WPM-167):\n";

// Spec: prod and QA responses for the same dept/subject MUST use independent
// transient keys so a QA fetch never poisons the prod cache and vice versa.
// NOTE: make_catalog() calls reset_test_state() which clears the env and resets
// $transients/$remote_requests. Set the target AFTER make_catalog().

// Fetch as prod (default target).
$catalog = make_catalog(); // resets env + state
$catalog->getCachedCourses(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
$prod_key_exists      = isset( $transients['course-catalog-prod-lit-dept'] );
$after_prod_requests  = count( $remote_requests );

// Fetch the same dept as qa — keep the prod transient in place, add only the QA fetch.
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=qa' );
$catalog_qa = new CourseCatalog(); // construct without reset so transients/requests persist
$catalog_qa->getCachedCourses(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET' ); // restore
$qa_key_exists = isset( $transients['course-catalog-csqa-lit-dept'] );

check( 'prod dept query is stored under a prod-prefixed transient key',  $prod_key_exists );
check( 'QA dept query is stored under a csqa-prefixed transient key',    $qa_key_exists );
check( 'prod and QA use distinct transient keys for the same query',     $prod_key_exists && $qa_key_exists && 2 === count( $transients ) );
check( 'both prod and QA each issue exactly one remote request',         2 === count( $remote_requests ) );

// Confirm a QA cache hit does not satisfy a prod request.
$catalog = make_catalog(); // resets env + state
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET=qa' );
$catalog_qa2 = new CourseCatalog();
$catalog_qa2->getCachedCourses( array( 'subjectOrDept' => 'dept', 'department' => 'lit', 'subject' => '' ) );
putenv( 'UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET' );
check( 'a QA cache entry is present after QA fetch', isset( $transients['course-catalog-csqa-lit-dept'] ) );

// Now fetch as prod — should NOT hit the QA transient, should issue a new request.
$catalog_prod = new CourseCatalog();
$catalog_prod->getCachedCourses( array( 'subjectOrDept' => 'dept', 'department' => 'lit', 'subject' => '' ) );
check( 'prod fetch after QA cache is populated still issues a remote request (no cross-target cache hit)', 2 === count( $remote_requests ) );

$catalog = make_catalog();
putenv( 'UCSC_COURSE_CATALOG_BYPASS_CACHE=true' );
$catalog->getCachedCourses(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
check( 'cache bypass does not store successful response', array() === $transients );
check( 'cache bypass still calls the remote feed', 1 === count( $remote_requests ) );

echo "feed error handling:\n";

$catalog         = make_catalog();
$remote_response = new WP_Error( 'http_request_failed', 'cURL error 28: connection timed out' );
$result          = $catalog->getCachedCourses(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
check( 'transport WP_Error is returned to the caller', $result instanceof WP_Error && 'http_request_failed' === $result->get_error_code() );
check( 'transport error is not cached', array() === $transients );

$catalog                 = make_catalog();
$remote_response['code'] = 500;
$result                  = $catalog->getCachedCourses(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
check( 'non-2xx response returns a feed error', $result instanceof WP_Error && 'course_catalog_feed_error' === $result->get_error_code() );
check( 'non-2xx response reports the upstream status code', 500 === $result->get_error_data()['status'] );
check( 'non-2xx response is not cached', array() === $transients );

$catalog                 = make_catalog();
$remote_response['body'] = 'PeopleSoft is down for maintenance';
$result                  = $catalog->getCachedCourses(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
check( 'invalid XML in a 200 response returns a feed XML error', $result instanceof WP_Error && 'course_catalog_feed_xml_error' === $result->get_error_code() );
check( 'invalid XML in a 200 response is not cached', array() === $transients );

echo "subject queries:\n";

$catalog = make_catalog();
$catalog->getCachedCourses(
	array(
		'subjectOrDept' => 'subject',
		'department'    => '',
		'subject'       => 'LIT',
	)
);
check( 'subject query is cached under subject-aware key', isset( $transients['course-catalog-prod-lit-subject'] ) );
check( 'subject query requests the lowercased subject element', false !== strpos( $remote_requests[0]['args']['body'], '<subject>lit</subject>' ) );

echo "rendered HTML:\n";

$catalog                 = make_catalog();
$remote_response['code'] = 500;
$html                    = $catalog->theHTML(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
check( 'feed failure renders the unavailable fallback', false !== strpos( $html, 'temporarily unavailable' ) );
check( 'feed failure fallback keeps the block wrapper', false !== strpos( $html, 'id="courseCatalog"' ) );

$catalog                 = make_catalog();
$remote_response['body'] = '<?xml version="1.0"?><catalog>'
	. '<course><subject>LIT</subject><catalog_nbr>80A</catalog_nbr><title>Intro</title><level>Graduate</level><units>5</units><description>Test</description></course>'
	. '<course><subject>LIT</subject><catalog_nbr>80B</catalog_nbr><title>Mystery</title><level>Mystery Level</level><units>5</units><description>Test</description></course>'
	. '</catalog>';
$html                    = $catalog->theHTML(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
check( 'course rows render the course title', false !== strpos( $html, '<td class="collapseExpandText">Intro</td>' ) );
check( 'graduate level maps to sort value 3', false !== strpos( $html, 'Graduate<span class="secret">3</span>' ) );
check( 'unknown level maps to sort value 0 instead of reusing the previous row', false !== strpos( $html, 'Mystery Level<span class="secret">0</span>' ) );

echo "block wrapper custom class support (WPM-23):\n";

$catalog = make_catalog();
$html    = $catalog->theHTML(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
check( 'wrapper has no class attribute when the site editor has not set one', 0 === strpos( $html, '<div id="courseCatalog">' ) );

$catalog = make_catalog();
set_block_custom_class( 'econ-course-catalog-small-text' );
$html    = $catalog->theHTML(
	array(
		'subjectOrDept' => 'dept',
		'department'    => 'lit',
		'subject'       => '',
	)
);
check( 'site-editor-supplied custom class renders on the block wrapper alongside the id', 0 === strpos( $html, '<div id="courseCatalog" class="econ-course-catalog-small-text">' ) );

echo "cache clearing:\n";

$deleted = CourseCatalog::clearCachedCourses( 'qa' );
check( 'cache clear returns deleted row count', 2 === $deleted );
check( 'cache clear maps qa alias to csqa transient prefix', false !== strpos( $wpdb->last_query, $wpdb->esc_like( '_transient_course-catalog-csqa-' ) ) );

$deleted = CourseCatalog::clearCachedCourses();
check( 'cache clear all returns deleted row count', 2 === $deleted );
check( 'cache clear all targets all course catalog transients', false !== strpos( $wpdb->last_query, $wpdb->esc_like( '_transient_course-catalog-' ) ) );

finish_tests();
