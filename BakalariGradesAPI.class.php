<?php
require('simple_html_dom.php');

class BakalariGradesAPI {

  private $username;
  private $password;
  private $host;
  private $cookie;
  private $lbver;
  private $loginInputName;
  private $gradesUrl;

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

  private function parseLbver($html){
    $dom = str_get_html($html);
    $class = $dom->find('.lbver'); //temporary variable because of PHP 5.3
    return $class[0]->plaintext;
  }
  
  private function parseLoginInputName($html)
  {
    $matches = array(); 
    $pattern = "/dxo\.uniqueID = '(ctl00".'\$cphmain\$Txt'.".+)';/";
    preg_match($pattern, $html, $matches);
    return $matches[1];
  }
  
  private function parseGradesUrl($html)
  {
    $matches = array(); 
    $pattern = '/<a.* href="(prehled.aspx\?s=\d+)".*>(?:<.*>)*Průběžn&#225; klasifikace(?:<\/.*>)*<\/a>/';
    preg_match($pattern, $html, $matches);
    return $matches[1];
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

  private function fetchLoginPage() {
    // Getting Viewstate and Cookies
    $ch1 = curl_init();
    curl_setopt($ch1, CURLOPT_COOKIEJAR, $this->cookie);
    //curl_setopt($ch1, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($ch1, CURLOPT_URL,$this->host."/login.aspx");
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_HEADER, true);
    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec ($ch1);
    curl_close($ch1);
    return $html;
  }

