<?php

include_once(plugin_dir_path(__FILE__) . 'CampusDirectoryAPI.php');

class SiteSettings
{
  function __construct()
  {
    add_action('admin_menu', array($this, 'settingsLink'));
    add_action('admin_init', array($this, 'settings'));
    add_action("network_admin_menu", array($this, 'networkSettingsLink'));
    add_action('network_admin_edit_ucscplugin', array($this, 'networkSaveSettings'));
    add_action('network_admin_notices', array($this, 'networkSettingsNotifications'));
    add_action('rest_api_init', function () {
      register_rest_route('ucscgutenbergblocks/v1', '/departmentcode/', array(
        'methods' => 'GET',
        'callback' => array($this, 'departmentcode'),
        'permission_callback' => function() {return true;}
      ));
      register_rest_route('ucscgutenbergblocks/v1', '/subjectcode/', array(
        'methods' => 'GET',
        'callback' => array($this, 'subjectcode'),
        'permission_callback' => function() {return true;}
      ));
      register_rest_route('ucscgutenbergblocks/v1', '/cddepartmentcode/', array(
        'methods' => 'GET',
        'callback' => array($this, 'cddepartmentcode'),
        'permission_callback' => function() {return true;}
      ));
      register_rest_route('ucscgutenbergblocks/v1', '/divisioncode/', array(
        'methods' => 'GET',
        'callback' => array($this, 'divisioncode'),
        'permission_callback' => function() {return true;}
      ));
    });
  }

