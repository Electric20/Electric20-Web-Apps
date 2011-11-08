<?php
	ini_set('display_errors',1);
	error_reporting(E_ALL|E_STRICT);
    include '/var/www/dataStore/dataAccess/db_connect.php';
	$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
	mysql_select_db($database,$connection);
	$selectStatement = "select userId, venueId, start as startDate1, end as endDate2, (date(start) + interval 1 day) as startDate2, (date(end) - interval 1 day) as endDate1 from tblCalendar where datediff(end,start) = 1";
	$resultSet = mysql_query($selectStatement);
	
	while($dataSet = mysql_fetch_assoc($resultSet))
	{
		$venueId = $dataSet['venueId'];
		$userId = $dataSet['userId'];
		$startDate1 = $dataSet['startDate1'];
		$endDate1 = $dataSet['endDate1'];
		$startDate2 = $dataSet['startDate2'];
		$endDate2 = $dataSet['endDate2'];
		
		$updateStatement = "update tblCalendar set end = '$endDate1 23:59:00' where userId = $userId and venueId = $venueId and start = '$startDate1'";
		$insertStatement = "insert into tblCalendar values($userId, $venueId, '$startDate2 00:01:00', '$endDate2')";
		mysql_query($updateStatement);
		mysql_query($insertStatement);
		print($updateStatement . "\n");
		print($insertStatement . "\n \n");
	}
	
	mysql_close($connection);
	
?>


 
