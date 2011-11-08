<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database,$connection);
	$jsonIn = (json_decode($_GET['json'],True));
	$readingSet = $jsonIn['readingSet'];
	$user = $readingSet['user'];
	$apiKey = $readingSet['apiKey'];
	$login_statement = "select userId from tblUser where email='$user' and apiKey = '$apiKey'";
	$loginResult = mysql_query($login_statement);
	if(mysql_num_rows($loginResult) >= 1)
	{
		try
		{
			$timeStamp = $readingSet['timeStamp'];
			$hubId = $readingSet['hubId'];
			$numReadings = count($readingSet['readings']);
			print("number of readings is ".$numReadings);
			foreach($readingSet['readings'] as $reading)
			{
				$sensorId = $reading['sensorId'];
				$load = $reading['load'];
				$insertStatement = "insert into tblLoad values($hubId,$sensorId,current_timestamp,$load)";
				mysql_query($insertStatement);
				print("inserted reading for hub $hubId sensor $sensorId");
			}
			mysql_close($connection);
		}
		catch(exception $e)
		{
			mysql_close($connection);
		}
	}
	else
	{
		mysql_close($connection);
		print("user auth fail");
	}
?>


 
