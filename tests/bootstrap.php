<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../BakalariGradesAPI.class.php';
require __DIR__ . '/../BakalariGradesAPIMock.class.php';

use Tracy\Debugger;
Tester\Environment::setup();

date_default_timezone_set('Europe/Prague');

header("Content-Type: text/html; charset=UTF-8");

header("Content-Type: text/html; charset=UTF-8");
if (php_sapi_name() != 'cgi-fcgi') {
    Debugger::enable();
    Debugger::$strictMode = true;
    Debugger::$showLocation = true;
}