<!DOCTYPE html>
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>BakalariGradesAPI – example of usage</title>
  </head>
  <body>


<?php
date_default_timezone_set('Europe/Prague');

//Bakalari credentials and other setting
require('example.config.php');

require("BakalariGradesAPI.class.php");
$bakalari = new BakalariGradesAPI($username, $password, $host, $cookieFile);
$subjects = $bakalari->getGrades();

foreach ($subjects as $subject => $grades): ?>
    <h2><?php echo $subject ?></h2>
    <table border=1>
    <tr>
        <th>Grade</th>
        <th>Description</th>
        <th>Date</th>
    </tr>
    <?php foreach ($grades as $grade): ?>
    <tr>
        <td><?php echo $grade['grade'] ?></td>
        <td><?php echo $grade['description'] ?></td>
        <td><?php echo $grade['date'] ?></td>
    </tr>
    <?php endforeach; ?>
    </table>
<?php endforeach; ?>

  </body>
</html>