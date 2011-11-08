<?php
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database,$connection);
	$venueId =  $_POST['vid'];
	$userId = $_POST['uid'];
	$inout = $_POST['inout'];
	$checkin_statement = "insert into tblCheckin values ($userId,$venueId,$inout,current_timestamp)";
	mysql_query($checkin_statement);
	mysql_close($connection);

?>
