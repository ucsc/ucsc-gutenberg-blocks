<?php

get_header();
$cruzid = get_query_var('directoryprofilecruzid');
$campusDirectoryAPI = new CampusDirectoryAPI($attributes);
$profileData = $campusDirectoryAPI->getCampusDirData($cruzid,true);
if (count($profileData)) {
  $profileData = $profileData[0];
  ?>
    <div id="wrapper">
        <div id='directoryprofile'>
            <div class="pname"><?php echo $profileData["cn"][0]; ?></div>
            <div class="photo"><img src="<?php echo "/wp-content/uploads/directoryimages/{$profileData['uid'][0]}.jpg"; ?>" alt="<?php echo $profileData["cn"][0]; ?>">
            </div>
            <div class="details">
                <ul>
                    <li><strong>Title</strong>
                        <ul>
                            <?php
                                for($i=0; $i<count($profileData["title"]); $i++) {
                                    echo "<li>{$profileData['title'][$i]}</li>";
                                }
                            ?>
                        </ul>
                    </li>
                    <li><strong>Division</strong>
                        <?php
                            for($i=0; $i<count($profileData["ucscpersonpubdivision"]); $i++) {
                                echo "{$profileData['ucscpersonpubdivision'][$i]}";
                                if ($i < count($profileData["ucscpersonpubdivision"]) - 2) echo ", ";
                            }
                        ?>
                    </li>
                    <li><strong>Department</strong>
                        <ul>
                            <?php
                                for($i=0; $i<count($profileData["departmentnumber"]); $i++) {
                                    echo "<li>{$profileData['departmentnumber'][$i]}</li>";
                                }
                            ?>
                        </ul>
                    </li>
                    <?php if (count($profileData["ucscpersonpubaffiliateddepartment"])) { ?>
                        <li><strong>Affiliations</strong>
                            <?php
                                for($i=0; $i<count($profileData["ucscpersonpubaffiliateddepartment"]); $i++) {
                                    echo "{$profileData['ucscpersonpubaffiliateddepartment'][$i]}";
                                    if ($i < count($profileData["ucscpersonpubaffiliateddepartment"]) - 2) echo ", ";
                                }
                            ?>
                        </li>
                    <?php } ?>
                    <li><strong>Phone</strong>
                        <div>
                            <?php
                                for($i=0; $i<count($profileData["telephonenumber"]); $i++) {
                                    echo "<span class=\"p-tel\">{$profileData['telephonenumber'][$i]}";
                                    if ($i < count($profileData["telephonenumber"]) - 2) echo ", ";
                                    echo "</span>";
                                }
                            ?>
                        </div>
                    </li>
                    <li><strong>Email</strong>
                        <ul>
                            <?php
                                for($i=0; $i<count($profileData["mail"]); $i++) {
                                    echo "<li><span class=\"u-email\"><a href=\"{$profileData['mail'][$i]}\">{$profileData['mail'][$i]}</a></span></li>";
                                }
                                for($i=0; $i<count($profileData["ucscpersonpubalternatemail"]); $i++) {
                                    echo "<li><span class=\"u-email\"><a href=\"{$profileData['mail'][$i]}\">{$profileData['ucscpersonpubalternatemail'][$i]}</a></span></li>";
                                }
                            ?>
                        </ul>
                    </li>
                    <li><strong>Office Location</strong>
                        <ul>
                            <li><span class="p-extended-address"><?php echo "{$profileData['ucscprimarylocationpubofficialname'][0]}, {$profileData['ucscpersonpubofficelocationdetail'][0]}"; ?></span></li>
                        </ul>
                    </li>
                    <li><strong>Mail Stop</strong> <?php echo $profileData['ucscpersonpubmailstop'][0]; ?></li>
                    <?php if (count($profileData["street"])
                            || count($profileData["ucscpersonpubl"])
                            || count($profileData["ucscpersonpubst"])
                            || count($profileData["ucscpersonpubpostalcode"])
                            || count($profileData["ucscpersonpubco"]) ) { ?>
                        <li><strong>Mailing Address</strong>
                            <ul>
                                <?php if (count($profileData["street"])) echo "<li>{$profileData["street"][0]}</li>"; ?>
                                <li>
                                    <?php if (count($profileData["ucscpersonpubl"])) echo "{$profileData["ucscpersonpubl"][0]}"; ?>
                                    <?php if (count($profileData["ucscpersonpubst"])) echo "{$profileData["ucscpersonpubst"][0]}"; ?>
                                    <?php if (count($profileData["ucscpersonpubpostalcode"])) echo "{$profileData["ucscpersonpubpostalcode"][0]}"; ?>
                                </li>
                                <?php if (count($profileData["ucscpersonpubco"])) echo "<li>{$profileData["ucscpersonpubco"][0]}</li>"; ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php if (count($profileData["ucscpersonpubexpertisereference"])) { ?>
                        <li>
                            <strong>Faculty Areas of Expertise</strong>
                            <span class="p-label">
                                <?php
                                    for ($i=0; $i<count($profileData["ucscpersonpubexpertisereference"]); $i++) {
                                        echo "<span class=\"p-label\">{$profileData['ucscpersonpubexpertisereference'][$i]}";
                                        if ($i < count($profileData["ucscpersonpubexpertisereference"]) - 2) echo ", ";
                                        echo "</span>";
                                    }
                                ?>
                            </span>
                        </li>
                    <?php } ?>
                    <?php if (count($profileData["ucscpersonpubfacultycourses"])) { ?>
                        <li><strong>Courses</strong>
                                <?php
                                    for ($i=0; $i<count($profileData["ucscpersonpubfacultycourses"]); $i++) {
                                        echo "{$profileData['ucscpersonpubfacultycourses'][$i]}";
                                        if ($i < count($profileData["ucscpersonpubfacultycourses"]) - 2) echo ", ";
                                    }
                                ?>
                        </li>
                    <?php } ?>
                    <?php if (count($profileData["ucscpersonpubadvisees"])) { ?>
                        <li><strong>Advisees, Grad Students, Researchers</strong>
                                <?php
                                    for ($i=0; $i<count($profileData["ucscpersonpubadvisees"]); $i++) {
                                        $adviseesExploded = explode(",", $profileData['ucscpersonpubadvisees'][$i]);
                                        $uid = explode("=", $adviseesExploded[0]);
                                        if (strlen($uid[1])) {
                                            $strCruzids .= $uid[1] . ",";
                                        }
                                    }
                                    $adviseesData = $campusDirectoryAPI->getCampusDirData($strCruzids,true);
                                    for ($i=0; $i<count($adviseesData); $i++) {
                                        echo "{$adviseesData[$i]['cn'][0]}";
                                        if ($i < count($adviseesData) - 1) echo ", ";
                                    }
                                ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="items-expertise">
                <?php if (count($profileData['ucscpersonpubareaofexpertise'])) { ?>
                <div class="item-expertise">
                    <h3>Summary of Expertise</h3>
                    <p><?php echo $profileData['ucscpersonpubareaofexpertise'][0] ?></p>
                </div>
                <?php } ?>
                <?php if (count($profileData['ucscpersonpubresearchinterest'])) { ?>
                    <div class="item-expertise">
                        <h3>Research Interests</h3>
                        <?php echo $profileData['ucscpersonpubresearchinterest'][0]; ?>
                    </div>
                <?php } ?>
                <?php if (count($profileData['ucscpersonpubdescription'])) { ?>
                    <div class="item-expertise">
                        <h3>Biography, Education and Training</h3>
                        <?php echo $profileData['ucscpersonpubdescription'][0]; ?>
                    </div>
                <?php } ?>
                <?php if (count($profileData['ucscpersonpubawardshonorsgrants'])) { ?>
                    <div class="item-expertise">
                        <h3>Honors, Awards and Grants</h3>
                        <?php echo $profileData['ucscpersonpubawardshonorsgrants'][0]; ?>
                    </div>
                <?php } ?>
                <?php if (count($profileData['ucscpersonpubselectedpublication'])) { ?>
                    <div class="item-expertise">
                        <h3>Selected Publications</h3>
                        <?php echo $profileData['ucscpersonpubselectedpublication'][0]; ?>
                    </div>
                <?php } ?>
            </div>
            <div class="items-expertise">
                <?php if (count($profileData['ucscpersonpubselectedpresentation'])) { ?>
                    <div class="item-expertise">
                        <h3>Selected Presentations</h3>
                        <?php echo $profileData['ucscpersonpubselectedpresentation'][0]; ?>
                    </div>
                <?php } ?>
                <?php if (count($profileData['ucscpersonpubselectedexhibition'])) { ?>
                    <div class="item-expertise">
                        <h3>Selected Exhibitions</h3>
                        <?php echo $profileData['ucscpersonpubselectedexhibition'][0]; ?>
                    </div>
                <?php } ?>
                <?php if (count($profileData['ucscpersonpubselectedpublication'])) { ?>
                    <div class="item-expertise">
                        <h3>Selected Recordings</h3>
                        <?php echo $profileData['ucscpersonpubselectedpublication'][0]; ?>
                    </div>
                <?php } ?>
                <?php if (count($profileData['ucscpersonpubselectedrecording'])) { ?>
                    <div class="item-expertise">
                        <h3>Selected Performances</h3>
                        <?php echo $profileData['ucscpersonpubselectedrecording'][0]; ?>
                    </div>
                <?php } ?>
                <?php if (count($profileData['ucscpersonpubteachinginterest'])) { ?>
                    <div class="item-expertise">
                        <h3>Teaching Interests</h3>
                        <?php echo $profileData['ucscpersonpubteachinginterest'][0]; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
} else {
  echo "<div id=\"wrapper\"><h3>CruzID: $cruzid not found.</h3></div>";
}
get_footer();

