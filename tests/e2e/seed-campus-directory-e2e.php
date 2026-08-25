<?php
/**
 * seed-campus-directory-e2e.php - upsert the pages driven by
 * campus-directory-profile.spec.js.
 *
 * This creates two named local page shapes from the production incident:
 *
 * - LALS format: `/lals/people/faculty/`, where names link out to
 *   campusdirectory.ucsc.edu.
 * - EEB format: `/eeb/people/`, where names stay in-site via
 *   `?directoryprofilecruzid=<cruzid>`.
 *
 * This matters: the LALS-focused WPM-99 fix changed rendering globally, which
 * regressed the EEB-style in-site profile route.
 *
 * The block markup below is copied verbatim from the working
 * `campus-directory-page` fixture with only `linkOutToCampusDirectory` flipped,
 * so the nested-JSON attribute escaping (\u0022) matches what the editor emits.
 * Re-encoding these attributes by hand does not round-trip correctly.
 *
 * Idempotent; prints `LALS_URL=...` and `EEB_URL=...` for run-e2e.sh.
 *
 * Run in-container only (host PHP is not guaranteed):
 *   docker compose exec -T wpcli wp eval-file - < tests/e2e/seed-campus-directory-e2e.php
 */

$eeb_content = <<<'BLOCK'
<!-- wp:ucscblocks/campusdirectory {"pageLayout":"tiled","automatedFeeds":true,"cruzidList":"","strFacultyTypes":"{\u0022All\u0022:false,\u0022Regular Faculty\u0022:false,\u0022Lecturer\u0022:false,\u0022Emeriti\u0022:false,\u0022Research Professor\u0022:false,\u0022Researcher\u0022:false,\u0022Adjunct Faculty\u0022:false,\u0022Visiting Scholar\u0022:false,\u0022Graduate Student Instructor\u0022:false,\u0022Retired\u0022:false}","strStaffTypes":"{\u0022Regular Staff\u0022:false,\u0022Researcher\u0022:false,\u0022Postdoctoral Scholar\u0022:false}","strGradTypes":"{\u0022Grad Students\u0022:true}","manualAdd":false,"addCruzids":"","excludeCruzids":"","displayDeptartmentAffiliates":false,"linkToProfile":true,"linkOutToCampusDirectory":false,"strInformationTypes":"{\u0022Pronouns\u0022:false,\u0022Photo\u0022:true,\u0022Title\u0022:false,\u0022Department\u0022:true,\u0022Phone\u0022:true,\u0022Campus Email\u0022:true,\u0022Other Email\u0022:false,\u0022Fax\u0022:false,\u0022Website\u0022:true,\u0022Office Location\u0022:true,\u0022Office Hours\u0022:true,\u0022Mailstop\u0022:false,\u0022Mailing Address\u0022:false,\u0022Faculty Areas of Expertise\u0022:false,\u0022Summary of Expertise\u0022:false}","strInformationTypesTable":"{\u0022Pronouns\u0022:false,\u0022Title\u0022:true,\u0022Department\u0022:true,\u0022Phone\u0022:true,\u0022Campus Email\u0022:true,\u0022Other Email\u0022:false,\u0022Fax\u0022:false,\u0022Website\u0022:false,\u0022Office Location\u0022:false,\u0022Office Hours\u0022:false,\u0022Mailstop\u0022:false,\u0022Mailing Address\u0022:false,\u0022Faculty Areas of Expertise\u0022:false,\u0022Summary of Expertise\u0022:false}","department":"History Department","division":"\u002d\u002d-","deptOrDiv":"dept"} /-->
BLOCK;

$lals_content = str_replace(
	'"linkOutToCampusDirectory":false',
	'"linkOutToCampusDirectory":true',
	$eeb_content
);

$lals_home_content = <<<'BLOCK'
<!-- wp:paragraph -->
<p><a href="/lals/people/faculty/">Campus Directory LALS Faculty</a></p>
<!-- /wp:paragraph -->
BLOCK;

$lals_people_content = <<<'BLOCK'
<!-- wp:paragraph -->
<p><a href="/lals/people/faculty/">Campus Directory LALS Faculty</a></p>
<!-- /wp:paragraph -->
BLOCK;

$eeb_home_content = <<<'BLOCK'
<!-- wp:paragraph -->
<p><a href="/eeb/people/">Campus Directory EEB People</a></p>
<!-- /wp:paragraph -->
BLOCK;

function ucsc_seed_page( $slug, $title, $content = '', $parent_id = 0 ) {
	$postarr = array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_parent'  => $parent_id,
		// wp_insert_post() runs wp_unslash() on its input, which would strip the
		// backslashes out of the \u0022 escapes and corrupt the nested JSON
		// attributes (strStaffTypes then decodes to null and fatals). Slash first.
		'post_content' => wp_slash( $content . "\n" ),
	);

	$path     = $parent_id ? trim( get_page_uri( $parent_id ) . '/' . $slug, '/' ) : $slug;
	$existing = get_page_by_path( $path, OBJECT, 'page' );
	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$id            = wp_update_post( $postarr, true );
	} else {
		$id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, 'seed-campus-directory-e2e: ' . $id->get_error_message() . "\n" );
		exit( 1 );
	}

	return $id;
}

$lals_id        = ucsc_seed_page( 'lals', 'Campus Directory LALS E2E', $lals_home_content );
$lals_people_id = ucsc_seed_page( 'people', 'Campus Directory LALS People', $lals_people_content, $lals_id );
$lals_faculty   = ucsc_seed_page( 'faculty', 'Campus Directory LALS Faculty', $lals_content, $lals_people_id );

$eeb_id      = ucsc_seed_page( 'eeb', 'Campus Directory EEB E2E', $eeb_home_content );
$eeb_people  = ucsc_seed_page( 'people', 'Campus Directory EEB People', $eeb_content, $eeb_id );
$legacy_page = ucsc_seed_page( 'campus-directory-e2e', 'Campus Directory E2E', $eeb_content );

echo 'LALS_URL=', get_permalink( $lals_faculty ), "\n";
echo 'EEB_URL=', get_permalink( $eeb_people ), "\n";
echo 'LEGACY_URL=', get_permalink( $legacy_page ), "\n";
