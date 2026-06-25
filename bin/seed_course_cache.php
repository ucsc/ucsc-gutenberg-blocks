<?php
/**
 * seed_course_cache.php — seed the PeopleSoft term + course transients that
 * Course_Schedule_API reads, so the ClassSchedule block renders real course
 * rows offline (no live my.ucsc.edu feed, no UCSC VPN).
 *
 * Mirrors the exact cache contract in src/API/Course_Schedule_API.php:
 *   terms   -> transient 'ucsc_ps_terms'                     => [ 'terms'   => [...] ]
 *   courses -> transient 'ucsc_ps_courses_' . md5($term . $qs) => [ 'classes' => [...] ]
 * where $qs = http_build_query( [ 'dept' => strtoupper($dept) ] ) i.e. "dept=CSE".
 * get_courses() builds that same key from the block's dept attribute, so a seed
 * keyed identically is served from cache and the upstream fetch never runs.
 *
 * Defaults to term 2258 (Fall) / department CSE — matching bin/seed_demo_page.php.
 * Override via env before running:
 *   SEED_TERM=2258            (PeopleSoft term code; seeded term is marked default)
 *   SEED_TERM_DESC="2025 Fall Quarter"
 *   SEED_DEPARTMENT=CSE       (must match the block's department attribute)
 *
 * Idempotent: set_transient overwrites. Persistent (no expiration) so the demo
 * stays deterministic until `wp transient delete --all`. Prints a summary.
 *
 * Run in-container only:
 *   docker compose exec -T wpcli wp eval-file - < bin/seed_course_cache.php
 */

$term      = getenv( 'SEED_TERM' ) ?: '2258';
$term_desc = getenv( 'SEED_TERM_DESC' ) ?: '2025 Fall Quarter';
$dept      = strtoupper( getenv( 'SEED_DEPARTMENT' ) ?: 'CSE' );

// --- Terms transient -------------------------------------------------------
$terms_payload = array(
	'terms' => array(
		array(
			'code'        => $term,
			'description' => $term_desc,
			'default'     => 'Y',
		),
		array(
			'code'        => (string) ( (int) $term - 4 ), // prior quarter, non-default
			'description' => 'Prior Quarter',
			'default'     => 'N',
		),
	),
);
set_transient( 'ucsc_ps_terms', $terms_payload, 0 );

// --- Courses transient -----------------------------------------------------
// One row per status class the template renders (open / closed / waitlist), so
// verify can assert on each. Every field ClassScheduleTemplate.php reads is set
// to avoid PHP notices during render.
$mk = function ( $catalog_nbr, $class_nbr, $title, $cap, $total, $status, $days, $start, $end, $loc, $instr_name, $cruzid ) use ( $dept ) {
	return array(
		'subject'       => $dept,
		'catalog_nbr'   => (string) $catalog_nbr,
		'class_nbr'     => (string) $class_nbr,
		'title'         => $title,
		'enrl_capacity' => $cap,
		'enrl_total'    => $total,
		'enrl_status'   => $status,
		'meeting_days'  => $days,
		'start_time'    => $start,
		'end_time'      => $end,
		'location'      => $loc,
		'instructors'   => array(
			array( 'name' => $instr_name, 'cruzid' => $cruzid ),
		),
	);
};

$classes = array(
	$mk( '12',  20001, 'Computer Systems and Assembly Language',   120, 84,  'Open',      'MWF',  '10:40AM', '11:45AM', 'Thimann Lecture 003', 'Ada Lovelace',  'alovelac' ),
	$mk( '16',  20002, 'Applied Discrete Mathematics',             80,  80,  'Closed',    'TuTh', '1:30PM',  '3:05PM',  'Social Sciences 2 075', 'Alan Turing',  'aturing' ),
	$mk( '30',  20003, 'Programming Abstractions: Python',         150, 150, 'Wait List', 'MWF',  '9:20AM',  '10:25AM', 'Classroom Unit 002', 'Grace Hopper',  'ghopper' ),
	$mk( '101', 20004, 'Introduction to Data Structures',          90,  61,  'Open',      'TuTh', '11:40AM', '1:15PM',  'Engineering 2 192', 'Donald Knuth',   'dknuth' ),
	$mk( '120', 20005, 'Software Engineering',                     60,  44,  'Open',      'MWF',  '2:40PM',  '3:45PM',  'Baskin Engr 152', 'Staff',           '' ),
);

$qs        = http_build_query( array( 'dept' => $dept ) ); // "dept=CSE"
$cache_key = 'ucsc_ps_courses_' . md5( $term . $qs );
set_transient( $cache_key, array( 'classes' => $classes ), 0 );

printf(
	"seeded term=%s dept=%s courses=%d\n  terms transient: ucsc_ps_terms\n  courses transient: %s\n",
	$term,
	$dept,
	count( $classes ),
	$cache_key
);
