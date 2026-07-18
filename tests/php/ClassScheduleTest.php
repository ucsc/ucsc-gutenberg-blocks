<?php
/**
 * Dependency-free tests for ClassSchedule.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/ClassScheduleTest.php
 */

require __DIR__ . '/helpers/harness.php';

$rest_handler     = null;
$rest_requests    = array();
$enqueued_scripts = array();
$enqueued_styles  = array();
$query_var_values = array();
$redirects        = array();
$rewrite_rules    = array();
$options          = array();

function rest_do_request( $request ) {
	global $rest_handler, $rest_requests;
	$rest_requests[] = $request;
	return $rest_handler( $request );
}
function wp_enqueue_script( $handle ) {
	global $enqueued_scripts;
	$enqueued_scripts[] = $handle;
}
function wp_enqueue_style( $handle ) {
	global $enqueued_styles;
	$enqueued_styles[] = $handle;
}
function get_query_var( $var ) {
	global $query_var_values;
	return isset( $query_var_values[ $var ] ) ? $query_var_values[ $var ] : '';
}
function add_rewrite_rule( $regex, $query, $after = 'bottom' ) {
	global $rewrite_rules;
	$rewrite_rules[] = array(
		'regex' => $regex,
		'query' => $query,
		'after' => $after,
	);
}
function get_option( $name ) {
	global $options;
	return isset( $options[ $name ] ) ? $options[ $name ] : false;
}

// wp_redirect() in ClassSchedule is followed by exit; throw instead so tests
// can both observe the redirect and keep running.
class Test_Redirect_Called extends Exception {}
function wp_redirect( $location, $status = 302 ) {
	global $redirects;
	$redirects[] = array(
		'location' => $location,
		'status'   => $status,
	);
	throw new Test_Redirect_Called();
}

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
	private $data;

	public function __construct( $data = null ) {
		$this->data = $data;
	}

	public function get_data() {
		return $this->data;
	}
}

class Test_REST_Response {
	private $data;

	public function __construct( $data ) {
		$this->data = $data;
	}

	public function get_data() {
		return $this->data;
	}
}

require __DIR__ . '/../../classes/ClassSchedule.php';

function reset_test_state() {
	global $rest_requests, $enqueued_scripts, $enqueued_styles, $query_var_values, $redirects, $rewrite_rules, $options;
	$rest_requests    = array();
	$enqueued_scripts = array();
	$enqueued_styles  = array();
	$query_var_values = array();
	$redirects        = array();
	$rewrite_rules    = array();
	$options          = array();
}

function render_schedule( $attributes, $terms_response, $courses_response = array() ) {
	global $rest_handler;
	reset_test_state();

	$rest_handler = function ( $request ) use ( $terms_response, $courses_response ) {
		if ( '/ucsc/v1/terms' === $request->route ) {
			return $terms_response instanceof WP_Error
				? $terms_response
				: new Test_REST_Response( $terms_response );
		}

		if ( 0 === strpos( $request->route, '/ucsc/v1/courses/' ) ) {
			return $courses_response instanceof WP_Error
				? $courses_response
				: new Test_REST_Response( $courses_response );
		}

		return new WP_Error();
	};

	$class_schedule = new ClassSchedule();
	return $class_schedule->theHTML( $attributes );
}

function course_fixture( $overrides = array() ) {
	return array_merge(
		array(
			'subject'       => 'CSE',
			'catalog_nbr'   => '101',
			'title'         => 'Algorithms',
			'class_nbr'     => '12345',
			'enrl_capacity' => 100,
			'enrl_total'    => 90,
			'enrl_status'   => 'Open',
			'meeting_days'  => 'MWF',
			'start_time'    => '10:40 AM',
			'end_time'      => '11:45 AM',
			'location'      => 'Engineering 2 192',
			'instructors'   => array(
				array(
					'name'   => 'Ada Lovelace',
					'cruzid' => 'alovelace',
				),
			),
		),
		$overrides
	);
}

