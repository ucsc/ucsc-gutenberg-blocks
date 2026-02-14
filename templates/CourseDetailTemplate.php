<?php
/**
 * Course Detail Template
 * Displays full course information including description, requirements, instructors, and sections
 */

if ( file_exists( get_theme_file_path( 'header-plugin.php' ) ) ) {
	get_header( 'plugin' );
} else {
	get_header();
}

$term = get_query_var('course_term');
$course_id = get_query_var('course_id');

// Fetch course details from API using internal REST API call
$request = new WP_REST_Request('GET', '/ucsc/v1/course/' . $term . '/' . $course_id);
$response = rest_do_request($request);

if (is_wp_error($response)) {
	echo '<p>Error loading course details. Please try again later.</p>';
	get_footer();
	exit;
}

$course_data = $response->get_data();

if (empty($course_data)) {
	echo '<p>Course not found.</p>';
	get_footer();
	exit;
}

// Extract data
$primary = $course_data['primary_section'] ?? null;
$meetings = $course_data['meetings'] ?? [];
$secondary_sections = $course_data['secondary_sections'] ?? [];

if (!$primary) {
	echo '<p>Course information unavailable.</p>';
	get_footer();
	exit;
}

// Enqueue styles
wp_enqueue_style('classschedule', plugins_url('../src/components/ClassSchedule/classschedule.css', dirname(__FILE__)));

?>

<main class="is-layout-flow wp-block-group content-region" id="wp--skip-link--target" style="margin-block-start: var(--wp--preset--font-size--one);">
	<div class="has-global-padding is-layout-constrained wp-block-group">
		<nav class="breadcrumbs alignwide" role="navigation" aria-label="Breadcrumbs" itemprop="breadcrumb">
			<ul class="breadcrumbs__trail" itemscope="" itemtype="https://schema.org/BreadcrumbList">
				<li class="breadcrumbs__crumb breadcrumbs__crumb--home" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
					<a href="/" itemprop="item">
						<span itemprop="name">Home</span>
					</a>
				</li>
				<li class="breadcrumbs__crumb" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
					<span itemprop="name">Course Details</span>
				</li>
			</ul>
		</nav>
	</div>

	<div class="has-global-padding is-layout-constrained wp-block-group alignwide">
		<article class="course-detail">
			<header class="course-header">
				<h1><?php echo esc_html($primary['subject'] . ' ' . $primary['catalog_nbr'] . ': ' . $primary['title_long']); ?></h1>
				<div class="course-meta">
					<span class="course-component"><?php echo esc_html($primary['component']); ?></span>
					<span class="course-credits"><?php echo esc_html($primary['credits']); ?> Units</span>
					<span class="course-grading"><?php echo esc_html($primary['grading']); ?></span>
				</div>
			</header>

			<?php if (!empty($primary['description'])): ?>
			<section class="course-description">
				<h2>Description</h2>
				<p><?php echo nl2br(esc_html($primary['description'])); ?></p>
			</section>
			<?php endif; ?>

			<?php if (!empty($primary['requirements'])): ?>
			<section class="course-requirements">
				<h2>Requirements</h2>
				<p><?php echo nl2br(esc_html($primary['requirements'])); ?></p>
			</section>
			<?php endif; ?>

			<?php if (!empty($primary['gened'])): ?>
			<section class="course-gened">
				<h2>General Education</h2>
				<p><?php echo esc_html($primary['gened']); ?></p>
			</section>
			<?php endif; ?>

			<section class="course-enrollment">
				<h2>Enrollment Information</h2>
				<ul class="course-info-list">
					<li>
						<strong>Class Number</strong>
						<span><?php echo esc_html($primary['class_nbr']); ?></span>
					</li>
					<li>
						<strong>Section</strong>
						<span><?php echo esc_html($primary['class_section']); ?></span>
					</li>
					<li>
						<strong>Status</strong>
						<span class="status-<?php echo esc_attr(strtolower($primary['enrl_status'])); ?>">
							<?php echo esc_html($primary['enrl_status']); ?>
						</span>
					</li>
					<li>
						<strong>Enrollment</strong>
						<span><?php echo esc_html($primary['enrl_total'] . ' / ' . $primary['capacity']); ?></span>
					</li>
					<li>
						<strong>Waitlist</strong>
						<span><?php echo esc_html($primary['waitlist_total'] . ' / ' . $primary['waitlist_capacity']); ?></span>
					</li>
					<li>
						<strong>Term Dates</strong>
						<span><?php echo esc_html(date('M j, Y', strtotime($primary['start_date'])) . ' - ' . date('M j, Y', strtotime($primary['end_date']))); ?></span>
					</li>
				</ul>
			</section>

			<?php if (!empty($meetings)): ?>
			<section class="course-meetings">
				<h2>Meeting Times</h2>
				<div class="meeting-list">
					<?php foreach ($meetings as $meeting): ?>
					<div class="meeting-item">
						<div class="meeting-detail">
							<strong>Days & Times</strong>
							<span><?php echo esc_html($meeting['days'] . ' ' . $meeting['start_time'] . ' - ' . $meeting['end_time']); ?></span>
						</div>
						<div class="meeting-detail">
							<strong>Location</strong>
							<span><?php echo esc_html($meeting['location']); ?></span>
						</div>
						<div class="meeting-detail">
							<strong>Instructor(s)</strong>
							<span>
								<?php
								if (!empty($meeting['instructors'])) {
									$instructor_links = array_map(function($instructor) {
										if (!empty($instructor['cruzid'])) {
											return '<a href="' . esc_url(home_url('/directory/' . $instructor['cruzid'])) . '">' . esc_html($instructor['name']) . '</a>';
										}
										return esc_html($instructor['name']);
									}, $meeting['instructors']);
									echo implode(', ', $instructor_links);
								}
								?>
							</span>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</section>
			<?php endif; ?>

			<?php if (!empty($secondary_sections['secondary_section'])): ?>
			<section class="course-sections">
				<h2>Sections</h2>
				<div class="section-list">
					<?php
					$sections = $secondary_sections['secondary_section'];
					// Ensure it's an array of sections
					if (isset($sections['class_section'])) {
						$sections = [$sections];
					}
					foreach ($sections as $section):
						$section_meeting = $section['meetings']['meeting'] ?? [];
					?>
					<div class="section-item">
						<div class="section-detail">
							<strong>Section</strong>
							<span><?php echo esc_html($section['class_section']); ?></span>
						</div>
						<div class="section-detail">
							<strong>Type</strong>
							<span><?php echo esc_html($section['component']); ?></span>
						</div>
						<div class="section-detail">
							<strong>Days & Times</strong>
							<span><?php echo esc_html(($section_meeting['days'] ?? '') . ' ' . ($section_meeting['start_time'] ?? '') . ' - ' . ($section_meeting['end_time'] ?? '')); ?></span>
						</div>
						<div class="section-detail">
							<strong>Location</strong>
							<span><?php echo esc_html($section_meeting['location'] ?? ''); ?></span>
						</div>
						<div class="section-detail">
							<strong>Instructor</strong>
							<span>
								<?php
								if (!empty($section_meeting['instructors']['instructor'])) {
									$instructor = $section_meeting['instructors']['instructor'];
									$instructor_name = $instructor['name'] ?? '';
									if (!empty($instructor['cruzid']) && !empty($instructor_name)) {
										echo '<a href="' . esc_url(home_url('/directory/' . $instructor['cruzid'])) . '">' . esc_html($instructor_name) . '</a>';
									} else {
										echo esc_html($instructor_name);
									}
								}
								?>
							</span>
						</div>
						<div class="section-detail">
							<strong>Status</strong>
							<span class="status-<?php echo esc_attr(strtolower($section['enrl_status'])); ?>">
								<?php echo esc_html($section['enrl_status']); ?>
							</span>
						</div>
						<div class="section-detail">
							<strong>Enrollment</strong>
							<span><?php echo esc_html($section['enrl_total'] . ' / ' . $section['capacity']); ?></span>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</section>
			<?php endif; ?>

		</article>
	</div>
