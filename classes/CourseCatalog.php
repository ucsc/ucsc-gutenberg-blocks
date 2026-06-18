<?php

class CourseCatalog
{
    const PEOPLESOFT_TARGETS = array(
        'prod' => array(
            'host' => 'my.prd.ais.aws.ucsc.edu',
            'to' => 'PSFT_CSPRD',
        ),
        'csqa' => array(
            'host' => 'my.qa.ais.aws.ucsc.edu',
            'to' => 'PSFT_CSQA',
        ),
    );

    function __construct() {
        add_action('init', array($this, 'renderFrontend'));

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
    }

    function renderFrontend() {
        register_block_type('ucscblocks/coursecatalog', array(
            'editor_script' => 'ucscblocks',
            'render_callback' => array($this, 'theHTML')
        ));
    }

    function normalizePeopleSoftTarget($target) {
        $target = strtolower(trim((string) $target));
        if ($target == 'qa' || $target == 'test') {
            $target = 'csqa';
        }

        if (!array_key_exists($target, self::PEOPLESOFT_TARGETS)) {
            $target = 'prod';
        }

        return $target;
    }

    function getRequestValue($names) {
        foreach ($names as $name) {
            if (isset($_GET[$name])) {
                return is_array($_GET[$name]) ? '' : wp_unslash($_GET[$name]);
            }
        }

        return null;
    }

    function getBooleanConfig($constantName, $envName, $default = false) {
        $value = $default;
        if (defined($constantName)) {
            $value = constant($constantName);
        } elseif (getenv($envName) !== false) {
            $value = getenv($envName);
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    function isRequestOverrideAllowed() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            return false;
        }

        if ($this->getBooleanConfig('UCSC_COURSE_CATALOG_ALLOW_REQUEST_OVERRIDE', 'UCSC_COURSE_CATALOG_ALLOW_REQUEST_OVERRIDE')) {
            return true;
        }

        $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
        $allowedHosts = array(
            'wp-dev.ucsc',
            'wordpress-dev.ucsc.edu',
            'localhost',
            '127.0.0.1',
        );

        return in_array($host, $allowedHosts, true);
    }

    function getRequestTargetOverride() {
        if (!$this->isRequestOverrideAllowed()) {
            return null;
        }

        return $this->getRequestValue(array(
            'ucsc_course_catalog_target',
            'ucsc-course-catalog-target',
        ));
    }

    function getPeopleSoftTarget() {
        $target = 'prod';
        if (defined('UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET')) {
            $target = constant('UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET');
        } elseif (getenv('UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET')) {
            $target = getenv('UCSC_COURSE_CATALOG_PEOPLESOFT_TARGET');
        }

        $requestTarget = $this->getRequestTargetOverride();
        if ($requestTarget !== null && $requestTarget !== '') {
            $target = $requestTarget;
        }

        $target = $this->normalizePeopleSoftTarget($target);
        $config = self::PEOPLESOFT_TARGETS[$target];
        $config['target'] = $target;
        $config['url'] = 'https://' . $config['host'] . ':443/PSIGW/HttpListeningConnector';

        return $config;
    }