$terms = array(
	'terms' => array(
		array(
			'code'        => '2260',
			'description' => 'Summer 2026',
			'default'     => 'N',
		),
		array(
			'code'        => '2262',
			'description' => 'Fall 2026',
			'default'     => 'Y',
		),
	),
);

echo "error and empty states:\n";
$html = render_schedule( array( 'subjectOrDept' => 'dept', 'department' => 'CSE' ), new WP_Error() );
check( 'returns a terms error message when the terms request fails', false !== strpos( $html, 'Error loading terms' ) );

$html = render_schedule( array( 'subjectOrDept' => 'dept', 'department' => 'CSE' ), array( 'terms' => array() ) );
check( 'returns a no-terms message for an empty terms response', '<p>No terms available.</p>' === $html );

$html = render_schedule( array( 'subjectOrDept' => 'dept', 'department' => '' ), $terms );
check( 'prompts for block settings when no department is selected', false !== strpos( $html, 'Please select a department or subject' ) );
check( 'does not request courses without a selected criterion', 1 === count( $rest_requests ) );

$html = render_schedule(
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE' ),
	$terms,
	new WP_Error()
);
check( 'returns a no-courses message when the courses request fails', '<p>No courses found for the selected criteria.</p>' === $html );

echo "REST requests and rendered template:\n";
$html = render_schedule(
	array( 'subjectOrDept' => 'dept', 'department' => 'cse' ),
	$terms,
	array(
		'classes' => array(
			course_fixture(
				array(
					'catalog_nbr' => '101',
					'title'       => 'Algorithms',
					'class_nbr'   => '12345',
				)
			),
			course_fixture(
				array(
					'catalog_nbr' => '20',
					'title'       => 'Beginning Programming',
					'class_nbr'   => '54321',
				)
			),
		),
	)
);

check( 'requests courses for the default term', '/ucsc/v1/courses/2262' === $rest_requests[1]->route );
check( 'uppercases the department query parameter', array( 'dept' => 'CSE' ) === $rest_requests[1]->query_params );
check( 'renders the current ClassSchedule template', false !== strpos( $html, 'id="classSchedule"' ) );
check( 'selects the default term in the quarter dropdown', false !== strpos( $html, 'value="2262"' . "\n              " . 'selected="selected"' ) );
check( 'sorts courses numerically by catalog number', strpos( $html, 'CSE-20' ) < strpos( $html, 'CSE-101' ) );
check( 'renders course detail links', false !== strpos( $html, 'https://example.ucsc.edu/course/2262/54321' ) );
check( 'renders directory links for instructors', false !== strpos( $html, 'https://example.ucsc.edu/directory/alovelace' ) );
check( 'enqueues the schedule script after successful rendering', array( 'classschedule-js' ) === $enqueued_scripts );
check( 'enqueues the schedule stylesheet after successful rendering', array( 'classschedule' ) === $enqueued_styles );

$html = render_schedule(
	array( 'subjectOrDept' => 'subject', 'subject' => 'ams' ),
	array(
		'terms' => array(
			array(
				'code'        => '2260',
				'description' => 'Summer 2026',
				'default'     => 'N',
			),
		),
	),
	array( 'classes' => array( course_fixture( array( 'subject' => 'AMS' ) ) ) )
);
check( 'falls back to the first term when no term is marked default', '/ucsc/v1/courses/2260' === $rest_requests[1]->route );
check( 'uppercases the subject query parameter', array( 'subject' => 'AMS' ) === $rest_requests[1]->query_params );

