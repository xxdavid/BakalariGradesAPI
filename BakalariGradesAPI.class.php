<?php
require('simple_html_dom.php');

class BakalariGradesAPI {

  private $html;
  private $viewstate;
  private $eventValidation;
  private $username;
  private $password;
  private $host;
  private $subjectID;
  private $cookie;

  public function __construct($username,$password,$host,$cookie) {
    $this->username = $username;
    $this->password = $password;
    $this->host = $host;
    $this->cookie = $cookie;
  }

  public function version() {
    return (float) '0.1';
  }

  private function parseViewstate($html) {
    $viewstatePosition1 = strpos ($html, '<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="');
    $viewstatePosition2 = strpos ($html, '" />', $viewstatePosition1);
    return substr($html, $viewstatePosition1+64, $viewstatePosition2 - $viewstatePosition1 - 64);
  }

  private function parseEventValidation($html) {
    $eventValidationPostion1 = strpos ($html, '<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="');
    $eventValidationPostion2 = strpos ($html, '" />', $eventValidationPostion1);
    return substr($html, $eventValidationPostion1+76, $eventValidationPostion2 - $eventValidationPostion1 - 76);
  }

  private function implodeParams($params) {
    $string = "";
    $i = 0;
    foreach ($params as $key => $val) {
      if ($i > 0) {
        $string .= "&";
      }
      $string .= urlencode($key) . "=" . urlencode($val);
      $i++;
    }
    return $string;
  }

