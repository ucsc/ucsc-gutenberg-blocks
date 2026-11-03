<?php
/**
 * seed-campus-directory-rich-text-e2e.php - upsert the pages driven by
 * campus-directory-rich-text.spec.js.
 *
 * Writes fake people straight into the directory cache, so the pages render without
 * LDAP. The cache keys are built by hand because the wpcli container has no ldap
 * extension; they match buildUidFilter() for plain alphanumeric CruzIDs.
 *
 * Idempotent. Run in-container only:
 *   docker compose exec -T wpcli wp eval-file - < tests/e2e/seed-campus-directory-rich-text-e2e.php
 */

$ucsc_rich_person = array(
	'count'                        => 3,
	'uid'                          => array( 'count' => 1, 'cdrichtext' ),
	'cn'                           => array( 'count' => 1, 'Rich Text Person' ),
	'ucscpersonpubareaofexpertise' => array(
		'count' => 1,
		'<p>First <strong>bold</strong> <em>paragraph</em></p><p>Second paragraph</p><ul><li>One <a href="https://example.edu/lab">link</a> tail</li><li>Two</li></ul><ol><li>Three</li></ol>',
	),
);

$ucsc_rich_attack = array(
	'count'                        => 3,
	'uid'                          => array( 'count' => 1, 'cdrichattack' ),
	'cn'                           => array( 'count' => 1, 'Rich Attack Person' ),
	'ucscpersonpubareaofexpertise' => array(
		'count' => 1,
		'<a id="rich-overlay" href="https://example.com/x" style="position:fixed;inset:0;z-index:99999;background:red">cover</a><p style="position:fixed">styled</p><script>window.__richXss=1</script><img src=x onerror="window.__richXss=2"><a id="rich-js" href="javascript:window.__richXss=3">bad</a></div></div></div><strong>unclosed',
	),
);

foreach ( array( 'tiled', 'list', 'table' ) as $ucsc_layout ) {
	$ucsc_types = wp_json_encode( array( 'Summary of Expertise' => true ) );
	$ucsc_attrs = array(
		'pageLayout'               => $ucsc_layout,
		'automatedFeeds'           => false,
		'cruzidList'               => 'cdrichtext,cdrichattack',
		'linkToProfile'            => true,
		'linkOutToCampusDirectory' => false,
		'strInformationTypes'      => $ucsc_types,
		'strInformationTypesTable' => $ucsc_types,
	);
	$ucsc_slug = "campus-directory-rich-text-$ucsc_layout-e2e";
	$ucsc_page = array(
		'post_title'   => "Campus Directory rich text $ucsc_layout",
		'post_name'    => $ucsc_slug,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		// wp_insert_post unslashes, and the " escapes must survive.
		'post_content' => wp_slash( '<!-- wp:ucscblocks/campusdirectory ' . wp_json_encode( $ucsc_attrs, JSON_HEX_QUOT ) . ' /-->' ),
	);
	$ucsc_existing = get_page_by_path( $ucsc_slug );
	if ( $ucsc_existing ) {
		$ucsc_page['ID'] = $ucsc_existing->ID;
	}
	wp_insert_post( $ucsc_page );
	echo "/$ucsc_slug/\n";
}

set_transient( md5( '(|(uid=cdrichtext)(uid=cdrichattack))' ) . '_l', array( $ucsc_rich_person, $ucsc_rich_attack ), HOUR_IN_SECONDS );
set_transient( md5( '(uid=cdrichtext)' ) . '_p', array( $ucsc_rich_person ), HOUR_IN_SECONDS );
set_transient( md5( '(uid=cdrichattack)' ) . '_p', array( $ucsc_rich_attack ), HOUR_IN_SECONDS );
