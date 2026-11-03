<?php
/**
 * Dependency-free tests for CampusDirectoryTemplate.php.
 *
 * WPM-144: render the template directly against fixture data and assert that
 * every interpolated value is escaped before it reaches the front-end markup.
 *
 * Run from the plugin directory:
 *   docker run --rm -v "$PWD:/plugin" -w /plugin php:8.1-cli \
 *     php tests/php/CampusDirectoryTemplateTest.php
 */

function esc_url( $value ) {
	$value = esc_attr( $value );
	return preg_match( '/^javascript:/i', $value ) ? '' : $value;
}

// Stand-ins for the WordPress sanitizers: enough to tell sanitized output from
// escaped output here. The real wp_kses_post() policy is WordPress's own.
function wp_kses_post( $value ) {
	$value = preg_replace( '#<script\b[^>]*>.*?</script>#is', '', $value );
	return preg_replace( '#\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $value );
}

function force_balance_tags( $value ) {
	return str_replace( '</div>', '', $value );
}

function wp_strip_all_tags( $value ) {
	return strip_tags( $value );
}

function rest_url( $path = '' ) {
	return 'https://example.test/wp-json/' . ltrim( $path, '/' );
}

// Load the real API class so the template uses the real RICH_TEXT_FIELDS. Its
// file requires wp-admin classes, so point ABSPATH at empty stand-ins.
define( 'ABSPATH', sys_get_temp_dir() . '/wp-mock-cdt/' );
@mkdir( ABSPATH . 'wp-admin/includes', 0777, true );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php', "<?php\nclass WP_Filesystem_Base {}\n" );
@file_put_contents( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php', "<?php\nclass WP_Filesystem_Direct extends WP_Filesystem_Base {}\n" );
require __DIR__ . '/../../classes/CampusDirectoryAPI.php';

require __DIR__ . '/helpers/harness.php';

function render_campus_directory_template( $items, $headingLevel = null ) {
	ob_start();
	include __DIR__ . '/../../templates/CampusDirectoryTemplate.php';
	return ob_get_clean();
}

function person_fixture( $overrides = array() ) {
	return array_merge(
		array(
			'uid'                                      => array( 'jdoe' ),
			'cn'                                       => array( 'Jane Doe' ),
			'title'                                    => array( 'Banana Slug Wrangler' ),
			'mail'                                     => array(
				'count' => 1,
				0       => 'jdoe@ucsc.edu',
			),
			'ucscpersonpubalternatemail'               => array(
				'count' => 1,
				0       => 'jane@example.com',
			),
			'ucscpersonpubwebsite'                     => array(
				'count' => 1,
				0       => 'https://example.ucsc.edu Jane Website',
			),
			'ucscprimarylocationpubofficialname'       => array( 'McHenry Library' ),
			'ucscpersonpubofficelocationdetail'        => array( 'Room 404' ),
		),
		$overrides
	);
}

function directory_items_fixture( $overrides = array() ) {
	return array_merge(
		array(
			'dirLayout'            => 'list',
			'linkToProfile'        => true,
			'nodeContent'          => array(
				'linkOutToCampusDirectory' => false,
			),
			'informationToDisplay' => array(
				array( 'title' => 'Title' ),
				array( 'mail' => 'Campus Email' ),
				array( 'ucscpersonpubalternatemail' => 'Other Email' ),
				array( 'ucscpersonpubwebsite' => 'Website' ),
				array( 'ucscpersonpubofficelocationdetail' => 'Office Location' ),
			),
			'items'                => array( person_fixture() ),
		),
		$overrides
	);
}

function assert_no_raw_attack_markup( $html ) {
	check( 'does not render a raw script tag', false === strpos( $html, '<script' ) );
	check( 'does not render an unescaped image event handler', false === strpos( $html, '<img src=x onerror=' ) );
	check( 'does not render a raw javascript URL', false === strpos( $html, 'href="javascript:' ) && false === strpos( $html, "href='javascript:" ) );
}

echo "CampusDirectoryTemplate tests:\n\n";

echo "list layout rendering:\n";
$html = render_campus_directory_template( directory_items_fixture() );
check( 'renders the list-page wrapper', false !== strpos( $html, 'ucsc-block-directory list-page' ) );
check( 'renders a person card', false !== strpos( $html, 'section-item h-card wrap' ) );
check( 'renders the profile name', false !== strpos( $html, 'Jane Doe' ) );
check( 'links names to the in-site profile route by default', false !== strpos( $html, '?directoryprofilecruzid=jdoe' ) );
check( 'renders campus email as a mailto link', false !== strpos( $html, 'mailto:jdoe@ucsc.edu' ) );
check( 'renders the office location and detail', false !== strpos( $html, 'McHenry Library, Room 404' ) );

$html = render_campus_directory_template(
	directory_items_fixture( array( 'items' => array( person_fixture( array( 'uid' => array( 'j doe/x' ) ) ) ) ) )
);
check( 'URL-encodes the cruzid in the profile link', false !== strpos( $html, '?directoryprofilecruzid=j%20doe%2Fx' ) );

echo "\ntable layout rendering:\n";
$html = render_campus_directory_template(
	directory_items_fixture(
		array(
			'dirLayout'            => 'table',
			'informationToDisplay' => array(
				array( 'title' => 'Title' ),
				array( 'mail' => 'Campus Email' ),
			),
		)
	)
);
check( 'renders the table-page wrapper', false !== strpos( $html, 'ucsc-block-directory table-page' ) );
check( 'renders a table row for the person', false !== strpos( $html, 'class="item-body"' ) );
check( 'renders non-email table headers', false !== strpos( $html, '<th scope="col" class="titles">Title</th>' ) );
check( 'renders the Campus Email table header', false !== strpos( $html, '<th scope="col" class="titles">Campus Email</th>' ) );
check( 'renders table email as a mailto link', false !== strpos( $html, 'mailto:jdoe@ucsc.edu' ) );

echo "\nempty result (CPM-12):\n";
$html = render_campus_directory_template(
	directory_items_fixture(
		array(
			'dirLayout'            => 'table',
			'items'                => array(),
			'informationToDisplay' => array(
				array( 'title' => 'Title' ),
				array( 'mail' => 'Campus Email' ),
			),
		)
	)
);
check( 'table still renders its headers', false !== strpos( $html, '<th scope="col" class="titles">Title</th>' ) );
check( 'table renders a no-records row', false !== strpos( $html, 'No records found.' ) );
check( 'no-records cell spans every column', false !== strpos( $html, 'colspan="3"' ) );
foreach ( array( 'list', 'tiled' ) as $layout ) {
	$html = render_campus_directory_template( directory_items_fixture( array( 'dirLayout' => $layout, 'items' => array() ) ) );
	check( "$layout layout renders the no-records message", false !== strpos( $html, '<p class="no-records">No records found.</p>' ) );
}
$html = render_campus_directory_template( directory_items_fixture( array( 'dirLayout' => 'table' ) ) );
check( 'no message when there are results', false === strpos( $html, 'No records found.' ) );

echo "\nexternal profile links and image fallback:\n";
$html = render_campus_directory_template(
	directory_items_fixture(
		array(
			'nodeContent'          => array(
				'linkOutToCampusDirectory' => true,
			),
			'informationToDisplay' => array(
				array( 'b64image' => 'Photo' ),
			),
		)
	)
);
check( 'links names to campusdirectory.ucsc.edu when configured', false !== strpos( $html, 'https://campusdirectory.ucsc.edu/cd_detail?uid=jdoe' ) );
check( 'renders profile photo with escaped alt text', false !== strpos( $html, 'alt="Profile picture of Jane Doe"' ) );
check( 'renders the escaped static fallback image URL', false !== strpos( $html, '//static.ucsc.edu/images/icon-slug.jpg' ) );
check( 'renders the photo from the local LDAP photo endpoint', false !== strpos( $html, 'src="https://example.test/wp-json/ucscgutenbergblocks/v1/directoryphoto/jdoe"' ) );
check( 'photo no longer depends on campusdirectory.ucsc.edu', false === strpos( $html, 'photo.php' ) );
check( 'loads directory photos lazily at their 160px display size (WPM-257)', false !== strpos( $html, 'directoryphoto/jdoe" loading="lazy" width="160" height="160"' ) );

echo "\nescaping LDAP data and block settings:\n";
$attack_uid     = 'jdoe"><script>alert("uid")</script>';
$attack_name    = 'Jane <script>alert("name")</script> Doe';
$attack_title   = '"><img src=x onerror=alert("title")>';
$attack_email   = 'jane"><script>alert("mail")</script>@ucsc.edu';
$attack_website = 'javascript:alert(1) <script>alert("label")</script>';
$html           = render_campus_directory_template(
	directory_items_fixture(
		array(
			'items' => array(
				person_fixture(
					array(
						'uid'                        => array( $attack_uid ),
						'cn'                         => array( $attack_name ),
						'title'                      => array( $attack_title ),
						'mail'                       => array(
							'count' => 1,
							0       => $attack_email,
						),
						'ucscpersonpubwebsite'       => array(
							'count' => 1,
							0       => $attack_website,
						),
					)
				),
			),
		)
	)
);
assert_no_raw_attack_markup( $html );
check( 'escapes the profile name text', false !== strpos( $html, 'Jane &lt;script&gt;alert(&quot;name&quot;)&lt;/script&gt; Doe' ) );
check( 'escapes normal field values', false !== strpos( $html, '&quot;&gt;&lt;img src=x onerror=alert(&quot;title&quot;)&gt;' ) );
check( 'escapes the profile URL attribute', false === strpos( $html, '?directoryprofilecruzid=' . $attack_uid ) );
check( 'escapes the mailto href attribute', false === strpos( $html, 'mailto:' . $attack_email ) );
check( 'escapes the website label', false !== strpos( $html, '&lt;script&gt;alert(&quot;label&quot;)&lt;/script&gt;' ) );

// ---------------------------------------------------------------------------
// Rich-text LDAP fields keep their markup (WPM-144 regression)
// ---------------------------------------------------------------------------
echo "\nrich-text fields:\n";
// The profile template keeps its own field => heading map, so make sure the two can't drift.
$profile_source = file_get_contents( __DIR__ . '/../../templates/DirectoryProfileTemplate.php' );
preg_match( '/\$expertiseFields = \[(.*?)\];/s', $profile_source, $profile_map );
preg_match_all( '/"(ucscpersonpub\w+)"\s*=>/', $profile_map[1] ?? '', $profile_keys );
$rich_constant = CampusDirectoryAPI::RICH_TEXT_FIELDS;
sort( $rich_constant );
sort( $profile_keys[1] );
check( 'RICH_TEXT_FIELDS matches the profile page expertise fields', 10 === count( $rich_constant ) && $rich_constant === $profile_keys[1] );

$rich_value  = '<p>Studies <strong>kelp</strong> <a href="https://example.edu/lab">lab</a></p>';
$rich_attack = '<p>Safe</p><script>alert("rich")</script><img src=x onerror=alert("rich")></div>';
$rich_info   = array(
	array( 'ucscpersonpubareaofexpertise' => 'Summary of Expertise' ),
	array( 'ucscpersonpubofficehours' => 'Office Hours' ),
);
foreach ( array( 'list', 'tiled', 'table' ) as $layout ) {
	$html = render_campus_directory_template(
		directory_items_fixture(
			array(
				'dirLayout'            => $layout,
				'informationToDisplay' => $rich_info,
				'items'                => array(
					person_fixture(
						array(
							'ucscpersonpubareaofexpertise' => array( $rich_value ),
							'ucscpersonpubofficehours'     => array( '<em>Tue</em>' ),
						)
					),
				),
			)
		)
	);
	check( "$layout renders Summary of Expertise markup", false !== strpos( $html, '<li><div class="directory-rich-text">' . $rich_value . '</div></li>' ) );
	check( "$layout does not show Summary of Expertise tags as text", false === strpos( $html, '&lt;strong&gt;kelp' ) );
	check( "$layout still escapes fields not marked rich text", false !== strpos( $html, '&lt;em&gt;Tue&lt;/em&gt;' ) );

	$html = render_campus_directory_template(
		directory_items_fixture(
			array(
				'dirLayout'            => $layout,
				'informationToDisplay' => $rich_info,
				'items'                => array(
					person_fixture( array( 'ucscpersonpubareaofexpertise' => array( $rich_attack ) ) ),
				),
			)
		)
	);
	check( "$layout strips script from rich text", false === strpos( $html, 'alert("rich")</script>' ) );
	check( "$layout strips event handlers from rich text", false === strpos( $html, 'onerror=' ) );
	check( "$layout drops stray closing tags from rich text", false === strpos( $html, '</div></div></li>' ) );
}
// Scans the whole rendered page, so it also covers the table's <strong> label.
$rich_all_tags = '<p>One <strong>two</strong> <em>three</em><br /> <a href="https://example.edu/lab">four</a></p><ul><li>five</li></ul><h4>six</h4>';
foreach ( array( 'list', 'tiled', 'table' ) as $layout ) {
	$html = render_campus_directory_template(
		directory_items_fixture(
			array(
				'dirLayout'            => $layout,
				'informationToDisplay' => array( array( 'ucscpersonpubareaofexpertise' => 'Summary of Expertise' ) ),
				'items'                => array( person_fixture( array( 'ucscpersonpubareaofexpertise' => array( $rich_all_tags ) ) ) ),
			)
		)
	);
	check( "$layout renders no escaped HTML anywhere", ! has_escaped_html( $html ) );
	check( "$layout renders the rich text as real tags", false !== strpos( $html, $rich_all_tags ) );
}
$html = render_campus_directory_template(
	directory_items_fixture(
		array(
			'dirLayout'            => 'table',
			'informationToDisplay' => $rich_info,
			'items'                => array(
				person_fixture( array( 'ucscpersonpubareaofexpertise' => array( $rich_value ), 'ucscpersonpubofficehours' => array( 'Tue' ) ) ),
			),
		)
	)
);
check( 'table keeps block markup out of the strong label', false !== strpos( $html, '<strong>Studies kelp lab</strong>' ) );

// ---------------------------------------------------------------------------
// Person-name heading level (WPM-92)
// ---------------------------------------------------------------------------
echo "\nperson-name heading level (WPM-92):\n";
foreach ( array( 'list', 'tiled' ) as $layout ) {
	$html_default = render_campus_directory_template( directory_items_fixture( array( 'dirLayout' => $layout ) ) );
	check( "$layout: names default to H3", 1 === preg_match( '/<h3 class="item-name">.*?<\/h3>/s', $html_default ) );
	$html_h2 = render_campus_directory_template( directory_items_fixture( array( 'dirLayout' => $layout ) ), 2 );
	check( "$layout: names use H2 when chosen", 1 === preg_match( '/<h2 class="item-name">.*?<\/h2>/s', $html_h2 ) && false === strpos( $html_h2, '<h3' ) );
}
foreach ( array( 1, 5, 6, 0 ) as $bad_level ) {
	$html_bad = render_campus_directory_template( directory_items_fixture(), $bad_level );
	check( "falls back to H3 for out-of-range level $bad_level", 1 === preg_match( '/<h3 class="item-name">.*?<\/h3>/s', $html_bad ) );
}

finish_tests();
