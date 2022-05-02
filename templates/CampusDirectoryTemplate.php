<h1>Faculty Directory</h1>
<?php if (strlen($items["introParagraph"])) { ?>
  <div class="intro-paragraph">
    <?php echo $items["introParagraph"] ?>
  </div>
<?php } ?>
<?php
// echo "<pre>";
// echo print_r($items['people'], true);
// echo "</pre>";
// echo "<pre>";
// echo print_r($items['nodeContent'], true);
// echo "</pre>";

  if ($items['dirLayout'] == "table") {
  ?>
    <div class="section-container table-page">
      <div class="section-body">
        <div class="content-box">
          <table class="table-wrapper">
            <tbody class="section-body">
              <tr>
                <th scope="col" class="titles">Name</th>
                <?php
                  foreach($items['informationToDisplay'] as $disItem) {
                    foreach($disItem as $headerInfo) {
                      echo '<th scope="col" class="titles">' . $headerInfo . '</th>';
                    }
                  }
                ?>
              </tr>
              <?php
                $people = $items['items'];
                for($i = 0; $i < count($people); $i++) {
                  echo '<tr class="item-body">';
                    echo '<td class="item-name">';
                      if ($items['linkToProfile']) echo '<a class="u-url" href="/?directoryprofilecruzid=' . $people[$i]['uid'][0] . '">';
                        echo '<span class="p-name" style="white-space: nowrap;">' . $people[$i]['cn'][0] . '</span>';
                      if ($items['linkToProfile']) echo '</a>';
                    echo '</td>';
                    foreach($items['informationToDisplay'] as $disItem) {
                      foreach($disItem as $key => $value) {
                        echo '<td class="item-info table-renderer">';
                          echo '<strong>' . $people[$i][$key][0] . '</strong>';
                          echo '<ul class="inline-list">';
                            echo '<li>' . $people[$i][$key][0] . '</li>';
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
    ?> <div class="section-container tiled-page"> <?php
  } else {
    ?> <div class="section-container list-page"> <?php
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
                        echo '<a class="u-url" href="?directoryprofilecruzid=' . $people[$i]['uid'][0] . '">';
                      }
                      echo '<span class="p-name">' . $people[$i]['cn'][0] . '</span>';
                      if ($items['linkToProfile']) {
                        echo '</a>';
                      }
                    ?>
                  </h3>
                  <ul class="item-info list-renderer">
                    <?php
                      foreach($items['informationToDisplay'] as $disItem) {
                        foreach($disItem as $key => $value){
                          if ($people[$i][$key][0]) {
                            echo "<li>";
                              echo "<strong>{$disItem[$key]}</strong>";
                              echo '<ul class="inline-list">';
                                if ($disItem[$key] == "Email") {
                                  if (array_key_exists("mail", $people[$i])) {
                                    for($j=0; $j<count($people[$i]["mail"]); $j++) {
                                        echo "<li><span class=\"u-email\"><a href=\"{$people[$i]['mail'][$j]}\">{$people[$i]['mail'][$j]}</a></span></li>";
                                    }
                                  }
                                  if (array_key_exists("ucscpersonpubalternatemail", $people[$i])) {
                                    for($j=0; $j<count($people[$i]["ucscpersonpubalternatemail"]); $j++) {
                                        echo "<li><span class=\"u-email\"><a href=\"{$people[$i]['mail'][$j]}\">{$people[$i]['ucscpersonpubalternatemail'][$j]}</a></span></li>";
                                    }
                                  }
                                } else if ($disItem[$key] == "Office Location") {
                                  echo "<li>{$people[$i]['ucscprimarylocationpubofficialname'][0]}, {$people[$i]['ucscpersonpubofficelocationdetail'][0]}</li>";

                                } else {
                                  echo "<li>{$people[$i][$key][0]}</li>";
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
                  if (array_key_exists('jpegphoto', $people[$i])) {
                    $imgSrc = "data:image/jpeg;base64, " .  base64_encode($people[$i]['jpegphoto'][0]);
                  } else {
                    $imgSrc = "//static.ucsc.edu/images/icon-slug.jpg";
                  }
                  if ($items['linkToProfile']) echo '<a class="u-url square-img" href="?directoryprofilecruzid=' . $people[$i]['uid'][0] . '">';
                  echo "<img src='" . $imgSrc . "' class='item-image square-img imgLiquid imgLiquid_bgSize imgLiquid_ready' style='object-fit: cover;' />";
                  if ($items['linkToProfile']) echo '</a>';
                ?>
              </div>
              <?php
            }
          ?>
        </div>
      </div>
    <?php
}