    function shouldBypassCache() {
        $bypassCache = $this->getBooleanConfig('UCSC_COURSE_CATALOG_BYPASS_CACHE', 'UCSC_COURSE_CATALOG_BYPASS_CACHE');
        if ($this->isRequestOverrideAllowed()) {
            $requestBypassCache = $this->getRequestValue(array(
                'ucsc_course_catalog_bypass_cache',
                'ucsc-course-catalog-bypass-cache',
            ));
            if ($requestBypassCache !== null) {
                $bypassCache = filter_var($requestBypassCache, FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $bypassCache;
    }

    static function clearCachedCourses($target = 'all') {
        global $wpdb;

        $target = strtolower(trim($target));
        if ($target == 'qa' || $target == 'test') {
            $target = 'csqa';
        }

        if ($target && $target != 'all') {
            $transientPrefix = 'course-catalog-' . $target . '-';
        } else {
            $transientPrefix = 'course-catalog-';
        }

        $transientLike = $wpdb->esc_like('_transient_' . $transientPrefix) . '%';
        $timeoutLike = $wpdb->esc_like('_transient_timeout_' . $transientPrefix) . '%';

        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                $transientLike,
                $timeoutLike
            )
        );
    }

    // function getCachedCourses($subject, $subjectOrDept) {
    function getCachedCourses($attributes) {
        $peopleSoftTarget = $this->getPeopleSoftTarget();
        $subjectOrDept = $attributes['subjectOrDept'];
        $queryStr = "";
        if ($subjectOrDept == 'dept') {
            $lowerTitle = strtolower($attributes['department']);
            $queryStr = '<acad_org>' . $lowerTitle . '</acad_org>';

        } else {
            $lowerTitle = strtolower($attributes['subject']);
            $queryStr = '<subject>' . $lowerTitle . '</subject>';
        }
        $cacheKey = 'course-catalog-' . $peopleSoftTarget['target'] . '-' . $lowerTitle . '-' . $subjectOrDept;
        $body = $this->shouldBypassCache() ? false : get_transient($cacheKey);
        if (!$body) {
            $request_body = '<?xml version="1.0"?>
      <catalog>
          ' . $queryStr . '
      </catalog>';
            $args = array(
                'body' => $request_body,
                'headers' => array(
                    'Host' => $peopleSoftTarget['host'],
                    'Content-Type' => 'text/xml',
                    'OperationName' => 'SCX_SERVICE_CTLG.v1',
                    'From' => 'SCX_CTLG_TARGET',
                    'To' => $peopleSoftTarget['to'],
                    'Connection' => 'close'
                ),
                'timeout' => 30,
            );

            $response = wp_remote_post($peopleSoftTarget['url'], $args);
            if (is_wp_error($response)) {
                return $response;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            if ($response_code < 200 || $response_code >= 300) {
                return new WP_Error(
                    'course_catalog_feed_error',
                    'Course Catalog feed request failed.',
                    array(
                        'status' => $response_code,
                        'target' => $peopleSoftTarget['target'],
                    )
                );
            }

            if (!$this->shouldBypassCache()) {
                set_transient($cacheKey, $body, WEEK_IN_SECONDS);
            }
        }

        libxml_use_internal_errors(true);
        $xmlBody = simplexml_load_string($body);
        if (!$xmlBody) {
            libxml_clear_errors();
            return new WP_Error(
                'course_catalog_feed_xml_error',
                'Course Catalog feed returned invalid XML.',
                array(
                    'target' => $peopleSoftTarget['target'],
                )
            );
        }
        libxml_clear_errors();

        return $xmlBody;
    }

    function theHTML($attributes) {
        ob_start();
        $courses = $this->getCachedCourses($attributes);
        $extraAttributes = get_block_wrapper_attributes(['id' => 'courseCatalog']);
        echo '<div ' . $extraAttributes . '>
    ';
        if (is_wp_error($courses)) {
            echo '<p class="introText">Course Catalog data is temporarily unavailable.</p>';
            echo '</div>';

            $output = ob_get_contents();
            ob_end_clean();

            return $output;
        }

        echo '
    <div class="introText">
        <label>
        Search Courses:<input type="text" id="search" onkeyup="tableSearch(event)">
        </label>
    </div>
    <div class="introText clickText">
        <label>
            Select a course title for details.
            <a id="expandAll" class="expandAll collapseExpandLinks pointer">Expand all</a>
            <a id="collapseAll" class="collapseAll collapseExpandLinks pointer">Collapse all</a>
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
            echo '<tr class="pointer"><td>' . $course->subject . '  <span class="intsort">' .$course->catalog_nbr .'</span></td><td class="collapseExpandText">' . $course->title . '</td><td>' . $course->level . '<span class="secret">' . $lvlval . '</span></td><td>' . $course->units . ' Units</td></tr>';
            echo '<tr class="hide"><td colspan="4"><p>' . $course->description . '</p></td></tr>';
        }
        echo '</tbody></table></div>';

        $output = ob_get_contents(); // collect output
        ob_end_clean(); // Turn off ouput buffer

        return $output;
    }

}
