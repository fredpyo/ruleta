<?php

$players[0]['name'] = 'Player 1';
$players[0]['weight'] = 10;
$players[1]['name'] = 'Player 2';
$players[1]['weight'] = 20;
$players[2]['name'] = 'Player 3';
$players[2]['weight'] = 30;
$players[3]['name'] = 'Player 4';
$players[3]['weight'] = 100;

$balance = 100;

$numTeams = 2;

if ($numTeams == 2)
	$posibilidades = getOrderedPosibilities2Teams($players);
	
imprimirEquiposCompletos($posibilidades);

//de acuerdo al balance elegido, el índice máximo para el sorteo
$maxDeltaDiff = $posibilidades[0][count($posibilidades[0]) - 1]['diff'] - $posibilidades[0][0]['diff'];
$maxIndex = (int)($balance / 100 * (count($posibilidades[0]) - 1));

mt_srand();
$resultIndex = mt_rand(0,$maxIndex);
 
echo "ultimoDiff = ".$posibilidades[0][count($posibilidades[0]) - 1]['diff']."<br>";
echo "maxDeltaDiff = $maxDeltaDiff<br>";
echo "maxIndex = $maxIndex<br>";
echo "resultIndex = $resultIndex<br>";
 
 
function getOrderedPosibilities2Teams($elems) {
	$numElems = count($elems);
	//crea todas las combinaciones posibles
	$posibilidades[0] = array();
	$hasta = (int) ($numElems / 2);
	 for ($i = 1; $i <= $hasta; $i++) {
		$posibilidades[0] = addToVec($posibilidades[0], permutacion($elems, $i));
	 }
	//calcula el puntaje de ambos equipos generados y su diferencia
	$countPosibilidades = count($posibilidades[0]);
	for ($i = 0; $i < $countPosibilidades; $i++) {
		$posibilidades[0][$i]['weight'] = calculateTeamScore($posibilidades[0][$i]);
		$posibilidades[1][$i] = getPlayersLeft($elems, $posibilidades[0][$i]);
		$posibilidades[1][$i]['weight'] = calculateTeamScore($posibilidades[1][$i]);
		//la diferencia
		$posibilidades[0][$i]['diff'] = abs($posibilidades[0][$i]['weight'] - $posibilidades[1][$i]['weight']);
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

function imprimirEquiposCompletos($equipos) {
	for ($i = 0; $i < count($equipos[0]); $i++) {
		echo "[$i]";
		imprimirPermutacion($equipos[0][$i], true, true);
		echo " <b>VS</b> ";
		imprimirPermutacion($equipos[1][$i], true, false);
		echo "<br>";
	}
}

function imprimirPermutaciones($permutaciones, $conPuntaje) {
	for ($i = 0; $i < count($permutaciones); $i++) {
		echo "<ol>";
		imprimirPermutacion($permutaciones[$i], $conPuntaje);
		echo "</ol>";
	}
}

function imprimirPermutacion($permutacion, $conPuntaje, $conDiff) {
	$hasta = count($permutacion);
	if ($conPuntaje)
		$hasta--;
	if ($conDiff)
		$hasta--;
	
	for ($i = 0; $i < $hasta; $i++) {
		$aux[$i] = $permutacion[$i];
	}
	imprimirJugadores($aux);
	if ($conPuntaje)
		echo " |Total: ".$permutacion['weight']."|";
	if ($conDiff)
		echo " |<b>Dif: ".$permutacion['diff']."|</b>";
}

function imprimirJugadores($listaJugadores) {
	//echo '<b>Lista de jugadores:</b><br>';
	for ($i = 0; $i < count($listaJugadores); $i++) {
		if ($i > 0)
			echo ", ";
		//echo "Jugador[$i]:";
		imprimirJugador($listaJugadores[$i]);
		//echo "<br>";
	}
}

function imprimirJugador($jugador) {
	echo $jugador['name']." --&gt; ".$jugador['weight'];
}

?>