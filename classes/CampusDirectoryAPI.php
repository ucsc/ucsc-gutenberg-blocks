<?php
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

class CampusDirectoryAPI {
  public $nodeContent;
  public $allStaffTypes;
  public $ldap_password;
  public $count;

  function __construct($content = [])
  {
    $this->nodeContent = $content;
    $this->allStaffTypes = [
      "Regular Staff",
      "Researcher",
      "Postdoctoral Scholar"
    ];
    $this->ldap_password = get_site_option('ldap_api_key');
    if (!$this->ldap_password) $this->ldap_password = get_option('ldap_api_key');
  }

  public function setDirectoryData()
  {
    $variables = [];
    $strCruzids = $this->nodeContent['cruzidList'];
    $variables['dirLayout'] = $this->nodeContent["pageLayout"];
    $variables['linkToProfile'] = $this->nodeContent["linkToProfile"];
    $variables['informationToDisplay'] = $this->getInformationToDisplay();

    $campusDirData = $this->getCampusDirData($strCruzids);
    $variables['items'] = $campusDirData[0];
    $variables['q'] = $campusDirData[1];
    $variables['nodeContent'] = $this->nodeContent;
    $variables['count'] = $this->count;

    return $variables;
  }

  public function getInformationToDisplay()
  {
    $dictFieldInformation = array(
      'Pronouns' => 'ucscpersonpubpronouns',
      'Photo' => 'b64image',
      'Title' => 'title',
      'Department' => 'ucscpersonpubdepartmentnumber',
      'Phone' => 'telephonenumber',
      'Campus Email' => 'mail',
      "Other Email" => 'ucscpersonpubalternatemail',
      'Fax' => 'facsimiletelephonenumber',
      'Website' => 'ucscpersonpubwebsite',
      'Office Location' => 'ucscpersonpubofficelocationdetail',
      'Office Hours' => 'ucscpersonpubofficehours',
      'Mailstop' => 'ucscpersonpubmailstop',
      'Mailing Address' => 'combineaddressfields',
      'Faculty Areas of Expertise' => 'ucscpersonpubexpertisereference',
      'Summary of Expertise' => 'ucscpersonpubareaofexpertise'
    );

    $returnDictionary = array();
    $informationTypes = $this->nodeContent["pageLayout"] === "table" ? $this->nodeContent['objInformationTypesTable'] : $this->nodeContent['objInformationTypes'];
    foreach ($informationTypes as $label => $isSet) {
      if ($isSet) {
        array_push($returnDictionary, [$dictFieldInformation[$label] => $label]);
      }
    }
    return $returnDictionary;
  }

  public function getCampusDirData($strCruzids, $profileView = false)
  {
    if ($profileView || !$this->nodeContent['automatedFeeds']) {
      $arrCruzids = explode(",", $strCruzids);
      $q = '(|';
      for ($i = 0; $i < count($arrCruzids); $i++) {
        $arrCruzids[$i] = trim($arrCruzids[$i]);
        $q .= "(uid={$arrCruzids[$i]})";
      }
      $q .= ")";
    } else {
      $q = $this->buildFilterString();
    }
    // check to see if $q is in cache
    $md5_q = md5($q);
    $people = get_transient($md5_q);
    if (!$people) {
      $people = $this->doLDAPQuery($q, $arrCruzids);
      set_transient($md5_q, $people, 600);
    }
    return [$people, $q];
  }

  public function doLDAPQuery($q, $arrCruzids) {
    $dev_env = getenv("DOCKER_DEV") == "docker_dev";
    if ($dev_env) {
      $rli = ldap_connect("ldap://ldap-blue.ucsc.edu/");
    } else {
      $rli = ldap_connect("ldaps://ldap-blue.ucsc.edu/");
    }

    if ($rli) {
      ldap_set_option($rli, LDAP_OPT_TIMELIMIT, 90);
      ldap_set_option($rli, LDAP_OPT_PROTOCOL_VERSION, 3);

      if ($dev_env) @$ldapbind = ldap_bind($rli);
      else @$ldapbind = ldap_bind($rli, "cn=pantheon-webapps,ou=apps,dc=ucsc,dc=edu", $this->ldap_password);
      if ($ldapbind) {
        $sr = ldap_search($rli, "ou=people,dc=ucsc,dc=edu", "(|{$q})");
        $people = $this->processSearchResults($rli, $sr);
        $people = $this->addVacantPositions($people, $this->nodeContent['automatedFeeds'], $arrCruzids);
        ldap_close($rli);
        return $people;
      } else {
        echo ldap_error($rli );
      }
    }

    return [];
  }

