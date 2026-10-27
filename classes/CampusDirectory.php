<?php


include_once(plugin_dir_path(__FILE__) . 'CampusDirectoryAPI.php');

class CampusDirectory
{
  // Where the last getDirectoryPhoto() answer came from.
  public $photoSource = '';

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

      return plugin_dir_path(__FILE__) . '../templates/DirectoryProfileTemplate.php';
    });
    add_action('template_redirect', array($this, 'directoryProfileStatus'));
    add_filter('the_content', array($this, 'renderDirectoryProfile'), 20);
    add_filter('document_title_parts', array($this, 'directory_profile_title'));
    add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));
    add_action('rest_api_init', function () {
      register_rest_route('ucscgutenbergblocks/v1', '/campusdirectoryrequirements/', array(
        'methods' => 'GET',
        'callback' => array($this, 'requirements'),
        'permission_callback' => function() {return true;}
      ));
      register_rest_route('ucscgutenbergblocks/v1', '/directoryphoto/(?P<uid>[A-Za-z0-9._-]+)', array(
        'methods' => 'GET',
        'callback' => array($this, 'directoryPhoto'),
        'permission_callback' => function() {return true;}
      ));
    });
  }

  // Serve a person's photo from LDAP (jpegPhoto) instead of linking to
  // campusdirectory.ucsc.edu/photo.php, which only answers on campus/VPN.
  // X-Directory-Photo-Source says where the answer came from (ldap, none, or
  // cache), so one request on a live site shows which source works.
  function directoryPhoto($request) {
    $photo = $this->getDirectoryPhoto($request['uid']);
    header('X-Directory-Photo-Source: ' . $this->photoSource);
    if ($photo === '') {
      header('Cache-Control: public, max-age=3600');
      wp_redirect('https://static.ucsc.edu/images/icon-slug.jpg', 302);
      exit;
    }
    header('Content-Type: image/jpeg');
    header('Content-Length: ' . strlen($photo));
    header('Cache-Control: public, max-age=3600');
    echo $photo;
    exit;
  }

  // Raw JPEG bytes for $uid, or '' when the person has no LDAP photo, is not
  // publicly listed, or LDAP failed. LDAP only returns publicly listed people,
  // so nobody else's photo is ever served, and campusdirectory photo.php is
  // never asked: it does not check listing and only mirrors LDAP's jpegPhoto
  // (WPM-230). Photos are cached for 4 hours (and an hour in browsers), so a
  // photo removed or hidden in the directory stops showing within about 5 hours
  // (WPM-231). Misses are cached for an hour (as '') so a person without a photo
  // does not cost an LDAP lookup on every page view (WPM-233).
  function getDirectoryPhoto($uid) {
    $key = 'ucsc_cd_photo_' . md5($uid);
    $cached = get_transient($key);
    if ($cached !== false) {
      $this->photoSource = 'cache';
      return base64_decode($cached);
    }

    $campusDirectoryAPI = new CampusDirectoryAPI(['automatedFeeds' => false]);
    $people = $campusDirectoryAPI->getCampusDirData($uid, true)[0];
    $photo = !empty($people[0]['jpegphoto'][0]) ? $people[0]['jpegphoto'][0] : '';
    $this->photoSource = $photo === '' ? 'none' : 'ldap';
    if ($photo !== '') set_transient($key, base64_encode($photo), 4 * HOUR_IN_SECONDS);
    else set_transient($key, '', HOUR_IN_SECONDS);
    return $photo;
  }

  // A CruzID LDAP does not return gets a real 404 status instead of a 200
  // "not found" page that search engines index; the page itself is unchanged
  // (WPM-247). Not while LDAP is marked down: those people may well exist.
  function directoryProfileStatus() {
    $cruzid = get_query_var('directoryprofilecruzid');
    if ($cruzid === false || $cruzid === '') return;
    $campusDirectoryAPI = new CampusDirectoryAPI(['automatedFeeds' => false]);
    $data = $campusDirectoryAPI->getCampusDirData($cruzid, true);
    if (empty($data[0]) && !get_transient('ucsc_cd_ldap_down')) status_header(404);
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

    $file = '../src/components/CampusDirectory/photofallback.js';
    wp_register_script(
            'campusdirectoryphotofallback',
            plugins_url($file, __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . $file),
            true
    );
    wp_enqueue_script('campusdirectoryphotofallback');

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
      'api_version' => 3,
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
    // WPM-113: missing/empty/malformed str* attributes make json_decode() return
    // null, which fatals downstream (array_sum(null)) on PHP 8.x. Cast to array
    // so every decode site always yields an array, even for '' or invalid JSON.
    $attributes['objFacultyTypes'] = (array) json_decode($attributes['strFacultyTypes'] ?? '', true);
    $attributes['objStaffTypes'] = (array) json_decode($attributes['strStaffTypes'] ?? '', true);
    $attributes['objGradTypes'] = (array) json_decode($attributes['strGradTypes'] ?? '', true);
    $attributes['objInformationTypes'] = (array) json_decode($attributes['strInformationTypes'] ?? '', true);
    if (($attributes['strInformationTypesTable'] ?? null) != null) $attributes['objInformationTypesTable'] = (array) json_decode($attributes['strInformationTypesTable'], true);
    else $attributes['objInformationTypesTable'] = array();
    $campusDirectoryAPI = new CampusDirectoryAPI($attributes);
    $items = $campusDirectoryAPI->setDirectoryData();
    // WPM-92: authors pick the person-name heading level so it fits the page outline.
    $headingLevel = (int) ($attributes['headingLevel'] ?? 3);
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
    return $content . ob_get_clean();
  }
}
