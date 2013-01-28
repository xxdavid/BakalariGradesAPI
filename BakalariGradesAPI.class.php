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
    
    
    
    function version() {
                return (float) '0.1';
        }

 
    function getViewstate($html){
      $viewstatePosition1 = strpos ($html, '<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="');
      $viewstatePosition2 = strpos ($html, '" />', $viewstatePosition1);    
      return(rawurlencode(substr($html, $viewstatePosition1+64, $viewstatePosition2 - $viewstatePosition1 - 64)));
    }
    
    function getEventValidation($html){
      $eventValidationPostion1 = strpos ($html, '<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="');
      $eventValidationPostion2 = strpos ($html, '" />', $eventValidationPostion1);      
      return(rawurlencode(substr($html, $eventValidationPostion1+76, $eventValidationPostion2 - $eventValidationPostion1 - 76)));
    }
  
  
    function __construct($username,$password,$host,$cookie) {
    $this->username = $username;
    $this->password = $password;
    $this->host = $host;
    $this->cookie = $cookie;
    
    }
    


    function getGrades($subjectID){
      // Getting Viewstate and Cookies
      $ch1 = curl_init();
      curl_setopt($ch1, CURLOPT_COOKIEJAR, $this->cookie);
      curl_setopt($ch1, CURLOPT_URL,$this->host."/login.aspx");
      curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
      $html = curl_exec ($ch1);
      curl_close($ch1);         
      $viewstate = $this->getViewstate($html);
      
      // Logging in 
      $ch2 = curl_init();
      curl_setopt($ch2, CURLOPT_COOKIEFILE, $this->cookie);
      curl_setopt($ch2, CURLOPT_URL,$this->host."/login.aspx");
      curl_setopt($ch2, CURLOPT_POST, 1);
      curl_setopt($ch2, CURLOPT_POSTFIELDS, '__LASTFOCUS=&__VIEWSTATE='.$viewstate.'&__EVENTTARGET=&__EVENTARGUMENT=&ctl00%24cphmain%24TextBoxjmeno='.$this->username.'&ctl00%24cphmain%24TextBoxheslo='.$this->password.'&ctl00%24cphmain%24ButtonPrihlas=P%C5%99ihl%C3%A1sit');
      curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
      curl_exec ($ch2);
      curl_close($ch2);
      
                     
      // Grades page
      $ch3 = curl_init();
      curl_setopt($ch3, CURLOPT_RETURNTRANSFER,1);
      curl_setopt($ch3, CURLOPT_COOKIEFILE, $this->cookie);
      curl_setopt($ch3, CURLOPT_URL,$this->host."/prehled.aspx?s=2");
      $html = curl_exec ($ch3);
      curl_close($ch3); 
      
      $viewstate = $this->getViewstate($html);     
      $eventvalidation = $this->getEventValidation($html);
     
  
      //Subject page
      $ch4 = curl_init();
      curl_setopt($ch4, CURLOPT_COOKIEFILE, $this->cookie);
      curl_setopt($ch4, CURLOPT_URL,$this->host."/prehled.aspx?s=2");
      curl_setopt($ch4, CURLOPT_POST, 1);
      curl_setopt($ch4, CURLOPT_POSTFIELDS, '__EVENTTARGET=ctl00$cphmain$'.$subjectID.'&__EVENTARGUMENT=&__LASTFOCUS=&__VIEWSTATE='.$viewstate.'&__EVENTVALIDATION='.$eventvalidation.'&ctl00%24cphmain%24listdoba=pololeti&ctl00%24cphmain%24Flyout2%24listrazeni2=podle+data&ctl00%24cphmain%24Flyout2%24Checkdatumy=on');
      curl_setopt($ch4, CURLOPT_RETURNTRANSFER, 1);
      $html = curl_exec ($ch4);
      curl_close($ch4); 
      
      
       
      
  
    $grades = array(array());
    $i = 0;
    
    while (strpos ($html, '<div class="detzn">', $gradeNumberPostion3) != false) {
    $gradeNumberPostion1 = strpos ($html, '<div class="detzn">', $gradeNumberPostion3);
    $gradeNumberPostion2 = strpos ($html, '</div>', $gradeNumberPostion1);
    $grades[$i][0] = substr($html, $gradeNumberPostion1+19, $gradeNumberPostion2 - $gradeNumberPostion1 - 20);  

    
    
    $gradeDescriptionPosition1 = strpos ($html, '<td class="detcaption">', $gradeNumberPostion3);
    $gradeDescriptionPosition2 = strpos ($html, '</td>', $gradeDescriptionPosition1);
    $grades[$i][1] = htmlspecialchars(substr($html, $gradeDescriptionPosition1 + 24, $gradeDescriptionPosition2 - $gradeDescriptionPosition1 - 24));  
      
    $gradeDatePosition1 = strpos ($html, '<td nowrap class="detdatum">', $gradeNumberPostion3);
    $gradeDatePosition2 = strpos ($html, '</td>', $gradeDatePosition1);
    $grades[$i][2] = substr($html, $gradeDatePosition1 + 28, $gradeDatePosition2 - $gradeDatePosition1 - 28);  
    
    
    
    $gradeNumberPostion3 = $gradeNumberPostion1 + 35;
    $i++;
     }
    return $grades; 
     
    
    }
  }   


?>