echo "status, cancellation, and seats rendering:\n";
$html = render_schedule(
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE' ),
	$terms,
	array(
		'classes' => array(
			course_fixture( array( 'class_nbr' => '11111', 'enrl_status' => 'Open' ) ),
			course_fixture( array( 'class_nbr' => '22222', 'enrl_status' => 'Closed' ) ),
			course_fixture( array( 'class_nbr' => '33333', 'enrl_status' => 'Closed with Wait List' ) ),
		),
	)
);
check( 'maps Open enrollment status to the open row status', false !== strpos( $html, 'data-status="open"' ) );
check( 'maps Closed enrollment status to the closed row status', false !== strpos( $html, 'data-status="closed"' ) );
check( 'maps wait list status to waitlist (wait wins over closed)', false !== strpos( $html, 'data-status="waitlist"' ) );
check( 'labels the wait list status icon for screen readers', false !== strpos( $html, 'aria-label="Closed with Wait List"' ) );

$html = render_schedule(
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE' ),
	$terms,
	array(
		'classes' => array(
			course_fixture( array( 'meeting_days' => 'Cancelled', 'enrl_capacity' => 30, 'enrl_total' => 45 ) ),
		),
	)
);
check( 'flags cancelled courses on the title link', false !== strpos( $html, 'class="cancelled"' ) );
check( 'shows Cancelled in the days column for cancelled courses', false !== strpos( $html, '<span>Cancelled</span>' ) );
check( 'clamps open seats at zero when over-enrolled', false !== strpos( $html, '0 open' ) );
check( 'shows the enrollment capacity as the seat total', false !== strpos( $html, '/ 30 total' ) );

echo "instructor rendering:\n";
$html = render_schedule(
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE' ),
	$terms,
	array(
		'classes' => array(
			course_fixture(
				array(
					'instructors' => array(
						array( 'name' => 'Staff', 'cruzid' => 'staff' ),
					),
				)
			),
		),
	)
);
check( 'does not link Staff instructors even with a cruzid', false === strpos( $html, '/directory/staff' ) );
check( 'still prints the Staff instructor name', false !== strpos( $html, '<span>Staff</span>' ) );

$html = render_schedule(
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE' ),
	$terms,
	array(
		'classes' => array(
			course_fixture(
				array(
					'instructors' => array(
						array( 'name' => 'Ada Lovelace', 'cruzid' => 'alovelace' ),
						array( 'name' => 'Grace Hopper', 'cruzid' => '' ),
						array( 'name' => '', 'cruzid' => 'ghost' ),
					),
				)
			),
		),
	)
);
check( 'joins multiple instructors with a comma', false !== strpos( $html, '</a>, Grace Hopper' ) );
check( 'renders instructors without a cruzid as plain text', false === strpos( $html, '/directory/ghopper' ) && false !== strpos( $html, 'Grace Hopper' ) );
check( 'skips instructors with an empty name', false === strpos( $html, '/directory/ghost' ) );

echo "course detail routing:\n";
reset_test_state();
$class_schedule = new ClassSchedule();

$vars = $class_schedule->add_query_vars( array( 'existing' ) );
check( 'registers course_term, course_id, and legacy_redirect query vars', array( 'existing', 'course_term', 'course_id', 'legacy_redirect' ) === $vars );

$query_var_values = array( 'course_term' => '2262', 'course_id' => '50222' );
$template = $class_schedule->course_detail_template( '/theme/index.php' );
check( 'uses the course detail template when term and id are set', false !== strpos( $template, 'templates/CourseDetailTemplate.php' ) );

$query_var_values = array( 'course_term' => '2262' );
$template = $class_schedule->course_detail_template( '/theme/index.php' );
check( 'keeps the original template without a course id', '/theme/index.php' === $template );

echo "rewrite rules:\n";
reset_test_state();
$class_schedule->add_course_detail_rewrite();
check( 'registers two rewrite rules at the top', 2 === count( $rewrite_rules ) && 'top' === $rewrite_rules[0]['after'] && 'top' === $rewrite_rules[1]['after'] );

$canonical = $rewrite_rules[0]['regex'];
$legacy    = $rewrite_rules[1]['regex'];

