<?php
/**
 * Course Detail Template
 * Matches the original WCSI app's course detail layout.
 *
 * Variables available from query vars:
 *   course_term  – term code (e.g. "2262")
 *   course_id    – class number
 */

if ( file_exists( get_theme_file_path( 'header-plugin.php' ) ) ) {
	get_header( 'plugin' );
} else {
	get_header();
}

$term      = get_query_var( 'course_term' );
$course_id = get_query_var( 'course_id' );

// ── Fetch course details ───────────────────────────────────────────────────────

$request  = new WP_REST_Request( 'GET', '/ucsc/v1/course/' . $term . '/' . $course_id );
$response = rest_do_request( $request );

if ( is_wp_error( $response ) ) {
	echo '<p>Error loading course details. Please try again later.</p>';
	get_footer();
	exit;
}

$course_data = $response->get_data();

if ( empty( $course_data ) ) {
	echo '<p>Course not found.</p>';
	get_footer();
	exit;
}

$primary            = $course_data['primary_section']   ?? null;
$meetings           = $course_data['meetings']          ?? [];
$secondary_sections = $course_data['secondary_sections'] ?? [];
$notes_array        = $course_data['notes']             ?? [];

if ( ! $primary ) {
	echo '<p>Course information unavailable.</p>';
	get_footer();
	exit;
}

// Enqueue shared styles (status dot shapes reused here)
wp_enqueue_style( 'classschedule', plugins_url( '../src/components/ClassSchedule/classschedule.css', dirname( __FILE__ ) ) );
wp_enqueue_style( 'ucsc-shared-templates', plugins_url( '../src/components/shared/templates.css', dirname( __FILE__ ) ) );
wp_enqueue_style( 'static-directory-page', plugins_url( '../src/components/shared/static-directory-page.css', dirname( __FILE__ ) ) );

// ── Computed values ───────────────────────────────────────────────────────────

// Status class
$status_text  = strtolower( $primary['enrl_status'] ?? '' );
if ( strpos( $status_text, 'wait' ) !== false ) {
	$status_class = 'waitlist';
} elseif ( strpos( $status_text, 'closed' ) !== false ) {
	$status_class = 'closed';
} else {
	$status_class = 'open';
}

// Term name from term code: "2262" → "Winter 2026"
$term_qtrs   = [ '0' => 'Winter', '2' => 'Spring', '4' => 'Summer', '6' => 'Summer', '8' => 'Fall' ];
$term_name   = ( $term_qtrs[ substr( $term, 3, 1 ) ] ?? '' ) . ' 20' . substr( $term, 1, 2 );

// Course identifier: "ANCS 49-01"
$course_name = $primary['subject'] . ' ' . $primary['catalog_nbr'] . '-' . $primary['class_section'];

// Available seats
$available = max( 0, ( (int) ( $primary['capacity']   ?? 0 ) ) - ( (int) ( $primary['enrl_total'] ?? 0 ) ) );

// Notes
$notes_text = '';
foreach ( $notes_array as $note ) {
	$notes_text .= $note . '. ';
}
$notes_text = trim( $notes_text );

// A11Y: Breadcrumb back URL — use the HTTP referer if it's on the same site, otherwise fall back to home
$breadcrumb_back_url = home_url( '/' );
$referer = wp_get_referer();
if ( $referer && wp_validate_redirect( $referer, false ) && strpos( $referer, home_url() ) === 0 ) {
	$breadcrumb_back_url = $referer;
}

// Time formatter — "TBA" passthrough, otherwise "g:i A"
// function_exists protects us if this template ever gets included twice, PHP won't have a fatal error with "Cannot redeclare."
if ( ! function_exists( 'format_course_time' ) ) {
	function format_course_time( $t ) {
		if ( ! $t || strtoupper( trim( $t ) ) === 'TBA' ) return 'TBA';
		$parsed = strtotime( '2001-01-01 ' . $t );
		return $parsed ? date( 'g:i A', $parsed ) : $t;
	}
}

// Aggregate meeting info from all meetings
$day_times_parts = [];
$locations       = [];
$instructor_map  = []; // name → instructor array (deduped)

