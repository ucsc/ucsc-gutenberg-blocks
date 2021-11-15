<?php

class CampusDirectory {
  function __construct()
  {
    add_action('init', array($this, 'renderFrontend'));
  }

  function renderFrontend()
  {
    wp_register_style(
      'ucscblocks-editor',
      plugins_url('../src/components/CampusDirectory/editor.css', __FILE__),
      array('wp-edit-blocks'),
      filemtime(plugin_dir_path(__FILE__) . '../src/components/CampusDirectory/editor.css')
    );
    register_block_type('ucscblocks/campusdirectory', array(
      'editor_script' => 'ucscblocks',
      'editor_style' => 'ucscblocks-editor',
      'render_callback' => array($this, 'theHTML'),
    ));
  }

  function theHTML($attributes)
  {
    ob_start();

    echo "<h2>Campus Directory Frontend Output: " . plugin_dir_path(__FILE__) . "</h2>";

    $output = ob_get_contents(); // collect output
    ob_end_clean(); // Turn off ouput buffer

    return $output;
  }
}
