<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
	include '/var/www/dataStore/dataAccess/db_connect.php';
	$hubId = $_GET['hubId'];
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
    mysql_select_db($database,$connection);
	$returnJson = array();
	
	
	$distinctSensorStatement = "select sensorId, description from tblSensorDir where hubId = $hubId";
	$distinctSensorResultset = mysql_query($distinctSensorStatement);
	$returnJson['dataset'] = array();
	
	while($dataset = mysql_fetch_assoc($distinctSensorResultset))
	{
		$sensorId = $dataset['sensorId'];
		$description = $dataset['description'];
		$returnJson['dataset'][$description] = array();
		$returnJson['dataset'][$description]['label'] = $description;
		$dataStatement = "select UNIX_TIMESTAMP(timeStamp + interval 1 hour)*1000 as utime, tblLoad.load from tblLoad where hubId=$hubId and sensorId=$sensorId and timeStamp > (current_timestamp - interval 30 minute) order by timeStamp desc limit 300";
		$dataResults = mysql_query($dataStatement);
		$returnJson['dataset'][$description]['data'] = array();	
		while($datadata = mysql_fetch_assoc($dataResults))
		{
			array_push($returnJson['dataset'][$description]['data'], array($datadata['utime'],$datadata['load']));
		}
		
	}
	mysql_close($connection);
	echo preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($returnJson));
	
?>


