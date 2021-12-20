<?php

class ClassSchedule
{
  function __construct()
  {
    add_action('init', array($this, 'adminAssets'));
  }

  function adminAssets()
  {
    register_block_type('ucscblocks/classschedule', array(
      'editor_script' => 'ucscblocks',
      'render_callback' => array($this, 'theHTML')
    ));
  }

  function theHTML($attributes)
  {
    $markup = '
      <link rel="stylesheet" href="https://webapps.ucsc.edu/wcsi/css/app.css">
      <div id="wcsi">

      </div>
      <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=default,Promise,Object.assign,Object.values,Array.prototype.find,Array.prototype.findIndex,Array.prototype.includes,String.prototype.includes,String.prototype.startsWith,String.prototype.endsWith"></script>
      <script src="https://webapps.ucsc.edu/wcsi/js/manifest.js"></script>
      <script src="https://webapps.ucsc.edu/wcsi/js/vendor.js"></script>
      <script src="https://webapps.ucsc.edu/wcsi/js/app.js"></script>';
    return $markup;
  }
}
