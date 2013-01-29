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
$bakalariSubjectID = 'IDPredmetu'; //ID předmětu lze zjistit například ve FireBugu po kliknutí na předmět v kolonce pod POST jako parametr celý EVENTTARGET; např. ctl00$cphmain$02 či ctl00$cphmain$roundprub$02; záleží na verzi Bakalářů; POZOR: musí být použity apostrofy

require("BakalariGradesAPI.class.php");
$znamky = new BakalariGradesAPI($bakalariUsername,$bakalariPassword,$bakalariHost,$bakalariCookie);
$poleZnamek = $znamky->getGrades($bakalariSubjectID);
echo "<table border=1>";
echo "<tr><th>Známka</th><th>Popis</th><th>Datum</th></tr>";
for ($i = 0; $i < count($poleZnamek); $i++){
echo ("<tr><td>".$poleZnamek[$i][0]."</td><td>".$poleZnamek[$i][1]."</td><td>".$poleZnamek[$i][2]."</td></tr>");
}
echo "</table>";
?>

  </body>
</html>