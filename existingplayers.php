<?php
/*
Archivo utilitario que sirve para el widget de autocompletado de nicks
*/
include 'abrirdb.php';
//entresaca todos los nicks de la base de datos
$query = "SELECT name FROM nicks";
$result = mysql_query($query);
$numNicks = 0;
while ($row = mysql_fetch_array($result)) {
	$res[$numNicks++] = $row['name'];
}
 
 
header("Content-type: text/plain");
echo json_encode($res);


?>