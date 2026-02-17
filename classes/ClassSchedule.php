<?php

class ClassSchedule
{
  function __construct()
  {
    add_action('init', array($this, 'adminAssets'));
    add_action('init', array($this, 'add_course_detail_rewrite'));
    add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));
    add_filter('query_vars', array($this, 'add_query_vars'));
    add_action('template_include', array($this, 'course_detail_template'));
    add_action('rest_api_init', function () {
      register_rest_route('ucscgutenbergblocks/v1', '/classscheduledept/', array(
        'methods' => 'GET',
        'callback' => array($this, 'classscheduledept'),
        'permission_callback' => function() {return true;}
      ));
    });
  }

  function add_course_detail_rewrite() {
    add_rewrite_rule(
      '^course/([0-9]+)/([0-9]+)/?$',
      'index.php?course_term=$matches[1]&course_id=$matches[2]',
      'top'
    );
  }

  function add_query_vars($query_vars) {
    $query_vars[] = 'course_term';
    $query_vars[] = 'course_id';
    return $query_vars;
  }

  function course_detail_template($template) {
    if (get_query_var('course_term') && get_query_var('course_id')) {
      return plugin_dir_path(__FILE__) . '../templates/CourseDetailTemplate.php';
    }
    return $template;
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
    $subjectOrDept = $attributes['subjectOrDept'] ?? 'dept';
    $cache_key = 'class-schedule-' . $term . '-';

    if ($subjectOrDept == 'dept') {
      $dept = strtoupper($attributes['department'] ?? '');
      // Skip if department is empty or default value
      if (empty($dept) || $dept === '---') {
        return array();
      }
      $cache_key .= 'dept-' . $dept;
      $query_param = 'dept=' . $dept;
    } else {
      $subject = strtoupper($attributes['subject'] ?? '');
      // Skip if subject is empty or default value
      if (empty($subject) || $subject === '---') {
        return array();
      }
      $cache_key .= 'subject-' . $subject;
      $query_param = 'subject=' . $subject;
    }

    // Try to get cached data
    $courses_data = get_transient($cache_key);

    if (!$courses_data) {
      // Fetch from API using internal REST API call
      $request = new WP_REST_Request('GET', '/ucsc/v1/courses/' . $term);
      $request->set_query_params(array($subjectOrDept == 'dept' ? 'dept' : 'subject' => $subjectOrDept == 'dept' ? strtoupper($attributes['department'] ?? '') : strtoupper($attributes['subject'] ?? '')));
      $response = rest_do_request($request);

      if (is_wp_error($response)) {
        return array();
      }

      $courses_data = $response->get_data();

      // Cache for 15 minutes
      set_transient($cache_key, $courses_data, 15 * MINUTE_IN_SECONDS);
    }

    return $courses_data;
  }

  function theHTML($attributes)
  {
    // Get the current term using internal REST API call
    $request = new WP_REST_Request('GET', '/ucsc/v1/terms');
    $response = rest_do_request($request);

    if (is_wp_error($response)) {
      return '<p>Error loading terms. Please try again later.</p>';
    }

    $terms_data = $response->get_data();

    if (empty($terms_data['terms'])) {
      return '<p>No terms available.</p>';
    }

    // Allow a specific term to be selected via query param
    $current_term = null;
    $requested_term = sanitize_text_field($_GET['class_schedule_term'] ?? '');
    if ($requested_term) {
      foreach ($terms_data['terms'] as $term) {
        if ($term['code'] === $requested_term) {
          $current_term = $term['code'];
          break;
        }
      }
    }

    // Fall back to the default term
    if (!$current_term) {
      foreach ($terms_data['terms'] as $term) {
        if ($term['default'] === 'Y') {
          $current_term = $term['code'];
          break;
        }
      }
    }

    if (!$current_term) {
      $current_term = $terms_data['terms'][0]['code'];
    }

    // Get courses
    $courses_data = $this->getCachedCourses($current_term, $attributes);

    if (empty($courses_data['classes'])) {
      $subjectOrDept = $attributes['subjectOrDept'] ?? 'dept';
      $selected_value = $subjectOrDept == 'dept' ? ($attributes['department'] ?? '') : ($attributes['subject'] ?? '');

      if (empty($selected_value) || $selected_value === '---') {
        return '<p>Please select a department or subject from the block settings to view courses.</p>';
      }

      return '<p>No courses found for the selected criteria.</p>';
    }

    $courses = $courses_data['classes'];

    ob_start();
    include(plugin_dir_path(__FILE__) . '../templates/ClassScheduleTemplate.php');
    return ob_get_clean();
  }
}
