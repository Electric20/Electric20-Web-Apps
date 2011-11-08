<?php
	session_start();

        include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);

	$hubId = -1;

	if (isset($_SESSION['loggedIn'])) {
                $results = mysql_query("SELECT hubId FROM tblUserInfo WHERE userId=".$_SESSION['loggedIn']);
                while ($row = mysql_fetch_assoc($results)) {
                        $hubId = $row['hubId'];
                }
                mysql_free_result($results);
                unset($results);
	}

	$total = 0;
	$description = null;

	if ($hubId != -1) {
		$results = mysql_query("SELECT * FROM (SELECT tblSensorDir.hubId, tblLoad.sensorId, timeStamp, tblLoad.load, category FROM tblLoad JOIN tblSensorDir ON 
tblLoad.hubId = 
tblSensorDir.hubId AND tblLoad.sensorId = tblSensorDir.sensorId WHERE tblLoad.hubId=".$hubId." AND timeStamp >= DATE_SUB(NOW(), INTERVAL 1 MINUTE) ORDER BY category ASC, 
timeStamp DESC) AS loads JOIN tblHubDir ON loads.hubId = tblHubDir.hubId GROUP BY sensorId");
		$category = 0;
		if ($results != null) {
        	        while ($row = mysql_fetch_assoc($results)) {
				if ($category == 0) {
					$category = $row['category'];
					$description = 
$row['description'];
				}
				if ($row['category'] == $category) {
					$total += $row['load'];
				}
                	}
                	mysql_free_result($results);
                	unset($results);
		}
	}

	echo json_encode(array('description' => $description, 'load' => $total));

	mysql_close();

?>	  
