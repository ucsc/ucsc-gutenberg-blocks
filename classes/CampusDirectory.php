<?php

class CampusDirectory
{
  function __construct()
  {
    add_action('init', array($this, 'renderFrontend'));
    add_action('admin_menu', array($this, 'settingsLink'));
    add_action('admin_init', array($this, 'settings'));
    add_filter('query_vars', function($query_vars) {
      $query_vars[] = 'cruzid';
      return $query_vars;
    });

    add_action('template_include', function ($template) {
      if (get_query_var('cruzid') == false || get_query_var('cruzid') == '') {
        return $template;
      }

      return plugin_dir_path(__FILE__) . '../templates/DirectoryProfileTemplate.php';
    });
    add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));
  }
  public function register_plugin_styles() {
    wp_register_style( 'directoryprofile', '../src/components/CampusDirectory/directoryprofile.css' );
    wp_enqueue_style( 'directoryprofile');
  }
  function settings()
  {
    add_settings_section('cd_first_section', null, null, 'campus-directory-settings-page');
    add_settings_field('campus_directory_department', 'Campus Directory Department', array($this, 'apikeyHTML'), 'campus-directory-settings-page', 'cd_first_section');
    register_setting('campus_directory_settings', 'campus_directory_department', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
  }

  function apikeyHTML()
  { ?>
    <input type="text" name="campus_directory_department" value="<?php echo esc_attr(get_option('campus_directory_department')) ?>" />
  <?php }

  function settingsLink()
  {
    add_options_page('Campus Directory Settings', 'Campus Directory Settings', 'manage_options', 'campus-directory-settings-page', array($this, 'settingsPageHTML'));
  }

  function settingsPageHTML()
  { ?>
    <div class="wrap">
      <h1>Campus Directory Settings</h1>
      <form action="options.php" method="POST">
        <?php
        settings_fields('campus_directory_settings');
        do_settings_sections('campus-directory-settings-page');
        submit_button();
        ?>
      </form>
    </div>
<?php }

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
    add_rewrite_rule('directoryprofile/(.*)/?', 'index.php?cruzid=$matches[1]', 'top');
    wp_register_style(
      'directoryprofile',
      plugins_url('../src/components/CampusDirectory/directoryprofile.css', __FILE__),
      array(),
      filemtime(plugin_dir_path(__FILE__) . '../src/components/CampusDirectory/directoryprofile.css')
    );
  }

  function theHTML($attributes)
  {
    $path = plugin_dir_path(__FILE__);
    ob_start();
    include(plugin_dir_path(__FILE__) . '../templates/CampusDirectoryTemplate.php');

    $output = ob_get_contents(); // collect output
    ob_end_clean(); // Turn off output buffer

    return $output;
  }
}