  private function login($viewstate) {
    // Logging in
    $ch2 = curl_init();
    //curl_setopt($ch2, CURLOPT_COOKIEJAR, $this->cookie);
    curl_setopt($ch2, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($ch2, CURLOPT_URL, $this->host . "/login.aspx");
    curl_setopt($ch2, CURLOPT_POST, 1);
    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);

    $params = array();
    $params['__LASTFOCUS'] = '';
    $params['__EVENTTARGET'] = '';
    $params['__EVENTARGUMENT'] = '';
    $params['__VIEWSTATE'] = $viewstate;
    $this->lbver === '2.9.2013' ? $params[$this->loginInputName] = $this->username : $params['ctl00$cphmain$TextBoxjmeno'] = $this->username;
    $params['ctl00$cphmain$TextBoxheslo'] = $this->password;
    $params['ctl00$cphmain$ButtonPrihlas'] = '';
    $implodedParams = $this->implodeParams($params);

    curl_setopt($ch2, CURLOPT_POSTFIELDS, $implodedParams);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_HEADER, true);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    $loginHtml = curl_exec($ch2);
    $this->lbver === "2.9.2013" ? $this->gradesUrl = $this->parseGradesUrl($loginHtml) : $this->gradesUrl = 'prehled.aspx?s=2';
    curl_close($ch2);
  }

  private function fetchGrades() {
    $ch3 = curl_init();
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER,1);
    //curl_setopt($ch3, CURLOPT_COOKIEJAR, $this->cookie);
    curl_setopt($ch3, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($ch3, CURLOPT_URL, $this->host.'/' . $this->gradesUrl);
    curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch3);
    curl_close($ch3);
    return $html;
  }

  private function fetchDetails($viewstate, $eventvalidation, $weightAvailable) {
    //Subject page
    $ch4 = curl_init();
    curl_setopt($ch4, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($ch4, CURLOPT_URL,$this->host. '/'. $this->gradesUrl);
    curl_setopt($ch4, CURLOPT_POST, 1);
    $params = array();
    $params['__EVENTTARGET'] = 'ctl00$cphmain$Checkdetail';
    $params['__EVENTARGUMENT'] = '';
    $params['__LASTFOCUS'] = '';
    $params['__VIEWSTATE'] = $viewstate;
    $params['__EVENTVALIDATION'] = $eventvalidation;
    if ($weightAvailable) {
      $params['ctl00$cphmain$Flyout2$Checktypy'] = 'on';
    }
    $params['ctl00$cphmain$Flyout2$Checkdatumy'] = 'on';  // must be sent - lbver 17.5.2012
    $params['ctl00$cphmain$Checkdetail'] = 'on';
    $implodedParams = $this->implodeParams($params);
    curl_setopt($ch4, CURLOPT_POSTFIELDS, $implodedParams);
    curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch4, CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($ch4, CURLOPT_HEADER, true);
    $html = curl_exec($ch4);
    curl_close($ch4);
    return $html;
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

  protected function parseGrades($source){
    $grades = array();
    $html = str_get_html($source);
    if (!$html) {
      return;
    }

    $lines = $html->find('.dettable tbody tr');

    foreach ($lines as $line) {
      $grade = array();

      $el_subject = $line->find('.detpredm', 0);
      $grade['subject'] = $el_subject->plaintext;
      if (!$grade['subject']) {
        $last_grade = end($grades);
        $grade['subject'] = $last_grade['subject'];
      }

      $el_grade = $line->find('.detznb', 0);
      $el_grade = $el_grade ? $el_grade : $line->find('.detznbnova', 0);
      $el_grade = $el_grade ? $el_grade : $line->find('.detzn', 0);
      $grade['grade'] = $el_grade && $el_grade->plaintext ? trim($el_grade->plaintext) : null;
      // Remove suspicious date exclamation mark
      $grade['grade'] = str_replace('!', '', $grade['grade']);
      // Do not return 'absent' grades
      if ($grade['grade'] == 'A') {
        continue;
      }

      $el_weight = $line->find('.detvaha', 0);
      $grade['weight'] = $el_weight && $el_weight->plaintext ? $el_weight->plaintext : null;
      if ($grade['weight']) {
        preg_match('/([0-9]+)/', $grade['weight'], $weight_parts);
        $grade['weight'] = reset($weight_parts);
      }

      $el_date = $line->find('.detdatum', 0);
      $grade['date'] = $el_date && $el_date->plaintext ? $el_date->plaintext : null;
      if ($grade['date']) {
        $date_parts = explode('.', $grade['date']);
        $date = '20' . $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
        $grade['date'] = date('Y-m-d', strtotime($date));
      }

      $el_description = $line->find('.detcaption', 0);
      $el_description = $el_description ? $el_description : $line->find('.detpozn2', 0);
      $grade['description'] = $el_description ? $el_description->plaintext : '';
      // Remove brackets
      $grade['description'] = preg_replace('/\((.+)\)/', '$1', $grade['description']);
      $grade['description'] = strip_tags($grade['description']);
      $grade['description'] = str_replace('... Podezřelé datum!', '', $grade['description']);
      $grade['description'] = trim($grade['description']);
      $grade['description'] = $grade['description'] ? $grade['description'] : null;

      $grades[] = $grade;
    }

    return $this->orderBySubjects($grades);
  }

  private function isWeightAvailable($source) {
    $html = str_get_html($source);
    $found = $html->find('#cphmain_Flyout2_Checktypy', 0);
    return $found != null;
  }

  public function getGrades() {
    // Viewstate
    $loginPageHtml = $this->fetchLoginPage();
    $viewstate = $this->parseViewstate($loginPageHtml);
    $this->lbver = $this->parseLbver($loginPageHtml);
    if ($this->lbver === '2.9.2013'){
        $this->loginInputName = $this->parseLoginInputName($loginPageHtml);
    }
    
    // Login
    $this->login($viewstate);

    // Grades page
    // TODO: Does not return grades at first call, why?
    $html = $this->fetchGrades();    
    if ($this->lbver === '31.8.2012'){
        $html = $this->fetchGrades();
    }
    
    $viewstate = $this->parseViewstate($html);
    $eventvalidation = $this->parseEventValidation($html);

    // Check whether weight is available
    $weightAvailable = $this->isWeightAvailable($html);

    // Details
    $html = $this->fetchDetails($viewstate, $eventvalidation, $weightAvailable);

    // Parse grades
    return $this->parseGrades($html);
  }
  
    public function getGradesDetails() { //Backward compatibility alias
        return $this->getGrades();
    }

}
