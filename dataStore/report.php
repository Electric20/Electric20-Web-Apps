<?php

	require_once('query.php');

	$rawC = getConsumptionYesterday();

        include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);

        foreach ($rawC as $hubId => $category) {
                        $categoryTotal = array();
                        foreach ($rawC[$hubId] as $sensorC) {
                                if (isset($categoryTotal[$sensorC['category']])) {
                                        $categoryTotal[$sensorC['category']] += $sensorC['total'];
                                } else {
                                        $categoryTotal[$sensorC['category']] = $sensorC['total'];
				}
                        }
                        if ($categoryTotal[1] > 0) {
                                $consumption = $categoryTotal[1];
                        } else if ($categoryTotal[2] > 0) {
                                $consumption = $categoryTotal[2];
                        } else if ($categoryTotal[3] > 0) {
                                $consumption = $categoryTotal[3];
			}
		mysql_query("INSERT INTO tblReports (hubId, timeStamp, KWh) VALUES (".$hubId.", CURRENT_DATE(),".$consumption.")");	
	}

	mysql_close();
	unset($rawC);
?>