  function cddepartmentcode()
  {
    // $campusDirectoryAPI = new CampusDirectoryAPI(['automatedFeeds' => true]);
    // return new WP_REST_Response($campusDirectoryAPI->getDirDropdowns('departmentnumber'));

    $ret = [];
    $ret[] = [
      "label" => "---",
      "value" => "---"
    ];
    $ret[] = [
      "label" => "Academic Affairs",
      "value" => "Academic Affairs"
    ];
    $ret[] = [
      "label" => "Academic Excellence Program (ACE)",
      "value" => "Academic Excellence Program (ACE)"
    ];
    $ret[] = [
      "label" => "Academic Personnel Office",
      "value" => "Academic Personnel Office"
    ];
    $ret[] = [
      "label" => "Academic Senate",
      "value" => "Academic Senate"
    ];
    $ret[] = [
      "label" => "Academic Support Resources",
      "value" => "Academic Support Resources"
    ];
    $ret[] = [
      "label" => "Accounting Services",
      "value" => "Accounting Services"
    ];
    $ret[] = [
      "label" => "Admissions Office",
      "value" => "Admissions Office"
    ];
    $ret[] = [
      "label" => "Advising Office",
      "value" => "Advising Office"
    ];
    $ret[] = [
      "label" => "African American Resource and Cultural Center",
      "value" => "African American Resource and Cultural Center"
    ];
    $ret[] = [
      "label" => "Alumni Engagement",
      "value" => "Alumni Engagement"
    ];
    $ret[] = [
      "label" => "American Indian Resource Center",
      "value" => "American Indian Resource Center"
    ];
    $ret[] = [
      "label" => "American Studies Department",
      "value" => "American Studies Department"
    ];
    $ret[] = [
      "label" => "Anthropology Department",
      "value" => "Anthropology Department"
    ];
    $ret[] = [
      "label" => "Applied Linguistics and Multilingualism",
      "value" => "Applied Linguistics and Multilingualism"
    ];
    $ret[] = [
      "label" => "Applied Mathematics",
      "value" => "Applied Mathematics"
    ];
    $ret[] = [
      "label" => "Arboretum",
      "value" => "Arboretum"
    ];
    $ret[] = [
      "label" => "Archaeological Research Center",
      "value" => "Archaeological Research Center"
    ];
    $ret[] = [
      "label" => "Art Department",
      "value" => "Art Department"
    ];
    $ret[] = [
      "label" => "Arts Division",
      "value" => "Arts Division"
    ];
    $ret[] = [
      "label" => "Asian-American/Pacific Islander Resource Center",
      "value" => "Asian-American/Pacific Islander Resource Center"
    ];
    $ret[] = [
      "label" => "Astronomy & Astrophysics Department",
      "value" => "Astronomy & Astrophysics Department"
    ];
    $ret[] = [
      "label" => "Athletics & Recreation",
      "value" => "Athletics & Recreation"
    ];
    $ret[] = [
      "label" => "Audit & Management Advisory Services",
      "value" => "Audit & Management Advisory Services"
    ];
    $ret[] = [
      "label" => "Baskin School of Engineering",
      "value" => "Baskin School of Engineering"
    ];
    $ret[] = [
      "label" => "Bay Tree Bookstore",
      "value" => "Bay Tree Bookstore"
    ];
    $ret[] = [
      "label" => "Biomolecular Engineering",
      "value" => "Biomolecular Engineering"
    ];
    $ret[] = [
      "label" => "Biomolecular Science & Engineering",
      "value" => "Biomolecular Science & Engineering"
    ];
    $ret[] = [
      "label" => "Budget Analysis and Planning",
      "value" => "Budget Analysis and Planning"
    ];
    $ret[] = [
      "label" => "Business & Financial Analysis - CHES",
      "value" => "Business & Financial Analysis - CHES"
    ];
    $ret[] = [
      "label" => "CalTeach",
      "value" => "CalTeach"
    ];
    $ret[] = [
      "label" => "Campus Controller's Office",
      "value" => "Campus Controller's Office"
    ];
    $ret[] = [
      "label" => "Campus Housing Office",
      "value" => "Campus Housing Office"
    ];
    $ret[] = [
      "label" => "Cantu GLBTI Resource Center",
      "value" => "Cantu GLBTI Resource Center"
    ];
    $ret[] = [
      "label" => "Capital Planning & Space Management",
      "value" => "Capital Planning & Space Management"
    ];
    $ret[] = [
      "label" => "Career Center",
      "value" => "Career Center"
    ];
    $ret[] = [
      "label" => "Center for Adaptive Optics",
      "value" => "Center for Adaptive Optics"
    ];
    $ret[] = [
      "label" => "Center for Agroecology & Sustainable Food Systems",
      "value" => "Center for Agroecology & Sustainable Food Systems"
    ];
    $ret[] = [
      "label" => "Center for Collaborative Research for an Equitable California",
      "value" => "Center for Collaborative Research for an Equitable California"
    ];
    $ret[] = [
      "label" => "Center for Computational Experience",
      "value" => "Center for Computational Experience"
    ];
    $ret[] = [
      "label" => "Center for Innovation and Entrepreneurial Development",
      "value" => "Center for Innovation and Entrepreneurial Development"
    ];
    $ret[] = [
      "label" => "Center for Innovations in Teaching and Learning (CITL)",
      "value" => "Center for Innovations in Teaching and Learning (CITL)"
    ];
    $ret[] = [
      "label" => "Center for Justice",
      "value" => "Center for Justice"
    ];
    $ret[] = [
      "label" => "Center for Labor Studies",
      "value" => "Center for Labor Studies"
    ];
    $ret[] = [
      "label" => "Center for Remote Sensing",
      "value" => "Center for Remote Sensing"
    ];
    $ret[] = [
      "label" => "Center for Statistical Analysis in the Social Sciences",
      "value" => "Center for Statistical Analysis in the Social Sciences"
    ];
    $ret[] = [
      "label" => "Center for Teaching & Learning",
      "value" => "Center for Teaching & Learning"
    ];
    $ret[] = [
      "label" => "Center for the Origin, Dynamics, and Evolution of Planets",
      "value" => "Center for the Origin, Dynamics, and Evolution of Planets"
    ];
    $ret[] = [
      "label" => "Central Heating Plant",
      "value" => "Central Heating Plant"
    ];
    $ret[] = [
      "label" => "Chancellor's Office/EVC",
      "value" => "Chancellor's Office/EVC"
    ];
    $ret[] = [
      "label" => "Chemistry & Biochemistry Department",
      "value" => "Chemistry & Biochemistry Department"
    ];
    $ret[] = [
      "label" => "Child Care/Early Education",
      "value" => "Child Care/Early Education"
    ];
    $ret[] = [
      "label" => "Classical Studies",
      "value" => "Classical Studies"
    ];
    $ret[] = [
      "label" => "Coastal Science & Policy Program",
      "value" => "Coastal Science & Policy Program"
    ];
    $ret[] = [
      "label" => "College Nine",
      "value" => "College Nine"
    ];
    $ret[] = [
      "label" => "College Scholars Program",
      "value" => "College Scholars Program"
    ];
    $ret[] = [
      "label" => "Colleges, Housing & Educational Services (CHES)",
      "value" => "Colleges, Housing & Educational Services (CHES)"
    ];
    $ret[] = [
      "label" => "Communications & Marketing",
      "value" => "Communications & Marketing"
    ];
    $ret[] = [
      "label" => "Community Rentals Office",
      "value" => "Community Rentals Office"
    ];
    $ret[] = [
      "label" => "Community Safety Program",
      "value" => "Community Safety Program"
    ];
    $ret[] = [
      "label" => "Community Studies Program",
      "value" => "Community Studies Program"
    ];
    $ret[] = [
      "label" => "Computational Media",
      "value" => "Computational Media"
    ];
    $ret[] = [
      "label" => "Computer Science and Engineering",
      "value" => "Computer Science and Engineering"
    ];
    $ret[] = [
      "label" => "Conduct and Community Standards Office",
      "value" => "Conduct and Community Standards Office"
    ];
    $ret[] = [
      "label" => "Conference Services",
      "value" => "Conference Services"
    ];
    $ret[] = [
      "label" => "Copier Program",
      "value" => "Copier Program"
    ];
    $ret[] = [
      "label" => "Copy Center",
      "value" => "Copy Center"
    ];
    $ret[] = [
      "label" => "Counseling & Psychological Services",
      "value" => "Counseling & Psychological Services"
    ];
    $ret[] = [
      "label" => "Cowell Academic Advising",
      "value" => "Cowell Academic Advising"
    ];
    $ret[] = [
      "label" => "Cowell College",
      "value" => "Cowell College"
    ];
    $ret[] = [
      "label" => "Creative Writing Program",
      "value" => "Creative Writing Program"
    ];
    $ret[] = [
      "label" => "Critical Race and Ethnic Studies",
      "value" => "Critical Race and Ethnic Studies"
    ];
    $ret[] = [
      "label" => "Crown College",
      "value" => "Crown College"
    ];
    $ret[] = [
      "label" => "Cultural Arts Diversity",
      "value" => "Cultural Arts Diversity"
    ];
    $ret[] = [
      "label" => "Dean of Students",
      "value" => "Dean of Students"
    ];
    $ret[] = [
      "label" => "Dickens Project",
      "value" => "Dickens Project"
    ];
    $ret[] = [
      "label" => "Digital Arts and New Media",
      "value" => "Digital Arts and New Media"
    ];
    $ret[] = [
      "label" => "Dining Services",
      "value" => "Dining Services"
    ];
    $ret[] = [
      "label" => "Disability Resource Center",
      "value" => "Disability Resource Center"
    ];
    $ret[] = [
      "label" => "Dispatch Center",
      "value" => "Dispatch Center"
    ];
    $ret[] = [
      "label" => "Division of Finance, Operations and Administration",
      "value" => "Division of Finance, Operations and Administration"
    ];
    $ret[] = [
      "label" => "Early Education Services",
      "value" => "Early Education Services"
    ];
    $ret[] = [
      "label" => "Earth & Planetary Sciences Department",
      "value" => "Earth & Planetary Sciences Department"
    ];
    $ret[] = [
      "label" => "East Asian Studies",
      "value" => "East Asian Studies"
    ];
    $ret[] = [
      "label" => "Ecology & Evolutionary Biology Department",
      "value" => "Ecology & Evolutionary Biology Department"
    ];
    $ret[] = [
      "label" => "Economics Department",
      "value" => "Economics Department"
    ];
    $ret[] = [
      "label" => "Education Department",
      "value" => "Education Department"
    ];
    $ret[] = [
      "label" => "Educational Opportunity Program",
      "value" => "Educational Opportunity Program"
    ];
    $ret[] = [
      "label" => "Educational Partnership Center",
      "value" => "Educational Partnership Center"
    ];
    $ret[] = [
      "label" => "Electrical and Computer Engineering",
      "value" => "Electrical and Computer Engineering"
    ];
    $ret[] = [
      "label" => "Emergency Services Office",
      "value" => "Emergency Services Office"
    ];
    $ret[] = [
      "label" => "Engineering Business Office",
      "value" => "Engineering Business Office"
    ];
    $ret[] = [
      "label" => "Enrollment Management",
      "value" => "Enrollment Management"
    ];
    $ret[] = [
      "label" => "Enterprise Financial Systems",
      "value" => "Enterprise Financial Systems"
    ];
    $ret[] = [
      "label" => "Environmental Health & Safety",
      "value" => "Environmental Health & Safety"
    ];
    $ret[] = [
      "label" => "Environmental Studies Department",
      "value" => "Environmental Studies Department"
    ];
    $ret[] = [
      "label" => "Equal Employment Opportunity/Affirmative Action",
      "value" => "Equal Employment Opportunity/Affirmative Action"
    ];
    $ret[] = [
      "label" => "Extramural Funds",
      "value" => "Extramural Funds"
    ];
    $ret[] = [
      "label" => "FAST / Accounts Payable",
      "value" => "FAST / Accounts Payable"
    ];
    $ret[] = [
      "label" => "Faculty and Staff Housing",
      "value" => "Faculty and Staff Housing"
    ];
    $ret[] = [
      "label" => "Feminist Studies Department",
      "value" => "Feminist Studies Department"
    ];
    $ret[] = [
      "label" => "Film and Digital Media Department",
      "value" => "Film and Digital Media Department"
    ];
    $ret[] = [
      "label" => "Financial Affairs",
      "value" => "Financial Affairs"
    ];
    $ret[] = [
      "label" => "Financial Aid and Scholarship Office",
      "value" => "Financial Aid and Scholarship Office"
    ];
    $ret[] = [
      "label" => "Financial Service Center",
      "value" => "Financial Service Center"
    ];
    $ret[] = [
      "label" => "Genome Bioinformatics Group",
      "value" => "Genome Bioinformatics Group"
    ];
    $ret[] = [
      "label" => "Genomics Institute",
      "value" => "Genomics Institute"
    ];
    $ret[] = [
      "label" => "German Studies",
      "value" => "German Studies"
    ];
    $ret[] = [
      "label" => "Global Engagement",
      "value" => "Global Engagement"
    ];
    $ret[] = [
      "label" => "Global Learning",
      "value" => "Global Learning"
    ];
    $ret[] = [
      "label" => "Graduate Studies Division",
      "value" => "Graduate Studies Division"
    ];
    $ret[] = [
      "label" => "Greenhouses",
      "value" => "Greenhouses"
    ];
    $ret[] = [
      "label" => "Harassment and Discrimination Prevention Investigation Unit",
      "value" => "Harassment and Discrimination Prevention Investigation Unit"
    ];
    $ret[] = [
      "label" => "History Department",
      "value" => "History Department"
    ];
    $ret[] = [
      "label" => "History of Art/Visual Culture",
      "value" => "History of Art/Visual Culture"
    ];
    $ret[] = [
      "label" => "History of Consciousness Department",
      "value" => "History of Consciousness Department"
    ];
    $ret[] = [
      "label" => "Housing Services",
      "value" => "Housing Services"
    ];
    $ret[] = [
      "label" => "Humanities Division",
      "value" => "Humanities Division"
    ];
    $ret[] = [
      "label" => "Humanities Institute",
      "value" => "Humanities Institute"
    ];
    $ret[] = [
      "label" => "IMS-Diving & Boating Safety Program",
      "value" => "IMS-Diving & Boating Safety Program"
    ];
    $ret[] = [
      "label" => "IMS-Fisheries Collaborative Program",
      "value" => "IMS-Fisheries Collaborative Program"
    ];
    $ret[] = [
      "label" => "IMS-Santa Cruz Predatory Bird Research Group",
      "value" => "IMS-Santa Cruz Predatory Bird Research Group"
    ];
    $ret[] = [
      "label" => "IMS-Seymour Marine Discovery Center",
      "value" => "IMS-Seymour Marine Discovery Center"
    ];
    $ret[] = [
      "label" => "ITS-Experience Strategy & Design",
      "value" => "ITS-Experience Strategy & Design"
    ];
    $ret[] = [
      "label" => "ITS-Information Security",
      "value" => "ITS-Information Security"
    ];
    $ret[] = [
      "label" => "ITS-Resource Planning & Management",
      "value" => "ITS-Resource Planning & Management"
    ];
    $ret[] = [
      "label" => "ITS-Technology & Services",
      "value" => "ITS-Technology & Services"
    ];
    $ret[] = [
      "label" => "ITS-Technology Engineering",
      "value" => "ITS-Technology Engineering"
    ];
    $ret[] = [
      "label" => "ITS-Vice Chancellor's Office",
      "value" => "ITS-Vice Chancellor's Office"
    ];
    $ret[] = [
      "label" => "Information Technology Services",
      "value" => "Information Technology Services"
    ];
    $ret[] = [
      "label" => "Innovation & Business Engagement (IBE) Hub",
      "value" => "Innovation & Business Engagement (IBE) Hub"
    ];
    $ret[] = [
      "label" => "Institute for International Economics",
      "value" => "Institute for International Economics"
    ];
    $ret[] = [
      "label" => "Institute for Scientist & Engineer Educators",
      "value" => "Institute for Scientist & Engineer Educators"
    ];
    $ret[] = [
      "label" => "Institute for Social Transformation",
      "value" => "Institute for Social Transformation"
    ];
    $ret[] = [
      "label" => "Institute of Marine Sciences",
      "value" => "Institute of Marine Sciences"
    ];
    $ret[] = [
      "label" => "Institute of the Arts and Sciences",
      "value" => "Institute of the Arts and Sciences"
    ];
    $ret[] = [
      "label" => "Institutional Research, Assessment & Policy Studies",
      "value" => "Institutional Research, Assessment & Policy Studies"
    ];
    $ret[] = [
      "label" => "International Education Office",
      "value" => "International Education Office"
    ];
    $ret[] = [
      "label" => "International Student & Scholar Services",
      "value" => "International Student & Scholar Services"
    ];
    $ret[] = [
      "label" => "Italian Studies",
      "value" => "Italian Studies"
    ];
    $ret[] = [
      "label" => "Jewish Studies",
      "value" => "Jewish Studies"
    ];
    $ret[] = [
      "label" => "John R. Lewis College",
      "value" => "John R. Lewis College"
    ];
    $ret[] = [
      "label" => "Kenneth S Norris Center for Natural History",
      "value" => "Kenneth S Norris Center for Natural History"
    ];
    $ret[] = [
      "label" => "Kresge College",
      "value" => "Kresge College"
    ];
    $ret[] = [
      "label" => "Languages and Applied Linguistics",
      "value" => "Languages and Applied Linguistics"
    ];
    $ret[] = [
      "label" => "Latin American & Latino Studies",
      "value" => "Latin American & Latino Studies"
    ];
    $ret[] = [
      "label" => "Learning Support Services",
      "value" => "Learning Support Services"
    ];
    $ret[] = [
      "label" => "Legal Studies",
      "value" => "Legal Studies"
    ];
    $ret[] = [
      "label" => "Library, University",
      "value" => "Library, University"
    ];
    $ret[] = [
      "label" => "Linguistics Department",
      "value" => "Linguistics Department"
    ];
    $ret[] = [
      "label" => "Literature Department",
      "value" => "Literature Department"
    ];
    $ret[] = [
      "label" => "MBEST Center",
      "value" => "MBEST Center"
    ];
    $ret[] = [
      "label" => "Mail Services",
      "value" => "Mail Services"
    ];
    $ret[] = [
      "label" => "Mary Porter Sesnon Art Gallery",
      "value" => "Mary Porter Sesnon Art Gallery"
    ];
    $ret[] = [
      "label" => "Mathematics Department",
      "value" => "Mathematics Department"
    ];
    $ret[] = [
      "label" => "Merrill College",
      "value" => "Merrill College"
    ];
    $ret[] = [
      "label" => "Microbiology & Environmental Toxicology Department",
      "value" => "Microbiology & Environmental Toxicology Department"
    ];
    $ret[] = [
      "label" => "Molecular, Cell, & Developmental Biology Department",
      "value" => "Molecular, Cell, & Developmental Biology Department"
    ];
    $ret[] = [
      "label" => "Music Department",
      "value" => "Music Department"
    ];
    $ret[] = [
      "label" => "NASA Ames Research Center",
      "value" => "NASA Ames Research Center"
    ];
    $ret[] = [
      "label" => "Natural Reserve System",
      "value" => "Natural Reserve System"
    ];
    $ret[] = [
      "label" => "Oakes College",
      "value" => "Oakes College"
    ];
    $ret[] = [
      "label" => "Ocean Sciences Department",
      "value" => "Ocean Sciences Department"
    ];
    $ret[] = [
      "label" => "Office for Diversity, Equity, and Inclusion",
      "value" => "Office for Diversity, Equity, and Inclusion"
    ];
    $ret[] = [
      "label" => "Office of Research",
      "value" => "Office of Research"
    ];
    $ret[] = [
      "label" => "Office of Research Administration",
      "value" => "Office of Research Administration"
    ];
    $ret[] = [
      "label" => "Office of Research Business & Operations",
      "value" => "Office of Research Business & Operations"
    ];
    $ret[] = [
      "label" => "Office of Research Compliance Administration",
      "value" => "Office of Research Compliance Administration"
    ];
    $ret[] = [
      "label" => "Office of Research Development",
      "value" => "Office of Research Development"
    ];
    $ret[] = [
      "label" => "Office of Sponsored Projects",
      "value" => "Office of Sponsored Projects"
    ];
    $ret[] = [
      "label" => "Office of the Registrar",
      "value" => "Office of the Registrar"
    ];
    $ret[] = [
      "label" => "Online Education",
      "value" => "Online Education"
    ];
    $ret[] = [
      "label" => "Orientations Office",
      "value" => "Orientations Office"
    ];
    $ret[] = [
      "label" => "Payroll Office",
      "value" => "Payroll Office"
    ];
    $ret[] = [
      "label" => "Philosophy Department",
      "value" => "Philosophy Department"
    ];
    $ret[] = [
      "label" => "Physical & Biological Sciences Division - Academic Personnel",
      "value" => "Physical & Biological Sciences Division - Academic Personnel"
    ];
    $ret[] = [
      "label" => "Physical & Biological Sciences Division - Communications",
      "value" => "Physical & Biological Sciences Division - Communications"
    ];
    $ret[] = [
      "label" => "Physical & Biological Sciences Division - Curriculum Management",
      "value" => "Physical & Biological Sciences Division - Curriculum Management"
    ];
    $ret[] = [
      "label" => "Physical & Biological Sciences Division - Dean's Office",
      "value" => "Physical & Biological Sciences Division - Dean's Office"
    ];
    $ret[] = [
      "label" => "Physical & Biological Sciences Division - Divisional Accounting",
      "value" => "Physical & Biological Sciences Division - Divisional Accounting"
    ];
    $ret[] = [
      "label" => "Physical & Biological Sciences Division - Facilities",
      "value" => "Physical & Biological Sciences Division - Facilities"
    ];
    $ret[] = [
      "label" => "Physical & Biological Sciences Division - Philanthropy",
      "value" => "Physical & Biological Sciences Division - Philanthropy"
    ];
    $ret[] = [
      "label" => "Physical & Biological Sciences Division - Research Accounting",
      "value" => "Physical & Biological Sciences Division - Research Accounting"
    ];
    $ret[] = [
      "label" => "Physical Planning Development & Operations - Business Office",
      "value" => "Physical Planning Development & Operations - Business Office"
    ];
    $ret[] = [
      "label" => "Physical Planning Development & Operations - Design & Construction Services",
      "value" => "Physical Planning Development & Operations - Design & Construction Services"
    ];
    $ret[] = [
      "label" => "Physical Planning Development & Operations - Engineering Services",
      "value" => "Physical Planning Development & Operations - Engineering Services"
    ];
    $ret[] = [
      "label" => "Physical Planning Development & Operations - Physical & Environmental Planning Services",
      "value" => "Physical Planning Development & Operations - Physical & Environmental Planning Services"
    ];
    $ret[] = [
      "label" => "Physical Planning Development & Operations - Physical Plant Services",
      "value" => "Physical Planning Development & Operations - Physical Plant Services"
    ];
    $ret[] = [
      "label" => "Physics Department",
      "value" => "Physics Department"
    ];
    $ret[] = [
      "label" => "Police Department",
      "value" => "Police Department"
    ];
    $ret[] = [
      "label" => "Politics Department",
      "value" => "Politics Department"
    ];
    $ret[] = [
      "label" => "Porter College",
      "value" => "Porter College"
    ];
    $ret[] = [
      "label" => "Procurement Services",
      "value" => "Procurement Services"
    ];
    $ret[] = [
      "label" => "Program in Community & Agroecology (PICA)",
      "value" => "Program in Community & Agroecology (PICA)"
    ];
    $ret[] = [
      "label" => "Psychology Department",
      "value" => "Psychology Department"
    ];
    $ret[] = [
      "label" => "Queer and Sexualities Studies",
      "value" => "Queer and Sexualities Studies"
    ];
    $ret[] = [
      "label" => "Rachel Carson College",
      "value" => "Rachel Carson College"
    ];
    $ret[] = [
      "label" => "Real Estate & Contract Services",
      "value" => "Real Estate & Contract Services"
    ];
    $ret[] = [
      "label" => "Receiving",
      "value" => "Receiving"
    ];
    $ret[] = [
      "label" => "Research Center for the Americas",
      "value" => "Research Center for the Americas"
    ];
    $ret[] = [
      "label" => "Research Integrity & Export Control",
      "value" => "Research Integrity & Export Control"
    ];
    $ret[] = [
      "label" => "Resource Centers",
      "value" => "Resource Centers"
    ];
    $ret[] = [
      "label" => "Retiree and Emeriti Center",
      "value" => "Retiree and Emeriti Center"
    ];
    $ret[] = [
      "label" => "Risk & Safety Services",
      "value" => "Risk & Safety Services"
    ];
    $ret[] = [
      "label" => "Risk Services",
      "value" => "Risk Services"
    ];
    $ret[] = [
      "label" => "SOAR/Student Media/Cultural Arts and Diversity (SOMeCA)",
      "value" => "SOAR/Student Media/Cultural Arts and Diversity (SOMeCA)"
    ];
    $ret[] = [
      "label" => "STEM Diversity Programs",
      "value" => "STEM Diversity Programs"
    ];
    $ret[] = [
      "label" => "Santa Cruz Institute for Particle Physics (SCIPP)",
      "value" => "Santa Cruz Institute for Particle Physics (SCIPP)"
    ];
    $ret[] = [
      "label" => "Science & Justice Research Center",
      "value" => "Science & Justice Research Center"
    ];
    $ret[] = [
      "label" => "Science Communication Program",
      "value" => "Science Communication Program"
    ];
    $ret[] = [
      "label" => "Sikh and Punjabi Studies",
      "value" => "Sikh and Punjabi Studies"
    ];
    $ret[] = [
      "label" => "Social Sciences Division",
      "value" => "Social Sciences Division"
    ];
    $ret[] = [
      "label" => "Sociology Department",
      "value" => "Sociology Department"
    ];
    $ret[] = [
      "label" => "South Asia Studies",
      "value" => "South Asia Studies"
    ];
    $ret[] = [
      "label" => "Spanish Studies",
      "value" => "Spanish Studies"
    ];
    $ret[] = [
      "label" => "Staff Advisory Board",
      "value" => "Staff Advisory Board"
    ];
    $ret[] = [
      "label" => "Staff Human Resources",
      "value" => "Staff Human Resources"
    ];
    $ret[] = [
      "label" => "Statistics",
      "value" => "Statistics"
    ];
    $ret[] = [
      "label" => "Stevenson College",
      "value" => "Stevenson College"
    ];
    $ret[] = [
      "label" => "Student Achievement & Equity Innovation",
      "value" => "Student Achievement & Equity Innovation"
    ];
    $ret[] = [
      "label" => "Student Affairs and Success Division",
      "value" => "Student Affairs and Success Division"
    ];
    $ret[] = [
      "label" => "Student Business Services",
      "value" => "Student Business Services"
    ];
    $ret[] = [
      "label" => "Student Health Center",
      "value" => "Student Health Center"
    ];
    $ret[] = [
      "label" => "Student Housing Services",
      "value" => "Student Housing Services"
    ];
    $ret[] = [
      "label" => "Student Media",
      "value" => "Student Media"
    ];
    $ret[] = [
      "label" => "Student Organization Advising and Resources (SOAR)",
      "value" => "Student Organization Advising and Resources (SOAR)"
    ];
    $ret[] = [
      "label" => "Student Success Equity Research Center (SSERC)",
      "value" => "Student Success Equity Research Center (SSERC)"
    ];
    $ret[] = [
      "label" => "Summer Session",
      "value" => "Summer Session"
    ];
    $ret[] = [
      "label" => "Sustainability Office",
      "value" => "Sustainability Office"
    ];
    $ret[] = [
      "label" => "The Village",
      "value" => "The Village"
    ];
    $ret[] = [
      "label" => "Theater Arts Department",
      "value" => "Theater Arts Department"
    ];
    $ret[] = [
      "label" => "Title IX Office",
      "value" => "Title IX Office"
    ];
    $ret[] = [
      "label" => "Training and Development",
      "value" => "Training and Development"
    ];
    $ret[] = [
      "label" => "Transfer & Re-Entry Services",
      "value" => "Transfer & Re-Entry Services"
    ];
    $ret[] = [
      "label" => "Transportation & Parking Services",
      "value" => "Transportation & Parking Services"
    ];
    $ret[] = [
      "label" => "UC Observatories",
      "value" => "UC Observatories"
    ];
    $ret[] = [
      "label" => "UC Santa Cruz Foundation",
      "value" => "UC Santa Cruz Foundation"
    ];
    $ret[] = [
      "label" => "UCSC Extension (UNEX)",
      "value" => "UCSC Extension (UNEX)"
    ];
    $ret[] = [
      "label" => "Undergraduate Education",
      "value" => "Undergraduate Education"
    ];
    $ret[] = [
      "label" => "University Business Services",
      "value" => "University Business Services"
    ];
    $ret[] = [
      "label" => "University Catering",
      "value" => "University Catering"
    ];
    $ret[] = [
      "label" => "University Center",
      "value" => "University Center"
    ];
    $ret[] = [
      "label" => "University Interfaith Council",
      "value" => "University Interfaith Council"
    ];
    $ret[] = [
      "label" => "University Relations",
      "value" => "University Relations"
    ];
    $ret[] = [
      "label" => "Visiting Faculty/Staff",
      "value" => "Visiting Faculty/Staff"
    ];
    $ret[] = [
      "label" => "Women's Center",
      "value" => "Women's Center"
    ];
    $ret[] = [
      "label" => "Writing Program",
      "value" => "Writing Program"
    ];

    return new WP_REST_Response($ret);
  }

