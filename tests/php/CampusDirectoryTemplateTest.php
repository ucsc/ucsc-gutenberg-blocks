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

require __DIR__ . '/helpers/harness.php';

function render_campus_directory_template( $items ) {
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

finish_tests();
