<?php
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
    curl_setopt($ch1, CURLOPT_COOKIEFILE, $this->cookie);
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
    curl_setopt($ch2, CURLOPT_COOKIEJAR, $this->cookie);
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
    curl_setopt($ch3, CURLOPT_COOKIEJAR, $this->cookie);
    curl_setopt($ch3, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($ch3, CURLOPT_URL,$this->host."/prehled.aspx?s=2");
    $html = curl_exec($ch3);
    curl_close($ch3);
    return $html;
  }

  private function fetchSubject($subjectID, $viewstate, $eventvalidation) {
    //Subject page
    $ch4 = curl_init();
    curl_setopt($ch4, CURLOPT_COOKIEJAR, $this->cookie);
    curl_setopt($ch4, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($ch4, CURLOPT_URL,$this->host."/prehled.aspx?s=2");
    curl_setopt($ch4, CURLOPT_POST, 1);
    $params = array();
    $params['__EVENTTARGET'] = 'ctl00$cphmain$' . 'roundprub$ ' . $subjectID;
    $params['__EVENTARGUMENT'] = '';
    $params['__LASTFOCUS'] = '';
    $params['__VIEWSTATE'] = $viewstate;
    $params['__EVENTVALIDATION'] = $eventvalidation;
    //$params['hlavnimenuSI'] = '2i0';
    //$params['ctl00$cphmain$listdoba'] = 'pololeti';
    //$params['ctl00$cphmain$Flyout2$listrazeni2'] = '0';
    //$params['ctl00$cphmain$Flyout2$Checktypy'] = 'on';
    //$params['ctl00$cphmain$Flyout2$Checkdatumy'] = 'on';
    $implodedParams = $this->implodeParams($params);

    curl_setopt($ch4, CURLOPT_POSTFIELDS, $implodedParams);
    curl_setopt($ch4, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($ch4, CURLOPT_HEADER, true);
    $html = curl_exec($ch4);
    curl_close($ch4);
    return $html;
  }

  public function getGrades($subjectID) {
    $viewstate = $this->fetchViewstate();
    $this->login($viewstate);

    // Grades page
    $gradesHtml = $this->fetchGrades();
    $viewstate = $this->parseViewstate($gradesHtml);
    $eventvalidation = $this->parseEventValidation($gradesHtml);

    $html = $this->fetchSubject($subjectID, $viewstate, $eventvalidation);

    $grades = array(array());
    $i = 0;

    // TODO: parsing refactoring
    $gradeNumberPostion3 = null;
    while (strpos ($html, '<div class="detznb">', $gradeNumberPostion3) != false) {
      $gradeNumberPostion1 = strpos ($html, '<div class="detznb">', $gradeNumberPostion3);
      $gradeNumberPostion2 = strpos ($html, '</div>', $gradeNumberPostion1);
      $grades[$i][0] = substr($html, $gradeNumberPostion1+20, $gradeNumberPostion2 - $gradeNumberPostion1 - 21);

      $gradeDescriptionPosition1 = strpos ($html, '<td class="detpozn2">', $gradeNumberPostion3);
      $gradeDescriptionPosition2 = strpos ($html, '</td>', $gradeDescriptionPosition1);
      $grades[$i][1] = htmlspecialchars(substr($html, $gradeDescriptionPosition1 + 22, $gradeDescriptionPosition2 - $gradeDescriptionPosition1 - 23));

      $gradeDatePosition1 = strpos ($html, '<td nowrap class="detdatum">', $gradeNumberPostion3);
      $gradeDatePosition2 = strpos ($html, '</td>', $gradeDatePosition1);
      $grades[$i][2] = substr($html, $gradeDatePosition1 + 28, $gradeDatePosition2 - $gradeDatePosition1 - 28);

      $gradeNumberPostion3 = $gradeNumberPostion1 + 35;
      $i++;
     }
    return $grades;
  }

}
