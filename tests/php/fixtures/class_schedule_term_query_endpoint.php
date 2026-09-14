<?php
/**
 * WPM-158: HTTP fixture for exercising the real $_GET / filter_input(INPUT_GET)
 * superglobal path in ClassSchedule::theHTML().
 *
 * classes/ClassSchedule.php reads the query-param term override via
 * filter_input(INPUT_GET, ...), which only reflects the real request
 * superglobals under PHP's built-in web server (or a true SAPI request) —
 * assigning $_GET directly in a CLI test process does not populate it.
 * This script is served by `php -S` from ClassScheduleQueryTermTest.php so
 * the query string on the actual HTTP request drives the override.
 *
 * Two fixed terms are available: 2258 (default) and 2262 (non-default).
 */

function rest_do_request( $request ) {
	if ( '/ucsc/v1/terms' === $request->route ) {
		return new Class_Schedule_Fixture_Response(
			array(
				'terms' => array(
					array( 'code' => '2258', 'description' => 'Fall 2025', 'default' => 'Y' ),
					array( 'code' => '2262', 'description' => 'Winter 2026', 'default' => 'N' ),
				),
			)
		);
	}

	if ( 0 === strpos( $request->route, '/ucsc/v1/courses/' ) ) {
		$term = str_replace( '/ucsc/v1/courses/', '', $request->route );
		return new Class_Schedule_Fixture_Response(
			array(
				'classes' => array(
					array(
						'subject'     => 'CSE',
						'catalog_nbr' => '101',
						'title'       => 'Algorithms (term ' . $term . ')',
						'class_nbr'   => '12345',
						'meetings'    => array(),
					),
				),
			)
		);
	}

	return new WP_Error( 'not_found', 'no fixture route' );
}

class Class_Schedule_Fixture_Response {
	private $data;
	public function __construct( $data ) {
		$this->data = $data;
	}
	public function get_data() {
		return $this->data;
	}
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

function wp_enqueue_script( $handle ) {}
function wp_enqueue_style( $handle ) {}
function get_query_var( $var ) { return ''; }

if ( ! function_exists( 'checked' ) ) {
	function checked( $checked, $current = true ) {
		if ( (string) $checked === (string) $current ) {
			echo 'checked="checked"';
		}
	}
}

require __DIR__ . '/../../../classes/ClassSchedule.php';
require __DIR__ . '/../helpers/harness.php';

$class_schedule = new ClassSchedule();
echo $class_schedule->theHTML( array( 'subjectOrDept' => 'dept', 'department' => 'CSE' ) );
