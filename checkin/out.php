<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database,$connection);
	
	$venueId =  $_GET['vid'];
	$userId = $_GET['uid'];
	
	$checkout_statement = "insert into tblCheckin values ($userId,$venueId,0,current_timestamp)";
	mysql_query($checkout_statement);
	mysql_close($connection);

?>