  function divisioncode()
  {
    // $campusDirectoryAPI = new CampusDirectoryAPI(['automatedFeeds' => true]);
    // return new WP_REST_Response($campusDirectoryAPI->getDirDropdowns('ucscpersonpubdivision'));

    $ret = [];
    $ret[] = [
      "label" => "---",
      "value" => "---"
    ];
    $ret[] = [
      "label" => "Academic Affairs",
      "value" => "Academic Affairs"
    ];
    $ret[] = [
      "label" => "Academic Personnel Office",
      "value" => "Academic Personnel Office"
    ];
    $ret[] = [
      "label" => "Arts Division",
      "value" => "Arts Division"
    ];
    $ret[] = [
      "label" => "Baskin School of Engineering",
      "value" => "Baskin School of Engineering"
    ];
    $ret[] = [
      "label" => "Chancellor's Office/EVC",
      "value" => "Chancellor's Office/EVC"
    ];
    $ret[] = [
      "label" => "Division of Finance, Operations and Administration",
      "value" => "Division of Finance, Operations and Administration"
    ];
    $ret[] = [
      "label" => "Division of Global Engagement",
      "value" => "Division of Global Engagement"
    ];
    $ret[] = [
      "label" => "Graduate Studies Division",
      "value" => "Graduate Studies Division"
    ];
    $ret[] = [
      "label" => "Humanities Division",
      "value" => "Humanities Division"
    ];
    $ret[] = [
      "label" => "Information Technology Services",
      "value" => "Information Technology Services"
    ];
    $ret[] = [
      "label" => "Library, University",
      "value" => "Library, University"
    ];
    $ret[] = [
      "label" => "Office of Research",
      "value" => "Office of Research"
    ];
    $ret[] = [
      "label" => "Physical & Biological Sciences Division",
      "value" => "Physical & Biological Sciences Division"
    ];
    $ret[] = [
      "label" => "Planning and Budget",
      "value" => "Planning and Budget"
    ];
    $ret[] = [
      "label" => "Silicon Valley Initiatives",
      "value" => "Silicon Valley Initiatives"
    ];
    $ret[] = [
      "label" => "Social Sciences Division",
      "value" => "Social Sciences Division"
    ];
    $ret[] = [
      "label" => "Staff Human Resources",
      "value" => "Staff Human Resources"
    ];
    $ret[] = [
      "label" => "Student Affairs and Success",
      "value" => "Student Affairs and Success"
    ];
    $ret[] = [
      "label" => "Student Services",
      "value" => "Student Services"
    ];
    $ret[] = [
      "label" => "Undergraduate Education",
      "value" => "Undergraduate Education"
    ];
    $ret[] = [
      "label" => "University Extension",
      "value" => "University Extension"
    ];
    $ret[] = [
      "label" => "University Relations",
      "value" => "University Relations"
    ];
    $ret[] = [
      "label" => "Visiting Faculty/Staff",
      "value" => "Visiting Faculty/Staff"
    ];

    return new WP_REST_Response($ret);

  }

