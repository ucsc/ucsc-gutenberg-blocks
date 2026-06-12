<?php


include_once(plugin_dir_path(__FILE__) . 'CampusDirectoryAPI.php');

class CampusDirectory
{
  function __construct()
  {
    add_action('init', array($this, 'renderFrontend'));
    add_action('init', array($this, 'add_directory_profile_rewrite'));
    add_filter('query_vars', function($query_vars) {
      $query_vars[] = 'directoryprofilecruzid';
      return $query_vars;
    });

    add_action('template_include', function ($template) {
      $cruzid = get_query_var('directoryprofilecruzid');
      if ($cruzid === false || $cruzid === '') {
        return $template;
      }

      // On a real page/post, render the profile inline via the_content (see
      // renderDirectoryProfile) so the active theme supplies its own header,
      // navigation, and footer. Block (FSE) themes have no header.php for the
      // standalone template to load, which dropped the site nav (WPM-99). Only
      // fall back to the standalone template for pretty /directory/<cruzid>/
      // URLs that do not resolve to a singular page.
      if (is_singular()) {
        return $template;
      }

      return plugin_dir_path(__FILE__) . '../templates/DirectoryProfileTemplate.php';
    });
    add_filter('the_content', array($this, 'renderDirectoryProfile'), 20);
    add_filter('document_title_parts', array($this, 'directory_profile_title'));
    add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));
    add_action('rest_api_init', function () {
      register_rest_route('ucscgutenbergblocks/v1', '/campusdirectoryrequirements/', array(
        'methods' => 'GET',
        'callback' => array($this, 'requirements'),
        'permission_callback' => function() {return true;}
      ));
    });
  }

  function add_directory_profile_rewrite() {
    // instead of something like ?directoryprofilecruzid=jsmith, users see /directory/jsmith/
    add_rewrite_rule(
      '^directory/([^/]+)/?$',
      'index.php?directoryprofilecruzid=$matches[1]',
      'top'
    );
  }
  // a11y: set a descriptive <title> for directory profile pages
  function directory_profile_title($title_parts) {
    $cruzid = get_query_var('directoryprofilecruzid');
    if ($cruzid) {
      $campusDirectoryAPI = new CampusDirectoryAPI([]);
      $data = $campusDirectoryAPI->getCampusDirData($cruzid, true);
      if (!empty($data[0][0]['cn'][0])) {
        $title_parts['title'] = $data[0][0]['cn'][0];
      }
    }
    return $title_parts;
  }

  function requirements(){
    $resp = [];
    $ldap_password = get_site_option('ldap_api_key');
    if (!$ldap_password) $ldap_password = get_option('ldap_api_key');
    $resp["ldap_pass"] = strlen($ldap_password) > 0;
    $resp["multisite"] = is_multisite();

    return new WP_REST_Response($resp);
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

    wp_enqueue_style( 'ucsc-shared-templates',
        plugins_url('../src/components/shared/templates.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . '../src/components/shared/templates.css')
    );

    wp_enqueue_style( 'static-directory-page',
        plugins_url('../src/components/shared/static-directory-page.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . '../src/components/shared/static-directory-page.css')
    );

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
    if ($attributes['strInformationTypesTable'] != null) $attributes['objInformationTypesTable'] = json_decode($attributes['strInformationTypesTable'], true);
    $campusDirectoryAPI = new CampusDirectoryAPI($attributes);
    $items = $campusDirectoryAPI->setDirectoryData();
    ob_start();
    include(plugin_dir_path(__FILE__) . '../templates/CampusDirectoryTemplate.php');

    $output = ob_get_contents(); // collect output
    ob_end_clean(); // Turn off output buffer

    return $output;
  }

  // Render a directory profile inline within the queried page's content so the
  // active theme keeps its header, navigation, and footer (WPM-99). Only fires
  // for the main query's singular page when a directoryprofilecruzid is set.
  function renderDirectoryProfile($content)
  {
    $cruzid = get_query_var('directoryprofilecruzid');
    if ($cruzid === false || $cruzid === '') return $content;
    if (is_admin() || !is_singular() || !is_main_query()) return $content;
    if (get_the_ID() !== get_queried_object_id()) return $content;

    $attributes = [];
    $directory_profile_inline = true;
    ob_start();
    include(plugin_dir_path(__FILE__) . '../templates/DirectoryProfileTemplate.php');
    return ob_get_clean();
  }
}
