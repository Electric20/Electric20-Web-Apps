<?php
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$returnJson = array();
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
    mysql_select_db($database,$connection);
	$returnJson = array();
	$selectDistinctStatement = "select distinct(hubId) as hubId from tblLoad";
	$results = mysql_query($selectDistinctStatement);
	
	while ($row = mysql_fetch_assoc($results)) 
	{
			array_push($returnJson, $row['hubId']);	
    }
	
 	echo preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($returnJson))

	
?>
