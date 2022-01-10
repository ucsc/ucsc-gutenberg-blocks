<div class="intro-paragraph">
  <?php echo $items["introParagraph"] ?>
</div>
<?php
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
                      if ($items['linkToProfile']) echo '<a class="u-url" href="/?directoryprofilecruzid=' . $people[$i]['uid'] . '">';
                        echo '<span class="p-name" style="white-space: nowrap;">' . $people[$i]['cn'] . '</span>';
                      if ($items['linkToProfile']) echo '</a>';
                    echo '</td>';
                    foreach($items['informationToDisplay'] as $disItem) {
                      foreach($disItem as $key => $value) {
                        echo '<td class="item-info table-renderer">';
                          echo '<strong>' . $people[$i][$key] . '</strong>';
                          echo '<ul class="inline-list">';
                            echo '<li>' . $people[$i][$key] . '</li>';
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
                        echo '<a class="u-url" href="?directoryprofilecruzid=' . $people[$i]['uid'] . '">';
                      }
                      echo '<span class="p-name">' . $people[$i]['cn'] . '</span>';
                      if ($items['linkToProfile']) {
                        echo '</a>';
                      }
                    ?>
                  </h3>
                  <ul class="item-info list-renderer">
                    <?php
                      foreach($items['informationToDisplay'] as $disItem) {
                        foreach($disItem as $key => $value){
                          if ($people[$i][$key]) {
                            echo "<li>";
                              echo "<strong>{$disItem[$key]}</strong>";
                              echo '<ul class="inline-list">';
                                echo "<li>{$people[$i][$key]}</li>";
                              echo '</ul>';
                            echo "</li>";
                          }
                        }
                      }
                    ?>
                  </ul>
                </div>
                <?php
                  $imgUrl = "//static.ucsc.edu/images/icon-slug.jpg";
                  if ($people[$i]['b64image']) {
                    $imgUrl = "/sites/default/files/cache/directory/" . $people[$i]['uid'] . ".jpg";
                  }
                  echo '<div class="item-image square-img imgLiquid imgLiquid_bgSize imgLiquid_ready" style=\'background-image: url("' . $imgUrl . '"); background-size: cover; background-position: center center; background-repeat: no-repeat;\'>';
                    if ($items['linkToProfile']) echo '<a class="u-url" href="/directoryprofile/' . $people[$i]['uid'] . '" style="display: block; width: 100%; height: 100%;">';
                    echo '<img src="" alt="Individual profile page for ' . $people[$i]['cn'] . '" style="display: none;">';
                    if ($items['linkToProfile']) echo '</a>';
                  echo '</div>';
                ?>
              </div>
              <?php
            }
          ?>
        </div>
      </div>
    <?php
}
