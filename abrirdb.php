<?php
	require_once('configdb.php');
	
	//se conecta al servidor
	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die (mysql_error());

	if(!$link) {
		die('No se pudo conectar al servidor: ' . mysql_error());
	}
	
	//elige la db
	$db = mysql_select_db(DB_DATABASE);
	if(!$db) {
		die("No se pudo elegir la db");
	}

?>