<?php
/**
 * Dependency-free tests for CourseDetailTemplate.php.
 *
 * WPM-181: the class-schedule course detail page (/course/{term}/{class_nbr}/)
 * was only covered by a path assertion in ClassScheduleTest.php. This suite
 * renders the template itself against fixture course data and asserts the
 * behaviour in openspec/specs/class-schedule/course-detail-pages/spec.md:
 * failure messages, status, seat clamping, meeting aggregation, TBA handling,
 * instructor linking, associated-section dedup, breadcrumb referer fallback,
 * and escaping of every interpolated upstream value.
 *
 * The template calls exit on its failure paths, so each scenario renders in a
 * child PHP process (this same file, invoked with --render <scenario>). Under
 * UCSC_COVERAGE the child emits its own coverage on shutdown.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/CourseDetailTemplateTest.php
 */

// esc_url in production strips javascript: URIs; mirror that so the escaping
// assertions exercise the same guard the page relies on.
function esc_url( $value ) {
	$value = esc_attr( $value );
	return preg_match( '/^javascript:/i', $value ) ? '' : $value;
}

require __DIR__ . '/helpers/harness.php';

function course_detail_primary_fixture( $overrides = array() ) {
	return array_merge(
		array(
			'subject'       => 'CSE',
			'catalog_nbr'   => '101',
			'class_section' => '01',
			'class_nbr'     => '50222',
			'title_long'    => 'Algorithms <script>alert("title")</script>',
			'enrl_status'   => 'Wait List',
			'capacity'      => '50',
			'enrl_total'    => '60',
			'description'   => "Line one & <b>bold</b>\nLine two",
			'gened'         => 'MF',
			'requirements'  => 'Prerequisite(s): CSE 30 <img src=x onerror=alert(1)>',
			'acad_career'   => 'Undergraduate',
			'grading'       => 'Letter Grade',
			'component'     => 'Lecture',
			'credits'       => '5',
			'start_date'    => '2026-03-30',
			'end_date'      => '2026-06-05',
		),
		$overrides
	);
}

/**
 * Course payloads keyed by scenario, as rest_do_request() would return them.
 * A WP_Error value models a failed REST call.
 */
function course_detail_scenarios() {
	return array(
		'full'        => array(
			'term'    => '2262',
			'referer' => 'https://example.ucsc.edu/class-schedule/?term=2262',
			'data'    => array(
				'primary_section'    => course_detail_primary_fixture(),
				'notes'              => array( 'Bring a laptop', 'No <em>auditors</em>' ),
				'meetings'           => array(
					array(
						'days'        => 'MWF',
						'start_time'  => '10:40',
						'end_time'    => '11:45',
						'location'    => 'Engineering 2 192',
						'instructors' => array(
							array( 'name' => 'Ada Lovelace', 'cruzid' => 'alovelace' ),
							array( 'name' => 'Staff', 'cruzid' => 'staff' ),
						),
					),
					array(
						'days'        => 'Tu',
						'start_time'  => 'tba',
						'end_time'    => 'TBA',
						'location'    => 'Engineering 2 192',
						'instructors' => array(
							array( 'name' => 'Ada Lovelace', 'cruzid' => 'other' ),
							array( 'name' => 'Grace <i>Hopper</i>', 'cruzid' => '"><script>x</script>' ),
						),
					),
				),
				'secondary_sections' => array(
					'secondary_section' => array(
						array( 'class_section' => '01', 'component' => 'DUPLICATE-OF-PRIMARY' ),
						array(
							'class_section' => '01A',
							'component'     => 'Discussion',
							'class_nbr'     => '50223',
							'capacity'      => '25',
							'enrl_total'    => '20',
							'meetings'      => array(
								'meeting' => array(
									'days'        => 'W',
									'start_time'  => '13:20',
									'end_time'    => '14:25',
									'location'    => 'Soc Sci 1 <u>145</u>',
									'instructors' => array(
										'instructor' => array( 'name' => 'Bob <b>TA</b>', 'cruzid' => 'bta' ),
									),
								),
							),
						),
						array(
							'class_section' => '01B',
							'component'     => 'Laboratory',
							'meetings'      => array(
								array(
									'days'        => 'Th',
									'start_time'  => 'TBA',
									'end_time'    => 'TBA',
									'instructors' => array(
										array( 'name' => 'Pat Lab', 'cruzid' => 'plab' ),
										array( 'name' => 'Pat Lab', 'cruzid' => 'plab2' ),
										array( 'name' => 'Staff' ),
									),
								),
							),
						),
					),
				),
			),
		),
		'minimal'     => array(
			'term'    => '2260',
			'referer' => 'https://evil.example.com/class-schedule/',
			'data'    => array(
				'primary_section' => array(
					'subject'       => 'MATH',
					'catalog_nbr'   => '19A',
					'class_section' => '02',
					'title_long'    => 'Calculus',
					'enrl_status'   => 'Closed',
					'capacity'      => '30',
					'enrl_total'    => '10',
					'start_date'    => '',
					'end_date'      => '',
				),
			),
		),
		'open'        => array(
			'term'    => '2268',
			'referer' => '',
			'data'    => array(
				'primary_section' => course_detail_primary_fixture( array( 'enrl_status' => 'Open' ) ),
			),
		),
		'rest-error'  => array(
			'term' => '2262',
			'data' => new WP_Error( 'rest_error', 'upstream down' ),
		),
		'empty'       => array(
			'term' => '2262',
			'data' => array(),
		),
		'no-primary'  => array(
			'term' => '2262',
			'data' => array( 'meetings' => array() ),
		),
	);
}

