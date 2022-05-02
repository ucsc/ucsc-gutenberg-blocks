<?php
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

class CampusDirectoryAPI {
  public $nodeContent;
  public $allStaffTypes;
  public $ldap_password;

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
    $variables['introParagraph'] = $this->nodeContent["introParagraph"];
    $variables['informationToDisplay'] = $this->getInformationToDisplay();

    $campusDirData = $this->getCampusDirData($strCruzids);
    $variables['items'] = $campusDirData[0];
    $variables['q'] = $campusDirData[1];
    $variables['nodeContent'] = $this->nodeContent;

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
      'Email' => 'mail',
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
    foreach ($this->nodeContent['objInformationTypes'] as $label => $isSet) {
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
      foreach ($arrCruzids as $cruzid) {
        $cruzid = trim($cruzid);
        $q .= "(uid={$cruzid})";
      }
      $q .= ")";
    } else {
      $q = $this->buildFilterString();
    }
    // check to see if $q is in cache
    $md5_q = md5($q);
    $people = get_transient($md5_q);
    if (!$people) {
      $rli = ldap_connect("ldaps://ldap-blue.ucsc.edu/");
      if ($rli) {
        ldap_set_option($rli, LDAP_OPT_TIMELIMIT, 90);
        ldap_set_option($rli, LDAP_OPT_PROTOCOL_VERSION, 3);

        if (ldap_bind($rli, "cn=pantheon-webapps,ou=apps,dc=ucsc,dc=edu", $this->ldap_password)) {
          $sr = ldap_search($rli, "ou=people,dc=ucsc,dc=edu", "(|{$q})");
          $people = $this->processSearchResults($rli, $sr);
          set_transient($md5_q, $people, 600);
          ldap_close($rli);
        }
      }
    }
    return [$people, $q];
  }

  public function buildFilterString()
  {
    $filterString = "";
    $vacancies = array();
    $count = 0;
    if ($this->nodeContent["objGradTypes"]["Grad Students"]) {
      $filterString .= "(ucscpersonpubaffiliation=Graduate)";
      $count++;
    }
    $this->processFacultyFilterString($filterString, $count);
    $this->processStaffFilterString($filterString, $count);
    $this->processDeptDivFilterString($filterString, $count);
    $this->processAddExcludeFilterString($filterString, $count);

    return $filterString;
  }

  public function processFacultyFilterString(&$filterString, &$count)
  {
    if (!empty($this->nodeContent["objFacultyTypes"])) {
      $facultyTypes = $this->nodeContent["objFacultyTypes"];
      if ($facultyTypes['All']) {
        $filterString .= "(ucscpersonpubaffiliation=Faculty)";
        $count++;
      } else {
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
        $count++;
      }
    }
  }

  public function processStaffFilterString(&$filterString, &$count)
  {
    $staffTypes = $this->nodeContent["objStaffTypes"];
    if (array_sum($this->nodeContent["objStaffTypes"]) == 3) {
      $filterString .= "(ucscpersonpubaffiliation=Staff)";
      $count++;
    } else {
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
      $count++;
    }
  }

  public function processDeptDivFilterString(&$filterString, &$count)
  {
    $department = get_option('campus_directory_department');
    $division = get_option('campus_directory_division');

    if ($count > 0) {
      if ($count > 1) $filterString = "(|$filterString)";

      if (!empty($department) || !empty($division)) {
        if ($this->nodeContent['displayDeptartmentAffiliates']) {
          $filterString = "(&(ucscpersonpubaffiliateddepartment=$department)$filterString)";
        } elseif (!empty($department)) {
          $filterString = "(&(ucscpersonpubdepartmentnumber=$department)$filterString)";
        } elseif (!empty($division)) {
          $filterString = "(&(ucscpersonpubdivision=$division)$filterString)";
        }
      }
    }
  }

  public function processAddExcludeFilterString(&$filterString, &$count)
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
    if ($this->nodeContent['automatedFeeds']) {
      @ldap_sort($rli, $sr, "givenname");
      @ldap_sort($rli, $sr, "sn");
    }

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
    return $people;
  }
}
