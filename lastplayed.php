<?php

include 'abrirdb.php';
//elige los nicks que estuvieron en la última partida que se jugó
$query = "SELECT m.nick AS nick
FROM matches AS m
JOIN games AS g ON m.game=g.idgames
WHERE `timestamp`=(SELECT `timestamp` FROM games ORDER BY `timestamp` DESC LIMIT 1)";

$result = mysql_query($query);
$numNicks = 0;
while ($row = mysql_fetch_array($result)) {
	$res['nicks'][$numNicks++] = $row['nick'];
}

header("Content-type: text/plain");
echo json_encode($res);


?>