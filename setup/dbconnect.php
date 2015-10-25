<?php

	ini_set('max_execution_time', 30000); 
	ini_set('memory_limit', '912M');

	$nrec = 1;
	$host = 'localhost';
	$username = 'root';
	$password = '';
	$db = 'kws_'.$nrec.'k';

	$connection = mysqli_connect($host, $username, $password, $db);
	if (!$connection) {
	    die("Connection failed: " . mysqli_connect_error());
	}
?>