$matched = preg_match( '#^' . $canonical . '#', 'course/2262/50222/', $m );
check( 'canonical rule matches /course/<term>/<id>/ and captures both', 1 === $matched && '2262' === $m[1] && '50222' === $m[2] );
check( 'canonical rule ignores legacy hyphenated URLs', 0 === preg_match( '#^' . $canonical . '#', 'course/2262-50222/class' ) );

$matched = preg_match( '#^' . $legacy . '#', 'courses/course/2262-50222/history-of-the-present', $m );
check( 'legacy rule matches prefixed hyphenated URLs and captures both', 1 === $matched && '2262' === $m[1] && '50222' === $m[2] );
check( 'legacy rule matches /course/<term>-<id>/class', 1 === preg_match( '#^' . $legacy . '#', 'course/2262-50222/class' ) );
check( 'legacy rule ignores canonical URLs', 0 === preg_match( '#^' . $legacy . '#', 'course/2262/50222/' ) );
check( 'legacy rule sets the legacy_redirect query flag', false !== strpos( $rewrite_rules[1]['query'], 'legacy_redirect=1' ) );

echo "legacy redirect:\n";
reset_test_state();
$query_var_values = array( 'legacy_redirect' => '1', 'course_term' => '2262', 'course_id' => '50222' );
$redirected = false;
try {
	$class_schedule->maybe_redirect_legacy_course();
} catch ( Test_Redirect_Called $e ) {
	$redirected = true;
}
check( 'redirects legacy course URLs', $redirected );
check( 'redirects to the canonical course URL with a 301', $redirected && array( 'location' => 'https://example.ucsc.edu/course/2262/50222/', 'status' => 301 ) === $redirects[0] );

reset_test_state();
$query_var_values = array( 'course_term' => '2262', 'course_id' => '50222' );
$class_schedule->maybe_redirect_legacy_course();
check( 'does not redirect without the legacy_redirect flag', array() === $redirects );

reset_test_state();
$query_var_values = array( 'legacy_redirect' => '1', 'course_term' => '2262' );
$class_schedule->maybe_redirect_legacy_course();
check( 'does not redirect when the course id is missing', array() === $redirects );

echo "course detail title:\n";
reset_test_state();
$rest_handler = function ( $request ) {
	return new Test_REST_Response(
		array(
			'primary_section' => array(
				'subject'     => 'CSE',
				'catalog_nbr' => '101',
				'title_long'  => 'Introduction to Algorithms',
			),
		)
	);
};
$query_var_values = array( 'course_term' => '2262', 'course_id' => '50222' );
$parts = $class_schedule->course_detail_title( array( 'title' => 'Old Title' ) );
check( 'requests the course detail REST route', '/ucsc/v1/course/2262/50222' === $rest_requests[0]->route );
check( 'builds the document title from the primary section', 'CSE 101 - Introduction to Algorithms' === $parts['title'] );

reset_test_state();
$rest_handler = function ( $request ) {
	return new WP_Error();
};
$query_var_values = array( 'course_term' => '2262', 'course_id' => '50222' );
$parts = $class_schedule->course_detail_title( array( 'title' => 'Old Title' ) );
check( 'keeps the title when the course request fails', 'Old Title' === $parts['title'] );

reset_test_state();
$rest_handler = function ( $request ) {
	return new Test_REST_Response( array() );
};
$query_var_values = array( 'course_term' => '2262', 'course_id' => '50222' );
$parts = $class_schedule->course_detail_title( array( 'title' => 'Old Title' ) );
check( 'keeps the title without a primary section', 'Old Title' === $parts['title'] );

reset_test_state();
$parts = $class_schedule->course_detail_title( array( 'title' => 'Old Title' ) );
check( 'skips the REST request outside course detail pages', array() === $rest_requests && 'Old Title' === $parts['title'] );

echo "department endpoint:\n";
reset_test_state();
$options['class_schedule_department'] = 'CSE';
$response = $class_schedule->classscheduledept();
check( 'returns the configured department from the REST endpoint', array( 'dept' => 'CSE' ) === $response->get_data() );

finish_tests();
