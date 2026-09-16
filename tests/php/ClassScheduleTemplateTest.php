<?php
/**
 * Dependency-free tests for ClassScheduleTemplate.php.
 *
 * WPM-119: render the template directly against fixture data and assert both
 * the ARIA-table structure the front-end depends on and that every interpolated
 * value is escaped before it reaches the markup. The template is normally reached
 * only through ClassSchedule::theHTML(); this suite requires and renders the
 * template file itself so its 189 lines get real executing coverage rather than
 * being credited to a comment match in a Jest test.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/ClassScheduleTemplateTest.php
 */

// esc_url in production strips javascript: URIs; mirror that so the escaping
// assertions exercise the same guard the front-end relies on.
function esc_url( $value ) {
	$value = esc_attr( $value );
	return preg_match( '/^javascript:/i', $value ) ? '' : $value;
}

// The template calls checked() for the column toggles; the shared harness does
// not stub it, so define our version before requiring the harness.
function checked( $checked, $current = true ) {
	if ( (string) $checked === (string) $current ) {
		echo 'checked="checked"';
	}
}

require __DIR__ . '/helpers/harness.php';

/**
 * Render ClassScheduleTemplate.php with the given variables in scope.
 * These are the exact locals ClassSchedule::theHTML() exposes to the template.
 */
