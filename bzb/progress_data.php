<?php
	session_start();

	require_once('../dataStore/query.php');

        include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);

	$raw = array();
	$max = 0;

	$myHubId = -1;

	$results = mysql_query("SELECT hubId FROM tblUserInfo WHERE userId=".$_SESSION['loggedIn']);
        while ($row = mysql_fetch_assoc($results)) {
                $myHubId = $row['hubId'];
	}

	mysql_free_result($results);

	$results = mysql_query("SELECT tblReports.hubId, tblHubDir.description, UNIX_TIMESTAMP(timeStamp) AS time, KWh FROM tblReports JOIN tblHubDir ON tblReports.hubId=tblHubDir.hubId WHERE timeStamp 
>= 
DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY) ORDER BY hubId ASC, timeStamp 
ASC");
	while ($row = mysql_fetch_assoc($results)) {
		$raw[$row['hubId']]['data'][] = array($row['time']*1000-60000*60*23, floatval($row['KWh']*0.1285));
		$raw[$row['hubId']]['label'] = $row['description'];
		if ($row['hubId'] != $myHubId) {
			$raw[$row['hubId']]['color'] = '#ddd';
		} else {
			$raw[$row['hubId']]['color'] = '#3cf';
		}
		if (floatval($row['KWh']) > $max) {
			$max = floatval($row['KWh']);
		}
	}

	$myHub = $raw[$myHubId];
        unset($raw[$myHubId]);
        array_push($raw, $myHub);

	$average = array();

	foreach ($raw as $hubId => $hubData) {
		$data[] = array('data' => array_reverse($hubData['data']), 'color' => $hubData['color'], 'label' => $hubData['label'], 'hubId' => $hubId);
		foreach ($hubData['data'] as $index => $dayData) {
			$average[$dayData[0]][] = $dayData[1];
		}
	}

	$averageData = array();
	foreach ($average as $index => $dayData) {
		$thisAverage = 0;
		foreach ($dayData as $day) {
			$thisAverage += $day;
		}
		$thisAverage /= count($dayData);
		$averageData[] = array($index, $thisAverage);
	}
	$data[] = array('data' => $averageData, 'color' => 'orange', 'label' =>'Neighbourhood average', 'hubId' => 'undefined');

	mysql_free_result($results);
	unset($results);
	mysql_close();

	echo json_encode(array('data' => $data, 'max' => $max));

	unset($raw);
	unset($average);
	unset($data);
?>	  