foreach ( $meetings as $meeting ) {
	$days  = $meeting['days']       ?? '';
	$start = $meeting['start_time'] ?? '';
	$end   = $meeting['end_time']   ?? '';

	if ( strtoupper( trim( $start ) ) === 'TBA' || strtoupper( trim( $end ) ) === 'TBA' ) {
		$time_str = trim( $days . ' TBA' );
	} else {
		$time_str = trim( $days . ' ' . format_course_time( $start ) . ' - ' . format_course_time( $end ) );
	}
	$day_times_parts[] = $time_str;
	$locations[]       = $meeting['location'] ?? '';

	if ( ! empty( $meeting['instructors'] ) ) {
		foreach ( $meeting['instructors'] as $inst ) {
			$iname = $inst['name'] ?? '';
			if ( $iname && ! isset( $instructor_map[ $iname ] ) ) {
				$instructor_map[ $iname ] = $inst;
			}
		}
	}
}

$day_times = implode( '; ', $day_times_parts );
$location  = implode( ', ', array_unique( $locations ) );

// Meeting dates  e.g. "1/5/2026 - 3/13/2026"
$dates = '';
$start_ts = $primary['start_date'] ? strtotime( $primary['start_date'] ) : 0;
$end_ts   = $primary['end_date']   ? strtotime( $primary['end_date'] )   : 0;
if ( $start_ts && $end_ts ) {
	$dates = date( 'n/j/Y', $start_ts ) . ' - ' . date( 'n/j/Y', $end_ts );
}

// Secondary sections — normalize to array, preprocess meeting data
$sections = [];
if ( ! empty( $secondary_sections ) ) {
	$secs = $secondary_sections['secondary_section'] ?? $secondary_sections;

	if ( isset( $secs['class_section'] ) ) {
		$secs = [ $secs ]; // single section → wrap in array
	}

	foreach ( $secs as $sec ) {
		if ( ! is_array( $sec ) ) {
			continue;
		}

		// Skip if same section as primary
		if ( ( $sec['class_section'] ?? '' ) === ( $primary['class_section'] ?? '' ) ) {
			continue;
		}

		$sec_meetings = $sec['meetings'] ?? [];
		if ( isset( $sec_meetings['meeting'] ) ) {
			$sec_meetings = $sec_meetings['meeting'];
		}
		if ( isset( $sec_meetings['days'] ) || isset( $sec_meetings['start_time'] ) || isset( $sec_meetings['end_time'] ) ) {
			$sec_meetings = [ $sec_meetings ];
		}

		$sec_days_parts  = [];
		$sec_time_parts  = [];
		$sec_locations   = [];
		$sec_instructors = [];

		foreach ( (array) $sec_meetings as $meeting ) {
			if ( ! is_array( $meeting ) ) {
				continue;
			}

			$meeting_days  = $meeting['days'] ?? '';
			$meeting_start = $meeting['start_time'] ?? '';
			$meeting_end   = $meeting['end_time'] ?? '';

			if ( $meeting_days !== '' ) {
				$sec_days_parts[] = $meeting_days;
			}

			if ( strtoupper( trim( $meeting_start ) ) === 'TBA' || strtoupper( trim( $meeting_end ) ) === 'TBA' ) {
				$sec_time_parts[] = 'TBA';
			} elseif ( $meeting_start !== '' || $meeting_end !== '' ) {
				$sec_time_parts[] = format_course_time( $meeting_start ) . ' - ' . format_course_time( $meeting_end );
			}

			if ( ! empty( $meeting['location'] ) ) {
				$sec_locations[] = $meeting['location'];
			}

			$meeting_instructors = $meeting['instructors']['instructor'] ?? $meeting['instructors'] ?? [];
			if ( isset( $meeting_instructors['name'] ) || isset( $meeting_instructors['text'] ) ) {
				$meeting_instructors = [ $meeting_instructors ];
			}

			foreach ( (array) $meeting_instructors as $si ) {
				if ( ! is_array( $si ) ) {
					continue;
				}

				$iname   = $si['name'] ?? $si['text'] ?? '';
				$icruzid = $si['cruzid'] ?? '';
				if ( $iname === '' ) {
					continue;
				}

				$inst_key = $iname . '|' . $icruzid;
				if ( ! isset( $sec_instructors[ $inst_key ] ) ) {
					if ( $icruzid && $iname !== 'Staff' ) {
						$sec_instructors[ $inst_key ] = '<a href="' . esc_url( home_url( '/directory/' . $icruzid ) ) . '">' . esc_html( $iname ) . '</a>';
					} else {
						$sec_instructors[ $inst_key ] = esc_html( $iname );
					}
				}
			}
		}

		$sections[] = [
			'data'      => $sec,
			'days'      => implode( ', ', array_unique( $sec_days_parts ) ),
			'times'     => implode( ', ', array_unique( $sec_time_parts ) ),
			'location'  => implode( ', ', array_unique( $sec_locations ) ),
			'inst_html' => implode( ', ', array_values( $sec_instructors ) ),
		];
	}
}
?>

