<?php

        include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);
        $results = mysql_query("SELECT status.hubId, status.sensorId, status.sDescription, status.timeStamp, tblHubDir.description FROM (SELECT tDir.hubId, tDir.sensorId, tDir.description AS 
sDescription, tLoad.timeStamp FROM (SELECT * FROM tblSensorDir WHERE decommissioned IS NULL) AS tDir LEFT JOIN (SELECT * FROM tblLoad ORDER BY timeStamp DESC) AS tLoad 
ON 
tDir.hubId = tLoad.hubId 
AND tDir.sensorId = tLoad.sensorId GROUP BY tDir.hubId, tDir.sensorId) AS status JOIN tblHubDir ON status.hubId = tblHubDir.hubId WHERE pilot = 1 ORDER BY status.timeStamp, status.hubId");

	$data = "";
	$nodata = "";

        while ($row = mysql_fetch_assoc($results)) {
		if (isset($row['timeStamp'])) {
			$data .= "<li><a style='color: white; background-color: green' href='live.php?hubId=".$row['hubId']."&sensorId=".$row['sensorId']."'>".$row['description']." 
".$row['sDescription']." - last sent data @ 
".$row['timeStamp']." 
(".(time()-strtotime($row['timeStamp']))." seconds 
ago)</a></li>";
		} else {
                	$nodata .= "<li><a style='color: white; background-color: red' href='detailed.php?hubId=".$row['hubId']."&sensorId=".$row['sensorId']."'>".$row['description']." 
".$row['sDescription']." - no data</a></li>";
        	}
	}

        mysql_free_result($results);
        unset($results);

	$results = mysql_query("SELECT email, user, timeStamp, agent FROM (SELECT * FROM (SELECT * FROM LoggingDatabase.LivingWithEnergyPilot WHERE user != '' ORDER 
BY 
timeStamp DESC) AS 
log GROUP BY user) AS log2 RIGHT JOIN EnergyDataStoreV2.tblUser ON user=userId 
ORDER BY timeStamp ASC, email ASC");

        $access = "";
        $noaccess = "";

        while ($row = mysql_fetch_assoc($results)) {
                if (isset($row['timeStamp'])) {
                        $access .= "<li><span style='color: white; background-color: green'><a href='logSummary.php?userId=".$row['user']."'>".$row['email']."</a> - last 
accessed 
site 
@ ".$row['timeStamp']." 
from 
".$row['agent']."</span></li>";
               	} else {
                        $noaccess .= "<li><span style='color: white; background-color: red'>".$row['email']." - no access yet</span></li>";
                }
        }

	mysql_free_result($results);
        unset($results);
        mysql_close();

?>

<html>
<head>
<meta http-equiv='refresh' content='60'>
</head>
<body style='font-size: 9pt;'>
<h1>Logs</h1>
<ol>
<li><a href="#tblLoad">Electricity data</a></li>
<li><a href="#access">Website access</a></li>
</ol>
<h2 id="tblLoad">tblLoad Status</h2>
<ul>
<?php echo $nodata; ?>
<?php echo $data; ?>
</ul>
<h2 id="access">Access log</h2>
<ul>
<?php echo $noaccess; ?>
<?php echo $access; ?>
</ul>
</body>
</html>
