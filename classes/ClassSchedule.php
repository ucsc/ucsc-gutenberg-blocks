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
    add_filter('document_title_parts', array($this, 'course_detail_title'));
    add_action('rest_api_init', function () {
      register_rest_route('ucscgutenbergblocks/v1', '/classscheduledept/', array(
        'methods' => 'GET',
        'callback' => array($this, 'classscheduledept'),
        'permission_callback' => function() {return true;}
      ));
    });
  }

  function add_course_detail_rewrite() {
    // instead of something like index.php?course_term=2258&course_id=12345, users see /course/2258/12345/
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

  // a11y: set a descriptive <title> for course detail pages
  function course_detail_title($title_parts) {
    $term      = get_query_var('course_term');
    $course_id = get_query_var('course_id');

    if ($term && $course_id) {
      $request  = new WP_REST_Request('GET', '/ucsc/v1/course/' . $term . '/' . $course_id);
      $response = rest_do_request($request);

      if (!is_wp_error($response)) {
        $data    = $response->get_data();
        $primary = $data['primary_section'] ?? null;
        if ($primary) {
          $course_name = ($primary['subject'] ?? '') . ' ' . ($primary['catalog_nbr'] ?? '') . ' - ' . ($primary['title_long'] ?? 'Course Detail');
          $title_parts['title'] = trim($course_name);
        }
      }
    }

    return $title_parts;
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

    if ($subjectOrDept == 'dept') {
      $dept = strtoupper($attributes['department'] ?? '');
      if (empty($dept) || $dept === '---') {
        return array();
      }
      $param_key = 'dept';
      $param_value = $dept;
    } else {
      $subject = strtoupper($attributes['subject'] ?? '');
      if (empty($subject) || $subject === '---') {
        return array();
      }
      $param_key = 'subject';
      $param_value = $subject;
    }

    // Fetch via internal REST API call; caching is handled by Course_Schedule_API
    $request = new WP_REST_Request('GET', '/ucsc/v1/courses/' . $term);
    $request->set_query_params(array($param_key => $param_value));
    $response = rest_do_request($request);

    if (is_wp_error($response)) {
      return array();
    }

    return $response->get_data();
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
    $requested_term = sanitize_text_field( filter_input( INPUT_GET, 'class_schedule_term', FILTER_DEFAULT ) ?? '' );
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