  public function getDirDropdowns($deptOrDiv) {
    $retDepts = get_transient('ucsc_campus_directory_departments_' . $deptOrDiv);
    if (!$retDepts) {
      $people = $this->doLDAPQuery("(|(ucscpersonpubaffiliation=Graduate)(ucscpersonpubaffiliation=Faculty)(ucscpersonpubaffiliation=Staff))", []);

      $uniqueDepts = [];
      for($i=0; $i<count($people); $i++) {
        for($j=0; $j<$people[$i][$deptOrDiv]['count']; $j++) {
          $uniqueDepts[$people[$i][$deptOrDiv][$j]] = '';
        }
      }

      $retDepts = [];
      foreach($uniqueDepts as $dept => $emptyStr) {
        $retDepts[] = [
          'label' => $dept,
          'value' => $dept
        ];
      }

      function cmp($a, $b) {
        return strcmp($a['label'], $b['label']);
      }

      usort($retDepts, "cmp");

      array_unshift($retDepts, [
        'label' => '---',
        'value' => '---'
      ]);

      set_transient('ucsc_campus_directory_departments_' . $deptOrDiv, $retDepts, 86400);
    }

    return $retDepts;
  }

  public function addVacantPositions($people, $automatedFeed, $arrCruzids)
  {
    $retArr = [];
    if ($automatedFeed) {
      $retArr = $people;
      if (array_key_exists('addCruzids', $this->nodeContent) && strlen($this->nodeContent['addCruzids'])) {
        $arrCruzids = explode(",", $this->nodeContent['addCruzids']);
        for ($i = 0; $i < count($arrCruzids); $i++) {
          $arrCruzids[$i] = trim($arrCruzids[$i]);
          if (substr($arrCruzids[$i], 0, 1) == "%") {
            array_push($retArr, $this->addVacantPosition($arrCruzids[$i]));
          }
        }
      }
    } else {
      $cruzidToPersion = [];
      foreach($people as $person) {
        $cruzidToPersion[$person["uid"][0]] = $person;
      }
      for ($i = 0; $i < count($arrCruzids); $i++) {
        if (strlen($arrCruzids[$i])) {
          if (substr($arrCruzids[$i], 0, 1) == "%") {
            array_push($retArr, $this->addVacantPosition($arrCruzids[$i]));
          } elseif (array_key_exists($arrCruzids[$i], $cruzidToPersion)) {
            array_push($retArr, $cruzidToPersion[$arrCruzids[$i]]);
          }
        }
      }
    }

    return $retArr;
  }

  public function addVacantPosition($position) {
    $trimmedPosition = trim($position, "%");
    $arrPosition = explode("%", $trimmedPosition);
    return [
      "cn" => [$arrPosition[0]],
      "title" => [$arrPosition[1]]
    ];
  }

  public function buildFilterString()
  {
    $filterString = "";
    $vacancies = array();
    $this->count = 0;
    if ($this->nodeContent["objGradTypes"]["Grad Students"]) {
      $filterString .= "(ucscpersonpubaffiliation=Graduate)";
      $this->count++;
    }
    $this->processFacultyFilterString($filterString);
    $this->processStaffFilterString($filterString);
    $this->processDeptDivFilterString($filterString);
    $this->processAddExcludeFilterString($filterString);

    return $filterString;
  }

  public function processFacultyFilterString(&$filterString)
  {
    if (!empty($this->nodeContent["objFacultyTypes"])) {
      $facultyTypes = $this->nodeContent["objFacultyTypes"];
      if ($facultyTypes['All']) {
        $filterString .= "(ucscpersonpubaffiliation=Faculty)";
        $this->count++;
      } else if (array_sum($facultyTypes) > 0) {
        $affiliation = "(ucscpersonpubaffiliation=Faculty)";
        $typeCount = 0;
        $typeList = "";
        foreach ($facultyTypes as $key => $value) {
          if ($value) {
            $typeList .= "(ucscpersonpubfacultytype={$key})";
            $typeCount++;
          }
        }
        if ($typeCount > 1) $typeList = "(|$typeList)";
        $filterString .= "(&$affiliation$typeList)";
        $this->count++;
      }
    }
  }