  function departmentcode()
  {

    $retDepts = get_transient('ucsc_depts');
    if (!$retDepts) {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://my.ucsc.edu/PSIGW/RESTListeningConnector/PSFT_CSPRD/SCX_CLASS_DEPTS_V2.v2/'. date("Y", strtotime("-7 months")) .'/Fall',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      $arrResponse = json_decode($response, true);
      $depts = $arrResponse['depts'];
      $retDepts = [];
      for($i=0; $i<count($depts); $i++) {
        $retDepts[] = [
          'label' => $depts[$i]['description'],
          'value' => $depts[$i]['code']
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

      set_transient('ucsc_depts', $retDepts, WEEK_IN_SECONDS);
    }
    return new WP_REST_Response($retDepts);

  }

  function subjectcode()
  {

    $retDepts = get_transient('ucsc_subjects');
    if (!$retDepts) {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://my.ucsc.edu/PSIGW/RESTListeningConnector/PSFT_CSPRD/SCX_CLASS_DEPTS_V2.v2/'. date("Y", strtotime("-7 months")) .'/Fall',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
      ));

      $response = curl_exec($curl);

      curl_close($curl);
      $arrResponse = json_decode($response, true);
      $depts = $arrResponse['depts'];
      $retDepts = [];
      for($i=0; $i<count($depts); $i++) {
        if (array_key_exists('subjects', $depts[$i])) {
          for($j=0; $j<count($depts[$i]['subjects']); $j++) {
            $retDepts[] = [
              'label' => $depts[$i]['subjects'][$j]['description'],
              'value' => $depts[$i]['subjects'][$j]['code']
            ];
          }
        }
      }

      function cmp($a, $b) {
        return strcmp($a['label'], $b['label']);
      }

      usort($retDepts, "cmp");

      array_unshift($retDepts, [
        'label' => '---',
        'value' => '---'
      ]);

      set_transient('ucsc_subjects', $retDepts, WEEK_IN_SECONDS);
    }
    return new WP_REST_Response($retDepts);

  }

