<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="generator" content="PSPad editor, www.pspad.com">
  <title>BakalariGradesAPI - example usage</title>
  </head>
  <body>
  
  
<?
$bakalariUsername = "YourUsername"; 
$bakalariPassword = "YourPassword"; 
$bakalariHost = "Host"; 
$bakalariCookie = "cookies.txt"; 
$bakalariSubjectID = "SubjectId";

require("BakalariGradesAPI.class.php");
$znamky = new BakalariGradesAPI($bakalariUsername,$bakalariPassword,$bakalariHost,$bakalariCookie);
$gradesArray = $znamky->getGrades($bakalariSubjectID);
echo "<table border=1>";
echo "<tr><th>Grade</th><th>Description</th><th>Date</th></tr>";
for ($i = 0; $i < count($gradesArray); $i++){
echo ("<tr><td>".$gradesArray[$i][0]."</td><td>".$gradesArray[$i][1]."</td><td>".$gradesArray[$i][2]."</td></tr>");
}
echo "</table>";
?>

  </body>
</html>