// ── Child mode: render one scenario and exit ────────────────────────────────

if ( isset( $argv[1] ) && '--render' === $argv[1] ) {
	$scenario = course_detail_scenarios()[ $argv[2] ];

	// Stubs for the WordPress calls the template makes.
	class WP_REST_Request {
		public function __construct( $method, $route ) {
			$GLOBALS['course_detail_route'] = $route;
		}
	}
	class Course_Detail_Response {
		private $data;
		public function __construct( $data ) {
			$this->data = $data;
		}
		public function get_data() {
			return $this->data;
		}
	}
	function rest_do_request( $request ) {
		global $scenario;
		return is_wp_error( $scenario['data'] ) ? $scenario['data'] : new Course_Detail_Response( $scenario['data'] );
	}
	function get_query_var( $var ) {
		global $scenario;
		return 'course_term' === $var ? $scenario['term'] : '50222';
	}
	function get_theme_file_path( $file ) {
		return '/nonexistent/' . $file;
	}
	function get_header() {
		echo "<!--header-->\n";
	}
	function get_footer() {
		echo "<!--footer-->\n";
	}
	function wp_enqueue_style() {}
	function plugins_url( $path ) {
		return 'https://example.ucsc.edu/wp-content/plugins/' . $path;
	}
	function wp_get_referer() {
		global $scenario;
		return $scenario['referer'] ?? false;
	}
	function wp_validate_redirect( $location, $fallback ) {
		return $location;
	}

	// exit inside the template bypasses finish_tests(), so emit coverage here.
	register_shutdown_function(
		function () {
			global $ucsc_coverage;
			if ( $ucsc_coverage && function_exists( 'xdebug_get_code_coverage' ) ) {
				ucsc_emit_coverage( xdebug_get_code_coverage(), $ucsc_coverage );
			}
			echo "\n<!--route:" . ( $GLOBALS['course_detail_route'] ?? '' ) . '-->';
		}
	);

	include __DIR__ . '/../../templates/CourseDetailTemplate.php';
	exit( 0 );
}

// ── Parent mode: render each scenario in a child and assert ─────────────────

function render_course_detail( $scenario ) {
	$cmd = escapeshellarg( PHP_BINARY ) . ' ' . escapeshellarg( __FILE__ ) . ' --render ' . escapeshellarg( $scenario ) . ' 2>&1';
	return (string) shell_exec( $cmd );
}

/**
 * Text of the <dd> that follows the <dt> with the given label, or null.
 */
function dd_after( $html, $label, $offset = 0 ) {
	$pos = strpos( $html, '<dt>' . $label . '</dt>', $offset );
	if ( false === $pos ) {
		return null;
	}
	preg_match( '#<dd>(.*?)</dd>#s', $html, $m, 0, $pos );
	return isset( $m[1] ) ? trim( $m[1] ) : null;
}

echo "failure messages:\n";

