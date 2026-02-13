<?php

class ClassSchedule
{
  function __construct()
  {
    add_action('init', array($this, 'adminAssets'));
    add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));
    add_action('rest_api_init', function () {
      register_rest_route('ucscgutenbergblocks/v1', '/classscheduledept/', array(
        'methods' => 'GET',
        'callback' => array($this, 'classscheduledept'),
        'permission_callback' => function() {return true;}
      ));
    });
  }

  function classscheduledept() {
    $resp = [];
    $resp["dept"] = get_option('class_schedule_department');

    return new WP_REST_Response($resp);
  }

  public function register_plugin_styles() {
    $js_file = '../src/components/ClassSchedule/classschedule.js';
    wp_register_script(
      'classschedule-js',
      plugins_url($js_file, __FILE__),
      array(),
      filemtime(plugin_dir_path(__FILE__) . $js_file),
      true
    );
    wp_enqueue_script('classschedule-js');

    $css_file = '../src/components/ClassSchedule/classschedule.css';
    wp_register_style(
      'classschedule',
      plugins_url($css_file, __FILE__),
      array(),
      filemtime(plugin_dir_path(__FILE__) . $css_file)
    );
    wp_enqueue_style('classschedule');
  }

  function adminAssets()
  {
    register_block_type('ucscblocks/classschedule', array(
      'editor_script' => 'ucscblocks',
      'render_callback' => array($this, 'theHTML')
    ));
  }

  function getCachedCourses($term, $attributes) {
    $subjectOrDept = $attributes['subjectOrDept'];
    $cache_key = 'class-schedule-' . $term . '-';

    if ($subjectOrDept == 'dept') {
      $dept = strtoupper($attributes['department']);
      $cache_key .= 'dept-' . $dept;
      $query_param = 'dept=' . $dept;
    } else {
      $subject = strtoupper($attributes['subject']);
      $cache_key .= 'subject-' . $subject;
      $query_param = 'subject=' . $subject;
    }

    // Try to get cached data
    $courses_data = get_transient($cache_key);

    if (!$courses_data) {
      // Fetch from API
      $courses_url = home_url('/wp-json/ucsc/v1/courses/' . $term . '?' . $query_param);
      $response = wp_remote_get($courses_url);

      if (is_wp_error($response)) {
        return array();
      }

      $courses_data = json_decode(wp_remote_retrieve_body($response), true);

      // Cache for 15 minutes
      set_transient($cache_key, $courses_data, 15 * MINUTE_IN_SECONDS);
    }

    return $courses_data;
  }

  function theHTML($attributes)
  {
    ob_start();

    // Get the current term
    $terms_response = wp_remote_get(home_url('/wp-json/ucsc/v1/terms'));

    if (is_wp_error($terms_response)) {
      echo '<p>Error loading terms. Please try again later.</p>';
      return ob_get_clean();
    }

    $terms_data = json_decode(wp_remote_retrieve_body($terms_response), true);

    if (empty($terms_data['terms'])) {
      echo '<p>No terms available.</p>';
      return ob_get_clean();
    }

    // Find the default term
    $current_term = null;
    foreach ($terms_data['terms'] as $term) {
      if ($term['default'] === 'Y') {
        $current_term = $term['code'];
        break;
      }
    }

    if (!$current_term) {
      $current_term = $terms_data['terms'][0]['code'];
    }

    // Get courses
    $courses_data = $this->getCachedCourses($current_term, $attributes);

    if (empty($courses_data['classes'])) {
      echo '<p>No courses found for the selected criteria.</p>';
      return ob_get_clean();
    }

    $courses = $courses_data['classes'];

    // Render the table
    echo '<div id="classSchedule">';
    echo '<div class="introText">';
    echo '<label>Search Courses: <input type="text" id="courseSearch" onkeyup="classScheduleSearch(event)"></label>';
    echo '</div>';
    echo '<table class="table-sortable" id="classScheduleTable">';
    echo '<thead><tr>';
    echo '<th onclick="sortClassSchedule(0)">Subject</th>';
    echo '<th onclick="sortClassSchedule(1)">Course #</th>';
    echo '<th onclick="sortClassSchedule(2)">Title</th>';
    echo '<th onclick="sortClassSchedule(3)">Type</th>';
    echo '<th onclick="sortClassSchedule(4)">Days</th>';
    echo '<th onclick="sortClassSchedule(5)">Time</th>';
    echo '<th onclick="sortClassSchedule(6)">Location</th>';
    echo '<th onclick="sortClassSchedule(7)">Instructor</th>';
    echo '<th onclick="sortClassSchedule(8)">Status</th>';
    echo '<th onclick="sortClassSchedule(9)">Seats</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($courses as $course) {
      $instructor_names = '';
      if (!empty($course['instructors']) && is_array($course['instructors'])) {
        $names = array_map(function($instructor) {
          return $instructor['name'];
        }, $course['instructors']);
        $instructor_names = implode(', ', $names);
      }

      $available = $course['enrl_capacity'] - $course['enrl_total'];
      $status_class = 'status-' . strtolower($course['enrl_status']);

      echo '<tr>';
      echo '<td>' . esc_html($course['subject']) . '</td>';
      echo '<td>' . esc_html($course['catalog_nbr']) . '</td>';
      echo '<td>' . esc_html($course['title']) . '</td>';
      echo '<td>' . esc_html($course['component']) . '</td>';
      echo '<td>' . esc_html($course['meeting_days']) . '</td>';
      echo '<td>' . esc_html($course['start_time'] . ' - ' . $course['end_time']) . '</td>';
      echo '<td>' . esc_html($course['location']) . '</td>';
      echo '<td>' . esc_html($instructor_names) . '</td>';
      echo '<td class="' . $status_class . '">' . esc_html($course['enrl_status']) . '</td>';
      echo '<td>' . esc_html($available . ' / ' . $course['enrl_capacity']) . '</td>';
      echo '</tr>';
    }

    echo '</tbody></table></div>';

    $output = ob_get_contents();
    ob_end_clean();

    return $output;
  }
}
