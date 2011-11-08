<?php
	//ini_set('display_errors',1);
	//error_reporting(E_ALL|E_STRICT);
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$decoded = json_decode($_GET['json']);
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database);
	$updateStatement = "update tblVenue set location = geomFromText('polygon((";
	$first = true;
	foreach($decoded -> points as $p)
	{
		if($first)
		{
			$updateStatement = $updateStatement. $p -> lat . " " . $p -> lng ;
			$first = false;
		}
		else
		{
			$updateStatement = $updateStatement. "," . $p -> lat .  " " . $p -> lng ;
		}
	}
	$updateStatement = $updateStatement . "))'), description = '" . $decoded -> name . "' where tblVenue.venueId = " . $decoded -> id;
	mysql_query($updateStatement);
	mysql_close($connection);
?>