<?php

require_once('BakalariGradesAPI.class.php');

class BakalariGradesAPIMock extends BakalariGradesAPI
{

    private $html;

    public function __construct($html)
    {
        $this->html = $html;
    }

    public function getGrades()
    {
        return $this->parseGrades($this->html);
    }

}
