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

require("BakalariGradesAPI.class.php");
$znamky = new BakalariGradesAPI($bakalariUsername,$bakalariPassword,$bakalariHost,$bakalariCookie);
$poleZnamek = $znamky->getGradesDetails();

echo ("Tak třeba známky z Češtiny");
echo "<table border=1>";
echo "<tr><th>Známka</th><th>Popis</th><th>Datum</th></tr>";
for ($i = 0; $i < count($poleZnamek['Český jazyk']); $i++){
echo ("<tr><td>".$poleZnamek['Český jazyk'][$i]['grade']."</td><td>".$poleZnamek['Český jazyk'][$i]['description']."</td><td>".$poleZnamek['Český jazyk'][$i]['date']."</td></tr>");
}
echo "</table>";
?>

  </body>
</html>