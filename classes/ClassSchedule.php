<?php

class ClassSchedule
{
  function __construct()
  {
    add_action('init', array($this, 'adminAssets'));
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

  function adminAssets()
  {
    register_block_type('ucscblocks/classschedule', array(
      'editor_script' => 'ucscblocks',
      'render_callback' => array($this, 'theHTML')
    ));
    wp_register_style(
      'ucscblocks-editor',
      plugins_url('//webapps.ucsc.edu/wcsi/css/app.css', __FILE__),
      array('wp-edit-blocks')
    );
    wp_register_style( 'classschedule',
      plugins_url('../src/components/ClassSchedule/classschedule.css', __FILE__),
      array(),
      filemtime(plugin_dir_path(__FILE__) . '../src/components/ClassSchedule/classschedule.css'));
    wp_enqueue_style( 'classschedule');
  }

  function theHTML($attributes)
  {
    $deptOrSubAttribute = '';
    if ($attributes['subjectOrDept'] == 'dept') {
      $deptOrSubAttribute = 'department="' . $attributes['department']  . '"';
    } else {
      $deptOrSubAttribute = 'subject="' . $attributes['subject']  . '" department=""';
    }
    $markup = '
      <link rel="stylesheet" href="https://webapps.ucsc.edu/wcsi/css/app.css">
      <div id="wcsi" ' . $deptOrSubAttribute . ' >

      </div>
      <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=default,Promise,Object.assign,Object.values,Array.prototype.find,Array.prototype.findIndex,Array.prototype.includes,String.prototype.includes,String.prototype.startsWith,String.prototype.endsWith"></script>
      <script src="https://webapps.ucsc.edu/wcsi/js/manifest.js"></script>
      <script src="https://webapps.ucsc.edu/wcsi/js/vendor.js"></script>
      <script src="https://webapps.ucsc.edu/wcsi/js/app.js"></script>';
    return $markup;
  }
}
