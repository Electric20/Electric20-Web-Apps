<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
    include '/var/www/dataStore/dataAccess/db_connect.php';
	
	$decoded = json_decode($_GET['json']);
	
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database);
	$insertStatement = "insert into tblVenue values(" . $decoded -> id. ", geomFromText('polygon((";
	$first = true;
	foreach($decoded -> points as $p)
	{
		if($first)
		{
			$insertStatement = $insertStatement. $p -> lat . " " . $p -> lng ;
			$first = false;
		}
		else
		{
			$insertStatement = $insertStatement. "," . $p -> lat .  " " . $p -> lng ;
		}
	}
	$insertStatement = $insertStatement . "))'),'" . $decoded -> name . "')";
	mysql_query($insertStatement);
	mysql_close($connection);
	//die ($insertStatement);
?>