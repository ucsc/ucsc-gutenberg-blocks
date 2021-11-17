<?php

class CampusDirectory
{
  function __construct()
  {
    add_action('init', array($this, 'renderFrontend'));
    add_action('admin_menu', array($this, 'settingsLink'));
    add_action('admin_init', array($this, 'settings'));
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