$html = render_course_detail( 'rest-error' );
check( 'a failed REST call shows the load-error message', false !== strpos( $html, 'Error loading course details. Please try again later.' ) );
check( 'a failed REST call renders no course markup', false === strpos( $html, '<main' ) );
check( 'a failed REST call still closes the page with the footer', false !== strpos( $html, '<!--footer-->' ) );

$html = render_course_detail( 'empty' );
check( 'an empty response shows the not-found message', false !== strpos( $html, 'Course not found.' ) && false === strpos( $html, '<main' ) );

$html = render_course_detail( 'no-primary' );
check( 'a response with no primary section shows the unavailable message', false !== strpos( $html, 'Course information unavailable.' ) && false === strpos( $html, '<main' ) );

echo "\nfull course page:\n";

$html = render_course_detail( 'full' );
check( 'no PHP warnings or notices while rendering', false === stripos( $html, 'Warning:' ) && false === stripos( $html, 'Notice:' ) && false === stripos( $html, 'Deprecated:' ) );
check( 'requests the course from /ucsc/v1/course/{term}/{id}', false !== strpos( $html, '<!--route:/ucsc/v1/course/2262/50222-->' ) );
check( 'renders the page inside <main> between header and footer', false !== strpos( $html, '<!--header-->' ) && false !== strpos( $html, '<main' ) && false !== strpos( $html, '<!--footer-->' ) );
check( 'shows the course identifier as subject catalog-section', 1 === substr_count( $html, '<span>CSE 101-01</span>' ) );
check( 'maps term code 2262 to Spring 2026', false !== strpos( $html, '<span>Spring 2026</span>' ) );
check( 'shows the wait-list status as both indicator class and text', false !== strpos( $html, '<i class="waitlist" aria-hidden="true"></i>' ) && false !== strpos( $html, '<span>Wait List</span>' ) );
check( 'escapes the course title', false !== strpos( $html, 'Algorithms &lt;script&gt;alert(&quot;title&quot;)&lt;/script&gt;' ) );
check( 'escapes the description and keeps its line breaks', false !== strpos( $html, 'Line one &amp; &lt;b&gt;bold&lt;/b&gt;<br />' ) );
check( 'shows the general education code', false !== strpos( $html, '(General Education Code(s): MF.)' ) );
check( 'escapes enrollment requirements', false !== strpos( $html, '&lt;img src=x onerror=alert(1)&gt;' ) );
check( 'joins notes into one escaped class-notes block', false !== strpos( $html, 'Bring a laptop. No &lt;em&gt;auditors&lt;/em&gt;.' ) );
check( 'available seats clamp to 0 when enrolled exceeds capacity', '0' === dd_after( $html, 'Available Seats' ) );
check( 'shows enrollment capacity and enrolled count', '50' === dd_after( $html, 'Enrollment Capacity' ) && '60' === dd_after( $html, 'Enrolled' ) );
check( 'shows class details', 'Undergraduate' === dd_after( $html, 'Career' ) && 'Letter Grade' === dd_after( $html, 'Grading' ) && '50222' === dd_after( $html, 'Class Number' ) && '5' === dd_after( $html, 'Credits' ) );

echo "\nmeeting information:\n";

check( 'joins meeting patterns with a semicolon and formats clock times', 'MWF 10:40 AM - 11:45 AM; Tu TBA' === dd_after( $html, 'Days &amp; Times' ) );
check( 'deduplicates meeting locations', 'Engineering 2 192' === dd_after( $html, 'Room' ) );
check( 'shows meeting dates as n/j/Y - n/j/Y', '3/30/2026 - 6/5/2026' === dd_after( $html, 'Meeting Dates' ) );

$instructors = dd_after( $html, 'Instructor' );
check( 'links an instructor with a CruzID to their directory profile', false !== strpos( (string) $instructors, '<a href="https://example.ucsc.edu/directory/alovelace">Ada Lovelace</a>' ) );
check( 'renders Staff as plain text, not a link', false !== strpos( (string) $instructors, ', Staff' ) && false === strpos( (string) $instructors, '/directory/staff' ) );
check( 'deduplicates primary-section instructors by name alone', 1 === substr_count( (string) $instructors, 'Ada Lovelace' ) && false === strpos( (string) $instructors, '/directory/other' ) );
check( 'escapes instructor names and CruzIDs in profile links', false !== strpos( (string) $instructors, 'Grace &lt;i&gt;Hopper&lt;/i&gt;' ) && false !== strpos( (string) $instructors, '/directory/&quot;&gt;&lt;script&gt;x&lt;/script&gt;' ) );

