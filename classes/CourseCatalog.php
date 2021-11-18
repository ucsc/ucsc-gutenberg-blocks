<?php
class CourseCatalog
{
  function __construct()
  {
    add_action('init', array($this, 'renderFrontend'));
    add_action('admin_menu', array($this, 'settingsLink'));
    add_action('admin_init', array($this, 'settings'));
    add_action( 'rest_api_init', function () {
      register_rest_route( 'ucscgutenbergblocks/v1', '/course-catalog/', array(
        'methods' => 'GET',
        'callback' => array($this, 'getCourses')
        ));
    });
  }


  function settingsLink()
  {
    add_options_page('Course Catalog Settings', 'Course Catalog', 'manage_options', 'course-catalog-settings-page', array($this, 'settingsPageHTML'));
  }

  function settings()
  {
    add_settings_section('course-catalog-section', null, null, 'course-catalog-settings-page');
    add_settings_field('course-catalog-subject', 'Course Catalog Subject', array($this, 'subjectHTML'), 'course-catalog-settings-page', 'course-catalog-section');
    register_setting('course-catalog', 'course-catalog-subject', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
  }

  function subjectHTML()
  { ?>
    <input type="text" name="course-catalog-subject" value="<?php echo esc_attr(get_option('course-catalog-subject')) ?>" />
  <?php }



  function settingsPageHTML()
  { ?>
    <div class="wrap">
      <h1>Course Catalog Settings</h1>
      <form action="options.php" method="POST">
        <?php
        settings_fields('course-catalog');
        do_settings_sections('course-catalog-settings-page');
        submit_button();
        ?>
      </form>
    </div>
    <?php }



  function renderFrontend()
  {
    register_block_type('ucscblocks/coursecatalog', array(
      'editor_script' => 'ucscblocks',
      'render_callback' => array($this, 'theHTML')
    ));
  }


  function getCachedCourses($subject) {
    $lowerTitle = strtolower($subject);
    if (is_multisite()){
      $body = get_site_transient('course-catalog-' . $lowerTitle);
    } else {
      $body = get_transient('course-catalog-' . $lowerTitle);
    }
    if (!$body) {
      // Look for subject at network level settings
      $subject = strtolower(get_site_option('course-catalog-subject'));
      if (!strlen($subject)) {
        // if no key is found at network level, get key from site level
        $subject = strtolower(get_option('course-catalog-subject'));
      }
      $request_body = '<!--?xml version="1.0"?-->
      <catalog>
          <subject>' . $subject . '</subject>
      </catalog>';
      $args = array(
        'body' => $request_body,
        'headers' => array(
          'Host' => 'my.prd.ais.aws.ucsc.edu',
          'Content-Type' => 'text/xml',
          'OperationName' => 'SCX_SERVICE_CTLG.v1',
          'From' => 'SCX_CTLG_TARGET',
          'To' => 'PSFT_CSPRD',
          'Connection' => 'close',
          'Cookie' => 'ROUTEID=.CSWEB1; AWSELB=37353D3D08AD73923AF46389C63E613E323FBAA9DFC82182E35FFF861EB914BAEF6182B6091A04D24D123FB954C7865F2D40E142E380F4B165EAB96D28B758CE5FC92F57B7; AWSELBCORS=37353D3D08AD73923AF46389C63E613E323FBAA9DFC82182E35FFF861EB914BAEF6182B6091A04D24D123FB954C7865F2D40E142E380F4B165EAB96D28B758CE5FC92F57B7'
        ),
      );
      $response = wp_remote_post("https://my.prd.ais.aws.ucsc.edu:443/PSIGW/HttpListeningConnector", $args);
      $body = wp_remote_retrieve_body($response);

      if (is_multisite()) {
        set_site_transient('course-catalog-' . $lowerTitle, $body, WEEK_IN_SECONDS);
      } else {
        set_transient('course-catalog-' . $lowerTitle, $body, WEEK_IN_SECONDS);
      }
    }
    $xmlBody = simplexml_load_string($body);
    return $xmlBody;
  }
  function getCourses()
  {
    $subject = strtolower(get_site_option('course-catalog-subject'));
    if (!strlen($subject)) {
      // if no key is found at network level, get key from site level
      $subject = strtolower(get_option('course-catalog-subject'));
    }
    $cachedData = $this->getCachedCourses($subject);
    return $cachedData;
  }

  function theHTML($attributes)
  {
    ob_start();
    $subject = strtolower(get_site_option('course-catalog-subject'));
    if (!strlen($subject)) {
      // if no key is found at network level, get key from site level
      $subject = strtolower(get_option('course-catalog-subject'));
    }
    $courses = $this->getCourses();

    echo '<table>';
    echo '<tr><th>Course #</th><th>Course Title</th><th>Course Level</th><th>Units</th></thr>';
    foreach ($courses->course as $course) {
      echo '<tr><td>' . $course->crse_id . '</td><td>' . $course->title . '</td><td>' . $course->level . '</td><td>' . $course->units . '</td></tr>';
    }
    echo '</table>';

    $output = ob_get_contents(); // collect output
    ob_end_clean(); // Turn off ouput buffer

    return $output;
  }

}
