<?php


include(plugin_dir_path(__FILE__) . 'CampusDirectoryAPI.php');

class CampusDirectory
{
  function __construct()
  {
    add_action('init', array($this, 'renderFrontend'));
    add_filter('query_vars', function($query_vars) {
      $query_vars[] = 'directoryprofilecruzid';
      return $query_vars;
    });

    add_action('template_include', function ($template) {
      if (get_query_var('directoryprofilecruzid') == false || get_query_var('directoryprofilecruzid') == '') {
        return $template;
      }

      return plugin_dir_path(__FILE__) . '../templates/DirectoryProfileTemplate.php';
    });
    add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));
  }
  public function register_plugin_styles() {
    wp_register_style( 'directoryprofile',
        plugins_url('../src/components/CampusDirectory/directoryprofile.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . '../src/components/CampusDirectory/directoryprofile.css'));
    wp_enqueue_style( 'directoryprofile');

    $file = '../src/components/CampusDirectory/campusdirectory.css';
    wp_register_style(
            'campusdirectory',
            plugins_url($file, __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) .$file)
    );
    wp_enqueue_style('campusdirectory');
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
    $attributes['objFacultyTypes'] = json_decode($attributes['strFacultyTypes'], true);
    $attributes['objStaffTypes'] = json_decode($attributes['strStaffTypes'], true);
    $attributes['objGradTypes'] = json_decode($attributes['strGradTypes'], true);
    $attributes['objInformationTypes'] = json_decode($attributes['strInformationTypes'], true);
    $campusDirectoryAPI = new CampusDirectoryAPI($attributes);
    $items = $campusDirectoryAPI->setDirectoryData();
    ob_start();
    include(plugin_dir_path(__FILE__) . '../templates/CampusDirectoryTemplate.php');

    $output = ob_get_contents(); // collect output
    ob_end_clean(); // Turn off output buffer

    return $output;
  }
}
