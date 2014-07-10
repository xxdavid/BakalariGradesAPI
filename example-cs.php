<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <title>BakalariGradesAPI - příklad použití</title>
  </head>
  <body>


<?php
$bakalariUsername = "VaseUzivatelskeJmeno"; //Uživatelské jméno na Bakalářích
$bakalariPassword = "VaseHeslo"; //Heslo na Bakalářích
$bakalariHost = "AdresaBakalaru"; //Základní adresa Bakalářů (bez konkretního souboru); např. http://www.zssirotkova.cz:81 nebo http://bakalari.gfpvm.cz/bakaweb nebo http://bakalari.gfxs.cz ; POZOR: adresa musí být bez lomítka na konci
$bakalariCookie = "cookies.txt"; //soubor s cookies

// Moje přihlašovací údaje pro účely testování
@require('config.php');

require("BakalariGradesAPI.class.php");
$znamky = new BakalariGradesAPI($bakalariUsername,$bakalariPassword,$bakalariHost,$bakalariCookie);
$subjects = $znamky->getGrades();

foreach ($subjects as $subject => $grades) {

    echo '<h2>'.$subject.'</h2>';
    echo "<table border=1>";
    echo "<tr><th>Známka</th><th>Popis</th><th>Datum</th></tr>";
    foreach ($grades as $grade) {
        echo ("<tr><td>".$grade['grade']."</td><td>".$grade['description']."</td><td>".$grade['date']."</td></tr>");
    }
    echo "</table>";

}
?>

  </body>
</html>