  public function processStaffFilterString(&$filterString)
  {
    $staffTypes = $this->nodeContent["objStaffTypes"];
    if (array_sum($this->nodeContent["objStaffTypes"]) == 3) {
      $filterString .= "(ucscpersonpubaffiliation=Staff)";
      $this->count++;
    } else if (array_sum($staffTypes) > 0)  {
      $affiliation = "";
      $typeCount = 0;
      $typeList = "";

      if ($staffTypes['Regular Staff']) {
        foreach ($this->allStaffTypes as $type) {
          if (!$this->nodeContent['objStaffTypes'][$type]) {
            $typeList .= "(ucscpersonpubstafftype=$type)";
            $typeCount++;
          }
        }
        if ($typeCount > 1) $typeList = "(|$typeList)";
        $typeList = "(!$typeList)";
      } else {
        foreach ($this->allStaffTypes as $type) {
          if ($this->nodeContent['objStaffTypes'][$type]) { // we already know "Regular Staff" is false
            $typeList .= "(ucscpersonpubstafftype=$type)";
            $typeCount++;
          }
        }
        if ($typeCount > 1) $typeList = "(|$typeList)";
      }
      if ($typeCount > 0) $affiliation = "(ucscpersonpubaffiliation=Staff)";
      $filterString .= "(&$affiliation$typeList)";
      $this->count++;
    }
  }

  public function processDeptDivFilterString(&$filterString)
  {
    $department = $this->nodeContent['department'];
    $division = $this->nodeContent['division'];
    $deptOrDiv = $this->nodeContent['deptOrDiv'];


    if ($this->count > 0) {
      if ($this->count > 1) $filterString = "(|$filterString)";

      if (!empty($department) || !empty($division)) {
        if ($this->nodeContent['displayDeptartmentAffiliates'] && $deptOrDiv == 'dept') {
          $filterString = "(&(ucscpersonpubaffiliateddepartment=$department)$filterString)";
        } elseif ($deptOrDiv == 'dept') {
          $filterString = "(&(ucscpersonpubdepartmentnumber=$department)$filterString)";
        } elseif ($deptOrDiv == 'div') {
          $filterString = "(&(ucscpersonpubdivision=$division)$filterString)";
        }
      }
    }
  }

  public function processAddExcludeFilterString(&$filterString)
  {
    if ($this->nodeContent['manualAdd']) {
      if (strlen($this->nodeContent['excludeCruzids'])) {
        $sectionExclude = $this->nodeContent['excludeCruzids'];
        $exclude = "";
        $excludeCount = 0;
        foreach (explode(',', $sectionExclude) as $excludeMe) {
          $exclude .= "(uid=" . trim($excludeMe) . ")";
          $excludeCount++;
        }
        if ($excludeCount > 1) $exclude = "(|$exclude)";
        $filterString = "(&$filterString(!$exclude))";
      }

      if (strlen($this->nodeContent['addCruzids'])) {
        $sectionInclude = $this->nodeContent['addCruzids'];
        $add = "";
        $includeCount = 0;
        foreach (explode(',', $sectionInclude) as $cruzid) {
          $add .= "(uid=" . trim($cruzid) . ")";
          $includeCount++;
        }
        if ($includeCount > 1) $add = "(|$add)";
        $filterString = "(|$filterString$add)";
      }
    }
  }

  public function processSearchResults($rli, $sr)
  {


    $people = [];

    for ($entry = ldap_first_entry($rli, $sr); $entry != false; $entry = ldap_next_entry($rli, $entry)) {
      $attrs = ldap_get_attributes($rli, $entry);
      $person = array();
      if ((!empty($attrs["ucscPersonPubDisplay"]) && $attrs["ucscPersonPubDisplay"][0] === "TRUE") ||
       (!empty($attrs["ucscpersonpubdisplay"]) && $attrs["ucscpersonpubdisplay"][0] === "TRUE")
      ) {
        for ($attr = ldap_first_attribute($rli, $entry); $attr != false; $attr = ldap_next_attribute($rli, $entry)) {
          $attr = strtolower($attr);
          $values = ldap_get_values($rli, $entry, $attr);
          $person[$attr] = $values;
        }
        array_push($people, $person);
      }
    }

    if ($this->nodeContent['automatedFeeds']) {
      usort($people, function($a, $b) {
        return strnatcasecmp($a['givenname'][0], $b['givenname'][0]);
      });
      usort($people, function($a, $b) {
        return strnatcasecmp($a['sn'][0], $b['sn'][0]);
      });
    }

    return $people;
  }
}