echo "\nassociated sections:\n";

check( 'renders the associated-sections heading', false !== strpos( $html, 'Associated Discussion Sections or Labs' ) );
check( 'renders one block per non-primary section', 2 === substr_count( $html, '<div class="class-section">' ) );
check( 'skips a secondary section that duplicates the primary', false === strpos( $html, 'DUPLICATE-OF-PRIMARY' ) );

$disc = strpos( $html, '<dd>01A</dd>' );
check( 'unwraps a single meeting and single instructor on a section', false !== $disc && 'W' === dd_after( $html, 'Days', $disc ) && '1:20 PM - 2:25 PM' === dd_after( $html, 'Time', $disc ) );
check( 'escapes section location and instructor', 'Soc Sci 1 &lt;u&gt;145&lt;/u&gt;' === dd_after( $html, 'Location', $disc ) && '<a href="https://example.ucsc.edu/directory/bta">Bob &lt;b&gt;TA&lt;/b&gt;</a>' === dd_after( $html, 'Instructor', $disc ) );
check( 'shows section capacity and enrolled', '25' === dd_after( $html, 'Enrollment Capacity', $disc ) && '20' === dd_after( $html, 'Enrolled', $disc ) );

$lab = strpos( $html, '<dd>01B</dd>' );
check( 'a TBA section time renders as TBA', false !== $lab && 'TBA' === dd_after( $html, 'Time', $lab ) );
check( 'deduplicates section instructors by name and CruzID', '<a href="https://example.ucsc.edu/directory/plab">Pat Lab</a>, <a href="https://example.ucsc.edu/directory/plab2">Pat Lab</a>, Staff' === dd_after( $html, 'Instructor', $lab ) );
check( 'omits section fields absent from upstream data', false === strpos( substr( $html, $lab ), '<dt>Location</dt>' ) );

echo "\nbreadcrumb:\n";

check( 'breadcrumb names the course', false !== strpos( $html, '<span itemprop="name">CSE 101-01</span>' ) );
check( 'breadcrumb links back to a same-site referer', false !== strpos( $html, '<a href="https://example.ucsc.edu/class-schedule/?term=2262" itemprop="item"><span itemprop="name">Class Schedule</span>' ) );

$html = render_course_detail( 'minimal' );
check( 'breadcrumb falls back to home for an off-site referer', false !== strpos( $html, '<a href="https://example.ucsc.edu/" itemprop="item"><span itemprop="name">Class Schedule</span>' ) && false === strpos( $html, 'evil.example.com' ) );

$html_open = render_course_detail( 'open' );
check( 'breadcrumb falls back to home with no referer', false !== strpos( $html_open, '<a href="https://example.ucsc.edu/" itemprop="item"><span itemprop="name">Class Schedule</span>' ) );

echo "\nminimal course page:\n";

check( 'no PHP warnings or notices on a sparse payload', false === stripos( $html, 'Warning:' ) && false === stripos( $html, 'Notice:' ) && false === stripos( $html, 'Deprecated:' ) );
check( 'maps term code 2260 to Winter 2026', false !== strpos( $html, '<span>Winter 2026</span>' ) );
check( 'shows the closed status indicator', false !== strpos( $html, '<i class="closed" aria-hidden="true"></i>' ) );
check( 'computes available seats as capacity minus enrolled', '20' === dd_after( $html, 'Available Seats' ) );
check( 'omits optional sections with no data', false === strpos( $html, '>Description</h2>' ) && false === strpos( $html, 'Meeting Information' ) && false === strpos( $html, 'Associated Discussion' ) && false === strpos( $html, 'Class Notes' ) );

check( 'maps term code 2268 to Fall 2026', false !== strpos( $html_open, '<span>Fall 2026</span>' ) );
check( 'shows the open status indicator', false !== strpos( $html_open, '<i class="open" aria-hidden="true"></i>' ) );

finish_tests();
