<?php

if ( file_exists( get_theme_file_path( 'header-plugin.php' ) ) ) {
	get_header( 'plugin' );
}
?>
<link href="//static.ucsc.edu/css/directory-page.css" media="all" rel="stylesheet" type="text/css">
<style>
/* Simple clean profile page styling to match class-schedule */
.content-region {
    margin-block-start: 0 !important;
}

#wrapper {
    max-width: 900px;
    margin: 20px auto;
    padding: 0 20px;
}

#profilehead {
    margin-bottom: 30px;
}

.profileheaditem {
    margin-bottom: 20px;
}

/* Hide the SVG icons */
#profilehead svg,
.svg-inline--fa {
    display: none !important;
}

/* Main heading - name */
#profilehead h2 {
    font-family: Georgia, 'Times New Roman', serif;
    font-size: 2.5em;
    font-weight: normal;
    margin: 20px 0 10px 0;
    color: #000;
    clear: both;
}

/* Profile layout with image on left */
.profileheadimage {
    float: left;
    margin-right: 30px;
    margin-bottom: 20px;
}

.profileheadimage img {
    width: 180px;
    height: auto;
    display: block;
}

/* Basic info list */
.item-info {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.item-info > li {
    display: flex;
    padding: 8px 0;
    border-bottom: 1px solid #ccc;
    align-items: baseline;
}

.item-info > li:last-child {
    border-bottom: none;
}

.item-info > li > strong {
    flex: 0 0 180px;
    font-weight: bold;
    color: #000;
}

.item-info > li > div,
.item-info > li > span,
.item-info > li > ul {
    flex: 1;
    color: #000;
}

.item-info .inline-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: inline;
}

.item-info .inline-list li {
    display: inline;
}

.item-info .inline-list li:not(:last-child):after {
    content: ", ";
}

/* Profile body sections - two column layout */
#profilebody {
    clear: both;
    margin-top: 40px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

#profilebody > div {
    margin-bottom: 30px;
    break-inside: avoid;
}

#profilebody > div > p {
    margin: 0 0 15px 0;
}

#profilebody label {
    display: block;
    font-size: 1.4em;
    color: #000;
    font-weight: bold;
    margin: 0 0 15px 0;
    font-family: inherit;
}

#profilebody p {
    line-height: 1.6;
    margin: 0 0 10px 0;
}

@media (max-width: 768px) {
    #profilebody {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .item-info > li {
        flex-direction: column;
        align-items: flex-start;
    }

    .item-info > li > strong {
        flex: 1;
        margin-bottom: 5px;
    }
}

/* Clear floats */
#wrapper::after {
    content: "";
    display: table;
    clear: both;
}
</style>
<?php

$cruzid = get_query_var('directoryprofilecruzid');
$campusDirectoryAPI = new CampusDirectoryAPI($attributes);
$profileData = $campusDirectoryAPI->getCampusDirData($cruzid,true)[0];
$cruzidEmail = $profileData['0']['mail'][0];


