<?php
require('BakalariGradesAPI.class.php');

class BakalariGradesAPIMock extends BakalariGradesAPI {

  public function getGradesDetails() {
    $file = 'podezrele-datum.html';
    $html = file_get_contents(__DIR__ . '/test_source/' . $file);
    $grades = $this->parseGradesDetails($html);
    return $grades;
  }

}
