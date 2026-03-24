<?php
/**
 * Class Schedule block template.
 *
 * Variables available from ClassSchedule::theHTML():
 *   $courses       array   Course data from the API
 *   $current_term  string  Active term code
 *   $terms_data    array   Full terms response (terms_data['terms'])
 *   $attributes    array   Block attributes (department, subject, subjectOrDept)
 */
$terms = $terms_data['terms'] ?? [];
?>
<div id="classSchedule">

  <div class="introText no-print">
    <label for="quarterDropdown" class="screen-reader-text">Select Quarter</label>
    <label for="courseSearch" class="screen-reader-text">Search Schedule</label>

    <div class="input-with-select">
      <div class="term-select-wrap">
        <!-- a11y: no inline onchange to avoid jump menu warning; change handled via addEventListener in classschedule.js -->
        <select id="quarterDropdown" aria-label="Select Quarter">
          <?php foreach ($terms as $term) : ?>
            <option value="<?php echo esc_attr($term['code']); ?>"
              <?php selected($term['code'], $current_term); ?>>
              <?php echo esc_html($term['description']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <input type="text" id="courseSearch" placeholder="Search Schedule" aria-label="Search Schedule" onkeyup="classScheduleSearch(event)">
    </div>

    <div class="button-group">
      <button id="filterButton" class="filter-button" onclick="openFilterModal()" title="Filter Options">
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="icon-filter"><path fill="currentColor" d="M487.976 0H24.028C2.71 0-8.047 25.866 7.058 40.971L192 225.941V432c0 7.831 3.821 15.17 10.237 19.662l80 55.98C298.02 518.69 320 507.493 320 487.98V225.941l184.947-184.97C520.021 25.896 509.338 0 487.976 0z"/></svg>
        <span>Filter</span>
      </button>
      <button class="filter-button" onclick="classScheduleCopyUrl()" title="Copy URL" aria-label="Copy URL">
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="icon-filter"><path fill="currentColor" d="M336 64h-80c0-35.3-28.7-64-64-64s-64 28.7-64 64H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM192 40c13.3 0 24 10.7 24 24s-10.7 24-24 24-24-10.7-24-24 10.7-24 24-24zm144 418c0 3.3-2.7 6-6 6H54c-3.3 0-6-2.7-6-6V118c0-3.3 2.7-6 6-6h42v36c0 6.6 5.4 12 12 12h168c6.6 0 12-5.4 12-12v-36h42c3.3 0 6 2.7 6 6z"/></svg>
      </button>
      <button class="filter-button" onclick="classScheduleDownloadCSV()" title="Download CSV" aria-label="Download CSV">
        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="icon-filter"><path fill="currentColor" d="M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm76.45 211.36l-96.42 95.7c-6.65 6.61-17.39 6.61-24.04 0l-96.42-95.7C73.42 337.29 80.54 320 94.82 320H160v-80c0-8.84 7.16-16 16-16h32c8.84 0 16 7.16 16 16v80h65.18c14.28 0 21.4 17.29 11.27 27.36zM377 105L279.1 7c-4.5-4.5-10.6-7-17-7H256v128h128v-6.1c0-6.3-2.5-12.4-7-16.9z"/></svg>
      </button>
    </div>
  </div>

  <div id="filterModal" class="filter-modal" role="dialog" aria-modal="true" aria-labelledby="filterModalTitle">
    <div class="filter-modal-content">
      <h2 id="filterModalTitle" class="screen-reader-text">Filter Options</h2>
      <div class="divider">
        <strong>Display Columns</strong>
        <div class="column-toggles">
          <label><input type="checkbox" class="column-toggle" data-column="seats" checked> Seats</label>
          <label><input type="checkbox" class="column-toggle" data-column="days" checked> Days</label>
          <label><input type="checkbox" class="column-toggle" data-column="time"> Time</label>
          <label><input type="checkbox" class="column-toggle" data-column="location"> Location</label>
          <label><input type="checkbox" class="column-toggle" data-column="instructor"> Instructor</label>
          <label><input type="checkbox" class="column-toggle" data-column="class-num"> Class #</label>
          <label><input type="checkbox" class="column-toggle" data-column="enrollment"> Enrollment</label>
        </div>
      </div>

      <div class="divider">
        <strong>Status</strong>
        <div class="status-filters">
          <label><input type="checkbox" class="status-filter" data-status="open" checked> Open</label>
          <label><input type="checkbox" class="status-filter" data-status="closed" checked> Closed</label>
          <label><input type="checkbox" class="status-filter" data-status="waitlist" checked> Wait List</label>
        </div>
      </div>

      <div class="advButtons">
        <button class="reset-filters" onclick="resetFilters()">Reset all filters</button>
        <div>
          <button class="apply-button" onclick="applyFilters()">Apply</button>
          <button class="cancel-button" onclick="closeFilterModal()">Cancel</button>
        </div>
      </div>
    </div>
  </div>

  <div class="display-key no-print">
    <div id="classCount" aria-live="polite">Displaying <strong><?php echo count($courses); ?></strong> classes</div>
    <div class="right">
      <span class="open" aria-hidden="true"></span>Open
      <span class="closed" aria-hidden="true"></span>Closed
      <span class="waitlist" aria-hidden="true"></span>Closed w/ Wait List
    </div>
  </div>

  <!-- a11y: uses divs with ARIA table roles instead of <table> elements to avoid "layout table" scanner warnings -->
  <div class="el-table" id="classScheduleTable" role="table" aria-label="Class Schedule">
    <div class="el-table__header" role="rowgroup">
      <div class="el-table__header-row" role="row">
        <div class="col-status" role="columnheader"><div class="cell"><span class="screen-reader-text">Status</span></div></div>
        <!-- a11y: inner button elements handle both mouse and keyboard activation natively -->
        <div class="col-course-id is-sortable" role="columnheader"><button type="button" class="cell" onclick="sortClassSchedule(1)">Course ID<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></button></div>
        <div class="col-title is-sortable" role="columnheader"><button type="button" class="cell" onclick="sortClassSchedule(2)">Title<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></button></div>
        <div class="col-seats is-sortable" role="columnheader"><button type="button" class="cell" onclick="sortClassSchedule(3)">Seats<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></button></div>
        <div class="col-days is-sortable" role="columnheader"><button type="button" class="cell" onclick="sortClassSchedule(4)">Days<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></button></div>
        <div class="col-time is-sortable hidden" role="columnheader"><button type="button" class="cell" tabindex="-1" onclick="sortClassSchedule(5)">Time<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></button></div>
        <div class="col-location is-sortable hidden" role="columnheader"><button type="button" class="cell" tabindex="-1" onclick="sortClassSchedule(6)">Location<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></button></div>
        <div class="col-instructor is-sortable hidden" role="columnheader"><button type="button" class="cell" tabindex="-1" onclick="sortClassSchedule(7)">Instructor<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></button></div>
        <div class="col-class-num is-sortable hidden" role="columnheader"><button type="button" class="cell" tabindex="-1" onclick="sortClassSchedule(8)">Class #<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></button></div>
        <div class="col-enrollment is-sortable hidden" role="columnheader"><button type="button" class="cell" tabindex="-1" onclick="sortClassSchedule(9)">Enrollment<span class="caret-wrapper"><i class="sort-caret ascending"></i><i class="sort-caret descending"></i></span></button></div>
      </div>
    </div>

    <div class="el-table__body" role="rowgroup">
      <?php foreach ($courses as $course) : ?>
        <?php
          $available        = max(0, $course['enrl_capacity'] - $course['enrl_total']);
          $status_text      = strtolower($course['enrl_status']);
          $status_class     = 'open';
          if (strpos($status_text, 'wait') !== false) {
            $status_class = 'waitlist';
          } elseif (strpos($status_text, 'closed') !== false) {
            $status_class = 'closed';
          }
          $is_cancelled     = strtolower(trim($course['meeting_days'] ?? '')) === 'cancelled';
          $course_id        = esc_html($course['subject'] . '-' . $course['catalog_nbr']);
          $course_url       = home_url('/course/' . $current_term . '/' . $course['class_nbr']);
          $instructor_html = '';
          if (!empty($course['instructors']) && is_array($course['instructors'])) {
            $inst_parts = [];
            foreach ($course['instructors'] as $inst) {
              $iname = $inst['name'] ?? '';
              $icruzid = $inst['cruzid'] ?? '';
              if ($iname === '') continue;
              if ($icruzid && $iname !== 'Staff') {
                $inst_parts[] = '<a href="' . esc_url(home_url('/directory/' . $icruzid)) . '">' . esc_html($iname) . '</a>';
              } else {
                $inst_parts[] = esc_html($iname);
              }
            }
            $instructor_html = implode(', ', $inst_parts);
          }
        ?>
        <div class="el-table__row course-row" role="row" data-status="<?php echo esc_attr($status_class); ?>">
          <?php
            $status_label = 'Open';
            if ($status_class === 'closed') $status_label = 'Closed';
            elseif ($status_class === 'waitlist') $status_label = 'Closed with Wait List';
          ?>
          <div class="col-status" role="cell"><div class="cell"><span class="<?php echo esc_attr($status_class); ?>" aria-label="<?php echo esc_attr($status_label); ?>" role="img"></span></div></div>
          <div class="col-course-id" role="cell"><div class="cell"><span><?php echo $course_id; ?></span></div></div>
          <div class="col-title" role="cell"><div class="cell">
            <a href="<?php echo esc_url($course_url); ?>"<?php if ($is_cancelled) echo ' class="cancelled"'; ?>><?php echo esc_html($course['title']); ?></a>
          </div></div>
          <div class="col-seats" role="cell"><div class="cell">
            <span class="seats"><span class="available"><?php echo esc_html($available); ?> open</span> / <?php echo esc_html($course['enrl_capacity']); ?> total</span>
          </div></div>
          <div class="col-days" role="cell"><div class="cell"><span><?php echo $is_cancelled ? 'Cancelled' : esc_html($course['meeting_days']); ?></span></div></div>
          <div class="col-time hidden" role="cell"><div class="cell"><span><?php echo esc_html($course['start_time'] . ' - ' . $course['end_time']); ?></span></div></div>
          <div class="col-location hidden" role="cell"><div class="cell"><span><?php echo esc_html($course['location']); ?></span></div></div>
          <div class="col-instructor hidden" role="cell"><div class="cell"><span><?php echo $instructor_html; ?></span></div></div>
          <div class="col-class-num hidden" role="cell"><div class="cell"><span><?php echo esc_html($course['class_nbr']); ?></span></div></div>
          <div class="col-enrollment hidden" role="cell"><div class="cell"><span><?php echo esc_html($course['enrl_total']); ?></span></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
