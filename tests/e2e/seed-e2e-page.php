<?php
/**
 * seed-e2e-page.php — upsert the page driven by class-schedule.spec.js: a
 * single ucscblocks/classschedule block pinned to a department so the table
 * renders real course rows. Idempotent; prints the permalink.
 *
 * Run in-container only (host PHP is not guaranteed):
 *   docker compose exec -T wpcli wp eval-file - < tests/e2e/seed-e2e-page.php
 */

$slug    = 'class-schedule-e2e';
$content = "<!-- wp:ucscblocks/classschedule {\"subjectOrDept\":\"dept\",\"department\":\"CSE\"} /-->\n";

$postarr = array(
	'post_title'   => 'Class Schedule E2E',
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
	fwrite( STDERR, 'seed-e2e-page: ' . $id->get_error_message() . "\n" );
	exit( 1 );
}

echo get_permalink( $id ), "\n";