  function networkSettingsNotifications()
  {
    if (isset($_GET['page']) && $_GET['page'] == 'ucsc-gutenberg-blocks-network-settings' && isset($_GET['updated'])) {
      echo '<div id="message" class="updated notice is-dismissible"><p>Settings updated.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
    }
  }

  function networkSaveSettings()
  {
    check_admin_referer('ucscplugin-validate'); // Nonce security check

    update_site_option('ldap_api_key', $_POST['ldap_api_key']);

    wp_redirect(add_query_arg(
      array(
        'page' => 'ucsc-gutenberg-blocks-network-settings',
        'updated' => true
      ),
      network_admin_url('settings.php')
    ));

    exit;
  }

  function networkSettingsLink()
  {
    add_submenu_page(
      'settings.php', // Parent element
      'UCSC Gutenberg Blocks Network Settings', // Text in browser title bar
      'UCSC Gutenberg Blocks Network Settings', // Text to be displayed in the menu.
      'manage_options', // Capability
      'ucsc-gutenberg-blocks-network-settings', // Page slug, will be displayed in URL
      array($this, 'networkSettingsPage') // Callback function which displays the page
    );
  }

  function networkSettingsPage()
  {
    echo '<div class="wrap">
		<h1>UCSC Gutenberg Blocks Network Settings</h1>
		<form method="post" action="edit.php?action=ucscplugin">';
    wp_nonce_field('ucscplugin-validate');
    echo '
			<table class="form-table">
				<tr>
					<th scope="row"><label for="ldap_api_key">LDAP API Key</label></th>
					<td>
						<input name="ldap_api_key" class="regular-text" type="text" id="ldap_api_key" value="' . esc_attr(get_site_option('ldap_api_key')) . '" />
					</td>
				</tr>
			</table>';
    submit_button();
    echo '</form></div>';
  }

