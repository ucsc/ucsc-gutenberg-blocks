<?php

if ( file_exists( get_theme_file_path( 'header-plugin.php' ) ) ) {
	get_header( 'plugin' );
}
?>
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
    <div class="has-global-padding is-layout-constrained wp-block-group alignwide">

        <h1 id="title" class="page-title"><span class="p-name"><?php echo esc_html($profileData["cn"][0]); ?></span></h1>

        <div id="teacher-info" class="section-container person list-page">
            <div class="section-body">
                <div class="section-item h-card wrap">
                    <div class="item-image square-img imgLiquid">
                        <?php
                            $profileUid = !empty($profileData['uid'][0]) ? $profileData['uid'][0] : $cruzid;
                            if (!empty($profileUid)) {
                                $imgSrc = 'https://campusdirectory.ucsc.edu/photo.php?type=people&uid=' . rawurlencode($profileUid);
                            } else {
                                $imgSrc = '//static.ucsc.edu/images/icon-slug.jpg';
                            }
                        ?>
                        <img src="<?php echo esc_url($imgSrc); ?>" alt="<?php echo esc_attr($profileData["cn"][0]); ?>">
                    </div>
                    <div class="item-body">
                        <ul class="item-info">
                            <?php if (!empty($profileData['title']) && $profileData['title']['count']): ?>
                            <li>
                                <strong>Title</strong>
                                <ul class="inline-list">
                                    <?php for ($i = 0; $i < $profileData['title']['count']; $i++): ?>
                                        <li><?php echo esc_html($profileData['title'][$i]); ?></li>
                                    <?php endfor; ?>
                                </ul>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubdivision']) && $profileData['ucscpersonpubdivision']['count']): ?>
                            <li>
                                <strong>Division</strong>
                                <?php
                                for ($i = 0; $i < $profileData['ucscpersonpubdivision']['count']; $i++) {
                                    if ($i > 0) echo '; ';
                                    echo esc_html($profileData['ucscpersonpubdivision'][$i]);
                                }
                                ?>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubdepartmentnumber']) && $profileData['ucscpersonpubdepartmentnumber']['count']): ?>
                            <li>
                                <strong>Department</strong>
                                <ul class="inline-list">
                                    <?php for ($i = 0; $i < $profileData['ucscpersonpubdepartmentnumber']['count']; $i++): ?>
                                        <li><?php echo esc_html($profileData['ucscpersonpubdepartmentnumber'][$i]); ?></li>
                                    <?php endfor; ?>
                                </ul>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubaffiliateddepartment']) && $profileData['ucscpersonpubaffiliateddepartment']['count']): ?>
                            <li>
                                <strong>Affiliations</strong>
                                <?php
                                for ($i = 0; $i < $profileData['ucscpersonpubaffiliateddepartment']['count']; $i++) {
                                    if ($i > 0) echo ', ';
                                    echo esc_html($profileData['ucscpersonpubaffiliateddepartment'][$i]);
                                }
                                ?>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($profileData['telephonenumber']) && $profileData['telephonenumber']['count']): ?>
                            <li>
                                <strong>Phone</strong>
                                <div>
                                    <span style="white-space: nowrap" class="p-tel">
                                        <?php
                                        for ($i = 0; $i < $profileData['telephonenumber']['count']; $i++) {
                                            if ($i > 0) echo ', ';
                                            echo esc_html($profileData['telephonenumber'][$i]);
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
                                    if (!empty($profileData['mail']) && $profileData['mail']['count']) {
                                        for ($i = 0; $i < $profileData['mail']['count']; $i++) {
                                            $item = $profileData['mail'][$i];
                                            $seenEmails[] = $item;
                                            echo '<li><span class="u-email"><a href="mailto:' . esc_attr($item) . '">' . esc_html($item) . '</a></span></li>';
                                        }
                                    }
                                    if (!empty($profileData['ucscpersonpubalternatemail']) && $profileData['ucscpersonpubalternatemail']['count']) {
                                        for ($i = 0; $i < $profileData['ucscpersonpubalternatemail']['count']; $i++) {
                                            $item = $profileData['ucscpersonpubalternatemail'][$i];
                                            if (!in_array($item, $seenEmails)) {
                                                echo '<li><span class="u-email"><a href="mailto:' . esc_attr($item) . '">' . esc_html($item) . '</a></span></li>';
                                            }
                                        }
                                    }
                                    ?>
                                </ul>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($profileData['facsimiletelephonenumber']) && $profileData['facsimiletelephonenumber']['count']): ?>
                            <li>
                                <strong>Fax</strong>
                                <ul class="inline-list">
                                    <?php for ($i = 0; $i < $profileData['facsimiletelephonenumber']['count']; $i++): ?>
                                        <li><span style="white-space: nowrap" class="p-tel"><?php echo esc_html($profileData['facsimiletelephonenumber'][$i]); ?></span></li>
                                    <?php endfor; ?>
                                </ul>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubwebsite']) && $profileData['ucscpersonpubwebsite']['count']): ?>
                            <li>
                                <strong>Website</strong>
                                <ul class="inline-list">
                                    <?php for ($i = 0; $i < $profileData['ucscpersonpubwebsite']['count']; $i++): ?>
                                        <?php
                                        $parts = explode(' ', $profileData['ucscpersonpubwebsite'][$i], 2);
                                        $url = $parts[0];
                                        $label = isset($parts[1]) ? $parts[1] : $url;
                                        ?>
                                        <li><span class="u-url"><a href="<?php echo esc_url($url); ?>" target="_website"><?php echo esc_html($label); ?></a></span></li>
                                    <?php endfor; ?>
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
                                            echo esc_html($profileData['ucscprimarylocationpubofficialname'][0]);
                                            if (!empty($profileData['ucscpersonpubofficelocationdetail'][0])) {
                                                echo ', ' . esc_html($profileData['ucscpersonpubofficelocationdetail'][0]);
                                            }
                                            ?>
                                        </span>
                                    </li>
                                    <?php if (!empty($profileData['roomnumber'])): ?>
                                    <li><span class="p-extended-address"><?php echo esc_html($profileData['roomnumber'][0]); ?></span></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubofficehours']) && $profileData['ucscpersonpubofficehours']['count']): ?>
                            <li>
                                <strong>Office Hours</strong>
                                <ul class="inline-list">
                                    <?php for ($i = 0; $i < $profileData['ucscpersonpubofficehours']['count']; $i++): ?>
                                        <li><?php echo esc_html($profileData['ucscpersonpubofficehours'][$i]); ?></li>
                                    <?php endfor; ?>
                                </ul>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubmailstop'])): ?>
                            <li>
                                <strong>Mail Stop</strong>
                                <?php echo esc_html($profileData['ucscpersonpubmailstop'][0]); ?>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($profileData['street'])): ?>
                            <li>
                                <strong>Mailing Address</strong>
                                <ul class="inline-list">
                                    <li><?php echo esc_html($profileData['street'][0]); ?></li>
                                    <li>Santa Cruz CA 95064</li>
                                </ul>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubexpertisereference']) && $profileData['ucscpersonpubexpertisereference']['count']): ?>
                            <li>
                                <strong>Faculty Areas of Expertise</strong>
                                <span class="p-label">
                                    <?php
                                    for ($i = 0; $i < $profileData['ucscpersonpubexpertisereference']['count']; $i++) {
                                        if ($i > 0) echo ', ';
                                        echo esc_html($profileData['ucscpersonpubexpertisereference'][$i]);
                                    }
                                    ?>
                                </span>
                            </li>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubfacultycourses']) && $profileData['ucscpersonpubfacultycourses']['count']): ?>
                            <li>
                                <strong>Courses Taught</strong>
                                <?php
                                for ($i = 0; $i < $profileData['ucscpersonpubfacultycourses']['count']; $i++) {
                                    if ($i > 0) echo '; ';
                                    echo esc_html($profileData['ucscpersonpubfacultycourses'][$i]);
                                }
                                ?>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div><!-- .item-body -->
                </div><!-- .section-item.h-card.wrap -->

                <div class="section-item">
                    <div class="item-body expertise">
                        <?php
                            $expertiseFields = [
                                "ucscpersonpubareaofexpertise" => "Summary of Expertise",
                                "ucscpersonpubresearchinterest" => "Research Interests",
                                "ucscpersonpubdescription" => "Biography, Education and Training",
                                "ucscpersonpubteachinginterest" => "Teaching Interests",
                                "ucscpersonpubawardshonorsgrants" => "Awards, Honors and Grants",
                                "ucscpersonpubselectedexhibition" => "Selected Exhibitions",
                                "ucscpersonpubselectedperformance" => "Selected Performances",
                                "ucscpersonpubselectedpresentation" => "Selected Presentations",
                                "ucscpersonpubselectedpublication" => "Selected Publications",
                                "ucscpersonpubselectedrecording" => "Selected Recordings"
                            ];
                            foreach ($expertiseFields as $key => $title) {
                                if (!empty($profileData[$key]) && $profileData[$key]['count']) {
                                    echo '<div class="item-expertise">';
                                    echo '<h2 class="h3-style">' . esc_html($title) . '</h2>';
                                    echo '<div class="item-expertise">';
                                    for ($i = 0; $i < $profileData[$key]['count']; $i++) {
                                        echo '<p>' . linkify($key, $profileData[$key][$i]) . '</p>';
                                    }
                                    echo '</div>';
                                    echo '</div>';
                                }
                            }
                        ?>
                    </div><!-- .item-body.expertise -->
                </div><!-- .section-item -->

            </div><!-- .section-body -->
        </div><!-- #teacher-info -->

    </div>
    <?php
} else {
  echo "<div class=\"has-global-padding is-layout-constrained wp-block-group alignwide\"><h2 class=\"h3-style\">CruzID: " . esc_html($cruzid) . " not found.</h2></div>";
}
if ( file_exists( get_theme_file_path( 'footer-plugin.php' ) ) ) {
	get_footer( 'plugin' );
}
