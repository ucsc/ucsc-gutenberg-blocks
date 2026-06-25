<?php
/**
 * seed_demo_page.php — upsert a "Class Schedule Demo" page that contains a
 * configured `ucscblocks/classschedule` block, so the dynamic block can be
 * driven/verified on a single deterministic frontend URL.
 *
 * The block is server-rendered (ClassSchedule::theHTML) from live PeopleSoft
 * data, so the page is seeded with sensible defaults (department = CSE, the
 * code most exercised by the test fixtures). Override via env before running:
 *   SEED_SUBJECT_OR_DEPT=dept|subject   (default: dept)
 *   SEED_DEPARTMENT=CSE                  (used when subjectOrDept = dept)
 *   SEED_SUBJECT=LIT                     (used when subjectOrDept = subject)
 *
 * Idempotent: keyed on the page slug, it updates in place on re-run. Prints the
 * permalink on success.
 *
 * Run in-container only (host PHP is not guaranteed):
 *   docker compose exec -T wpcli wp eval-file - < bin/seed_demo_page.php
 */

$slug = 'class-schedule-demo';

$subject_or_dept = getenv( 'SEED_SUBJECT_OR_DEPT' ) ?: 'dept';
$department      = getenv( 'SEED_DEPARTMENT' ) ?: 'CSE';
$subject         = getenv( 'SEED_SUBJECT' ) ?: 'LIT';

$attrs = array( 'subjectOrDept' => $subject_or_dept );
if ( 'subject' === $subject_or_dept ) {
	$attrs['subject'] = $subject;
} else {
	$attrs['department'] = $department;
}

$block_comment = '<!-- wp:ucscblocks/classschedule ' . wp_json_encode( $attrs ) . ' /-->';

$content = "<!-- wp:heading --><h2>Class Schedule Demo</h2><!-- /wp:heading -->\n\n"
	. $block_comment . "\n";

$postarr = array(
	'post_title'   => 'Class Schedule Demo',
	'post_name'    => $slug,
	'post_status'  => 'publish',
	'post_type'    => 'page',
	'post_content' => $content,
);

$existing = get_page_by_path( $slug, OBJECT, 'page' );
if ( $existing ) {
	$postarr['ID'] = $existing->ID;
	$id            = wp_update_post( $postarr, true );
} else {
	$id = wp_insert_post( $postarr, true );
}

if ( is_wp_error( $id ) ) {
	fwrite( STDERR, 'seed_demo_page: ' . $id->get_error_message() . "\n" );
	exit( 1 );
}

echo get_permalink( $id ), "\n";
