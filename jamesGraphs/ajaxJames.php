<?php
    include '/var/www/dataStore/dataAccess/db_connect.php';

	$connection = mysql_connect($hostname, $username, $password) OR 
die('MYSQL Fail: ' . mysql_error());
    mysql_select_db($database);
	$results = mysql_query("select UNIX_TIMESTAMP(timeStamp)*1000 as 
utime, tblLoad.load from tblLoad where hubId=14 and sensorId=0 order by timeStamp desc 
limit 100;");
	$dataset1 = array();
	
	while ($row = mysql_fetch_assoc($results)) 
	{
			$dataset1[] = array($row['utime'],$row['load']);	
    }
	mysql_close($connection);
 	echo preg_replace('/"(-?\d+\.?\d*)"/', '$1', json_encode($dataset1));

	
?>
