<?php

include 'abrirdb.php';
//elige de la vista de puntajes
$query = "SELECT * FROM nicks_stats ORDER BY score DESC, won DESC, lost ASC, name ASC";

$result = mysql_query($query);
$numRows = 0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	$res[$numRows++] = $row;
}

header("Content-type: text/plain");
echo json_encode($res);


?>