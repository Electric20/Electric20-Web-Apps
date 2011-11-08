<?php
    include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
    mysql_select_db($database);

	$hubId = $_GET['hubId'];

	if ($hubId == "undefined") {
		
	} else {
		$results = mysql_query("SELECT MAX(tblLoadAgg.loadAvg) as max FROM EnergyDataStoreV2.tblLoadAgg WHERE tblLoadAgg.hubId = ".$hubId);
		while ($row = mysql_fetch_assoc($results)) {
			$data["max"] = (int)$row['max'];
	    }
	
		$results = mysql_query("SELECT MAX(tblLoad.load) as max FROM EnergyDataStoreV2.tblLoad WHERE tblLoad.hubId = ".$hubId." AND tblLoad.timeStamp >= (NOW() - INTERVAL 1 minute) ORDER BY tblLoad.load DESC LIMIT 1");
		while ($row = mysql_fetch_assoc($results)) {
			$data["current"] = (int)$row['max'];
	    }
	}
	
	echo(json_encode($data));
	
?>