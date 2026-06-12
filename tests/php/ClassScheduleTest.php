<?php
/**
 * Dependency-free tests for ClassSchedule::theHTML().
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/ClassScheduleTest.php
 */

$rest_handler     = null;
$rest_requests    = array();
$enqueued_scripts = array();
$enqueued_styles  = array();

function add_action() {}
function add_filter() {}
function register_rest_route() {}
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
function wp_enqueue_script( $handle ) {
	global $enqueued_scripts;
	$enqueued_scripts[] = $handle;
}
function wp_enqueue_style( $handle ) {
	global $enqueued_styles;
	$enqueued_styles[] = $handle;
}
function esc_attr( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}
function esc_html( $value ) {
	return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
}
function esc_url( $value ) {
	return esc_attr( $value );
}
function selected( $selected, $current ) {
	if ( (string) $selected === (string) $current ) {
		echo 'selected="selected"';
	}
}
function checked( $checked, $current = true ) {
	if ( (string) $checked === (string) $current ) {
		echo 'checked="checked"';
	}
}
function home_url( $path = '' ) {
	return 'https://example.ucsc.edu' . $path;
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
	global $rest_requests, $enqueued_scripts, $enqueued_styles;
	$rest_requests    = array();
	$enqueued_scripts = array();
	$enqueued_styles  = array();
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
check( 'renders #classScheduleTable mount node', false !== strpos( $html, 'id="classScheduleTable"' ) );
check( 'selects the default term in the quarter dropdown', false !== strpos( $html, 'value="2262"' . "\n              " . 'selected="selected"' ) );
check( 'sorts courses numerically by catalog number', strpos( $html, 'CSE-20' ) < strpos( $html, 'CSE-101' ) );
check( 'renders course detail links', false !== strpos( $html, 'https://example.ucsc.edu/course/2262/54321' ) );
check( 'renders directory links for instructors', false !== strpos( $html, 'https://example.ucsc.edu/directory/alovelace' ) );
check( 'enqueues the schedule script after successful rendering', array( 'classschedule-js' ) === $enqueued_scripts );
check( 'enqueues the schedule stylesheet after successful rendering', array( 'classschedule' ) === $enqueued_styles );
check( 'does not render the legacy WCSI mount node', false === strpos( $html, 'id="wcsi"' ) );
check( 'does not render the legacy WCSI host', false === strpos( $html, 'webapps.ucsc.edu' ) );

echo "default visible columns:\n";
check( 'emits data-default-columns="seats,days"', false !== strpos( $html, 'data-default-columns="seats,days"' ) );
check( 'Seats header is visible', false !== strpos( $html, 'col-seats is-sortable"' ) );
check( 'Time header is hidden', false !== strpos( $html, 'col-time is-sortable hidden' ) );
check( 'Class # header is hidden', false !== strpos( $html, 'col-class-num is-sortable hidden' ) );
check( 'Seats toggle is checked', false !== strpos( $html, 'data-column="seats" checked="checked"' ) );
check( 'Time toggle is unchecked', false === strpos( $html, 'data-column="time" checked="checked"' ) );

echo "editor-configured default columns:\n";
$html = render_schedule(
	array(
		'subjectOrDept'  => 'dept',
		'department'     => 'CSE',
		'defaultColumns' => array( 'class-num', 'time' ),
	),
	$terms,
	array( 'classes' => array( course_fixture() ) )
);
check( 'emits data-default-columns="class-num,time"', false !== strpos( $html, 'data-default-columns="class-num,time"' ) );
check( 'Class # header is visible', false !== strpos( $html, 'col-class-num is-sortable"' ) );
check( 'Time header is visible', false !== strpos( $html, 'col-time is-sortable"' ) );
check( 'Seats header is hidden', false !== strpos( $html, 'col-seats is-sortable hidden' ) );
check( 'Class # toggle is checked', false !== strpos( $html, 'data-column="class-num" checked="checked"' ) );
check( 'Seats toggle is unchecked', false === strpos( $html, 'data-column="seats" checked="checked"' ) );

echo "empty default columns:\n";
$html = render_schedule(
	array(
		'subjectOrDept'  => 'dept',
		'department'     => 'CSE',
		'defaultColumns' => array(),
	),
	$terms,
	array( 'classes' => array( course_fixture() ) )
);
check( 'emits empty data-default-columns', false !== strpos( $html, 'data-default-columns=""' ) );
check( 'Seats header is hidden when no defaults set', false !== strpos( $html, 'col-seats is-sortable hidden' ) );

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

echo "\n" . ( $tests - $failed ) . "/$tests passed\n";
exit( 0 === $failed ? 0 : 1 );
