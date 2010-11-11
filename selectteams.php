<?php

$logFile = 'logFile';
$req = json_decode(stripslashes($_POST['json']), true);
//el número de equipos
$numTeams = $req['numTeams'];
//un array de cadenas donde cada $req[i] contiene el nombre de un jugador
$players = $req['players'];
$numPlayers = count($players);
/*
el % de aleatoriedad
un valor entre 0 y 100 para indicar qué tan random se tienen que armar los equipos
*/
$randomness = $req['randomness'];
/*
si se quiere equipos sin demasiada diferencia en cantidad de jugadores
true o false dependiendo de si se quiere evitar o permitir equipos de 3vs1, 5vs2, etcétera respectivamente
*/
$useMinPlayers = $req['useMinPlayers'];

$minPlayers = 1;
if ($useMinPlayers) {
	$minPlayers = (int)($numPlayers / $numTeams);
}


include 'abrirdb.php';
$query = "SELECT * FROM nicks_stats WHERE name=";

for ($i = 0; $i < count($players); $i++) {
	if ($i > 0)
		$query = $query." OR name=";
	$query = $query."'".$players[$i]."'";
}

//echo ($query);
//error_log("\nquery: ".$query, 3, $logFile);
$result = mysql_query($query);
if (mysql_num_rows($result) < 2) {
	$res['teams'] = array();
	header("Content-type: text/plain");
	die(json_encode($res));
}

$res = array();
$res['teams'] = array();
unset($players);
$players = array();
//carga el resultado en la lista de jugadores
$currResult = 0;
while ($row = mysql_fetch_array($result)) {
	//el nombre
	$players[$currResult]['name'] = $row['name'];
	//el peso
	if ($row['played'] != 0) {
		$players[$currResult]['weight'] = (int) ($row['won'] / $row['played'] * 100);
	} else {
		$players[$currResult]['weight'] = 0;
	}
	$currResult++;
}

//de acuerdo a la cantidad de equipos
if ($numTeams == 2)
	$posibilidades = getOrderedPosibilities2Teams($players, $minPlayers);

//de acuerdo al balance elegido, el índice máximo para el sorteo
$maxDeltaDiff = $posibilidades[0][count($posibilidades[0]) - 1]['diff'] - $posibilidades[0][0]['diff'];
$maxBalanceDelta = (int)($randomness / 100 * $maxDeltaDiff);
$minDiff = $posibilidades[0][0]['diff'];

$hasta = $minDiff + $maxBalanceDelta;
$maxIndex = 0;
for ($i = 0; $i < count($posibilidades[0]); $i++) {
	if ($posibilidades[0][$i]['diff'] > $hasta) {
		break;
	} else {
		$maxIndex = $i;
	}
}

//$maxIndex = (int)($balance / 100 * (count($posibilidades[0]) - 1));

//un número al azar de entre los que caen dentro del balanceo elegido
mt_srand();
$resultIndex = mt_rand(0,$maxIndex);
	


	
	
function getOrderedPosibilities2Teams($elems, $minPlayers) {
	$numElems = count($elems);
	//crea todas las combinaciones posibles
	$posibilidades[0] = array();
	$permutaciones = array();
	$hasta = (int) ($numElems / 2);
	 for ($i = $minPlayers; $i <= $hasta; $i++) {
		$permutaciones = addToVec($permutaciones, permutacion($elems, $i));
	 }
	//calcula el puntaje de ambos equipos generados y su diferencia
	$countPermutaciones = count($permutaciones);
	$numPosibilidades = 0;
	for ($i = 0; $i < $countPermutaciones; $i++) {
		if (count($permutaciones[$i]) < $minPlayers || count(getPlayersLeft($elems, $permutaciones[$i])) < $minPlayers)
			continue;
		$posibilidades[0][$numPosibilidades] = $permutaciones[$i];
		$posibilidades[1][$numPosibilidades] = getPlayersLeft($elems, $permutaciones[$i]);
		$posibilidades[0][$numPosibilidades]['weight'] = calculateTeamScore($permutaciones[$i]);
		$posibilidades[1][$numPosibilidades]['weight'] = calculateTeamScore($posibilidades[1][$i]);
		//la diferencia
		$posibilidades[0][$numPosibilidades]['diff'] = abs($posibilidades[0][$numPosibilidades]['weight'] - $posibilidades[1][$numPosibilidades]['weight']);
		$numPosibilidades++;
	}

	//ordena los equipos de acuerdo a la diferencia entre puntajes (bubblesort). De menor a mayor.
	$posibilidades = bubblesort($posibilidades);
	
	return $posibilidades;
}

//ordena de menor a mayor diferencia de puntos entre los equipos
function bubblesort($posibilidades) {
	do { 
		$swapped = false;
		$hasta = count($posibilidades[0]) - 1;
		for ($i = 0; $i < $hasta; $i++) {
			if (abs($posibilidades[0][$i]['weight'] - $posibilidades[1][$i]['weight']) > abs($posibilidades[0][$i + 1]['weight'] - $posibilidades[1][$i + 1]['weight'])) {
				$aux = $posibilidades[0][$i];
				$posibilidades[0][$i] = $posibilidades[0][$i + 1];
				$posibilidades[0][$i + 1] = $aux;
				
				$aux = $posibilidades[1][$i];
				$posibilidades[1][$i] = $posibilidades[1][$i + 1];
				$posibilidades[1][$i + 1] = $aux;
				$swapped = true;
			}
		}
	} while ($swapped);
	return $posibilidades;
}