function render_class_schedule_template( array $courses, $current_term, array $terms_data, array $attributes = array() ) {
	ob_start();
	include __DIR__ . '/../../templates/ClassScheduleTemplate.php';
	return ob_get_clean();
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

function terms_fixture() {
	return array(
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
}

echo "ClassScheduleTemplate rendering tests (WPM-119):\n\n";

// ---------------------------------------------------------------------------
// Table structure the front-end depends on
// ---------------------------------------------------------------------------
echo "table structure:\n";
$html = render_class_schedule_template(
	array(
		course_fixture( array( 'catalog_nbr' => '101', 'title' => 'Algorithms', 'class_nbr' => '12345' ) ),
		course_fixture( array( 'catalog_nbr' => '20', 'title' => 'Beginning Programming', 'class_nbr' => '54321' ) ),
	),
	'2262',
	terms_fixture(),
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE' )
);
check( 'renders the block wrapper', false !== strpos( $html, 'id="classSchedule"' ) );
check( 'renders the ARIA table mount node', false !== strpos( $html, 'id="classScheduleTable"' ) );
check( 'marks the table container with role="table"', false !== strpos( $html, 'class="el-table" id="classScheduleTable" role="table"' ) );
check( 'renders the header rowgroup', false !== strpos( $html, 'class="el-table__header" role="rowgroup"' ) );
check( 'renders the body rowgroup', false !== strpos( $html, 'class="el-table__body" role="rowgroup"' ) );
check( 'renders one course row per course', 2 === substr_count( $html, 'class="el-table__row course-row"' ) );
check( 'renders the Course ID column header', false !== strpos( $html, '>Course ID<' ) );
check( 'renders the Title column header', false !== strpos( $html, '>Title<' ) );
check( 'renders the filter modal dialog', false !== strpos( $html, 'id="filterModal"' ) );
check( 'renders the displayed-class count from the course array', false !== strpos( $html, 'Displaying <strong>2</strong> classes' ) );

echo "\nterm dropdown:\n";
check( 'renders an option for every term', 2 === substr_count( $html, '<option value=' ) );
check( 'renders the term description text', false !== strpos( $html, 'Fall 2026' ) );
check( 'marks the current term selected', false !== strpos( $html, 'value="2262"' . "\n              " . 'selected="selected"' ) );

echo "\ndefault columns:\n";
check( 'emits the default seats+days columns attribute', false !== strpos( $html, 'data-default-columns="seats,days"' ) );
check( 'shows the Seats column by default', false !== strpos( $html, 'col-seats is-sortable"' ) );
check( 'hides the Time column by default', false !== strpos( $html, 'col-time is-sortable hidden' ) );
check( 'checks the seats toggle for a default column', false !== strpos( $html, 'data-column="seats" checked="checked"' ) );
check( 'leaves the time toggle unchecked for a hidden column', false === strpos( $html, 'data-column="time" checked="checked"' ) );

$html_cols = render_class_schedule_template(
	array( course_fixture() ),
	'2262',
	terms_fixture(),
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE', 'defaultColumns' => array( 'class-num', 'time' ) )
);
check( 'honors editor-configured default columns', false !== strpos( $html_cols, 'data-default-columns="class-num,time"' ) );
check( 'shows an editor-enabled column', false !== strpos( $html_cols, 'col-time is-sortable"' ) );
check( 'hides a column not in the editor defaults', false !== strpos( $html_cols, 'col-seats is-sortable hidden' ) );

$html_bad_cols = render_class_schedule_template(
	array( course_fixture() ),
	'2262',
	terms_fixture(),
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE', 'defaultColumns' => 'seats' )
);
check( 'falls back to seats+days when defaultColumns is not an array', false !== strpos( $html_bad_cols, 'data-default-columns="seats,days"' ) );

echo "\ncell content and status:\n";
check( 'renders the course id in the row', false !== strpos( $html, '<span>CSE-101</span>' ) );
check( 'links the title to the course detail route', false !== strpos( $html, 'https://example.ucsc.edu/course/2262/54321' ) );
check( 'renders open seats and total', false !== strpos( $html, '10 open' ) && false !== strpos( $html, '/ 100 total' ) );
check( 'links instructors with a cruzid to the directory', false !== strpos( $html, 'https://example.ucsc.edu/directory/alovelace' ) );

$html_states = render_class_schedule_template(
	array(
		course_fixture( array( 'enrl_status' => 'Open' ) ),
		course_fixture( array( 'enrl_status' => 'Closed' ) ),
		course_fixture( array( 'enrl_status' => 'Closed with Wait List' ) ),
		course_fixture( array( 'meeting_days' => 'Cancelled', 'enrl_capacity' => 30, 'enrl_total' => 45 ) ),
	),
	'2262',
	terms_fixture(),
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE' )
);
check( 'maps Open status to the open row', false !== strpos( $html_states, 'data-status="open"' ) );
check( 'maps Closed status to the closed row', false !== strpos( $html_states, 'data-status="closed"' ) );
check( 'maps wait list status to waitlist (wait wins over closed)', false !== strpos( $html_states, 'data-status="waitlist"' ) );
check( 'labels the wait list status icon for screen readers', false !== strpos( $html_states, 'aria-label="Closed with Wait List"' ) );
check( 'flags cancelled courses on the title link', false !== strpos( $html_states, 'class="cancelled"' ) );
check( 'shows Cancelled in the days column for cancelled courses', false !== strpos( $html_states, '<span>Cancelled</span>' ) );
check( 'clamps open seats at zero when over-enrolled', false !== strpos( $html_states, '0 open' ) );

echo "\ninstructor edge cases:\n";
$html_inst = render_class_schedule_template(
	array(
		course_fixture(
			array(
				'instructors' => array(
					array( 'name' => 'Ada Lovelace', 'cruzid' => 'alovelace' ),
					array( 'name' => 'Grace Hopper', 'cruzid' => '' ),
					array( 'name' => 'Staff', 'cruzid' => 'staff' ),
					array( 'name' => '', 'cruzid' => 'ghost' ),
				),
			)
		),
	),
	'2262',
	terms_fixture(),
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE' )
);
check( 'joins multiple instructors with a comma', false !== strpos( $html_inst, '</a>, Grace Hopper' ) );
check( 'renders an instructor without a cruzid as plain text', false === strpos( $html_inst, '/directory/ghopper' ) && false !== strpos( $html_inst, 'Grace Hopper' ) );
check( 'does not link Staff instructors even with a cruzid', false === strpos( $html_inst, '/directory/staff' ) );
check( 'skips instructors with an empty name', false === strpos( $html_inst, '/directory/ghost' ) );

echo "\nempty course list:\n";
$html_empty = render_class_schedule_template(
	array(),
	'2262',
	terms_fixture(),
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE' )
);
check( 'renders the table shell with no rows for an empty course list', false !== strpos( $html_empty, 'id="classScheduleTable"' ) && false === strpos( $html_empty, 'course-row' ) );
check( 'reports a zero class count for an empty course list', false !== strpos( $html_empty, 'Displaying <strong>0</strong> classes' ) );

// ---------------------------------------------------------------------------
// Escaping of every interpolated value
// ---------------------------------------------------------------------------
echo "\nescaping interpolated course values:\n";
$attack_text = '<script>alert(1)</script>';
$attack_attr = '"><img src=x onerror=alert(1)>';
$html_xss = render_class_schedule_template(
	array(
		course_fixture(
			array(
				'subject'      => $attack_text,
				'catalog_nbr'  => $attack_attr,
				'title'        => $attack_text,
				'class_nbr'    => $attack_attr,
				'meeting_days' => $attack_text,
				'start_time'   => $attack_text,
				'end_time'     => $attack_attr,
				'location'     => $attack_text,
				'enrl_status'  => $attack_text,
				'instructors'  => array(
					array( 'name' => $attack_text, 'cruzid' => $attack_attr ),
				),
			)
		),
	),
	'2262',
	terms_fixture(),
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE' )
);
check( 'does not emit a raw script tag from any course field', false === strpos( $html_xss, '<script>alert(1)</script>' ) );
check( 'does not emit a raw image event handler from any course field', false === strpos( $html_xss, '<img src=x onerror=alert(1)>' ) );
check( 'escapes the course title', false !== strpos( $html_xss, '&lt;script&gt;alert(1)&lt;/script&gt;' ) );
check( 'escapes the instructor name', substr_count( $html_xss, '&lt;script&gt;alert(1)&lt;/script&gt;' ) >= 2 );
check( 'escapes attribute-breaking values', false !== strpos( $html_xss, '&quot;&gt;&lt;img src=x onerror=alert(1)&gt;' ) );
check( 'escapes the instructor cruzid inside the directory href', false === strpos( $html_xss, '/directory/"><img' ) );

echo "\nescaping term and status values:\n";
$html_term_xss = render_class_schedule_template(
	array( course_fixture( array( 'enrl_status' => $attack_attr ) ) ),
	$attack_attr,
	array(
		'terms' => array(
			array( 'code' => $attack_attr, 'description' => $attack_text, 'default' => 'Y' ),
		),
	),
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE' )
);
check( 'escapes the term option value attribute', false === strpos( $html_term_xss, 'value="">' ) );
check( 'escapes the term description text', false !== strpos( $html_term_xss, '&lt;script&gt;alert(1)&lt;/script&gt;' ) );
check( 'escapes the current_term interpolated into the course href', false === strpos( $html_term_xss, '/course/"><img' ) );

echo "\nescaping the default-columns attribute:\n";
$html_attr_xss = render_class_schedule_template(
	array( course_fixture() ),
	'2262',
	terms_fixture(),
	array( 'subjectOrDept' => 'dept', 'department' => 'CSE', 'defaultColumns' => array( 'seats', $attack_attr ) )
);
// The unknown column is filtered out by array_intersect, so the attribute is safe
// by construction; assert no attribute-breaking payload survives regardless.
check( 'never emits an attribute-breaking default-columns value', false === strpos( $html_attr_xss, 'data-default-columns="seats,"><img' ) );

finish_tests();
