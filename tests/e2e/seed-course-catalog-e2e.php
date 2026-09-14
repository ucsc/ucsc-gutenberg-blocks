<?php
/**
 * seed-course-catalog-e2e.php — upsert the page driven by
 * course-catalog.spec.js: a single ucscblocks/coursecatalog block pinned to
 * a department so the table renders real course rows. Idempotent; prints
 * the permalink.
 *
 * Run in-container only (host PHP is not guaranteed):
 *   docker compose exec -T wpcli wp eval-file - < tests/e2e/seed-course-catalog-e2e.php
 */

$slug    = 'course-catalog-e2e';
$content = "<!-- wp:ucscblocks/coursecatalog {\"subjectOrDept\":\"dept\",\"department\":\"CSE\"} /-->\n";

$postarr = array(
	'post_title'   => 'Course Catalog E2E',
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
	fwrite( STDERR, 'seed-course-catalog-e2e: ' . $id->get_error_message() . "\n" );
	exit( 1 );
}

echo get_permalink( $id ), "\n";