//calcula el puntaje de un equipo
function calculateTeamScore($team) {
	$score = 0;
	for ($i = 0; $i < count($team); $i++) {
		$score += $team[$i]['weight'];
	}
	return $score;
}

function addToVec($vec, $add) {
	$countVec = count($vec);
	$countAdd = count($add);
	for ($i = $countVec; $i < $countVec + $countAdd; $i++)
		$vec[$i] = $add[$i - $countVec];
		
	return $vec;
}

//recibe el vector de jugadores y los jugadores usados y devuelve un vector con los jugadores restantes
function getPlayersLeft($players, $playersUsed) {
	for($i = 0; $i < count($players); $i++) {
		$used = false;
		for ($j = 0; $j < count($playersUsed); $j++) {
			if ($players[$i]['name'] == $playersUsed[$j]['name'])
				$used = true;
		}
		if (!$used)
			$playersLeft[count($playersLeft)] = $players[$i];
	}
	
	return $playersLeft;
}

//recibe un vector y los permuta tomados $deACuanto
function permutacion($elementos, $deACuanto) {
	//inicializa el resultado
	$permutaciones = array();
	//la cantidad de permutaciones
	$numPermutaciones = 0;
	//cantidad de elementos
	$numElems = count($elementos);
	//el cursor (determina el elemento dentro de la permutación y va de 0 a $deACuanto)
	$cursor = 0;
	//el vector de posiciones de cada elemento. Tiene $deACuanto lugares
	$pos = array();
	//inicializa el vector de posiciones
	for ($i = 0; $i < $deACuanto; $i++) {
		$pos[$i] = $i;
	}
	//si se puede agregar una permutación nueva
	$sePuede = true;
	$saltear = false;
	
	//mientras se pueda, se agregan permutaciones
	while($sePuede) {
		for ($i = 0; $i < $deACuanto; $i++) {
			if ($pos[$i] > $numElems - ($deACuanto - $i)) {
				//echo "i>$i / $pos[$i]";
				if ($i == 0) {
					$sePuede = false;
					unset($permutaciones[$numPermutaciones]);
				} else {
					$pos[$i - 1] += 1;
					for ($j = $i; $j < $deACuanto; $j++)
						$pos[$j] = $pos[$j - 1] + 1;
					$saltear = true;
				}
				break;
			}
			$permutaciones[$numPermutaciones][$i] = $elementos[$pos[$i]];
		}
		if (!$saltear) {
			$pos[$deACuanto-1] += 1;
			$numPermutaciones++;
		} else {
			$saltear = false;
		}
	 }
	
	return $permutaciones;
}

//todos los índices son posibles
$posIndexes = range(0,$numTeams - 1);
//mezcla los índices
shuffle($posIndexes);
for ($i = 0; $i < $numTeams; $i++) {
	$hasta = count($posibilidades[$i][$resultIndex]) - 1; //descarta el ['weight']
	if ($i == 0)
		$hasta--; //descarta el ['diff']
	for ($j = 0; $j < $hasta; $j++)
		$aux['teams'][$posIndexes[$i]][$j] = $posibilidades[$i][$resultIndex][$j];
		//$res['teams'][(int)$posIndexes[$i]][$j] = $posibilidades[$i][$resultIndex][$j];
	$aux['weight'][$posIndexes[$i]] = $posibilidades[$i][$resultIndex]['weight'];
	//$res['weight'][(int)$posIndexes[$i]] = $posibilidades[$i][$resultIndex]['weight'];
	if ($i == 0)
		$aux['diff'][$i] = $posibilidades[$i][$resultIndex]['diff']; //la diferencia máxima de puntajes entre cualquiera de los equipos existentes
		// $res['diff'][(int)$posIndexes[$i]] = $posibilidades[$i][$resultIndex]['diff']; //la diferencia máxima de puntajes entre cualquiera de los equipos existentes
}


//creación del resultado
$res['diff'][0] = $aux['diff'][0];
$res['maxBalanceDelta'] = $maxBalanceDelta;
for ($i = 0; $i < $numTeams; $i++) {
	$res['teams'][$i] = $aux['teams'][$i];
	$res['weight'][$i] = $aux['weight'][$i];
}

/*
$res es un objeto donde:
$res['teams'] contiene los equipos con los jugadores y sus puntajes además del puntajes de cada equipo.
Ej.:
$res['teams'][0][0]['name'] = 'Dread';
$res['teams'][0][0]['weight'] = 121;
$res['teams'][0][1]['name'] = 'BeMySluts';
$res['teams'][0][1]['weight'] = 33;
$res['teams'][1][0]['name'] = 'TylerDurden';
$res['teams'][1][0]['weight'] = 61;
$res['teams'][1][1]['name'] = 'Judge';
$res['teams'][1][1]['weight'] = 60;

$res['weight'] es un array de enteros donde cada elemento es el puntaje total de cada equipo (actualmente, la suma del puntaje de cada jugador)
$res['weight'][0] = 121; //Dread: 88 + BeMySluts: 33
$res['weight'][1] = 121; //TylerDurden: 61 + Judge: 60
*/

header("Content-type: text/plain");
echo json_encode($res);


?>