  private function fetchViewstate() {
    // Getting Viewstate and Cookies
    $ch1 = curl_init();
    curl_setopt($ch1, CURLOPT_COOKIEJAR, $this->cookie);
    //curl_setopt($ch1, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($ch1, CURLOPT_URL,$this->host."/login.aspx");
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_HEADER, true);
    $html = curl_exec ($ch1);
    curl_close($ch1);
    $viewstate = $this->parseViewstate($html);
    return $viewstate;
  }

  private function login($viewstate) {
    // Logging in
    $ch2 = curl_init();
    //curl_setopt($ch2, CURLOPT_COOKIEJAR, $this->cookie);
    curl_setopt($ch2, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($ch2, CURLOPT_URL, $this->host . "/login.aspx");
    curl_setopt($ch2, CURLOPT_POST, 1);

    $params = array();
    $params['__LASTFOCUS'] = '';
    $params['__EVENTTARGET'] = '';
    $params['__EVENTARGUMENT'] = '';
    $params['__VIEWSTATE'] = $viewstate;
    $params['ctl00$cphmain$TextBoxjmeno'] = $this->username;
    $params['ctl00$cphmain$TextBoxheslo'] = $this->password;
    $params['ctl00$cphmain$ButtonPrihlas'] = '';
    $implodedParams = $this->implodeParams($params);

    curl_setopt($ch2, CURLOPT_POSTFIELDS, $implodedParams);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_HEADER, true);
    $loginHtml = curl_exec($ch2);
    curl_close($ch2);
  }

  private function fetchGrades() {
    $ch3 = curl_init();
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER,1);
    //curl_setopt($ch3, CURLOPT_COOKIEJAR, $this->cookie);
    curl_setopt($ch3, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($ch3, CURLOPT_URL,$this->host."/prehled.aspx?s=2");
    $html = curl_exec($ch3);
    curl_close($ch3);
    return $html;
  }

  private function fetchSubject($subjectID, $viewstate, $eventvalidation) {
    //Subject page
    $ch4 = curl_init();
    //curl_setopt($ch4, CURLOPT_COOKIEJAR, $this->cookie);
    curl_setopt($ch4, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($ch4, CURLOPT_URL,$this->host."/prehled.aspx?s=2");
    curl_setopt($ch4, CURLOPT_POST, 1);
    $params = array();
    $params['__EVENTTARGET'] = $subjectID;
    $params['__EVENTARGUMENT'] = '';
    $params['__LASTFOCUS'] = '';
    $params['__VIEWSTATE'] = $viewstate;
    $params['__EVENTVALIDATION'] = $eventvalidation;
    $params['ctl00$cphmain$Flyout2$Checkdatumy'] = 'on';  // must be sent - libver 17.5.2012
    //$params['hlavnimenuSI'] = '2i0';
    //$params['ctl00$cphmain$listdoba'] = 'pololeti';
    //$params['ctl00$cphmain$Flyout2$listrazeni2'] = '0';
    //$params['ctl00$cphmain$Flyout2$Checktypy'] = 'on';
    $params['ctl00$cphmain$Checkdetail'] = 'on';
    $implodedParams = $this->implodeParams($params);
    curl_setopt($ch4, CURLOPT_POSTFIELDS, $implodedParams);
    curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch4, CURLOPT_HEADER, true);
    $html = curl_exec($ch4);
    curl_close($ch4);
    return $html;
  }

  private function fetchDetails($viewstate, $eventvalidation) {
    //Subject page
    $ch4 = curl_init();
    curl_setopt($ch4, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($ch4, CURLOPT_URL,$this->host."/prehled.aspx?s=2");
    curl_setopt($ch4, CURLOPT_POST, 1);
    $params = array();
    $params['__EVENTTARGET'] = 'ctl00$cphmain$Checkdetail';
    $params['__EVENTARGUMENT'] = '';
    $params['__LASTFOCUS'] = '';
    $params['__VIEWSTATE'] = $viewstate;
    $params['__EVENTVALIDATION'] = $eventvalidation;
    $params['ctl00$cphmain$Flyout2$Checkdatumy'] = 'on';  // must be sent - libver 17.5.2012
    $params['ctl00$cphmain$Checkdetail'] = 'on';
    $implodedParams = $this->implodeParams($params);
    curl_setopt($ch4, CURLOPT_POSTFIELDS, $implodedParams);
    curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch4, CURLOPT_HEADER, true);
    $html = curl_exec($ch4);
    curl_close($ch4);
    //echo htmlspecialchars($html);
    return $html;
  }

  private function parseGrades($html) {
    $grades = array(array());
    $i = 0;

    // TODO: parsing refactoring
    $gradeNumberPostion3 = null;

    $gradeStart = '<div class="detznb">'; // Bakalari libver 31.8.2012
    $gradeNumberPostion1 = strpos ($html, $gradeStart, $gradeNumberPostion3);
      if (!$gradeNumberPostion1) {
        $gradeStart = '<div class="detzn">'; // Bakalari libver 17.5.2012
        $gradeNumberPostion1 = strpos ($html, $gradeStart, $gradeNumberPostion3);
      }

    while (strpos ($html, $gradeStart, $gradeNumberPostion3) != false) {

      $gradeStart = '<div class="detznb">'; // Bakalari libver 31.8.2012
      $gradeNumberPostion1 = strpos ($html, $gradeStart, $gradeNumberPostion3);
      if (!$gradeNumberPostion1) {
        $gradeStart = '<div class="detzn">'; // Bakalari libver 17.5.2012
        $gradeNumberPostion1 = strpos ($html, $gradeStart, $gradeNumberPostion3);
      }
      $gradeNumberPostion2 = strpos ($html, '</div>', $gradeNumberPostion1);
      $grades[$i][0] = substr($html, $gradeNumberPostion1 + strlen($gradeStart), $gradeNumberPostion2 - $gradeNumberPostion1 - strlen($gradeStart));
      $descriptionStart = '<td class="detpozn2">'; // Bakalari libver 31.8.2012
      $gradeDescriptionPosition1 = strpos ($html, $descriptionStart, $gradeNumberPostion3);
      if (!$gradeDescriptionPosition1) {
        $descriptionStart = '<td class="detcaption">'; // Bakalari libver 17.5.2012
        $gradeDescriptionPosition1 = strpos ($html, $descriptionStart, $gradeNumberPostion3);
      }
      $gradeDescriptionPosition2 = strpos ($html, '</td>', $gradeDescriptionPosition1);
      $grades[$i][1] = htmlspecialchars(substr($html, $gradeDescriptionPosition1 + strlen($descriptionStart), $gradeDescriptionPosition2 - $gradeDescriptionPosition1 - strlen($descriptionStart)));

      $dateStart = '<td nowrap class="detdatum">';
      $gradeDatePosition1 = strpos ($html, $dateStart, $gradeNumberPostion3);
      $gradeDatePosition2 = strpos ($html, '</td>', $gradeDatePosition1);
      $grades[$i][2] = substr($html, $gradeDatePosition1 + strlen($dateStart), $gradeDatePosition2 - $gradeDatePosition1 - strlen($dateStart));

      $gradeNumberPostion3 = $gradeNumberPostion1 + 35;
      $i++;
     }
    return $grades;
  }

  private function orderBySubjects($grades) {
    $subjects = array();
    foreach ($grades as $grade) {
      $subject = $grade['subject'];
      $subjects[$subject] = isset($subjects[$subject]) ? $subjects[$subject] : array();
      $subjects[$subject][] = $grade;
    }
    return $subjects;
  }

  private function parseGradesDetails($source){
    $grades = array();
    $html = str_get_html($source);
    $lines = $html->find('.dettable tbody tr');

    foreach ($lines as $line) {
      $grade = array();

      $el_subject = $line->find('.detpredm', 0);
      $grade['subject'] = $el_subject->plaintext;

      $el_grade = $line->find('.detznb', 0);
      if (!$el_grade) {
        $el_grade = $line->find('.detznbnova', 0);
      }
      $grade['grade'] = $el_grade ? $el_grade->plaintext : '';

      $el_date = $line->find('.detdatum', 0);
      $grade['date'] = $el_date ? $el_date->plaintext : '';

      $el_description = $line->find('.detpozn2', 0);
      $grade['description'] = $el_description ? $el_description->plaintext : '';

      $grades[] = $grade;
    }

    return $this->orderBySubjects($grades);
  }

  public function getGradesDetails() {
    // Viewstate
    $viewstate = $this->fetchViewstate();

    // Login
    $this->login($viewstate);

    // Grades page
    // TODO: Does not return grades at first call, why?
    $html = $this->fetchGrades();
    $html = $this->fetchGrades();   // not need for libver 17.5.2012
    $viewstate = $this->parseViewstate($html);
    $eventvalidation = $this->parseEventValidation($html);

    // Subject page
    // TODO: Returns date only after browser page refresh, when cookie file is created, why?
        $html = $this->fetchDetails($viewstate, $eventvalidation);

    // Parse grades
    return $this->parseGradesDetails($html);
  }

  public function getGrades($subjectID) {
    // Viewstate
    $viewstate = $this->fetchViewstate();

    // Login
    $this->login($viewstate);

    // Grades page
    // TODO: Does not return grades at first call, why?
    $html = $this->fetchGrades();
    $html = $this->fetchGrades();   // not need for libver 17.5.2012
    $viewstate = $this->parseViewstate($html);
    $eventvalidation = $this->parseEventValidation($html);

    // Subject page
    // TODO: Returns date only after browser page refresh, when cookie file is created, why?
    $html = $this->fetchSubject($subjectID, $viewstate, $eventvalidation);

    // Parse grades
    $grades = $this->parseGrades($html);
    return $grades;
  }

}
