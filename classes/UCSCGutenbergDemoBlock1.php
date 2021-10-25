<?php
/**
  Doc: a doc
*/
class UCSCGutenbergDemoBlock1
{
  function __construct()
  {
    add_action('init', array($this, 'adminAssets'));
  }

  function adminAssets()
  {
    register_block_type('ucscblocks/gutenberg', array(
      'editor_script' => 'ucscblocks',
      'render_callback' => array($this, 'theHTML')
    ));
  }

  function theHTML($attributes)
  {
    error_log("hello theHTML: " . $attributes['grassColor']);
    return '<p>Today the sky is ' . $attributes['skyColor'] . '  and the grass is ' . $attributes['grassColor'] . '!!!</p>';
  }
}
