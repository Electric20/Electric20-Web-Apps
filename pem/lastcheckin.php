<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database,$connection);
	$uid = $_GET['uid'];
	$returnJson = array();
	$lastCheckinStatement = "select tblVenue.description, timeStamp from tblVenue, tblCheckin where userId = $uid and tblCheckin.inout = 1 and tblVenue.venueId = tblCheckin.venueId order by tblCheckin.timeStamp desc limit 1";
	$resultSet = mysql_query($lastCheckinStatement);
	if(mysql_num_rows($resultSet) == 0)
	{
		$returnJson['status'] = 0;
	}
	else
	{
		while($dataSet = mysql_fetch_assoc($resultSet))
		{
			date_default_timezone_set('UTC');
			$returnJson['status'] = 1;
			$returnJson['time'] = date("g:i a F j, Y ", strtotime($dataSet['timeStamp']));
			$returnJson['location'] = $dataSet['description'];
		}
	}
	mysql_close($connection);
	print preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($returnJson));
?>