</main>

<style>
.course-detail {
	padding: 20px 0;
}

.course-header {
	margin-bottom: 30px;
	padding-bottom: 20px;
	border-bottom: 2px solid #003c6c;
}

.course-header h1 {
	margin-bottom: 10px;
	color: #003c6c;
}

.course-meta {
	display: flex;
	gap: 20px;
	flex-wrap: wrap;
}

.course-meta span {
	padding: 5px 10px;
	background: #f0f0f0;
	border-radius: 4px;
	font-size: 14px;
}

.course-detail section {
	margin-bottom: 30px;
}

.course-detail h2 {
	color: #000;
	margin: 40px 0 20px 0;
	font-size: 2.2em;
	font-weight: bold;
	text-align: left;
}

.course-info-list {
	list-style: none;
	padding: 0;
	margin: 30px auto;
	max-width: 600px;
}

.course-info-list li {
	display: grid;
	grid-template-columns: 200px 1fr;
	padding: 5px 0;
	border-bottom: 1px solid #e0e0e0;
	gap: 20px;
}

.course-info-list li:last-child {
	border-bottom: none;
}

.course-info-list li strong {
	font-weight: bold;
	color: #000;
	text-align: right;
	font-size: 1.1em;
	white-space: nowrap;
}

.course-info-list li span {
	color: #000;
	text-align: left;
	font-size: 1.1em;
}

.meeting-list,
.section-list {
	margin: 30px auto;
	max-width: 600px;
}

.meeting-item,
.section-item {
	margin-bottom: 30px;
}

.meeting-detail,
.section-detail {
	display: grid;
	grid-template-columns: 200px 1fr;
	padding: 5px 0;
	border-bottom: 1px solid #e0e0e0;
	gap: 20px;
}

.meeting-item .meeting-detail:last-child,
.section-item .section-detail:last-child {
	border-bottom: none;
}

.meeting-detail strong,
.section-detail strong {
	font-weight: bold;
	color: #000;
	text-align: right;
	font-size: 1.1em;
	white-space: nowrap;
}

.meeting-detail span,
.section-detail span {
	color: #000;
	text-align: left;
	font-size: 1.1em;
}

@media (max-width: 768px) {
	.course-info-list li,
	.meeting-detail,
	.section-detail {
		grid-template-columns: 1fr;
		gap: 5px;
	}

	.course-info-list li strong,
	.meeting-detail strong,
	.section-detail strong {
		text-align: left;
	}
}
</style>

<?php
get_footer();
?>