  function settings()
  {
    add_settings_section('ucsc_gutenberg_blocks_section', null, null, 'ucsc_gutenberg_blocks_settings_page');

    add_settings_field('ldap_api_key', 'LDAP API Key', array($this, 'ldapKeyHTML'), 'ucsc_gutenberg_blocks_settings_page', 'ucsc_gutenberg_blocks_section');
    register_setting('ucsc_gutenberg_blocks', 'ldap_api_key', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));

    register_setting('ucsc_network_settings', 'ldap_api_key', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
  }

  function ldapKeyHTML()
  { ?>
    <input type="text" name="ldap_api_key" value="<?php echo esc_attr(get_option('ldap_api_key')) ?>" />
  <?php }

  function settingsLink()
  {
    add_options_page('UCSC Gutenberg Block Settings', 'UCSC Gutenberg Block Settings', 'manage_options', 'ucsc_gutenberg_blocks_settings_page', array($this, 'settingsPageHTML'));
  }

  function settingsPageHTML()
  { ?>
    <div class="wrap">
      <h1>UCSC Gutenberg Blocks Settings</h1>
      <form action="options.php" method="POST">
        <?php
        settings_fields('ucsc_gutenberg_blocks');
        do_settings_sections('ucsc_gutenberg_blocks_settings_page');
        submit_button();
        ?>
      </form>
    </div>
<?php }
}
