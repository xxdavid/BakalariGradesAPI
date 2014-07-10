<?php

require('simple_html_dom.php');

class BakalariGradesAPI
{

    private $username;
    private $password;
    private $host;
    private $cookie;
    private $lbver;
    private $loginInputName;
    private $gradesUrl;

    public function __construct($username, $password, $host, $cookie)
    {
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;
        $this->cookie = $cookie;
    }

    public function version()
    {
        return (float) '0.1';
    }

    private function parseViewstate($dom)
    {
        $elements = $dom->find('#__VIEWSTATE'); //temporary variable because of PHP 5.3
        return $elements[0]->value;
    }

    private function parseEventValidation($dom)
    {
        $elements = $dom->find('#__EVENTVALIDATION');
        return $elements[0]->value;
    }

    private function parseLbver($html)
    {
        $dom = str_get_html($html);
        $class = $dom->find('.lbver');
        return $class[0]->plaintext;
    }

    private function parseLoginInputName($html)
    {
        $matches = array();
        $pattern = "/dxo\.uniqueID = '(ctl00" . '\$cphmain\$Txt' . ".+)';/";
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

    private function fetchLoginPage()
    {
        $ch1 = curl_init();
        curl_setopt($ch1, CURLOPT_COOKIEJAR, $this->cookie);
        curl_setopt($ch1, CURLOPT_URL, $this->host . "/login.aspx");
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch1, CURLOPT_HEADER, true);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch1);
        curl_close($ch1);
        return $html;
    }

    private function login($viewstate)
    {
        $ch2 = curl_init();
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
        $implodedParams = http_build_query($params);

        curl_setopt($ch2, CURLOPT_POSTFIELDS, $implodedParams);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HEADER, true);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        $loginHtml = curl_exec($ch2);
        $this->gradesUrl = $this->lbver === "2.9.2013" ? $this->parseGradesUrl($loginHtml) : 'prehled.aspx?s=2';
        curl_close($ch2);
    }

    private function fetchGrades()
    {
        $ch3 = curl_init();
        curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch3, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($ch3, CURLOPT_URL, $this->host . '/' . $this->gradesUrl);
        curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch3);
        curl_close($ch3);
        return $html;
    }

    private function fetchDetails($viewstate, $eventvalidation, $weightAvailable)
    {
        $ch4 = curl_init();
        curl_setopt($ch4, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($ch4, CURLOPT_URL, $this->host . '/' . $this->gradesUrl);
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
        $implodedParams = http_build_query($params);
        curl_setopt($ch4, CURLOPT_POSTFIELDS, $implodedParams);
        curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch4, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch4);
        curl_close($ch4);
        return $html;
    }

    private function orderBySubjects($grades)
    {
        $subjects = array();
        foreach ($grades as $grade) {
            $subject = $grade['subject'];
            $subjects[$subject] = isset($subjects[$subject]) ? $subjects[$subject] : array();
            $subjects[$subject][] = $grade;
        }
        return $subjects;
    }

    protected function parseGrades($source)
    {
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

    private function isWeightAvailable($source)
    {
        $html = str_get_html($source);
        $found = $html->find('#cphmain_Flyout2_Checktypy', 0);
        return $found != null;
    }

    public function getGrades()
    {
        $loginPageHtml = $this->fetchLoginPage();
        $LoginPageDom = str_get_html($loginPageHtml);
        $viewstate = $this->parseViewstate($LoginPageDom);
        $this->lbver = $this->parseLbver($loginPageHtml);
        if ($this->lbver === '2.9.2013') {
            $this->loginInputName = $this->parseLoginInputName($loginPageHtml);
        }

        $this->login($viewstate);

        // TODO: Does not return grades at first call, why?
        $html = $this->fetchGrades();
        if ($this->lbver === '31.8.2012') {
            $html = $this->fetchGrades();
        }

        $dom = str_get_html($html);
        $viewstate = $this->parseViewstate($dom);
        $eventvalidation = $this->parseEventValidation($dom);

        // Check whether weight is available
        $weightAvailable = $this->isWeightAvailable($html);

        $html = $this->fetchDetails($viewstate, $eventvalidation, $weightAvailable);

        return $this->parseGrades($html);
    }

    public function getGradesDetails()
    { //Backward compatibility alias
        return $this->getGrades();
    }

}
