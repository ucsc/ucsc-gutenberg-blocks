<?php

class CourseCatalog
{
    function __construct() {
        add_action('init', array($this, 'renderFrontend'));
        add_action('rest_api_init', function () {
            register_rest_route('ucscgutenbergblocks/v1', '/course-catalog/', array(
                'methods' => 'GET',
                'callback' => array($this, 'getCourses'),
                'permission_callback' => array($this, 'restPermissionsCheck')
            ));
        });
        add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));
    }

    function restPermissionsCheck() {
        // Restrict endpoint to only users who have the edit_posts capability.
        if ( ! current_user_can( 'edit_posts' ) ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You can not view private data.', 'my-text-domain' ), array( 'status' => 401 ) );
        }

        return true;
    }

    public function register_plugin_styles() {
        $file = '../src/components/CourseCatalog/tablesorter.js';
        wp_register_script(
                'tablesorterjs',
                plugins_url($file, __FILE__),
                array(),
                filemtime(plugin_dir_path(__FILE__) . $file),
                true
        );
        wp_enqueue_script('tablesorterjs');
        $file = '../src/components/CourseCatalog/tablesorter.css';
        wp_register_style(
                'tablesorter',
                plugins_url($file, __FILE__),
                array(),
                filemtime(plugin_dir_path(__FILE__) .$file)
        );
        wp_enqueue_style('tablesorter');
        $file = '../src/components/CourseCatalog/coursecatalog.css';
        wp_register_style(
            'coursecatalog',
            plugins_url($file, __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) .$file)
        );
        wp_enqueue_style('coursecatalog');
    }

    function renderFrontend() {
        register_block_type('ucscblocks/coursecatalog', array(
            'editor_script' => 'ucscblocks',
            'render_callback' => array($this, 'theHTML')
        ));
    }

    function getCachedCourses($subject) {
        $lowerTitle = strtolower($subject);
        $body = get_transient('course-catalog-' . $lowerTitle);
        if (!$body) {
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
            set_transient('course-catalog-' . $lowerTitle, $body, WEEK_IN_SECONDS);
        }
        $xmlBody = simplexml_load_string($body);
        return $xmlBody;
    }

    function getCourses() {
        $subject = strtolower(get_option('course_catalog_subject'));
        $cachedData = $this->getCachedCourses($subject);
        return $cachedData;
    }

    function theHTML($attributes) {
        ob_start();
        $subject = strtolower(get_site_option('course-catalog-subject'));
        if (!strlen($subject)) {
            // if no key is found at network level, get key from site level
            $subject = strtolower(get_option('course-catalog-subject'));
        }
        $courses = $this->getCourses();
        echo '<div id="courseCatalog"><div>
    <label>
      Search Dept Courses:<input type="text" id="Search" onkeyup="tableSearch()"
        placeholder="search table">
    </label><br/>
    View titles & course descriptions for department course offerings<br/>
    Click a course\'s title to read its description.
    <label>
      <button id="expandAll">View All</button>
    </label>
    <label>
      <button id="collapseAll">Collapse All</button>
    </label>
  </div>';
        echo '<table class="table-sortable" id="tableSorter">';
        echo '<thead><tr><th>Course #</th><th>Course Title</th><th>Course Level</th><th>Units</th></tr></thead>';
        echo '<tbody>';
        foreach ($courses->course as $course) {
            // build a value for the course level to enable logical sorting instead of alphanumeric
            switch ($course->level) {
                case 'Lower Division':
                    $lvlval = 1;
                    break;
                case 'Upper Division':
                    $lvlval = 2;
                    break;
                case 'Graduate':
                    $lvlval = 3;
                    break;
            }
            // the class 'intsort' isn't really necessary at this point, in the future it could signal a type of sorting
            // the class 'secret' is used to include a value, but not display it. the name is such because 'hidden' was already used
            echo '<tr class="pointer"><td>' . $course->subject . '  <span class="intsort">' .$course->catalog_nbr .'</span></td><td>' . $course->title . '</td><td>' . $course->level . '<span class="secret">' . $lvlval . '</span></td><td>' . $course->units . ' Units</td></tr>';
            echo '<tr class="hidden"><td colspan="4"><p>' . $course->description . '</p></td></tr>';
        }
        echo '</tbody></table></div>';

        $output = ob_get_contents(); // collect output
        ob_end_clean(); // Turn off ouput buffer

        return $output;
    }

}