function linkify($key, $str) {
    if ($key == "ucscpersonpubwebsite") {
        $website = explode(" ", $str);
        $str = "<a target='_blank' href='{$website[0]}'>{$website[1]}</a>";
    }
    return $str;
}
if (count($profileData)) {
  $profileData = $profileData[0];
  ?>
    <main class="is-layout-flow wp-block-group content-region" id="wp--skip-link--target" style="margin-block-start: var(--wp--preset--font-size--one);">
        <div class="has-global-padding is-layout-constrained wp-block-group">
            <nav class="breadcrumbs alignwide" role="navigation" aria-label="Breadcrumbs" itemprop="breadcrumb">
                <ul class="breadcrumbs__trail" itemscope="" itemtype="https://schema.org/BreadcrumbList">
                    <li class="breadcrumbs__crumb breadcrumbs__crumb--home" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
                        <a href="/" itemprop="item">
                            <span itemprop="name">Home</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </main>
    <div id="wrapper">
        <div id="profilehead">
            <div class="profileheaditem">
                <div class="profileheadimage">
                    <?php
                        $imgSrc = "";
                        if (array_key_exists('jpegphoto', $profileData)) {
                            $imgSrc = 'data:image/jpeg;base64, ' . base64_encode($profileData['jpegphoto'][0]);
                        } else {
                            $imgSrc = '//static.ucsc.edu/images/icon-slug.jpg';
                        }
                    ?>
                    <img src="<?php echo $imgSrc ?>" alt="<?php echo $profileData["cn"][0]; ?>">
                </div>
            </div>
            <div class="profileheaditem">
                <h2>
                    <svg class="svg-inline--fa fa-user-circle fa-w-16" title="User" aria-labelledby="svg-inline--fa-title-1" data-prefix="fa" data-icon="user-circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-1">User</title><path fill="currentColor" d="M8 256C8 119.033 119.033 8 256 8s248 111.033 248 248-111.033 248-248 248S8 392.967 8 256zm72.455 125.868C119.657 436.446 183.673 472 256 472s136.343-35.554 175.545-90.132c-3.141-26.99-22.667-49.648-49.538-56.366l-32.374-8.093C323.565 339.79 290.722 352 256 352s-67.565-12.21-93.634-34.591l-32.374 8.093c-26.87 6.718-46.396 29.376-49.537 56.366zM144 208c0 61.856 50.144 112 112 112s112-50.144 112-112S317.856 96 256 96s-112 50.144-112 112z"></path></svg>
                    <?php echo $profileData["cn"][0]; ?>
                </h2>

                <ul class="item-info">
                    <?php if (!empty($profileData['title'])): ?>
                    <li>
                        <strong>Title</strong>
                        <ul class="inline-list">
                            <?php foreach ((array)$profileData['title'] as $item): ?>
                                <li><?php echo esc_html($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['ucscpersonpubdivision'])): ?>
                    <li>
                        <strong>Division</strong>
                        <?php
                        $divisions = (array)$profileData['ucscpersonpubdivision'];
                        foreach ($divisions as $idx => $item) {
                            if ($idx > 0) echo '; ';
                            echo esc_html($item);
                        }
                        ?>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['ucscpersonpubdepartmentnumber'])): ?>
                    <li>
                        <strong>Department</strong>
                        <ul class="inline-list">
                            <?php foreach ((array)$profileData['ucscpersonpubdepartmentnumber'] as $item): ?>
                                <li><?php echo esc_html($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['ucscpersonpubaffiliateddepartment'])): ?>
                    <li>
                        <strong>Affiliations</strong>
                        <?php
                        $affiliations = (array)$profileData['ucscpersonpubaffiliateddepartment'];
                        foreach ($affiliations as $idx => $item) {
                            if ($idx > 0) echo ', ';
                            echo esc_html($item);
                        }
                        ?>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['telephonenumber'])): ?>
                    <li>
                        <strong>Phone</strong>
                        <div>
                            <span style="white-space: nowrap" class="p-tel">
                                <?php
                                $phones = (array)$profileData['telephonenumber'];
                                foreach ($phones as $idx => $item) {
                                    if ($idx > 0) echo ', ';
                                    echo esc_html($item);
                                }
                                ?>
                            </span>
                        </div>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['mail']) || !empty($profileData['ucscpersonpubalternatemail'])): ?>
                    <li>
                        <strong>Email</strong>
                        <ul class="inline-list">
                            <?php
                            $seenEmails = [];
                            if (!empty($profileData['mail'])) {
                                foreach ((array)$profileData['mail'] as $item) {
                                    $seenEmails[] = $item;
                                    echo '<li><span class="u-email"><a href="mailto:' . esc_attr($item) . '">' . esc_html($item) . '</a></span></li>';
                                }
                            }
                            if (!empty($profileData['ucscpersonpubalternatemail'])) {
                                foreach ((array)$profileData['ucscpersonpubalternatemail'] as $item) {
                                    if (!in_array($item, $seenEmails)) {
                                        echo '<li><span class="u-email"><a href="mailto:' . esc_attr($item) . '">' . esc_html($item) . '</a></span></li>';
                                    }
                                }
                            }
                            ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['facsimiletelephonenumber'])): ?>
                    <li>
                        <strong>Fax</strong>
                        <ul class="inline-list">
                            <?php foreach ((array)$profileData['facsimiletelephonenumber'] as $item): ?>
                                <li><span style="white-space: nowrap" class="p-tel"><?php echo esc_html($item); ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['ucscpersonpubwebsite'])): ?>
                    <li>
                        <strong>Website</strong>
                        <ul class="inline-list">
                            <?php foreach ((array)$profileData['ucscpersonpubwebsite'] as $item): ?>
                                <?php
                                $parts = explode(' ', $item, 2);
                                $url = $parts[0];
                                $label = isset($parts[1]) ? $parts[1] : $url;
                                ?>
                                <li><span class="u-url"><a href="<?php echo esc_url($url); ?>" target="_website"><?php echo esc_html($label); ?></a></span></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['ucscprimarylocationpubofficialname'])): ?>
                    <li>
                        <strong>Office Location</strong>
                        <ul class="inline-list">
                            <li>
                                <span class="p-extended-address">
                                    <?php
                                    $locations = (array)$profileData['ucscprimarylocationpubofficialname'];
                                    $details = !empty($profileData['ucscpersonpubofficelocationdetail']) ? (array)$profileData['ucscpersonpubofficelocationdetail'] : [];
                                    echo esc_html($locations[0]);
                                    if (!empty($details[0])) {
                                        echo ', ' . esc_html($details[0]);
                                    }
                                    ?>
                                </span>
                            </li>
                            <?php if (!empty($profileData['roomnumber'])): ?>
                            <li><span class="p-extended-address"><?php echo esc_html(((array)$profileData['roomnumber'])[0]); ?></span></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['ucscpersonpubofficehours'])): ?>
                    <li>
                        <strong>Office Hours</strong>
                        <ul class="inline-list">
                            <?php foreach ((array)$profileData['ucscpersonpubofficehours'] as $item): ?>
                                <li><?php echo esc_html($item); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['ucscpersonpubmailstop'])): ?>
                    <li>
                        <strong>Mail Stop</strong>
                        <?php echo esc_html(((array)$profileData['ucscpersonpubmailstop'])[0]); ?>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['street'])): ?>
                    <li>
                        <strong>Mailing Address</strong>
                        <ul class="inline-list">
                            <li><?php echo esc_html(((array)$profileData['street'])[0]); ?></li>
                            <li>Santa Cruz CA 95064</li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['ucscpersonpubexpertisereference'])): ?>
                    <li>
                        <strong>Faculty Areas of Expertise</strong>
                        <span class="p-label">
                            <?php
                            $expertise = (array)$profileData['ucscpersonpubexpertisereference'];
                            foreach ($expertise as $idx => $item) {
                                if ($idx > 0) echo ', ';
                                echo esc_html($item);
                            }
                            ?>
                        </span>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($profileData['ucscpersonpubfacultycourses'])): ?>
                    <li>
                        <strong>Courses Taught</strong>
                        <?php
                        $courses = (array)$profileData['ucscpersonpubfacultycourses'];
                        foreach ($courses as $idx => $item) {
                            if ($idx > 0) echo '; ';
                            echo esc_html($item);
                        }
                        ?>
                    </li>
                    <?php endif; ?>
                </ul>

            </div>
        </div>
        <div id="profilebody">
            <?php
                $bodyfields = [
                    "ucscpersonpubpronouns" => "Pronouns",
                    "ucscpersonpubdivision" => "Division",
                    "ucscpersonpubdivision" => "Department/College/Unit",
                    "title" => "Title",
                    "ucscpersonpubaffiliation" => "Affiliation",
                    "ucscpersonpubaffiliateddepartment" => "Other UCSC Campus Affiliation",
                    "ucscpersonpubstafftype" => "Staff Type",
                    "ucscpersonpubwebsite" => "Web Site",
                    "ucscprimarylocationpubofficialname|ucscpersonpubofficelocationdetail" => "Primary Office Location",
                    "roomnumber" => "Additional Location(s)",
                    "ucscpersonpubofficehours" => "Office Hours",
                    "ucscpersonpubmailstop" => "Mail Stop",
                    "ucscpersonpubdescription" => "Biography, Education and Training",
                    "ucscpersonpubareaofexpertise" => "Summary of Expertise",
                    "ucscpersonpubresearchinterest" => "Research Interests",
                    "ucscpersonpubteachinginterest" => "Teaching Interests",
                    "ucscpersonpubawardshonorsgrants" => "Awards, Honors and Grants",
                    "ucscpersonpubselectedexhibition" => "Selected Exhibitions",
                    "ucscpersonpubselectedperformance" => "Selected Performances",
                    "ucscpersonpubselectedpresentation" => "Selected Presentations",
                    "ucscpersonpubselectedpublication" => "Selected Publications",
                    "ucscpersonpubselectedrecording" => "Selected Recordings"
                ];
                foreach ($bodyfields as $key => $title) {
                    $arrKey = explode("|", $key);
                    if (count($arrKey) == 1) {
                        if ($profileData[$key]['count']) {
                            echo "<div><p>";
                                echo "<label>{$title}</label>";
                                for ($i = 0; $i < $profileData[$key]['count']; $i++) {
                                    echo linkify($key, $profileData[$key][$i]);
                                    if (($i + 1) < $profileData[$key]['count']) echo "<br />";
                                }
                            echo "</p></div>";
                        }
                    } else {
                        if ($profileData[$arrKey[0]]['count'] || $profileData[$arrKey[1]]['count']) {
                            echo "<div><p>";
                                echo "<label>{$title}</label>";
                                for ($i = 0; $i < $profileData[$arrKey[0]]['count']; $i++) {
                                    echo $profileData[$arrKey[0]][$i];
                                }
                                echo "<br />";
                                for ($i = 0; $i < $profileData[$arrKey[1]]['count']; $i++) {
                                    echo $profileData[$arrKey[1]][$i];
                                }
                            echo "</p></div>";
                        }
                    }
                }
            ?>
        </div>
    </div>
    <?php
} else {
  echo "<div id=\"wrapper\"><h3>CruzID: $cruzid not found.</h3></div>";
}
if ( file_exists( get_theme_file_path( 'footer-plugin.php' ) ) ) {
	get_footer( 'plugin' );
}

