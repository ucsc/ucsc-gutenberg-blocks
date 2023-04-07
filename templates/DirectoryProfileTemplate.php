<?php

if ( file_exists( get_theme_file_path( 'header-plugin.php' ) ) ) {
	get_header( 'plugin' );
}
$cruzid = get_query_var('directoryprofilecruzid');
$campusDirectoryAPI = new CampusDirectoryAPI($attributes);
$profileData = $campusDirectoryAPI->getCampusDirData($cruzid,true)[0];

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
                <?php
                    for($i=1; $profileData["cn"] && $i<count($profileData["cn"])-1; $i++) {
                        echo "<p>{$profileData['cn'][$i]}</p>";
                    }
                ?>
                <p>
                    <svg class="svg-inline--fa fa-address-book fa-w-14" title="User" aria-labelledby="svg-inline--fa-title-2" data-prefix="fa" data-icon="address-book" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><title id="svg-inline--fa-title-2">User</title><path fill="currentColor" d="M436 160c6.627 0 12-5.373 12-12v-40c0-6.627-5.373-12-12-12h-20V48c0-26.51-21.49-48-48-48H48C21.49 0 0 21.49 0 48v416c0 26.51 21.49 48 48 48h320c26.51 0 48-21.49 48-48v-48h20c6.627 0 12-5.373 12-12v-40c0-6.627-5.373-12-12-12h-20v-64h20c6.627 0 12-5.373 12-12v-40c0-6.627-5.373-12-12-12h-20v-64h20zm-228-32c44.183 0 80 35.817 80 80s-35.817 80-80 80-80-35.817-80-80 35.817-80 80-80zm128 232c0 13.255-10.745 24-24 24H104c-13.255 0-24-10.745-24-24v-18.523c0-22.026 14.99-41.225 36.358-46.567l35.657-8.914c29.101 20.932 74.509 26.945 111.97 0l35.657 8.914C321.01 300.252 336 319.452 336 341.477V360z"></path></svg>
                    <?php
                        for($i=0; $profileData["title"] && $i<count($profileData["title"]); $i++) {
                            echo "{$profileData['title'][$i]}";
                            break;
                        }
                    ?>
                </p>
            </div>
            <div class="profileheaditem">

                <?php
                    for($i=0; $profileData["telephonenumber"] && $i<count($profileData["telephonenumber"])-1; $i++) {
                        echo "<p>";
                            echo '<svg class="svg-inline--fa fa-phone fa-w-16" title="User" aria-labelledby="svg-inline--fa-title-3" data-prefix="fa" data-icon="phone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-3">User</title><path fill="currentColor" d="M493.397 24.615l-104-23.997c-11.314-2.611-22.879 3.252-27.456 13.931l-48 111.997a24 24 0 0 0 6.862 28.029l60.617 49.596c-35.973 76.675-98.938 140.508-177.249 177.248l-49.596-60.616a24 24 0 0 0-28.029-6.862l-111.997 48C3.873 366.516-1.994 378.08.618 389.397l23.997 104C27.109 504.204 36.748 512 48 512c256.087 0 464-207.532 464-464 0-11.176-7.714-20.873-18.603-23.385z"></path></svg>';
                            echo "{$profileData['telephonenumber'][$i]}";
                        echo "</p>";
                    }
                    for($i=0; $profileData["facsimiletelephonenumber"] && $i<count($profileData["facsimiletelephonenumber"])-1; $i++) {
                        echo "<p>";
                            echo '<svg class="svg-inline--fa fa-phone fa-w-16" title="User" aria-labelledby="svg-inline--fa-title-3" data-prefix="fa" data-icon="phone" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-3">User</title><path fill="currentColor" d="M493.397 24.615l-104-23.997c-11.314-2.611-22.879 3.252-27.456 13.931l-48 111.997a24 24 0 0 0 6.862 28.029l60.617 49.596c-35.973 76.675-98.938 140.508-177.249 177.248l-49.596-60.616a24 24 0 0 0-28.029-6.862l-111.997 48C3.873 366.516-1.994 378.08.618 389.397l23.997 104C27.109 504.204 36.748 512 48 512c256.087 0 464-207.532 464-464 0-11.176-7.714-20.873-18.603-23.385z"></path></svg>';
                            echo "{$profileData['facsimiletelephonenumber'][$i]} (Fax)";
                        echo "</p>";
                    }
                ?>
                <p>
                    <svg class="svg-inline--fa fa-envelope fa-w-16" title="User" aria-labelledby="svg-inline--fa-title-4" data-prefix="fas" data-icon="envelope" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-4">User</title><path fill="currentColor" d="M502.3 190.8c3.9-3.1 9.7-.2 9.7 4.7V400c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V195.6c0-5 5.7-7.8 9.7-4.7 22.4 17.4 52.1 39.5 154.1 113.6 21.1 15.4 56.7 47.8 92.2 47.6 35.7.3 72-32.8 92.3-47.6 102-74.1 131.6-96.3 154-113.7zM256 320c23.2.4 56.6-29.2 73.4-41.4 132.7-96.3 142.8-104.7 173.4-128.7 5.8-4.5 9.2-11.5 9.2-18.9v-19c0-26.5-21.5-48-48-48H48C21.5 64 0 85.5 0 112v19c0 7.4 3.4 14.3 9.2 18.9 30.6 23.9 40.7 32.4 173.4 128.7 16.8 12.2 50.2 41.8 73.4 41.4z"></path></svg>
                    <?php
                        for($i=0; $profileData["mail"] && $i<count($profileData["mail"]); $i++) {
                            echo "<a href=\"{$profileData['mail'][$i]}\">{$profileData['mail'][$i]}</a>";
                        }
                    ?>
                </p>


                    <?php
                        for($i=0; $profileData["ucscpersonpubalternatemail"] && $i<count($profileData["ucscpersonpubalternatemail"]) - 1; $i++) {
                            echo "<p class='altemail'>";
                            echo '<svg class="svg-inline--fa fa-envelope fa-w-16" title="User" aria-labelledby="svg-inline--fa-title-5" data-prefix="far" data-icon="envelope" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><title id="svg-inline--fa-title-5">User</title><path fill="currentColor" d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm0 48v40.805c-22.422 18.259-58.168 46.651-134.587 106.49-16.841 13.247-50.201 45.072-73.413 44.701-23.208.375-56.579-31.459-73.413-44.701C106.18 199.465 70.425 171.067 48 152.805V112h416zM48 400V214.398c22.914 18.251 55.409 43.862 104.938 82.646 21.857 17.205 60.134 55.186 103.062 54.955 42.717.231 80.509-37.199 103.053-54.947 49.528-38.783 82.032-64.401 104.947-82.653V400H48z"></path></svg>';
                                echo "<a href=\"{$profileData['mail'][$i]}\">{$profileData['ucscpersonpubalternatemail'][$i]}</a>";
                            echo "</p>";
                        }
                    ?>

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

