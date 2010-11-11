<?php

//$logFile = 'logFile';
/*
$req['teams'] es un array donde cada 
$req['teams'][i] es un array de cadenas donde cada
$req['teams'][i][j] es una cadena con el nombre del j-ésimo jugador del i-ésimo equipo

$req['winners'] es un entero con el número del equipo ganador

$req['pass'] es una cadena con el password crudo y puro (sin md5 ni nada) que hay que comparar con
el de la db para que no cualquiera pueda actualizar el puntaje de los jugadores
*/
$req = json_decode(stripslashes($_POST['json']), true);

//mira si el password está bien
include 'abrirdb.php';

$query = "SELECT pass FROM globals";
$result = mysql_query($query);

$row = mysql_fetch_array($result);

$pass = $req['pass'];

if ($pass == $row['pass']) {
	//crea el nuevo juego
	$val = date("Y-m-d H:i:s");
	$query = "INSERT INTO games (`timestamp`) VALUES ('$val')";
	$result = mysql_query($query);

	
	//el id del juego recién creado
	$query = "SELECT idgames FROM games ORDER BY `timestamp` DESC LIMIT 1";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	$idGames = $row['idgames'];
	$res['idGames'] = $idGames;
	
	
	//inserta cada uno de los jugadores en el último partido jugado
	$teams = $req['teams'];
	$winnersTeam = $req['winners'];
	for ($i = 0; $i < count($teams); $i++) {
		for ($j = 0; $j < count($teams[$i]); $j++) {
			$nick = $teams[$i][$j];
			if ($i == $winnersTeam)
				$victory = 'Y';
			else
				$victory = 'N';
			$query = "INSERT INTO matches (game, nick, team, victory) VALUES ($idGames, '$nick', $i, '$victory')";
			mysql_query($query);
		}
	}
	
	$res['result'] = "Puntaje de los jugadores actualizado correctamente";
	
} else {
	$res['result'] = "Password incorrecto";
}

/*
$res['result'] contiene una cadena que se muestra en un alert del lado del servidor
*/

header("Content-type: text/plain");
echo json_encode($res);

?>