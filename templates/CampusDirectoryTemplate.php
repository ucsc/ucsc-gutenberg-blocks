<?php
// echo "<pre>";
// echo print_r($items['informationToDisplay'], true);
// echo "</pre>";
// echo "<pre>";
// echo print_r($items['nodeContent'], true);
// echo "</pre>";
// echo "<pre>";
// echo print_r($items['items'][0]['mail']['count'], true);
// echo "</pre>";

$displayPhoto = false;
for($i=0; $i<count($items['informationToDisplay']); $i++) {
  foreach($items['informationToDisplay'][$i] as $key => $value) {
    if ($key == "b64image") {
      $displayPhoto = true;
    }
  }
}

$individualPageUrl = $items['nodeContent']['linkOutToCampusDirectory'] ?
                        'https://campusdirectory.ucsc.edu/cd_detail?uid=' :
                        '?directoryprofilecruzid=';

  if ($items['dirLayout'] == "table") {
  ?>
    <div class="section-container ucsc-block-directory table-page">
      <div class="section-body">
        <div class="content-box">
          <table class="table-wrapper">
            <tbody class="section-body">
              <tr>
                <th scope="col" class="titles">Name</th>
                <?php
                  foreach($items['informationToDisplay'] as $disItem) {
                    foreach($disItem as $headerInfo) {
                      if ($headerInfo == "Email") continue;
                      echo '<th scope="col" class="titles">' . esc_html($headerInfo) . '</th>';
                    }
                  }
                ?>
              </tr>
              <?php
                $people = $items['items'];
                for($i = 0; $i < count($people); $i++) {
                  echo '<tr class="item-body">';
                    echo '<td class="item-name">';
                      if ($items['linkToProfile']) echo '<a class="u-url" href="' . esc_url($individualPageUrl . $people[$i]['uid'][0]) . '">';
                        echo '<span class="p-name" style="white-space: nowrap;">' . esc_html($people[$i]['cn'][0]) . '</span>';
                      if ($items['linkToProfile']) echo '</a>';
                    echo '</td>';
                    foreach($items['informationToDisplay'] as $disItem) {
                      foreach($disItem as $key => $value) {
                        echo '<td class="item-info table-renderer">';
                          echo '<strong>' . esc_html($people[$i][$key][0]) . '</strong>';
                          echo '<ul class="inline-list">';
                            if ($value == "Campus Email" || $value == "Other Email") {
                              $mail = $people[$i][$key][0];
                              echo '<li><a href="' . esc_url('mailto:' . $mail) . '">' . esc_html($mail) . '</a></li>';
                            } else {
                              if ($value == "Email") continue;
                              echo '<li>' . esc_html($people[$i][$key][0]) . '</li>';
                            }
                          echo '</ul>';
                        echo '</td>';
                      }
                    }
                  echo '</tr>';
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php
} else {
  if ($items['dirLayout'] == "tiled") {
    ?> <div class="section-container ucsc-block-directory tiled-page"> <?php
  } else {
    ?> <div class="section-container ucsc-block-directory list-page"> <?php
  }
    ?>
        <div class="section-body">
          <?php
            $people = $items['items'];
            for($i = 0; $i < count($people); $i++){
              ?>
              <div class="section-item h-card wrap">
                <div class="item-body">
                  <h3 class="item-name">
                    <?php
                      if ($items['linkToProfile']) {
                        echo '<a class="u-url" href="' . esc_url($individualPageUrl . $people[$i]['uid'][0]) . '">';
                      }
                      echo '<span class="p-name">' . esc_html($people[$i]['cn'][0]) . '</span>';
                      if ($items['linkToProfile']) {
                        echo '</a>';
                      }
                    ?>
                  </h3>
                  <ul class="item-info list-renderer">
                    <?php
                      foreach($items['informationToDisplay'] as $disItem) {
                        foreach($disItem as $key => $value){
                          if (array_key_exists($key, $people[$i]) && $people[$i][$key][0]) {
                            echo "<li>";
                              echo "<strong>" . esc_html($disItem[$key]) . "</strong>";
                              echo '<ul class="inline-list">';
                                if ($disItem[$key] == "Campus Email" ) {
                                  if (array_key_exists("mail", $people[$i])) {
                                    for($j=0; $j<$people[$i]["mail"]['count']; $j++) {
                                        $mail = $people[$i]['mail'][$j];
                                        echo '<li><span><a class="u-email kwa" href="' . esc_url('mailto:' . $mail) . '">' . esc_html($mail) . '</a></span></li>';
                                    }
                                  }
                                } else if ($disItem[$key] == "Other Email") {
                                  if (array_key_exists("ucscpersonpubalternatemail", $people[$i])) {
                                    for($j=0; $j<$people[$i]["ucscpersonpubalternatemail"]["count"]; $j++) {
                                        $altMail = $people[$i]['ucscpersonpubalternatemail'][$j];
                                        echo '<li><span><a class="u-email" href="' . esc_url('mailto:' . $altMail) . '">' . esc_html($altMail) . '</a></span></li>';
                                    }
                                  }
                                } else if ($disItem[$key] == "Office Location") {
                                  echo "<li>" . esc_html($people[$i]['ucscprimarylocationpubofficialname'][0] . ', ' . $people[$i]['ucscpersonpubofficelocationdetail'][0]) . "</li>";

                                } else if ($disItem[$key] == "Website") {
                                  for($j=0; $j<$people[$i]["ucscpersonpubwebsite"]["count"]; $j++) {
                                    $arrWebsite = explode(" ", $people[$i]["ucscpersonpubwebsite"][$j]);
                                    $url = array_shift($arrWebsite);
                                    $label = implode(" ", $arrWebsite);
                                    echo "<li><span><a class=\"u-website\" href='" . esc_url($url) . "'>" . esc_html($label) . "</a></span></li>";
                                  }
                                } else {
                                  echo "<li>" . esc_html($people[$i][$key][0]) . "</li>";
                                }
                              echo '</ul>';
                            echo "</li>";
                          }
                        }
                      }
                    ?>
                  </ul>
                </div>
                <?php
                  if ($displayPhoto) {
                    $profileUid = !empty($people[$i]['uid'][0]) ? $people[$i]['uid'][0] : '';
                    if (!empty($profileUid)) {
                      $imgSrc = 'https://campusdirectory.ucsc.edu/photo.php?type=people&uid=' . rawurlencode($profileUid);
                    } else {
                      $imgSrc = '//static.ucsc.edu/images/icon-slug.jpg';
                    }
                    if ($items['linkToProfile']) echo '<a class="u-url square-img" href="' . esc_url($individualPageUrl . $people[$i]['uid'][0]) . '">';
                    $fallbackSrc = esc_url('//static.ucsc.edu/images/icon-slug.jpg');
                    echo '<img src="' . esc_url($imgSrc) . '" onerror="this.onerror=null;this.src=\'' . $fallbackSrc . '\';" class="item-image square-img imgLiquid imgLiquid_bgSize imgLiquid_ready" alt="Profile picture of ' . esc_attr($people[$i]['cn'][0]) . '" />';
                    if ($items['linkToProfile']) echo '</a>';
                  }
                ?>
              </div>
              <?php
            }
          ?>
        </div>
      </div>
    <?php
}
