<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database,$connection);
	$uid = $_GET['uid'];
	$returnJson = array();
	$checkinCountStatement = "select * from tblCheckin where userId = $uid and tblCheckin.inout = 1";
	$resultSet = mysql_query($checkinCountStatement);
	if(mysql_num_rows($resultSet) == 0)
	{
		$returnJson['status'] = 0;
	}
	else
	{
		$returnJson['status'] = 1;
		$returnJson['count'] = mysql_num_rows($resultSet);
	}
	mysql_close($connection);
	print preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($returnJson));
?>