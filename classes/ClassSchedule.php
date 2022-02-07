<?php

class ClassSchedule
{
  function __construct()
  {
    add_action('init', array($this, 'adminAssets'));
    add_filter( 'the_content', array($this, 'mainLoop'));
  }

  function mainLoop ($content) {
    // Check if we're inside the main loop in a single Post.
    if ( is_singular() && in_the_loop() && is_main_query() ) {
      if (has_block("ucscblocks/classschedule")) {
        $sub = (isset($_GET["sub"]) && strlen(trim($_GET["sub"])) > 0);
        if (!$sub) {
          global $wp;
          $currentUrl = home_url( $wp->request );
          // return $content . esc_html__( "strpos: " . strpos($currentUrl, "?") , 'wporg');
          $sub = get_option('class_schedule_department');
          if (strpos($currentUrl, "?") >= 0) $currentUrl = $currentUrl . "&sub=" . $sub;
          else $currentUrl = $currentUrl . "?sub=" . $sub;
          // wp_safe_redirect( "https://my-wordpress-blog.local/2022/02/03/45/?sub=HIS" );
          // exit;
          return $content . esc_html__( "URL: " . $currentUrl , 'wporg');
        }
      }
    }

    return $content;
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
