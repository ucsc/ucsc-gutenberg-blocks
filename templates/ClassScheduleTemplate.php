<?php
/**
 * Class Schedule block template.
 *
 * Variables available from ClassSchedule::theHTML():
 *   $courses       array   Course data from the API
 *   $current_term  string  Active term code
 */
?>
<div id="classSchedule">

  <div class="introText">
    <label>Search Courses: <input type="text" id="courseSearch" onkeyup="classScheduleSearch(event)"></label>
    <button id="filterButton" class="filter-button" onclick="openFilterModal()">⚙ Filter</button>
  </div>

  <div id="filterModal" class="filter-modal">
    <div class="filter-modal-content">
      <h3>Display Columns</h3>
      <div class="column-toggles">
        <label><input type="checkbox" class="column-toggle" data-column="subject" checked> Subject</label>
        <label><input type="checkbox" class="column-toggle" data-column="course-num" checked> Course #</label>
        <label><input type="checkbox" class="column-toggle" data-column="title" checked> Title</label>
        <label><input type="checkbox" class="column-toggle" data-column="type" checked> Type</label>
        <label><input type="checkbox" class="column-toggle" data-column="days" checked> Days</label>
        <label><input type="checkbox" class="column-toggle" data-column="time" checked> Time</label>
        <label><input type="checkbox" class="column-toggle" data-column="location" checked> Location</label>
        <label><input type="checkbox" class="column-toggle" data-column="instructor" checked> Instructor</label>
        <label><input type="checkbox" class="column-toggle" data-column="seats" checked> Seats</label>
      </div>

      <h3>Status</h3>
      <div class="status-filters">
        <label><input type="checkbox" class="status-filter" data-status="open" checked> Open</label>
        <label><input type="checkbox" class="status-filter" data-status="closed" checked> Closed</label>
        <label><input type="checkbox" class="status-filter" data-status="waitlist" checked> Wait List</label>
      </div>

      <div class="filter-actions">
        <button class="reset-filters" onclick="resetFilters()">Reset all filters</button>
        <button class="cancel-button" onclick="closeFilterModal()">Cancel</button>
        <button class="apply-button" onclick="applyFilters()">Apply</button>
      </div>
    </div>
  </div>

  <div class="status-legend">
    <div class="count">Displaying <strong><?php echo count($courses); ?></strong> classes</div>
    <div class="legend-items">
      <div class="legend-item"><div class="status-indicator open"></div> Open</div>
      <div class="legend-item"><div class="status-indicator closed"></div> Closed</div>
      <div class="legend-item"><div class="status-indicator waitlist"></div> Closed w/ Wait List</div>
    </div>
  </div>

  <div class="course-list-container" id="classScheduleTable">
    <div class="course-list-header">
      <div class="course-col status-col"></div>
      <div class="course-col subject"    onclick="sortClassSchedule(1)">Subject</div>
      <div class="course-col course-num" onclick="sortClassSchedule(2)">Course #</div>
      <div class="course-col title"      onclick="sortClassSchedule(3)">Title</div>
      <div class="course-col type"       onclick="sortClassSchedule(4)">Type</div>
      <div class="course-col days"       onclick="sortClassSchedule(5)">Days</div>
      <div class="course-col time"       onclick="sortClassSchedule(6)">Time</div>
      <div class="course-col location"   onclick="sortClassSchedule(7)">Location</div>
      <div class="course-col instructor" onclick="sortClassSchedule(8)">Instructor</div>
      <div class="course-col seats"      onclick="sortClassSchedule(9)">Seats</div>
    </div>

    <div class="course-list-body">
      <?php foreach ($courses as $course) : ?>
        <?php
          $instructor_names = '';
          if (!empty($course['instructors']) && is_array($course['instructors'])) {
            $names = array_map(function($instructor) {
              return $instructor['name'];
            }, $course['instructors']);
            $instructor_names = implode(', ', $names);
          }

          $available = $course['enrl_capacity'] - $course['enrl_total'];

          $status_text = strtolower($course['enrl_status']);
          $status_indicator_class = 'open';
          if (strpos($status_text, 'wait') !== false) {
            $status_indicator_class = 'waitlist';
          } elseif (strpos($status_text, 'closed') !== false) {
            $status_indicator_class = 'closed';
          }

          $course_url = home_url('/course/' . $current_term . '/' . $course['class_nbr']);
        ?>
        <div class="course-row" data-status="<?php echo esc_attr($status_indicator_class); ?>">
          <div class="course-col status-col">
            <div class="status-indicator <?php echo esc_attr($status_indicator_class); ?>"></div>
          </div>
          <div class="course-col subject"><?php echo esc_html($course['subject']); ?></div>
          <div class="course-col course-num"><?php echo esc_html($course['catalog_nbr']); ?></div>
          <div class="course-col title">
            <a href="<?php echo esc_url($course_url); ?>"><?php echo esc_html($course['title']); ?></a>
          </div>
          <div class="course-col type"><?php echo esc_html($course['component']); ?></div>
          <div class="course-col days"><?php echo esc_html($course['meeting_days']); ?></div>
          <div class="course-col time"><?php echo esc_html($course['start_time'] . ' - ' . $course['end_time']); ?></div>
          <div class="course-col location"><?php echo esc_html($course['location']); ?></div>
          <div class="course-col instructor"><?php echo esc_html($instructor_names); ?></div>
          <div class="course-col seats"><?php echo esc_html($available . ' / ' . $course['enrl_capacity']); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>
