<?php

// When set by CampusDirectory::renderDirectoryProfile, this template is being
// included inside the queried page's the_content, so the theme already renders
// the header, navigation, footer, and <main>. When unset (standalone
// template_include path for pretty /directory/<cruzid>/ URLs) we render that
// chrome ourselves.
$directory_profile_inline = !empty($directory_profile_inline);
if (!isset($attributes)) $attributes = [];

if (!$directory_profile_inline) {
	if ( file_exists( get_theme_file_path( 'header-plugin.php' ) ) ) {
		get_header( 'plugin' );
	} else {
		get_header();
	}
}
?>
<?php

$cruzid = get_query_var('directoryprofilecruzid');
$campusDirectoryAPI = new CampusDirectoryAPI($attributes);
$profileData = $campusDirectoryAPI->getCampusDirData($cruzid,true)[0];
$cruzidEmail = $profileData['0']['mail'][0];


function linkify($key, $str) {
    if ($key == "ucscpersonpubwebsite") {
        $parts = explode(" ", $str, 2);
        $url   = esc_url($parts[0]);
        $label = isset($parts[1]) ? esc_html($parts[1]) : esc_html($parts[0]);
        $str   = "<a target='_blank' rel='noopener' href='{$url}'>{$label}</a>";
    }

    // Sanitize LDAP HTML content — allow safe markup, strip dangerous tags/attributes
    $str = wp_kses_post($str);

    // Remove title attributes from links only when they duplicate the link text (a11y: WCAG 2.4.4)
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $str, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    foreach ($dom->getElementsByTagName('a') as $link) {
        if (!$link->hasAttribute('title')) continue;
        $titleText = trim($link->getAttribute('title'));
        $linkText  = trim($link->textContent);
        if (strcasecmp($titleText, $linkText) === 0 || str_starts_with($titleText, $linkText)) {
            $link->removeAttribute('title');
        }
    }
    $str = $dom->saveHTML();
    // DOMDocument wraps output in html/body tags; strip them
    $str = preg_replace('~^.*<body>|</body>.*$~s', '', $str);
    // Remove the XML encoding declaration
    $str = str_replace('<?xml encoding="UTF-8">', '', $str);
    return $str;
}
if (count($profileData)) {
  $profileData = $profileData[0];
  ?>
    <?php if (!$directory_profile_inline) : ?>
    <main class="is-layout-flow wp-block-group content-region" id="wp--skip-link--target" style="margin-block-start: var(--wp--preset--font-size--one);">
    <?php endif; ?>
        <div class="has-global-padding is-layout-constrained wp-block-group">
            <nav class="breadcrumbs alignwide" aria-label="Breadcrumbs" itemprop="breadcrumb">
                <ul class="breadcrumbs__trail" itemscope="" itemtype="https://schema.org/BreadcrumbList">
                    <li class="breadcrumbs__crumb breadcrumbs__crumb--home" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
                        <a href="/" itemprop="item">
                            <span itemprop="name">Home</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

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
                        <img src="<?php echo esc_url($imgSrc); ?>" onerror="this.onerror=null;this.src='<?php echo esc_url('//static.ucsc.edu/images/icon-slug.jpg'); ?>';" alt="Portrait of <?php echo esc_attr($profileData["cn"][0]); ?>">
                    </div>
                    <div class="item-body">
                        <dl class="item-info">
                            <?php if (!empty($profileData['title']) && $profileData['title']['count']): ?>
                            <div>
                                <dt>Title</dt>
                                <dd><ul class="inline-list">
                                    <?php for ($i = 0; $i < $profileData['title']['count']; $i++): ?>
                                        <li><?php echo esc_html($profileData['title'][$i]); ?></li>
                                    <?php endfor; ?>
                                </ul></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubdivision']) && $profileData['ucscpersonpubdivision']['count']): ?>
                            <div>
                                <dt>Division</dt>
                                <dd><?php
                                for ($i = 0; $i < $profileData['ucscpersonpubdivision']['count']; $i++) {
                                    if ($i > 0) echo '; ';
                                    echo esc_html($profileData['ucscpersonpubdivision'][$i]);
                                }
                                ?></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubdepartmentnumber']) && $profileData['ucscpersonpubdepartmentnumber']['count']): ?>
                            <div>
                                <dt>Department</dt>
                                <dd><ul class="inline-list">
                                    <?php for ($i = 0; $i < $profileData['ucscpersonpubdepartmentnumber']['count']; $i++): ?>
                                        <li><?php echo esc_html($profileData['ucscpersonpubdepartmentnumber'][$i]); ?></li>
                                    <?php endfor; ?>
                                </ul></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubaffiliateddepartment']) && $profileData['ucscpersonpubaffiliateddepartment']['count']): ?>
                            <div>
                                <dt>Affiliations</dt>
                                <dd><?php
                                for ($i = 0; $i < $profileData['ucscpersonpubaffiliateddepartment']['count']; $i++) {
                                    if ($i > 0) echo ', ';
                                    echo esc_html($profileData['ucscpersonpubaffiliateddepartment'][$i]);
                                }
                                ?></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['telephonenumber']) && $profileData['telephonenumber']['count']): ?>
                            <div>
                                <dt>Phone</dt>
                                <dd>
                                    <span style="white-space: nowrap" class="p-tel">
                                        <?php
                                        for ($i = 0; $i < $profileData['telephonenumber']['count']; $i++) {
                                            if ($i > 0) echo ', ';
                                            echo esc_html($profileData['telephonenumber'][$i]);
                                        }
                                        ?>
                                    </span>
                                </dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['mail']) || !empty($profileData['ucscpersonpubalternatemail'])): ?>
                            <div>
                                <dt>Email</dt>
                                <dd><ul class="inline-list">
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
                                </ul></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['facsimiletelephonenumber']) && $profileData['facsimiletelephonenumber']['count']): ?>
                            <div>
                                <dt>Fax</dt>
                                <dd><ul class="inline-list">
                                    <?php for ($i = 0; $i < $profileData['facsimiletelephonenumber']['count']; $i++): ?>
                                        <li><span style="white-space: nowrap" class="p-tel"><?php echo esc_html($profileData['facsimiletelephonenumber'][$i]); ?></span></li>
                                    <?php endfor; ?>
                                </ul></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubwebsite']) && $profileData['ucscpersonpubwebsite']['count']): ?>
                            <div>
                                <dt>Website</dt>
                                <dd><ul class="inline-list">
                                    <?php for ($i = 0; $i < $profileData['ucscpersonpubwebsite']['count']; $i++): ?>
                                        <?php
                                        $parts = explode(' ', $profileData['ucscpersonpubwebsite'][$i], 2);
                                        $url = $parts[0];
                                        $label = isset($parts[1]) ? $parts[1] : $url;
                                        ?>
                                        <li><span class="u-url"><a href="<?php echo esc_url($url); ?>" target="_website" rel="noopener"><?php echo esc_html($label); ?></a></span></li>
                                    <?php endfor; ?>
                                </ul></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscprimarylocationpubofficialname'])): ?>
                            <div>
                                <dt>Office Location</dt>
                                <dd><ul class="inline-list">
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
                                </ul></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubofficehours']) && $profileData['ucscpersonpubofficehours']['count']): ?>
                            <div>
                                <dt>Office Hours</dt>
                                <dd><ul class="inline-list">
                                    <?php for ($i = 0; $i < $profileData['ucscpersonpubofficehours']['count']; $i++): ?>
                                        <li><?php echo esc_html($profileData['ucscpersonpubofficehours'][$i]); ?></li>
                                    <?php endfor; ?>
                                </ul></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubmailstop'])): ?>
                            <div>
                                <dt>Mail Stop</dt>
                                <dd><?php echo esc_html($profileData['ucscpersonpubmailstop'][0]); ?></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['street'])): ?>
                            <div>
                                <dt>Mailing Address</dt>
                                <dd><ul class="inline-list">
                                    <li><?php echo esc_html($profileData['street'][0]); ?></li>
                                    <li>Santa Cruz CA 95064</li>
                                </ul></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubexpertisereference']) && $profileData['ucscpersonpubexpertisereference']['count']): ?>
                            <div>
                                <dt>Faculty Areas of Expertise</dt>
                                <dd><span class="p-label">
                                    <?php
                                    for ($i = 0; $i < $profileData['ucscpersonpubexpertisereference']['count']; $i++) {
                                        if ($i > 0) echo ', ';
                                        echo esc_html($profileData['ucscpersonpubexpertisereference'][$i]);
                                    }
                                    ?>
                                </span></dd>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($profileData['ucscpersonpubfacultycourses']) && $profileData['ucscpersonpubfacultycourses']['count']): ?>
                            <div>
                                <dt>Courses Taught</dt>
                                <dd><?php
                                for ($i = 0; $i < $profileData['ucscpersonpubfacultycourses']['count']; $i++) {
                                    if ($i > 0) echo '; ';
                                    echo esc_html($profileData['ucscpersonpubfacultycourses'][$i]);
                                }
                                ?></dd>
                            </div>
                            <?php endif; ?>
                        </dl>
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
    <?php if (!$directory_profile_inline) : ?>
    </main>
    <?php endif; ?>
    <?php
} else {
  echo "<div class=\"has-global-padding is-layout-constrained wp-block-group alignwide\"><h2 class=\"h3-style\">CruzID: " . esc_html($cruzid) . " not found.</h2></div>";
}
if (!$directory_profile_inline) {
	if ( file_exists( get_theme_file_path( 'footer-plugin.php' ) ) ) {
		get_footer( 'plugin' );
	} else {
		get_footer();
	}
}
