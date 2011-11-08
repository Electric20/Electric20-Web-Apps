<?php

	session_start();

	require_once('../dataStore/utility.php');

	logAction('arrive');

        if (!isset($_SESSION['loggedIn'])) {
		logAction('noauth');
                header('Location: http://www.electric20.com/bzb/login.php?to=logSummary');
        } else {
		$html = "<table>";
            include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
	        $database = "LoggingDatabase";
        	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	        mysql_select_db($database);

        	$results = mysql_query("select email, agent, url, action, timeStamp from LivingWithEnergyPilot join EnergyDataStoreV2.tblUser on user=userId WHERE userId = 
".$_GET['userId']." ORDER BY timeStamp ASC");
        
		$date = "";
		$colour = true;
        	while ($row = mysql_fetch_assoc($results)) {
			if (substr($row['timeStamp'], 0, 10) != $date) {
				$html .= "<tr style='background-color: black; color: white'><td colspan='5'>".substr($row['timeStamp'], 0, 10)."</td></tr>";
				$date = substr($row['timeStamp'], 0, 10);	
			}
			if ($colour) {
				$colour = false;
				$html .= "<tr style='background-color: #eee'>";
			} else {
				$colour = true;
				$html .= "<tr>";
			}
			$html .= 
"<td>".$row['timeStamp']."</td><td>".$row['email']."</td><td>".$row['action']."</td><td>".$row['url']."</td><td style='width: 20%;'>".$row['agent']."</td></tr>";
	        }

  		mysql_free_result($results);
	        mysql_close();
	
		$html .= "</table>";
       	}

?>

<html>
<head>
	<link href="style.css" rel="stylesheet" type="text/css" />
	<style>
body, td {
	font-size: 8pt;
}

td {
	border-right: 1pt dotted;
	padding: 5px;
}
	</style>
</head>
<body>
<?php echo $html; ?>
</body>
</html>
	  