<main class="is-layout-flow wp-block-group content-region" id="wp--skip-link--target" style="margin-block-start: var(--wp--preset--font-size--one);">
	<div class="has-global-padding is-layout-constrained wp-block-group">
		<nav class="breadcrumbs alignwide" aria-label="Breadcrumbs" itemprop="breadcrumb">
			<ul class="breadcrumbs__trail" itemscope="" itemtype="https://schema.org/BreadcrumbList">
				<li class="breadcrumbs__crumb breadcrumbs__crumb--home" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
					<a href="/" itemprop="item"><span itemprop="name">Home</span></a>
				</li>
				<li class="breadcrumbs__crumb" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
					<a href="<?php echo esc_url( $breadcrumb_back_url ); ?>" itemprop="item"><span itemprop="name">Class Schedule</span></a>
				</li>
				<li class="breadcrumbs__crumb" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
					<span itemprop="name"><?php echo esc_html( $course_name ); ?></span>
				</li>
			</ul>
		</nav>
	</div>

	<div class="has-global-padding is-layout-constrained wp-block-group alignwide">

		<div class="course-status-heading">
			<i class="<?php echo esc_attr( $status_class ); ?>" aria-hidden="true"></i>
			<span><?php echo esc_html( $primary['enrl_status'] ); ?></span>
		</div>

		<h1 id="title" class="page-title"><span class="p-name"><?php echo esc_html( $primary['title_long'] ); ?></span></h1>

		<div class="section-container person list-page">
			<span><?php echo esc_html( $term_name ); ?></span> -
			<span><?php echo esc_html( $course_name ); ?></span>

			<div class="section-body">
				<div id="class-info" class="section-item h-card wrap">
					<div class="item-body">

						<?php if ( ! empty( $primary['description'] ) ) : ?>
						<div class="item-expertise">
							<h2 class="h3-style">Description</h2>
							<div class="item-expertise">
								<span><?php echo nl2br( esc_html( $primary['description'] ) ); ?></span>
								<?php if ( ! empty( $primary['gened'] ) ) : ?>
								<span>(General Education Code(s): <?php echo esc_html( $primary['gened'] ); ?>.)</span>
								<?php endif; ?>
							</div>
						</div>
						<?php endif; ?>

						<div>
							<h2 class="h3-style">Capacity and Available Seats</h2>
							<dl class="item-info">
								<div><dt>Available Seats</dt><dd><?php echo esc_html( $available ); ?></dd></div>
								<?php if ( ! empty( $primary['capacity'] ) ) : ?>
								<div><dt>Enrollment Capacity</dt><dd><?php echo esc_html( $primary['capacity'] ); ?></dd></div>
								<?php endif; ?>
								<div><dt>Enrolled</dt><dd><?php echo esc_html( $primary['enrl_total'] ?? 0 ); ?></dd></div>
							</dl>
						</div>

						<?php if ( ! empty( $primary['requirements'] ) ) : ?>
						<div class="item-expertise">
							<h2 class="h3-style">Enrollment Requirements</h2>
							<div class="item-expertise"><?php echo nl2br( esc_html( $primary['requirements'] ) ); ?></div>
						</div>
						<?php endif; ?>

						<?php if ( $notes_text ) : ?>
						<div class="item-expertise">
							<h2 class="h3-style">Class Notes</h2>
							<div class="item-expertise"><?php echo nl2br( esc_html( $notes_text ) ); ?></div>
						</div>
						<?php endif; ?>

						<div>
							<h2 class="h3-style">Class Details</h2>
							<dl class="item-info">
								<?php if ( ! empty( $primary['acad_career'] ) ) : ?>
								<div><dt>Career</dt><dd><?php echo esc_html( $primary['acad_career'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $primary['grading'] ) ) : ?>
								<div><dt>Grading</dt><dd><?php echo esc_html( $primary['grading'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $primary['class_nbr'] ) ) : ?>
								<div><dt>Class Number</dt><dd><?php echo esc_html( $primary['class_nbr'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $primary['component'] ) ) : ?>
								<div><dt>Type</dt><dd><?php echo esc_html( $primary['component'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $primary['credits'] ) ) : ?>
								<div><dt>Credits</dt><dd><?php echo esc_html( $primary['credits'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $primary['gened'] ) ) : ?>
								<div><dt>General Education</dt><dd><?php echo esc_html( $primary['gened'] ); ?></dd></div>
								<?php endif; ?>
							</dl>
						</div>

						<?php if ( ! empty( $meetings ) ) : ?>
						<div>
							<h2 class="h3-style">Meeting Information</h2>
							<dl class="item-info">
								<div><dt>Days &amp; Times</dt><dd><?php echo esc_html( $day_times ); ?></dd></div>
								<div><dt>Room</dt><dd><?php echo esc_html( $location ); ?></dd></div>
								<?php if ( ! empty( $instructor_map ) ) : ?>
								<div><dt>Instructor</dt><dd>
									<?php
									$inst_parts = [];
									foreach ( $instructor_map as $iname => $inst ) {
										$icruzid = $inst['cruzid'] ?? '';
										if ( $icruzid && $iname !== 'Staff' ) {
											$inst_parts[] = '<a href="' . esc_url( home_url( '/directory/' . $icruzid ) ) . '">' . esc_html( $iname ) . '</a>';
										} else {
											$inst_parts[] = esc_html( $iname );
										}
									}
									echo implode( ', ', $inst_parts );
									?>
								</dd></div>
								<?php endif; ?>
								<?php if ( $dates ) : ?>
								<div><dt>Meeting Dates</dt><dd><?php echo esc_html( $dates ); ?></dd></div>
								<?php endif; ?>
							</dl>
						</div>
						<?php endif; ?>

						<?php if ( ! empty( $sections ) ) : ?>
						<h2 class="h3-style">Associated Discussion Sections or Labs</h2>
						<?php foreach ( $sections as $sec_info ) :
							$sec = $sec_info['data'];
						?>
						<div class="class-section">
							<dl class="item-info">
								<?php if ( ! empty( $sec['class_section'] ) ) : ?>
								<div><dt>Section</dt><dd><?php echo esc_html( $sec['class_section'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $sec['component'] ) ) : ?>
								<div><dt>Type</dt><dd><?php echo esc_html( $sec['component'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $sec['class_nbr'] ) ) : ?>
								<div><dt>Class Number</dt><dd><?php echo esc_html( $sec['class_nbr'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $sec_info['days'] ) ) : ?>
								<div><dt>Days</dt><dd><?php echo esc_html( $sec_info['days'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $sec_info['times'] ) ) : ?>
								<div><dt>Time</dt><dd><?php echo esc_html( $sec_info['times'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $sec_info['inst_html'] ) ) : ?>
								<div><dt>Instructor</dt><dd><?php echo $sec_info['inst_html']; ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $sec_info['location'] ) ) : ?>
								<div><dt>Location</dt><dd><?php echo esc_html( $sec_info['location'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $sec['capacity'] ) ) : ?>
								<div><dt>Enrollment Capacity</dt><dd><?php echo esc_html( $sec['capacity'] ); ?></dd></div>
								<?php endif; ?>
								<?php if ( ! empty( $sec['enrl_total'] ) ) : ?>
								<div><dt>Enrolled</dt><dd><?php echo esc_html( $sec['enrl_total'] ); ?></dd></div>
								<?php endif; ?>
							</dl>
						</div>
						<?php endforeach; ?>
						<?php endif; ?>

					</div><!-- .item-body -->
				</div><!-- #class-info -->
			</div><!-- .section-body -->
		</div><!-- .section-container -->

	</div>
</main>

<style>
/* Keep status indicator aligned with the title at wide viewports.
   !important overrides the theme's .is-layout-constrained > * { max-width: 80rem } */
.course-status-heading {
	max-width: none !important;
	margin-left: 0 !important;
	margin-right: 0 !important;
}
/* Secondary sections get a light-blue tinted background, matching old WCSI */
.class-section {
	margin: 10px 0;
	background-color: #F0F7FD;
}
.class-section .item-info {
	margin: 20px 0 !important;
}
.class-section .item-info > div {
	border-bottom: 2px solid #fff !important;
}
@media (min-width: 768px) {
	#class-info .item-info dt {
		flex-basis: 180px;
	}
}
@media print {
	.no-print { display: none !important; }
	#class-info .item-info > div {
		align-items: flex-start;
		flex-direction: row;
		border-bottom: 1px solid rgba(0,0,0,0.12);
	}
	#class-info .item-info > div:last-of-type {
		border-bottom: 0;
	}
	#class-info .item-info dt {
		flex: 0 0 180px;
		text-align: right;
		padding-right: 1.5em;
	}
}
</style>

<?php get_footer(); ?>
