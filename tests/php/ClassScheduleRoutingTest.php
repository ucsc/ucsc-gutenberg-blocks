<?php
/**
 * Dependency-free tests for the ClassSchedule routing / course-detail methods
 * that ClassScheduleTest.php (which focuses on theHTML()) does not cover:
 * add_query_vars(), course_detail_template(), course_detail_title(),
 * classscheduledept(), and the maybe_redirect_legacy_course() bail path.
 *
 * Run from the plugin directory (php mode of the validate harness):
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/ClassScheduleRoutingTest.php
 */

$rest_handler  = null;
$rest_requests = array();
$query_vars    = array();
$options       = array();
$redirected_to = null;

function add_action() {}
function add_filter() {}
function register_rest_route() {}
function add_rewrite_rule() {}
function sanitize_text_field( $value ) {
	return trim( strip_tags( (string) $value ) );
}
function is_wp_error( $value ) {
	return $value instanceof WP_Error;
}
function rest_do_request( $request ) {
	global $rest_handler, $rest_requests;
	$rest_requests[] = $request;
	return $rest_handler( $request );
}
function plugin_dir_path( $file ) {
	return dirname( $file ) . '/';
}
function get_query_var( $key ) {
	global $query_vars;
	return $query_vars[ $key ] ?? '';
}
function get_option( $key ) {
	global $options;
	return $options[ $key ] ?? false;
}
function home_url( $path = '' ) {
	return 'https://example.ucsc.edu' . $path;
}
function wp_redirect( $location, $status = 302 ) {
	global $redirected_to;
	$redirected_to = array( 'location' => $location, 'status' => $status );
}

class WP_Error {}

class WP_REST_Request {
	public $method;
	public $route;
	public $query_params = array();

	public function __construct( $method, $route ) {
		$this->method = $method;
		$this->route  = $route;
	}

	public function set_query_params( $query_params ) {
		$this->query_params = $query_params;
	}
}

class WP_REST_Response {
	public $data;

	public function __construct( $data ) {
		$this->data = $data;
	}

	public function get_data() {
		return $this->data;
	}
}

require __DIR__ . '/../../classes/ClassSchedule.php';

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

function reset_test_state() {
	global $rest_requests, $query_vars, $options, $redirected_to, $rest_handler;
	$rest_requests = array();
	$query_vars    = array();
	$options       = array();
	$redirected_to = null;
	$rest_handler  = function () {
		return new WP_Error();
	};
}

reset_test_state();
$block = new ClassSchedule();

echo "add_query_vars():\n";
$vars = $block->add_query_vars( array( 'existing' ) );
check( 'preserves existing query vars', 'existing' === $vars[0] );
check( 'registers course_term', in_array( 'course_term', $vars, true ) );
check( 'registers course_id', in_array( 'course_id', $vars, true ) );
check( 'registers legacy_redirect', in_array( 'legacy_redirect', $vars, true ) );

echo "course_detail_template():\n";
reset_test_state();
$query_vars = array( 'course_term' => '2262', 'course_id' => '12345' );
$template   = $block->course_detail_template( 'theme/page.php' );
check( 'routes to the course detail template when both query vars are set', false !== strpos( $template, 'CourseDetailTemplate.php' ) );

reset_test_state();
$query_vars = array( 'course_term' => '2262' );
check( 'returns the original template when course_id is missing', 'theme/page.php' === $block->course_detail_template( 'theme/page.php' ) );

reset_test_state();
check( 'returns the original template for a normal page', 'theme/page.php' === $block->course_detail_template( 'theme/page.php' ) );

echo "course_detail_title():\n";
reset_test_state();
$query_vars   = array( 'course_term' => '2262', 'course_id' => '12345' );
$rest_handler = function ( $request ) {
	if ( '/ucsc/v1/course/2262/12345' === $request->route ) {
		return new WP_REST_Response(
			array(
				'primary_section' => array(
					'subject'     => 'CSE',
					'catalog_nbr' => '101',
					'title_long'  => 'Introduction to Algorithms',
				),
			)
		);
	}
	return new WP_Error();
};
$parts = $block->course_detail_title( array( 'title' => 'Old Title' ) );
check( 'requests the course detail for the term and id', '/ucsc/v1/course/2262/12345' === $rest_requests[0]->route );
check( 'sets a descriptive course title', 'CSE 101 - Introduction to Algorithms' === $parts['title'] );

reset_test_state();
$parts = $block->course_detail_title( array( 'title' => 'Old Title' ) );
check( 'leaves the title unchanged off a course detail page', 'Old Title' === $parts['title'] );
check( 'does not call the REST API without course query vars', 0 === count( $rest_requests ) );

reset_test_state();
$query_vars   = array( 'course_term' => '2262', 'course_id' => '12345' );
$rest_handler = function () {
	return new WP_Error();
};
$parts = $block->course_detail_title( array( 'title' => 'Old Title' ) );
check( 'leaves the title unchanged when the REST request errors', 'Old Title' === $parts['title'] );

echo "classscheduledept():\n";
reset_test_state();
$options  = array( 'class_schedule_department' => 'HISTORY' );
$response = $block->classscheduledept();
check( 'returns a WP_REST_Response', $response instanceof WP_REST_Response );
check( 'exposes the configured department option', array( 'dept' => 'HISTORY' ) === $response->get_data() );

echo "maybe_redirect_legacy_course():\n";
reset_test_state();
$query_vars = array( 'course_term' => '2262', 'course_id' => '12345' );
$block->maybe_redirect_legacy_course();
check( 'does not redirect when legacy_redirect is not set', null === $redirected_to );

echo "\n" . ( $tests - $failed ) . "/$tests passed\n";
exit( 0 === $failed ? 0 : 1 );
