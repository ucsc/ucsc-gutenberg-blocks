<?php

class ClassSchedule
{
  function __construct()
  {
    add_action('admin_menu', array($this, 'settingsLink'));
    add_action('admin_init', array($this, 'settings'));
  }

  function settings()
  {
    add_settings_section('cs_first_section', null, null, 'class-schedule-settings-page');
    add_settings_field('class_schedule_department', 'Class Schedule Department', array($this, 'apikeyHTML'), 'class-schedule-settings-page', 'cs_first_section');
    register_setting('class_schedule_settings', 'class_schedule_department', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
  }

  function apikeyHTML()
  { ?>
    <input type="text" name="class_schedule_department" value="<?php echo esc_attr(get_option('class_schedule_department')) ?>" />
  <?php }

  function settingsLink()
  {
    add_options_page('Class Schedule Settings', 'Class Schedule Settings', 'manage_options', 'class-schedule-settings-page', array($this, 'settingsPageHTML'));
  }

  function settingsPageHTML()
  { ?>
    <div class="wrap">
      <h1>Class Schedule Settings</h1>
      <form action="options.php" method="POST">
        <?php
        settings_fields('class_schedule_settings');
        do_settings_sections('class-schedule-settings-page');
        submit_button();
        ?>
      </form>
    </div>
<?php }
}
