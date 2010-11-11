<?php

//parsea la petición
$req = json_decode(stripslashes($_POST['json']), true);

/*
$req['newPlayers'] es un array de cadenas donde cada
$req['newPlayers'][i] es el nombre de un jugador
*/
$newPlayers = $req['newPlayers'];

include 'abrirdb.php';
//inserta los jugadores en la bd
$query = "INSERT INTO nicks (name) VALUES ('";
for ($i = 0; $i < count($newPlayers); $i++) {
	mysql_query($query.$newPlayers[$i]."')");
}

?>