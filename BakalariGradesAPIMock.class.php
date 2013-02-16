<?php
require('BakalariGradesAPI.class.php');

class BakalariGradesAPIMock extends BakalariGradesAPI {

  public function getGradesDetails() {
    $html = file_get_contents(__DIR__ . '/test_source/grades_31-8-2012.html');
    return $this->parseGradesDetails($html);
  }

}
